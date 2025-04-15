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
 * The assign_submission_onlyoffice editor api actions
 *
 * @package    assignsubmission_onlyoffice
 * @subpackage
 * @copyright   2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__.'/../../../../../config.php');
require_once(__DIR__.'/../../../locallib.php');
// phpcs:enable

use mod_onlyofficeeditor\onlyoffice_file_utility;
use mod_onlyofficeeditor\jwt_wrapper;
use mod_onlyofficeeditor\configuration_manager;
use assignsubmission_onlyoffice\filemanager;

global $USER;
global $DB;

$contextid = required_param('contextid', PARAM_INT);
$itemid = required_param('itemid', PARAM_INT);
$readonly = !!optional_param('readonly', 0, PARAM_BOOL);
$tmplkey = optional_param('tmplkey', null, PARAM_ALPHANUMEXT);
$format = optional_param('format', null, PARAM_ALPHA);
$templatetype = optional_param('templatetype', null, PARAM_ALPHA);

$modconfig = get_config('onlyofficeeditor');
$storageurl = $CFG->wwwroot;
if (class_exists('mod_onlyofficeeditor\configuration_manager')) {
    $storageurl = configuration_manager::get_storage_url();
}

$context = null;
$assing = null;
$submission = null;
$file = null;
$groupmode = false;

if ($contextid !== 0) {
    list($context, $course, $cm) = get_context_info_array($contextid);
    $assing = new assign($context, $cm, $course);
}

if (!isset($tmplkey)) {
    $submission = $DB->get_record('assign_submission', ['id' => $itemid]);
    if (!$submission) {
        http_response_code(400);
        die();
    }

    $groupmode = !!$assing->get_instance()->teamsubmission;

    $file = filemanager::get($contextid, $itemid);
} else {
    $file = filemanager::get_template($contextid);
}

if ($file === null
    && (!isset($tmplkey) || $format === null)) {
    http_response_code(404);
    die();
}

$filename = !empty($file) ? $file->get_filename() : "form_template.$format";
$key = !empty($file) ? filemanager::generate_key($file) : $tmplkey;

$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

$crypt = new \mod_onlyofficeeditor\hasher();
$downloadhash = $crypt->get_hash([
    'action' => 'download',
    'contextid' => $contextid,
    'itemid' => $itemid,
    'tmplkey' => $tmplkey,
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

$canedit = in_array('.' . $ext, onlyoffice_file_utility::get_editable_extensions());

$editable = false;
if (!empty($assing) && !empty($submission)) {
    $editable = !$groupmode ? $assing->can_edit_submission($submission->userid)
                            : $assing->can_edit_group_submission($submission->groupid);
} else if (isset($tmplkey)) {
    $editable = !empty($context) ? has_capability('moodle/course:manageactivities', $context) : true;
}

$config['document']['permissions']['edit'] = $editable;
if ($editable && $canedit && !$readonly) {
    $callbackhash = $crypt->get_hash([
        'action' => 'track',
        'contextid' => $contextid,
        'itemid' => $itemid,
        'tmplkey' => $tmplkey,
        'userid' => $USER->id,
        'format' => $format,
        'templatetype' => $templatetype,
    ]);
    $config['editorConfig']['callbackUrl'] = $storageurl . '/mod/assign/submission/onlyoffice/callback.php?doc=' . $callbackhash;
    // Disable editing for users who has a student role assigned.
    if (
        $ext === 'pdf'
        && ((user_has_role_assignment($USER->id, 5) && $templatetype === 'custom')
        || (is_siteadmin($USER) && $context && !has_capability('moodle/course:manageactivities', $context)))
    ) {
        $config['document']['permissions']['edit'] = false;
    }
    $config['document']['permissions']['fillForms'] = true;
} else {
    $viewable = $assing->can_grade() || $editable;

    if (!$viewable) {
        http_response_code(403);
        die();
    }

    $config['editorConfig']['mode'] = 'view';
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

echo json_encode($config);
