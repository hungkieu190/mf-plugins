# LearnPress Mamflow Insight - Architecture & Logic

## Plugin Structure

```
lp-mamflow-insights/
├── lp-mamflow-insights.php      # Main plugin file (singleton)
├── includes/
│   ├── class-database.php       # Database queries layer
│   ├── class-helpers.php        # Utility functions
│   └── admin/
│       ├── class-admin.php      # Admin controller + tabbed UI
│       ├── class-course-health.php
│       ├── class-lesson-analytics.php
│       ├── class-student-analytics.php
│       ├── class-instructor-performance.php
│       ├── class-alerts.php
│       └── class-export.php
├── inc/license/                  # Mamflow License System
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
```

---

## Core Architecture

### 1. Singleton Pattern
```php
MF_Insights::instance() → mf_insights()
```
- Main plugin class loads on `plugins_loaded`
- Initializes all handlers in `load_license_system()`

### 2. Tabbed Interface (Single Page)
**URL Structure:** `admin.php?page=mf-insights&tab={tab_key}`

| Tab Key | Handler Class | Method |
|---------|--------------|--------|
| `course-health` | `MF_Insights_Course_Health` | `render_content()` |
| `lessons` | `MF_Insights_Lesson_Analytics` | `render()` |
| `students` | `MF_Insights_Student_Analytics` | `render()` |
| `instructors` | `MF_Insights_Instructor_Performance` | `render_content()` |

**Note:** Classes với `render()` và `render_content()`:
- `render()` = standalone page with wrapper
- `render_content()` = content only for tabbed UI

### 3. Database Layer
`MF_Insights_Database` wraps `$wpdb` với các methods:
- `get_enrolled_count($course_id)`
- `get_completed_count($course_id)`
- `get_course_completion_rate($course_id)`
- `get_avg_progress($course_id)`
- `get_dropoff_rate($course_id, $days)`
- `get_course_quiz_pass_rate($course_id)`
- `get_instructor_courses($instructor_id)`
- `get_lessons_analytics($course_id)`
- `get_students_analytics($course_id)`

### 4. Features Matrix

| Feature | Class | AJAX Action | Status |
|---------|-------|-------------|--------|
| Course Metrics | `class-course-health.php` | - | ✅ |
| Lesson Funnel | `class-lesson-analytics.php` | - | ✅ |
| Student Risk | `class-student-analytics.php` | - | ✅ |
| Instructor Stats | `class-instructor-performance.php` | `mf_insights_get_instructor_data` | ✅ |
| Alerts System | `class-alerts.php` | `mf_insights_dismiss_alert` | ✅ |
| CSV Export | `class-export.php` | `mf_insights_export_csv` | ✅ |

### 5. Alerts Thresholds
Stored in: `get_option('mf_insights_alert_thresholds')`
```php
[
    'low_completion' => 30,   // % below triggers warning
    'high_dropout' => 50,     // % above triggers warning
    'quiz_fail' => 40,        // % below triggers warning
    'inactive_days' => 30     // days threshold
]
```

### 6. Email Digest
- WP Cron: `mf_insights_daily_alerts`
- Scheduled: Daily at midnight
- Sends summary email to admin

---

## Key Integration Points

### LearnPress Tables Used
- `{prefix}learnpress_user_items` - Course enrollments & progress
- `{prefix}learnpress_sections` - Course sections
- `{prefix}learnpress_section_items` - Lesson/Quiz links
- `{prefix}posts` (post_type: `lp_course`, `lp_lesson`, `lp_quiz`)

### JavaScript
`admin.js` handles:
- Chart.js initialization (engagement chart)
- Select2 for course/instructor dropdowns
- CSV export via AJAX → Blob download
- Alert dismiss functionality

---

## Development Phases

- **Phase 1:** ✅ Foundation & Core Analytics (MVP)
- **Phase 2:** ✅ Alerts & Instructor Analytics
- **Phase 3:** ⬜ EngageSim (Pro Module) - Predictive analytics
