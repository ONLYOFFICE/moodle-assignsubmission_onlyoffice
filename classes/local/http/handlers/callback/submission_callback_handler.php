<?php

namespace assignsubmission_onlyoffice\local\http\handlers\callback;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\local\http\requests\callback\submission_callback_request;

class submission_callback_handler extends callback_handler
{
    public function __construct(submission_callback_request $request)
    {
        $this->request = $request;
    }

    public function handle_save()
    {
        $file = null;
        $canwrite = false;
        
        if ($this->request->submission) {
            $canwrite = !!$this->request->submission->groupid ? $this->request->assign->can_edit_group_submission($this->request->submission->groupid)
                : $this->request->assign->can_edit_submission($this->request->submission->userid);
        }

        if (!$canwrite) {
            return self::RESULT_ERROR;
        }

        $file = filemanager::get($this->request->context->id, $this->request->submission->id);
        if (empty($file)) {
            return self::RESULT_ERROR;
        }

        if (isset($this->request->callbackdata->url)) {
            filemanager::write($file, $this->request->callbackdata->url);
        }

        return self::RESULT_OK;
    }
}