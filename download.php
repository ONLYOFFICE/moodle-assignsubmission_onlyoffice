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
 * The assign_submission_onlyoffice download handler
 *
 * @package    assignsubmission_onlyoffice
 * @copyright  2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__.'/../../../../config.php');
require_once(__DIR__.'/../../locallib.php');
// phpcs:enable

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\templatekey;

global $USER;
global $DB;

$modconfig = get_config('onlyofficeeditor');
if (!empty($modconfig->documentserversecret)) {
    $jwtheader = !empty($modconfig->jwtheader) ? $modconfig->jwtheader : 'Authorization';
    $token = substr(getallheaders()[$jwtheader], strlen('Bearer '));
    try {
        $decodedheader = \mod_onlyofficeeditor\jwt_wrapper::decode($token, $modconfig->documentserversecret);
    } catch (\UnexpectedValueException $e) {
        http_response_code(403);
        die();
    }
}

$doc = required_param('doc', PARAM_RAW);

$crypt = new \mod_onlyofficeeditor\hasher();
list($hash, $error) = $crypt->read_hash($doc);

if ($error || $hash === null) {
    http_response_code(403);
    die();
}

if ($hash->action !== 'download') {
    http_response_code(400);
    die();
}

$contextid = $hash->contextid;
$itemid = $hash->itemid;
$tmplkey = $hash->tmplkey;
$userid = $hash->userid;
$format = $hash->format;
$templatetype = $hash->templatetype;

$user = null;
$canread = false;
$context = null;
$assing = null;
$submission = null;
$file = null;

$user = \core_user::get_user($userid);
if (empty($user)) {
    http_response_code(400);
    die();
}

$USER = $user;

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

    if (!empty($assing)) {
        $canread = !!$assing->get_instance()->teamsubmission ? $assing->can_view_group_submission($submission->groupid)
                                          : $assing->can_view_submission($submission->userid);
    }

    $file = filemanager::get($contextid, $itemid);
} else {
    $canread = !empty($context) ? has_capability('moodle/course:manageactivities', $context) : true;

    if (templatekey::get_contextid($tmplkey) === $contextid) {
        $file = filemanager::get_template($contextid);
    }
}

if (!$canread) {
    http_response_code(403);
    die();
}

if ($file === null) {
    if (isset($tmplkey) && isset($format) && $format !== 'upload') {
        $withsample = $templatetype === 'custom';
        $templatepath = filemanager::get_template_path($format, $withsample);
        $templatename = pathinfo($templatepath, PATHINFO_BASENAME);

        send_file($templatepath, $templatename, 0, 0, false, false, '', false, []);

        return;
    } else {
        http_response_code(404);
        die();
    }
}

send_stored_file($file);
