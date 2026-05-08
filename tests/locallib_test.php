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
 * Unit tests for the assign_submission_onlyoffice plugin class.
 *
 * @package    assignsubmission_onlyoffice
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice;

use mod_assign_test_generator;

\defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/tests/generator.php');

/**
 * Tests for the assign_submission_onlyoffice plugin class.
 *
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class locallib_test extends \advanced_testcase {

    use mod_assign_test_generator;

    /** @var \mod_assign_testable_assign */
    private $assign;

    /** @var \stdClass */
    private $student;

    /** @var \assign_submission_onlyoffice */
    private $plugin;

    /** @var \stdClass */
    private $submission;

    /** @var int */
    private $contextid;

    /**
     * Stand up an assignment with the onlyoffice submission plugin enabled and a single
     * enrolled student.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->assign = $this->create_instance($course, [
            'assignsubmission_onlyoffice_enabled' => 1,
            'assignsubmission_onlyoffice_format' => 'docx',
            'assignsubmission_onlyoffice_template_type' => 'empty',
            'assignsubmission_onlyoffice_enablecomment' => 0,
            'assignsubmission_onlyoffice_tmplkey' => '67f3a9b2c1d4e',
        ]);
        $this->plugin = $this->assign->get_submission_plugin_by_type('onlyoffice');
        $this->submission = $this->assign->get_user_submission($this->student->id, true);
        $this->contextid = $this->assign->get_context()->id;
    }

    /**
     * Test that is_empty reports true when the submission area holds no file.
     */
    public function test_is_empty_returns_true_when_no_submission_file_exists(): void {
        $this->assertTrue($this->plugin->is_empty($this->submission));
    }

    /**
     * Test that is_empty reports false once a submission file lands in the area.
     */
    public function test_is_empty_returns_false_when_submission_file_exists(): void {
        $this->stage_submission_file('Macbeth Essay_Alice Anderson.docx', 'Final draft of the essay body.');

        $this->assertFalse($this->plugin->is_empty($this->submission));
    }

    /**
     * Test that get_files returns an empty array when no submission file exists.
     */
    public function test_get_files_returns_empty_array_when_no_submission_file_exists(): void {
        $this->assertSame([], $this->plugin->get_files($this->submission, $this->student));
    }

    /**
     * Test that get_files returns the submission stored_file keyed by its filename.
     */
    public function test_get_files_returns_stored_file_keyed_by_filename_when_present(): void {
        $filename = 'Macbeth Essay_Alice Anderson.docx';

        $this->stage_submission_file($filename, 'Final draft of the essay body.');

        $files = $this->plugin->get_files($this->submission, $this->student);
        $this->assertCount(1, $files);
        $this->assertArrayHasKey($filename, $files);
        $this->assertSame($filename, $files[$filename]->get_filename());
    }

    /**
     * Drop a stored_file directly into the plugin's submission area for the test's submission.
     *
     * @param string $filename Filename to register against the submission.
     * @param string $content File body.
     * @return \stored_file
     */
    private function stage_submission_file(string $filename, string $content): \stored_file {
        return get_file_storage()->create_file_from_string([
            'contextid' => $this->contextid,
            'component' => filemanager::COMPONENT_NAME,
            'filearea' => filemanager::FILEAREA_ONLYOFFICE_SUBMISSION_FILE,
            'itemid' => $this->submission->id,
            'filepath' => '/',
            'filename' => $filename,
        ], $content);
    }
}
