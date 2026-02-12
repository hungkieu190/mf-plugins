<?php
/**
 * Live Lesson Type Class
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Live_Lesson
 *
 * Handles Live Session lesson type registration and metaboxes
 */
class MF_LLS_Live_Lesson
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register metabox fields
        add_filter('lp/metabox/lesson/lists', array($this, 'add_live_lesson_fields'));

        // Save logic
        add_action('learnpress_save_lp_lesson_metabox', array($this, 'save_live_lesson_meta'), 10, 1);

        // Auto create meeting
        add_action('save_post_lp_lesson', array($this, 'trigger_meeting_creation'), 20, 2);
    }

    /**
     * Add live session fields to lesson metabox
     *
     * @param array $fields
     * @return array
     */
    public function add_live_lesson_fields($fields)
    {
        $live_fields = array(
            '_mf_lls_is_live' => array(
                'title' => __('Is Live Session?', 'lp-live-studio'),
                'type' => 'checkbox',
                'default' => '0',
                'desc' => __('Check this if the lesson is a live session.', 'lp-live-studio'),
            ),
            '_mf_lls_platform' => array(
                'title' => __('Platform', 'lp-live-studio'),
                'type' => 'select',
                'options' => array(
                    'zoom' => __('Zoom', 'lp-live-studio'),
                    'google_meet' => __('Google Meet', 'lp-live-studio'),
                    'agora' => __('Agora', 'lp-live-studio'),
                ),
                'default' => 'zoom',
                'desc' => __('Select the video meeting platform.', 'lp-live-studio'),
                'condition' => array('_mf_lls_is_live', '=', '1'),
            ),
            '_mf_lls_start_time' => array(
                'title' => __('Start Time', 'lp-live-studio'),
                'type' => 'datetime',
                'default' => '',
                'desc' => __('Set the date and time when the session starts.', 'lp-live-studio'),
                'condition' => array('_mf_lls_is_live', '=', '1'),
            ),
            '_mf_lls_duration' => array(
                'title' => __('Duration (minutes)', 'lp-live-studio'),
                'type' => 'number',
                'default' => '60',
                'desc' => __('Expected duration in minutes.', 'lp-live-studio'),
                'condition' => array('_mf_lls_is_live', '=', '1'),
            ),
            '_mf_lls_participant_limit' => array(
                'title' => __('Participant Limit', 'lp-live-studio'),
                'type' => 'number',
                'default' => '100',
                'desc' => __('Maximum number of participants allowed.', 'lp-live-studio'),
                'condition' => array('_mf_lls_is_live', '=', '1'),
            ),
        );

        return array_merge($fields, $live_fields);
    }

    /**
     * Save live session meta
     *
     * @param int $post_id
     */
    public function save_live_lesson_meta($post_id)
    {
        if (isset($_POST['_mf_lls_is_live'])) {
            update_post_meta($post_id, '_mf_lls_is_live', sanitize_text_field($_POST['_mf_lls_is_live']));
        } else {
            update_post_meta($post_id, '_mf_lls_is_live', '0');
        }

        if (isset($_POST['_mf_lls_platform'])) {
            update_post_meta($post_id, '_mf_lls_platform', sanitize_text_field($_POST['_mf_lls_platform']));
        }

        if (isset($_POST['_mf_lls_start_time'])) {
            update_post_meta($post_id, '_mf_lls_start_time', sanitize_text_field($_POST['_mf_lls_start_time']));
        }

        if (isset($_POST['_mf_lls_duration'])) {
            update_post_meta($post_id, '_mf_lls_duration', absint($_POST['_mf_lls_duration']));
        }

        if (isset($_POST['_mf_lls_participant_limit'])) {
            update_post_meta($post_id, '_mf_lls_participant_limit', absint($_POST['_mf_lls_participant_limit']));
        }

        // Set default status if is live
        if ('1' === get_post_meta($post_id, '_mf_lls_is_live', true)) {
            if (!get_post_meta($post_id, '_mf_lls_status', true)) {
                update_post_meta($post_id, '_mf_lls_status', 'upcoming');
            }
        }
    }

    /**
     * Get session status
     *
     * @param int $lesson_id
     * @return string upcoming|live|ended
     */
    public static function get_session_status($lesson_id)
    {
        $is_live = get_post_meta($lesson_id, '_mf_lls_is_live', true);
        $start_time = get_post_meta($lesson_id, '_mf_lls_start_time', true);
        $duration = get_post_meta($lesson_id, '_mf_lls_duration', true);

        if ('1' !== $is_live || !$start_time) {
            return '';
        }

        $now = time();
        $start_timestamp = strtotime($start_time);
        $end_timestamp = $start_timestamp + (absint($duration) * 60);

        if ($now < $start_timestamp) {
            return 'upcoming';
        } elseif ($now >= $start_timestamp && $now <= $end_timestamp) {
            return 'live';
        } else {
            return 'ended';
        }
    }

    /**
     * Update session status meta
     *
     * @param int $lesson_id
     */
    /**
     * Trigger meeting creation on lesson save
     *
     * @param int     $post_id
     * @param WP_Post $post
     */
    public function trigger_meeting_creation($post_id, $post)
    {
        // Only trigger on publish
        if ('publish' !== $post->post_status) {
            return;
        }

        // Check if is live lesson
        if ('1' !== get_post_meta($post_id, '_mf_lls_is_live', true)) {
            return;
        }

        // Check if meeting already created
        if (get_post_meta($post_id, '_mf_lls_meeting_id', true)) {
            return;
        }

        // License Gate Check
        $license_handler = MF_LLS_Addon::instance()->get_license_handler();
        if (!$license_handler->is_feature_enabled()) {
            error_log('[Live Studio]: Meeting creation blocked - License not active for Lesson ID: ' . $post_id);
            return;
        }

        // Get platform
        $platform_slug = get_post_meta($post_id, '_mf_lls_platform', true);
        $platform = MF_LLS_Addon::instance()->get_platform($platform_slug);

        if (!$platform || !$platform->is_configured()) {
            error_log('[Live Studio]: Meeting creation failed - Platform not configured or not found: ' . $platform_slug);
            return;
        }

        // Prepare meeting args
        $args = array(
            'title' => $post->post_title,
            'start_time' => get_post_meta($post_id, '_mf_lls_start_time', true),
            'duration' => get_post_meta($post_id, '_mf_lls_duration', true),
            'participant_limit' => get_post_meta($post_id, '_mf_lls_participant_limit', true),
        );

        // Create room
        $result = $platform->create_room($args);

        if (is_wp_error($result)) {
            error_log('[Live Studio]: Meeting creation API error: ' . $result->get_error_message());
            return;
        }

        // Save meeting data
        update_post_meta($post_id, '_mf_lls_meeting_id', $result['meeting_id']);
        update_post_meta($post_id, '_mf_lls_join_url', $result['join_url']);
        update_post_meta($post_id, '_mf_lls_host_url', $result['host_url']);
        update_post_meta($post_id, '_mf_lls_platform_data', $result['platform_data']);
    }
}
