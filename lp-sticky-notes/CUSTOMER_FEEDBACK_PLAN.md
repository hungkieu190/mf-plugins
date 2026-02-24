# LP Sticky Notes — Customer Feedback & Fix Plan

> **Nguồn:** 2 email phản hồi từ khách hàng
> **Cập nhật:** 2026-02-24

---

## 🐛 Bugs — Cần fix sớm

### Bug #1: Button Size "Small" bị oval thay vì tròn

- **Mô tả:** Khi chọn Button Size = `Small` trong Customization Settings, nút sticky hiển thị hình bầu dục (oval) thay vì hình tròn (circle).
- **Nguyên nhân dự kiến:** CSS `border-radius: 50%` chưa được enforce đồng thời với `width` = `height` cố định khi scale nhỏ.
- **Fix:** Đảm bảo khi size = small → `width` và `height` bằng nhau (ví dụ `40px x 40px`) và `border-radius: 50%`.
- **File liên quan:** `assets/css/` → file style của sticky button.

---

### Bug #2: Sticky button đè lên nút "Next" của LearnPress trên Mobile

- **Mô tả:** Khi đặt button ở vị trí `bottom-right`, trên mobile nó che mất nút **Next** của LearnPress navigation.
- **Khách yêu cầu:** Có thể nhập số pixel để đẩy button lên cao hơn (ví dụ: `bottom: X px`).
- **Fix:**
  - Thêm setting mới: **"Bottom Offset (px)"** trong Customization Settings (chỉ áp dụng cho mobile, hoặc áp dụng chung).
  - Enqueue setting này vào inline CSS cho sticky button.
- **File liên quan:** `inc/class-lp-sticky-notes-settings.php`, `assets/css/`, `assets/js/`.

---

## 💡 Feature Requests

### Feature #1: Sort / Group ghi chú theo Course (Admin Backend)

- **Vị trí:** Admin & Instructor Notes Management (backend)
- **Yêu cầu:** Thêm bộ lọc / sort để xem ghi chú được nhóm theo từng Course.
- **Ghi chú:** Hiện tại filter tốt, chỉ cần thêm group by course ở query + UI dropdown chọn course.
- **Priority:** 🟡 Trung bình

---

### Feature #2: Sort ghi chú theo Course → Lesson (Frontend Shortcode)

- **Vị trí:** Shortcode output phía frontend
- **Yêu cầu:** Hiển thị ghi chú được sắp xếp: **Course** trước → **Lesson** sau (nested grouping).
- **Ghi chú:** Đồng bộ logic sort với Feature #1 ở backend.
- **Priority:** 🟡 Trung bình

---

### Feature #3: Đồng nhất nút "View Full" giữa Backend và Shortcode

- **Vị trí:** Shortcode output phía frontend
- **Yêu cầu:** Dùng cùng kiểu UI nút "View Full" như ở backend Admin Notes Management — không phải kiểu inline hiện tại.
- **Ghi chú:** Đây là yêu cầu UI nhất quán, không có logic phức tạp.
- **Priority:** 🟡 Trung bình

---

### Feature #4: Export Notes as PDF

- **Vị trí:** Frontend (Student Profile / Shortcode) + Admin
- **Yêu cầu:** Nút "Export as PDF" để học viên tải về toàn bộ ghi chú của mình (hoặc admin xuất ghi chú học viên). Khách ưu tiên PDF hơn Print.
- **Ghi chú kỹ thuật:**
  - Không được dùng library ngoài (Composer/npm bị cấm per coding rules).
  - Phương án: Dùng JavaScript `window.print()` với CSS `@media print` custom → giả lập PDF sạch sẽ mà không cần thêm dependency PHP.
  - Hoặc: Generate HTML table → dùng browser "Save as PDF" native qua print dialog.
- **Priority:** 🟢 Thấp (phức tạp nếu dùng PHP PDF lib)

---

## 📌 Thứ tự xử lý đề xuất

```
1. ✅ ~~Bug #1  — Fix CSS button Small → oval thành circle~~ (Done 2026-02-24)
2. ✅ ~~Bug #2  — Thêm "Bottom Offset (px)" setting cho mobile~~ (Done 2026-02-24)
3. 🟡 Feature #1 — Sort/Group by Course (Admin backend)
4. 🟡 Feature #2 — Sort by Course → Lesson (Shortcode frontend)
5. ✅ ~~Feature #3 — Đồng nhất UI nút "View Full"~~ (Done 2026-02-24)
6. ✅ ~~Feature #4 — Export PDF (dùng browser print + CSS @media print)~~ (Done 2026-02-24)
```
