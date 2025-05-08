<?php

namespace assignsubmission_onlyoffice\local\http\requests\download;

use moodle_exception;

class settings_download_request extends download_request {
    public $tmplkey;
    public $contextid;
    public $context;
    public $cm;
    public $course;
    public $format;
    public $templatetype;
    
    public function __construct() {
        parent::__construct();
        $this->tmplkey = $this->documenthash->tmplkey;

        if ($this->documenthash->contextid !== 0) {
            $this->collect_course_info($this->documenthash->contextid);
        }

        $this->contextid = $this->documenthash->contextid;

        $this->format = $this->documenthash->format ?? null;
        $this->templatetype = $this->documenthash->templatetype ?? null;
    }

    protected function collect_course_info($contextid)
    {
        list($context, $course, $cm) = get_context_info_array($contextid);

        $canread = !empty($context) ? has_capability('moodle/course:manageactivities', $context) : true;

        if (!$canread) {
            throw new moodle_exception('nopermissions', 'assignsubmission_onlyoffice');
        }

        $this->context = $context;
        $this->cm = $cm;
        $this->course = $course;
    }
}
