# Manual Test Plan v1.0.6

Plugin: **Sticky Notes Add-on for LearnPress**

Muc tieu: checklist test manual truoc khi release v1.0.6. Tap trung vao cac bug fix, regression va cac workflow chinh cua hoc vien, instructor va admin.

## Test Environment

- WordPress 6.0+
- PHP 7.4+
- LearnPress 4.2.0+
- Plugin version: 1.0.6
- Browser:
  - Chrome latest
  - Firefox latest
  - Safari hoac Edge neu co
- Viewport:
  - Desktop 1440px
  - Tablet 768px
  - Mobile 390px

## Test Accounts

- Admin user
- Instructor user
- Student A enrolled in Test Course 1
- Student B enrolled in Test Course 2
- Student C not enrolled

## Test Data Setup

- Course A: `Test Course 1`
- Course B: `Test Course 2`
- Lesson shared: same lesson added to both Course A and Course B if LearnPress setup supports reused lessons.
- Lesson unique A: lesson only in Course A.
- Lesson unique B: lesson only in Course B.
- At least 2 notes:
  - text note
  - highlight note
- At least 30+ students/courses with notes if possible, to test searchable filters.

## Pass Criteria

- No PHP fatal error, JS console error, or broken layout.
- No duplicate notes when editing.
- Notes are scoped by both course and lesson.
- License inactive state blocks premium UI consistently.
- Admin Student Notes works with large data filters.
- Release package excludes dev-only files.

---

# 1. Installation, Activation, Migration

## TC-001 Activate Plugin

Steps:
1. Activate `lp-sticky-notes`.
2. Open Plugins page.
3. Open LearnPress admin pages.

Expected:
- Plugin activates without fatal error.
- No unexpected admin notice except license notice if license inactive.

## TC-002 DB Table Exists

Steps:
1. Check database table `wp_learnpress_sticky_notes`.

Expected:
- Table exists.
- Columns exist: `id`, `user_id`, `course_id`, `lesson_id`, `note_type`, `highlight_text`, `position`, `content`, `created_at`, `updated_at`.

## TC-003 DB Version Option

Steps:
1. Check option `lp_sticky_notes_db_version`.

Expected:
- Option exists.
- Value is `1.0.6`.

## TC-004 DB Indexes

Steps:
1. Inspect indexes on `wp_learnpress_sticky_notes`.

Expected:
- Existing single indexes remain.
- Composite indexes exist:
  - `user_course_lesson`
  - `user_created`
  - `course_lesson`

## TC-005 Upgrade From Older Version

Steps:
1. Simulate older install without composite indexes.
2. Load site after updating plugin.
3. Recheck DB schema.

Expected:
- `maybe_update_schema()` runs.
- DB version updates.
- Indexes are added without data loss.

---

# 2. License Behavior

## TC-006 License Inactive Admin Notice

Steps:
1. Deactivate/remove license.
2. Open wp-admin dashboard.

Expected:
- License notice appears.
- Link points to `admin.php?page=mamflow-license&tab=sticky-notes`.

## TC-007 License Page UI

Steps:
1. Open LearnPress > Mamflow License > Sticky Notes.

Expected:
- Page has clear heading, status badge, form/actions.
- No broken encoding characters.
- Buttons and table spacing look consistent.

## TC-008 Activate License

Steps:
1. Enter valid license key.
2. Submit activation.

Expected:
- License becomes active.
- Status displays Active.
- Feature pages unlock.

## TC-009 Invalid License

Steps:
1. Enter invalid license key.
2. Submit activation.

Expected:
- Clear error message.
- License remains inactive.
- No PHP/JS error.

## TC-010 Manual Check License

Steps:
1. With active license, click Check status.

Expected:
- Status remains active for valid license.
- License is not marked invalid because of inconclusive API response.

## TC-011 License Does Not Check Continuously

Steps:
1. Browse admin and frontend pages repeatedly.
2. Observe that license check API is not called on every page load.

Expected:
- Feature gate uses local active status.
- License does not become invalid because checks run too frequently.

## TC-012 License Inactive Frontend

Steps:
1. Remove license.
2. Visit a LearnPress lesson as enrolled student.

Expected:
- Sticky Notes UI does not render.
- No JS console error.

## TC-013 License Inactive Profile Tab

Steps:
1. Remove license.
2. Open LearnPress profile.

Expected:
- `My Notes` tab is hidden.
- Direct access to tab does not expose notes.

## TC-014 License Inactive Admin Student Notes

Steps:
1. Remove license.
2. Open `admin.php?page=lp-student-notes`.

