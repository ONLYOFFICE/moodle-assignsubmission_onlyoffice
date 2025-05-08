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
 * This file contains the class for submission download requests
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\requests\download;

use assign;
use moodle_exception;

/**
 * Submission download request
 */
class submission_download_request extends download_request {
    /** @var string $tmplkey */
    public $tmplkey;
    /** @var int $contextid */
    public $contextid;
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
    /** @var string $format */
    public $format;
    /** @var string $templatetype */
    public $templatetype;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->collect_course_info($this->documenthash->contextid, $this->documenthash->submissionid);

        $this->format = $this->documenthash->format ?? null;
        $this->templatetype = $this->documenthash->templatetype ?? null;
    }

    /**
     * Collect course info
     *
     * @param int $contextid
     * @param int $submissionid
     */
    protected function collect_course_info($contextid, $submissionid) {
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
