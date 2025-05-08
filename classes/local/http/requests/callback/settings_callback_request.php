<?php

namespace assignsubmission_onlyoffice\local\http\requests\callback;

use assign;
use assignsubmission_onlyoffice\templatekey;
use moodle_exception;

class settings_callback_request extends callback_request {
    public $tmplkey;
    public $context;
    public $cm;
    public $course;
    public $assign;
    public $assignconfig;
    public $callbackuser;
    
    public function __construct() {
        parent::__construct();
        $this->tmplkey = $this->documenthash->tmplkey;

        $contextid = $this->documenthash->contextid === 0 ? templatekey::get_contextid($this->tmplkey) : $this->documenthash->contextid;

        if ($contextid !== 0) {
            $this->collect_course_info($contextid);
        }

        // Get current user
        $userid = isset($this->callbackdata->users) ? $this->callbackdata->users[0] : $this->documenthash->userid;
        $this->callbackuser = \core_user::get_user($userid);

        if (!$this->callbackuser) {
            throw new moodle_exception('invaliduser', 'assignsubmission_onlyoffice');
        }
    }

    private function collect_course_info($contextid)
    {
        list($context, $course, $cm) = get_context_info_array($contextid);
        $assign = new assign($context, $cm, $course);
        $plugin = $assign->get_submission_plugin_by_type('onlyoffice');

        $this->context = $context;
        $this->cm = $cm;
        $this->course = $course;
        $this->assign = $assign;
        $this->assignconfig = $plugin->get_config();
    }
}
