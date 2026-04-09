# Mamflow License System Integration Guide

> **Purpose**: Complete step-by-step guide for integrating Mamflow License System into any LearnPress plugin.
> **Last Updated**: 2026-03-05
> **Tested On**: LP-Telegram-Notifier (47313), MF-Quiz-Importer-For-LearnPress

---

## 📋 Overview

Mamflow License System provides centralized license management for all Mamflow plugins. Each plugin:
- Has its own license handler with **unique class name**
- Registers a tab in unified "Mamflow License" page
- Gates features based on license status
- Validates license daily via WP Cron

---

## ⚠️ CRITICAL REQUIREMENTS

### 1. Unique Class Names (MUST DO!)

**❌ DO NOT use these class names (will cause conflicts):**
- `Mamflow_License_Handler`
- `LP_Sticky_Notes_Cron`
- Any class name from other Mamflow plugins

**✅ MUST create unique names per plugin:**
```php
// Pattern: MF_{PLUGIN_ABBREVIATION}_License_Handler
// Examples:
class MF_LP_TG_License_Handler {}      // LP Telegram Notifier
class MF_LP_SN_License_Handler {}      // LP Sticky Notes  
class MF_LP_QZ_License_Handler {}      // LP Quiz Extension
```

### 2. Product ID Required

- Get from mamflow.com product page
- Usually 5-digit number (e.g., 47313)
- Must exist in WooCommerce before testing

### 3. Settings Page License Gate (MANDATORY RULE)

**⚠️ GENERAL RULE**: All admin settings pages for premium features MUST implement a license gate overlay. Do not allow users to view or modify settings without an active license.

**❌ Forbidden Behaviors:**
- Displaying settings options to unlicensed users.
- Relying only on admin notices (they are easily ignored).
- Providing a "close" button that just hides the popup but stays on the settings page.

**✅ Mandatory Requirements:**
- **License Check**: Must be the first check in the `render_settings_page()` method.
- **Visual Gate**: Full-screen overlay with blurred background (`filter: blur(5px)`).
- **Dashboard Redirect**: Must include a close ("X") button that redirects the user to the WordPress Dashboard (`admin_url('index.php')`).
- **Call to Action**: Direct link to the specific license activation tab.
- **Value Proposition**: List the premium features that will be unlocked.

**Reference Implementation**: `lp-live-studio` (Module 10 integration)

**Implementation Pattern**:
```php
public function render_settings_page() {
    // Check license FIRST
    $license_handler = Your_Plugin::instance()->get_license_handler();
    $is_licensed     = $license_handler->is_feature_enabled();
    
    // If not licensed, show gate overlay
    if ( ! $is_licensed ) {
        $this->render_license_gate();
        return;
    }
    
    // Normal settings page (only if licensed)
    include PLUGIN_PATH . '/includes/admin/views/settings-page.php';
}

private function render_license_gate() {
    // Blurred settings preview + overlay modal
    // Modal MUST include a close button linking to admin_url('index.php')
    // See lp-live-studio for complete implementation
}
```

**Why This Matters**:
- Prevents users from configuring features they can't use
- Clear call-to-action for license activation
- Professional user experience
- Consistent across all Mamflow plugins

### 4. License Validation Rules — 3-Layer Protection (MANDATORY)

Mỗi plugin Mamflow PHẢI implement đủ 3 lớp bảo vệ license. **KHÔNG được bỏ bớt bất kỳ lớp nào.**

#### Layer 1: Local Expiry Check (trong `is_feature_enabled()`)

**Mục đích**: Block ngay lập tức khi license hết hạn — KHÔNG cần gọi API, KHÔNG phụ thuộc cron.

```php
// Trong is_feature_enabled() — SAU khi check status === 'active'
if ( ! empty( $license_data['expires_at'] ) ) {
    if ( strtotime( $license_data['expires_at'] ) < current_time( 'timestamp' ) ) {
        // Mark invalid locally so subsequent calls are fast.
        $license_data['status'] = 'invalid';
        update_option( $this->option_key, $license_data );
        return false;
    }
}
```

