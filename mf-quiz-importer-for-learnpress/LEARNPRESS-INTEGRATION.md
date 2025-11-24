# LearnPress Integration Guide

## Tích hợp với LearnPress

Plugin này đã được cập nhật để tương thích hoàn toàn với LearnPress 4.x và hỗ trợ fallback cho LearnPress 3.x.

## Cấu trúc Quiz trong LearnPress

### Quiz Post Type
- **Post Type**: `lp_quiz`
- **Status**: `publish` hoặc `draft` (tùy thuộc vào cài đặt Auto Publish)

### Quiz Metadata

| Meta Key | Mô tả | Giá trị | Ghi chú |
|----------|-------|---------|---------|
| `_lp_duration` | Thời gian làm bài | Số giây | Plugin tự động chuyển đổi từ phút sang giây |
| `_lp_passing_grade` | Điểm đạt | 0-100 | Phần trăm |
| `_lp_retake_count` | Số lần làm lại | -1 hoặc số dương | -1 = không giới hạn |
| `_lp_show_result` | Hiển thị kết quả | yes/no | Mặc định: yes |
| `_lp_show_check_answer` | Hiển thị đáp án đúng | yes/no | Mặc định: yes |
| `_lp_show_hint` | Hiển thị gợi ý | yes/no | Mặc định: yes |
| `_lp_review` | Cho phép xem lại | yes/no | Mặc định: yes |
| `_lp_negative_marking` | Trừ điểm câu sai | yes/no | Mặc định: no |
| `_lp_instant_check` | Kiểm tra ngay | yes/no | Mặc định: no |

## Cấu trúc Question trong LearnPress

### Question Post Type
- **Post Type**: `lp_question`
- **Status**: `publish`

### Question Metadata

| Meta Key | Mô tả | Giá trị |
|----------|-------|---------|
| `_lp_type` | Loại câu hỏi | `true_or_false`, `single_choice`, `multi_choice` |
| `_lp_mark` | Điểm số | Số dương (mặc định: 1) |
| `_lp_answer_options` | Các đáp án | Array với format đặc biệt |
| `_lp_correct` | Đáp án đúng | String (single) hoặc Array (multiple) |
| `_lp_explanation` | Giải thích | Text (tùy chọn) |

### Format Đáp Án (_lp_answer_options)

```php
array(
    'answer_12345' => array(
        'text' => 'Nội dung đáp án',
        'value' => 'true',  // hoặc 'false'
        'is_true' => 'yes'  // hoặc '' (empty string)
    ),
    'answer_67890' => array(
        'text' => 'Đáp án khác',
        'value' => 'false',
        'is_true' => ''
    )
)
```

**Lưu ý quan trọng:**
- Mỗi đáp án cần có ID duy nhất (ví dụ: `answer_12345`)
- `value` là string: `'true'` hoặc `'false'`
- `is_true` là string: `'yes'` hoặc `''` (empty string)

### Format Đáp Án Đúng (_lp_correct)

**Single Choice / True or False:**
```php
'answer_12345'  // ID của đáp án đúng
```

**Multiple Choice:**
```php
array('answer_12345', 'answer_67890')  // Array các ID đáp án đúng
```

## Quan hệ Quiz-Question

### LearnPress 4.x
Sử dụng bảng database: `wp_learnpress_quiz_questions`

```sql
CREATE TABLE wp_learnpress_quiz_questions (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    quiz_id bigint(20) NOT NULL,
    question_id bigint(20) NOT NULL,
    question_order int(11) NOT NULL,
    PRIMARY KEY (id)
)
```

Plugin tự động thêm câu hỏi vào bảng này với thứ tự tăng dần.

### LearnPress 3.x (Fallback)
Sử dụng post meta: `_lp_questions`

```php
array(123, 456, 789)  // Array các question IDs
```

## Ví dụ Import

### JSON Format - Single Choice

```json
{
  "title": "Quiz về WordPress",
  "description": "Kiểm tra kiến thức WordPress cơ bản",
  "duration": 30,
  "passing_grade": 70,
  "retake_count": 0,
  "questions": [
    {
      "title": "WordPress là gì?",
      "content": "Chọn đáp án đúng",
      "type": "single_choice",
      "answers": [
        {"text": "Hệ quản trị nội dung", "correct": true},
        {"text": "Ngôn ngữ lập trình", "correct": false},
        {"text": "Cơ sở dữ liệu", "correct": false}
      ]
    }
  ]
}
```

