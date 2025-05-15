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
 * This file contains the class for download requests
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\requests\download;

use core_user;
use moodle_exception;
use mod_onlyofficeeditor\hasher;
use mod_onlyofficeeditor\jwt_wrapper;
use UnexpectedValueException;

/**
 * Abstract class for download requests
 */
abstract class download_request {
    /** @var stdClass $documenthash */
    public $documenthash;

    /**
     * Constructor
     */
    public function __construct() {
        global $USER;

        $this->check_jwt();

        $hash = required_param('doc', PARAM_RAW);
        $crypt = new hasher();
        list($documenthash, $error) = $crypt->read_hash($hash);

        if ($error || $documenthash === null) {
            throw new moodle_exception('invalidhash', 'assignsubmission_onlyoffice');
        }

        $this->documenthash = $documenthash;

        // Set up user.
        $user = core_user::get_user($this->documenthash->userid);
        if (empty($user)) {
            throw new moodle_exception('invaliduser', 'assignsubmission_onlyoffice');
        }
        $USER = $user;
    }

    /**
     * Check JWT token if the document server secret is set
     */
    protected function check_jwt() {
        $modconfig = get_config('onlyofficeeditor');

        if (!empty($modconfig->documentserversecret)) {
            $jwtheader = !empty($modconfig->jwtheader) ? $modconfig->jwtheader : 'Authorization';
            $token = substr(getallheaders()[$jwtheader], strlen('Bearer '));
            try {
                jwt_wrapper::decode($token, $modconfig->documentserversecret);
            } catch (UnexpectedValueException $e) {
                throw new moodle_exception('invalidjwt', 'assignsubmission_onlyoffice');
            }
        }
    }
}

