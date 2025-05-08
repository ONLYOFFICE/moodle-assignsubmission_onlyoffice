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
 * This file contains the class for settings download handlers
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\handlers\download;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\local\http\requests\download\settings_download_request;
use assignsubmission_onlyoffice\templatekey;
use moodle_exception;

/**
 * Settings download handler
 */
class settings_download_handler {

    /** @var settings_download_request $request */
    private $request;

    /**
     * Constructor
     *
     * @param settings_download_request $request
     */
    public function __construct(settings_download_request $request) {
        $this->request = $request;
    }

    /**
     * Handle download request
     *
     * @return \stored_file
     */
    public function __invoke() {
        if (templatekey::get_contextid($this->request->tmplkey) === $this->request->contextid) {
            $file = filemanager::get_template($this->request->contextid);
        }

        // Check if file exists and serve appropriate content.
        if ($file === null) {
            if (isset($this->request->format) && $this->request->format !== 'upload') {
                $withsample = $this->request->templatetype === 'custom';
                $templatepath = filemanager::get_template_path($this->request->format, $withsample);
                $templatename = pathinfo($templatepath, PATHINFO_BASENAME);

                // Return path and filename instead of sending directly.
                return [
                    'path' => $templatepath,
                    'filename' => $templatename,
                ];
            } else {
                throw new moodle_exception('invalidtemplate', 'assignsubmission_onlyoffice');
            }
        }

        return $file;
    }
}
