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
 * Repository to perform WS calls for assignsubmission_onlyoffice.
 *
 * @module     assignsubmission_onlyoffice/repository
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';

export const buildSettingsEditorConfig = (contextid, key, format, templatetype) => {
    const args = {
        contextid,
        key,
        format,
        templatetype
    };

    return fetchMany([{methodname: 'assignsubmission_onlyoffice_build_settings_editor_config', args}])[0];
};

export const buildSubmissionEditorConfig = (contextid, submissionid, readonly, format, templatetype) => {
    const args = {
        contextid,
        submissionid,
        readonly,
        format,
        templatetype
    };

    return fetchMany([{methodname: 'assignsubmission_onlyoffice_build_submission_editor_config', args}])[0];
};
