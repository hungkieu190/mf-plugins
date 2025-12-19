# Phase 2: Integrate License System vào Plugin Thương Mại

## Tổng quan
Hướng dẫn này giúp bạn tích hợp Mamflow License System vào plugin thương mại của bạn.

**Reference Code:** `inc/example-licensed-plugin/`

---

## Bước 1: Copy Files vào Plugin

### Files cần copy:
```
your-commercial-plugin/
├── includes/
│   ├── license/
│   │   ├── class-license-handler.php      (copy từ example)
│   │   ├── admin-license-page.php         (copy từ example)
│   │   └── cron-scheduler.php             (copy từ example)
```

### Checklist:
- [ ] Copy `class-license-handler.php` 
- [ ] Copy `admin-license-page.php`
- [ ] Copy `cron-scheduler.php`
- [ ] Đổi tên class `Example_Licensed_Plugin_Cron` → `Your_Plugin_Cron`

---

## Bước 2: Update Main Plugin File

### File: `your-plugin.php`

```php
<?php
/**
 * Plugin Name: Your Commercial Plugin
 * Version: 1.0.0
 * ...
 */

// Define constants
define('YOUR_PLUGIN_VERSION', '1.0.0');
define('YOUR_PLUGIN_DIR', plugin_dir_path(__FILE__));

// IMPORTANT: Replace với Product ID thực tế trên mamflow.com
define('YOUR_PLUGIN_PRODUCT_ID', 123); // ← Đổi số này

class Your_Commercial_Plugin {
    private static $instance = null;
    private $license_handler;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_license_system();
        $this->init();
    }
    
    private function load_license_system() {
        require_once YOUR_PLUGIN_DIR . 'includes/license/class-license-handler.php';
        require_once YOUR_PLUGIN_DIR . 'includes/license/admin-license-page.php';
        require_once YOUR_PLUGIN_DIR . 'includes/license/cron-scheduler.php';
        
        // Initialize license handler
        $this->license_handler = new Mamflow_License_Handler([
            'product_id' => YOUR_PLUGIN_PRODUCT_ID,
            'product_name' => 'Your Plugin Name',
            'api_url' => 'https://mamflow.com/wp-json/mamflow/v1',
            'option_key' => 'your_plugin_license_data' // Unique key
        ]);
    }
    
    private function init() {
        // Admin menu
        add_action('admin_menu', [$this, 'add_license_menu']);
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);
        
        // License notices
        add_action('admin_notices', [$this, 'license_notices']);
    }
    
    public function on_activation() {
        Your_Plugin_Cron::schedule_license_check();
    }
    
    public function on_deactivation() {
        Your_Plugin_Cron::clear_license_check();
    }
    
    public function add_license_menu() {
        add_submenu_page(
            'options-general.php', // Hoặc add dưới plugin settings
            'License Settings',
            'License',
            'manage_options',
            'your-plugin-license',
            'your_plugin_render_license_page' // Function trong admin-license-page.php
        );
    }
    
    public function license_notices() {
        if (!$this->license_handler->is_feature_enabled()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong>Your Plugin:</strong> 
                    <a href="<?php echo admin_url('options-general.php?page=your-plugin-license'); ?>">
                        Activate your license
                    </a> to unlock all features.
                </p>
            </div>
            <?php
        }
    }
    
    public function get_license_handler() {
        return $this->license_handler;
    }
}

// Initialize
Your_Commercial_Plugin::get_instance();
```

### Checklist Bước 2:
- [ ] Đổi tên class thành tên plugin của bạn
- [ ] Cập nhật `YOUR_PLUGIN_PRODUCT_ID` với product ID thật
- [ ] Đổi `option_key` thành unique key cho plugin
- [ ] Test plugin activate/deactivate

---

## Bước 3: Customize Admin License Page

### File: `includes/license/admin-license-page.php`

**Thay đổi function name:**
```php
// Từ:
function example_licensed_plugin_render_license_page()

// Thành:
function your_plugin_render_license_page()
```

**Thay đổi nội dung:**
- [ ] Đổi tiêu đề page
- [ ] Đổi text instructions
- [ ] Đổi links tới documentation của bạn
- [ ] Customize styling (optional)

---

## Bước 4: Update Cron Scheduler

### File: `includes/license/cron-scheduler.php`

**Đổi class name:**
```php
// Từ:
class Example_Licensed_Plugin_Cron

// Thành:
class Your_Plugin_Cron
```

**Đổi cron hook:**
```php
// Từ:
const CRON_HOOK = 'example_licensed_plugin_daily_license_check';

// Thành:
const CRON_HOOK = 'your_plugin_daily_license_check';
```

**Update init section:**
```php
add_action('init', function() {
    add_action(Your_Plugin_Cron::CRON_HOOK, [Your_Plugin_Cron::class, 'run_license_check']);
});
```

### Checklist Bước 4:
- [ ] Đổi class name
- [ ] Đổi cron hook name (phải unique)
- [ ] Test cron scheduling: `wp cron event list`

---

## Bước 5: Implement Feature Gating

### Ví dụ 1: Gate Admin Page

