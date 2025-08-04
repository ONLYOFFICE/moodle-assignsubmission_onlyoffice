<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The assign_submission_onlyoffice callback handler for templates
 *
 * @package    assignsubmission_onlyoffice
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use assignsubmission_onlyoffice\local\http\handlers\callback\settings_callback_handler;
use assignsubmission_onlyoffice\local\http\requests\callback\settings_callback_request;

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/../../../../../../config.php');
require_once(__DIR__ . '/../../../../locallib.php');
// phpcs:enable

try {
    $request = new settings_callback_request();
    $handler = new settings_callback_handler($request);
    $result = $handler();
} catch (\Exception $e) {
    debugging($e->getMessage());
    $result = settings_callback_handler::RESULT_ERROR;
}

http_response_code(200);
echo(json_encode(['error' => $result]));
