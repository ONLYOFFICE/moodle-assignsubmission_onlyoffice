<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Class build_editor_config
 * @package    assignsubmission_onlyoffice
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\external\submissions;

// phpcs:ignore moodle.Files.MoodleInternal.MoodleInternalGlobalState
global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');

use assignsubmission_onlyoffice\filemanager;
use context_system;
use external_api;
use external_function_parameters;
use external_value;
use mod_onlyofficeeditor\configuration_manager;
use mod_onlyofficeeditor\jwt_wrapper;
use mod_onlyofficeeditor\onlyoffice_file_utility;

/**
 * Submission editor config builder external function class
 */
class build_editor_config extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'context id'),
            'submissionid' => new external_value(PARAM_INT, 'submission id'),
            'readonly' => new external_value(PARAM_BOOL, 'readonly'),
            'format' => new external_value(PARAM_TEXT, 'format'),
            'templatetype' => new external_value(PARAM_TEXT, 'templatetype'),
        ]);
    }

    /**
     * Build editor config for submissions.
     * @param int $contextid context id
     * @param int $submissionid submission id
     * @param string $readonly readonly
     * @param string $format format
     * @param string $templatetype templatetype
     * @return array editor config
     */
    public static function execute($contextid, $submissionid, $readonly, $format, $templatetype) {
        global $USER;
        global $DB;

        [
            'contextid' => $contextid,
            'submissionid' => $submissionid,
            'readonly' => $readonly,
            'format' => $format,
            'templatetype' => $templatetype,
        ] = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'submissionid' => $submissionid,
            'readonly' => $readonly,
            'format' => $format,
            'templatetype' => $templatetype,
        ]);

        $context = \context::instance_by_id($contextid);
        $coursecontext = $context->get_course_context();
        $cansubmit = has_capability('mod/assign:submit', $coursecontext);
        $cangrade = has_capability('mod/assign:grade', $coursecontext);

        if (!$cansubmit && !$cangrade) {
            throw new \moodle_exception('You do not have the required permissions to access this submission.');
        }

        $modconfig = get_config('onlyofficeeditor');
        $storageurl = configuration_manager::get_storage_url();

        $context = null;
        $assign = null;
        $submission = null;
        $file = null;
        $groupmode = false;

        list($context, $course, $cm) = get_context_info_array($contextid);
        $assign = new \assign($context, $cm, $course);

        // Get the ONLYOFFICE submission plugin.
        $onlyofficeplugin = $assign->get_submission_plugin_by_type('onlyoffice');

        // Get the plugin configuration.
        $onlyofficepluginconfig = $onlyofficeplugin->get_config();

        $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);
        if (!$submission) {
            throw new \moodle_exception('Submission not found');
        }

        $groupmode = !!$assign->get_instance()->teamsubmission;

        $file = filemanager::get($contextid, $submissionid);

        if ($file === null) {
            throw new \moodle_exception('File not found');
        }

        $filename = $file->get_filename();
        $key = filemanager::generate_key($file);

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $crypt = new \mod_onlyofficeeditor\hasher();
        $downloadhash = $crypt->get_hash([
            'contextid' => $contextid,
            'submissionid' => $submissionid,
            'userid' => $USER->id,
        ]);

        $config = [
            'document' => [
                'fileType' => $ext,
                'key' => $key,
                'title' => $filename,
                'url' => $storageurl . '/mod/assign/submission/onlyoffice/api/download/submission.php?doc=' . $downloadhash,
            ],
            'documentType' => onlyoffice_file_utility::get_document_type('.' . $ext),
            'editorConfig' => [
                'lang' => $USER->lang,
                'user' => [
                    'id' => $USER->id,
                    'name' => \fullname($USER),
                ],
            ],
        ];

        if ($format === 'pdf') {
            $config['document']['isForm'] = true;
        }

        $canedit = in_array('.' . $ext, onlyoffice_file_utility::get_editable_extensions());

        $editable = !$groupmode ? $assign->can_edit_submission($submission->userid)
            : $assign->can_edit_group_submission($submission->groupid);

        $config['document']['permissions']['edit'] = $editable;
        if ($editable && $canedit && !$readonly) {
            $callbackhash = $crypt->get_hash([
                'contextid' => $contextid,
                'submissionid' => $submissionid,
                'userid' => $USER->id,
            ]);
            $config['editorConfig']['callbackUrl'] = $storageurl .
                '/mod/assign/submission/onlyoffice/api/callback/submission.php?doc=' .
                $callbackhash;
            // Disable editing for users who has a student role assigned.

            if ($format === 'pdf' && $templatetype === 'custom') {
                $config['document']['permissions']['edit'] = false;
                $config['document']['permissions']['fillForms'] = true;
            }
        } else {
            if ($cangrade || $cansubmit && $onlyofficepluginconfig->enablecomment) {
                $callbackhash = $crypt->get_hash([
                    'contextid' => $contextid,
                    'submissionid' => $submissionid,
                    'userid' => $USER->id,
                    'notifyusers' => true,
                ]);
                $config['editorConfig']['callbackUrl'] = $storageurl .
                    '/mod/assign/submission/onlyoffice/api/callback/submission.php?doc=' .
                    $callbackhash;

                $config['editorConfig']['mode'] = 'edit';
                $config['document']['permissions']['edit'] = false;
                $config['document']['permissions']['comment'] = true;
            } else {
                $config['editorConfig']['mode'] = 'view';
            }
        }

        $config['document']['permissions']['protect'] = false;

        $customization = [];
        $customization['integrationMode'] = 'embed';

        if (isset($modconfig->editor_security_plugin)) {
            $customization['plugins'] = $modconfig->editor_security_plugin == 1;
        }
        if (isset($modconfig->editor_security_macros)) {
            $customization['macros'] = $modconfig->editor_security_macros == 1;
        }

        $config['editorConfig']['customization'] = $customization;

        if (!empty($modconfig->documentserversecret)) {
            $token = jwt_wrapper::encode($config, $modconfig->documentserversecret);
            $config['token'] = $token;
        }

        return json_encode($config);
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Config');
    }
}
