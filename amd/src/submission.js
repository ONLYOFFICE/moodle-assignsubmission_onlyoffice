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
    'assignsubmission_onlyoffice/fullscreen'
], function(Str, repository, docsapi, fullscreen) {
    /**
     * Calls the callback once the element is displayed.
     * The editor does not render correctly when created inside a hidden element.
     *
     * @param {HTMLElement} element The element to observe
     * @param {Function} callback The function to call
     */
    const whenVisible = function(element, callback) {
        if (element.getClientRects().length) {
            callback();
            return;
        }

        const observer = new ResizeObserver(() => {
            if (element.getClientRects().length) {
                observer.disconnect();
                callback();
            }
        });
        observer.observe(element);
    };

    const openEditor = function(container, editorid, documentserverurl, contextid, submissionid, readonly, format,
            templatetype) {
        // First ensure the DocsAPI is loaded
        // eslint-disable-next-line promise/always-return
        docsapi.init(documentserverurl).then(function(DocsAPI) {
            // eslint-disable-next-line promise/no-nesting
            repository.buildSubmissionEditorConfig(contextid, submissionid, readonly, format, templatetype).then(config => {
                // Ignores the response for an editor removed from the page.
                if (!container.isConnected) {
                    return;
                }
                const editorConfig = JSON.parse(config);
                new DocsAPI.DocEditor(editorid, editorConfig);
                fullscreen.init(container);
                return;
            }).catch(error => {
                // eslint-disable-next-line no-console
                console.error('Error building submission editor config:', error);
            });
        }).catch(function() {
            // eslint-disable-next-line promise/no-nesting
            Str.get_string('docserverunreachable', 'onlyofficeeditor')
                .then(function(string) {
                    container.innerHTML = '<div class="alert alert-danger">' + string + '</div>';
                    return;
                })
                .catch(function(error) {
                    // eslint-disable-next-line no-console
                    console.error('Error getting string:', error);
                });
            return;
        });
    };

    return {
        init: function(documentserverurl, containerid, editorid, contextid, submissionid, readonly, format, templatetype) {
            const container = document.getElementById(containerid);
            if (!container) {
                return;
            }

            whenVisible(container, function() {
                openEditor(container, editorid, documentserverurl, contextid, submissionid, readonly, format, templatetype);
            });
        }
    };
});
