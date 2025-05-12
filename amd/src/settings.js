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
 * @module assignsubmission_onlyoffice/settings
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define([
    'core/notification',
    'assignsubmission_onlyoffice/repository'
], function(Notification, repository) {
    const editorId = 'onlyoffice-editor';
    let docEditor = null;

    const openEditor = function(contextid, key, format, templatetype) {
        closeEditor();
        repository.buildSettingsEditorConfig(contextid, key, format, templatetype).then(config => {
            const editorConfig = JSON.parse(config);
            // eslint-disable-next-line no-undef
            docEditor = new DocsAPI.DocEditor(editorId, editorConfig);
            return;
        }).catch(error => {
            // eslint-disable-next-line no-console
            console.error('Error building editor config:', error);
        });
    };

    const closeEditor = function() {
        if (docEditor) {
            docEditor.destroyEditor();
            docEditor = null;
        }
    };

    const generateUniqueId = () => {
        return Math.floor(Date.now() / 1000).toString(16) +
               Math.floor(Math.random() * 1000).toString(16);
    };

    return {
        init: function(contextid) {
            // eslint-disable-next-line no-undef
            if (typeof DocsAPI === "undefined" && DocsAPI === null) {
                Notification.error('Document Server is not defined!');
                return;
            }

            const selectformat = document.querySelector('select[id="id_assignsubmission_onlyoffice_format"]');
            const selecttemplatetype = document.querySelector('select[id="id_assignsubmission_onlyoffice_template_type"]');
            const enabletoggleelement = document.querySelector('input[id="id_assignsubmission_onlyoffice_enabled"]');
            const templatekeyelement = document.querySelector("input[name='assignsubmission_onlyoffice_tmplkey']");

            if (!selectformat || !selecttemplatetype) {
                enabletoggleelement.addEventListener("change", function(e) {
                    const hassubmissionalert = document.getElementById('assignsubmission_onlyoffice-hassubmissionalert');
                    if (e.currentTarget.checked) {
                        hassubmissionalert.classList.remove('hidden');
                    } else {
                        hassubmissionalert.classList.add('hidden');
                    }
                });
                return;
            }

            const originalformat = selectformat.value;
            const originaltemplatekey = templatekeyelement.value;
            const originaltemplatetype = selecttemplatetype.value;

            if (enabletoggleelement.checked && selecttemplatetype.value === 'custom') {
                openEditor(
                    contextid,
                    templatekeyelement.value,
                    selectformat.value,
                    selecttemplatetype.value
                );
            }

            enabletoggleelement.addEventListener('change', function(e) {
                if (e.currentTarget.checked
                    && selectformat.value !== 'upload'
                    && selecttemplatetype.value === 'custom'
                ) {
                    if (docEditor === null) {
                        openEditor(
                            contextid,
                            templatekeyelement.value,
                            selectformat.value,
                            selecttemplatetype.value
                        );
                    }
                }
            });

            selectformat.addEventListener('change', async function(e) {
                if (e.currentTarget.value === originalformat && selecttemplatetype.value === originaltemplatetype) {
                    templatekeyelement.value = originaltemplatekey;
                } else {
                    templatekeyelement.value = generateUniqueId();
                }

                if (e.currentTarget.value !== 'upload' && selecttemplatetype.value === 'custom') {
                    openEditor(
                        contextid,
                        templatekeyelement.value,
                        selectformat.value,
                        selecttemplatetype.value
                    );
                } else {
                    closeEditor();
                }
            });

            selecttemplatetype.addEventListener('change', function(e) {
                if (e.currentTarget.value === originalformat && selecttemplatetype.value === originaltemplatetype) {
                    templatekeyelement.value = originaltemplatekey;
                } else {
                    templatekeyelement.value = generateUniqueId();
                }

                if (e.currentTarget.value === 'custom' && selectformat.value !== 'upload') {
                    openEditor(
                        contextid,
                        templatekeyelement.value,
                        selectformat.value,
                        e.currentTarget.value
                    );
                } else {
                    closeEditor();
                }
            });
        }
    };
});
