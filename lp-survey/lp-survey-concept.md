# 📊 LearnPress Add-on: LP Survey

## 1. Mục tiêu
LP Survey là add-on dành cho LearnPress giúp thu thập phản hồi học viên **ngay sau khi hoàn thành bài học (Lesson)** hoặc **hoàn thành khóa học (Course)**.  
Mục tiêu chính:
- Đo lường mức độ hiểu bài và trải nghiệm học tập
- Thu thập phản hồi đúng thời điểm, chính xác và có giá trị
- Hỗ trợ giảng viên cải thiện nội dung khóa học

---

## 2. Vấn đề cần giải quyết
- Giảng viên thiếu dữ liệu phản hồi trực tiếp từ học viên
- Feedback thường đến muộn hoặc rời rạc (email, form ngoài)
- Tỷ lệ phản hồi thấp do học viên không được nhắc đúng thời điểm

LP Survey giải quyết bằng cách tích hợp khảo sát trực tiếp vào flow học tập của LearnPress.

---

## 3. Flow tổng quát
Học viên hoàn thành lesson hoặc course  
→ Survey hiển thị (popup hoặc inline)  
→ Học viên trả lời nhanh (10–30 giây)  
→ Dữ liệu được lưu và tổng hợp  
→ Instructor/Admin xem báo cáo

---

## 4. Điểm kích hoạt Survey (Triggers)

### 4.1 Sau khi hoàn thành Lesson
- Hook LearnPress: `learn_press_user_complete_lesson`
- Mục tiêu:
  - Đánh giá mức độ hiểu bài
  - Phát hiện lesson khó, dài hoặc chưa rõ

### 4.2 Sau khi hoàn thành Course
- Hook LearnPress: `learn_press_user_finish_course`
- Mục tiêu:
  - Đánh giá tổng thể khóa học
  - Thu thập góp ý cải thiện nội dung

---

## 5. Các loại Survey

### 5.1 Lesson Survey (ngắn gọn)
Ví dụ câu hỏi:
- ⭐ Bài học này dễ hiểu không? (1–5)
- ⏱ Thời lượng bài học có phù hợp không?
- ❓ Phần nào bạn thấy khó hoặc chưa rõ?

---

### 5.2 Course Survey (tổng quan)
Ví dụ câu hỏi:
- ⭐ Đánh giá tổng thể khóa học
- 🎯 Khóa học có đúng kỳ vọng của bạn không?
- 👍 Bạn có sẵn sàng giới thiệu khóa học này không?
- 💬 Góp ý thêm cho giảng viên

---

## 6. Quyền & Đối tượng sử dụng

| Role | Quyền |
|----|----|
| Student | Trả lời survey |
| Instructor | Xem survey của khóa học mình |
| Admin | Xem tất cả survey |

---

## 7. Dashboard & Báo cáo

### 7.1 Tổng quan
- Tỷ lệ học viên phản hồi
- Điểm đánh giá trung bình
- Lesson/Course có điểm thấp nhất

### 7.2 Chi tiết
- Danh sách phản hồi theo lesson/course
- Lọc theo thời gian
- Xem nội dung phản hồi dạng text

---

## 8. Trải nghiệm người dùng (UX)

- Survey hiển thị dưới dạng:
  - Popup
  - Inline block dưới nút “Complete Lesson”
- Không bắt buộc trả lời
- Có thể bỏ qua hoặc nhắc lại sau
- Tối ưu cho mobile

---

## 9. Cấu hình Add-on

### 9.1 Global Settings
- Bật/tắt survey cho Lesson / Course
- Số câu hỏi tối đa
- Cho phép bỏ qua survey hay không

### 9.2 Per Course Settings
- Bật/tắt survey cho từng khóa học
- Chọn template survey

---

## 10. Database đề xuất

### Bảng `lp_surveys`
- id
- type (lesson | course)
- ref_id (lesson_id / course_id)
- created_at

### Bảng `lp_survey_questions`
- id
- survey_id
- type (rating | text | choice)
- content

### Bảng `lp_survey_answers`
- id
- survey_id
- question_id
- user_id
- answer
- created_at

---

## 11. Điểm mạnh của LP Survey
- Gắn chặt với flow học tập LearnPress
- Thu thập phản hồi đúng thời điểm
- Không cần tích hợp dịch vụ ngoài
- Dữ liệu trực tiếp phục vụ cải thiện khóa học

---

## 12. MVP đề xuất (Phase 1)
- Survey cho Lesson & Course
- Rating + Text question
- Dashboard thống kê cơ bản
- Không tích hợp AI
