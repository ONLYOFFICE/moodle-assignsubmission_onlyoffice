<?php

namespace assignsubmission_onlyoffice\local\http\requests\download;

use core_user;
use moodle_exception;
use mod_onlyofficeeditor\hasher;
use mod_onlyofficeeditor\jwt_wrapper;
use UnexpectedValueException;

abstract class download_request {
    public $documenthash;

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

        // Set up user
        $user = core_user::get_user($this->documenthash->userid);
        if (empty($user)) {
            throw new moodle_exception('invaliduser', 'assignsubmission_onlyoffice');
        }
        $USER = $user;
    }

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
    
