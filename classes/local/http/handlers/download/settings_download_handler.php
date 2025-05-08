<?php

namespace assignsubmission_onlyoffice\local\http\handlers\download;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\local\http\requests\download\settings_download_request;
use assignsubmission_onlyoffice\templatekey;
use moodle_exception;

class settings_download_handler {

    private $request;

    public function __construct(settings_download_request $request) {
        $this->request = $request;
    }
    
    public function __invoke()
    {
        if (templatekey::get_contextid($this->request->tmplkey) === $this->request->contextid) {
            $file = filemanager::get_template($this->request->contextid);
        }

        // Check if file exists and serve appropriate content
        if ($file === null) {
            if (isset($this->request->format) && $this->request->format !== 'upload') {
                $withsample = $this->request->templatetype === 'custom';
                $templatepath = filemanager::get_template_path($this->request->format, $withsample);
                $templatename = pathinfo($templatepath, PATHINFO_BASENAME);

                // Return path and filename instead of sending directly
                return [
                    'path' => $templatepath,
                    'filename' => $templatename
                ];
            } else {
                throw new moodle_exception('invalidtemplate', 'assignsubmission_onlyoffice');
            }
        }

        return $file;
    }
}
    

    
