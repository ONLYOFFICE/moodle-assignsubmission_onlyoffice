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
 * Unit tests for the filemanager helper.
 *
 * @package    assignsubmission_onlyoffice
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice;

\defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the filemanager pure-logic helpers.
 *
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class filemanager_test extends \advanced_testcase {

    /**
     * Test that generate_key concatenates contextid, itemid and timemodified into a single string.
     *
     * @dataProvider generate_key_provider
     * @param int $contextid Context id reported by the file.
     * @param int $itemid Item id reported by the file.
     * @param int $timemodified Last-modified timestamp reported by the file.
     * @param string $expected Expected concatenated key.
     */
    public function test_generate_key(int $contextid, int $itemid, int $timemodified, string $expected): void {
        $file = $this->createStub(\stored_file::class);
        $file->method('get_contextid')->willReturn($contextid);
        $file->method('get_itemid')->willReturn($itemid);
        $file->method('get_timemodified')->willReturn($timemodified);

        $this->assertSame($expected, filemanager::generate_key($file));
    }

    /**
     * Cases for {@see test_generate_key}.
     *
     * @return array
     */
    public static function generate_key_provider(): array {
        return [
            'realistic context, item and timestamp' => [
                115, 47, 1700000000, '115471700000000',
            ],
            'zero values stringify' => [
                0, 0, 0, '000',
            ],
        ];
    }

    /**
     * Test that get_template_path without a sample resolves to a path under the parent
     * mod_onlyofficeeditor module's bundled new-document directory.
     */
    public function test_get_template_path_without_sample_resolves_to_parent_module_directory(): void {
        $this->resetAfterTest();
        global $USER;
        $USER->lang = 'en';

        $path = filemanager::get_template_path('docx');

        $this->assertStringContainsString('/mod/onlyofficeeditor/newdocs/', $path);
        $this->assertStringEndsWith('/new.docx', $path);
        $this->assertFileExists($path);
    }

    /**
     * Test that get_template_path with a sample resolves to the plugin's own bundled template.
     */
    public function test_get_template_path_with_sample_resolves_to_plugin_local_template(): void {
        global $CFG;

        $this->resetAfterTest();
        global $USER;
        $USER->lang = 'en';

        $this->assertSame(
            $CFG->dirroot . '/mod/assign/submission/onlyoffice/newdocs/en-US/new.docx',
            filemanager::get_template_path('docx', true)
        );
    }

    /**
     * Test that get_path_to_template_with_sample returns the bundled English (en-US) template.
     */
    public function test_get_path_to_template_with_sample_returns_en_us_path_for_english_user(): void {
        global $CFG;

        $this->resetAfterTest();
        global $USER;
        $USER->lang = 'en';

        $this->assertSame(
            $CFG->dirroot . '/mod/assign/submission/onlyoffice/newdocs/en-US/new.pdf',
            filemanager::get_path_to_template_with_sample('pdf')
        );
    }

    /**
     * Test that get_path_to_template_with_sample throws when the bundled template is absent.
     */
    public function test_get_path_to_template_with_sample_throws_for_extension_without_bundled_template(): void {
        $this->resetAfterTest();
        global $USER;
        $USER->lang = 'en';

        $this->expectException(\file_exception::class);
        filemanager::get_path_to_template_with_sample('odp');
    }

    /**
     * Test that get returns null for an empty submission area, and the stored file after create.
     */
    public function test_get_returns_stored_file_after_create_in_submission_area(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();
        $submissionid = 47;

        $this->assertNull(filemanager::get($contextid, $submissionid));

        $created = filemanager::create($contextid, $submissionid, 'Macbeth Essay', 'docx', $userid, 'Alice Anderson');

        $fetched = filemanager::get($contextid, $submissionid);
        $this->assertInstanceOf(\stored_file::class, $fetched);
        $this->assertEquals($created->get_id(), $fetched->get_id());
        $this->assertSame('Macbeth Essay_Alice Anderson.docx', $fetched->get_filename());
    }

    /**
     * Test that create_by_initial materialises a submission file from the assignment's initial template,
     * preserving content while applying the per-submission filename.
     */
    public function test_create_by_initial_copies_initial_file_into_submission_area(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();
        $submissionid = 47;

        $template = filemanager::create_template($contextid, 'docx', $userid);
        filemanager::create_initial_from_file($template);
        $initial = filemanager::get_initial($contextid);

        $created = filemanager::create_by_initial(
            $initial,
            $submissionid,
            'Macbeth Essay',
            'docx',
            $userid,
            'Alice Anderson',
        );

        $this->assertSame('Macbeth Essay_Alice Anderson.docx', $created->get_filename());
        $this->assertSame($initial->get_contenthash(), $created->get_contenthash());
        $this->assertEquals($created->get_id(), filemanager::get($contextid, $submissionid)->get_id());
    }

    /**
     * Test that delete only removes files for the targeted submission, leaving siblings in place.
     */
    public function test_delete_removes_only_files_for_the_given_submission(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        filemanager::create($contextid, 47, 'Macbeth Essay', 'docx', $userid, 'Alice Anderson');
        filemanager::create($contextid, 48, 'Macbeth Essay', 'docx', $userid, 'Bob Brown');

        filemanager::delete($contextid, 47);

        $this->assertNull(filemanager::get($contextid, 47));
        $this->assertNotNull(filemanager::get($contextid, 48));
    }

    /**
     * Test that create_template writes the assignment template into the template area
     * and that get_template returns it back.
     */
    public function test_create_template_writes_into_template_area_and_can_be_fetched(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        $this->assertNull(filemanager::get_template($contextid));

        $created = filemanager::create_template($contextid, 'docx', $userid);

        $fetched = filemanager::get_template($contextid);
        $this->assertEquals($created->get_id(), $fetched->get_id());
        $this->assertSame('docx', strtolower(pathinfo($fetched->get_filename(), PATHINFO_EXTENSION)));
    }

    /**
     * Test that create_template with a sample uses the plugin-local newdocs template,
     * not the parent module's blank one.
     */
    public function test_create_template_with_sample_uses_plugin_local_template(): void {
        global $CFG;

        [$contextid, $userid] = $this->arrange_context_and_user();

        $created = filemanager::create_template($contextid, 'pdf', $userid, true);

        $expectedpath = $CFG->dirroot . '/mod/assign/submission/onlyoffice/newdocs/en-US/new.pdf';
        $this->assertSame(file_get_contents($expectedpath), $created->get_content());
    }

    /**
     * Test that create_template_from_uploaded_file replaces any pre-existing template
     * with the user-supplied file, sanitising its filename.
     */
    public function test_create_template_from_uploaded_file_replaces_existing_template(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        filemanager::create_template($contextid, 'docx', $userid);
        $previous = filemanager::get_template($contextid);

        $uploaded = $this->create_uploaded_file($userid, 'lecture-notes.pdf', 'Lecture notes for week 3.');

        filemanager::create_template_from_uploaded_file($contextid, $uploaded);

        $template = filemanager::get_template($contextid);
        $this->assertSame('lecture-notes.pdf', $template->get_filename());
        $this->assertNotSame($previous->get_id(), $template->get_id());
    }

    /**
     * Test that delete_template empties the template area.
     */
    public function test_delete_template_clears_template_area(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        filemanager::create_template($contextid, 'docx', $userid);

        filemanager::delete_template($contextid);

        $this->assertNull(filemanager::get_template($contextid));
    }

    /**
     * Test that create_initial_from_file copies a stored file into the initial area,
     * preserving filename and content.
     */
    public function test_create_initial_from_file_copies_into_initial_area(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        $template = filemanager::create_template($contextid, 'docx', $userid);
        $this->assertNull(filemanager::get_initial($contextid));

        filemanager::create_initial_from_file($template);

        $initial = filemanager::get_initial($contextid);
        $this->assertSame($template->get_filename(), $initial->get_filename());
        $this->assertSame($template->get_contenthash(), $initial->get_contenthash());
    }

    /**
     * Test that create_initial_from_uploaded_file replaces any pre-existing initial file.
     */
    public function test_create_initial_from_uploaded_file_replaces_existing_initial(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        $template = filemanager::create_template($contextid, 'docx', $userid);
        filemanager::create_initial_from_file($template);

        $uploaded = $this->create_uploaded_file($userid, 'lecture-notes.pdf', 'Lecture notes for week 3.');

        filemanager::create_initial_from_uploaded_file($contextid, $uploaded);

        $this->assertSame('lecture-notes.pdf', filemanager::get_initial($contextid)->get_filename());
    }

    /**
     * Test that write_to_initial_from_file replaces content in place when the source file
     * shares the initial's mime type, keeping the same stored_file row.
     */
    public function test_write_to_initial_from_file_replaces_in_place_for_same_mime_type(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        $template = filemanager::create_template($contextid, 'docx', $userid);
        filemanager::create_initial_from_file($template);
        $initialbefore = filemanager::get_initial($contextid);

        $uploaded = $this->create_uploaded_file($userid, 'updated-syllabus.docx', 'Course syllabus revision 2.');

        filemanager::write_to_initial_from_file($initialbefore, $uploaded);

        $initialafter = filemanager::get_initial($contextid);
        $this->assertSame($initialbefore->get_id(), $initialafter->get_id());
        $this->assertSame($uploaded->get_contenthash(), $initialafter->get_contenthash());
    }

    /**
     * Test that write_to_initial_from_file creates a fresh initial and discards the previous one
     * when the source file has a different mime type.
     */
    public function test_write_to_initial_from_file_recreates_for_different_mime_type(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        $docxtemplate = filemanager::create_template($contextid, 'docx', $userid);
        filemanager::create_initial_from_file($docxtemplate);
        $initialbefore = filemanager::get_initial($contextid);

        $pdfsource = filemanager::create_template($contextid, 'pdf', $userid);

        filemanager::write_to_initial_from_file($initialbefore, $pdfsource);

        $initialafter = filemanager::get_initial($contextid);
        $this->assertInstanceOf(\stored_file::class, $initialafter);
        $this->assertNotSame($initialbefore->get_id(), $initialafter->get_id());
        $this->assertSame('pdf', strtolower(pathinfo($initialafter->get_filename(), PATHINFO_EXTENSION)));
    }

    /**
     * Test that delete_initial empties the initial area.
     */
    public function test_delete_initial_clears_initial_area(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        $template = filemanager::create_template($contextid, 'docx', $userid);
        filemanager::create_initial_from_file($template);

        filemanager::delete_initial($contextid);

        $this->assertNull(filemanager::get_initial($contextid));
    }

    /**
     * Test that disallowed characters (ampersand, pipe, asterisk) are stripped from
     * the generated filename, while permitted characters (spaces, underscore, period) survive.
     */
    public function test_create_strips_disallowed_characters_from_filename(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        $created = filemanager::create($contextid, 47, 'Q&A | Discussion *', 'docx', $userid, 'Alice Anderson');

        $this->assertSame('QA  Discussion _Alice Anderson.docx', $created->get_filename());
    }

    /**
     * Test that filenames longer than FILENAME_MAXIMUM_LENGTH are truncated by shortening
     * the name portion while preserving the suffix and extension.
     */
    public function test_create_truncates_filename_to_filename_max_length(): void {
        [$contextid, $userid] = $this->arrange_context_and_user();

        $longname = str_repeat('Final thesis on Macbeth ', 14);

        $created = filemanager::create($contextid, 47, $longname, 'docx', $userid, 'Alice Anderson');

        $this->assertSame(filemanager::FILENAME_MAXIMUM_LENGTH, strlen($created->get_filename()));
        $this->assertStringEndsWith('_Alice Anderson.docx', $created->get_filename());
    }

    /**
     * Set up a course context and a logged-in English-language user, and request the test
     * harness to reset state on tear-down.
     *
     * @return array [course context id, user id].
     */
    private function arrange_context_and_user(): array {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user(['lang' => 'en']);
        $this->setUser($user);

        return [\context_course::instance($course->id)->id, (int) $user->id];
    }

    /**
     * Create a stored_file in the given user's draft area, simulating a user upload.
     *
     * @param int $userid The owning user.
     * @param string $filename Filename including extension.
     * @param string $content File body.
     * @return \stored_file
     */
    private function create_uploaded_file(int $userid, string $filename, string $content): \stored_file {
        return get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($userid)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => file_get_unused_draft_itemid(),
            'filepath' => '/',
            'filename' => $filename,
        ], $content);
    }
}
