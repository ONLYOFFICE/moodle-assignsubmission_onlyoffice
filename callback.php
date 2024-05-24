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

require_once(__DIR__.'/../../../../config.php');
require_once(__DIR__.'/../../locallib.php');

use mod_onlyofficeeditor\util;
use mod_onlyofficeeditor\document_service;
use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\templatekey;
use assignsubmission_onlyoffice\utility;
use mod_onlyofficeeditor\configuration_manager;

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

if ($hash->action !== 'track') {
    http_response_code(400);
    die();
}

$contextid = $hash->contextid;
$itemid = $hash->itemid;
$tmplkey = $hash->tmplkey;
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

        if ($contextid === 0) {
            $contextid = templatekey::get_contextid($tmplkey);
        }
        if ($contextid === 0) {
            http_response_code(400);
            die();
        }

        list($context, $course, $cm) = get_context_info_array($contextid);
        if (isset($tmplkey)) {
            $canwrite = has_capability('moodle/course:manageactivities', $context);
        } else {
            $assing = new assign($context, $cm, $course);
            $submission = $DB->get_record('assign_submission', array('id' => $itemid));
            if ($submission) {
                $canwrite = !!$submission->groupid ? $assing->can_edit_group_submission($submission->groupid)
                                                   : $assing->can_edit_submission($submission->userid);
            }
        }

        if (!$canwrite) {
            http_response_code(403);
            die();
        }

        $file = !isset($tmplkey) ? filemanager::get($contextid, $itemid) : filemanager::get_template($contextid);
        if (empty($file) && isset($tmplkey)) {
            $file = filemanager::create_template($contextid, 'docxf', $itemid);
            $mustsaveinitial = true;
        }

        if (empty($file)) {
            http_response_code(404);
            die();
        }

        if (isset($url)) {
            filemanager::write($file, $url);

            if (isset($tmplkey)) {
                $mustsaveinitial = true;
            }
        }

        $filename = $file->get_filename();
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if ($ext === 'docxf' && $mustsaveinitial) {
            $submissionformat = utility::get_form_format();

            $crypt = new \mod_onlyofficeeditor\hasher();
            $downloadhash = $crypt->get_hash([
                'action' => 'download',
                'contextid' => $contextid,
                'itemid' => 0,
                'tmplkey' => $tmplkey,
                'userid' => $USER->id
            ]);

            $storageurl = $CFG->wwwroot;
            if (class_exists('mod_onlyofficeeditor\configuration_manager')) {
                $storageurl = configuration_manager::get_storage_url();
            }
            $documenturi = $storageurl . '/mod/assign/submission/onlyoffice/download.php?doc=' . $downloadhash;
            $conversionkey = filemanager::generate_key($file);
            $pdfparam = [];
            if ($submissionformat === 'pdf') {
                $pdfparam['form'] = true;
            }

            $conversionurl = document_service::get_conversion_url($documenturi, $ext, $submissionformat, $conversionkey, $pdfparam);

            if (empty($conversionurl)) {
                break;
            }

            $initialfile = filemanager::get_initial($contextid);
            if ($initialfile === null) {
                filemanager::create_initial($contextid, $submissionformat, $itemid, $conversionurl);
            } else {
                filemanager::write($initialfile, $conversionurl);
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
