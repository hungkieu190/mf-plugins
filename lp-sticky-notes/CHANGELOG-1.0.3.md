# Changelog 1.0.3

## 🎉 New Features

### 1. Admin & Instructor Notes Management
- **New Dashboard Page**: Added **"Student Notes"** submenu under the LearnPress menu.
- **Role Support**: Allows **Administrators** and **Instructors** to view notes created by students.
- **Filtering**:
  - Filter notes by **Student** (search by name/email).
  - Filter notes by **Course**.
- **View Details**: "View Full" button to read the complete content of long notes.

### 2. Shortcode Support
- **New Shortcode**: `[lp_sticky_notes]`
- **Usage**: Display sticky notes on any post, page, or widget area.
- **Parameters**:
  - `limit`: Number of notes to display (default: 10).
  - `course_id`: Filter by specific course.
  - `user_id`: Filter by specific user (defaults to current user).
  - `show_course`: Show/hide course name (true/false).
  - `show_lesson`: Show/hide lesson name (true/false).
- **Responsive Design**: Notes are displayed in a responsive grid layout.

### 3. Customization Settings
- **Integrated Settings**: Added a dedicated **"Sticky Notes"** tab within **LearnPress > Settings**.
- **Appearance Options**:
  - **Sidebar Position**: Option to place the notes sidebar on the **Left** or **Right** of the screen.
  - **Toggle Button Position**: 6 predefined positions (Top-Left, Top-Right, Middle-Left, Middle-Right, Bottom-Left, Bottom-Right).
  - **Colors**: Customizers for **Primary Color** (backgrounds, buttons) and **Text Color**.
  - **Sizes**: Options to adjust **Button Size** (Small/Medium/Large) and **Sidebar Width**.
  - **Custom CSS**: Dedicated field for advanced CSS customization.

## 🛠 Improvements
- **CSS Variables**: Refactored the entire stylesheet to use CSS variables (`var(--lp-sn-...)`), enabling real-time customization and easier theming.
- **Performance**: Optimized CSS loading and rendering.
- **UX**: Improved the "Empty State" message when there are no notes.

## 🐛 Bug Fixes
- **Menu Visibility**: Fixed an issue where the "Student Notes" submenu would not appear due to hook priority conflicts.
- **Database**: Fixed a table name mismatch (`lp_sticky_notes` vs `learnpress_sticky_notes`) that prevented notes from being retrieved in the admin view.
- **Permissions**: Fixed permission checks to ensure Instructors can properly access the notes view.
- **Menu Slug**: Corrected the parent menu slug from `learn-press` to `learn_press` to ensure proper nesting in the WordPress admin menu.

## 📦 Technical Details
- **Class Structure**:
  - Added `LP_Sticky_Notes_Admin` for backend management.
  - Added `LP_Sticky_Notes_Settings` extending `LP_Abstract_Settings_Page`.
- **Hooks**:
  - Used `learn-press/admin/settings-tabs-array` for settings integration.
  - Used `wp_add_inline_style` for dynamic CSS injection.
