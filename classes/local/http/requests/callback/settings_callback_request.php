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
 * This file contains the class for settings callback requests
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\requests\callback;

use assign;
use assignsubmission_onlyoffice\templatekey;
use moodle_exception;

/**
 * Settings callback request
 */
class settings_callback_request extends callback_request {
    /** @var string $tmplkey */
    public $tmplkey;
    /** @var \context $context */
    public $context;
    /** @var \cm_info $cm */
    public $cm;
    /** @var \course $course */
    public $course;
    /** @var assign $assign */
    public $assign;
    /** @var array $assignconfig */
    public $assignconfig;
    /** @var \core_user $callbackuser */
    public $callbackuser;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->tmplkey = $this->documenthash->tmplkey;

        $contextid = $this->documenthash->contextid === 0
            ? templatekey::get_contextid($this->tmplkey)
            : $this->documenthash->contextid;

        if ($contextid !== 0) {
            $this->collect_course_info($contextid);
        }

        // Get current user.
        $userid = isset($this->callbackdata->users) ? $this->callbackdata->users[0] : $this->documenthash->userid;
        $this->callbackuser = \core_user::get_user($userid);

        if (!$this->callbackuser) {
            throw new moodle_exception('invaliduser', 'assignsubmission_onlyoffice');
        }
    }

    /**
     * Collect course info
     *
     * @param int $contextid
     */
    private function collect_course_info($contextid) {
        [$context, $course, $cm] = get_context_info_array($contextid);
        $assign = new assign($context, $cm, $course);
        $plugin = $assign->get_submission_plugin_by_type('onlyoffice');

        $this->context = $context;
        $this->cm = $cm;
        $this->course = $course;
        $this->assign = $assign;
        $this->assignconfig = $plugin->get_config();
    }
}
