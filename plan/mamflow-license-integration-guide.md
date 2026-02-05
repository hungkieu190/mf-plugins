# Mamflow License System Integration Guide

> **Purpose**: Complete step-by-step guide for integrating Mamflow License System into any LearnPress plugin.
> **Last Updated**: 2026-02-05
> **Tested On**: LP-Telegram-Notifier (Product ID: 47313)

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
    
    // Copy all methods from lp-telegram-notifier/includes/license/class-license-handler.php
    // Methods: activate_license, deactivate_license, check_license_status, 
    //          is_feature_enabled, get_license_data, get_site_domain,
    //          get_days_until_expiration, is_expired
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

**Last Updated**: 2026-02-05 by AI Assistant
**Tested On**: LP-Telegram-Notifier v1.0.0
**Status**: ✅ Production Ready
