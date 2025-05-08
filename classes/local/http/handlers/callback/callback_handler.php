<?php

namespace assignsubmission_onlyoffice\local\http\handlers\callback;

use mod_onlyofficeeditor\util;

abstract class callback_handler {
    const RESULT_OK = 0;
    const RESULT_ERROR = 1;

    public $request;

    public function __invoke()
    {
        $result = self::RESULT_ERROR;

        switch ($this->request->callbackdata->status) {
            case util::STATUS_MUSTSAVE:
            case util::STATUS_ERRORSAVING:
            case util::STATUS_CLOSEDNOCHANGES:
                $result = $this->handle_save();
                break;

            case util::STATUS_EDITING:
                $result = self::RESULT_OK;
                break;
        }

        return $result;
    }

    abstract public function handle_save();
}