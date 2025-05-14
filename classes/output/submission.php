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
 * This file contains the class for building editor for submissions
 *
 * @package    assignsubmission_onlyoffice
 * @subpackage
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\output;

use moodle_url;
use core\output\named_templatable;

/**
 * Submission class for editor page template
 */
class submission implements \renderable, named_templatable {
    /** @var \stdClass $data */
    private $data;

    /**
     * Construct
     *
     * @param int $contextid context identifier.
     * @param string $itemid property of the file that is submissionid.
     * @param bool $readonly readonly editor mode.
     * @param string $format format of the file.
     * @param string $templatetype template type.
     */
    public function __construct($contextid,
                                $itemid,
                                $readonly = false,
                                $format = null,
                                $templatetype = null) {

        $this->data = new \stdClass();

        $this->data->contextid = $contextid;
        $this->data->itemid = $itemid;
        $this->data->readonly = $readonly;
        $this->data->format = $format;
        $this->data->templatetype = $templatetype;
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

        $jsparams = [
            $this->data->contextid,
            $this->data->itemid,
            $this->data->readonly,
            $this->data->format,
            $this->data->templatetype,
        ];

        $documentserverurl = get_config('onlyofficeeditor', 'documentserverurl');
        $src = new moodle_url(trim($documentserverurl, '/') . "/web-apps/apps/api/documents/api.js");
        $PAGE->requires->js($src);

        $PAGE->requires->js_call_amd('assignsubmission_onlyoffice/submission', 'init', $jsparams);

        return $this->data;
    }

    /**
     * Gets the name of the mustache template used to render the data.
     *
     * @param \renderer_base $renderer renderer to be used.
     *
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'assignsubmission_onlyoffice/editor';
    }
}
