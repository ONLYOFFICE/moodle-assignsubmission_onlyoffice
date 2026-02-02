# ONLYOFFICE Assign Submission plugin for Moodle

This plugin is an extension of the [mod_assign](https://github.com/moodle/moodle/tree/master/mod/assign) Moodle plugin and allows users to add a custom submission of the "ONLYOFFICE document" type in various formats.

## Features ✨

The plugin allows to:

* Create, edit, and fill out templates for Moodle assignments.

**Supported formats**

* For viewing and editing: PDF, DOCX, PPTX, XLSX.

<p align="center">
  <a href="https://www.onlyoffice.com/office-for-moodle?utm_source=github&utm_medium=cpc&utm_campaign=GitHubMoodleAssignSubmission">
    <img width="840" src="https://static-site.onlyoffice.com/public/images/templates/office-for-moodle/assignments/assignments-1@2x.png" alt="ONLYOFFICE Assign Submission plugin for Moodle">
  </a>
</p>

## Plugin installation ⚙️

This plugin is an **Assign submission plugin**.

Follow the standard Moodle plugin installation process and place it in the following directory:
`mod/assign/submission/onlyoffice`

For detailed steps, refer to the [Moodle Plugin Installation Guide](https://docs.moodle.org/501/en/Installing_plugins).

> ⚠️ Note: This plugin requires the main [ONLYOFFICE Plugin for Moodle](https://github.com/ONLYOFFICE/moodle-mod_onlyofficeeditor) to be installed and configured beforehand.

## Plugin configuration 🔧

No additional configuration is required. All necessary settings are managed through the [main ONLYOFFICE plugin](https://github.com/ONLYOFFICE/moodle-mod_onlyofficeeditor).

## Plugin usage 📝

Once the plugin is installed, you can select **ONLYOFFICE document** as the submission type.

### Teacher's viewpoint: Creating an assignment

- Open the necessary course page.
- Activate the **Edit Mode** using the switcher at the top right corner.
- Click **Add an activity or resource**.
- Select the **Assignment** activity in the pop-up window.
- In the **Submission types** section, select the **ONLYOFFICE document** type.

The latest action reveals two dropdown menus: **Format** (set to **Form**) and **File template** (set to **Start with empty document**), along with the **Allow students to comment inside the document after grading** checkbox.

The **File template (ONLYOFFICE)** field lets teachers decide whether to prepare the file before publishing the assignment:

- **Start with empty template:** The teacher does not edit the file. Students will see a blank document to complete the assignment. In this case, the ONLYOFFICE editors are not displayed on the assignment creation page.
- **Edit default template:** The teacher can customize the file before adding it to the course. A file template is displayed on the assignment creation page, with specific templates available for each file type (DOCX, XLSX, PPTX, PDF).

It's also possible to upload a pre-prepared file from the device. Simply select **Upload file** in the **Format (ONLYOFFICE)** field. This will open the standard Moodle file explorer, and the **File template (ONLYOFFICE)** field will be disabled.

The teacher can then choose the desired file, which will be added to the Assignment. Note that the uploaded file cannot be edited at this stage, and the ONLYOFFICE editors will not be displayed on the activity creation page.

If the teacher enables the **Allow students to comment inside the document after grading** option, students will have the ability to leave comments directly in the ONLYOFFICE document after their work has been graded.

Once everything is set, click **Save and return to course** or **Save and display** at the bottom of the page. The Assignment will then be added to the course section.

### Student's viewpoint: Completing an assignment

Students can access the assignment and click the **Add submission** button to complete it.

#### PDF

When completing an assignment, the PDF file can open in one of two modes, based on the teacher's settings during assignment creation:

- If the teacher selected **File template (ONLYOFFICE) = Edit default template**, the student will view the PDF in the form-filling mode.
- If the teacher selected **File template (ONLYOFFICE) = Start with empty template**, the student will view the PDF in the form-editing mode.

#### Document/Spreadsheet/Presentation

The corresponding editor will open with either a blank file or a pre-defined template, allowing the student to complete the task.

### Teacher's viewpoint: Checking the completed assignments

Teachers can easily review all assignments submitted by students. To do this, navigate to the **Submissions** tab on the specific assignment's page.

Here, a table displaying all submitted assignments will appear. By clicking the **View ONLYOFFICE document** magnifying glass icon, the teacher can open the page with the student's completed assignment. Teachers can also leave comments directly in the completed file if needed.

Students will be notified of any comments left by the teacher via the bell icon in the top Moodle panel. Clicking the notification will provide a quick link to the **Assignment submission** page, where the student can access the file.

On the submission page, students can view all comments left by the teacher. If the teacher enabled the **Allow students to comment inside the document after grading** option during activity creation, students can also leave their own comments or reply to the teacher's feedback. If a student adds comments to the file, the teacher will receive a notification.

## Feedback & Support 💬

If you encounter technical issues or have questions, you can reach the ONLYOFFICE team through the following channels:

- 🐞 Report bugs: [GitHub issues](https://github.com/ONLYOFFICE/moodle-assignsubmission_onlyoffice/issues)
- 💬 Forum: [ONLYOFFICE Community](https://community.onlyoffice.com/)
- 💡 Feedback and feature suggestions: [Your voice matters](https://feedback.onlyoffice.com/forums/966080-your-voice-matters)
- 👨‍💻 Need help for developers? [API documentation](https://api.onlyoffice.com?utm_source=github&utm_medium=cpc&utm_campaign=GitHubMoodleAssignSubmission) 