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
 * This file contains the class for settings callback handlers
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\handlers\callback;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\local\http\requests\callback\settings_callback_request;
use assignsubmission_onlyoffice\templatekey;

/**
 * Settings callback handler
 */
class settings_callback_handler extends callback_handler {

    /**
     * Constructor
     *
     * @param settings_callback_request $request
     */
    public function __construct(settings_callback_request $request) {
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

        $canwrite = has_capability('moodle/course:manageactivities', $this->request->context, $this->request->callbackuser);

        if (!$canwrite) {
            return self::RESULT_ERROR;
        }

        $file = filemanager::get_template($this->request->context->id);

        if (empty($file)) {
            return self::RESULT_ERROR;
        }

        if ($this->request->callbackdata->url) {
            filemanager::write($file, $this->request->callbackdata->url);

            $initialfile = filemanager::get_initial($this->request->context->id);

            if ($initialfile === null) {
                filemanager::create_initial_from_file($file);
            } else {
                filemanager::write_to_initial_from_file($initialfile, $file);
            }

            if ($this->request->tmplkey) {
                $newkey = uniqid() . '_' . $this->request->context->id;
                templatekey::replace_record($this->request->tmplkey, $newkey);
            }
        }

        return self::RESULT_OK;
    }
}