```php
function your_plugin_premium_settings_page() {
    $plugin = Your_Commercial_Plugin::get_instance();
    $license_handler = $plugin->get_license_handler();
    
    if (!$license_handler->is_feature_enabled()) {
        ?>
        <div class="wrap">
            <h1>Premium Settings</h1>
            <div class="notice notice-error">
                <p>
                    This feature requires an active license.
                    <a href="<?php echo admin_url('options-general.php?page=your-plugin-license'); ?>">
                        Activate License
                    </a>
                </p>
            </div>
        </div>
        <?php
        return;
    }
    
    // Premium settings UI here
}
```

### Ví dụ 2: Gate Shortcode

```php
add_shortcode('your_premium_shortcode', function($atts) {
    $plugin = Your_Commercial_Plugin::get_instance();
    $license_handler = $plugin->get_license_handler();
    
    if (!$license_handler->is_feature_enabled()) {
        return '<p><em>This feature requires a license. <a href="https://mamflow.com">Purchase here</a></em></p>';
    }
    
    // Premium shortcode logic
    return '<div>Premium content</div>';
});
```

### Ví dụ 3: Conditional Hook

```php
$plugin = Your_Commercial_Plugin::get_instance();
if ($plugin->get_license_handler()->is_feature_enabled()) {
    add_action('wp_footer', 'your_premium_footer_code');
    add_filter('the_content', 'your_premium_content_filter');
}
```

### Checklist Bước 5:
- [ ] Identify các premium features cần gate
- [ ] Wrap với `is_feature_enabled()` check
- [ ] Test với license inactive
- [ ] Test với license active

---

## Bước 6: Testing Checklist

### Test Local Development

**Setup:**
- [ ] Có test product trên mamflow.com với `_mamflow_licensed_product = yes`
- [ ] Note lại Product ID
- [ ] Update `YOUR_PLUGIN_PRODUCT_ID` trong code

**Test Activation:**
- [ ] Complete test order trên mamflow.com
- [ ] Check email nhận license key
- [ ] Activate plugin
- [ ] Vào Settings → License
- [ ] Nhập license key và activate
- [ ] Verify success message
- [ ] Check premium features unlock

**Test Validation:**
- [ ] Trigger cron: `wp cron event run your_plugin_daily_license_check`
- [ ] Check license status vẫn active
- [ ] Verify `wp_options` table có data đúng

**Test Deactivation:**
- [ ] Click "Deactivate License" button
- [ ] Verify features bị lock
- [ ] Check `wp_options` table data cleared

**Test Refund:**
- [ ] Activate license
- [ ] Refund order trên mamflow.com
- [ ] Trigger cron check
- [ ] Verify license becomes invalid
- [ ] Verify features locked

**Test Edge Cases:**
- [ ] Activate với invalid key → Error message
- [ ] Activate với key của product khác → Error  
- [ ] Network error → Graceful handling
- [ ] Cron fail → 72h fallback works

---

## Bước 7: Production Deployment

### Pre-deployment:
- [ ] Remove all debug code (error_log, var_dump)
- [ ] Remove test product IDs
- [ ] Update với production product ID
- [ ] Test trên staging environment
- [ ] Verify cron runs (không bị host disable)

### API Configuration:
- [ ] Confirm API URL: `https://mamflow.com/wp-json/mamflow/v1`
- [ ] Test từ production server (firewall allow outbound)
- [ ] Verify SSL certificate valid

### Documentation:
- [ ] Viết docs cho customers về activation
- [ ] Screenshots của license page
- [ ] FAQ về lost license keys
- [ ] Support email/form

### Checklist Final:
- [ ] Plugin works without license (với limited features)
- [ ] Clear messaging khi license inactive
- [ ] Link to purchase page
- [ ] Admin notices không spam
- [ ] Cron không ảnh hưởng performance

---

## Bước 8: Customer Support Preparation

### Common Issues:

**1. License won't activate**
→ Check product ID matches
→ Verify site URL không có typo
→ Check firewall/proxy settings

**2. License randomly deactivates**
→ Check cron running
→ Verify server time correct
→ Check for refunds/chargebacks

**3. Moving to new domain**
→ Deactivate on old domain first
→ Activate on new domain
→ Contact support nếu exceed limit

### Support Tools:
- [ ] Tạo debug info function (show license status, last check time, site URL)
- [ ] Log failed API calls cho debugging
- [ ] Admin notice khi license sắp expire

---

## Files Reference

Tất cả example code ở:
- `inc/example-licensed-plugin/example-licensed-plugin.php`
- `inc/example-licensed-plugin/includes/class-license-handler.php`
- `inc/example-licensed-plugin/includes/admin-license-page.php`
- `inc/example-licensed-plugin/includes/cron-scheduler.php`
- `inc/example-licensed-plugin/README.md`

## API Endpoints Reference

**Base URL:** `https://mamflow.com/wp-json/mamflow/v1`

1. **POST /activate** - Activate license
2. **POST /check** - Validate license
3. **POST /deactivate** - Deactivate license

Chi tiết payload xem trong `class-license-handler.php`

---

## Next Steps Tomorrow

1. ✅ Mở plugin thương mại thực tế
2. ✅ Follow checklist từ Bước 1 → 8
3. ✅ Test thoroughly
4. ✅ Deploy to staging
5. ✅ Get first customer to test real activation

## Cần đặc biệt chú ý
Lưu ý cực kỳ quan trọng vì nhiều plugin đề sử dụng các active này, nên phải đổi tên class theo từng sản phẩm để tránh lỗi 
PHP Fatal error:  Cannot declare class Mamflow_License_Handler


Good luck! 🚀
