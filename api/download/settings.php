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

use assignsubmission_onlyoffice\local\http\handlers\download\settings_download_handler;
use assignsubmission_onlyoffice\local\http\requests\download\settings_download_request;

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__.'/../../../../../../config.php');
require_once(__DIR__.'/../../../../locallib.php');
// phpcs:enable

try {
    $request = new settings_download_request();
    $handler = new settings_download_handler($request);
    $result = $handler();

    if (is_array($result)) {
        // Handle file path and filename.
        send_file($result['path'], $result['filename'], 0, 0, false, false, '', false, []);
    } else {
        // Handle stored_file object.
        send_stored_file($result);
    }
} catch (\Exception $e) {
    debugging($e->getMessage());
}
