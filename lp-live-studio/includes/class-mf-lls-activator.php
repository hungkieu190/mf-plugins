<?php
/**
 * Activation handler
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Activator
 */
class MF_LLS_Activator
{
    /**
     * Run on plugin activation
     */
    public static function activate()
    {
        self::create_tables();
        self::set_default_options();
        self::schedule_cron_jobs();

        // Save DB version
        update_option('mf_lls_db_version', '1.0.0');
    }

    /**
     * Create database tables
     */
    private static function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table: Attendance
        $table_attendance = $wpdb->prefix . 'mf_lls_attendance';
        $sql_attendance = "CREATE TABLE IF NOT EXISTS $table_attendance (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			lesson_id bigint(20) UNSIGNED NOT NULL,
			course_id bigint(20) UNSIGNED NOT NULL,
			join_time datetime NOT NULL,
			leave_time datetime DEFAULT NULL,
			duration int(10) UNSIGNED DEFAULT 0,
			platform varchar(50) NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY user_lesson (user_id, lesson_id),
			KEY lesson_id (lesson_id),
			KEY course_id (course_id)
		) $charset_collate;";

        // Table: Ratings
        $table_ratings = $wpdb->prefix . 'mf_lls_ratings';
        $sql_ratings = "CREATE TABLE IF NOT EXISTS $table_ratings (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			lesson_id bigint(20) UNSIGNED NOT NULL,
			course_id bigint(20) UNSIGNED NOT NULL,
			tutor_id bigint(20) UNSIGNED NOT NULL,
			rating_tutor tinyint(1) UNSIGNED NOT NULL,
			rating_content tinyint(1) UNSIGNED NOT NULL,
			comment text DEFAULT NULL,
			created_at datetime NOT NULL,
			expires_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY user_lesson (user_id, lesson_id),
			KEY tutor_id (tutor_id),
			KEY lesson_id (lesson_id)
		) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_attendance);
        dbDelta($sql_ratings);
    }

    /**
     * Set default options
     */
    private static function set_default_options()
    {
        $defaults = array(
            'mf_lls_default_platform' => 'zoom',
            'mf_lls_reminder_1h_enabled' => '1',
            'mf_lls_reminder_15m_enabled' => '1',
            'mf_lls_rating_enabled' => '1',
            'mf_lls_rating_expire_days' => '7',
        );

        foreach ($defaults as $key => $value) {
            if (false === get_option($key)) {
                add_option($key, $value);
            }
        }
    }

    /**
     * Schedule cron jobs
     */
    private static function schedule_cron_jobs()
    {
        // Status update cron (every 5 minutes)
        if (!wp_next_scheduled('mf_lls_cron_update_status')) {
            wp_schedule_event(time(), 'mf_lls_5min', 'mf_lls_cron_update_status');
        }

        // Attendance sync cron (daily)
        if (!wp_next_scheduled('mf_lls_cron_sync_attendance')) {
            wp_schedule_event(time(), 'daily', 'mf_lls_cron_sync_attendance');
        }

        // License validation cron (daily) - requires license files loaded
        if (class_exists('MF_LLS_License_Cron')) {
            MF_LLS_License_Cron::schedule_license_check();
        }
    }
}
