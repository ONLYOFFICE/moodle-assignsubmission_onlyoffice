<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The assign_submission_onlyoffice class
 *
 * @package    assignsubmission_onlyoffice
 * @copyright  2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_onlyofficeeditor\document_service;
use mod_onlyofficeeditor\configuration_manager;
use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\templatekey;
use assignsubmission_onlyoffice\output\error;
use assignsubmission_onlyoffice\output\settings;
use assignsubmission_onlyoffice\output\submission;
use assignsubmission_onlyoffice\utility;
use mod_onlyofficeeditor\onlyoffice_file_utility;

/**
 * Library class for onlyoffice submission plugin extending submission plugin base class
 */
class assign_submission_onlyoffice extends assign_submission_plugin {

    /**
     * Should return the name of this plugin type.
     *
     * @return string - the name
     */
    public function get_name() {
        return get_string('pluginname', 'assignsubmission_onlyoffice');
    }

    /**
     * Get the default setting for submission plugin.
     *
     * @param MoodleQuickForm $mform - The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $OUTPUT;

        $contextid = 0;
        $tmplkey = null;

        $assignconfig = new stdClass();
        $assignformat = [
            'docx' => get_string('docxformname', 'onlyofficeeditor'),
            'xlsx' => get_string('xlsxformname', 'onlyofficeeditor'),
            'pptx' => get_string('pptxformname', 'onlyofficeeditor'),
            'pdf' => get_string('pdfformname', 'assignsubmission_onlyoffice'),
            'upload' => get_string('uploadfile', 'assignsubmission_onlyoffice'),
        ];

        $mform->addElement('select', 'assignsubmission_onlyoffice_format',
            get_string('assignformat', 'assignsubmission_onlyoffice'), $assignformat);

        $filemanageroptions = [
            'accepted_types' => onlyoffice_file_utility::get_editable_extensions(),
            'maxbytes' => -1,
            'maxfiles' => 1,
            'subdirs' => 0,
        ];

        $filemanager = $mform->createElement('filemanager', 'assignsubmission_onlyoffice_file', null, null, $filemanageroptions);
        $mform->addElement($filemanager);

        $templatetypes = [
            'empty' => get_string('templatetype:empty', 'assignsubmission_onlyoffice'),
            'custom' => get_string('templatetype:custom', 'assignsubmission_onlyoffice'),
        ];

        $mform->addElement('select', 'assignsubmission_onlyoffice_template_type',
            get_string('templatetype', 'assignsubmission_onlyoffice'), $templatetypes);
        $mform->addHelpButton('assignsubmission_onlyoffice_template_type', 'templatetype', 'assignsubmission_onlyoffice');

        if ($this->assignment->has_instance()) {
            $contextid = $this->assignment->get_context()->id;
            $assignconfig = $this->get_config();
            $hassubmission = $this->assignment->count_submissions_with_status(ASSIGN_SUBMISSION_STATUS_SUBMITTED);

            if ($hassubmission) {
                $hassubmissionalert = $OUTPUT->notification(
                    get_string('hassubmissionswarning', 'assignsubmission_onlyoffice'),
                    'warning'
                );
                $hassubmissionwarning = $mform->createElement(
                    'html',
                    $OUTPUT->render_from_template(
                        'assignsubmission_onlyoffice/hassubmissionalert',
                        ['alert' => $hassubmissionalert]
                    )
                );
                $mform->insertElementBefore($hassubmissionwarning, 'assignsubmission_onlyoffice_format');
            }

            if (property_exists($assignconfig, 'format')) {
                $mform->getElement('assignsubmission_onlyoffice_format')
                    ->setSelected($assignconfig->format === 'docxf' ? 'pdf' : $assignconfig->format);
                if ($hassubmission) {
                    $mform->freeze('assignsubmission_onlyoffice_format');
                    $mform->hideIf('assignsubmission_onlyoffice_file', 'assignsubmission_onlyoffice_format', 'eq', 'upload');
                }

                if ($assignconfig->format === 'docxf') {
                    $mform->addElement('hidden', 'assignsubmission_onlyoffice_hidden_format', $assignconfig->format);
                    $mform->setType('assignsubmission_onlyoffice_hidden_format', PARAM_ALPHA);
                }

                if ($assignconfig->format === 'upload') {
                    $draftitemid = 0;
                    file_prepare_draft_area(
                        $draftitemid,
                        $contextid,
                        filemanager::COMPONENT_NAME,
                        filemanager::FILEAREA_ONLYOFFICE_ASSIGN_TEMPLATE,
                        0,
                        $filemanageroptions
                    );
                    $filemanager->setValue($draftitemid);
                }
            }

            if (property_exists($assignconfig, 'templatetype')) {
                $mform->getElement('assignsubmission_onlyoffice_template_type')
                    ->setSelected($assignconfig->templatetype);
                if ($hassubmission) {
                    $mform->freeze('assignsubmission_onlyoffice_template_type');
                }
            }

            if (property_exists($assignconfig, 'tmplkey') && $assignconfig->tmplkey) {
                list($origintmplkey, $contextid) = templatekey::parse_contextid($assignconfig->tmplkey);
                $tmplkey = $origintmplkey;
            }
        } else {
            // Set pdf as default.
            $mform->getElement('assignsubmission_onlyoffice_format')->setSelected('pdf');
        }

        $tmplkey = $tmplkey ?? uniqid();
        $mform->addElement('hidden', 'assignsubmission_onlyoffice_tmplkey', $tmplkey);
        $mform->setType('assignsubmission_onlyoffice_tmplkey', PARAM_ALPHANUM);

        $settingsrenderable = new settings($contextid);
        $mform->addElement('html', $OUTPUT->render($settingsrenderable));

        $mform->hideif('assignsubmission_onlyoffice_format', 'assignsubmission_onlyoffice_enabled', 'notchecked');
        $mform->hideif('assignsubmission_onlyoffice_template_type', 'assignsubmission_onlyoffice_enabled', 'notchecked');
        $mform->disabledif('assignsubmission_onlyoffice_template_type', 'assignsubmission_onlyoffice_format', 'eq', 'upload');
        $mform->hideif('assignsubmission_onlyoffice_file', 'assignsubmission_onlyoffice_format', 'neq', 'upload');
    }

    /**
     * Save the settings for submission plugin.
     *
     * @param stdClass $data - the form data.
     * @return bool - on error the subtype should call set_error and return false.
     */
    public function save_settings(stdClass $data) {
        global $USER;

        $templatetype = property_exists($data, 'assignsubmission_onlyoffice_template_type')
            ? $data->assignsubmission_onlyoffice_template_type
            : null;
        $format = $data->assignsubmission_onlyoffice_format;
        $contextid = $this->assignment->get_context()->id;
        $tmplkey = $data->assignsubmission_onlyoffice_tmplkey;

        $this->set_config(
            'templatetype',
            $format === 'upload' ? 'custom' : $templatetype
        );
        $this->set_config('format', $format);

        if ($format === 'upload') {
            $usercontext = \context_user::instance($USER->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files(
                $usercontext->id,
                'user',
                'draft',
                $data->assignsubmission_onlyoffice_file,
                'sortorder, id',
                false
            );
            $file = reset($files);
            if ($file) {
                // Delete existing files first.
                filemanager::delete_template($contextid);
                filemanager::delete_initial($contextid);

                // Create new files from the uploaded file.
                filemanager::create_template_from_uploaded_file($contextid, $file);
                filemanager::create_initial_from_uploaded_file($contextid, $file);
            }
        } else {
            if (templatekey::get_contextid($tmplkey) !== $contextid) {
                filemanager::delete_template($contextid);
                filemanager::delete_initial($contextid);
            }

            if (!filemanager::get_template($contextid)) {
                $format = isset($data->assignsubmission_onlyoffice_hidden_format)
                    ? $data->assignsubmission_onlyoffice_hidden_format
                    : $data->assignsubmission_onlyoffice_format;
                $this->set_config('format', $format);

                $file = filemanager::create_template(
                    $contextid,
                    $format,
                    $USER->id,
                    $templatetype === 'custom'
                );
                filemanager::create_initial_from_file($file);
            }
        }

        $this->set_config('tmplkey', $tmplkey . '_' . $contextid);

        return true;
    }

    /**
     * Get any additional fields for the submission form for this assignment.
     *
     * @param stdClass $submission
     * @param MoodleQuickForm $mform
     * @param stdClass $data - the form data.
     * @param int $userid
     * @return boolean - true if we added anything to the form
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data, $userid = null) {
        global $OUTPUT;
        global $CFG;
        global $USER;

        $cfg = $this->get_config();
        $contextid = $this->assignment->get_context()->id;

        $initialfile = filemanager::get_initial($contextid);

        $submissionformat = utility::get_form_format();

        $isform = $cfg->format === 'pdf' || $cfg->format === 'docxf';
        $submissionfile = filemanager::get($contextid, $submission->id);

        if ($initialfile && $submissionfile
            && $initialfile->get_timemodified() > $submissionfile->get_timemodified()) {
            $submissionfile->delete();
            $submissionfile = null;
        }

        if (!!$this->assignment->get_instance()->teamsubmission) {
            $filenamesuffix = $submission->groupid == "0" ? 'default' : groups_get_group_name($submission->groupid);
        } else {
            $filenamesuffix = fullname($USER);
        }

        if ($submissionfile === null) {
            if ($initialfile) {
                $initialfilename = $initialfile->get_filename();
                $initialextension = strtolower(pathinfo($initialfilename, PATHINFO_EXTENSION));

                $submissionfile = filemanager::create_by_initial($initialfile,
                                                                    $submission->id,
                                                                    $this->assignment->get_instance()->name,
                                                                    $initialextension,
                                                                    $submission->userid,
                                                                    $filenamesuffix);
            } else {
                $submissionfile = filemanager::create($contextid,
                                                        $submission->id,
                                                        $this->assignment->get_instance()->name,
                                                        $cfg->format,
                                                        $submission->userid,
                                                        $filenamesuffix,
                                                    );
            }
        }

        if ($submissionfile === null) {
            $mform->addElement('html', $OUTPUT->render(
                new error(get_string('formnotready', 'assignsubmission_onlyoffice'))
            ));

            return true;
        }

        $submissionfilename = $submissionfile->get_filename();
        $submissionextension = strtolower(pathinfo($submissionfilename, PATHINFO_EXTENSION));
        if ($isform
            && $submissionextension !== $submissionformat) {
            $crypt = new \mod_onlyofficeeditor\hasher();
            $downloadhash = $crypt->get_hash([
                'action' => 'download',
                'contextid' => $contextid,
                'itemid' => $submissionfile->get_itemid(),
                'tmplkey' => null,
                'userid' => $submissionfile->get_userid(),
            ]);

            $storageurl = $CFG->wwwroot;
            if (class_exists('mod_onlyofficeeditor\configuration_manager')) {
                $storageurl = configuration_manager::get_storage_url();
            }
            $documenturi = $storageurl . '/mod/assign/submission/onlyoffice/download.php?doc=' . $downloadhash;
            $conversionkey = filemanager::generate_key($submissionfile);

            $conversionurl = document_service::get_conversion_url($documenturi,
                                                                    $submissionextension,
                                                                    $submissionformat,
                                                                    $conversionkey);

            filemanager::write($submissionfile, $conversionurl);
            $submissionfile->rename($submissionfile->get_filepath(),
                                    $this->assignment->get_instance()->name . $submission->id . '.' . $submissionformat);
        }

        $submissionrenderable = new submission(
            $contextid,
            $submission->id,
            false,
            $cfg->format,
            $cfg->templatetype
        );
        $mform->addElement('html', $OUTPUT->render($submissionrenderable));

        return true;
    }

    /**
     * View submission - the submission file will always be read only.
     *
     * @param stdClass $submission
     * @return string - html frame of the submitted file.
     */
    public function view(stdClass $submission) {
        global $OUTPUT;

        $contextid = $this->assignment->get_context()->id;

        $submissionfile = filemanager::get($contextid, $submission->id);
        if ($submissionfile === null) {
            return get_string('filenotfound', 'assignsubmission_onlyoffice');
        }

        $html = $OUTPUT->render(new submission($contextid, $submission->id, true));

        return $html;
    }

    /**
     * View the submission summary and sets whether a view link be provided.
     *
     * @param stdClass $submission
     * @param bool $showviewlink - whether or not to have a link to view the submission file.
     * @return string view text.
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        $showviewlink = false;
        $linkname = null;

        $contextid = $this->assignment->get_context()->id;
        $submissionfile = filemanager::get($contextid, $submission->id);
        if ($submissionfile !== null) {
            $showviewlink = true;
            $linkname = get_string('viewdocument', 'assignsubmission_onlyoffice');
        }

        return $linkname;
    }

    /**
     * Is this assignment plugin empty?(ie no submission)
     *
     * @param stdClass $submission assign_submission.
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        $contextid = $this->assignment->get_context()->id;

        $submissionfile = filemanager::get($contextid, $submission->id);
        if ($submissionfile === null) {
            return true;
        }

        return false;
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     *
     * @return array - An array of fileareas(keys) and descriptions(values)
     */
    public function get_file_areas() {
        return [
            filemanager::FILEAREA_ONLYOFFICE_SUBMISSION_FILE => $this->get_name(),
            filemanager::FILEAREA_ONLYOFFICE_ASSIGN_TEMPLATE => $this->get_name(),
            filemanager::FILEAREA_ONLYOFFICE_ASSIGN_INITIAL => $this->get_name(),
        ];
    }

    /**
     * Produce a list of files suitable for export that represent this submission.
     *
     * @param stdClass $submission
     * @param stdClass $user
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission, stdClass $user) {
        $result = [];

        $contextid = $this->assignment->get_context()->id;

        $submissionfile = filemanager::get($contextid, $submission->id);
        if ($submissionfile !== null) {
            $filename = $submissionfile->get_filename();
            $result[$filename] = $submissionfile;
        }

        return $result;
    }

    /**
     * Remove any saved data from this submission.
     *
     * @param stdClass $submission - assign_submission data
     * @return void
     */
    public function remove(stdClass $submission) {
        $contextid = $this->assignment->get_context()->id;

        filemanager::delete($contextid, $submission->id);
    }
}