Expected:
- License required state appears.
- Activate License button works.
- Purchase License button points to product URL.

---

# 3. Frontend Lesson Notes

## TC-015 Sticky Notes Render On Lesson

Steps:
1. Login as enrolled Student A.
2. Open Course A lesson.

Expected:
- Sticky notes toggle/sidebar appears.
- CSS/JS load only on lesson page.

## TC-016 Sticky Notes Do Not Render For Logged Out User

Steps:
1. Logout.
2. Open lesson page.

Expected:
- Sticky Notes UI does not render.
- No AJAX request for notes.

## TC-017 Sticky Notes Do Not Render For Non-Enrolled User

Steps:
1. Login as Student C.
2. Open restricted lesson.

Expected:
- Sticky Notes UI does not render or access is blocked by LearnPress.

## TC-018 Add Text Note

Steps:
1. Open lesson as Student A.
2. Click Add Note.
3. Enter text.
4. Save.

Expected:
- Success message appears.
- Note appears in sidebar.
- One row is inserted in database with correct `user_id`, `course_id`, `lesson_id`, `note_type=text`.

## TC-018A Note Timestamp Uses Site Time

Steps:
1. Set WordPress timezone to the target site timezone, for example UTC+7 / Asia/Bangkok.
2. Open a lesson as Student A.
3. Create a new text note.
4. Immediately open LearnPress Profile > My Notes.
5. Check the same note in lesson sidebar, shortcode output, and Student Notes admin.

Expected:
- Profile date shows a fresh relative time such as seconds/minutes ago, not several hours ago.
- Sidebar, shortcode, and Student Notes admin display the date/time according to WordPress site timezone.
- Database `created_at` and `updated_at` are saved in WordPress local MySQL datetime format.

## TC-019 Add Highlight Note

Steps:
1. Select lesson text.
2. Click Add Note popup.
3. Enter note content.
4. Save.

Expected:
- Highlight preview appears before saving.
- Saved note has `note_type=highlight`.
- Highlight text is stored.

## TC-020 Empty Note Validation

Steps:
1. Open add note form.
2. Leave content empty.
3. Submit.

Expected:
- Clear validation error.
- No note is created.

## TC-021 Edit Text Note

Steps:
1. Create text note.
2. Click Edit.
3. Change content.
4. Save.

Expected:
- Existing note is updated.
- No duplicate note is created.
- Success message says note updated.

## TC-022 Edit Highlight Note

Steps:
1. Create highlight note.
2. Edit the note.
3. Change content.
4. Save.

Expected:
- Same note ID is updated.
- Highlight text remains unless intentionally changed.
- No duplicate note.

## TC-023 Delete Note

Steps:
1. Create note.
2. Click Delete.
3. Confirm.

Expected:
- Note is removed from UI.
- Note row is deleted from DB.
- Empty state appears if no notes remain.

## TC-024 Sidebar Reload After Add/Edit/Delete

Steps:
1. Add, edit, then delete notes.
2. Refresh lesson page.

Expected:
- Sidebar state matches database.
- No stale note remains.

## TC-025 AJAX Error Message

Steps:
1. Simulate AJAX failure or invalid nonce.
2. Try add/edit/delete.

Expected:
- User sees clear error message.
- Console has no uncaught JS exception.

---

# 4. Course/Lesson Scoping

## TC-026 Same Lesson In Two Courses

Steps:
1. Add same lesson to Course A and Course B.
2. As Student A, create note in Course A lesson.
3. Open Course B same lesson.

Expected:
- Course B does not show Course A note.

## TC-027 Notes Scoped By Course ID

Steps:
1. Create separate notes for same lesson in Course A and Course B.
2. Check database rows.
3. Open each course context.

Expected:
- Each context loads only notes with matching `course_id` and `lesson_id`.

## TC-028 View All Modal Grouping

Steps:
1. Create notes in Course A and Course B for same lesson.
2. Open View All Notes modal.

Expected:
- Notes are grouped by `course_id:lesson_id`.
- Lesson links point to correct course context.
- Only notes from the currently opened course are shown.

## TC-029 Shortcode With Course Filter

Steps:
1. Add `[lp_sticky_notes course_id="COURSE_A_ID"]` to a page.
2. Login as Student A.

Expected:
- Only Course A notes appear.
- Same lesson notes from Course B do not appear.

## TC-030 Shortcode With Lesson Filter

Steps:
1. Add `[lp_sticky_notes lesson_id="LESSON_ID"]`.
2. Add notes for same lesson across courses.

Expected:
- Shows notes for that lesson.
- If course scoping is needed, adding `course_id` narrows results correctly.

---

