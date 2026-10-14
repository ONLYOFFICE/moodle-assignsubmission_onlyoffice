# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`assignsubmission_onlyoffice` is a **mod_assign submission subplugin** that adds an "ONLYOFFICE document" submission type. When setting up an assignment, a teacher picks a document format or uploads a file, and can optionally edit a template. Each student or group then edits a personal copy in an embedded ONLYOFFICE Docs editor. Graders open that copy read-only or comment-only.

The code assumes it is installed at `mod/assign/submission/onlyoffice/`. Entry-point scripts include `config.php` by relative path, and both template paths and the URLs sent to Document Server are built from that location.

It is **not standalone**: `version.php` declares a minimum required version of `mod_onlyofficeeditor`. It reuses that plugin's request hashing, JWT handling, URL configuration, conversion service, file-format lists, blank templates, lang strings and AMD modules, so check the parent plugin before reimplementing any of these. The parent plugin has its own CLAUDE.md, which covers the shared auth and URL mechanisms in depth. Read it before changing anything auth-related here.

Release notes go in `CHANGELOG.md`. The artifact workflow takes the package version from the first version number in that file, so the top entry must be the release being built.

## Commands

CI (`.github/workflows/`) runs these moodle-plugin-ci checks. The Moodle branch, PHP and database matrix is defined there:

```bash
moodle-plugin-ci phplint
moodle-plugin-ci phpcs --max-warnings 0     # zero-warning gate
moodle-plugin-ci phpdoc --max-warnings 0    # zero-warning gate: full docblocks + GPL header on every file
moodle-plugin-ci validate
moodle-plugin-ci savepoints
moodle-plugin-ci mustache
moodle-plugin-ci grunt --max-lint-warnings 0
moodle-plugin-ci phpunit --fail-on-warning
moodle-plugin-ci behat --profile chrome
```

Server-to-server entry points intentionally skip `require_login`. They silence the phpcs rule with a `phpcs:ignore` … `phpcs:enable` pair, and new endpoints should do the same.

**AMD build.** The built `amd/build/` files are committed. Rebuild them with Moodle's `grunt amd`, run from the Moodle root with `--root` pointing at this plugin (prefix the path with `public/` on Moodle 5.1+):

```bash
npx grunt amd --root=mod/assign/submission/onlyoffice
```

Write new modules as ES modules (`import`/`export`), even where existing modules still use legacy `define()` AMD.

Moodle applies changes under `db/` only after `$plugin->version` increases.

## Architecture

### Lifecycle via `assign_submission_plugin` hooks

The integration with mod_assign is the `assign_submission_plugin` subclass in `locallib.php`:

- **Settings hooks:** build and save the teacher's form section. Once any submission has been submitted, the format and template choices are frozen.
- **Submission form hook:** creates the student's or group's file the first time, then renders the editor.
- **View hooks:** render the submitted file outside the edit form. The editor opens as view-only or comment-only (see *Permissions* below).

The plugin has **no DB tables**. Per-assignment state is stored with `set_config()` in `assign_plugin_config`. Legacy values from older releases can still be there, so code reading these settings must keep handling them.

### File storage

The plugin's file manager class owns several file areas in the assignment's module context:

- **Template:** the file the teacher edits.
- **Initial snapshot:** the copy that student files are cloned from.
- **Submission:** one file per submission.
- **Draft:** a scratch area used only while writing new content.

Two invariants:

- **A submission file's Document Server key is derived from its modification time.** Write new content through the file manager's write path, which replaces the file and bumps that time. Any other write path leaves Document Server serving its cached copy. The settings editor is keyed differently (see *Template keys* below).
- **Template edits reset student work.** If the initial snapshot is newer than a student's file, the submission form deletes the student's file and clones it again. This is how teacher edits reach students, and it is destructive by design.

A new persistent file area must be registered in the plugin's file-area list, in backup/restore (`backup/moodle2/`), and in the privacy provider's delete paths. Add it to all of them in the same change.

### Template keys: editing before the assignment exists

A teacher can edit a template while the assignment is still being created, before it has a context. The plugin's template-key class handles this:

1. The settings form carries a random template key in a hidden field, and the settings editor uses it as its Document Server key. The settings JavaScript generates a new key whenever the format or template type changes from the saved values.
2. On save, the key is stored in `assign_plugin_config` combined with the new context id. For non-upload formats, if the submitted key doesn't belong to this context, the stored template and snapshot are discarded and recreated. For uploads, a newly uploaded file replaces both.
3. Document Server sends its save callback some time *after* the editor closes, which is normally after the form has been submitted. When the callback's hash has no context, the settings callback finds the context by looking up the stored key. It then writes the template, syncs the snapshot, and **rotates** the key so the next editing session starts with a fresh one.

The settings download endpoint falls back to a template on disk when no stored template matches the key.

### Two parties, two kinds of endpoint

The **browser** gets its editor config from session-authenticated web services declared in `db/services.php`, called through the AMD repository module. The config is returned as a **JSON string**, signed as a whole with JWT when a secret is set, and parsed client-side.

**Document Server** has no Moodle session. It connects through the scripts under `api/`, which come in download and callback pairs, one pair for the settings editor and one for submissions. Callbacks always answer HTTP 200 with a `{"error": …}` body.

Each script is thin. It builds a *request* object and hands it to a *handler*, both under `classes/local/http/`:

- **Request classes** decode the `?doc=` hash, verify the JWT (on callbacks, a decoded JWT payload **replaces** the request body), and resolve the Moodle objects and the acting user. Download requests check read access themselves.
- **Handlers** do the work. Callback handlers check write access before saving. The callback base handler dispatches on Document Server's `status`, and only final-save statuses write content.

Who becomes the global `$USER` differs between endpoints, so check the specific request class before relying on it.

Each request class reads its own set of fields from the hash. When code mints a URL for an endpoint, its hash payload must match what that endpoint's request class reads.

### Permissions and comment notifications

The submission editor-config service decides each user's mode:

- Users who can edit the submission get full edit, unless the editor was opened read-only or the file type isn't editable. PDF forms built from a custom template are limited to form filling.
- Graders, and students when commenting is enabled, get comment-only mode, and their callback hash is flagged to notify users.
- Everyone else gets view-only.

When that flag is set, the submission callback handler sends a message (provider in `db/messages.php`) after saving. A teacher's comment notifies the student or the group; a student's comment notifies the graders.

### Legacy form formats

Older releases stored forms in formats that Document Server has since replaced. The settings form maps those legacy values onto the current choices, and the submission form asks the parent plugin's conversion service to convert existing files to the form format the connected Document Server version supports. Verify that conversion path end to end before relying on it.

### Frontend

- The Docs API script is loaded once per page, through a shared promise.
- The editor wrapper waits until its container is actually visible before constructing the editor, because the settings editor can sit in a hidden or collapsed part of the form.
- Renderables in `classes/output/` pass the Document Server URL and context into the AMD `init` calls and render a shared Mustache template.

## Conventions

- Editor config keys follow the ONLYOFFICE API's camelCase (`editorConfig`, `callbackUrl`, `permissions`). All other PHP uses Moodle's lowercase-no-underscore style.
- Only `lang/en/` is authoritative. Strings shared with the parent plugin are read from the `onlyofficeeditor` component, not this one.
- Every source file carries the Moodle GPL header. PHP and JS files also carry the `@package` / `@copyright` / `@license` docblock, and Mustache templates use Moodle's `@template` doc comment instead. Copy both from a neighbouring file of the same type. `3rd-Party.license` exists at the root and in `licenses/`, so keep both copies identical.
