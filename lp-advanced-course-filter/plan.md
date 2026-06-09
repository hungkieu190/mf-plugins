# Advanced Course Filter for LearnPress

**Tài liệu Kế hoạch Phát triển Addon**  
**Phiên bản:** 1.0  
**Ngày:** 28/05/2026  
**Tác giả:** Grok + Hungkv  

---

## 1. Tổng quan Dự án

### 1.1 Tên Addon
- **Tên chính:** Advanced Course Filter for LearnPress
- **Tên ngắn:** LP Advanced Filter / LP Ultra Filter
- **Slug:** `lp-advanced-course-filter`

### 1.2 Mục tiêu
- Xây dựng addon filter khóa học mạnh nhất cho LearnPress
- Giải quyết hạn chế của widget filter mặc định
- Đạt ít nhất **500–1000 lượt bán** trong năm đầu
- Trở thành addon filter được recommend nhiều nhất cho LearnPress

### 1.3 Lý do làm
- Từ khóa "course filter learnpress" có lượng tìm kiếm cao
- Widget filter mặc định của LearnPress quá cơ bản
- Đối thủ (Tutor LMS, Masteriyo) có filter đẹp và mạnh hơn → khách hàng đang chuyển nền tảng

---

## 2. Phân tích Thị trường & Đối thủ

### 2.1 Đối thủ cạnh tranh
- **LearnPress Default Widget**: Rất yếu
- **Tutor LMS**: Filter mạnh, horizontal + sidebar
- **Masteriyo**: Filter hiện đại, AJAX tốt
- **LearnDash**: Filter mạnh nhưng đắt
- **LifterLMS**: Tốt nhưng phức tạp

### 2.2 Cơ hội
- LearnPress vẫn là LMS miễn phí phổ biến nhất tại Việt Nam và một số nước châu Á
- Nhiều theme Eduma, ThimPress đang dùng
- Khách hàng sẵn sàng trả tiền cho addon chất lượng ($29–$59)

---

## 3. Tính năng Chi tiết

### 3.1 Phiên bản Free (MVP)
- Filter theo Category (multi)
- Filter theo Price (Free / Paid / All)
- Filter theo Level (Beginner, Intermediate, Advanced)
- Filter theo Rating (≥ 4 sao, ≥ 4.5 sao)
- Live Search
- AJAX load kết quả
- Shortcode + Gutenberg Block + Elementor Widget
- Sidebar + Top Filter Bar

### 3.2 Phiên bản Pro (Premium)
- **Filter nâng cao**:
  - Price Range Slider
  - Duration Slider (giờ)
  - Language
  - Instructor (multi-select với avatar)
  - Tag / Custom Taxonomy
  - Custom Fields (ACF, Meta Box)
  - Progress Status (In Progress, Completed...)
  - Date (Newest, This month...)

- **UI/UX Pro**:
  - Horizontal Modern Filter Bar (kiểu Udemy)
  - Collapsible Filter Groups
  - Active Filter Tags (có thể xóa từng cái)
  - Preset Filters (lưu bộ lọc)
  - Responsive hoàn hảo

- **Tính năng cao cấp**:
  - Dynamic Filter (tự động hiển thị filter phù hợp với category hiện tại)
  - Sorting nâng cao (Popular, Bestseller, Most Rated, Trending)
  - Filter bằng Geo-location (nếu có)
  - Integration với Wishlist & Course Bundle
  - Analytics (số lần filter, conversion rate)

### 3.3 Tương lai (v2.0+)
- AI Smart Filter (gợi ý filter dựa trên hành vi)
- Filter theo Competency / Skill
- Save & Share Filter URL

---

## 4. Yêu cầu Kỹ thuật

### 4.1 Công nghệ Stack
- WordPress 6.0+
- LearnPress 4.2+
- PHP 8.1+
- JavaScript (Vanilla + Alpine.js hoặc Vue 3 nhẹ)
- AJAX + WP REST API
- CSS (SCSS + Tailwind hoặc Bootstrap 5)

### 4.2 Cấu trúc Folder
```
lp-advanced-course-filter/
├── assets/
├── includes/
│   ├── class-lp-acf.php
│   ├── class-lp-acf-query.php
│   ├── class-lp-acf-settings.php
│   └── ...
├── templates/
├── languages/
├── lp-advanced-course-filter.php
├── readme.txt
└── ...
```

### 4.3 Các Class chính
- `LP_Advanced_Course_Filter`
- `LP_ACF_Query` (xử lý query)
- `LP_ACF_Settings`
- `LP_ACF_Shortcode`
- `LP_ACF_Elementor`
- `LP_ACF_Gutenberg`

---

## 5. Roadmap Phát triển

### Phase 1: MVP (4–5 tuần)
- Core filter (category, price, level, rating)
- AJAX + Shortcode + Elementor + Gutenberg
- Basic UI
- Documentation

### Phase 2: Pro Version (4–6 tuần)
- Tất cả tính năng Pro
- Horizontal Filter
- Custom Fields
- Preset & Active Filters
- Polish UI/UX

### Phase 3: Polish & Launch (2–3 tuần)
- Testing trên nhiều theme (Eduma, College, ...)
- Compatibility check
- Translation (tiếng Việt + English)
- Marketing materials

