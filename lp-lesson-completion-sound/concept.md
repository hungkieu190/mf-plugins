# 💡 Concept: LP-Lesson-Completion-Sound (Âm thanh Hoàn thành Bài học)

> **Mục tiêu:** Kích hoạt dopamine và cảm giác thành tựu bằng cách phát một âm thanh nhỏ cùng hiệu ứng confetti khi người dùng hoàn thành một bài học (Lesson). Điều này nhằm tăng cường động lực và tỉ lệ giữ chân (retention).

---

## 🎯 Giá trị Đề xuất

| Chỉ số | Mô tả |
| :--- | :--- |
| **Dopamine Hit** | Củng cố thói quen học tập bằng phần thưởng âm thanh và hình ảnh ngay lập tức. |
| **Gamification** | Biến quá trình học thành trải nghiệm vui vẻ, ít đơn điệu hơn. |
| **Retention** | Khuyến khích người dùng chuyển sang bài học tiếp theo. |

---

## ⚙️ Cấu hình Người dùng (Settings)

Người dùng có thể quản lý tính năng này trong phần Cài đặt (Settings) hoặc Hồ sơ (Profile) dưới mục **"Âm thanh & Hiệu ứng Hoàn thành Bài học"**.

| Tùy chọn | Mô tả | Mặc định | Ghi chú |
| :--- | :--- | :--- | :--- |
| **Bật/Tắt Tính năng** | Kích hoạt hoặc vô hiệu hóa toàn bộ âm thanh và hiệu ứng. | Bật | |
| **Chọn Âm thanh** | Chọn âm thanh hoàn thành từ thư viện có sẵn. | `Ting! (Mặc định)` | |
| **Hiệu ứng Confetti** | Bật/tắt hiệu ứng hình ảnh Confetti đi kèm. | Bật | |
| **Tải lên Âm thanh Riêng** | (Premium/Pro) Cho phép người dùng tải lên tệp `.mp3` hoặc `.wav` của riêng họ. | Tắt | Tính năng Premium. |

---

## 🎵 Thư viện Âm thanh Mặc định

Các âm thanh nên ngắn gọn (dưới 0.5 giây), rõ ràng và vui tai.

* **Ting! (Mặc định):** Âm thanh kim loại nhẹ nhàng, sạch sẽ. (Phù hợp cho mọi đối tượng).
* **Success Chime:** Âm thanh chiến thắng, vang vọng ngắn. (Tạo cảm giác thành tựu lớn).
* **Magic Sparkle:** Âm thanh lấp lánh, nhẹ nhàng và huyền ảo. (Thú vị, thư giãn).
* **Pop!:** Âm thanh nổ nhỏ, năng lượng. (Trẻ trung, vui vẻ).

---

## ✨ Trải nghiệm Người dùng (UX/UI)

### 1. Kích hoạt (Trigger)

Âm thanh và hiệu ứng phải được kích hoạt **ngay lập tức** khi hệ thống xác nhận bài học đã hoàn thành:

* **Thời điểm:** Khi người dùng nhấn nút **"Hoàn thành Bài học"** hoặc **"Nộp Bài Kiểm tra Cuối"**.
* **Vị trí Phát:** Trên màn hình xác nhận thành công hoặc màn hình chuyển tiếp sang bài học tiếp theo.

### 2. Chi tiết Hiệu ứng Confetti

* **Thời lượng:** Rơi xuống nhanh chóng trong khoảng **1.5 - 2 giây**.
* **Vị trí:** Phun ra từ trung tâm hoặc từ phía trên của màn hình.
* **Màu sắc:** Nên sử dụng màu sắc tươi sáng, rực rỡ hoặc màu sắc chủ đạo của thương hiệu.

### 3. Yêu cầu Kỹ thuật

* Âm thanh cần tuân thủ cài đặt **Mute/Volume** chung của ứng dụng.
* Đảm bảo hiệu suất tốt trên các thiết bị di động để tránh giật lag khi phát hiệu ứng hình ảnh Confetti.