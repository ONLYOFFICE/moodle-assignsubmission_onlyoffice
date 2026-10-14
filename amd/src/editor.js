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
 * Lifecycle of an ONLYOFFICE editor in its container.
 *
 * @module     assignsubmission_onlyoffice/editor
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Fullscreen from 'mod_onlyofficeeditor/fullscreen';

/**
 * Creates the editor once its config is loaded and the container is displayed.
 * DocsAPI must be loaded before the call.
 *
 * @param {HTMLElement} container Parent element of the editor placeholder
 * @param {Promise<string>} config Promise resolving with the editor config as JSON
 * @returns {{destroy: Function}} Handle that removes the editor or cancels its creation
 */
export const create = (container, config) => {
    let destroyed = false;
    let observer = null;
    let docEditor = null;
    let fullscreen = null;

    const isDisplayed = () => container.getClientRects().length > 0;

    const displayed = () => new Promise((resolve) => {
        if (isDisplayed()) {
            resolve();
            return;
        }
        observer = new ResizeObserver(() => {
            if (isDisplayed()) {
                observer.disconnect();
                observer = null;
                resolve();
            }
        });
        observer.observe(container);
    });

    const build = async() => {
        const json = await config;
        if (destroyed) {
            return;
        }
        const editorConfig = JSON.parse(json);
        await displayed();
        // eslint-disable-next-line no-undef
        docEditor = new DocsAPI.DocEditor(container.id + '-editor', editorConfig);
        fullscreen = Fullscreen.init(container, editorConfig.documentType);
    };

    build().catch((error) => {
        // eslint-disable-next-line no-console
        console.error('Error creating editor:', error);
    });

    return {
        destroy: () => {
            destroyed = true;
            if (observer) {
                observer.disconnect();
                observer = null;
            }
            if (fullscreen) {
                fullscreen.destroy();
                fullscreen = null;
            }
            if (docEditor) {
                docEditor.destroyEditor();
                docEditor = null;
            }
        },
    };
};