### Phase 4: Post-Launch
- Support & Updates
- Version 2.0 (AI + Dynamic)

---

## 6. Thiết kế Giao diện (UI/UX)

- **Màu sắc**: Theo theme LearnPress hoặc tùy chỉnh
- **Style**: Clean, Modern, Card-based
- **2 Layout chính**:
  1. Sidebar Filter (trái/phải)
  2. Horizontal Top Bar
- Active filter hiển thị dạng tag phía trên kết quả
- Loading skeleton khi filter

---

## 7. Marketing & Bán hàng

### 7.1 Kênh bán
- **Chính**: ThimPress Marketplace / Sell trên website riêng
- **Phụ**: Codecanyon, Facebook Group, Freelancer groups

### 7.2 Giá bán gợi ý
- **Single Site**: $39 (giá launch $29)
- **5 Sites**: $89
- **Unlimited**: $149
- **Lifetime**: $199

### 7.3 Marketing Plan
- Post demo video trên YouTube & TikTok
- Free version trên WordPress.org
- Case study với theme Eduma
- Hợp tác với reviewer LMS Việt Nam
- SEO bài viết: "Best Course Filter for LearnPress 2026"

---

## 8. Timeline & Resource

- **Tổng thời gian**: 10–14 tuần
- **Developer**: 1 người full-time
- **Designer** (UI/UX): Freelance nếu cần
- **Tester**: 3–5 người dùng thực tế

---

## 9. Rủi ro & Giải pháp
- Tương thích theme: Test kỹ với Eduma, ThimPress themes
- Hiệu năng: Optimize query (caching, transient)
- Security: Sanitize tất cả input
- Support: Chuẩn bị documentation tốt + video hướng dẫn

---

## 10. Tiêu chí Thành công
- 200+ lượt active install sau 3 tháng
- Rating ≥ 4.7 sao
- Ít nhất 1–2 bài review tích cực
- Doanh thu $3000+ trong 6 tháng đầu

---

**Ghi chú**: 
- Luôn ưu tiên UX mượt mà và tốc độ load
- Code phải sạch, comment rõ ràng, follow WordPress coding standard
- Chuẩn bị demo site riêng để khách thử

---
## 11. Trang thai trien khai - cap nhat 09/06/2026

### Da xong - Free MVP code
- Scaffold plugin `lp-advanced-course-filter` da duoc tao.
- Bootstrap plugin chinh: `lp-advanced-course-filter.php`.
- Core classes da co:
  - `LP_Advanced_Course_Filter`
  - `LP_ACF_Query`
  - `LP_ACF_Shortcode`
  - `LP_ACF_Settings`
  - `LP_ACF_Gutenberg`
  - `LP_ACF_Elementor`
  - `LP_ACF_Elementor_Widget`
- Filter da implement:
  - Category multi-select qua taxonomy `course_category`.
  - Price: All / Free / Paid qua meta `_lp_price`.
  - Level qua meta `_lp_level`.
  - Rating >= 4 va >= 4.5 qua cac meta rating pho bien.
  - Live search keyword.
- AJAX da implement bang `admin-ajax.php` voi nonce.
- Shortcode da co:
  - `[lp_advanced_course_filter layout="sidebar" per_page="9" columns="3"]`
  - Ho tro `layout="horizontal"`.
- Gutenberg block da co, server-side render va editor script khong can build step.
- Elementor widget da co, tu dong dang ky khi Elementor active.
- UI frontend da co:
  - Sidebar filter.
  - Horizontal top filter.
  - Course cards.
  - Active filter tags co the xoa tung tag.
  - Sort basic: Newest, Title A-Z, Price low/high.
  - Load more.
  - Reset filter.
  - Responsive CSS co ban.
- Readme plugin da co.
- Kiem tra ky thuat da chay:
  - PHP lint tat ca file PHP moi: OK.
  - JS syntax check `frontend.js` va `block.js`: OK.

### Chua xong - can lam truoc khi release Free
- Chua test runtime trong WordPress admin/site that vi PHP CLI hien tai thieu extension `mysqli`, khong load duoc `wp-load.php`.
- Can activate plugin trong WordPress admin va test thu cong:
  - Shortcode render tren page.
  - AJAX filter voi du lieu course that.
  - Gutenberg block trong editor.
  - Elementor widget trong editor.
  - Layout mobile/tablet/desktop.
  - Tuong thich theme Eduma/ThimPress va archive LearnPress.
- Chua co package release zip rieng cho addon nay.
- Chua co screenshot/demo/documentation day du cho marketplace.
- Chua co file dich `.pot` / `.po`.
- Rating filter can verify voi du lieu review that cua site, vi LearnPress/addon review co the luu rating bang meta/function khac nhau.

### Chua lam - Pro va phase sau
- Price range slider.
- Duration slider.
- Language filter.
- Instructor multi-select voi avatar.
- Tag/custom taxonomy filter.
- Custom Fields ACF/Meta Box.
- Progress status filter.
- Date filter.
- Collapsible filter groups.
- Preset filters.
- Dynamic filter theo category hien tai.
- Sorting nang cao: Popular, Bestseller, Most Rated, Trending.
- Geo-location filter.
- Wishlist/Course Bundle integration.
- Analytics.
- AI Smart Filter.
- Save & Share Filter URL.

---
**End of Document**
