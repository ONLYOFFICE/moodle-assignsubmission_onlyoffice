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
 * This file contains the class for callback requests
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\requests\callback;

use moodle_exception;
use mod_onlyofficeeditor\hasher;
use UnexpectedValueException;

/**
 * Abstract class for callback requests
 */
abstract class callback_request {
    /** @var stdClass $documenthash */
    public $documenthash;
    /** @var object $callbackdata */
    public $callbackdata;

    /**
     * Constructor
     */
    public function __construct() {
        $hash = required_param('doc', PARAM_RAW);
        $crypt = new hasher();
        [$documenthash, $error] = $crypt->read_hash($hash);

        if ($error || $documenthash === null) {
            throw new moodle_exception('invalidhash', 'assignsubmission_onlyoffice');
        }

        $this->documenthash = $documenthash;

        $bodystream = file_get_contents('php://input');
        $data = json_decode($bodystream);

        $this->collect_callback_data_from_jwt($data);
    }

    /**
     * Collect callback data from JWT
     *
     * @param object $data
     */
    private function collect_callback_data_from_jwt($data) {
        $modconfig = get_config('onlyofficeeditor');

        if (!empty($modconfig->documentserversecret)) {
            if (!empty($data->token)) {
                try {
                    $payload = \mod_onlyofficeeditor\jwt_wrapper::decode($data->token, $modconfig->documentserversecret);
                } catch (UnexpectedValueException $e) {
                    throw new moodle_exception('invalidjwt', 'assignsubmission_onlyoffice');
                }
            } else {
                $jwtheader = !empty($modconfig->jwtheader) ? $modconfig->jwtheader : 'Authorization';
                $token = substr(getallheaders()[$jwtheader], strlen('Bearer '));
                try {
                    $decodedheader = \mod_onlyofficeeditor\jwt_wrapper::decode($token, $modconfig->documentserversecret);

                    $payload = $decodedheader->payload;
                } catch (\UnexpectedValueException $e) {
                    throw new moodle_exception('invalidjwt', 'assignsubmission_onlyoffice');
                }
            }

            $this->callbackdata = $payload;
        } else {
            $this->callbackdata = $data;
        }
    }
}
