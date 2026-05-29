# LP Sticky Notes Roadmap

Tai lieu nay tom tat tam nhin phat trien cac phien ban tiep theo cua plugin **Sticky Notes Add-on for LearnPress**.

Muc tieu san pham: bien plugin tu cong cu ghi chu co ban trong lesson thanh mot he thong hoc tap ca nhan hoa, giup hoc vien ghi nho, on tap, tim lai kien thuc va cho phep instructor/admin nam duoc muc do tuong tac cua hoc vien.

## Dinh Huong Chinh

- On dinh trai nghiem ghi chu trong lesson, profile, shortcode va admin.
- Tang kha nang tim kiem, sap xep, loc va quan ly ghi chu khi du lieu lon.
- Cai thien highlight de gan chat voi noi dung bai hoc, de hoc vien quay lai dung ngu canh.
- Bo sung tinh nang hoc tap nang cao: tag, pin, review, share voi instructor.
- Nang cap admin/instructor dashboard de san pham co gia tri hon cho LMS owner.
- Dam bao performance, security, migration va privacy tot hon cho ban tra phi.

## v1.0.6 - Stability & Bug Fix Release

Muc tieu: sua cac loi anh huong truc tiep den hanh vi hien tai truoc khi them tinh nang moi.

### Priority High

- Fix edit note bi tao note moi: frontend can goi `lp_sticky_notes_update` khi co `note_id` thay vi luon goi `lp_sticky_notes_add`.
- Fix sidebar lesson notes bi lan giua cac course neu cung mot lesson duoc dung trong nhieu course: query can loc theo ca `course_id` va `lesson_id`.
- Dong nhat license gate cho Profile tab `My Notes`: an tab hoac hien thong bao license required khi license chua active.
- Fix instructor/admin menu capability: instructor dang duoc cho phep trong render page nhung co the khong thay menu vi submenu dung `manage_options`.
- Chuan hoa cac URL license trong admin de tranh link chet hoac dan sai trang.

### Priority Medium

- Cai thien message loi AJAX de user biet nguyen nhan ro hon.
- Kiem tra lai shortcode, modal View All va profile voi lesson duoc gan vao nhieu course.
- Dam bao tat ca trang frontend/admin chi load CSS/JS khi thuc su can.

### Technical

- Them DB version option de ho tro migration cho cac phien ban sau.
- Them composite indexes can thiet cho bang notes:
  - `(user_id, course_id, lesson_id)`
  - `(user_id, created_at)`
  - `(course_id, lesson_id)`
- Kiem tra release zip khong chua file dev khong can thiet.

## v1.1.0 - Search, Filter & Better Note Management

Muc tieu: giup hoc vien va admin tim lai ghi chu nhanh khi so luong notes tang.

### Student Features

- Them search trong Profile tab va modal View All Notes.
- Search theo note content, highlighted text, course title va lesson title.
- Filter theo course, lesson, note type va khoang thoi gian.
- Sort theo newest, oldest, course, lesson va updated date.
- Them pagination hoac load more cho Profile tab va modal View All Notes.
- Them pin/favorite note de day ghi chu quan trong len dau.

### Shortcode Improvements

- Them shortcode attributes moi:
  - `type="text|highlight|all"`
  - `orderby="created_at|updated_at|course|lesson"`
  - `order="asc|desc"`
  - `paged="yes|no"`
- Ho tro hien thi grouped view theo Course -> Lesson.
- Them search/filter UI optional cho shortcode.

### Admin Improvements

- Nang cap Student Notes admin page voi search keyword.
- Them filter note type va date range.
- Them sort theo course, lesson, student, created date.
- Them bulk export theo filter hien tai.

## v1.2.0 - Highlight Experience Upgrade

Muc tieu: bien highlight thanh tinh nang cot loi, khong chi la text duoc luu kem note.

### Highlight Reliability

- Luu them context truoc/sau highlighted text de tim lai vi tri khi noi dung lesson thay doi nhe.
- Re-apply highlight vao noi dung lesson khi load lesson.
- Xu ly truong hop mot doan text xuat hien nhieu lan trong lesson.
- Hien trang thai khi highlight khong con tim thay trong lesson.

### UX Improvements