**Tại sao quan trọng**: Nếu cron bị disable (shared hosting, WP Cron tắt), đây là tuyến phòng thủ duy nhất chặn license hết hạn.

#### Layer 2: Fallback 72h API Re-check (trong `is_feature_enabled()`)

**Mục đích**: Nếu `last_check` > 72 giờ, tự động gọi API verify — bảo vệ trường hợp cron bị tắt hoặc license bị revoke/refund trên server.

```php
// Trong is_feature_enabled() — SAU local expiry check
$last_check = isset( $license_data['last_check'] ) ? $license_data['last_check'] : 0;
$hours_since_check = ( current_time( 'timestamp' ) - $last_check ) / HOUR_IN_SECONDS;

if ( $hours_since_check > 72 ) {
    return $this->check_license_status();
}
```

**Tại sao quan trọng**: License bị revoke/ban trên server sẽ được phát hiện trong tối đa 72 giờ, kể cả khi cron không chạy.

#### Layer 3: Connection Error Handling (trong `check_license_status()`)

**Mục đích**: Khi API server down, giữ nguyên status hiện tại NHƯNG vẫn enforce local expiry.

```php
// Trong check_license_status() — khi is_wp_error($response)
if ( is_wp_error( $response ) ) {
    if ( 'active' !== $license_data['status'] ) {
        return false;
    }
    // Even if status is active, block if locally expired.
    if ( ! empty( $license_data['expires_at'] ) && strtotime( $license_data['expires_at'] ) < current_time( 'timestamp' ) ) {
        $license_data['status'] = 'invalid';
        update_option( $this->option_key, $license_data );
        return false;
    }
    return true;
}
```

**Tại sao quan trọng**: Tránh 2 rủi ro:
- API server down → license bị block nhầm (false negative)
- API server down + license đã hết hạn → user vẫn dùng được (false positive)

#### Bảng tổng hợp coverage

| Trường hợp | Layer 1 | Layer 2 | Layer 3 |
|---|---|---|---|
| License hết hạn, cron hoạt động | ✅ | — | — |
| License hết hạn, cron **bị tắt** | ✅ | — | — |
| License bị refund/ban trên server | — | ✅ (max 72h) | — |
| API server down, license còn hạn | — | — | ✅ giữ active |
| API server down, license hết hạn | — | — | ✅ block |
| Chưa check > 72h | — | ✅ force re-check | — |

#### ❌ Forbidden Behaviors

- **KHÔNG** chỉ dựa vào cron để check expiry — cron có thể bị tắt
- **KHÔNG** cho phép dùng feature khi API error + license đã hết hạn
- **KHÔNG** gọi API mỗi lần `is_feature_enabled()` — chỉ gọi khi > 72h hoặc qua cron
- **KHÔNG** xóa `expires_at` khỏi option khi ghi — phải luôn đồng bộ từ API response

#### `check_license_status()` — Sync `expires_at` từ API

Khi API trả về thành công, PHẢI update cả `expires_at` vào local:

```php
// Trong check_license_status() — sau khi parse response
$license_data['status']     = ( isset( $body['valid'] ) && $body['valid'] ) ? 'active' : 'invalid';
$license_data['last_check'] = current_time( 'timestamp' );

if ( isset( $body['expires_at'] ) ) {
    $license_data['expires_at'] = $body['expires_at'];
}

update_option( $this->option_key, $license_data );
```

---

## 🗂️ File Structure

```
your-plugin/
├── your-plugin.php              # Main file (refactor to singleton)
└── includes/
    └── license/                 # NEW FOLDER - Create this
        ├── class-license-handler.php      # Unique class name
        ├── shared-license-page.php        # Copy as-is
        ├── admin-license-page.php         # Customize function name
        └── cron-scheduler.php             # Unique class name
```

