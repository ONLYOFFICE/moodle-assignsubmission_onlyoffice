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
 * Web service function declarations for the assignsubmission_onlyoffice plugin.
 *
 * @package     assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [
    'assignsubmission_onlyoffice_build_settings_editor_config' => [
        'capabilities' => 'moodle/course:manageactivities',
        'classname'   => 'assignsubmission_onlyoffice\external\settings\build_editor_config',
        'description' => 'Builds editor config for assign settings.',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'assignsubmission_onlyoffice_build_submission_editor_config' => [
        'capabilities' => 'moodle/assign:submit',
        'classname'   => 'assignsubmission_onlyoffice\external\submissions\build_editor_config',
        'description' => 'Builds editor config for submissions.',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
];
