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
 * The assign_submission_onlyoffice template file download handler
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
$tmplkey = $hash->tmplkey;
$userid = $hash->userid;
$format = $hash->format;
$templatetype = $hash->templatetype;

// Initialize variables
$canread = false;
$context = null;
$file = null;

// Set up user
$user = \core_user::get_user($userid);
if (empty($user)) {
    http_response_code(400);
    die();
}

$USER = $user;

// Get context
if ($contextid !== 0) {
    list($context, $course, $cm) = get_context_info_array($contextid);
}

// Check permissions and get template file
$canread = !empty($context) ? has_capability('moodle/course:manageactivities', $context) : true;

if (templatekey::get_contextid($tmplkey) === $contextid) {
    $file = filemanager::get_template($contextid);
}

// Check read permission
if (!$canread) {
    http_response_code(403);
    die();
}

// Check if file exists and serve appropriate content
if ($file === null) {
    if (isset($format) && $format !== 'upload') {
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

// Serve the file
send_stored_file($file);
