# Hướng dẫn Debug Plugin

## Bật Debug Mode trong WordPress

Thêm vào file `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

## Xem Log File

Log file sẽ được lưu tại: `wp-content/debug.log`

Để xem log realtime:

```bash
tail -f wp-content/debug.log
```

## Các Log Messages của Plugin

Plugin sẽ ghi log các sự kiện sau:

### 1. Quiz Creation
```
MF Quiz Importer: Successfully created quiz {quiz_id}
```

### 2. Question Creation
```
MF Quiz Importer: Created {count} questions for quiz {quiz_id}
MF Quiz Importer: Added question {question_id} to quiz {quiz_id}
```

### 3. Errors
```
MF Quiz Importer: Failed to create quiz post
MF Quiz Importer: Failed to create question - {error_message}
MF Quiz Importer: Failed to add question {question_id} to quiz {quiz_id}
MF Quiz Importer: Failed to set answers for question {question_id} - {error_message}
```

## Kiểm tra Database

### 1. Kiểm tra Quiz được tạo

```sql
SELECT * FROM wp_posts 
WHERE post_type = 'lp_quiz' 
ORDER BY ID DESC 
LIMIT 10;
```

### 2. Kiểm tra Quiz Meta

```sql
SELECT * FROM wp_postmeta 
WHERE post_id = {QUIZ_ID} 
AND meta_key LIKE '_lp_%';
```

### 3. Kiểm tra Questions được tạo

```sql
SELECT * FROM wp_posts 
WHERE post_type = 'lp_question' 
ORDER BY ID DESC 
LIMIT 10;
```

### 4. Kiểm tra Quiz-Question Relationship

```sql
SELECT * FROM wp_learnpress_quiz_questions 
WHERE quiz_id = {QUIZ_ID};
```

### 5. Kiểm tra Question Answers

```sql
SELECT * FROM wp_learnpress_question_answers 
WHERE question_id = {QUESTION_ID}
ORDER BY `order`;
```

### 6. Kiểm tra toàn bộ Quiz với Questions và Answers

```sql
SELECT 
    q.ID as quiz_id,
    q.post_title as quiz_title,
    qq.question_id,
    qq.question_order,
    p.post_title as question_title,
    qa.question_answer_id,
    qa.title as answer_text,
    qa.is_true,
    qa.order as answer_order
