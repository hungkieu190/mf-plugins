# LearnPress Mamflow Insight - Implementation Plan

> Plugin Analytics chuyên sâu cho LearnPress, giúp Instructor & Co-Instructor kiểm soát chất lượng khóa học bằng dữ liệu.

---

## Tổng quan dự án

Plugin được chia thành **3 Phase** theo Release Strategy trong concept:
- **Phase 1 (MVP)**: Core Analytics - Course/Lesson/Student analytics
- **Phase 2**: Alerts & Instructor Performance
- **Phase 3 (Pro)**: EngageSim - Predictive Simulation

> **IMPORTANT:** Tất cả đều **hook-based & read-only** - không can thiệp core LearnPress.

---

## Kiến trúc Plugin

```
lp-mamflow-insights/
├── lp-mamflow-insights.php       # Main plugin file
├── includes/
│   ├── class-database.php        # Database queries
│   ├── class-helpers.php         # Utility functions
│   ├── admin/
│   │   ├── class-admin.php       # Admin controller
│   │   ├── class-course-health.php
│   │   ├── class-lesson-analytics.php
│   │   ├── class-student-analytics.php
│   │   ├── class-alerts.php      # Phase 2
│   │   ├── class-instructor-performance.php  # Phase 2
│   │   └── class-export.php      # Phase 2
│   └── engagesim/                # Phase 3
│       ├── class-engagesim.php
│       ├── class-risk-scoring.php
│       └── class-suggestions.php
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
└── templates/
    └── admin/
        ├── dashboard.php
        ├── course-health.php
        ├── lesson-analytics.php
        └── student-analytics.php
```

---

## Phase 1: Core Analytics (MVP)

### 1.1 Plugin Bootstrap

**File:** `lp-mamflow-insights.php`
- Plugin header
- Constants (version, path, url)
- LearnPress dependency check
- Singleton class `MF_Insights`
- Autoload required files

---

### 1.2 Database Layer

**File:** `includes/class-database.php`

Query data từ LearnPress tables:
- `{prefix}learnpress_user_items` - Course/Lesson enrollment & completion
- `{prefix}learnpress_user_itemmeta` - Progress data, time spent
- `{prefix}learnpress_quiz_results` - Quiz scores

**Methods chính:**
```php
// Course metrics
get_course_completion_rate($course_id)
get_course_avg_progress($course_id)
get_course_dropout_rate($course_id)
get_course_enrolled_count($course_id)

// Lesson metrics
get_lesson_completion_rates($course_id)
get_lesson_avg_time($lesson_id)
get_lesson_dropoff_rate($lesson_id)

// Quiz metrics
get_quiz_pass_rate($quiz_id)
get_quiz_avg_score($quiz_id)

// Student metrics
get_active_students($course_id, $days = 7)
get_inactive_students($course_id, $days = 30)
get_at_risk_students($course_id)
get_student_progress_data($course_id)
```

---

### 1.3 Course Health Dashboard

**File:** `includes/admin/class-course-health.php`

| Metric | Mô tả | Formula |
|--------|-------|---------|
| Completion Rate | % học viên hoàn thành | `completed / enrolled × 100` |
| Avg Progress | Tiến độ trung bình | `SUM(progress) / enrolled` |
| Drop-off Rate | % bỏ học | `inactive 30d / enrolled × 100` |
| Quiz Pass Rate | % pass quiz tổng | `passed / attempted × 100` |
| Feedback Score | Điểm đánh giá | Từ LP Survey hoặc reviews |

---

### 1.4 Lesson-Level Analytics

**File:** `includes/admin/class-lesson-analytics.php`

- Table view tất cả lessons trong course
- Completion % per lesson
- Avg time spent
- Drop-off indicator (high/medium/low)
- Quiz fail rate nếu lesson có quiz

---

### 1.5 Student Behavior Analytics

**File:** `includes/admin/class-student-analytics.php`

- Student list with status (Active/Inactive/At-Risk)
- Last activity date
- Current progress
- Lessons completed
- Quiz attempts

---

## Phase 2: Alerts & Instructor Analytics

### 2.1 Alerts System
**File:** `includes/admin/class-alerts.php`
- Threshold configuration
- Dashboard alerts
- Email notifications

### 2.2 Instructor Performance
**File:** `includes/admin/class-instructor-performance.php`
- Instructor/Co-Instructor mapping
- Performance metrics per instructor

### 2.3 Export & Reports
**File:** `includes/admin/class-export.php`
- CSV/PDF export

---

## Phase 3: EngageSim (Pro)

### 3.1 Rule-Based Risk Engine
**File:** `includes/engagesim/class-engagesim.php`
- Course structure analysis
- Dropout probability calculation

### 3.2 Risk Scoring
**File:** `includes/engagesim/class-risk-scoring.php`
- Risk Score per lesson/section (0-100)
- Visual Risk Map

### 3.3 Smart Suggestions
**File:** `includes/engagesim/class-suggestions.php`
- Structural recommendations

---

## Permissions Matrix

| Feature | Instructor | Co-Instructor |
|---------|------------|---------------|
| View Course Health | ✅ | ✅ |
| View Lesson Analytics | ✅ | ✅ |
| View Student Analytics | ✅ | ✅ (assigned only) |
| Run EngageSim | ✅ | ⚠️ (view only) |
| Export Reports | ✅ | ❌ |
| Configure Alerts | ✅ | ❌ |

---

## Dependencies

- **Required:** LearnPress 4.x+
- **Optional:** LP Survey (for feedback scores)
- **Included:** Chart.js
