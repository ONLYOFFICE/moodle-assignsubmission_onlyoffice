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
 * This file contains the class for submission callback handlers
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\handlers\callback;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\local\http\requests\callback\submission_callback_request;

/**
 * Submission callback handler
 */
class submission_callback_handler extends callback_handler {

    /**
     * Constructor
     *
     * @param submission_callback_request $request
     */
    public function __construct(submission_callback_request $request) {
        $this->request = $request;
    }

    /**
     * Handle save request
     *
     * @return int
     */
    public function handle_save() {
        $file = null;
        $canwrite = false;

        if ($this->request->submission) {
            $canwrite = !!$this->request->submission->groupid
                ? $this->request->assign->can_edit_group_submission($this->request->submission->groupid)
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
