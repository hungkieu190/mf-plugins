<?php
/**
 * WP Cron Scheduler for LearnPress Live Studio
 * 
 * Handles scheduling of daily license validation checks.
 * 
 * @package LearnPress_Live_Studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_License_Cron
 * 
 * IMPORTANT: Class name is unique to avoid conflicts with other Mamflow plugins.
 */
class MF_LLS_License_Cron
{

    /**
     * Cron hook name
     *
     * @var string
     */
    const CRON_HOOK = 'mf_lls_daily_license_check';

    /**
     * Schedule daily license check
     */
    public static function schedule_license_check()
    {
        // Check if already scheduled.
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            // Schedule daily at 3 AM.
            wp_schedule_event(strtotime('tomorrow 3:00 AM'), 'daily', self::CRON_HOOK);
        }

        // Register cron callback.
        add_action(self::CRON_HOOK, array(__CLASS__, 'run_license_check'));
    }

    /**
     * Clear scheduled license check
     */
    public static function clear_license_check()
    {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);

        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }

        // Clear all instances (in case of multiple schedules).
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /**
     * Run the license check (cron callback)
     */
    public static function run_license_check()
    {
        // Get license handler instance.
        $plugin = MF_LLS_Addon::instance();
        $license_handler = $plugin->get_license_handler();

        // Check license status.
        $is_valid = $license_handler->check_license_status();

        // Optional: Log result for debugging.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('LearnPress Live Studio: Daily license check - ' . ($is_valid ? 'VALID' : 'INVALID'));
        }

        // Optional: Send admin notification if license becomes invalid.
        if (!$is_valid) {
            self::send_admin_notification();
        }
    }

    /**
     * Send email notification to admin when license becomes invalid
     */
    private static function send_admin_notification()
    {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');

        /* translators: %s: Site name */
        $subject = sprintf(__('[%s] License Validation Failed', 'lp-live-studio'), $site_name);

        $message = sprintf(
            /* translators: %s: License management URL */
            __(
                "Your license for LearnPress Live Studio has failed validation.\n\n" .
                "This could mean:\n" .
                "- Your license has expired\n" .
                "- Your license was refunded\n" .
                "- Your license was banned\n\n" .
                "Please check your license status at:\n%s\n\n" .
                "If you believe this is an error, please contact support at mamflow.com",
                'lp-live-studio'
            ),
            admin_url('admin.php?page=mamflow-license&tab=live-studio')
        );

        wp_mail($admin_email, $subject, $message);
    }
}

// Initialize cron on plugin load.
add_action(
    'init',
    function () {
        // Register the cron hook.
        add_action(MF_LLS_License_Cron::CRON_HOOK, array(MF_LLS_License_Cron::class, 'run_license_check'));
    }
);
