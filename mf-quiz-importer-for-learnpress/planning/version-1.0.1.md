# Version 1.0.1 Plan - Stability And Compatibility

Version này chỉ tập trung sửa lỗi nền tảng. Không thêm feature lớn, không refactor lan rộng nếu không cần thiết.

## AI Execution Rules

AI hoặc developer thực hiện version này bắt buộc làm theo các quy tắc sau:

- Phải đọc file này trước khi code.
- Phải làm theo checklist từ trên xuống, trừ khi có lý do kỹ thuật rõ ràng.
- Không được đánh dấu `[x]` cho task chưa hoàn thành thật sự.
- Một task chỉ được đánh dấu `[x]` khi đã code xong và đã có bước kiểm tra phù hợp.
- Nếu phát hiện task mới trong quá trình làm, phải thêm vào mục `Discovered Follow-up Tasks` trước khi triển khai hoặc trước khi kết thúc phiên làm việc.
- Không được xóa checklist để làm báo cáo đẹp hơn.
- Không được bỏ qua license/security checks vì đang test local.
- Không được thay đổi format import public-facing nếu chưa cập nhật docs/UI/sample tương ứng.
- Không thêm dependency mới cho XLS parser trong `1.0.1`; hướng xử lý version này là bỏ `.xls` khỏi UI/docs/validation.
- Không được đổi behavior import quiz đầy đủ nếu không liên quan trực tiếp đến bugfix.
- Sau khi hoàn thành, phải cập nhật mục `Completion Checklist` ở cuối file.

## Release Goal

Mục tiêu của `1.0.1`:

- Questions-only import phải hoạt động đúng với LearnPress 4.
- Plugin không còn quảng cáo hỗ trợ `.xls` khi parser không hỗ trợ thật.
- CSV import không phát sinh warning khi row thiếu/thừa cột.
- Upload không reject nhầm CSV/JSON hợp lệ vì MIME khác nhau giữa browser/hosting.
- License inactive không thể upload hoặc import qua AJAX.

## Current Problems

### 1. Questions-only Import Uses Old LearnPress Meta

Hiện trạng:

- Complete quiz import dùng `MF_Quiz_Creator`.
- `MF_Quiz_Creator` tạo answers bằng bảng `learnpress_question_answers`.
- `MF_Quiz_Creator` attach question vào quiz qua `LP_Quiz_CURD::add_question()` hoặc bảng `learnpress_quiz_questions`.
- Questions-only import dùng `MF_Question_Importer`.
- `MF_Question_Importer` lưu answers vào `_lp_answer_options`, `_lp_answer` và attach quiz bằng `_lp_questions`.

Rủi ro:

- Questions import vào quiz có sẵn có thể không hoạt động đúng trong LearnPress 4.
- Correct answers có thể không được LearnPress nhận.
- Quiz admin/frontend có thể không hiển thị questions đúng.

Files:

- `includes/class-question-importer.php`
- `includes/class-quiz-creator.php`
- `includes/admin/class-importer.php`

### 2. XLS Support Is Misleading

Hiện trạng:

- UI cho phép `.xls` trong `accept`.
- UI text ghi `Excel (XLSX/XLS)`.
- `MF_Quiz_Importer::import_from_file()` route `.xls` vào Excel parser.
- `MF_Excel_Parser::parse_xls()` trả lỗi `xls_not_supported`.

Rủi ro:

- User upload `.xls` vì UI nói có hỗ trợ, sau đó import fail.
- Gây mất niềm tin vào plugin.

Files:

- `includes/admin/views/importer-page.php`
- `includes/admin/class-admin.php`
- `includes/admin/class-importer.php`
- `includes/class-excel-parser.php`
- `README.md`
- `IMPORT-GUIDE.md`
- `QUICK-START.md`
- `UI-GUIDE.md`
- `samples/EXCEL-GUIDE.md`
- `readme.txt`

### 3. CSV Row Handling Can Fail

Hiện trạng:

- CSV import dùng `array_combine($header, $row)` trực tiếp.
- Nếu row thiếu hoặc thừa cột, PHP có thể warning và dữ liệu bị `false`.
- Error reporting chưa chỉ rõ lỗi ở dòng nào.

Rủi ro:

- Import fail khó hiểu.
- Một dòng lỗi có thể ảnh hưởng toàn bộ import.
- User khó tự sửa CSV.

Files:

- `includes/admin/class-importer.php`
- `includes/class-quiz-parser.php`

### 4. MIME Validation Is Too Strict

Hiện trạng:

- Upload validation đang phụ thuộc vào `$_FILES['file']['type']`.
- CSV/JSON hợp lệ có thể được browser/server gửi thành `text/plain` hoặc `application/octet-stream`.

Rủi ro:

- File hợp lệ bị reject trên một số hosting.
- Support ticket tăng vì lỗi không tái hiện được trên mọi máy.

Files:

- `includes/admin/class-admin.php`

### 5. AJAX Actions Bypass License Gate

Hiện trạng:

