# Advanced Course Filter for LearnPress

**Tai lieu ke hoach phat trien addon**  
**Phien ban ke hoach:** 2.0  
**Cap nhat:** 10/06/2026  
**Dinh huong moi:** Chi phat hanh ban Premium, khong lam ban Free rieng.

---

## 1. Tong quan du an

### 1.1 Ten addon

- **Ten chinh:** Advanced Course Filter for LearnPress
- **Ten ngan:** LP Advanced Filter / LP Ultra Filter
- **Slug:** `lp-advanced-course-filter`
- **Loai san pham:** Premium WordPress plugin cho LearnPress

### 1.2 Dinh vi san pham

Addon nay se la bo loc khoa hoc premium cho LearnPress, tap trung vao:

- Thay the widget filter mac dinh cua LearnPress.
- Tao trai nghiem filter hien dai, nhanh, de tuy bien.
- Ho tro shortcode, WordPress widget, Gutenberg block, Elementor widget.
- Huong den nguoi dung Eduma/ThimPress va cac website ban khoa hoc can UX tot hon filter mac dinh.

### 1.3 Muc tieu

- Ban ngay nhu mot plugin premium day du, khong chia Free/Pro.
- Dat 500-1000 luot ban trong nam dau.
- Tro thanh addon filter duoc recommend cho LearnPress.
- Giam phu thuoc vao custom code khi khach can loc khoa hoc nang cao.

---

## 2. Pham vi tinh nang Premium

### 2.1 Filter core bat buoc cho ban 1.0

- Category multi-select qua taxonomy `course_category`.
- Price:
  - All / Free / Paid.
  - Price range slider.
- Level multi-select.
- Rating filter:
  - >= 4 sao.
  - >= 4.5 sao.
  - Co kha nang mo rong theo du lieu review that cua LearnPress.
- Live search keyword.
- Instructor filter:
  - Multi-select.
  - Hien avatar neu co.
- Tag filter.
- Custom taxonomy filter.
- Duration filter neu du lieu course co meta duration.
- Language filter neu site co taxonomy/meta ngon ngu.
- Date filter:
  - Newest.
  - This month.
  - Custom date range neu can.

### 2.2 Filter nang cao cho ban 1.x

- Custom Fields:
  - ACF.
  - Meta Box.
  - Custom post meta.
- Progress Status cho user da dang nhap:
  - Not started.
  - In progress.
  - Completed.
- Dynamic Filter:
  - Tu an/hien filter theo category/archive hien tai.
  - Khong hien group filter khong co du lieu.
- Sorting nang cao:
  - Newest.
  - Title A-Z.
  - Price low/high.
  - Popular.
  - Bestseller.
  - Most Rated.
  - Trending.

### 2.3 UI/UX Premium

- Sidebar filter de thay widget hien tai.
- Horizontal filter bar kieu marketplace/Udemy.
- Collapsible filter groups.
- Active filter tags, xoa tung filter rieng le.
- Reset all.
- Load more va/hoac pagination.
- Loading state/skeleton.
- Mobile drawer/off-canvas filter.
- Responsive hoan chinh cho desktop/tablet/mobile.
- Option tuy bien:
  - Mau accent.
  - So cot course card.
  - Hien/an tung filter group.
  - Thu tu filter group.
  - Default expanded/collapsed.

### 2.4 Integration

- Shortcode:
  - `[lp_advanced_course_filter layout="sidebar" per_page="9" columns="3"]`
- WordPress widget:
  - `LearnPress Advanced Course Filter`
  - Dung de thay widget filter mac dinh trong sidebar.
- Gutenberg block.
- Elementor widget.
- LearnPress archive/course category compatibility.
- Theme compatibility uu tien:
  - Eduma.
  - ThimPress themes.
  - Theme WordPress pho bien co sidebar/archive.

### 2.5 Premium business features

- Preset filters:
  - Admin tao san bo loc theo use case.
  - User co the ap preset tu frontend neu duoc bat.
- Save & Share Filter URL:
  - Filter state duoc luu tren query string.
  - Co the copy/share URL da loc.
