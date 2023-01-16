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
 * @copyright  2022 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../../config.php');

use mod_onlyofficeeditor\onlyoffice_file_utility;
use assignsubmission_onlyoffice\filemanager;

$modconfig = get_config('onlyofficeeditor');
if (!empty($modconfig->documentserversecret)) {
    $token = substr(getallheaders()['Authorization'], strlen('Bearer '));
    try {
        $decodedheader = \Firebase\JWT\JWT::decode($token, $modconfig->documentserversecret, array('HS256'));
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
$groupmode = $hash->groupmode;

$submissionfile = filemanager::get($contextid, $itemid, $groupmode);
if ($submissionfile === null) {
    http_response_code(404);
}

send_stored_file($submissionfile);