FROM wp_posts q
LEFT JOIN wp_learnpress_quiz_questions qq ON q.ID = qq.quiz_id
LEFT JOIN wp_posts p ON qq.question_id = p.ID
LEFT JOIN wp_learnpress_question_answers qa ON p.ID = qa.question_id
WHERE q.ID = {QUIZ_ID}
ORDER BY qq.question_order, qa.order;
```

## Các Vấn Đề Thường Gặp

### 1. Quiz được tạo nhưng không có câu hỏi

**Kiểm tra:**
```sql
SELECT COUNT(*) FROM wp_learnpress_quiz_questions WHERE quiz_id = {QUIZ_ID};
```

**Nguyên nhân có thể:**
- JSON format không đúng
- Field `questions` không phải array
- Lỗi khi tạo question (xem log)

**Giải pháp:**
- Kiểm tra file JSON có đúng format không
- Xem `wp-content/debug.log` để tìm lỗi cụ thể

### 2. Questions được tạo nhưng không có answers

**Kiểm tra:**
```sql
SELECT * FROM wp_learnpress_question_answers 
WHERE question_id = {QUESTION_ID};
```

**Nguyên nhân có thể:**
- Bảng `wp_learnpress_question_answers` không tồn tại
- Lỗi khi insert vào database

**Giải pháp:**
- Kiểm tra LearnPress đã được cài đặt và kích hoạt chưa
- Chạy lại LearnPress database upgrade

### 3. Import không có phản hồi gì

**Kiểm tra:**
- Mở Browser Console (F12) > Network tab
- Xem AJAX request có lỗi không
- Kiểm tra `wp-content/debug.log`

**Nguyên nhân có thể:**
- PHP error
- Memory limit
- Timeout

**Giải pháp:**
- Tăng PHP memory limit trong `wp-config.php`:
```php
define('WP_MEMORY_LIMIT', '256M');
```
- Tăng max execution time trong `.htaccess`:
```
php_value max_execution_time 300
```

### 4. Lỗi "LearnPress is not installed or activated"

**Kiểm tra:**
```php
var_dump(class_exists('LearnPress'));
var_dump(class_exists('LP_Quiz_CURD'));
var_dump(class_exists('LP_Question_CURD'));
```

**Giải pháp:**
- Cài đặt và kích hoạt LearnPress
- Đảm bảo LearnPress version >= 4.0

### 5. Answers không được đánh dấu đúng

**Kiểm tra:**
```sql
SELECT question_answer_id, title, is_true 
FROM wp_learnpress_question_answers 
WHERE question_id = {QUESTION_ID};
```

**Nguyên nhân:**
- Field `is_true` phải là 'yes' hoặc '' (empty string)
- Không phải 'true'/'false' hoặc 1/0

**Giải pháp:**
- Plugin đã xử lý đúng format này
- Nếu vẫn sai, kiểm tra JSON input

## Test Import

### 1. Test với file nhỏ

Tạo file `test-simple.json`:

```json
[
  {
    "title": "Test Quiz",
    "description": "Simple test",
    "duration": 10,
    "passing_grade": 70,
    "retake_count": 0,
    "questions": [
      {
        "title": "Test Question",
        "content": "Is this a test?",
        "type": "true_or_false",
        "answers": [
          {"text": "Yes", "correct": true},
          {"text": "No", "correct": false}
        ]
      }
    ]
  }
]
```

### 2. Import và kiểm tra

1. Import file `test-simple.json`
2. Xem log: `tail -f wp-content/debug.log`
3. Kiểm tra database:

```sql
-- Get quiz ID
SELECT ID, post_title FROM wp_posts 
WHERE post_type = 'lp_quiz' 
ORDER BY ID DESC LIMIT 1;

-- Check questions
SELECT * FROM wp_learnpress_quiz_questions 
WHERE quiz_id = {QUIZ_ID};

-- Check answers
SELECT qa.* FROM wp_learnpress_question_answers qa
INNER JOIN wp_learnpress_quiz_questions qq ON qa.question_id = qq.question_id
WHERE qq.quiz_id = {QUIZ_ID};
```

### 3. Test trên Frontend

1. Tạo một Course mới
2. Thêm quiz vào course
3. Publish course
4. Enroll vào course
5. Làm quiz và kiểm tra

## Công cụ Debug

### 1. Query Monitor Plugin

Cài đặt plugin Query Monitor để xem:
- Database queries
- PHP errors
- Hooks fired
- HTTP requests

### 2. Debug Bar Plugin

Cài đặt Debug Bar để xem:
- PHP errors
- SQL queries
- WP_Query
- Object cache

### 3. phpMyAdmin

Sử dụng phpMyAdmin để:
- Xem database structure
- Run SQL queries
- Export/Import data

## Liên hệ Support

Nếu vẫn gặp vấn đề sau khi debug:

1. Thu thập thông tin:
   - WordPress version
   - LearnPress version
   - PHP version
   - Error log từ `wp-content/debug.log`
   - File JSON đang import
   - Screenshots của lỗi

2. Tạo issue trên GitHub hoặc liên hệ support

## Checklist Debug

- [ ] Bật WP_DEBUG và WP_DEBUG_LOG
- [ ] Kiểm tra LearnPress đã kích hoạt
- [ ] Kiểm tra bảng database tồn tại
- [ ] Xem log file có lỗi gì
- [ ] Test với file JSON đơn giản
- [ ] Kiểm tra Browser Console
- [ ] Kiểm tra Network tab
- [ ] Kiểm tra PHP memory limit
- [ ] Kiểm tra file permissions
- [ ] Test trên frontend
