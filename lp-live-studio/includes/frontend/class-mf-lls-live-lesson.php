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
 * Handles Live Session lesson type registration, metabox fields,
 * save logic, session status management, and auto meeting creation.
 */
class MF_LLS_Live_Lesson
{

    /**
     * Allowed platform slugs (whitelist)
     *
     * @var array
     */
    const ALLOWED_PLATFORMS = array('zoom', 'google_meet', 'agora');

    /**
     * Transient key prefix for admin notices
     *
     * @var string
     */
    const NOTICE_TRANSIENT_PREFIX = 'mf_lls_admin_notice_';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register metabox fields into LearnPress lesson metabox
        add_filter('lp/metabox/lesson/lists', array($this, 'add_live_lesson_fields'));

        // Save live meta when LP saves the lesson metabox
        add_action('learnpress_save_lp_lesson_metabox', array($this, 'save_live_lesson_meta'), 10, 1);

        // Auto create meeting on publish (priority 20 — after meta is saved by LP)
        add_action('save_post_lp_lesson', array($this, 'trigger_meeting_creation'), 20, 2);

        // Display admin notices from transient
        add_action('admin_notices', array($this, 'display_admin_notices'));
    }

    /**
     * Add live session fields to LearnPress lesson metabox
     *
     * @param array $fields Existing LP metabox fields.
     * @return array Modified fields array.
     */
    public function add_live_lesson_fields($fields)
    {
        $live_fields = array(
            '_mf_lls_is_live' => array(
                'title' => esc_html__('Is Live Session?', 'lp-live-studio'),
                'type' => 'checkbox',
                'default' => '0',
                'desc' => esc_html__('Enable this to mark the lesson as a live streaming session.', 'lp-live-studio'),
            ),
            '_mf_lls_platform' => array(
                'title' => esc_html__('Platform', 'lp-live-studio'),
                'type' => 'select',
                'options' => array(
                    'zoom' => esc_html__('Zoom', 'lp-live-studio'),
                    'google_meet' => esc_html__('Google Meet', 'lp-live-studio'),
                    'agora' => esc_html__('Agora', 'lp-live-studio'),
                ),
                'default' => get_option(MF_LLS_OPT_DEFAULT_PLATFORM, 'zoom'),
                'desc' => esc_html__('Select the video meeting platform for this session.', 'lp-live-studio'),
                'condition' => array('_mf_lls_is_live', '=', '1'),
            ),
            '_mf_lls_start_time' => array(
                'title' => esc_html__('Start Time', 'lp-live-studio'),
                'type' => 'datetime',
                'default' => '',
                'desc' => esc_html__('Date and time when the live session starts (e.g. 2026-03-01 09:00).', 'lp-live-studio'),
                'condition' => array('_mf_lls_is_live', '=', '1'),
            ),
            '_mf_lls_duration' => array(
                'title' => esc_html__('Duration (minutes)', 'lp-live-studio'),
                'type' => 'number',
                'default' => '60',
                'desc' => esc_html__('Expected session duration in minutes. Minimum 1, maximum 1440 (24h).', 'lp-live-studio'),
                'condition' => array('_mf_lls_is_live', '=', '1'),
            ),
            '_mf_lls_participant_limit' => array(
                'title' => esc_html__('Participant Limit', 'lp-live-studio'),
                'type' => 'number',
                'default' => '100',
                'desc' => esc_html__('Maximum number of participants. Set 0 for unlimited.', 'lp-live-studio'),
                'condition' => array('_mf_lls_is_live', '=', '1'),
            ),
        );

        return array_merge($fields, $live_fields);
    }

    /**
     * Save live session meta from lesson metabox
     *
     * @param int $post_id Lesson post ID.
     */
    public function save_live_lesson_meta($post_id)
    {
        // --- is_live (checkbox) ---
        $is_live = isset($_POST['_mf_lls_is_live']) && '1' === $_POST['_mf_lls_is_live'] ? '1' : '0';
        update_post_meta($post_id, '_mf_lls_is_live', $is_live);

        // Only process other fields if this is a live lesson
        if ('1' !== $is_live) {
            return;
        }

        // --- platform (whitelist) ---
        $platform = isset($_POST['_mf_lls_platform']) ? sanitize_text_field(wp_unslash($_POST['_mf_lls_platform'])) : 'zoom';
        if (!in_array($platform, self::ALLOWED_PLATFORMS, true)) {
            $platform = get_option(MF_LLS_OPT_DEFAULT_PLATFORM, 'zoom');
        }
        update_post_meta($post_id, '_mf_lls_platform', $platform);

        // --- start_time (validate datetime format) ---
        if (isset($_POST['_mf_lls_start_time'])) {
            $raw_start = sanitize_text_field(wp_unslash($_POST['_mf_lls_start_time']));
            $validated = $this->validate_datetime($raw_start);
            update_post_meta($post_id, '_mf_lls_start_time', $validated);
        }

        // --- duration (positive int, max 1440 minutes = 24h) ---
        if (isset($_POST['_mf_lls_duration'])) {
            $duration = absint($_POST['_mf_lls_duration']);
            $duration = max(1, min(1440, $duration));
            update_post_meta($post_id, '_mf_lls_duration', $duration);
        }

        // --- participant_limit (non-negative int) ---
        if (isset($_POST['_mf_lls_participant_limit'])) {
            $limit = absint($_POST['_mf_lls_participant_limit']);
            update_post_meta($post_id, '_mf_lls_participant_limit', $limit);
        }

        // --- auto-set status = 'upcoming' only if not already set ---
        if (!get_post_meta($post_id, '_mf_lls_status', true)) {
            update_post_meta($post_id, '_mf_lls_status', 'upcoming');
        }
    }

    /**
     * Validate and normalise a datetime string to 'Y-m-d H:i:s'.
     * Returns empty string if invalid.
     *
     * @param string $raw Raw datetime string from input.
     * @return string Normalised datetime or empty string.
     */
    private function validate_datetime($raw)
    {
        if (empty($raw)) {
            return '';
        }

        // Try multiple common formats coming from datetime-local input
        $formats = array(
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
        );

        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $raw);
            if ($dt && $dt->format($format) !== false) {
                return $dt->format('Y-m-d H:i:s');
            }
        }

        return '';
    }

    /**
     * Get real-time session status for a lesson.
     * Uses current server time — does NOT rely on cached _mf_lls_status meta.
     *
     * @param int $lesson_id Lesson post ID.
     * @return string 'upcoming'|'live'|'ended'|'' (empty = not a live lesson or missing data)
     */
    public static function get_session_status($lesson_id)
    {
        if ('1' !== get_post_meta($lesson_id, '_mf_lls_is_live', true)) {
            return '';
        }

        $start_time = get_post_meta($lesson_id, '_mf_lls_start_time', true);
        $duration = absint(get_post_meta($lesson_id, '_mf_lls_duration', true));

        if (empty($start_time)) {
            return '';
        }

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
     * Update session status post meta (called by cron via MF_LLS_Cron)
     *
     * @param int $lesson_id Lesson post ID.
     */
    public static function update_status_meta($lesson_id)
    {
        $status = self::get_session_status($lesson_id);
        if (!empty($status)) {
            update_post_meta($lesson_id, '_mf_lls_status', $status);
        }
    }

    /**
     * Trigger meeting creation when a live lesson is published.
     * Only creates if: is_live = 1, status = publish, meeting_id is empty.
     *
     * @param int     $post_id Lesson post ID.
     * @param WP_Post $post    Post object.
     */
    public function trigger_meeting_creation($post_id, $post)
    {
        // Skip auto-saves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Only trigger on published lessons
        if ('publish' !== $post->post_status) {
            return;
        }

        // Only for live lessons
        if ('1' !== get_post_meta($post_id, '_mf_lls_is_live', true)) {
            return;
        }

        // Skip if meeting already created
        if (get_post_meta($post_id, '_mf_lls_meeting_id', true)) {
            return;
        }

        // License gate
        $license_handler = MF_LLS_Addon::instance()->get_license_handler();
        if (!$license_handler->is_feature_enabled()) {
            $this->set_admin_notice(
                $post_id,
                'warning',
                esc_html__('Live Studio: Meeting was not created because your license is not active. Please activate your license.', 'lp-live-studio')
            );
            return;
        }

        // Get and validate platform
        $platform_slug = get_post_meta($post_id, '_mf_lls_platform', true);
        if (!in_array($platform_slug, self::ALLOWED_PLATFORMS, true)) {
            $this->set_admin_notice(
                $post_id,
                'error',
                esc_html__('Live Studio: Meeting was not created — invalid platform selected.', 'lp-live-studio')
            );
            return;
        }

        $platform = MF_LLS_Addon::instance()->get_platform($platform_slug);

        if (is_null($platform) || !$platform->is_configured()) {
            $this->set_admin_notice(
                $post_id,
                'warning',
                sprintf(
                    /* translators: %s: Platform name */
                    esc_html__('Live Studio: Meeting was not created — %s credentials are not configured. Please go to Live Studio Settings and enter your API keys.', 'lp-live-studio'),
                    esc_html(ucfirst(str_replace('_', ' ', $platform_slug)))
                )
            );
            return;
        }

        // Prepare meeting args
        $start_time = get_post_meta($post_id, '_mf_lls_start_time', true);
        if (empty($start_time)) {
            $this->set_admin_notice(
                $post_id,
                'error',
                esc_html__('Live Studio: Meeting was not created — Start Time is required for live sessions.', 'lp-live-studio')
            );
            return;
        }

        $args = array(
            'title' => $post->post_title,
            'start_time' => $start_time,
            'duration' => absint(get_post_meta($post_id, '_mf_lls_duration', true)),
            'participant_limit' => absint(get_post_meta($post_id, '_mf_lls_participant_limit', true)),
        );

        // Call platform API to create room
        $result = $platform->create_room($args);

        if (is_wp_error($result)) {
            $this->set_admin_notice(
                $post_id,
                'error',
                sprintf(
                    /* translators: %s: API error message */
                    esc_html__('Live Studio: Meeting creation failed — %s', 'lp-live-studio'),
                    $result->get_error_message()
                )
            );
            return;
        }

        // Persist meeting data
        update_post_meta($post_id, '_mf_lls_meeting_id', sanitize_text_field($result['meeting_id']));
        update_post_meta($post_id, '_mf_lls_join_url', esc_url_raw($result['join_url']));
        update_post_meta($post_id, '_mf_lls_host_url', esc_url_raw($result['host_url']));

        if (!empty($result['platform_data']) && is_array($result['platform_data'])) {
            update_post_meta($post_id, '_mf_lls_platform_data', $result['platform_data']);
        }

        // Set initial status
        update_post_meta($post_id, '_mf_lls_status', 'upcoming');

        $this->set_admin_notice(
            $post_id,
            'success',
            esc_html__('Live Studio: Meeting created successfully. Students can now join from the lesson page.', 'lp-live-studio')
        );
    }

    /**
     * Store an admin notice in a transient tied to the current admin user.
     *
     * @param int    $post_id  Lesson post ID (used as transient key suffix).
     * @param string $type     Notice type: 'success'|'warning'|'error'.
     * @param string $message  Already-escaped message string.
     */
    private function set_admin_notice($post_id, $type, $message)
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }

        $transient_key = self::NOTICE_TRANSIENT_PREFIX . $user_id;
        $notices = get_transient($transient_key);

        if (!is_array($notices)) {
            $notices = array();
        }

        $notices[] = array(
            'type' => $type,
            'message' => $message,
        );

        // Notices live for one page load only (60 seconds is sufficient for redirect)
        set_transient($transient_key, $notices, 60);
    }

    /**
     * Display admin notices stored in transient for current user.
     * Hooked to admin_notices.
     */
    public function display_admin_notices()
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }

        $transient_key = self::NOTICE_TRANSIENT_PREFIX . $user_id;
        $notices = get_transient($transient_key);

        if (!is_array($notices) || empty($notices)) {
            return;
        }

        // Delete immediately — show once only
        delete_transient($transient_key);

        foreach ($notices as $notice) {
            $type = in_array($notice['type'], array('success', 'warning', 'error', 'info'), true)
                ? $notice['type']
                : 'info';
            ?>
            <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
                <p><?php echo wp_kses_post($notice['message']); ?></p>
            </div>
            <?php
        }
    }
}

/**
 * Standalone helper: get real-time session status for a lesson.
 *
 * @param int $lesson_id Lesson post ID.
 * @return string 'upcoming'|'live'|'ended'|''
 */
function mf_lls_get_session_status($lesson_id)
{
    return MF_LLS_Live_Lesson::get_session_status(absint($lesson_id));
}