---

## 🚀 Step-by-Step Implementation

### Step 1: Create License Folder

```bash
mkdir -p includes/license
```

---

### Step 2: Create License Handler

**File**: `includes/license/class-license-handler.php`

**Template**:
```php
<?php
/**
 * License Handler for [YOUR PLUGIN NAME]
 * 
 * @package MF_[ABBREVIATION]_License_Handler
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MF_[ABBREVIATION]_License_Handler {
    
    private $product_id;
    private $product_name;
    private $api_url;
    private $option_key;
    
    public function __construct( $config ) {
        $this->product_id   = $config['product_id'];
        $this->product_name = $config['product_name'];
        $this->api_url      = isset( $config['api_url'] ) ? $config['api_url'] : 'https://mamflow.com/wp-json/mamflow/v1';
        $this->option_key   = $config['option_key'];
    }
    
    /**
     * Activate license
     * @param string $license_key License key from user.
     * @return array Response with success status and message.
     */
    public function activate_license( $license_key ) {
        // Copy from reference plugin — call API /activate endpoint
        // Store: license_key, status, domain, expires_at, last_check
    }
    
    /**
     * Deactivate license
     * @return array Response with success status and message.
     */
    public function deactivate_license() {
        // Copy from reference plugin — call API /deactivate endpoint
        // Delete option on success
    }
    
    /**
     * Check license status via API (called by cron + 72h fallback)
     *
     * MUST implement Layer 3: Connection Error Handling
     * See section "4. License Validation Rules" for details.
     *
     * @return bool True if license is valid.
     */
    public function check_license_status() {
        $license_data = get_option( $this->option_key );

        if ( ! $license_data || empty( $license_data['license_key'] ) ) {
            return false;
        }

        $response = wp_remote_post(
            $this->api_url . '/check',
            array(
                'body'    => wp_json_encode( array(
                    'license_key' => $license_data['license_key'],
                    'domain'      => $license_data['domain'],
                ) ),
                'headers' => array( 'Content-Type' => 'application/json' ),
                'timeout' => 15,
            )
        );

        // Layer 3: Connection error — keep status but enforce local expiry
        if ( is_wp_error( $response ) ) {
            if ( 'active' !== $license_data['status'] ) {
                return false;
            }
            if ( ! empty( $license_data['expires_at'] ) && strtotime( $license_data['expires_at'] ) < current_time( 'timestamp' ) ) {
                $license_data['status'] = 'invalid';
                update_option( $this->option_key, $license_data );
                return false;
            }
            return true;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // Sync status + expires_at from API
        $license_data['status']     = ( isset( $body['valid'] ) && $body['valid'] ) ? 'active' : 'invalid';
        $license_data['last_check'] = current_time( 'timestamp' );

        if ( isset( $body['expires_at'] ) ) {
            $license_data['expires_at'] = $body['expires_at'];
        }

        update_option( $this->option_key, $license_data );

        return isset( $body['valid'] ) && $body['valid'];
    }
    
    /**
     * Check if premium features are enabled
     *
     * MUST implement Layer 1 (local expiry) + Layer 2 (72h fallback)
     * See section "4. License Validation Rules" for details.
     *
     * @return bool True if license is valid and active.
     */
    public function is_feature_enabled() {
        $license_data = get_option( $this->option_key );

        if ( ! $license_data ) {
            return false;
        }

        if ( ! isset( $license_data['status'] ) || 'active' !== $license_data['status'] ) {
            return false;
        }

        // Layer 1: Local expiry — works even when cron is disabled
        if ( ! empty( $license_data['expires_at'] ) ) {
            if ( strtotime( $license_data['expires_at'] ) < current_time( 'timestamp' ) ) {
                $license_data['status'] = 'invalid';
                update_option( $this->option_key, $license_data );
                return false;
            }
        }

        // Layer 2: Fallback 72h — force API re-check if cron missed
        $last_check        = isset( $license_data['last_check'] ) ? $license_data['last_check'] : 0;
        $hours_since_check = ( current_time( 'timestamp' ) - $last_check ) / HOUR_IN_SECONDS;

        if ( $hours_since_check > 72 ) {
            return $this->check_license_status();
        }

        return true;
    }
    
    /**
     * Get license data
     * @return array|false
     */
    public function get_license_data() {
        return get_option( $this->option_key );
    }
    
    /**
     * Get current site domain
     * @return string
     */
    public function get_site_domain() {
        // Copy from reference plugin
    }
    
    /**
     * Get days until license expires
     * @return int|null Days until expiration, null if lifetime.
     */
    public function get_days_until_expiration() {
        $license_data = $this->get_license_data();
        if ( ! $license_data || empty( $license_data['expires_at'] ) ) {
            return null;
        }
        $expires_timestamp = strtotime( $license_data['expires_at'] );
        $current_timestamp = current_time( 'timestamp' );
        $days = floor( ( $expires_timestamp - $current_timestamp ) / DAY_IN_SECONDS );
        return max( 0, $days );
    }
    
    /**
     * Check if license is expired
     * @return bool
     */
    public function is_expired() {
        $license_data = $this->get_license_data();
        if ( ! $license_data || empty( $license_data['expires_at'] ) ) {
            return false;
        }
        return strtotime( $license_data['expires_at'] ) < current_time( 'timestamp' );
    }
}
```

