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
 * This file contains the class for submission download handlers
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\handlers\download;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\local\http\requests\download\submission_download_request;
use moodle_exception;

/**
 * Submission download handler
 */
class submission_download_handler {
    /** @var submission_download_request $request */
    private $request;

    /**
     * Constructor
     *
     * @param submission_download_request $request
     */
    public function __construct(submission_download_request $request) {
        $this->request = $request;
    }

    /**
     * Handle download request
     *
     * @return \stored_file
     */
    public function __invoke() {
        $file = filemanager::get($this->request->context->id, $this->request->submission->id);

        if ($file === null) {
            throw new moodle_exception('submissionfilenotfound', 'assignsubmission_onlyoffice');
        }

        return $file;
    }
}
