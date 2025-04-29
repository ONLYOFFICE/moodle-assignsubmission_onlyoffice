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
 * This file contains the class for management temporary template key
 *
 * @package    assignsubmission_onlyoffice
 * @subpackage
 * @copyright   2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice;

/**
 * Class wrapper for management temporary template key
 */
class templatekey {

    /**
     * Get contextid by tmplkey
     *
     * @param string $tmplkey temporary template key.
     *
     * @return int
     */
    public static function get_contextid($tmplkey) {
        $record = self::get_record($tmplkey);
        if (!$record) {
            return 0;
        }

        $contextid = 0;
        list($origintmplkey, $contextid) = self::parse_contextid($record->value);
        if ($origintmplkey !== $tmplkey) {
            return 0;
        }

        return $contextid;
    }

    /**
     * Get contextid by tmplkey
     *
     * @param string $fulltmplkey full temporary template key that contains contextid.
     *
     * @return array
     */
    public static function parse_contextid($fulltmplkey) {
        $valuestmplkey = explode('_', $fulltmplkey);
        if (count($valuestmplkey) !== 2) {
            return [null, -1];
        }

        $origintmplkey = $valuestmplkey[0];
        $contextid = intval($valuestmplkey[1]);

        return [$origintmplkey, $contextid];
    }

    /**
     * Get record from plugin config
     *
     * @param string $tmplkey temporary template key.
     *
     * @return mixed
     */
    private static function get_record($tmplkey) {
        global $DB;

        $sql = "SELECT * FROM {assign_plugin_config} WHERE plugin = 'onlyoffice' AND name = 'tmplkey' AND value LIKE :tmplkey";

        return $DB->get_record_sql($sql, ['tmplkey' => $tmplkey . '%']);
    }

    /**
     * Replace record from plugin config
     *
     * @param string $originalkey original template key.
     * @param string $newkey new template key.
     *
     * @return mixed
     */
    public static function replace_record($originalkey, $newkey) {
        global $DB;

        $sql = "UPDATE {assign_plugin_config} SET value = :newkey WHERE plugin = 'onlyoffice' AND name = 'tmplkey' AND value LIKE :originalkey";

        return $DB->execute($sql, ['newkey' => $newkey, 'originalkey' => $originalkey . '%']);
    }
}