**Checklist**:
- [ ] Replace `[ABBREVIATION]` with your plugin abbreviation (e.g., TG, SN, QZ)
- [ ] Replace `[YOUR PLUGIN NAME]` with actual plugin name
- [ ] Copy all method bodies from reference plugin
- [ ] Class name is unique (search globally to verify)

---

### Step 3: Copy Shared License Page

**File**: `includes/license/shared-license-page.php`

**Action**: Copy exactly from `lp-sticky-notes` or `lp-telegram-notifier`

```bash
cp lp-sticky-notes/inc/license/shared-license-page.php your-plugin/includes/license/
```

**⚠️ NO MODIFICATIONS NEEDED** - This file uses global functions with existence checks.

---

### Step 4: Create Admin License Page

**File**: `includes/license/admin-license-page.php`

**Changes Required**:
1. Function name: `[plugin_prefix]_render_license_tab`
2. Plugin instance: `Your_Plugin_Class::instance()`
3. Text domain: `'your-plugin-domain'`
4. Option key: `'your_plugin_license'`

**Template**:
```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( '[PREFIX]_render_license_tab' ) ) {
    function [PREFIX]_render_license_tab() {
        // Get license handler instance
        $plugin          = [YOUR_PLUGIN_CLASS]::instance();
        $license_handler = $plugin->get_license_handler();

        // Copy rest from lp-telegram-notifier
        // Remember to update:
        // - All text domain to 'your-plugin-domain'
        // - All translatable strings
        // - Plugin name references
    }
}
```

**Checklist**:
- [ ] Function name is unique
- [ ] Text domain updated everywhere
- [ ] Plugin class reference correct
- [ ] Option key matches handler

---

### Step 5: Create Cron Scheduler

**File**: `includes/license/cron-scheduler.php`

**Changes Required**:
1. Class name: `MF_[ABBREVIATION]_Cron` (unique)
2. Cron hook: `mf_[abbreviation]_daily_license_check` (unique)
3. Plugin instance: `Your_Plugin_Class::instance()`

