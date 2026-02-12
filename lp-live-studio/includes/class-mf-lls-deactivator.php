<?php
/**
 * Deactivation handler
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Deactivator
 */
class MF_LLS_Deactivator
{
    /**
     * Run on plugin deactivation
     */
    public static function deactivate()
    {
        self::clear_cron_jobs();
    }

    /**
     * Clear all scheduled cron jobs
     */
    private static function clear_cron_jobs()
    {
        // Clear status update cron
        $timestamp = wp_next_scheduled('mf_lls_cron_update_status');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'mf_lls_cron_update_status');
        }

        // Clear attendance sync cron
        $timestamp = wp_next_scheduled('mf_lls_cron_sync_attendance');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'mf_lls_cron_sync_attendance');
        }

        // Clear all reminder crons (dynamic)
        wp_clear_scheduled_hook('mf_lls_cron_reminder_1h');
        wp_clear_scheduled_hook('mf_lls_cron_reminder_15m');

        // Clear license validation cron
        if (class_exists('MF_LLS_License_Cron')) {
            MF_LLS_License_Cron::clear_license_check();
        }
    }
}
