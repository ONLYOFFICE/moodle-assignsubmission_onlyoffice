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
 * Unit tests for the templatekey helper.
 *
 * @package    assignsubmission_onlyoffice
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice;

\defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the templatekey helper.
 *
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class templatekey_test extends \advanced_testcase {

    /**
     * Test that parse_contextid splits a well-formed key into its origin and integer context id,
     * and returns the [null, -1] sentinel for any malformed input.
     *
     * @dataProvider parse_contextid_provider
     * @param string $fulltmplkey Composite template key.
     * @param array $expected Expected [origin, contextid] tuple.
     */
    public function test_parse_contextid(string $fulltmplkey, array $expected): void {
        $this->assertSame($expected, templatekey::parse_contextid($fulltmplkey));
    }

    /**
     * Cases for {@see test_parse_contextid}.
     *
     * @return array
     */
    public static function parse_contextid_provider(): array {
        return [
            'uniqid origin with positive context id' => [
                '67f3a9b2c1d4e_115',
                ['67f3a9b2c1d4e', 115],
            ],
            'empty origin before separator' => [
                '_115',
                ['', 115],
            ],
            'empty context id coerces to zero' => [
                '67f3a9b2c1d4e_',
                ['67f3a9b2c1d4e', 0],
            ],
            'non-numeric context id coerces to zero' => [
                '67f3a9b2c1d4e_notanumber',
                ['67f3a9b2c1d4e', 0],
            ],
            'negative context id is preserved' => [
                '67f3a9b2c1d4e_-115',
                ['67f3a9b2c1d4e', -115],
            ],
            'no separator returns sentinel' => [
                '67f3a9b2c1d4e',
                [null, -1],
            ],
            'empty string returns sentinel' => [
                '',
                [null, -1],
            ],
            'extra separator returns sentinel' => [
                '67f3a9b2c1d4e_115_extra',
                [null, -1],
            ],
        ];
    }

    /**
     * Test that get_contextid returns the parsed context id for a well-formed stored record.
     */
    public function test_get_contextid_returns_contextid_for_matching_record(): void {
        $this->resetAfterTest();

        $this->insert_plugin_config('67f3a9b2c1d4e_115');

        $this->assertSame(115, templatekey::get_contextid('67f3a9b2c1d4e'));
    }

    /**
     * Test that get_contextid returns zero when no template-key record matches the requested key.
     */
    public function test_get_contextid_returns_zero_when_no_record_exists(): void {
        $this->resetAfterTest();

        $this->assertSame(0, templatekey::get_contextid('67f3a9b2c1d4e'));
    }

    /**
     * Test that get_contextid returns zero when the stored record's value cannot be parsed
     * back to the requested key, guarding against stale or corrupted entries.
     */
    public function test_get_contextid_returns_zero_when_record_value_is_malformed(): void {
        $this->resetAfterTest();

        $this->insert_plugin_config('67f3a9b2c1d4e');

        $this->assertSame(0, templatekey::get_contextid('67f3a9b2c1d4e'));
    }

    /**
     * Test that replace_record swaps the stored value when the original key matches the prefix.
     */
    public function test_replace_record_updates_value_of_matching_record(): void {
        global $DB;

        $this->resetAfterTest();

        $id = $this->insert_plugin_config('67f3a9b2c1d4e_115');

        templatekey::replace_record('67f3a9b2c1d4e', '4a1c8e9f2d3b5_115');

        $this->assertSame('4a1c8e9f2d3b5_115', $DB->get_field('assign_plugin_config', 'value', ['id' => $id]));
    }

    /**
     * Test that replace_record only touches rows scoped to the onlyoffice tmplkey setting,
     * leaving other plugin-config rows that happen to share the same prefix untouched.
     */
    public function test_replace_record_only_targets_plugin_tmplkey_records(): void {
        global $DB;

        $this->resetAfterTest();

        $tmplkeyid = $this->insert_plugin_config('67f3a9b2c1d4e_115');
        $formatid = $this->insert_plugin_config('67f3a9b2c1d4e_115', ['name' => 'format']);

        templatekey::replace_record('67f3a9b2c1d4e', '4a1c8e9f2d3b5_115');

        $this->assertSame('4a1c8e9f2d3b5_115', $DB->get_field('assign_plugin_config', 'value', ['id' => $tmplkeyid]));
        $this->assertSame('67f3a9b2c1d4e_115', $DB->get_field('assign_plugin_config', 'value', ['id' => $formatid]));
    }

    /**
     * Insert a fixture row into assign_plugin_config.
     *
     * @param string $value The config value to store.
     * @param array $overrides Field overrides (plugin, subtype, name, assignment).
     * @return int Inserted record id.
     */
    private function insert_plugin_config(string $value, array $overrides = []): int {
        global $DB;

        return $DB->insert_record('assign_plugin_config', (object) array_merge([
            'assignment' => 1,
            'plugin' => 'onlyoffice',
            'subtype' => 'assignsubmission',
            'name' => 'tmplkey',
            'value' => $value,
        ], $overrides));
    }
}