**Template**:
```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MF_[ABBREVIATION]_Cron {
    
    const CRON_HOOK = 'mf_[abbreviation]_daily_license_check';
    
    public static function schedule_license_check() {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( strtotime( 'tomorrow 3:00 AM' ), 'daily', self::CRON_HOOK );
        }
        add_action( self::CRON_HOOK, array( __CLASS__, 'run_license_check' ) );
    }
    
    public static function clear_license_check() {
        $timestamp = wp_next_scheduled( self::CRON_HOOK );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::CRON_HOOK );
        }
        wp_clear_scheduled_hook( self::CRON_HOOK );
    }
    
    public static function run_license_check() {
        $plugin          = [YOUR_PLUGIN_CLASS]::instance();
        $license_handler = $plugin->get_license_handler();
        $is_valid        = $license_handler->check_license_status();
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[YOUR PLUGIN]: Daily license check - ' . ( $is_valid ? 'VALID' : 'INVALID' ) );
        }
        
        if ( ! $is_valid ) {
            self::send_admin_notification();
        }
    }
    
    private static function send_admin_notification() {
        // Copy from lp-telegram-notifier and update strings
    }
}

add_action( 'init', function () {
    add_action( MF_[ABBREVIATION]_Cron::CRON_HOOK, array( MF_[ABBREVIATION]_Cron::class, 'run_license_check' ) );
} );
```

**Checklist**:
- [ ] Class name is unique
- [ ] Cron hook name is unique
- [ ] Plugin class reference correct
- [ ] Log messages updated

---

### Step 6: Refactor Main Plugin File

**File**: `your-plugin.php`

**Required Changes**:

#### 6.1 Add Constants

```php
// After existing constants
define( '[PREFIX]_PRODUCT_ID', 12345 ); // Your product ID from mamflow.com
```

#### 6.2 Convert to Singleton Pattern

```php
class Your_Plugin_Class {
    
    protected static $instance = null;
    private $license_handler;
    
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->load_license_system();
        $this->hooks();
    }
    
    private function includes() {
        // License files FIRST
        require_once [PLUGIN_PATH] . 'includes/license/class-license-handler.php';
        require_once [PLUGIN_PATH] . 'includes/license/shared-license-page.php';
        require_once [PLUGIN_PATH] . 'includes/license/admin-license-page.php';
        require_once [PLUGIN_PATH] . 'includes/license/cron-scheduler.php';
        
        // Then other includes
        require_once [PLUGIN_PATH] . 'includes/class-your-handler.php';
    }
    
    private function load_license_system() {
        $this->license_handler = new MF_[ABBREVIATION]_License_Handler(
            array(
                'product_id'   => [PREFIX]_PRODUCT_ID,
                'product_name' => 'Your Plugin Name',
                'api_url'      => 'https://mamflow.com/wp-json/mamflow/v1',
                'option_key'   => '[prefix]_license',
            )
        );
    }
    
    private function hooks() {
        add_action( 'plugins_loaded', array( $this, 'check_learnpress' ) );
        
        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'add_license_menu' ), 100 );
            add_action( 'admin_notices', array( $this, 'license_notice' ) );
        }
        
        register_activation_hook( [PLUGIN_FILE], array( $this, 'activate' ) );
        register_deactivation_hook( [PLUGIN_FILE], array( $this, 'deactivate' ) );
    }
    
    public function add_license_menu() {
        if ( ! class_exists( 'LearnPress' ) ) {
            return;
        }
        
        mamflow_register_license_menu();
        add_filter( 'mamflow_license_tabs', array( $this, 'register_license_tab' ) );
    }
    
    public function register_license_tab( $tabs ) {
        $tabs['[tab-slug]'] = array(
            'title'    => esc_html__( 'Your Plugin Name', '[text-domain]' ),
            'callback' => '[prefix]_render_license_tab',
            'priority' => 20, // Adjust as needed
        );
        return $tabs;
    }
    
    public function license_notice() {
        if ( ! class_exists( 'LearnPress' ) ) {
            return;
        }
        
        if ( isset( $_GET['page'] ) && 'mamflow-license' === $_GET['page'] ) {
            return;
        }
        
        if ( ! $this->license_handler->is_feature_enabled() ) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e( 'Your Plugin Name:', '[text-domain]' ); ?></strong>
                    <?php
                    printf(
                        esc_html__( 'Please %1$sactivate your license%2$s to enable premium features.', '[text-domain]' ),
                        '<a href="' . esc_url( admin_url( 'admin.php?page=mamflow-license&tab=[tab-slug]' ) ) . '">',
                        '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }
    
    public function get_license_handler() {
        return $this->license_handler;
    }
    
    public function activate() {
        MF_[ABBREVIATION]_Cron::schedule_license_check();
    }
    
    public function deactivate() {
        MF_[ABBREVIATION]_Cron::clear_license_check();
    }
}

// Initialize
Your_Plugin_Class::instance();
```

