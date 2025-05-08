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
 * This file contains the class for callback handlers
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\handlers\callback;

use mod_onlyofficeeditor\util;

/**
 * Callback handler
 */
abstract class callback_handler {
    /**
     * OK result code
     */
    const RESULT_OK = 0;
    /**
     * Error result code
     */
    const RESULT_ERROR = 1;

    /** @var callback_request */
    public $request;

    /**
     * Handle callback
     *
     * @return int
     */
    public function __invoke() {
        $result = self::RESULT_ERROR;

        switch ($this->request->callbackdata->status) {
            case util::STATUS_MUSTSAVE:
            case util::STATUS_ERRORSAVING:
            case util::STATUS_CLOSEDNOCHANGES:
                $result = $this->handle_save();
                break;

            case util::STATUS_EDITING:
                $result = self::RESULT_OK;
                break;
        }

        return $result;
    }

    /**
     * Handle save request
     *
     * @return int
     */
    abstract public function handle_save();
}
