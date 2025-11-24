# Hướng dẫn Test Plugin

## Chuẩn bị

1. **Cài đặt WordPress** (phiên bản 5.8 trở lên)
2. **Cài đặt LearnPress** (khuyến nghị phiên bản 4.x)
3. **Kích hoạt plugin** Quiz Importer For LearnPress

## Các bước Test

### 1. Test Import CSV (Chỉ Quiz Metadata)

1. Vào **LearnPress > Quiz Importer**
2. Upload file `samples/sample-quiz.csv`
3. Click **Import Quizzes**
4. Kiểm tra kết quả:
   - Số quiz đã import: 5
   - Vào **LearnPress > Quizzes** để xem danh sách

**Kết quả mong đợi:**
- 5 quiz được tạo với status là Draft (hoặc Published nếu bật Auto Publish)
- Mỗi quiz có đầy đủ thông tin: title, description, duration, passing grade, retake count

### 2. Test Import JSON (Quiz + Questions)

1. Vào **LearnPress > Quiz Importer**
2. Upload file `samples/sample-quiz.json`
3. Click **Import Quizzes**
4. Kiểm tra kết quả:
   - Số quiz đã import: 3
   - Mỗi quiz có câu hỏi

**Kết quả mong đợi:**
- 3 quiz được tạo
- Quiz "Introduction to WordPress" có 4 câu hỏi (bao gồm 1 multiple choice)
- Quiz "Advanced PHP Programming" có 3 câu hỏi
- Quiz "JavaScript Fundamentals" có 3 câu hỏi

### 3. Test Question Types

#### Test True/False Question

1. Mở quiz "Introduction to WordPress"
2. Tìm câu hỏi "WordPress is open source"
3. Kiểm tra:
   - Type: True or False
   - Có 2 đáp án: True và False
   - Đáp án đúng: True

#### Test Single Choice Question

1. Mở quiz "Introduction to WordPress"
2. Tìm câu hỏi "What is WordPress?"
3. Kiểm tra:
   - Type: Single Choice
   - Có 4 đáp án
   - Chỉ 1 đáp án đúng: "A content management system"

#### Test Multiple Choice Question

1. Mở quiz "Introduction to WordPress"
2. Tìm câu hỏi "Select all features of WordPress"
3. Kiểm tra:
   - Type: Multiple Choice
   - Có 4 đáp án
   - 3 đáp án đúng: "Plugin system", "Theme support", "User management"

### 4. Test Quiz Settings

1. Mở bất kỳ quiz nào đã import
2. Kiểm tra các settings:
   - **Duration**: Phải đúng với giá trị trong file (tính bằng giây)
   - **Passing Grade**: Phải đúng với giá trị trong file (%)
   - **Retake Count**: 
     - Nếu file có giá trị 0 → hiển thị "Unlimited" (-1)
     - Nếu file có giá trị > 0 → hiển thị đúng số đó

### 5. Test Frontend

1. Tạo một Course mới
2. Thêm quiz đã import vào course
3. Publish course
4. Đăng nhập với tài khoản student
5. Enroll vào course
6. Làm quiz và kiểm tra:
   - Câu hỏi hiển thị đúng
   - Đáp án hiển thị đúng
   - Submit được
   - Kết quả hiển thị đúng
   - Đáp án đúng được highlight

### 6. Test Error Handling

#### Test Invalid File Type

1. Upload file .txt hoặc .pdf
2. Kiểm tra: Phải hiển thị lỗi "Invalid file type"

#### Test Invalid JSON

1. Tạo file JSON với syntax sai
2. Upload file
3. Kiểm tra: Phải hiển thị lỗi "Invalid JSON format"

#### Test Missing Required Fields

1. Tạo file JSON không có field `title`
2. Upload file
3. Kiểm tra: Quiz không được tạo, hiển thị số failed

### 7. Test Settings

1. Vào **LearnPress > Quiz Importer**
2. Scroll xuống phần **Settings**
3. Thay đổi các giá trị:
   - Default Quiz Duration: 45
   - Default Passing Grade: 80
   - Default Retake Count: 3
   - Auto Publish: Checked
4. Click **Save Changes**
5. Upload file CSV (không có duration, passing_grade, retake_count)
6. Kiểm tra: Quiz mới phải sử dụng giá trị mặc định từ settings

### 8. Test Database Structure

#### Kiểm tra LearnPress 4.x

Chạy query SQL:

```sql
SELECT * FROM wp_learnpress_quiz_questions 
WHERE quiz_id = [QUIZ_ID];
```

**Kết quả mong đợi:**
- Có records với quiz_id và question_id
- question_order tăng dần (1, 2, 3, ...)

#### Kiểm tra Post Meta

```sql
SELECT * FROM wp_postmeta 
WHERE post_id = [QUIZ_ID] 
AND meta_key LIKE '_lp_%';
```

**Kết quả mong đợi:**
- `_lp_duration`: Giá trị tính bằng giây
- `_lp_passing_grade`: 0-100
- `_lp_retake_count`: -1 hoặc số dương
- Các meta khác: `_lp_show_result`, `_lp_review`, etc.

#### Kiểm tra Question Meta

```sql
SELECT * FROM wp_postmeta 
WHERE post_id = [QUESTION_ID] 
AND meta_key = '_lp_answer_options';
```

**Kết quả mong đợi:**
- Serialized array với format:
```php
array(
    'answer_xxxxx' => array(
        'text' => '...',
        'value' => 'true' or 'false',
        'is_true' => 'yes' or ''
    )
)
```

## Checklist

- [ ] CSV import hoạt động
- [ ] JSON import hoạt động
- [ ] True/False questions được tạo đúng
- [ ] Single Choice questions được tạo đúng
- [ ] Multiple Choice questions được tạo đúng
- [ ] Quiz settings được lưu đúng
- [ ] Questions được thêm vào quiz
- [ ] Frontend hiển thị quiz đúng
- [ ] Submit quiz hoạt động
- [ ] Kết quả hiển thị đúng
- [ ] Error handling hoạt động
- [ ] Settings được lưu và áp dụng
- [ ] Database structure đúng với LearnPress 4.x

## Debug

Nếu có lỗi, kiểm tra:

1. **WordPress Debug Log**: `wp-content/debug.log`
2. **Browser Console**: F12 > Console tab
3. **Network Tab**: F12 > Network tab (kiểm tra AJAX requests)
4. **Database**: Kiểm tra trực tiếp trong phpMyAdmin

## Lỗi thường gặp

### Quiz được tạo nhưng không có câu hỏi

**Nguyên nhân:**
- JSON format không đúng
- Field `questions` không phải array
- Question data thiếu field bắt buộc

**Giải pháp:**
- Kiểm tra JSON syntax
- Validate JSON trước khi upload
- Xem debug log

### Đáp án không được đánh dấu đúng

**Nguyên nhân:**
- Format `answers` không đúng
- Field `correct` không phải boolean

**Giải pháp:**
- Đảm bảo `correct` là `true` hoặc `false` (không phải string)
- Kiểm tra meta `_lp_correct` trong database

### Duration không đúng

**Nguyên nhân:**
- Plugin chuyển đổi từ phút sang giây

**Giải pháp:**
- Đây là behavior đúng của plugin
- LearnPress 4.x lưu duration bằng giây
- Frontend sẽ hiển thị đúng

## Kết luận

Sau khi test xong tất cả các bước trên, plugin sẽ hoạt động đúng với LearnPress 4.x và có thể import quiz + questions từ CSV và JSON.
