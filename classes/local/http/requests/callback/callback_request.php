<?php

namespace assignsubmission_onlyoffice\local\http\requests\callback;

use moodle_exception;
use mod_onlyofficeeditor\hasher;
use UnexpectedValueException;

abstract class callback_request {
    public $documenthash;
    public $callbackdata;

    public function __construct() {
        $hash = required_param('doc', PARAM_RAW);
        $crypt = new hasher();
        list($documenthash, $error) = $crypt->read_hash($hash);

        if ($error || $documenthash === null) {
            throw new moodle_exception('invalidhash', 'assignsubmission_onlyoffice');
        }

        $this->documenthash = $documenthash;

        $bodystream = file_get_contents('php://input');
        $data = json_decode($bodystream);

        $this->collect_callback_data_from_jwt($data);
    }

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
    
