# Module 10 — License Integration Plan

## ✅ Đã cập nhật IMPLEMENTATION_PLAN.md

**Date:** 2026-02-12
**Reference:** `mamflow-license-integration-guide.md`

---

## 📋 Tổng quan

Module 10 đã được thêm vào IMPLEMENTATION_PLAN.md với đầy đủ chi tiết về:

### 1. Unique Naming (CRITICAL!)

Tất cả class names và hook names đều UNIQUE để tránh conflict với plugins khác:

| Element | Value |
|---------|-------|
| License Handler Class | `MF_LLS_License_Handler` |
| Cron Class | `MF_LLS_License_Cron` |
| Cron Hook | `mf_lls_daily_license_check` |
| License Tab Function | `mf_lls_render_license_tab` |
| License Tab Slug | `live-studio` |
| Option Key | `mf_lls_license` |
| Product ID Constant | `MF_LLS_PRODUCT_ID` |

### 2. File Structure

```
includes/
└── license/
    ├── class-license-handler.php      # MF_LLS_License_Handler
    ├── shared-license-page.php        # Copy as-is from reference
    ├── admin-license-page.php         # mf_lls_render_license_tab()
    └── cron-scheduler.php             # MF_LLS_License_Cron
```

### 3. Implementation Steps (10 sections)

- **10.1** Unique Naming (6 checklist items)
- **10.2** File Structure (1 item)
- **10.3** License Handler (10 items)
- **10.4** Shared License Page (1 item)
- **10.5** Admin License Page (8 items)
- **10.6** Cron Scheduler (6 items)
- **10.7** Refactor Main Plugin File (7 items)
- **10.8** Feature Gating (3 items)
- **10.9** Verification Checklist (4 sections)
- **10.10** Configuration Reference

**Total:** 38 checklist items

### 4. Feature Gating Strategy

**Premium Features (require license):**
- Platform integrations (Zoom, Google Meet, Agora)
- Attendance tracking
- Rating system
- Email reminders

**Free Features (no license required):**
- View live sessions (read-only)
- Basic countdown timer

### 5. Dependency Graph Updated

```
Module 1 (Core & Admin Settings)
    │
    ├── Module 10 (License Integration) ← Refactor Module 1 to singleton
    │
    ├── Module 2 (Live Lesson Type)
    ...
```

Module 10 gates all premium features (Modules 2-7)

---

## 📊 Updated Statistics

| Category           | Before | After | Change |
|--------------------|--------|-------|--------|
| Total Tasks        | 141    | 179   | +38    |
| Total Modules      | 8      | 9     | +1     |
| Estimated Timeline | 20-28d | 24-34d| +4-6d  |

### Build Order Updated

**Old:**
Module 1 → 2 → 3 → 4 → 9 → 5 → 7 → 6 → Test → Deploy

**New:**
Module 1 → 2 → 3 → 4 → 9 → 5 → 7 → 6 → **Module 10** → Test → Deploy

---

## 🎯 Key Points for Implementation

### 1. Singleton Pattern Required

Module 1 (`MF_LLS_Addon`) MUST be refactored to singleton pattern:

```php
class MF_LLS_Addon extends LP_Addon {
    protected static $instance = null;
    private $license_handler;
    
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        parent::__construct();
        $this->load_license_system();
    }
}
```

### 2. License Handler Configuration

```php
$this->license_handler = new MF_LLS_License_Handler(
    array(
        'product_id'   => MF_LLS_PRODUCT_ID,
        'product_name' => 'LearnPress Live Studio',
        'api_url'      => 'https://mamflow.com/wp-json/mamflow/v1',
        'option_key'   => 'mf_lls_license',
    )
);
```

### 3. Feature Gating Example

```php
public function create_meeting() {
    $license_handler = MF_LLS_Addon::instance()->get_license_handler();
    if ( ! $license_handler->is_feature_enabled() ) {
        error_log( '[Live Studio]: Feature blocked - license not active' );
        return new WP_Error( 'license_required', __( 'License required', 'learnpress-live-studio' ) );
    }
    // Continue with feature logic
}
```

### 4. Admin Notice

```php
public function license_notice() {
    if ( ! $this->license_handler->is_feature_enabled() ) {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>LearnPress Live Studio:</strong>
                Please <a href="<?php echo admin_url( 'admin.php?page=mamflow-license&tab=live-studio' ); ?>">activate your license</a> to enable premium features.
            </p>
        </div>
        <?php
    }
}
```

---

## ✅ Verification Checklist

### Pre-deployment
- [ ] Product ID obtained from mamflow.com
- [ ] All class names verified unique (grep search)
- [ ] All hook names verified unique
- [ ] PHP syntax check passed
- [ ] No conflicts with other Mamflow plugins

### WordPress Admin Tests
- [ ] Plugin activates without errors
- [ ] Admin notice appears
- [ ] Notice links to correct license page
- [ ] "Live Studio" tab appears in Mamflow License page
- [ ] License form displays correctly
- [ ] Activate/Deactivate buttons work

### Feature Gating Tests
- [ ] Premium features blocked without license
- [ ] Errors logged (not shown to users)
- [ ] All features unlock with valid license
- [ ] Admin notice disappears with license

### Cron Tests
- [ ] Cron scheduled on activation
- [ ] Cron cleared on deactivation
- [ ] Daily license validation works
- [ ] Admin notification sent if invalid

---

## 📝 Reference Files

**Copy from:** `lp-telegram-notifier` plugin

1. `includes/license/shared-license-page.php` → Copy as-is
2. `includes/license/class-license-handler.php` → Change class name to `MF_LLS_License_Handler`
3. `includes/license/admin-license-page.php` → Change function name to `mf_lls_render_license_tab`
4. `includes/license/cron-scheduler.php` → Change class to `MF_LLS_License_Cron`

**Guide:** `mamflow-license-integration-guide.md` (604 lines)

---

## 🚀 Next Steps

1. **Complete Module 1-9 first** (core functionality)
2. **Then implement Module 10** (license system)
3. **Test thoroughly** with test license key
4. **Deploy** with production product ID

---

**Status:** ✅ Plan Updated
**Ready for:** Implementation after Modules 1-9 complete
