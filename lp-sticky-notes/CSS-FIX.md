# LP Sticky Notes - CSS Z-Index Fix

## Vấn đề
Sticky notes toggle button và sidebar có z-index quá cao (99999) gây ảnh hưởng đến footer và các elements khác của theme Twenty Twenty-Five.

## Giải pháp

### 1. Điều chỉnh z-index về mức hợp lý
- **Toggle button**: `9999` (giảm từ 99999)
- **Sidebar**: `9998` (giảm từ 99998)
- **Toast container**: `9999` (giữ nguyên)
- **Modal "View All Notes"**: `99999` (tăng từ 10000 để luôn hiển thị phía trên)

### 2. Thêm `pointer-events: auto`
Đảm bảo chỉ sticky notes elements có thể click, không block interaction với các elements khác.

### 3. Thêm `isolation: isolate`
Tạo stacking context riêng cho sticky notes components để không ảnh hưởng đến z-index của các elements khác trong theme.

## Z-Index Hierarchy (thấp → cao)
```
Theme elements (auto) →
Sticky Notes Sidebar (9998) →
Toggle Button/Toast (9999) →
All Notes Modal (99999)
```

## Kết quả
✅ Sticky notes vẫn hiển thị chính xác khi course finished
✅ Footer links và navigation vẫn clickable
✅ Không còn conflict với theme Twenty Twenty-Five
✅ Modal vẫn hiển thị phía trên tất cả
