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
 * @module assignsubmission_onlyoffice/docsapi
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define([], function() {
    /**
     * Load the ONLYOFFICE Document Server API script.
     *
     * @param {String} documentserverurl The URL to the document server
     * @return {Promise} A promise that resolves when the API is loaded
     */
    var loadDocsApi = function(documentserverurl) {
        return new Promise(function(resolve, reject) {
            // If DocsAPI is already loaded, resolve immediately
            // eslint-disable-next-line no-undef
            if (typeof DocsAPI !== "undefined" && DocsAPI !== null) {
                // eslint-disable-next-line no-undef
                resolve(DocsAPI);
                return;
            }

            // Create script element
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.async = true;
            script.src = documentserverurl + '/web-apps/apps/api/documents/api.js';

            // Set up load and error handlers
            script.onload = function() {
                // eslint-disable-next-line no-undef
                if (typeof DocsAPI !== "undefined" && DocsAPI !== null) {
                    // eslint-disable-next-line no-undef
                    resolve(DocsAPI);
                } else {
                    reject(new Error('DocsAPI failed to load properly'));
                }
            };
            script.onerror = function() {
                reject(new Error('Failed to load DocsAPI script'));
            };

            // Add script to document
            document.head.appendChild(script);
        });
    };

    return {
        /**
         * Initialize the DocsAPI.
         *
         * @param {String} documentserverurl The URL to the document server
         * @return {Promise} A promise that resolves with the DocsAPI object
         */
        init: function(documentserverurl) {
            return loadDocsApi(documentserverurl);
        }
    };
});