# 5. LearnPress Profile My Notes

## TC-031 Profile Tab Active License

Steps:
1. Activate license.
2. Login as Student A.
3. Open LearnPress Profile > My Notes.

Expected:
- My Notes tab appears.
- Notes list loads.

## TC-032 Profile Course Filter

Steps:
1. Open My Notes.
2. Filter by Course A.

Expected:
- Only Course A notes appear.
- Stats update based on filtered notes.

## TC-033 Profile Lesson Links

Steps:
1. In My Notes, click View Lesson.

Expected:
- Opens lesson in correct course URL.

## TC-034 Profile Delete Note

Steps:
1. Delete a note from profile.

Expected:
- Confirmation appears.
- Note is deleted.
- Empty state works if last note is removed.

## TC-035 Profile AJAX Error

Steps:
1. Simulate delete failure.

Expected:
- Clear error message appears.
- Note remains visible.

---

# 6. Shortcode

## TC-036 Basic Shortcode

Steps:
1. Add `[lp_sticky_notes]` to a page.
2. Login as Student A.

Expected:
- Current user's notes display.

## TC-037 Shortcode Limit

Steps:
1. Add `[lp_sticky_notes limit="1"]`.

Expected:
- Only one note appears.

## TC-038 Shortcode Show Course/Lesson

Steps:
1. Add `[lp_sticky_notes show_course="yes" show_lesson="yes"]`.

Expected:
- Course and lesson labels appear.
- Lesson link is correct.

## TC-039 Shortcode Other User Permission

Steps:
1. As Student A, use shortcode with `user_id` of Student B.
2. As Admin, use same shortcode.

Expected:
- Student A cannot view Student B notes.
- Admin can view them.

## TC-040 Shortcode License Inactive

Steps:
1. Remove license.
2. Open shortcode page.

Expected:
- License required message appears.
- Purchase link works.

---

# 7. Admin Student Notes

## TC-041 Admin Page Access

Steps:
1. Login as Admin.
2. Open LearnPress > Student Notes.

Expected:
- Page loads.
- Header, metrics, filters, table render correctly.

## TC-042 Student Sees Only Own Notes In Student Notes Admin

Steps:
1. Login as Student A.
2. Open `admin.php?page=lp-student-notes`.
3. Apply Course filter if Student A has notes in multiple courses.
4. Try changing `student_id` in the URL to Student B's user ID.

Expected:
- Page loads with the same Student Notes UI.
- Only Student A's notes are visible.
- Student filter search returns only Student A.
- Course filter search returns only courses where Student A has notes.
- Changing `student_id` in the URL does not expose Student B's notes.

## TC-043 Logged Out Access Blocked

Steps:
1. Logout.
2. Try `admin.php?page=lp-student-notes`.

Expected:
- Access is blocked by WordPress login/admin permissions.

## TC-044 Admin Metrics

Steps:
1. Add notes from multiple students and courses.
2. Open Student Notes.

Expected:
- Total notes count is correct.
- Students with notes count is correct.
- Courses with notes count is correct.

## TC-045 Student Filter Search

Steps:
1. Click Student filter.
2. Type part of student name or email.
3. Select a result.
4. Apply filters.

Expected:
- AJAX returns matching students.
- Selected student is preserved after reload.
- Table shows only that student's notes.

## TC-046 Course Filter Search

Steps:
1. Type part of course title in Course filter.
2. Select a result.
3. Apply filters.

Expected:
- Table shows only selected course.

## TC-047 Clear Filters

Steps:
1. Apply student/course filters.
2. Click Reset or Clear filters.

Expected:
- Filters reset to All.
- Table shows all notes.

## TC-048 Large Filter Dataset

Steps:
1. Create many students/courses with notes.
2. Open Student Notes.

Expected:
- Page does not render a huge select list.
- Initial options are capped.
- Search still finds non-initial records.

## TC-049 Current Page Search

Steps:
1. Type a keyword in Search current page.

Expected:
- Visible rows filter immediately.
- Expanded note rows are hidden if parent row no longer matches.

## TC-050 Table Sorting

Steps:
1. Click sortable headers: Student, Course, Lesson, Type, Created.

Expected:
- Rows sort ascending/descending.
- Expanded note detail rows stay attached to parent note.

## TC-051 View Full Note

Steps:
1. Click View full on a row.
2. Click another View full.

Expected:
- Detail expands.
- Only one note detail is expanded at a time.

## TC-052 Admin Table Layout

Steps:
1. Inspect table header/body.
2. Check first and last columns.

Expected:
- Header cells have enough padding.
- First column is not flush against table border.
- Rows are compact but readable.