### JSON Format - Multiple Choice

```json
{
  "title": "Quiz về PHP",
  "description": "Kiểm tra kiến thức PHP nâng cao",
  "duration": 60,
  "passing_grade": 80,
  "retake_count": 2,
  "questions": [
    {
      "title": "Các kiểu dữ liệu trong PHP?",
      "content": "Chọn tất cả đáp án đúng",
      "type": "multiple_choice",
      "answers": [
        {"text": "String", "correct": true},
        {"text": "Integer", "correct": true},
        {"text": "Character", "correct": false},
        {"text": "Array", "correct": true}
      ]
    }
  ]
}
```

### JSON Format - True/False

```json
{
  "title": "Quiz về JavaScript",
  "description": "Câu hỏi đúng/sai",
  "duration": 15,
  "passing_grade": 75,
  "retake_count": 1,
  "questions": [
    {
      "title": "JavaScript là ngôn ngữ biên dịch",
      "content": "Đúng hay Sai?",
      "type": "true_or_false",
      "answers": [
        {"text": "Đúng", "correct": false},
        {"text": "Sai", "correct": true}
      ]
    }
  ]
}
```

## CSV Format

CSV chỉ hỗ trợ import quiz metadata, không hỗ trợ import câu hỏi.

```csv
title,description,duration,passing_grade,retake_count
"Quiz WordPress","Kiến thức cơ bản",30,70,0
"Quiz PHP","Kiến thức nâng cao",60,80,2
```

## Kiểm tra sau khi Import

1. Vào **LearnPress > Quizzes** để xem các quiz đã import
2. Mở từng quiz để kiểm tra:
   - Thông tin quiz (duration, passing grade, retake count)
   - Danh sách câu hỏi
   - Đáp án của từng câu hỏi
3. Test quiz bằng cách:
   - Thêm quiz vào một course
   - Làm thử quiz ở frontend
   - Kiểm tra kết quả và đáp án đúng

## Troubleshooting

### Quiz được tạo nhưng không có câu hỏi
- Kiểm tra format JSON có đúng không
- Kiểm tra field `questions` có phải là array không
- Xem error log trong WordPress: `wp-content/debug.log`

### Câu hỏi được tạo nhưng không hiển thị trong quiz
- Kiểm tra bảng `wp_learnpress_quiz_questions` có dữ liệu không
- Nếu dùng LearnPress 3.x, kiểm tra meta `_lp_questions`

### Đáp án không được đánh dấu đúng
- Kiểm tra format của `answers` trong JSON
- Đảm bảo field `correct` là boolean: `true` hoặc `false`
- Kiểm tra meta `_lp_correct` của question

### Duration không đúng
- Plugin tự động chuyển đổi từ phút sang giây
- Nếu nhập 30 phút, sẽ lưu là 1800 giây

## Debug Mode

Để bật debug mode, thêm vào `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Plugin sẽ ghi log lỗi vào `wp-content/debug.log`.

## API Reference

### MF_Quiz_Creator::create_quiz($quiz_data)

Tạo quiz từ dữ liệu.

**Parameters:**
- `$quiz_data` (array): Dữ liệu quiz

**Returns:**
- `int`: Quiz ID nếu thành công
- `WP_Error`: Nếu có lỗi

### MF_Quiz_Parser::validate_quiz_data($quiz_data)

Kiểm tra tính hợp lệ của dữ liệu quiz.

**Parameters:**
- `$quiz_data` (array): Dữ liệu quiz cần kiểm tra

**Returns:**
- `true`: Nếu hợp lệ
- `WP_Error`: Nếu không hợp lệ

### MF_Quiz_Parser::sanitize_quiz_data($quiz_data)

Làm sạch dữ liệu quiz.

**Parameters:**
- `$quiz_data` (array): Dữ liệu quiz cần làm sạch

**Returns:**
- `array`: Dữ liệu đã được làm sạch
