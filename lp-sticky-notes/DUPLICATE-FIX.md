# LP Sticky Notes - Fix Duplicate Render Issue

## Vấn đề
Khi active plugin, sticky notes bị render 2 lần, khiến footer bị đẩy vào sai vị trí trong DOM.

### Root Cause
- Plugin sử dụng 2 hooks khác nhau:
  1. `learn-press/after-content-item-summary/lp_lesson` → render lần 1
  2. `learn-press/after-main-content` → render lần 2
  
- Mỗi hook gọi một function riêng với static flag riêng, nên không prevent được duplicate

## Giải pháp

### 1. Gộp logic vào một hook
Chỉ sử dụng hook `learn-press/after-main-content` duy nhất vì:
- ✅ Hook này chạy sau khi tất cả content rendered (bao gồm protected message)
- ✅ Hoạt động với cả normal và finished courses
- ✅ Không bị duplicate

### 2. Merge logic vào function duy nhất
Gộp logic của `render_sticky_notes_section()` và `render_sticky_notes_section_for_finished()` thành một function:

```php
public function render_sticky_notes_section() {
    // Static flag để chỉ render 1 lần
    static $rendered = false;
    if ($rendered) {
        return;
    }
    
    // Check logged in
    // Check lesson page
    // Check enrolled OR finished
    // Render template
    
    $rendered = true;
}
```

### 3. Xóa function không dùng
Deleted `render_sticky_notes_section_for_finished()` vì logic đã được merge.

## Kết quả
✅ Sticky notes chỉ render 1 lần
✅ Footer không bị đẩy vào sai vị trí
✅ Hoạt động với cả normal và finished courses
✅ Code cleaner, dễ maintain

## Files Changed
- `/inc/class-lp-sticky-notes-hooks.php`
  - Removed duplicate hook
  - Merged render logic
  - Added static $rendered flag
  - Deleted unused function
