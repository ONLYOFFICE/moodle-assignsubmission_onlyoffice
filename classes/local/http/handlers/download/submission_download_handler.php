<?php

namespace assignsubmission_onlyoffice\local\http\handlers\download;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\local\http\requests\download\submission_download_request;
use moodle_exception;

class submission_download_handler {

    private $request;

    public function __construct(submission_download_request $request) {
        $this->request = $request;
    }
    
    public function __invoke()
    {
        $file = filemanager::get($this->request->context->id, $this->request->submission->id);

        if ($file === null) {
            throw new moodle_exception('submissionfilenotfound', 'assignsubmission_onlyoffice');
        }

        return $file;
    }
}
