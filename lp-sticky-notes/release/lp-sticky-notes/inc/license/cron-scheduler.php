<?php
/**
 * WP Cron Scheduler
 * 
 * Handles scheduling of daily license validation checks.
 * 
 * @package LP_Sticky_Notes
 */

if (!defined('ABSPATH')) {
    exit;
}

class LP_Sticky_Notes_Cron {
    
    const CRON_HOOK = 'lp_sticky_notes_daily_license_check';
    
    /**
     * Schedule daily license check
     */
    public static function schedule_license_check() {
        // Check if already scheduled
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            // Schedule daily at 3 AM
            wp_schedule_event(strtotime('tomorrow 3:00 AM'), 'daily', self::CRON_HOOK);
        }
        
        // Register cron callback
        add_action(self::CRON_HOOK, [__CLASS__, 'run_license_check']);
    }
    
    /**
     * Clear scheduled license check
     */
    public static function clear_license_check() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
        
        // Clear all instances (in case of multiple schedules)
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }
    
    /**
     * Run the license check (cron callback)
     */
    public static function run_license_check() {
        // Get license handler instance
        $plugin = LP_Sticky_Notes::instance();
        $license_handler = $plugin->get_license_handler();
        
        // Check license status
        $is_valid = $license_handler->check_license_status();
        
        // Optional: Log result for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('LP Sticky Notes: Daily license check - ' . ($is_valid ? 'VALID' : 'INVALID'));
        }
        
        // Optional: Send admin notification if license becomes invalid
        if (!$is_valid) {
            self::send_admin_notification();
        }
    }
    
    /**
     * Send email notification to admin when license becomes invalid
     */
    private static function send_admin_notification() {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf('[%s] License Validation Failed', $site_name);
        
        $message = sprintf(
            "Your license for LearnPress Sticky Notes has failed validation.\n\n" .
            "This could mean:\n" .
            "- Your license has expired\n" .
            "- Your license was refunded\n" .
            "- Your license was banned\n\n" .
            "Please check your license status at:\n%s\n\n" .
            "If you believe this is an error, please contact support at mamflow.com",
            admin_url('admin.php?page=mamflow-license?tab=sticky-notes')
        );
        
        wp_mail($admin_email, $subject, $message);
    }
}

// Initialize cron on plugin load
add_action('init', function() {
    // Register the cron hook
    add_action(LP_Sticky_Notes_Cron::CRON_HOOK, [LP_Sticky_Notes_Cron::class, 'run_license_check']);
});