- Analytics:
  - So lan dung filter.
  - Filter nao duoc dung nhieu.
  - Search keyword pho bien.
  - Click/view course sau khi filter neu kha thi.
- Export analytics CSV.

### 2.6 De sau 2.0+

- AI Smart Filter dua tren hanh vi nguoi dung.
- Filter theo competency/skill.
- Geo-location filter neu co data location.
- Integration voi Wishlist va Course Bundle.

---

## 3. Yeu cau ky thuat

### 3.1 Nen tang

- WordPress 6.0+.
- LearnPress 4.2+.
- PHP 8.1+.
- JavaScript vanilla, uu tien khong can build step o giai doan dau.
- AJAX qua `admin-ajax.php`; co the them REST API neu can scale.
- CSS thuan, co the chuyen SCSS/build step khi UI phuc tap hon.

### 3.2 Nguyen tac code

- Khong nhan doi logic render giua shortcode/widget/block/Elementor.
- Query phai sanitize input va co hook filter de mo rong.
- Asset chi enqueue khi filter duoc render.
- Moi input frontend phai sanitize; output phai escape.
- Co cache/transient cho cac list nang nhu instructor, taxonomy, custom fields neu can.
- Tach ro:
  - Query builder.
  - Renderer.
  - Integrations.
  - Admin settings.
  - Analytics.

### 3.3 Cau truc folder muc tieu

