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
 * The assign_submission_onlyoffice callback handler for submission
 *
 * @package    assignsubmission_onlyoffice
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__.'/../../../../config.php');
require_once(__DIR__.'/../../locallib.php');
// phpcs:enable

use mod_onlyofficeeditor\util;
use assignsubmission_onlyoffice\filemanager;

global $USER;
global $DB;
global $CFG;

$doc = required_param('doc', PARAM_RAW);

$crypt = new \mod_onlyofficeeditor\hasher();
list($hash, $error) = $crypt->read_hash($doc);

if ($error || $hash === null) {
    http_response_code(403);
    die();
}

$contextid = $hash->contextid;
$submissionid = $hash->submissionid;
$callbackuserid = $hash->userid;

$bodystream = file_get_contents('php://input');
$data = json_decode($bodystream);

$status = $data->status;
$url = isset($data->url) ? $data->url : null;
$users = isset($data->users) ? $data->users : null;

$modconfig = get_config('onlyofficeeditor');
if (!empty($modconfig->documentserversecret)) {
    if (!empty($data->token)) {
        try {
            $payload = \mod_onlyofficeeditor\jwt_wrapper::decode($data->token, $modconfig->documentserversecret);
        } catch (\UnexpectedValueException $e) {
            $response['status'] = 'error';
            $response['error'] = '403 Access denied';
            die(json_encode($response));
        }
    } else {
        $jwtheader = !empty($modconfig->jwtheader) ? $modconfig->jwtheader : 'Authorization';
        $token = substr(getallheaders()[$jwtheader], strlen('Bearer '));
        try {
            $decodedheader = \mod_onlyofficeeditor\jwt_wrapper::decode($token, $modconfig->documentserversecret);

            $payload = $decodedheader->payload;
        } catch (\UnexpectedValueException $e) {
            $response['status'] = 'error';
            $response['error'] = '403 Access denied';
            die(json_encode($response));
        }
    }

    $status = $payload->status;
    $url = isset($payload->url) ? $payload->url : null;
    $users = isset($payload->users) ? $payload->users : null;
}

$status = $data->status;
$url = isset($data->url) ? $data->url : null;
$users = isset($data->users) ? $data->users : null;

$result = 1;
switch ($status) {
    case util::STATUS_MUSTSAVE:
    case util::STATUS_ERRORSAVING:
    case util::STATUS_CLOSEDNOCHANGES:
        $file = null;
        $canwrite = false;
        $mustsaveinitial = false;

        $userid = isset($users) ? $users[0] : $callbackuserid;
        $user = \core_user::get_user($userid);
        if ($user) {
            $USER = $user;
        }

        list($context, $course, $cm) = get_context_info_array($contextid);
        $assing = new assign($context, $cm, $course);
        $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);
        if ($submission) {
            $canwrite = !!$submission->groupid ? $assing->can_edit_group_submission($submission->groupid)
                                               : $assing->can_edit_submission($submission->userid);
        }

        if (!$canwrite) {
            http_response_code(403);
            die();
        }

        $file = filemanager::get($contextid, $submissionid);
        if (empty($file)) {
            http_response_code(404);
            die();
        }

        if (isset($url)) {
            filemanager::write($file, $url);
        }

        $result = 0;
        break;

    case util::STATUS_EDITING:
        $result = 0;
        break;
}

http_response_code(200);
echo(json_encode(['error' => $result]));
