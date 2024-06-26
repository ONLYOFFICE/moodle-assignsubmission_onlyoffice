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
 * This file contains the class for filling error template
 *
 * @package    assignsubmission_onlyoffice
 * @subpackage
 * @copyright   2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\output;

/**
 * Error class template
 */
class error implements \renderable, \templatable {
    /** @var \stdClass $data */
    private $data;

    /**
     * Construct
     *
     * @param string $error error message.
     */
    public function __construct($error) {

        $this->data = new \stdClass();

        $this->data->error = $error;
    }

    /**
     * Provider data to template
     *
     * @param \renderer_base $output output parameters.
     *
     * @return stdClass
     */
    public function export_for_template($output) {
        global $PAGE;

        return $this->data;
    }
}
