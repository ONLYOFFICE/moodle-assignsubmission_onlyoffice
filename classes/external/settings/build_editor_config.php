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

namespace assignsubmission_onlyoffice\external\settings;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\templatekey;
use context_system;
use external_api;
use external_function_parameters;
use external_value;
use mod_onlyofficeeditor\configuration_manager;
use mod_onlyofficeeditor\jwt_wrapper;
use mod_onlyofficeeditor\onlyoffice_file_utility;

/**
 * Settings editor config builder external function class
 */
class build_editor_config extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'context id'),
            'key' => new external_value(PARAM_ALPHANUMEXT, 'template key', VALUE_REQUIRED),
            'format' => new external_value(PARAM_TEXT, 'template format', VALUE_REQUIRED),
            'templatetype' => new external_value(PARAM_TEXT, 'templatetype', VALUE_REQUIRED),
        ]);
    }

    /**
     * Build editor config for settings.
     * @param int $contextid context id
     * @param string $key template key
     * @param string $format template format
     * @param string $templatetype templatetype
     * @return array editor config
     */
    public static function execute($contextid, $key, $format, $templatetype) {
        global $USER;

        $contextsystem = context_system::instance();
        self::validate_context($contextsystem);
        require_capability('moodle/site:config', $contextsystem);

        [
            'contextid' => $contextid,
            'key' => $key,
            'format' => $format,
            'templatetype' => $templatetype,
        ] = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'key' => $key,
            'format' => $format,
            'templatetype' => $templatetype,
        ]);

        $modconfig = get_config('onlyofficeeditor');
        $storageurl = configuration_manager::get_storage_url();

        if (templatekey::get_contextid($key) === $contextid) {
            $file = filemanager::get_template($contextid);
        }

        $filename = !empty($file) ? $file->get_filename() : "template." . $format;

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $crypt = new \mod_onlyofficeeditor\hasher();
        $downloadhash = $crypt->get_hash([
            'action' => 'download',
            'contextid' => $contextid,
            'itemid' => 0,
            'tmplkey' => $key,
            'userid' => $USER->id,
            'format' => $format,
            'templatetype' => $templatetype,
        ]);

        $config = [
            'document' => [
                'fileType' => $ext,
                'key' => $key,
                'title' => $filename,
                'url' => $storageurl . '/mod/assign/submission/onlyoffice/download.php?doc=' . $downloadhash,
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

        $config['document']['permissions'] = [
            'edit' => true,
            'protect' => false,
        ];

        $callbackhash = $crypt->get_hash([
            'action' => 'track',
            'contextid' => $contextid,
            'itemid' => 0,
            'tmplkey' => $key,
            'userid' => $USER->id,
            'format' => $format,
            'templatetype' => $templatetype,
        ]);
        $config['editorConfig']['callbackUrl'] = $storageurl .
            '/mod/assign/submission/onlyoffice/callback.php?doc=' .
            $callbackhash;

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