```text
lp-advanced-course-filter/
|-- assets/
|   |-- css/
|   `-- js/
|-- docs/
|-- includes/
|   |-- class-lp-acf.php
|   |-- class-lp-acf-query.php
|   |-- class-lp-acf-shortcode.php
|   |-- class-lp-acf-widget.php
|   |-- class-lp-acf-gutenberg.php
|   |-- class-lp-acf-elementor.php
|   |-- class-lp-acf-settings.php
|   |-- class-lp-acf-analytics.php
|   `-- class-lp-acf-presets.php
|-- languages/
|-- templates/
|-- lp-advanced-course-filter.php
|-- readme.txt
`-- plan.md
```

---

## 4. Launch plan de bat dau ban

Muc tieu cua plan nay la dua plugin tu nen mong hien tai sang trang thai co the ban ban dau. Khong doi den khi lam het moi tinh nang lon; ban 1.0 can co du gia tri premium ro rang, on dinh, co demo, co tai lieu, va co package sach.

### Sprint 1: Product baseline va runtime QA

Muc tieu: dam bao nhung gi da code hoat dong that tren WordPress.

- Activate plugin tren site local co LearnPress.
- Test shortcode tren page that.
- Test WordPress widget trong sidebar/archive sidebar.
- Test Gutenberg block.
- Test Elementor widget neu Elementor active.
- Test AJAX logged-in va logged-out.
- Test filter voi du lieu course that:
  - Category.
  - Price free/paid.
  - Level.
  - Rating.
  - Search.
  - Sort.
  - Load more.
- Fix fatal error, warning, console error, layout break.
- Verify rating meta/function cua LearnPress tren site target.
- Chot CSS scope de khong va cham theme.

Ket qua can co:

- Plugin co the thay widget filter mac dinh.
- Khong co loi fatal/JS console trong flow chinh.
- Co checklist QA da tick tren site local.

### Sprint 2: Premium feature 1 - Price range slider

Muc tieu: co tinh nang premium de khach thay khac biet ngay.

- Them min/max price vao query.
- Them UI range slider hoac min/max inputs co UX tot.
- Auto lay min/max tu course data neu kha thi.
- Ho tro reset, active tags, AJAX, query string.
- Dam bao free course van loc dung.
- Them setting bat/tat price range.

Ket qua can co:

- Loc khoa hoc theo khoang gia chay on dinh.
- Active tag hien khoang gia dang loc.
- Readme va checklist co huong dan test.

### Sprint 3: Premium feature 2 - Instructor filter

Muc tieu: them filter ma site LMS thuong can va co gia tri cao hon filter co ban.

- Xac dinh cach LearnPress luu instructor/author tren site target.
- Query theo instructor.
- UI multi-select instructor.
- Hien avatar neu co.
- Ho tro search trong danh sach instructor neu nhieu instructor.
- Ho tro reset, active tags, AJAX, query string.
- Them setting bat/tat instructor filter.

Ket qua can co:

- Loc theo mot hoac nhieu instructor.
- Widget/sidebar dung duoc tren archive co nhieu instructor.

### Sprint 4: Premium UX - Mobile drawer va settings page

Muc tieu: plugin co cam giac san pham tra phi, khong chi la shortcode.

- Admin settings page:
  - Enable/disable filter groups.
  - Default layout.
  - Courses per page.
  - Columns.
  - Accent color.
  - Show/hide course card fields.
- Mobile drawer/off-canvas filter:
  - Nut Filter tren mobile.
  - Drawer co Apply/Reset.
  - Dong drawer sau khi apply neu can.
- Collapsible filter groups.
- Better loading skeleton.
- Empty state polish.

Ket qua can co:

- Admin co the cau hinh plugin ma khong sua shortcode.
- Mobile UX du tot de quay demo/screenshot.

### Sprint 5: Share URL, docs, demo, package

Muc tieu: san pham san sang ban va support duoc.

- Sync filter state len URL query string.
- Khi mo URL da co filter, frontend phai restore state.
- Tao demo page:
  - Sidebar filter.
  - Horizontal filter.
  - Mobile screenshot.
- Viet documentation:
  - Install.
  - Replace default widget.
  - Shortcode.
  - Gutenberg.
  - Elementor.
  - Troubleshooting rating/instructor.
- Tao screenshots.
- Build zip clean.
- Cai zip tren site sach va smoke test lai.

Ket qua can co:

- Co file zip release 1.0.
- Co demo/screenshot/documentation de dua len landing page hoac marketplace.

### Backlog sau launch 1.0

- Tag/custom taxonomy filter.
- Duration filter.
- Language filter.
- Date filter.
- Preset filters.
- Analytics dashboard.
- CSV export analytics.
- Wishlist/Course Bundle integration.
- AI Smart Filter.

---

## 5. Gia ban goi y

### 5.1 Mat bang gia tham chieu

- LearnPress bundle dang o tam $149-$299 tuy goi va khuyen mai.
- Tutor LMS Pro dang o tam $199/năm cho 1 site.
- Masteriyo va cac LMS premium khac thuong ban theo goi lon hon, tu khoang $149/năm tro len.
- LearnPress add-on rieng le thuong re hon bundle, nhieu add-on nam trong khoang $29-$49.

Ket luan: plugin nay la add-on chuyen sau, nen gia phai thap hon LMS/bundle day du, nhung cao hon add-on nho vi no thay doi UX frontend va anh huong conversion.

### 5.2 Gia launch de bat dau ban

Khuyen nghi bat dau bang gia de de mua, lay khach hang dau tien va feedback:

- **Single Site:** $39/năm.
- **5 Sites:** $89/năm.
- **Unlimited Sites:** $149/năm.
- **Lifetime Single Site:** $129 mot lan.
- **Lifetime Unlimited:** $249 mot lan.

### 5.3 Gia sau khi co validation

Khi da co demo tot, screenshot, mobile drawer, price range, instructor filter va 5-10 khach dau tien:

- **Single Site:** $49/năm.
- **5 Sites:** $119/năm.
- **Unlimited Sites:** $199/năm.
- **Lifetime Single Site:** $179 mot lan.
- **Lifetime Unlimited:** $299 mot lan.

### 5.4 Goi nen public

Khong nen public qua nhieu goi luc dau. De landing page gon:

- **Starter:** $39/năm, 1 site.
- **Business:** $89/năm, 5 sites.
- **Agency:** $149/năm, unlimited sites.
- Them lifetime deal rieng cho launch campaign, khong can hien mac dinh tren pricing table.

### 5.5 Chinh sach ban hang

- 14 ngay money-back guarantee.
- 1 nam update va support cho license hang nam.
- Renewal discount 30%.
- License lifetime gom lifetime updates, support co the gioi han 1 nam hoac uu tien thap hon goi annual tuy chien luoc support.

---

## 6. Kenh ban va marketing

### 6.1 Kenh ban

- Website rieng Mamflow.
- ThimPress Marketplace neu phu hop.
- Codecanyon neu chap nhan support workload.
- Facebook group LearnPress/WordPress/LMS.

### 6.2 Noi dung marketing

- Demo video ngan: thay widget LearnPress mac dinh bang filter premium.
- Bai SEO: "Best Course Filter for LearnPress".
- Case study voi Eduma.
- Before/after UI cua LearnPress default widget vs addon nay.
- Landing page co demo filter that, khong chi screenshot.

---

## 7. Riu ro va cach xu ly

- **Du lieu LearnPress khong dong nhat:** rating, duration, level co the luu khac nhau theo version/addon.
  - Xu ly: viet adapter va hook filter de site override meta key.
- **Theme conflict:** Eduma/ThimPress co CSS rieng.
  - Xu ly: CSS scope trong `.lp-acf`, test theme target.
- **Query cham voi data lon:**
  - Xu ly: index-friendly meta query, cache option list, giam query lap.
- **Elementor/Gutenberg/widget nhan doi logic:**
  - Xu ly: tat ca integration render qua shortcode/core renderer.
- **Premium ma UI chua thuyet phuc:**
  - Xu ly: uu tien mobile drawer, skeleton, active tags, admin settings truoc launch.

---

## 8. Tieu chi release 1.0 Premium de bat dau ban

Bat buoc co truoc khi ban:

- Widget thay duoc filter mac dinh tren sidebar.
- Shortcode hoat dong.
- Gutenberg block hoat dong.
- Elementor widget hoat dong.
- AJAX filter chay voi du lieu course that.
- Category, price, level, rating, search hoat dong.
- Bat buoc co it nhat 3 gia tri premium ro rang:
  - Price range slider.
  - Instructor filter.
  - Mobile drawer/off-canvas.
- Nen co them Save/share filter URL neu kip.
- Settings page co cau hinh can thiet.
- Responsive pass desktop/tablet/mobile.
- Test voi Eduma hoac theme target.
- Co documentation va screenshot.
- Co zip release clean.
- Co demo page de khach thay san pham truoc khi mua.
- Co pricing page voi 3 goi: Starter, Business, Agency.

---

## 9. Trang thai hien tai - cap nhat 10/06/2026

### Da lam

- Scaffold plugin `lp-advanced-course-filter`.
- Bootstrap plugin chinh.
- Core classes:
  - `LP_Advanced_Course_Filter`
  - `LP_ACF_Query`
  - `LP_ACF_Shortcode`
  - `LP_ACF_Settings`
  - `LP_ACF_Gutenberg`
  - `LP_ACF_Elementor`
  - `LP_ACF_Elementor_Widget`
  - `LP_ACF_Widget`
- Filter da implement:
  - Category multi-select.
  - Price All/Free/Paid.
  - Level.
  - Rating >= 4 va >= 4.5.
  - Live search.
- AJAX da implement bang `admin-ajax.php` voi nonce.
- Shortcode da co.
- WordPress widget da co.
- Gutenberg block da co.
- Elementor widget da co.
- UI frontend co:
  - Sidebar filter.
  - Horizontal filter.
  - Course cards.
  - Active filter tags.
  - Sort basic.
  - Load more.
  - Reset.
  - Responsive CSS co ban.
- Readme da cap nhat theo huong co widget.
- POT file da co.
- Release checklist da co.
- Zip local da tao.
- PHP lint: OK.
- JS syntax check: OK.

### Chua lam

- Chua test runtime trong WordPress admin/site that.
- Chua verify rating filter voi du lieu review LearnPress that.
- Chua co price range slider.
- Chua co instructor filter.
- Chua co tag/custom taxonomy filter.
- Chua co duration/language/date filter.
- Chua co mobile drawer/off-canvas.
- Chua co settings page day du.
- Chua co preset filters.
- Chua co analytics.
- Chua co save/share filter URL.
- Chua co screenshot/demo/documentation marketplace day du.

---

**Ket luan:** Du an da chuyen huong thanh mot plugin Premium duy nhat. Phan code hien tai la nen mong ban dau, nhung chua du de release premium cho khach tra tien. Uu tien tiep theo nen la runtime test, settings page, price range slider, instructor filter, va mobile drawer/off-canvas.
