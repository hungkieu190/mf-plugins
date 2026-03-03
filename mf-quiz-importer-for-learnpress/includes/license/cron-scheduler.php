<?php
/**
 * WP Cron Scheduler for Quiz Importer For LearnPress
 *
 * Handles scheduling of daily license validation checks.
 *
 * @package MF_Quiz_Importer_For_LearnPress
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LP_QI_Cron
 *
 * IMPORTANT: Class name is unique to avoid conflicts with other Mamflow plugins.
 */
class MF_LP_QI_Cron
{

    /**
     * Cron hook name
     *
     * @var string
     */
    const CRON_HOOK = 'mf_lp_qi_daily_license_check';

    /**
     * Schedule daily license check
     */
    public static function schedule_license_check()
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(strtotime('tomorrow 3:00 AM'), 'daily', self::CRON_HOOK);
        }

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

        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /**
     * Run the license check (cron callback)
     */
    public static function run_license_check()
    {
        $plugin = MF_Quiz_Importer_For_LearnPress::instance();
        $license_handler = $plugin->get_license_handler();
        $is_valid = $license_handler->check_license_status();

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MF Quiz Importer: Daily license check - ' . ($is_valid ? 'VALID' : 'INVALID'));
        }

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
        $subject = sprintf(__('[%s] License Validation Failed', 'mf-quiz-importer-lp'), $site_name);

        $message = sprintf(
            /* translators: %s: License management URL */
            __(
                "Your license for Quiz Importer For LearnPress has failed validation.\n\n" .
                "This could mean:\n" .
                "- Your license has expired\n" .
                "- Your license was refunded\n" .
                "- Your license was banned\n\n" .
                "Please check your license status at:\n%s\n\n" .
                'If you believe this is an error, please contact support at mamflow.com',
                'mf-quiz-importer-lp'
            ),
            admin_url('admin.php?page=mamflow-license&tab=quiz-importer')
        );

        wp_mail($admin_email, $subject, $message);
    }
}

// Initialize cron on plugin load.
add_action(
    'init',
    function () {
        add_action(MF_LP_QI_Cron::CRON_HOOK, array(MF_LP_QI_Cron::class, 'run_license_check'));
    }
);
