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
 * This file contains the class for utils
 *
 * @package    assignsubmission_onlyoffice
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice;

use mod_onlyofficeeditor\document_service;

/**
 * Class for plugin utils
 */
class utility {

    /**
     * Get actuall form format.
     *
     * @return string format
     */
    public static function get_form_format() {
        $dsversion = document_service::get_version();
        $majorversion = stristr($dsversion, '.', true);
        $majorversion = intval($majorversion);

        $submissionformat = $majorversion < 8 ? 'oform' : 'pdf';

        return $submissionformat;
    }
}
