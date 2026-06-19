# Changelog v1.0.6
**Release Date:** 2026-06-15

### Fixed
- Fixed editing an existing note creating a new note instead of updating the saved note.
- Fixed note timestamps using server database time instead of WordPress site time, which could make a newly created note show as several hours old in Profile > My Notes.
- Fixed lesson sidebar notes being mixed between courses when the same lesson is reused in multiple LearnPress courses.
- Fixed the lesson View All Notes modal showing notes from other courses instead of only the current course.
- Fixed license validation becoming invalid when license checks were triggered too frequently.
- Scoped the Student Notes admin page by role so administrators can review all notes, while students can open the same page to view only their own notes.
- Fixed Student Notes filters and AJAX search so student users cannot expose notes, students, or courses outside their own note data.
- Fixed admin filter layout issues where student and course filters could collapse to a very narrow width.
- Fixed table spacing in Student Notes admin so table headers and edge columns are readable in wp-admin.
- Fixed broken encoding characters in backend UI copy.
- Fixed AJAX error handling so users receive clearer server or HTTP failure messages.

### Added
- Added searchable Student and Course combobox filters to the Student Notes admin page.
- Added AJAX endpoints for admin filter search:
  - `lp_sticky_notes_search_students`
  - `lp_sticky_notes_search_courses`
- Added capped initial filter option loading to avoid rendering very large student/course lists.
- Added separate count queries for Students with notes and Courses with notes.
- Added current-page search and client-side sorting to the Student Notes admin table.
- Added DB schema version option `lp_sticky_notes_db_version`.
- Added composite database indexes for larger datasets:
  - `(user_id, course_id, lesson_id)`
  - `(user_id, created_at)`
  - `(course_id, lesson_id)`

### Changed
- Rebuilt the Student Notes admin page UI to follow MamFlow backend design rules.
- Redesigned the Student Notes license-required state.
- Redesigned the Mamflow license tab UI for Sticky Notes.
- Hid the LearnPress Profile My Notes tab when the license is inactive.
- Standardized the Mamflow product URL through `LP_STICKY_NOTES_PRODUCT_URL`.
- Updated admin CSS/JS enqueue versions to include `filemtime()` for cache busting.
- Removed an unnecessary frontend footer debug script hook.
- Reduced backend inline styles and aligned admin UI spacing, borders, typography, and colors with MamFlow design rules.

### Technical
- Stored new note `created_at` and `updated_at` values with WordPress site time and refreshed `updated_at` on note edits.
- Updated database note queries to support filtering by both `lesson_id` and `course_id`.
- Passed the current `course_id` to the lesson View All Notes AJAX request and validated course access server-side before returning grouped notes.
- Verified shortcode, View All modal, and profile lesson links use the saved course/lesson context.
- Verified release packaging excludes dev-only files such as `node_modules`, `scripts`, `release`, package files, Composer files, and PHPUnit config.
