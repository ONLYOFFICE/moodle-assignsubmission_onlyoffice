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
 * This file contains the class for restore of this submission plugin
 *
 * @package assignsubmission_onlyoffice
 * @copyright 2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use assignsubmission_onlyoffice\filemanager;

/**
 * Backup class for onlyoffice submission plugin extending restore subplugin base class
 */
class restore_assignsubmission_onlyoffice_subplugin extends restore_subplugin {
    /**
     * Returns the paths to be handled by the subplugin at workshop level
     * @return array
     */
    protected function define_submission_subplugin_structure() {

        $paths = [];

        $elename = $this->get_namefor('submission');
        $elepath = $this->get_pathfor('/submission_onlyoffice');
        // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    /**
     * Processes one submission_onlyoffice element
     * @param mixed $data
     * @return void
     */
    public function process_assignsubmission_onlyoffice_submission($data) {
        $data = (object)$data;
        $data->assignmentid = $this->get_new_parentid('assign');
        $oldsubmissionid = $data->itemid;
        // The mapping is set in the restore for the core assign activity
        // when a submission node is processed.
        $data->itemid = $this->get_mappingid('submission', $data->itemid);

        $this->add_related_files(
            'assignsubmission_onlyoffice',
            filemanager::FILEAREA_ONLYOFFICE_SUBMISSION_FILE,
            'submission',
            null,
            $oldsubmissionid
        );

        $this->add_related_files(
            'assignsubmission_onlyoffice',
            filemanager::FILEAREA_ONLYOFFICE_ASSIGN_TEMPLATE,
            null
        );

        $this->add_related_files(
            'assignsubmission_onlyoffice',
            filemanager::FILEAREA_ONLYOFFICE_ASSIGN_INITIAL,
            null
        );
    }
}
