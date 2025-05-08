<?php

namespace assignsubmission_onlyoffice\local\http\requests\download;

use assign;
use moodle_exception;

class submission_download_request extends download_request {
    public $tmplkey;
    public $contextid;
    public $context;
    public $cm;
    public $course;
    public $assign;
    public $submission;
    public $format;
    public $templatetype;
    
    public function __construct() {
        parent::__construct();

        $this->collect_course_info($this->documenthash->contextid, $this->documenthash->submissionid);

        $this->format = $this->documenthash->format ?? null;
        $this->templatetype = $this->documenthash->templatetype ?? null;
    }

    protected function collect_course_info($contextid, $submissionid)
    {
        global $DB;

        list($context, $course, $cm) = get_context_info_array($contextid);
        $assign = new assign($context, $cm, $course);

        $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);

        if (!$submission) {
            throw new moodle_exception('submissionnotfound', 'assignsubmission_onlyoffice');
        }

        $canread = !!$assign->get_instance()->teamsubmission ? 
            $assign->can_view_group_submission($submission->groupid) : 
            $assign->can_view_submission($submission->userid);

        if (!$canread) {
            throw new moodle_exception('nopermissions', 'assignsubmission_onlyoffice');
        }

        $this->context = $context;
        $this->cm = $cm;
        $this->course = $course;
        $this->assign = $assign;
        $this->submission = $submission;
    }
}
