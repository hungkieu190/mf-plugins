Tổng quan Addon

Tên: LearnPress Live Studio
Mô tả: Addon cho LearnPress hỗ trợ tạo và quản lý khóa học livestream (live courses), tích hợp các nền tảng video conferencing tốt nhất, với tính năng rating tutor sau buổi học.
Phiên bản mục tiêu ban đầu: 1.0.0 (Phase 1: Zoom + Google Meet + Agora; Phase 2: thêm Jitsi/BigBlueButton)
Yêu cầu hệ thống: WordPress + LearnPress 4.x+, PHP 8+, API keys từ các nền tảng.

Cấu trúc Module (chia nhỏ)
Module 1: Core & Admin Settings

Mô tả: Xây dựng nền tảng addon, settings page, activation hook.
Tính năng chính:
Tạo custom post type cho "Live Session" (nếu cần) hoặc extend lesson type của LearnPress.
Trang settings admin: Chọn nền tảng mặc định, nhập API keys (Zoom JWT/OAuth, Google OAuth, Agora App ID/Key, v.v.).
Tab config cho từng nền tảng (enable/disable, credentials).
Template email notification (reminder, rating request).

Dependencies: LearnPress core.
Công việc gợi ý:
Tạo class LP_Live_Studio_Admin extend LP_Addon.
Sử dụng LP()->settings() để lưu config.
Hook admin_menu để thêm submenu dưới LearnPress.


Module 2: Live Lesson Type Integration

Mô tả: Thêm loại lesson mới "Live Session" vào curriculum của course.
Tính năng chính:
Trong metabox lesson: Chọn loại "Live", chọn nền tảng, nhập title/time/duration.
Tự động tạo room/meeting khi publish lesson hoặc cron job trước X giờ.
Hiển thị countdown timer, status (upcoming, live, ended).

Dependencies: Module 1.
Công việc gợi ý:
Extend LP_Lesson hoặc dùng filter learn_press_lesson_types.
Metabox fields: platform_select, meeting_id (auto-generated), start_time, duration, participant_limit.
AJAX để test/create meeting.


Module 3: Platform Integrations (Sub-modules riêng cho từng nền tảng)

Sub 3.1: Zoom Integration
Sử dụng Zoom API (JWT hoặc Server-to-Server OAuth).
Tạo meeting/webinar, get join_url, embed (iframe hoặc SDK).
Sync attendance via webhook.

Sub 3.2: Google Meet Integration
Sử dụng Google Calendar API + Meet add-on.
Tạo event → generate meet link.
Invite enrolled users qua email/calendar.

Sub 3.3: Agora Integration
Sử dụng Agora RTC SDK (Web) để embed interactive classroom.
Tạo channel động, token generation.
Hỗ trợ low-latency, hand-raising, co-host.

Dependencies: Module 2.
Công việc gợi ý:
Abstract class LP_Live_Platform với methods: create_room(), get_join_url(), end_session(), get_attendance().
Child classes cho từng platform.
Lưu meeting data vào post meta (lp_live_data).


Module 4: Embedding & Frontend Display

Mô tả: Hiển thị live room trong trang lesson/course.
Tính năng chính:
Embed iframe/SDK trực tiếp (responsive, mobile-friendly).
Shortcode [lp_live_room lesson_id="X"] để chèn bất kỳ đâu.
Waiting room, join button chỉ cho enrolled users.
Countdown + live indicator (sử dụng JS polling hoặc WebSocket nếu có).

Dependencies: Module 2 & 3.
Công việc gợi ý:
Template override learnpress/single-course/lesson.php cho live type.
Enqueue JS/CSS riêng (Agora SDK, Zoom Web SDK nếu cần).
Check enrollment + time để hiển thị join button.


Module 5: Attendance & Recording

Mô tả: Theo dõi tham gia và lưu recording.
Tính năng chính:
Log attendance (user_id, join_time, duration) → lưu vào custom table hoặc user meta.
Auto recording (nếu platform hỗ trợ) → upload về lesson như video bổ sung.
Report dashboard: % attendance per session.

Dependencies: Module 3.
Công việc gợi ý:
Hook webhook từ platform (Zoom webhook, Agora events).
Cron job để sync attendance sau session end.
Table wp_lp_live_attendance (user_id, lesson_id, join_time, leave_time).


Module 6: Feedback & Tutor Rating (Tính năng chính bạn yêu cầu)

Mô tả: Rating tutor sau buổi live.
Tính năng chính:
Sau session end: Gửi email/notification cho participants (dựa attendance).
Form rating trong course page (star 1-5 cho tutor + content, textarea comment).
Limit 1 lần/user/session, expire sau 7 ngày.
Hiển thị average rating trên tutor profile (nếu có addon Instructor).
Tutor xem feedback cá nhân trong dashboard.
Admin export CSV report.

Dependencies: Module 5 (dùng attendance để biết ai tham gia).
Công việc gợi ý:
Custom table wp_lp_live_ratings (user_id, lesson_id, tutor_id, rating_stars, comment, created_at).
AJAX form submit rating.
Hook learn_press_after_single_lesson để hiển thị form nếu session ended.


Module 7: Notifications & Reminders

Mô tả: Gửi thông báo trước/sau live.
Tính năng chính:
Reminder: 1 giờ, 15 phút trước (email + optional SMS via addon khác).
Post-session: Thank you + rating request email.
Custom template cho email.

Dependencies: Module 2.
Công việc gợi ý:
Sử dụng WP Cron để schedule reminders.
wp_mail() với template từ settings.


Module 8: Advanced Features & Analytics

Mô tả: Tính năng nâng cao (có thể phase 2).
Tính năng chính:
Live chat/Q&A/polling sync (nếu platform hỗ trợ).
Multi-tutor/co-host.
Capacity limit + waiting list.
Analytics: engagement score, drop-off rate.
Zapier/Webhook support.

Dependencies: Core modules.
Công việc gợi ý:
Thêm tab analytics trong tutor/admin dashboard.
Extend cho phase 2 platforms (Jitsi self-hosted, BigBlueButton).


Module 9: Security & Optimization

Mô tả: Cross-cutting concerns.
Tính năng chính:
Authentication: Chỉ enrolled users join.
Rate limiting API calls.
GDPR compliant (opt-out recording, data delete).
Mobile responsive + performance (lazy load SDK).

Dependencies: All modules.
Công việc gợi ý:
Nonce cho AJAX/forms.
Cache meeting links.