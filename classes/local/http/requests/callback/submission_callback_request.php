<?php

namespace assignsubmission_onlyoffice\local\http\requests\callback;

use assign;
use moodle_exception;

class submission_callback_request extends callback_request {
    public $context;
    public $cm;
    public $course;
    public $assign;
    public $submission;
    public $callbackuser;
    public $notifyusers;
    
    public function __construct() {
        global $USER;

        parent::__construct();

        $this->collect_course_info($this->documenthash->contextid, $this->documenthash->submissionid);

        $userid = isset($this->callbackdata->users) ? $this->callbackdata->users[0] : $this->documenthash->userid;
        $this->callbackuser = \core_user::get_user($userid);

        if (!$this->callbackuser) {
            throw new moodle_exception('invaliduser', 'assignsubmission_onlyoffice');
        } else {
            $USER = $this->callbackuser;
        }

        $this->notifyusers = isset($this->documenthash->notifyusers) ? $this->documenthash->notifyusers : false;
    }

    private function collect_course_info($contextid, $submissionid)
    {
        global $DB;

        list($context, $course, $cm) = get_context_info_array($contextid);
        $assign = new assign($context, $cm, $course);
        $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);

        $this->context = $context;
        $this->cm = $cm;
        $this->course = $course;
        $this->assign = $assign;
        $this->submission = $submission;
    }
}
