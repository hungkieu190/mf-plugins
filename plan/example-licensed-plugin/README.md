# Example Licensed Plugin

Demo plugin demonstrating client-side integration with Mamflow License System.

## Overview

This example plugin shows how to integrate license validation into your commercial plugins. It includes:

- **License Handler Class**: Reusable class for API communication
- **Admin Settings Page**: UI for license activation/deactivation
- **Daily Cron Validation**: Automatic license checking
- **Feature Gating**: Lock premium features behind license validation

## Installation

**For Testing:**
1. Copy the entire `example-licensed-plugin` folder to `wp-content/plugins/`
2. Activate the plugin in WordPress admin
3. Go to Settings → Example Plugin License
4. Enter a valid license key from mamflow.com

**For Production:** 
This is a reference implementation. Copy the files into your own commercial plugin.

## Files

### Core Files

- `example-licensed-plugin.php` - Main plugin file
- `includes/class-license-handler.php` - Reusable license handler class
- `includes/admin-license-page.php` - Settings page UI
- `includes/cron-scheduler.php` - Daily validation scheduler

## Usage in Your Plugin

### Step 1: Copy Files

Copy these files into your plugin:
- `includes/class-license-handler.php`
- `includes/admin-license-page.php` (customize as needed)
- `includes/cron-scheduler.php`

### Step 2: Initialize License Handler

```php
require_once plugin_dir_path(__FILE__) . 'includes/class-license-handler.php';

$license_handler = new Mamflow_License_Handler([
    'product_id' => 123, // Your WooCommerce product ID on mamflow.com
    'product_name' => 'Your Plugin Name',
    'api_url' => 'https://mamflow.com/wp-json/mamflow/v1',
    'option_key' => 'your_plugin_license' // Unique key
]);
```

### Step 3: Gate Premium Features

```php
if (!$license_handler->is_feature_enabled()) {
    // Show upgrade notice or return early
    return;
}

// Premium feature code here
do_premium_thing();
```

### Step 4: Add Settings Page

```php
add_action('admin_menu', function() {
    add_options_page(
        'License Settings',
        'Your Plugin License',
        'manage_options',
        'your-plugin-license',
        'your_plugin_render_license_page' // Use admin-license-page.php as template
    );
});
```

### Step 5: Schedule Cron

```php
// On plugin activation
register_activation_hook(__FILE__, function() {
    Your_Plugin_Cron::schedule_license_check();
});

// On plugin deactivation
register_deactivation_hook(__FILE__, function() {
    Your_Plugin_Cron::clear_license_check();
});
```

## API Methods

### License Handler Methods

**Activate license:**
```php
$result = $license_handler->activate_license($license_key);
// Returns: ['success' => true/false, 'message' => '...']
```

**Deactivate license:**
```php
$result = $license_handler->deactivate_license();
```

**Check status (manual):**
```php
$is_valid = $license_handler->check_license_status();
// Returns: true/false
```

**Check if features enabled (use this for gating):**
```php
if ($license_handler->is_feature_enabled()) {
    // Unlock feature
}
```

**Get license data:**
```php
$data = $license_handler->get_license_data();
// Returns array with: license_key, status, domain, expires_at, last_check
```

**Check expiration:**
```php
$days = $license_handler->get_days_until_expiration();
// Returns: int (days) or null (lifetime)

$is_expired = $license_handler->is_expired();
// Returns: bool
```

## Feature Gating Examples

### Example 1: Gate entire admin page

```php
function your_premium_feature_page() {
    global $license_handler;
    
    if (!$license_handler->is_feature_enabled()) {
        ?>
        <div class="wrap">
            <h1>Premium Feature</h1>
            <div class="notice notice-warning">
                <p>This feature requires an active license. 
                <a href="<?php echo admin_url('options-general.php?page=your-plugin-license'); ?>">
                    Activate your license
                </a></p>
            </div>
        </div>
        <?php
        return;
    }
    
    // Premium feature UI here
}
```

### Example 2: Gate shortcode

```php
add_shortcode('your_premium_shortcode', function($atts) {
    global $license_handler;
    
    if (!$license_handler->is_feature_enabled()) {
        return '<p>This feature requires a license. Purchase at mamflow.com</p>';
    }
    
    return '<div>Premium content here</div>';
});
```

### Example 3: Conditional hook

```php
if ($license_handler->is_feature_enabled()) {
    add_action('wp_footer', 'your_premium_footer_function');
}
```

## Testing

### Test Activation Flow

1. Get test license key from mamflow.com (complete test order)
2. Go to Settings → Example Plugin License
3. Enter license key and click "Activate"
4. Verify activation success message
5. Check that premium badge appears on frontend

### Test Validation

1. Activate license
2. Wait 24 hours (or trigger cron manually: `wp cron event run example_licensed_plugin_daily_license_check`)
3. Verify license is still valid

### Test Refund/Ban

1. Activate license
2. On mamflow.com, refund the test order
3. Trigger cron check
4. Verify license becomes invalid and features are locked

## Troubleshooting

**License won't activate:**
- Check API URL is correct (https://mamflow.com/wp-json/mamflow/v1)
- Verify product ID matches WooCommerce product on mamflow.com
- Check firewall isn't blocking wp_remote_post() calls

**Features locked after activation:**
- Clear WordPress object cache
- Run: `delete_option('your_plugin_license')` and re-activate
- Check cron is running: `wp cron event list`

**Cron not running:**
- Verify cron is scheduled: `wp cron event list`
- Manually trigger: `wp cron event run example_licensed_plugin_daily_license_check`
- Check server cron is working (some hosts disable WP cron)

## Support

For issues with the license system:
- Email: support@mamflow.com
- Documentation: https://mamflow.com/docs/licensing

## License

This example plugin is provided as reference implementation for Mamflow License System integrations.