- Click note highlight de scroll toi doan duoc highlight trong lesson.
- Hover/click highlight trong lesson de mo note tuong ung.
- Cho phep doi mau highlight theo note hoac theo tag.
- Them option tat/bat highlight rendering trong lesson.

### Mobile

- Toi uu highlight popup tren mobile.
- Cai thien sidebar thanh bottom sheet tren mobile neu can.
- Dam bao sticky button khong che navigation cua LearnPress.

## v1.3.0 - Organization: Tags, Folders & Review

Muc tieu: giup hoc vien bien notes thanh he thong on tap co to chuc.

### Tags & Organization

- Them tags cho notes, vi du: `important`, `exam`, `question`, `todo`.
- Filter/search theo tag.
- Them folder hoac collection neu nhu feedback nguoi dung can quan ly nang cao.
- Cho phep doi mau note/card theo tag.

### Review Mode

- Them trang Review Notes gom cac note da pin, note co tag quan trong hoac note duoc danh dau review later.
- Them trang thai note:
  - `new`
  - `review later`
  - `learned`
- Them sort theo ngay can review.

### Export

- Export notes theo tag, course, lesson hoac review status.
- Cai thien template PDF/print de phu hop viec on tap.

## v1.4.0 - Instructor & Admin Value

Muc tieu: tang gia tri cho nguoi ban khoa hoc, instructor va admin LMS.

### Instructor Dashboard

- Dashboard thong ke notes theo course.
- Hien lessons duoc highlight/ghi chu nhieu nhat.
- Hien students co muc do ghi chu cao/thap.
- Loc notes theo instructor-owned courses neu LearnPress ho tro.

### Shared Notes

- Them option cho hoc vien share note voi instructor.
- Note mac dinh van private, chi shared khi hoc vien chu dong bat.
- Instructor co the xem danh sach shared notes theo course/lesson/student.
- Them status cho shared note:
  - `open`
  - `answered`
  - `closed`

### Instructor Feedback

- Instructor co the reply/comment vao shared note.
- Hoc vien nhan thong bao khi instructor phan hoi.
- Them filter `unanswered shared notes` cho instructor.

## v1.5.0 - Rich Editing & Blocks

Muc tieu: nang cap trai nghiem bien tap va kha nang chen notes vao site.

### Rich Text Notes

- Thay textarea co ban bang editor nhe.
- Ho tro bold, italic, lists, links va simple formatting.
- Dam bao sanitize HTML chat che bang allowlist phu hop.

### Gutenberg / Block Support

- Them block hien thi notes thay cho shortcode thu cong.
- Block settings gom course, lesson, user, limit, order, filter UI.
- Preview notes trong editor neu co quyen.

### Template Customization

- Them template override documentation cho theme developers.
- Them hooks/filters chinh thuc cho render note card, export output va query args.

## v1.6.0 - Integrations, API & Privacy

Muc tieu: lam plugin san sang cho LMS lon, headless/mobile app va yeu cau privacy.

### REST API

- Them REST endpoints cho notes CRUD voi permission ro rang.
- Endpoint search/filter/pagination.
- Endpoint export metadata neu can.

### Notifications

- Email hoac in-site notification khi instructor reply shared note.
- Optional notification khi den ngay review note.

### Privacy & Data Ownership

- Tich hop WordPress personal data exporter.
- Tich hop WordPress personal data eraser.
- Them setting uninstall: giu lai notes hoac xoa notes khi uninstall.
- Them admin tool de export/delete notes cua mot user khi can.

## Backlog Ideas

- Import notes tu CSV/JSON.
- Sync notes giua sites trong multisite neu co nhu cau.
- Public note collections cho instructor tao sample notes cho course.
- AI summary cho notes theo course/lesson neu co chien luoc AI ro rang.
- Spaced repetition nang cao dua tren review status.
- Badge/gamification khi hoc vien ghi chu deu dan.

## Nguyen Tac Uu Tien

- Fix loi gay sai du lieu truoc khi them UI moi.
- Moi tinh nang can ho tro lesson duoc dung lai trong nhieu course.
- Moi query lon can co pagination va index phu hop.
- Private by default: notes cua hoc vien khong duoc share neu chua co hanh dong ro rang.
- Khong them dependency nang neu co the giai quyet bang WordPress/LearnPress APIs va browser native.
- Moi tinh nang premium can co gia tri ro cho hoc vien hoac instructor/admin.
