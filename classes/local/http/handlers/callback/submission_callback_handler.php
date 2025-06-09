<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * This file contains the class for submission callback handlers
 *
 * @package    assignsubmission_onlyoffice
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_onlyoffice\local\http\handlers\callback;

use assignsubmission_onlyoffice\filemanager;
use assignsubmission_onlyoffice\local\http\requests\callback\submission_callback_request;
use core\message\message;
use core_user;

/**
 * Submission callback handler
 */
class submission_callback_handler extends callback_handler {

    /**
     * Constructor
     *
     * @param submission_callback_request $request
     */
    public function __construct(submission_callback_request $request) {
        $this->request = $request;
    }

    /**
     * Handle save request
     *
     * @return int
     */
    public function handle_save() {
        $file = null;
        $canwrite = false;

        if ($this->request->submission) {
            $canwrite = !!$this->request->submission->groupid
                ? $this->request->assign->can_edit_group_submission($this->request->submission->groupid)
                : $this->request->assign->can_edit_submission($this->request->submission->userid);
        }

        if (!$canwrite) {
            return self::RESULT_ERROR;
        }

        $file = filemanager::get($this->request->context->id, $this->request->submission->id);
        if (empty($file)) {
            return self::RESULT_ERROR;
        }

        if (isset($this->request->callbackdata->url)) {
            filemanager::write($file, $this->request->callbackdata->url);

            if ($this->request->notifyusers) {
                $this->notify_users_about_comment();
            }
        }

        return self::RESULT_OK;
    }

    /**
     * Notify users about comment
     */
    private function notify_users_about_comment() {
        $users = $this->collect_users_for_notification();

        foreach ($users as $user) {
            $this->notify_user($user);
        }
    }

    /**
     * Notify a user about a comment
     *
     * @param stdClass $recipient The user record
     * @return void
     */
    private function notify_user($recipient) {
        global $DB;

        // Check if the user is a grader.
        $isgrader = has_capability('mod/assign:grade', $this->request->context, $recipient);

        if ($isgrader) {
            // For graders, generate a URL to the grading page.
            // Get the grade ID.
            $grade = $DB->get_record('assign_grades', [
                'assignment' => $this->request->submission->assignment,
                'userid' => $recipient->id,
            ], 'id', IGNORE_MULTIPLE);
            $gid = $grade ? $grade->id : 0;

            // URL for graders to view the submission.
            $url = new \moodle_url('/mod/assign/view.php', [
                'id' => $this->request->cm->id,
                'sid' => $this->request->submission->id,
                'gid' => $gid,
                'plugin' => 'onlyoffice',
                'action' => 'viewpluginassignsubmission',
                'returnaction' => 'grading',
            ]);
        } else {
            // For students, generate a URL to the submission view page.
            $url = new \moodle_url('/mod/assign/view.php', [
                'id' => $this->request->cm->id,
                'action' => 'view',
            ]);
        }

        $usertype = has_capability('mod/assign:grade', $this->request->context, $this->request->callbackuser)
            ? 'teacher'
            : 'student';
        $userfullname = fullname($this->request->callbackuser);
        $messagesubject = get_string('mentionsubject', 'assignsubmission_onlyoffice', ['type' => $usertype]);

        $message = new message();
        $message->component = 'assignsubmission_onlyoffice';
        $message->name = 'submissioncommentnotifier';
        $message->userfrom = core_user::get_noreply_user();
        $message->userto = $recipient;
        $message->subject = $messagesubject;
        $message->fullmessage = self::format_notification_message_text(
            $this->request->course,
            $this->request->cm,
            $url,
            $usertype,
            $userfullname,
        );
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = self::format_notification_message_html(
                $this->request->course,
                $this->request->cm,
                $url,
                $usertype,
                $userfullname,
            );
        $message->notification = 1;
        $message->contexturl = $url;
        $message->contexturlname = $this->request->cm->name;
        message_send($message);
    }

    /**
     * Collect users for notification
     *
     * @return array
     */
    private function collect_users_for_notification() {
        global $DB;

        $users = [];
        $isteacher = has_capability('mod/assign:grade', $this->request->context, $this->request->callbackuser);

        if ($isteacher) {
            // Teacher viewing submission - add students.
            if ($this->request->assign->get_instance()->teamsubmission) {
                // Team submission - add all group members.
                if (!empty($this->request->submission->groupid) && $this->request->submission->groupid != 0) {
                    $groupmembers = groups_get_members($this->request->submission->groupid, 'u.*');
                    foreach ($groupmembers as $member) {
                        if (!empty($member->email)) {
                            $users[] = $member;
                        }
                    }
                }
                // For "default" group (groupid = 0), can't track any specific users.
            } else {
                // Individual submission - add just the submitting student.
                $student = $DB->get_record('user', ['id' => $this->request->submission->userid]);
                if ($student && !empty($student->email)) {
                    $users[] = $student;
                }
            }
        } else {
            // Student viewing submission - add teachers.
            $teachers = get_enrolled_users($this->request->context, 'mod/assign:grade');
            foreach ($teachers as $teacher) {
                if (!empty($teacher->email)) {
                    $users[] = $teacher;
                }
            }
        }

        // Remove current user.
        foreach ($users as $key => $user) {
            if ($user->email === $this->request->callbackuser->email) {
                unset($users[$key]);
                break;
            }
        }

        $users = array_values($users);

        return $users;
    }

    /**
     * Format notification message text
     *
     * @param \stdClass $course
     * @param \stdClass $coursemodule
     * @param string $url
     * @param string $usertype
     * @param string $userfullname
     * @return string
     */
    private static function format_notification_message_text(
        $course,
        $coursemodule,
        $url,
        $usertype,
        $userfullname,
    ) {
        $messagebody = get_string(
            'mentionmessage',
            'assignsubmission_onlyoffice',
            ['type' => ucfirst($usertype), 'name' => $userfullname, 'document' => $coursemodule->name]
        );

        $text  = $course->shortname .
                     ' -> ' .
                     get_string('modulename', 'assign') .
                     ' -> ' .
                     $coursemodule->name . "\n";
        $text .= '---------------------------------------------------------------------' . "\n";
        $text .= $messagebody . "\n";
        $text .= get_string('mentionmessage:gotofile', 'assignsubmission_onlyoffice', ['url' => $url]) . "\n";
        $text .= "\n---------------------------------------------------------------------\n";

        return $text;
    }

    /**
     * Format notification message html
     *
     * @param \stdClass $course
     * @param \stdClass $coursemodule
     * @param string $url
     * @param string $usertype
     * @param string $userfullname
     * @return string
     */
    private static function format_notification_message_html(
        $course,
        $coursemodule,
        $url,
        $usertype,
        $userfullname,
    ) {
        global $CFG;

        $html = '<p>' .
                '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' .
                $course->shortname .
                '</a> ->' .
                '<a href="' . $CFG->wwwroot . '/mod/assign/index.php?id=' . $course->id . '">' .
                get_string('modulename', 'assign') .
                '</a> ->' .
                '<a href="' . $CFG->wwwroot . '/mod/assign/view.php?id=' . $coursemodule->id . '">' .
                $coursemodule->name .
                '</a></p>';

        $messagebody = get_string(
            'mentionmessage',
            'assignsubmission_onlyoffice',
            ['type' => ucfirst($usertype), 'name' => $userfullname, 'document' => $coursemodule->name]
        );

        $html .= '<hr/><p>' . $messagebody . '</p>';
        $html .= '<p>' .
                 get_string('mentionmessage:gotofile', 'assignsubmission_onlyoffice', ['url' => $url]) .
                 '</p><hr/>';

        return $html;
    }
}
