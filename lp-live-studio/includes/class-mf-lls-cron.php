<?php
/**
 * Cron Jobs Handler
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Cron
 *
 * Handle all cron jobs
 */
class MF_LLS_Cron
{
    /**
     * @var MF_LLS_Cron Singleton instance
     */
    protected static $_instance = null;

    /**
     * Get singleton instance
     *
     * @return MF_LLS_Cron
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register cron handlers
        add_action('mf_lls_cron_update_status', array($this, 'update_session_status'));
        add_action('mf_lls_cron_sync_attendance', array($this, 'sync_attendance'));
        add_action('mf_lls_cron_reminder_1h', array($this, 'send_reminder_1h'), 10, 1);
        add_action('mf_lls_cron_reminder_15m', array($this, 'send_reminder_15m'), 10, 1);
    }

    /**
     * Update session status for all live lessons
     */
    public function update_session_status()
    {
        global $wpdb;

        // Get all lessons with live sessions
        $lessons = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID, pm1.meta_value as start_time, pm2.meta_value as duration
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s AND pm.meta_value = %s
				INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = %s
				INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = %s
				WHERE p.post_type = %s AND p.post_status = %s",
                '_mf_lls_is_live',
                '1',
                '_mf_lls_start_time',
                '_mf_lls_duration',
                'lp_lesson',
                'publish'
            )
        );

        foreach ($lessons as $lesson) {
            $status = $this->calculate_status($lesson->start_time, $lesson->duration);
            update_post_meta($lesson->ID, '_mf_lls_status', $status);
        }
    }

    /**
     * Calculate session status
     *
     * @param string $start_time ISO 8601 datetime
     * @param int    $duration   Duration in minutes
     * @return string Status: upcoming|live|ended
     */
    private function calculate_status($start_time, $duration)
    {
        $now = current_time('timestamp');
        $start = strtotime($start_time);
        $end = $start + ($duration * 60);

        if ($now < $start) {
            return 'upcoming';
        } elseif ($now >= $start && $now <= $end) {
            return 'live';
        } else {
            return 'ended';
        }
    }

    /**
     * Sync attendance from platform APIs
     */
    public function sync_attendance()
    {
        // This will be implemented in Module 5
        // For now, just a placeholder
        do_action('mf_lls_sync_attendance');
    }

    /**
     * Send reminder 1 hour before session
     *
     * @param int $lesson_id Lesson ID
     */
    public function send_reminder_1h($lesson_id)
    {
        if ('1' !== get_option('mf_lls_reminder_1h_enabled', '1')) {
            return;
        }

        // This will be implemented in Module 7
        do_action('mf_lls_send_reminder', $lesson_id, '1h');
    }

    /**
     * Send reminder 15 minutes before session
     *
     * @param int $lesson_id Lesson ID
     */
    public function send_reminder_15m($lesson_id)
    {
        if ('1' !== get_option('mf_lls_reminder_15m_enabled', '1')) {
            return;
        }

        // This will be implemented in Module 7
        do_action('mf_lls_send_reminder', $lesson_id, '15m');
    }
}
