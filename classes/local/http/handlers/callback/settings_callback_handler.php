<?php

namespace assignsubmission_onlyoffice\local\http\handlers\callback;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\local\http\requests\callback\settings_callback_request;
use assignsubmission_onlyoffice\templatekey;

class settings_callback_handler extends callback_handler
{
    public function __construct(settings_callback_request $request)
    {
        $this->request = $request;
    }

    public function handle_save()
    {
        $file = null;
        $canwrite = false;

        $canwrite = has_capability('moodle/course:manageactivities', $this->request->context, $this->request->callbackuser);

        if (!$canwrite) {
            return self::RESULT_ERROR;
        }

        $file = filemanager::get_template($this->request->context->id);

        if (empty($file)) {
            return self::RESULT_ERROR;
        }

        if ($this->request->callbackdata->url) {
            filemanager::write($file, $this->request->callbackdata->url);

            $initialfile = filemanager::get_initial($this->request->context->id);

            if ($initialfile === null) {
                filemanager::create_initial_from_file($file);
            } else {
                filemanager::write_to_initial_from_file($initialfile, $file);
            }

            if ($this->request->tmplkey) {
                $newkey = uniqid() . '_' . $this->request->context->id;
                templatekey::replace_record($this->request->tmplkey, $newkey);
            }
        }

        return self::RESULT_OK;
    }
}