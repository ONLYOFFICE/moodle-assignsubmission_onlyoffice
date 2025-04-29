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
 * The assign_submission_onlyoffice submission file download handler
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

global $USER;
global $DB;

// JWT authentication
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

// Get and validate the document hash
$doc = required_param('doc', PARAM_RAW);

$crypt = new \mod_onlyofficeeditor\hasher();
list($hash, $error) = $crypt->read_hash($doc);

if ($error || $hash === null) {
    http_response_code(403);
    die();
}

// Extract required parameters
$contextid = $hash->contextid;
$submissionid = $hash->submissionid;
$userid = $hash->userid;

// Initialize variables
$canread = false;
$context = null;
$assign = null;
$submission = null;
$file = null;

// Set up user
$user = \core_user::get_user($userid);
if (empty($user)) {
    http_response_code(400);
    die();
}

$USER = $user;

// Get context and assignment
if ($contextid !== 0) {
    list($context, $course, $cm) = get_context_info_array($contextid);
    $assign = new assign($context, $cm, $course);
}

// Get submission and check permissions
$submission = $DB->get_record('assign_submission', ['id' => $submissionid]);
if (!$submission) {
    http_response_code(400);
    die();
}

if (!empty($assign)) {
    $canread = !!$assign->get_instance()->teamsubmission ? 
        $assign->can_view_group_submission($submission->groupid) : 
        $assign->can_view_submission($submission->userid);
}

// Get the file
$file = filemanager::get($contextid, $submissionid);

// Check read permission
if (!$canread) {
    http_response_code(403);
    die();
}

// Check if file exists
if ($file === null) {
    http_response_code(404);
    die();
}

// Serve the file
send_stored_file($file);