## TC-053 Admin Responsive

Steps:
1. Resize to tablet/mobile.

Expected:
- Filters stack cleanly.
- Table scrolls horizontally if needed.
- No overlap between controls.

---

# 8. Settings And Assets

## TC-054 Settings License Gate

Steps:
1. Remove license.
2. Open LearnPress settings > Sticky Notes.

Expected:
- Only license-required notice appears.
- Activate/Purchase buttons work.

## TC-055 General Settings

Steps:
1. Activate license.
2. Toggle Enable Sticky Notes off.
3. Visit lesson.

Expected:
- Sticky Notes UI does not render when disabled.

## TC-056 Highlight Setting

Steps:
1. Disable text highlighting.
2. Select lesson text.

Expected:
- Highlight popup does not appear.

## TC-057 Appearance Settings

Steps:
1. Change colors and button/sidebar settings.
2. Visit lesson.

Expected:
- CSS variables reflect settings.
- UI updates without breaking layout.

## TC-058 Asset Loading Lesson Only

Steps:
1. Visit non-LearnPress page.
2. Visit LearnPress lesson.

Expected:
- Sticky Notes frontend CSS/JS loads only on lesson pages where feature should render.

## TC-059 Asset Loading Profile Only

Steps:
1. Visit Profile overview tab.
2. Visit My Notes tab.

Expected:
- Profile notes CSS/JS only loads on My Notes tab.

## TC-060 Admin Asset Cache Busting

Steps:
1. Change admin CSS.
2. Reload Student Notes.
3. Inspect asset URL version.

Expected:
- Version includes plugin version and `filemtime`.
- Browser does not use stale CSS/JS.

---

# 9. Security And Permissions

## TC-061 AJAX Nonce Required

Steps:
1. Send add/update/delete AJAX without nonce.

Expected:
- Request fails.
- No data changes.

## TC-062 User Cannot Update Another User Note

Steps:
1. Create note as Student A.
2. Try update/delete note as Student B.

Expected:
- Request denied.
- Note remains unchanged.

## TC-063 Course Access Required

Steps:
1. Try add/get note for a course user cannot access.

Expected:
- Request denied with clear message.

## TC-064 Admin Filter AJAX Permission

Steps:
1. Call admin filter AJAX as unauthorized user.

Expected:
- Request returns 403.

## TC-065 Admin Filter AJAX License Gate

Steps:
1. Remove license.
2. Call admin filter AJAX.

Expected:
- Request returns error requiring license.

---

# 10. Release Package

## TC-066 Build Release Zip

Steps:
1. Run `npm run release`.

Expected:
- `release/lp-sticky-notes-1.0.6.zip` is created.
- `release/release-info.json` is updated.

## TC-067 Release Zip Exclusions

Steps:
1. Inspect zip contents.

Expected:
- Zip does not include:
  - `node_modules`
  - `scripts`
  - `release`
  - `.git`
  - `package.json`
  - `package-lock.json`
  - `composer.json`
  - `composer.lock`
  - `phpunit.xml`
  - temp/log files

## TC-068 Install From Zip

Steps:
1. Install plugin from release zip on a clean test site.
2. Activate plugin.

Expected:
- Plugin installs and activates.
- Required files are present.
- No dev files are present.

---

# 11. Regression Checklist

## TC-069 No Broken Encoding

Steps:
1. Inspect admin pages, profile, shortcode, lesson UI.

Expected:
- No broken characters like `â`, `âœ`, `âš`.

## TC-070 No Console Errors

Steps:
1. Test lesson, profile, shortcode, admin pages.
2. Open browser dev console.

Expected:
- No uncaught JS errors.

## TC-071 No PHP Errors

Steps:
1. Enable `WP_DEBUG_LOG`.
2. Run all core workflows.

Expected:
- No new warnings/fatals in debug log.

## TC-072 Browser Back/Forward In Lesson

Steps:
1. Navigate between lessons in LearnPress.
2. Use browser back/forward.

Expected:
- Current lesson ID updates.
- Notes reload for the correct lesson/course context.

## TC-073 Mobile Lesson UI

Steps:
1. Open lesson on mobile viewport.
2. Add/edit/delete notes.

Expected:
- Sidebar/toggle usable.
- No overlap with LearnPress navigation.

## TC-074 Final Smoke Test

Steps:
1. Activate license.
2. Add text note.
3. Add highlight note.
4. Edit both notes.
5. Delete one note.
6. View remaining note in sidebar, View All modal, shortcode, profile, and admin.

Expected:
- All locations show consistent data.
- Links open correct course-context lesson URL.
- No duplicate notes.