**Checklist**:
- [ ] Class converted to singleton
- [ ] License files included first
- [ ] License handler initialized
- [ ] Admin menu hooks added
- [ ] License notice implemented
- [ ] Activation/deactivation hooks updated
- [ ] Tab slug is unique

---

### Step 7: Add License Gating

**Location**: Where your plugin's main feature executes

**Example** (for notification handler):
```php
public function your_main_feature_method() {
    // Check license FIRST
    $license_handler = Your_Plugin_Class::instance()->get_license_handler();
    if ( ! $license_handler->is_feature_enabled() ) {
        error_log( '[Your Plugin]: Feature blocked - license not active' );
        return;
    }
    
    // Continue with feature logic
    // ...
}
```

**Checklist**:
- [ ] License check at beginning of method
- [ ] Error logged for debugging
- [ ] Graceful return (no user-facing errors)

---

## ✅ Verification Checklist

### Pre-Deployment Checks

```bash
# 1. PHP Syntax Check
php -l your-plugin.php
php -l includes/license/class-license-handler.php
php -l includes/license/admin-license-page.php
php -l includes/license/cron-scheduler.php

# 2. Search for Conflicts
grep -r "class Mamflow_License_Handler" your-plugin/
# Should return 0 results

grep -r "class MF_[YOUR_ABBREVIATION]_License_Handler" your-plugin/
# Should return exactly 1 result

# 3. Verify Unique Hook Names
grep -r "mf_[your_abbreviation]_daily_license_check" your-plugin/
# Should return 2-3 results (all in cron-scheduler.php)
```

### WordPress Admin Tests

- [ ] Plugin activates without errors
- [ ] Admin notice appears: "Please activate your license..."
- [ ] Notice links to `admin.php?page=mamflow-license&tab=[your-tab]`
- [ ] LearnPress → Mamflow License page exists
- [ ] Your plugin tab appears in license page
- [ ] Clicking tab shows license form
- [ ] Form displays "License Not Activated" warning
- [ ] License key input field present
- [ ] "Activate License" button works

### Feature Gating Tests

**Without License**:
- [ ] Main feature is blocked
- [ ] Error logged to debug.log
- [ ] No user-facing errors
- [ ] Admin notice persists

**With Test License** (if API available):
- [ ] License activates successfully
- [ ] Admin notice disappears
- [ ] Main feature works
- [ ] License status displays correctly

### Cron Test

```bash
# Check if cron is scheduled
wp cron event list

# Should show your cron hook:
# mf_[abbreviation]_daily_license_check

# Manually trigger
wp cron event run mf_[abbreviation]_daily_license_check

# Check logs
tail -f wp-content/debug.log
```

---

## 📝 Quick Reference

### Required Unique Names

| Element | Pattern | Example |
|---------|---------|---------|
| License Handler Class | `MF_{ABBR}_License_Handler` | `MF_LP_TG_License_Handler` |
| Cron Class | `MF_{ABBR}_Cron` | `MF_LP_TG_Cron` |
| Cron Hook | `mf_{abbr}_daily_license_check` | `mf_lp_tg_daily_license_check` |
| License Tab Function | `{prefix}_render_license_tab` | `mf_lp_telegram_render_license_tab` |
| License Tab Slug | `{plugin-slug}` | `telegram-notifier` |
| Option Key | `{prefix}_license` | `lp_telegram_notifier_license` |

