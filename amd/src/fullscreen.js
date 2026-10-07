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
 * @module assignsubmission_onlyoffice/fullscreen
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define([
    'core/str',
    'core/notification'
], function(Str, Notification) {
    const fullscreenClass = 'assignsubmission-onlyoffice-fullscreen';
    const enterButtons = new WeakMap();

    let exitButton = null;
    let activeContainer = null;

    const strings = Str.get_strings([
        {key: 'editorenterfullscreen', component: 'onlyofficeeditor'},
        {key: 'editorexitfullscreen', component: 'onlyofficeeditor'},
    ]);

    const createButton = function(iconClass, stringIndex) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-primary btn-sm assignsubmission-onlyoffice-fs-button';
        button.innerHTML = '<i class="icon fa ' + iconClass + ' fa-fw" aria-hidden="true"></i>'
            + '<span class="assignsubmission-onlyoffice-fs-button-text"></span>';

        strings.then(function(texts) {
            // Provides the text as a tooltip and accessible name for the icon-only display on small screens.
            button.title = texts[stringIndex];
            button.setAttribute('aria-label', texts[stringIndex]);
            button.lastChild.textContent = texts[stringIndex];
            return;
        }).catch(Notification.exception);

        return button;
    };

    const getLayout = function() {
        const userNavigation = document.getElementById('usernavigation');
        if (userNavigation) {
            return {
                header: userNavigation.closest('.navbar'),
                footer: null,
                attachExitButton: (button) => userNavigation.prepend(button),
            };
        }

        // Uses the navigation panel and actions bar of the grader.
        const gradeActions = document.querySelector('[data-region="grade-actions"] > .d-flex');
        if (gradeActions) {
            return {
                header: document.querySelector('[data-region="grading-navigation-panel"]'),
                footer: document.querySelector('[data-region="grade-actions-panel"]'),
                attachExitButton: (button) => gradeActions.append(button),
            };
        }

        return null;
    };

    const enter = function(container) {
        exit();
        const layout = getLayout();
        container.classList.add(fullscreenClass);
        container.style.setProperty('--oo-header-height', (layout.header ? layout.header.offsetHeight : 0) + 'px');
        container.style.setProperty('--oo-footer-height', (layout.footer ? layout.footer.offsetHeight : 0) + 'px');
        activeContainer = container;
        exitButton.focus();
    };

    const exit = function() {
        if (!activeContainer) {
            return;
        }
        activeContainer.classList.remove(fullscreenClass);
        const enterButton = enterButtons.get(activeContainer);
        activeContainer = null;
        if (enterButton) {
            enterButton.focus();
        }
    };

    return {
        /**
         * Adds the fullscreen buttons for the editor container.
         *
         * @param {HTMLElement} container The editor container
         */
        init: function(container) {
            // Limits the fullscreen mode to the assignment pages and the outline complete report.
            if (!document.body.matches('.path-mod-assign, .path-report-outline')) {
                return;
            }
            const layout = getLayout();
            if (!container || !layout || enterButtons.has(container)) {
                return;
            }

            if (!exitButton) {
                exitButton = createButton('fa-compress', 1);
                exitButton.id = 'assignsubmission-onlyoffice-exit-fs-button';
                exitButton.addEventListener('click', exit);
            }
            if (!exitButton.isConnected) {
                layout.attachExitButton(exitButton);
            }

            const enterButton = createButton('fa-expand', 0);
            enterButton.classList.add('assignsubmission-onlyoffice-enter-fs-button');
            enterButton.addEventListener('click', () => enter(container));
            container.before(enterButton);
            enterButtons.set(container, enterButton);
        },

        /**
         * Removes the fullscreen button of the editor container.
         *
         * @param {HTMLElement} container The editor container
         */
        remove: function(container) {
            if (container === activeContainer) {
                exit();
            }
            const enterButton = enterButtons.get(container);
            if (enterButton) {
                enterButton.remove();
                enterButtons.delete(container);
            }
        }
    };
});
