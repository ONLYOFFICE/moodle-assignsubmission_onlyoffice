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
 * @module assignsubmission_onlyoffice/submission
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define([
    'core/notification',
    'assignsubmission_onlyoffice/repository'
], function(Notification, repository) {
    return {
        init: function(contextid, submissionid, readonly, format, templatetype) {
            // eslint-disable-next-line no-undef
            if (typeof DocsAPI === "undefined" && DocsAPI === null) {
                Notification.error('Document Server is not defined!');
                return;
            }

            repository.buildSubmissionEditorConfig(contextid, submissionid, readonly, format, templatetype).then(config => {
                const editorConfig = JSON.parse(config);
                // eslint-disable-next-line no-undef
                new DocsAPI.DocEditor('onlyoffice-editor', editorConfig);
                return;
            }).catch(error => {
                // eslint-disable-next-line no-console
                console.error('Error building submission editor config:', error);
            });
        }
    };
});