### Required Configuration

```php
// In main plugin file
define( 'PLUGIN_PRODUCT_ID', 12345 );  // From mamflow.com

// In load_license_system()
$this->license_handler = new MF_XX_License_Handler(
    array(
        'product_id'   => PLUGIN_PRODUCT_ID,
        'product_name' => 'Display Name',
        'api_url'      => 'https://mamflow.com/wp-json/mamflow/v1',
        'option_key'   => 'plugin_license',
    )
);

// In register_license_tab()
$tabs['tab-slug'] = array(
    'title'    => 'Tab Display Name',
    'callback' => 'prefix_render_license_tab',
    'priority' => 20,
);
```

---

## 🐛 Common Issues & Solutions

### Issue 1: "Cannot declare class Mamflow_License_Handler"

**Cause**: Class name conflict with another plugin
**Solution**: Change class name to unique prefix (e.g., `MF_LP_XX_License_Handler`)

### Issue 2: License tab not showing

**Causes**:
- `mamflow_register_license_menu()` not called
- Tab slug already used by another plugin
- Filter priority too low

**Solutions**:
- Verify `add_license_menu()` is hooked at priority 100
- Use unique tab slug
- Check if other Mamflow plugins are active

### Issue 3: Admin notice shows on license page

**Cause**: Missing page check in `license_notice()`
**Solution**:
```php
if ( isset( $_GET['page'] ) && 'mamflow-license' === $_GET['page'] ) {
    return;
}
```

### Issue 4: Feature gating not working

**Causes**:
- License check not at beginning of method
- Using wrong instance getter
- License handler not initialized

**Solutions**:
- Move license check to first line of method
- Use `Your_Plugin::instance()->get_license_handler()`
- Verify `load_license_system()` is called in constructor

---

## 📦 Files to Copy From Reference

**Reference Plugin**: `lp-telegram-notifier` (Product ID: 47313)

**Copy These**:
1. `includes/license/shared-license-page.php` → No changes needed
2. `includes/license/class-license-handler.php` → Change class name only
3. `includes/license/admin-license-page.php` → Customize function name & strings
4. `includes/license/cron-scheduler.php` → Change class name & hook name

**DO NOT Copy**:
- Main plugin file (structure varies per plugin)
- Feature-specific files

---

## 🎯 Final Checklist Before Deploy

- [ ] Product exists on mamflow.com with correct ID
- [ ] All class names are unique (verified with grep)
- [ ] All hook names are unique
- [ ] PHP syntax check passed on all files
- [ ] Plugin activates without errors
- [ ] License page tab appears
- [ ] Admin notice appears and disappears correctly
- [ ] Feature gating blocks without license
- [ ] `is_feature_enabled()` has Layer 1 (local expiry check)
- [ ] `is_feature_enabled()` has Layer 2 (72h fallback API re-check)
- [ ] `check_license_status()` has Layer 3 (connection error + local expiry)
- [ ] `check_license_status()` syncs `expires_at` from API response
- [ ] Cron is scheduled on activation
- [ ] Cron is cleared on deactivation
- [ ] No conflicts with other Mamflow plugins
- [ ] README updated with license requirements
- [ ] Text domain consistent throughout

---

## 📞 Support

**For License System Issues**:
- Reference implementation: `lp-telegram-notifier`
- Reference plan: `license_integration_plan.md`
- API documentation: Contact mamflow.com development team

**For Multi-Plugin Conflicts**:
- Check class names globally: `grep -r "class.*License_Handler"`
- Verify hook names: `wp cron event list`
- Test with other plugins active

---

**Last Updated**: 2026-03-05 by AI Assistant
**Tested On**: LP-Telegram-Notifier v1.0.0, MF-Quiz-Importer-For-LearnPress v1.0.0
**Status**: ✅ Production Ready