- Admin page có license gate ở `render_admin_page()`.
- AJAX `mf_quiz_importer_upload` và `mf_quiz_importer_process` chỉ check nonce + capability.

Rủi ro:

- License inactive vẫn có thể gọi AJAX upload/process nếu có nonce hợp lệ.
- License gate chỉ khóa UI, chưa khóa backend business action.

Files:

- `includes/admin/class-admin.php`
- `mf-quiz-importer-for-learnpress.php`

## Implementation Checklist

### Phase 1 - Prepare And Verify Scope

- [x] Read `planning/roadmap.md`.
- [x] Read this file fully.
- [x] Inspect current code in `includes/admin/class-importer.php`.
- [x] Inspect current code in `includes/class-question-importer.php`.
- [x] Inspect current code in `includes/class-quiz-creator.php`.
- [x] Inspect current code in `includes/admin/class-admin.php`.
- [x] Inspect current UI in `includes/admin/views/importer-page.php`.
- [x] Confirm plugin version is still `1.0.0` before making release changes.

### Phase 2 - Fix Questions-only Import For LearnPress 4

- [x] Make question creation logic reusable between complete quiz import and questions-only import.
- [x] Ensure questions-only import stores answers in `learnpress_question_answers` for LearnPress 4.
- [x] Ensure questions-only import attaches questions through `LP_Quiz_CURD::add_question()` when available.
- [x] Ensure fallback attach uses `learnpress_quiz_questions`, not `_lp_questions` post meta.
- [x] Preserve validation for invalid target quiz ID.
- [x] Preserve supported question type mapping: `true_or_false`, `single_choice`, `multi_choice`, `fill_in_blanks` if currently supported.
- [x] Preserve answer parsing from CSV columns `answer_1`, `answer_2`, etc.
- [x] Preserve JSON questions format with `answers: [{"text":"...","correct":true}]`.
- [x] Return row/question-level errors without stopping successful valid questions where possible.
- [ ] Verify import questions-only with CSV into an existing quiz.
- [ ] Verify import questions-only with JSON into an existing quiz.
- [ ] Verify imported questions appear in LearnPress quiz admin.
- [ ] Verify correct answers are recognized by LearnPress.

### Phase 3 - Remove Misleading XLS Support

- [x] Remove `.xls` from file input `accept` attributes.
- [x] Change UI copy from `XLSX/XLS` to `XLSX`.
- [x] Change upload error copy to supported formats: `CSV, XLSX, JSON`.
- [x] Remove `.xls` routing from `MF_Quiz_Importer::import_from_file()` or return a clear unsupported message before parser routing.
- [x] Remove `.xls` MIME handling if it only exists for legacy XLS.
- [x] Update docs that mention XLS support.
- [x] Update sample guide to say legacy `.xls` is not supported in `1.0.1`.
- [ ] Verify `.xls` cannot be selected via UI accept filter where browser enforces it.
- [ ] Verify `.xls` upload is rejected with clear message if submitted manually.
- [ ] Verify `.xlsx` still imports.

### Phase 4 - Improve CSV Row Handling

- [x] Add a helper to normalize CSV row length against header length.
- [x] Pad missing row columns with empty strings.
- [x] Trim extra row columns or report row-level warning/error consistently.
- [x] Skip fully empty rows.
- [x] Validate that header exists and is not empty.
- [x] Validate required fields per import mode.
- [x] Include row number in errors, using real CSV line number where possible.
- [x] Avoid direct `array_combine()` calls on unnormalized rows.
- [x] Update questions-only CSV parsing to use normalized rows.
- [x] Update complete quiz CSV parsing to use normalized rows.
- [x] Update simple quiz CSV parsing to use normalized rows.
- [ ] Verify CSV with missing trailing columns does not produce PHP warning.
- [ ] Verify CSV with extra columns produces useful feedback or safe handling.
- [ ] Verify CSV with empty lines is skipped.

### Phase 5 - Improve Extension And MIME Validation

- [x] Validate uploaded file by extension first.
- [x] Allow only `csv`, `xlsx`, `json` in `1.0.1`.
- [x] Treat MIME as secondary signal, not the only source of truth.
- [x] Allow common CSV MIME values including `text/csv`, `text/plain`, `application/csv`, `application/vnd.ms-excel`, `application/octet-stream` when extension is `.csv`.
- [x] Allow common JSON MIME values including `application/json`, `text/plain`, `application/octet-stream` when extension is `.json`.
- [x] Allow valid XLSX MIME values when extension is `.xlsx`.
- [x] Reject dangerous extensions regardless of MIME.
- [x] Sanitize uploaded filename as currently done.
- [ ] Verify CSV uploaded as `text/plain` is accepted.
- [ ] Verify JSON uploaded as `application/octet-stream` is accepted.
- [ ] Verify PHP or unknown extension is rejected.

### Phase 6 - Enforce License Checks On AJAX Actions

