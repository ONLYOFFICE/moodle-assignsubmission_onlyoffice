<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * This file contains the class for submission callback requests
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\requests\callback;

use assign;
use moodle_exception;

/**
 * Submission callback request
 */
class submission_callback_request extends callback_request {
    /** @var \context $context */
    public $context;
    /** @var \cm_info $cm */
    public $cm;
    /** @var \course $course */
    public $course;
    /** @var assign $assign */
    public $assign;
    /** @var \assign_submission $submission */
    public $submission;
    /** @var \core_user $callbackuser */
    public $callbackuser;
    /** @var bool $notifyusers */
    public $notifyusers;

    /**
     * Constructor
     */
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

    /**
     * Collect course info
     *
     * @param int $contextid
     * @param int $submissionid
     */
    private function collect_course_info($contextid, $submissionid) {
        global $DB;

        [$context, $course, $cm] = get_context_info_array($contextid);
        $assign = new assign($context, $cm, $course);
        $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);

        $this->context = $context;
        $this->cm = $cm;
        $this->course = $course;
        $this->assign = $assign;
        $this->submission = $submission;
    }
}
