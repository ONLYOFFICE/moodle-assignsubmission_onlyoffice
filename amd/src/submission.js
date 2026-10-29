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
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define([
    'core/str',
    'assignsubmission_onlyoffice/repository',
    'assignsubmission_onlyoffice/docsapi',
    'assignsubmission_onlyoffice/editor'
], function(Str, repository, docsapi, Editor) {
    const showDocServerUnreachable = function(container) {
        Str.get_string('docserverunreachable', 'onlyofficeeditor')
            .then(function(string) {
                container.innerHTML = '<div class="alert alert-danger">' + string + '</div>';
                return;
            })
            .catch(function(error) {
                // eslint-disable-next-line no-console
                console.error('Error getting string:', error);
            });
    };

    return {
        init: function(documentserverurl, containerid, contextid, submissionid, readonly, format, templatetype) {
            const container = document.getElementById(containerid);
            if (!container) {
                return;
            }

            docsapi.init(documentserverurl).then(function() {
                Editor.create(
                    container,
                    repository.buildSubmissionEditorConfig(contextid, submissionid, readonly, format, templatetype)
                );
                return;
            }).catch(function() {
                showDocServerUnreachable(container);
            });
        }
    };
});