- [x] Add a reusable license check method in admin class or use plugin instance directly.
- [x] Check license inside `handle_file_upload()` after nonce/capability check and before processing file.
- [x] Check license inside `handle_import_process()` after nonce/capability check and before reading/importing file.
- [x] Return clear JSON error when license inactive.
- [x] Keep existing admin page license gate.
- [ ] Verify inactive license blocks upload AJAX.
- [ ] Verify inactive license blocks process AJAX.
- [ ] Verify active license allows upload/import.

### Phase 7 - Version, Docs, And Release Notes

- [x] Update plugin header version from `1.0.0` to `1.0.1`.
- [x] Update `MF_QUIZ_IMPORTER_VERSION` from `1.0.0` to `1.0.1`.
- [x] Update `CHANGELOG.md` with `1.0.1` notes.
- [x] Update `RELEASE-NOTES.md` if this repo uses it for releases.
- [x] Ensure docs mention supported formats as `CSV, XLSX, JSON` only.
- [x] Ensure docs mention `.xls` should be saved as `.xlsx` or `.csv`.
- [x] Ensure docs mention LearnPress 4 compatibility for questions-only import.

### Phase 8 - Final Verification

- [x] Run PHP syntax check on changed PHP files.
- [ ] Test complete quiz import from CSV.
- [ ] Test complete quiz import from JSON.
- [ ] Test complete quiz import from XLSX.
- [ ] Test questions-only import from CSV.
- [ ] Test questions-only import from JSON.
- [ ] Test questions-only import from XLSX.
- [ ] Test invalid CSV row handling.
- [ ] Test unsupported `.xls` rejection.
- [ ] Test license inactive behavior.
- [ ] Test license active behavior.
- [ ] Confirm no unrelated files were changed.

## Suggested Technical Approach

### Reuse LearnPress 4 Question Logic

Preferred approach:

- Move or expose reusable question methods from `MF_Quiz_Creator`.
- Let `MF_Question_Importer::import_questions()` call the same LearnPress 4-compatible methods.
- Avoid duplicate implementations for answers and quiz attachment.

Acceptable minimal approach:

- Keep `MF_Question_Importer`, but replace old meta logic with LearnPress 4 table logic copied carefully from `MF_Quiz_Creator`.
- This is acceptable only if method visibility/refactor risk is too high.

Not allowed in `1.0.1`:

- Keeping `_lp_questions` as primary attach mechanism.
- Keeping `_lp_answer_options`/`_lp_answer` as primary answer storage for LearnPress 4.
- Adding a new external dependency just for XLS.

### CSV Normalization Rules

Expected behavior:

- Header missing: fail import with clear error.
- Empty row: skip.
- Missing trailing cells: fill with empty string.
- Extra cells: do not crash; either trim or report row-level error.
- Required field missing: report row-level error.

### License Rules

Expected behavior:

- License inactive means no upload and no import processing.
- UI gate alone is not enough.
- AJAX must be protected even when request is crafted manually.

## Manual Test Matrix

| Area | Case | Expected Result | Done |
| --- | --- | --- | --- |
| Complete quiz CSV | Valid quiz with 3 questions | Quiz created, questions attached, answers correct | [ ] |
| Complete quiz JSON | Valid quiz with questions array | Quiz created, questions attached, answers correct | [ ] |
| Complete quiz XLSX | Valid sheet format | Quiz created/imported | [ ] |
| Questions-only CSV | Valid questions into existing quiz | Questions appear in quiz using LearnPress 4 tables | [ ] |
| Questions-only JSON | Valid questions into existing quiz | Questions appear in quiz using LearnPress 4 tables | [ ] |
| Questions-only XLSX | Valid sheet format | Questions imported into selected quiz | [ ] |
| CSV row missing cell | Missing optional trailing value | No PHP warning; row handled safely | [ ] |
| CSV row missing required title | Missing title | Row-level error shown | [ ] |
| CSV extra column | Extra delimiter | No PHP warning; useful error or safe trim | [ ] |
| XLS upload | Legacy `.xls` | Rejected with clear message | [ ] |
| MIME CSV text/plain | Valid `.csv` with `text/plain` | Accepted | [ ] |
| MIME JSON octet-stream | Valid `.json` with `application/octet-stream` | Accepted | [ ] |
| License inactive upload | AJAX upload | Blocked | [ ] |
| License inactive process | AJAX process | Blocked | [ ] |
| License active | Normal import | Allowed | [ ] |

## Completion Checklist

- [ ] All implementation checklist items are completed or moved to follow-up with reason.
- [ ] All manual test matrix items relevant to changed code are checked.
- [x] `CHANGELOG.md` updated.
- [x] Plugin version updated to `1.0.1`.
- [x] No `.xls` support claim remains in UI/docs unless explicitly marked unsupported.
- [x] Questions-only import uses LearnPress 4-compatible storage and quiz attachment.
- [x] AJAX actions enforce license status.
- [ ] Final response/report includes changed files and tests performed.

## Discovered Follow-up Tasks

Add new tasks here if found during implementation.

- [ ] Fix local ESLint/tooling mismatch: `npm run lint:js` currently fails because the installed ESLint does not recognize `.eslintrc.json` env key `es2021`.
