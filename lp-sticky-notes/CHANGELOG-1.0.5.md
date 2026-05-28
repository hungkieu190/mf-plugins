# Changelog v1.0.5
**Release Date:** 2026-05-28

### Fixed
- Fixed lesson links in shortcode `[lp_sticky_notes]`, LearnPress profile "My Notes", "View All Notes" modal, and admin student notes so they now open inside the correct LearnPress course URL instead of the raw lesson URL.
- Fixed shortcode lesson links that still fell back to `/lessons/lesson-slug/` when the LearnPress helper was unavailable or returned the standalone lesson permalink.
- Fixed "Export PDF" on shortcode notes pages generating a blank output when the shortcode was inside theme/page-builder wrappers.
- Fixed "View All Notes" grouping so notes are grouped by both course and lesson, preventing mixed notes when the same lesson is used in multiple courses.
- Kept the existing Primary Color behavior for headers/accent areas after adding new button color controls.

### Added
- Added more Appearance color controls:
  - Sidebar Background Color
  - Note Background Color
  - Button Background Color
  - Button Text Color
  - Button Hover Background Color
  - Highlight Background Color
  - Highlight Text Color
  - Shortcode Note Background Color
  - Shortcode Note Border Color
  - Shortcode Link Color
- Added dynamic CSS variables for the new appearance settings so sidebar, modal, notes, buttons, highlights, and shortcode notes can be customized from the settings page.
- Added shortcode-specific CSS variables so `[lp_sticky_notes]` pages respect the new color settings even when the main lesson stylesheet is not loaded.

### Changed
- Improved lesson URL generation with a fallback that builds LearnPress course-context lesson URLs from the saved `course_id` and `lesson_id`.
- Improved shortcode PDF export by cloning the notes list into a dedicated print container before opening the browser print/PDF dialog.
- Rebuilt release package as `release/lp-sticky-notes-1.0.5.zip`.
