# LearnPress Live Studio — Implementation Plan & Checklist

> **Version:** 1.0.0 (Phase 1)
> **Created:** 2026-02-12
> **Platforms (Phase 1):** Zoom + Google Meet + Agora
> **Platforms (Phase 2):** Jitsi + BigBlueButton
> **Prefix:** `mf_lls_` (Mamflow LearnPress Live Studio)

---

## Mục lục

1. [Tổng quan Architecture](#1-tổng-quan-architecture)
2. [File Structure](#2-file-structure)
3. [Database Schema](#3-database-schema)
4. [Phase 1 — Implementation Checklist](#4-phase-1--implementation-checklist)
5. [Phase 2 — Future Features](#5-phase-2--future-features)
6. [Testing Checklist](#6-testing-checklist)
7. [Deployment Checklist](#7-deployment-checklist)

---

## 1. Tổng quan Architecture

### Dependency Graph (Thứ tự build)

```
Module 1 (Core & Admin Settings)
    │
    ├── Module 10 (License Integration) ← Refactor Module 1 to singleton
    │
    ├── Module 2 (Live Lesson Type)
    │       │
    │       ├── Module 3 (Platform Integrations)
    │       │       │
    │       │       ├── Module 4 (Frontend Display)
    │       │       │
    │       │       └── Module 5 (Attendance & Recording)
    │       │               │
    │       │               └── Module 6 (Feedback & Rating)
    │       │
    │       └── Module 7 (Notifications & Reminders)
    │
    └── Module 9 (Security — cross-cutting, áp dụng xuyên suốt)
```

> Module 8 (Advanced Features & Analytics) → Phase 2
> Module 10 gates all premium features (Modules 2-7)

### Naming Convention

| Loại           | Convention                          | Ví dụ                                   |
|----------------|-------------------------------------|-----------------------------------------|
| Function       | `mf_lls_function_name()`           | `mf_lls_get_live_session()`            |
| Class          | `MF_LLS_Class_Name`               | `MF_LLS_Admin_Settings`                |
| Hook           | `mf_lls_hook_name`                 | `mf_lls_after_session_end`             |
| Constant       | `MF_LLS_CONSTANT_NAME`            | `MF_LLS_VERSION`                        |
| Post Meta      | `_mf_lls_meta_key`                | `_mf_lls_platform`                      |
| Option         | `mf_lls_option_name`              | `mf_lls_zoom_api_key`                  |
| DB Table       | `{$wpdb->prefix}mf_lls_table`     | `wp_mf_lls_attendance`                 |
| Script Handle  | `mf-lls-script-name`              | `mf-lls-countdown`                      |
| CSS Handle     | `mf-lls-style-name`               | `mf-lls-live-room`                      |
| Nonce Action   | `mf_lls_action_name`              | `mf_lls_save_settings`                 |
| AJAX Action    | `mf_lls_ajax_action`              | `mf_lls_create_meeting`                |
| Cron Hook      | `mf_lls_cron_hook`                | `mf_lls_cron_reminder`                 |
| Text Domain    | `learnpress-live-studio`           |                                         |

---

## 2. File Structure

```
learnpress-live-studio/
├── learnpress-live-studio.php              # Main plugin file (constants, load, hooks only)
│
├── includes/
│   ├── class-mf-lls-activator.php          # Activation: create tables, schedule cron
│   ├── class-mf-lls-deactivator.php        # Deactivation: clear cron
│   │
│   ├── admin/
│   │   ├── class-mf-lls-admin-settings.php # Settings page (tabs, API keys)
│   │   ├── class-mf-lls-admin-reports.php  # Attendance & Rating reports
│   │   └── views/
│   │       ├── settings-page.php           # Settings page template
│   │       ├── settings-tab-general.php    # General tab
│   │       ├── settings-tab-zoom.php       # Zoom config tab
│   │       ├── settings-tab-google.php     # Google Meet config tab
│   │       ├── settings-tab-agora.php      # Agora config tab
│   │       └── reports-page.php            # Reports template
│   │
│   ├── frontend/
│   │   ├── class-mf-lls-live-lesson.php    # Live lesson type registration
│   │   ├── class-mf-lls-live-room.php      # Frontend room display
│   │   ├── class-mf-lls-shortcodes.php     # Shortcode handler
│   │   ├── class-mf-lls-countdown.php      # Countdown & status logic
│   │   └── class-mf-lls-rating-form.php    # Rating form frontend
│   │
│   ├── platforms/
│   │   ├── abstract-mf-lls-platform.php    # Abstract platform class
│   │   ├── class-mf-lls-zoom.php           # Zoom integration
│   │   ├── class-mf-lls-google-meet.php    # Google Meet integration
│   │   └── class-mf-lls-agora.php          # Agora integration
│   │
│   ├── license/                             # Mamflow License System
│   │   ├── class-license-handler.php       # MF_LLS_License_Handler
│   │   ├── shared-license-page.php         # Unified license menu
│   │   ├── admin-license-page.php          # Plugin license tab
│   │   └── cron-scheduler.php              # MF_LLS_License_Cron
│   │
│   ├── class-mf-lls-attendance.php         # Attendance tracking
│   ├── class-mf-lls-recording.php          # Recording management
│   ├── class-mf-lls-rating.php             # Rating/Feedback logic
│   ├── class-mf-lls-notifications.php      # Email notifications & reminders
│   ├── class-mf-lls-ajax.php               # AJAX handlers
│   └── class-mf-lls-cron.php              # Cron jobs (reminders, sync)
│
├── src/
│   ├── css/
│   │   ├── admin-settings.scss             # Admin styles
│   │   ├── live-room.scss                  # Live room frontend
│   │   ├── countdown.scss                  # Countdown component
│   │   └── rating-form.scss               # Rating form
│   │
│   └── js/
│       ├── admin-settings.js               # Admin settings JS
│       ├── live-room.js                    # Room embed/join logic
│       ├── countdown.js                    # Countdown timer
│       ├── rating-form.js                  # Rating AJAX submit
│       └── agora-client.js                 # Agora SDK wrapper
│
├── assets/
│   ├── css/     # Compiled CSS (do NOT edit directly)
│   └── js/      # Compiled JS (do NOT edit directly)
│
├── templates/
│   ├── live-lesson.php                     # Template override for live lesson
│   ├── live-room.php                       # Live room embed template
│   ├── countdown.php                       # Countdown display
│   ├── rating-form.php                     # Rating form template
│   └── emails/
│       ├── reminder.php                    # Reminder email template
│       └── rating-request.php             # Rating request email template
│
├── languages/
│   └── learnpress-live-studio.pot
│
├── plan.md                                 # Feature specification
├── IMPLEMENTATION_PLAN.md                  # This file
└── README.txt
```

---

## 3. Database Schema

### Table: `{prefix}mf_lls_attendance`

| Column       | Type                | Description                    |
|--------------|---------------------|--------------------------------|
| id           | BIGINT UNSIGNED AI  | Primary key                    |
| user_id      | BIGINT UNSIGNED     | FK → wp_users.ID               |
| lesson_id    | BIGINT UNSIGNED     | FK → wp_posts.ID (lesson)      |
| course_id    | BIGINT UNSIGNED     | FK → wp_posts.ID (course)      |
| join_time    | DATETIME            | Thời điểm tham gia             |
| leave_time   | DATETIME NULL       | Thời điểm rời (null = ongoing) |
| duration     | INT UNSIGNED        | Tổng giây tham gia             |
| platform     | VARCHAR(50)         | zoom/google_meet/agora         |
| created_at   | DATETIME            | Record creation time           |

**Indexes:** `(user_id, lesson_id)`, `(lesson_id)`, `(course_id)`

### Table: `{prefix}mf_lls_ratings`

| Column       | Type                | Description                    |
|--------------|---------------------|--------------------------------|
| id           | BIGINT UNSIGNED AI  | Primary key                    |
| user_id      | BIGINT UNSIGNED     | FK → wp_users.ID               |
| lesson_id    | BIGINT UNSIGNED     | FK → wp_posts.ID (lesson)      |
| course_id    | BIGINT UNSIGNED     | FK → wp_posts.ID (course)      |
| tutor_id     | BIGINT UNSIGNED     | FK → wp_users.ID (instructor)  |
| rating_tutor | TINYINT UNSIGNED    | 1-5 stars cho tutor            |
| rating_content | TINYINT UNSIGNED  | 1-5 stars cho content          |
| comment      | TEXT                | Feedback text                  |
| created_at   | DATETIME            | Submit time                    |
| expires_at   | DATETIME            | Hết hạn rating (7 ngày)        |

**Indexes:** `(user_id, lesson_id)` UNIQUE, `(tutor_id)`, `(lesson_id)`

### Post Meta Keys (lesson)

| Meta Key                    | Type     | Description                        |
|-----------------------------|----------|------------------------------------|
| `_mf_lls_is_live`          | string   | '1' nếu lesson là live session     |
| `_mf_lls_platform`         | string   | 'zoom' / 'google_meet' / 'agora'  |
| `_mf_lls_start_time`       | string   | ISO 8601 datetime                  |
| `_mf_lls_duration`         | int      | Phút                               |
| `_mf_lls_meeting_id`       | string   | Platform meeting ID                |
| `_mf_lls_join_url`         | string   | URL tham gia                       |
| `_mf_lls_host_url`         | string   | URL host/start (cho tutor)         |
| `_mf_lls_participant_limit`| int      | Giới hạn số người                  |
| `_mf_lls_status`           | string   | 'upcoming' / 'live' / 'ended'     |
| `_mf_lls_recording_url`    | string   | URL recording sau session          |
| `_mf_lls_platform_data`    | array    | Raw platform response data         |

---

## 4. Phase 1 — Implementation Checklist

### 🔷 Module 1: Core & Admin Settings

> **Mục tiêu:** Plugin skeleton, activation, settings page
> **Dependencies:** LearnPress 4.x+

#### 1.1 Main Plugin File

- [x] **1.1.1** Tạo `lp-live-studio.php`
  - [x] Plugin header (Name, Version, Author, Text Domain, Require LP Version)
  - [x] `ABSPATH` check
  - [x] Define constants: `MF_LLS_VERSION`, `MF_LLS_DIR`, `MF_LLS_URL`, `MF_LLS_FILE`
  - [x] Check LearnPress active (dependency check)
  - [x] Load text domain
  - [x] Require files from `/includes/`
  - [x] Register activation/deactivation hooks

#### 1.2 Activator / Deactivator

- [x] **1.2.1** Tạo `class-mf-lls-activator.php`
  - [x] Method `mf_lls_activate()`: tạo DB tables, set default options, schedule cron
  - [x] Sử dụng `dbDelta()` cho table creation
  - [x] Lưu `mf_lls_db_version` vào options

- [x] **1.2.2** Tạo `class-mf-lls-deactivator.php`
  - [x] Method `mf_lls_deactivate()`: clear cron events

#### 1.3 Admin Settings Page

- [x] **1.3.1** Tạo `class-mf-lls-admin-settings.php`
  - [x] Hook `admin_menu` (priority 100) → add submenu dưới `learn_press`
  - [x] Tab General: default platform, enable/disable features
  - [x] Tab Zoom: API Key, API Secret, JWT/OAuth toggle
  - [x] Tab Google Meet: Client ID, Client Secret, OAuth redirect URI
  - [x] Tab Agora: App ID, App Certificate, Channel prefix
  - [ ] Tab Email Templates: Reminder, Rating request templates
  - [x] Nonce validation trên save
  - [x] `current_user_can('manage_options')` check
  - [x] Sanitize tất cả input trước khi save (`sanitize_text_field`, `sanitize_textarea_field`)
  - [x] **License Gate Overlay** with close button and dashboard redirect

- [x] **1.3.2** Tạo các view files trong `includes/admin/views/`
  - [x] `settings-page.php` — Layout wrapper với tab navigation
  - [x] `settings-tab-general.php`
  - [x] `settings-tab-zoom.php` (Account ID + Client ID + Client Secret)
  - [x] `settings-tab-google.php`
  - [x] `settings-tab-agora.php`
  - [x] `settings-tab-email.php` (Reminder + Rating templates)

- [x] **1.3.3** Admin CSS/JS
  - [x] `assets/css/admin-settings.css`
  - [x] `assets/js/admin-settings.js` (tab switching, test connection button, Google OAuth, send test email)
  - [x] Enqueue chỉ trên page `learnpress_page_mf-lls-settings`
  - [x] AJAX handlers: `mf_lls_test_connection`, `mf_lls_send_test_email`

#### 1.4 Constants & Config

- [x] **1.4.1** Define tất cả option keys dạng constants
  ```
  MF_LLS_OPT_DEFAULT_PLATFORM
  MF_LLS_OPT_ZOOM_AUTH_TYPE
  MF_LLS_OPT_ZOOM_ACCOUNT_ID
  MF_LLS_OPT_ZOOM_API_KEY
  MF_LLS_OPT_ZOOM_API_SECRET
  MF_LLS_OPT_GOOGLE_CLIENT_ID
  MF_LLS_OPT_GOOGLE_CLIENT_SECRET
  MF_LLS_OPT_AGORA_APP_ID
  MF_LLS_OPT_AGORA_APP_CERT
  ```

---

### 🔷 Module 2: Live Lesson Type Integration

> **Mục tiêu:** Thêm lesson type "Live" vào LearnPress curriculum
> **Dependencies:** Module 1

#### 2.1 Register Live Lesson Type

- [ ] **2.1.1** Tạo `class-mf-lls-live-lesson.php`
  - [ ] Hook `lp/metabox/lesson/lists` → thêm live session fields
  - [ ] Fields: `_mf_lls_is_live` (checkbox), `_mf_lls_platform` (select), `_mf_lls_start_time` (datetime), `_mf_lls_duration` (number), `_mf_lls_participant_limit` (number)
  - [ ] Sử dụng LP Meta Box Field classes (`LP_Meta_Box_Checkbox_Field`, `LP_Meta_Box_Select_Field`, `LP_Meta_Box_Text_Field`)

#### 2.2 Metabox & Save Logic

- [ ] **2.2.1** Hook `learnpress_save_lp_lesson_metabox` → save live meta
  - [ ] Sanitize `_mf_lls_start_time` (validate ISO 8601)
  - [ ] Sanitize `_mf_lls_duration` (`absint()`)
  - [ ] Sanitize `_mf_lls_participant_limit` (`absint()`)
  - [ ] Sanitize `_mf_lls_platform` (whitelist: 'zoom', 'google_meet', 'agora')
  - [ ] Auto-set `_mf_lls_status` = 'upcoming' khi save

#### 2.3 Session Status Management

- [ ] **2.3.1** Logic determine status
  - [ ] `upcoming`: start_time > now
  - [ ] `live`: start_time <= now <= start_time + duration
  - [ ] `ended`: now > start_time + duration
  - [ ] Function `mf_lls_get_session_status( $lesson_id )` trả status real-time
  - [ ] Cron job update status hàng loạt (mỗi 5 phút)

#### 2.4 Auto Create Meeting

- [ ] **2.4.1** Hook `save_post_lp_lesson` → trigger meeting creation
  - [ ] Chỉ create khi: `_mf_lls_is_live` = '1' AND `post_status` = 'publish' AND `_mf_lls_meeting_id` trống
  - [ ] Gọi platform class `create_room()` → lưu `_mf_lls_meeting_id`, `_mf_lls_join_url`, `_mf_lls_host_url`
  - [ ] Error handling: admin notice nếu API fail

---

### 🔷 Module 3: Platform Integrations

> **Mục tiêu:** Abstract platform layer + concrete implementations
> **Dependencies:** Module 2

#### 3.0 Abstract Platform

- [ ] **3.0.1** Tạo `abstract-mf-lls-platform.php`
  ```php
  abstract class MF_LLS_Platform {
      abstract public function create_room( $args );   // Return: meeting_id, join_url, host_url
      abstract public function delete_room( $meeting_id );
      abstract public function get_join_url( $meeting_id );
      abstract public function get_host_url( $meeting_id );
      abstract public function end_session( $meeting_id );
      abstract public function get_attendance( $meeting_id );
      abstract public function is_configured();        // Check credentials
      abstract public function test_connection();      // Test API connection
      abstract public function get_embed_html( $meeting_id, $user ); // Embed output
  }
  ```
- [ ] **3.0.2** Factory method `mf_lls_get_platform( $platform_slug )` → return correct class instance

#### 3.1 Zoom Integration

- [ ] **3.1.1** Tạo `class-mf-lls-zoom.php` extends `MF_LLS_Platform`
  - [ ] `create_room()`: POST `/users/me/meetings` → lưu meeting ID, join_url
  - [ ] `delete_room()`: DELETE `/meetings/{id}`
  - [ ] `get_join_url()`: Return stored join_url
  - [ ] `end_session()`: PUT `/meetings/{id}/status` action=end
  - [ ] `get_attendance()`: GET `/report/meetings/{id}/participants`
  - [ ] `get_embed_html()`: iframe embed hoặc redirect link
  - [ ] `test_connection()`: GET `/users/me` → verify credentials
  - [ ] `is_configured()`: Check API key + secret not empty

- [ ] **3.1.2** Zoom OAuth Token Management
  - [ ] Server-to-Server OAuth flow (preferred cho Zoom API v2+)
  - [ ] Cache access token vào transient (TTL = expires_in - 60s)
  - [ ] Method `mf_lls_zoom_get_access_token()` → return valid token

- [ ] **3.1.3** Zoom Webhook Handler
  - [ ] Endpoint: REST API route `mf-lls/v1/webhook/zoom`
  - [ ] Verify webhook signature
  - [ ] Handle events: `meeting.ended`, `meeting.participant_joined`, `meeting.participant_left`

#### 3.2 Google Meet Integration

- [ ] **3.2.1** Tạo `class-mf-lls-google-meet.php` extends `MF_LLS_Platform`
  - [ ] `create_room()`: Google Calendar API → create event với conferenceData (Meet link)
  - [ ] `delete_room()`: Google Calendar API → delete event
  - [ ] `get_join_url()`: Return stored Meet URL
  - [ ] `get_embed_html()`: Link + button (Meet không hỗ trợ embed iframe)
  - [ ] `test_connection()`: Verify OAuth token valid

- [ ] **3.2.2** Google OAuth Flow
  - [ ] Admin UI: "Connect Google Account" button
  - [ ] OAuth redirect handler
  - [ ] Store refresh_token encrypted trong options
  - [ ] Auto-refresh access_token khi expired

- [ ] **3.2.3** Calendar Invite
  - [ ] Auto add enrolled students vào calendar event attendees
  - [ ] Hook enrollment → add attendee to event

#### 3.3 Agora Integration

- [ ] **3.3.1** Tạo `class-mf-lls-agora.php` extends `MF_LLS_Platform`
  - [ ] `create_room()`: Generate unique channel name (prefix + lesson_id + timestamp)
  - [ ] `get_join_url()`: Return internal URL với channel params
  - [ ] `get_embed_html()`: Full Agora Web SDK embed (video/audio)
  - [ ] `test_connection()`: Validate App ID format

- [ ] **3.3.2** Agora Token Server
  - [ ] PHP token generation (RtcTokenBuilder)
  - [ ] REST API endpoint `mf-lls/v1/agora/token` → generate temp token
  - [ ] Token TTL: session duration + 30min buffer
  - [ ] Validate user enrollment trước khi issue token

- [ ] **3.3.3** Agora Client JS
  - [ ] `src/js/agora-client.js`: Wrapper cho Agora Web SDK
  - [ ] Join/Leave channel
  - [ ] Mute/Unmute audio/video
  - [ ] Hand-raise signaling (custom message)
  - [ ] Screen share toggle

---

### 🔷 Module 4: Embedding & Frontend Display

> **Mục tiêu:** Hiển thị live room, countdown, join button
> **Dependencies:** Module 2 & 3

#### 4.1 Live Room Display

- [ ] **4.1.1** Tạo `class-mf-lls-live-room.php`
  - [ ] Template override cho lesson live type
  - [ ] Check enrollment + session status trước khi hiển thị
  - [ ] Chỉ show join button cho `upcoming` (15min trước) và `live` sessions
  - [ ] Show recording link cho `ended` sessions (nếu có)

- [ ] **4.1.2** Template files
  - [ ] `templates/live-lesson.php` — Main live lesson template
  - [ ] `templates/live-room.php` — Room embed/iframe area
  - [ ] `templates/countdown.php` — Countdown timer component

- [ ] **4.1.3** Template override support
  - [ ] Theme có thể override: `yourtheme/learnpress-live-studio/live-lesson.php`

#### 4.2 Shortcode

- [ ] **4.2.1** Tạo `class-mf-lls-shortcodes.php`
  - [ ] `[mf_lls_live_room lesson_id="X"]` — Embed live room
  - [ ] `[mf_lls_countdown lesson_id="X"]` — Countdown timer
  - [ ] `[mf_lls_schedule course_id="X"]` — List upcoming live sessions
  - [ ] Enrollment check trong mỗi shortcode
  - [ ] Escape tất cả output

#### 4.3 Countdown & Status

- [ ] **4.3.1** Tạo `class-mf-lls-countdown.php`
  - [ ] PHP render initial countdown HTML
  - [ ] JS countdown timer (decrement mỗi giây)
  - [ ] Auto-refresh status via AJAX polling (mỗi 30 giây)
  - [ ] Visual states: "Starting in X:XX:XX" → "LIVE NOW" → "Session Ended"

- [ ] **4.3.2** Frontend CSS/JS
  - [ ] `src/css/live-room.scss` — Room layout responsive
  - [ ] `src/css/countdown.scss` — Countdown animation
  - [ ] `src/js/live-room.js` — Room embed logic
  - [ ] `src/js/countdown.js` — Timer + polling
  - [ ] Enqueue chỉ trên single lesson page có `_mf_lls_is_live`

---

### 🔷 Module 5: Attendance & Recording

> **Mục tiêu:** Track participation, lưu recording
> **Dependencies:** Module 3

#### 5.1 Attendance Tracking

- [ ] **5.1.1** Tạo `class-mf-lls-attendance.php`
  - [ ] Method `mf_lls_log_join( $user_id, $lesson_id )` — INSERT attendance record
  - [ ] Method `mf_lls_log_leave( $user_id, $lesson_id )` — UPDATE leave_time, calculate duration
  - [ ] Method `mf_lls_get_attendance( $lesson_id )` — SELECT all attendees cho session
  - [ ] Method `mf_lls_get_user_attendance( $user_id, $lesson_id )` — Check user đã attend chưa
  - [ ] Sử dụng `$wpdb->prepare()` cho tất cả queries

- [ ] **5.1.2** Attendance via Platform Webhooks
  - [ ] Zoom: Webhook `participant_joined` / `participant_left` → map email → user_id → log
  - [ ] Google Meet: Calendar API check (limited attendance data)
  - [ ] Agora: Client-side event → AJAX call `mf_lls_log_join` / `mf_lls_log_leave`

- [ ] **5.1.3** Cron Sync
  - [ ] Hook `mf_lls_cron_sync_attendance` — Chạy sau mỗi session end
  - [ ] Sync attendance từ platform API (Zoom report endpoint)
  - [ ] Reconcile with local logs

#### 5.2 Recording Management

- [ ] **5.2.1** Tạo `class-mf-lls-recording.php`
  - [ ] Zoom: GET `/meetings/{id}/recordings` → lưu download URL
  - [ ] Auto-save recording URL vào `_mf_lls_recording_url` post meta
  - [ ] Display recording link trên ended lesson page

#### 5.3 Admin Reports

- [ ] **5.3.1** Tạo `class-mf-lls-admin-reports.php`
  - [ ] Page: Attendance report per session
  - [ ] Columns: Student Name, Join Time, Leave Time, Duration, %
  - [ ] Filter by: Course, Lesson, Date range
  - [ ] Export CSV button
  - [ ] `current_user_can('manage_options')` check

---

### 🔷 Module 6: Feedback & Tutor Rating

> **Mục tiêu:** Rating tutor sau live session
> **Dependencies:** Module 5

#### 6.1 Rating Form

- [ ] **6.1.1** Tạo `class-mf-lls-rating-form.php`
  - [ ] Hook `learn_press_after_single_lesson` → show form nếu:
    - Session `ended`
    - User `attended` (có record trong attendance table)
    - User **chưa** rated session này
    - Chưa quá 7 ngày từ session end
  - [ ] Form fields: Star rating tutor (1-5), Star rating content (1-5), Comment textarea
  - [ ] Nonce field `mf_lls_rating_nonce`

- [ ] **6.1.2** Frontend assets
  - [ ] `src/css/rating-form.scss` — Star rating CSS, form layout
  - [ ] `src/js/rating-form.js` — Star click handler, AJAX submit
  - [ ] Enqueue chỉ khi form cần hiển thị

#### 6.2 Rating Logic

- [ ] **6.2.1** Tạo `class-mf-lls-rating.php`
  - [ ] Method `mf_lls_submit_rating( $data )` — INSERT vào `mf_lls_ratings` table
  - [ ] Validate: user_id, lesson_id phải khớp attendance record
  - [ ] UNIQUE constraint: 1 rating / user / lesson
  - [ ] Set `expires_at` = session_end + 7 days
  - [ ] Return success/error

- [ ] **6.2.2** AJAX handler
  - [ ] Action `mf_lls_submit_rating`
  - [ ] Nonce verify: `check_ajax_referer( 'mf_lls_rating_nonce' )`
  - [ ] `is_user_logged_in()` check
  - [ ] Sanitize: `absint()` cho stars, `sanitize_textarea_field()` cho comment
  - [ ] Return JSON response

- [ ] **6.2.3** Display Average Rating
  - [ ] Method `mf_lls_get_tutor_avg_rating( $tutor_id )` — AVG rating stars
  - [ ] Hook tutor profile page (nếu có Instructor addon)
  - [ ] Escape output: `esc_html()`

#### 6.3 Tutor Dashboard

- [ ] **6.3.1** Tutor feedback view
  - [ ] List tất cả ratings cho sessions của tutor
  - [ ] Columns: Session, Student (anonymized hoặc full), Stars, Comment, Date
  - [ ] Average summary at top

#### 6.4 Admin Rating Report

- [ ] **6.4.1** Admin report page
  - [ ] Filter: by tutor, by course, by date range
  - [ ] Export CSV
  - [ ] Average rating summary

---

### 🔷 Module 7: Notifications & Reminders

> **Mục tiêu:** Email reminders trước/sau live session
> **Dependencies:** Module 2

#### 7.1 Reminder System

- [ ] **7.1.1** Tạo `class-mf-lls-notifications.php`
  - [ ] Schedule reminder: 1 giờ trước session → WP Cron
  - [ ] Schedule reminder: 15 phút trước session → WP Cron
  - [ ] Hook `save_post_lp_lesson` → auto schedule cron khi lesson published
  - [ ] Hook `before_delete_post` → clear scheduled cron

- [ ] **7.1.2** Cron Handlers
  - [ ] `mf_lls_cron_reminder_1h` — Fire 1 giờ trước
  - [ ] `mf_lls_cron_reminder_15m` — Fire 15 phút trước
  - [ ] Get enrolled users → send email to each
  - [ ] `wp_mail()` với HTML template

#### 7.2 Post-Session Notifications

- [ ] **7.2.1** Rating request email
  - [ ] Trigger khi session status → `ended`
  - [ ] Chỉ gửi cho users có attendance record
  - [ ] Include link tới lesson page (chứa rating form)
  - [ ] Template: `templates/emails/rating-request.php`

#### 7.3 Email Templates

- [ ] **7.3.1** Template files
  - [ ] `templates/emails/reminder.php` — Variables: {student_name}, {lesson_title}, {course_title}, {start_time}, {join_url}
  - [ ] `templates/emails/rating-request.php` — Variables: {student_name}, {lesson_title}, {tutor_name}, {rating_url}
  - [ ] Template override via theme: `yourtheme/learnpress-live-studio/emails/reminder.php`

---

### 🔷 Module 9: Security & Optimization (Cross-cutting)

> **Mục tiêu:** Security headers trên tất cả code
> **Áp dụng:** Xuyên suốt tất cả modules

#### 9.1 Security

- [ ] **9.1.1** Mỗi PHP file đều có `if ( ! defined( 'ABSPATH' ) ) { exit; }`
- [ ] **9.1.2** Tất cả AJAX actions kiểm tra nonce
- [ ] **9.1.3** Tất cả admin actions kiểm tra `current_user_can()`
- [ ] **9.1.4** Tất cả input đều sanitize (`sanitize_*()`)
- [ ] **9.1.5** Tất cả output đều escape (`esc_*()`)
- [ ] **9.1.6** Tất cả DB queries đều `$wpdb->prepare()`
- [ ] **9.1.7** Enrollment check trước khi cho phép join session
- [ ] **9.1.8** REST API endpoints kiểm tra permission callback

#### 9.2 Performance

- [ ] **9.2.1** Lazy load platform SDK (JS chỉ load khi cần)
- [ ] **9.2.2** Cache meeting URLs trong transient (TTL = session duration)
- [ ] **9.2.3** Không query trong loop
- [ ] **9.2.4** Asset versioning dùng `filemtime()` cho cache busting
- [ ] **9.2.5** Conditional enqueue: chỉ load CSS/JS trên đúng page cần

#### 9.3 AJAX Handler Central

- [ ] **9.3.1** Tạo `class-mf-lls-ajax.php`
  - [ ] Register tất cả AJAX actions ở 1 chỗ
  - [ ] `mf_lls_create_meeting` — Admin create meeting
  - [ ] `mf_lls_test_connection` — Admin test platform connection
  - [ ] `mf_lls_join_session` — Student join (log attendance)
  - [ ] `mf_lls_leave_session` — Student leave (log attendance)
  - [ ] `mf_lls_submit_rating` — Student submit rating
  - [ ] `mf_lls_get_session_status` — Polling session status
  - [ ] Mỗi handler: nonce check → capability check → sanitize → process → JSON response

#### 9.4 REST API Endpoints

- [ ] **9.4.1** Register REST routes (namespace: `mf-lls/v1`)
  - [ ] `POST /webhook/zoom` — Zoom webhook receiver
  - [ ] `GET /agora/token` — Agora token generation
  - [ ] Permission callbacks trên mỗi route

---

### 🔷 Module 10: License System Integration

> **Mục tiêu:** Tích hợp Mamflow License System để quản lý license và gate premium features
> **Dependencies:** Module 1 (refactor main plugin file to singleton)
> **Reference:** `mamflow-license-integration-guide.md`

#### 10.1 Unique Naming (CRITICAL!)

- [x] **10.1.1** Define unique class names
  - [x] License Handler: `MF_LLS_License_Handler`
  - [x] Cron Class: `MF_LLS_License_Cron`
  - [x] Cron Hook: `mf_lls_daily_license_check`
  - [x] Tab Function: `mf_lls_render_license_tab`
  - [x] Tab Slug: `live-studio`
  - [x] Option Key: `mf_lls_license`

#### 10.2 File Structure

- [x] **10.2.1** Create license folder structure
  - [x] `includes/license/`
  - [x] `class-license-handler.php`
  - [x] `shared-license-page.php`
  - [x] `admin-license-page.php`
  - [x] `cron-scheduler.php`

#### 10.3 License Handler

- [x] **10.3.1** Create `includes/license/class-license-handler.php`
  - [x] All methods implemented and unique naming verified

#### 10.4 Shared License Page

- [x] **10.4.1** Copy `includes/license/shared-license-page.php`
  - [x] Copied and integrated

#### 10.5 Admin License Page

- [x] **10.5.1** Create `includes/license/admin-license-page.php`
  - [x] Tab rendering and AJAX actions implemented

#### 10.6 Cron Scheduler

- [x] **10.6.1** Create `includes/license/cron-scheduler.php`
  - [x] Daily check scheduled and notification system ready

#### 10.7 Refactor Main Plugin File

- [x] **10.7.1** Add Product ID constant (47326)
- [x] **10.7.2** Convert `MF_LLS_Addon` to singleton
- [x] **10.7.3** Load license system in constructor
- [x] **10.7.4** Add license menu hooks
- [x] **10.7.5** Add license notice
- [x] **10.7.6** Add getter method
- [x] **10.7.7** Update activation/deactivation

#### 10.8 Feature Gating (Infrastructure)

- [x] **10.8.1** License Gate Overlay on Settings Page
- [x] **10.8.2** Dashboard Redirect on close

#### 10.9 Verification Checklist

- [x] **10.9.1** Pre-deployment checks
- [x] **10.9.2** WordPress admin tests
- [x] **10.9.3** Feature gating tests (Infrastructure)
- [x] **10.9.4** Cron tests

#### 10.10 Configuration Reference

**Unique Names Summary:**
| Element | Value |
|---------|-------|
| License Handler Class | `MF_LLS_License_Handler` |
| Cron Class | `MF_LLS_License_Cron` |
| Cron Hook | `mf_lls_daily_license_check` |
| License Tab Function | `mf_lls_render_license_tab` |
| License Tab Slug | `live-studio` |
| Option Key | `mf_lls_license` |
| Product ID Constant | `MF_LLS_PRODUCT_ID` |

**API Configuration:**
```php
$this->license_handler = new MF_LLS_License_Handler(
    array(
        'product_id'   => MF_LLS_PRODUCT_ID,
        'product_name' => 'LearnPress Live Studio',
        'api_url'      => 'https://mamflow.com/wp-json/mamflow/v1',
        'option_key'   => 'mf_lls_license',
    )
);
```

**Tab Registration:**
```php
$tabs['live-studio'] = array(
    'title'    => esc_html__( 'Live Studio', 'learnpress-live-studio' ),
    'callback' => 'mf_lls_render_license_tab',
    'priority' => 20,
);
```

---

## 5. Phase 2 — Future Features

> Không code trong Phase 1. Chỉ plan.

### Module 8: Advanced Features & Analytics

- [ ] Live chat/Q&A sync
- [ ] Multi-tutor/Co-host support
- [ ] Capacity limit + waiting list
- [ ] Analytics dashboard (engagement score, drop-off rate)
- [ ] Zapier/Webhook support cho third-party

### Platform Expansion

- [ ] Jitsi Meet (self-hosted) integration
- [ ] BigBlueButton integration

### License System

- [ ] Integrate Mamflow License System (theo `phase-2-plugin-integration-guide.md`)
- [ ] Feature gating cho premium features

---

## 6. Testing Checklist

### Unit Tests (Manual)

#### Admin Settings
- [ ] Save/load settings cho Zoom credentials
- [ ] Save/load settings cho Google Meet credentials
- [ ] Save/load settings cho Agora credentials
- [ ] Tab switching works
- [ ] Invalid credentials → proper error display
- [ ] Test connection button → success/fail feedback

#### Live Lesson
- [ ] Create lesson → enable live → save → verify meta saved
- [ ] Select platform → verify meta saved
- [ ] Set start_time, duration → verify meta saved
- [ ] Publish lesson → auto-create meeting → verify meeting_id stored
- [ ] Unpublish → meeting NOT deleted (preserve)

#### Platform APIs
- [ ] **Zoom:** Create meeting → verify join_url returned
- [ ] **Zoom:** Delete meeting → verify response
- [ ] **Zoom:** Get attendance → verify data format
- [ ] **Google Meet:** Create event → verify Meet link
- [ ] **Google Meet:** OAuth flow → token stored
- [ ] **Agora:** Generate channel → verify name format
- [ ] **Agora:** Generate token → verify token valid

#### Frontend
- [ ] Enrolled user sees join button (upcoming session, within 15min)
- [ ] Enrolled user sees live room (live session)
- [ ] Enrolled user sees recording (ended session)
- [ ] Non-enrolled user sees enrollment CTA
- [ ] Countdown timer accuracy (±1 second)
- [ ] Status auto-update on polling
- [ ] Mobile responsive layout

#### Attendance
- [ ] Join logged với correct timestamp
- [ ] Leave logged với correct timestamp
- [ ] Duration calculated correctly
- [ ] Webhook data matches local logs

#### Rating
- [ ] Form shows only after session ended + user attended
- [ ] Form NOT shown if already rated
- [ ] Form NOT shown if expired (>7 days)
- [ ] Submit rating → database record created
- [ ] Submit duplicate → error message
- [ ] Star validation: 1-5 only
- [ ] Comment sanitized (no HTML/script injection)
- [ ] Average calculation correct

#### Notifications
- [ ] Reminder email sent 1h before
- [ ] Reminder email sent 15min before
- [ ] Rating request email sent after session end
- [ ] Email templates render correctly
- [ ] No duplicate emails

#### Security
- [ ] AJAX without nonce → rejected
- [ ] Non-enrolled user join → rejected
- [ ] Non-admin access settings → rejected
- [ ] SQL injection attempts → blocked by prepare()
- [ ] XSS attempts → blocked by esc_*()

---

## 7. Deployment Checklist

### Pre-Launch
- [ ] Tất cả `var_dump` / `console.log` / `error_log` đã removed
- [ ] Text domain `learnpress-live-studio` consistent
- [ ] POT file generated
- [ ] README.txt completed
- [ ] Version numbers consistent (plugin header, constants)
- [ ] All SCSS compiled → assets/css/
- [ ] All JS minified → assets/js/
- [ ] No `SELECT *` queries
- [ ] No debug code
- [ ] ABSPATH check trong mỗi PHP file

### Compatibility
- [ ] Test với LearnPress 4.x latest
- [ ] Test với WordPress 6.x latest
- [ ] Test với PHP 8.0, 8.1, 8.2
- [ ] Test conflict với: LearnPress WooCommerce Payment, LP Instructor
- [ ] Test với popular caching plugins (W3 Total Cache, WP Super Cache)

### Production
- [ ] Staging environment test passed
- [ ] API keys set to production credentials
- [ ] Webhook URLs pointing to production domain
- [ ] Cron jobs verified running
- [ ] SSL verified for all API calls
- [ ] Error handling graceful (no white screen)

---

## Summary Statistics

| Category           | Total Tasks |
|--------------------|-------------|
| Module 1 (Core)    | 14          |
| Module 2 (Lesson)  | 12          |
| Module 3 (Platform)| 18          |
| Module 4 (Frontend)| 12          |
| Module 5 (Attend)  | 8           |
| Module 6 (Rating)  | 10          |
| Module 7 (Notify)  | 7           |
| Module 9 (Security)| 16          |
| Module 10 (License)| 38          |
| Testing            | 30          |
| Deployment         | 14          |
| **TOTAL**          | **~179**    |

---

> **Recommended Build Order:**
> Module 1 → Module 2 → Module 3 (Zoom first) → Module 4 → Module 9 (security pass) → Module 5 → Module 7 → Module 6 → **Module 10 (License)** → Test → Deploy
>
> **Estimated Timeline (1 developer):**
> - Module 1-2: ~3-4 days
> - Module 3 (all platforms): ~5-7 days
> - Module 4: ~3-4 days
> - Module 5: ~2-3 days
> - Module 6: ~2-3 days
> - Module 7: ~2 days
> - Module 9 + Testing: ~3-4 days
> - **Module 10 (License): ~2-3 days**
> - **Total: ~24-34 working days**

