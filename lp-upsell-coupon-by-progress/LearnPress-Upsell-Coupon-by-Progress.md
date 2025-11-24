# LearnPress – Upsell Coupon by Progress  
**Tên rút gọn:** LP Upsell Progress Coupon  
**Phiên bản:** 1.0.0  
**Mục tiêu:** Tự động tặng coupon giảm giá cho khóa học tiếp theo khi học viên đạt ngưỡng % hoàn thành nhất định → tăng upsell tự nhiên, tăng retention.

## Mô tả ngắn gọn
Addon mini tự động tạo và gửi **coupon giảm giá** (mặc định 10%) cho **khóa học tiếp theo** khi học viên đạt X% tiến độ khóa hiện tại.

## Lợi ích nổi bật
- Upsell pathway hoàn toàn tự động
- Tạo cảm giác “phần thưởng” khi học viên tiến bộ tốt
- Không làm phiền bằng popup quảng cáo
- Tăng tỷ lệ chuyển đổi từ khóa cơ bản → khóa nâng cao

## Tính năng chi tiết (v1.0.0)

| Tính năng                        | Mô tả chi tiết                                                                                       | Giá trị mặc định |
|----------------------------------|------------------------------------------------------------------------------------------------------|------------------|
| Nhiều ngưỡng tiến độ             | Hỗ trợ nhiều rule cho 1 khóa (ví dụ: 50% → 5%, 80% → 10%, 100% → 20%)                               | 90%              |
| Loại giảm giá                    | Phần trăm (%) hoặc số tiền cố định (fixed amount)                                                    | 10%              |
| Khóa học áp dụng coupon          | 3 chế độ:<br>• Khóa cụ thể (1 hoặc nhiều)<br>• Cùng category<br>• Global (tất cả khóa còn lại)     | Khóa cụ thể      |
| Loại coupon                      | WooCommerce coupon (tự động restrict vào sản phẩm = các khóa next)                                   | WooCommerce      |
| Hạn sử dụng coupon               | Số ngày hết hạn kể từ ngày tạo                                                                       | 30 ngày          |
| Giới hạn sử dụng                 | Chỉ dùng 1 lần + chỉ user nhận coupon mới dùng được                                                  | Bật              |
| Cách gửi coupon                  | 1. Email tự động (template đẹp + nút “Dùng ngay” link thẳng khóa + auto apply coupon)<br>2. Hiển thị trong My Courses → tab “Ưu đãi của bạn” | Cả 2             |
| Ngăn gửi trùng                   | Lưu meta `_lp_progress_coupon_sent_{course_id}_{threshold}`                                          | Bật              |
| Gửi lại khi retake khóa          | Checkbox tùy chọn (một số site cho học lại khóa)                                                     | Tắt              |
| Log chi tiết                     | LearnPress → Coupons → Tab “Progress Coupons Log” (lọc theo user, khóa, ngày…)                       | Có               |

## Hook tối ưu (đã test nhẹ)
```php
add_action( 'learn_press_user_item_completed', function( $item_id, $course_id, $user_id ) {
    $user        = learn_press_get_user( $user_id );
    $course_data = $user->get_course_data( $course_id );
    $progress    = $course_data->get_percent_completed();

    $last_progress = get_user_meta( $user_id, '_lp_last_progress_' . $course_id, true );
    if ( $progress != $last_progress ) {
        update_user_meta( $user_id, '_lp_last_progress_' . $course_id, $progress );
        LP_Upsell_Progress_Coupon()->maybe_send_coupon( $user_id, $course_id, $progress );
    }
}, 10, 3 );

Giao diện setting
1. Settings chung (LearnPress → Settings → Upsell Progress)

Enable/Disable addon
Default discount type & amount
Default expiry days
Email template editor (hỗ trợ biến {user_name}, {course_name}, {next_course_name}, {coupon_code}, {discount}, {expiry_date})

2. Metabox trong Course Edit → Tab “Upsell by Progress”

Bật/tắt upsell cho khóa này
Thêm không giới hạn rule:
Tại % hoàn thành: ___
Giảm: ___ % hoặc ___ (fixed)
Hết hạn sau: ___ ngày
Áp dụng cho khóa: [chọn nhiều khóa / category / global]


Email template mẫu (đẹp lung linh)
Subject: Chúc mừng {user_name}! Bạn đã chinh phục {progress}% khóa {course_name} 🎉
Nội dung:
Bạn thật xuất sắc! Đây là phần thưởng dành riêng cho bạn:
Mã giảm {discount}%: {coupon_code}
Áp dụng ngay cho các khóa học tiếp theo:
Nhận ưu đãi ngay →
Hết hạn: {expiry_date}
Tương thích

LearnPress 4.2.7+
WooCommerce 8.x – 9.x
Theme phổ biến: Eduma, Education WP, Course Builder, LearnMate, Astra + Elementor/LearnPress templates
Đa ngôn ngữ (.pot sẵn)