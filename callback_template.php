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
 * The assign_submission_onlyoffice callback handler for templates
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
use assignsubmission_onlyoffice\templatekey;

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
$tmplkey = $hash->tmplkey;
$callbackuserid = $hash->userid;
$format = $hash->format;
$templatetype = $hash->templatetype;

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

        if ($contextid === 0) {
            $contextid = templatekey::get_contextid($tmplkey);
        }
        if ($contextid === 0) {
            http_response_code(400);
            die();
        }

        list($context, $course, $cm) = get_context_info_array($contextid);

        $canwrite = has_capability('moodle/course:manageactivities', $context);

        if (!$canwrite) {
            http_response_code(403);
            die();
        }

        $file = filemanager::get_template($contextid);

        if (empty($file)) {
            http_response_code(404);
            die();
        }

        if ($url) {
            filemanager::write($file, $url);

            $initialfile = filemanager::get_initial($contextid);

            if ($initialfile === null) {
                filemanager::create_initial_from_file($file);
            } else {
                filemanager::write_to_initial_from_file($initialfile, $file);
            }

            if ($tmplkey) {
                $newkey = uniqid() . '_' . $contextid;
                templatekey::replace_record($tmplkey, $newkey);
            }
        }

        $result = 0;
        break;

    case util::STATUS_EDITING:
        $result = 0;
        break;
}

http_response_code(200);
echo(json_encode(['error' => $result]));
