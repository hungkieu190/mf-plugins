<?php
/**
 * Alerts handler class for LearnPress Mamflow Insight
 * 
 * @package MF_Insights
 */

defined('ABSPATH') || exit;

class MF_Insights_Alerts
{
    /**
     * Alert types
     */
    const ALERT_LOW_COMPLETION = 'low_completion';
    const ALERT_HIGH_DROPOUT = 'high_dropout';
    const ALERT_QUIZ_FAIL_RATE = 'quiz_fail_rate';
    const ALERT_INACTIVE_STUDENTS = 'inactive_students';

    /**
     * Database handler
     * 
     * @var MF_Insights_Database
     */
    private $db;

    /**
     * Default thresholds
     * 
     * @var array
     */
    private $default_thresholds = [
        'low_completion' => 30,      // Alert if completion rate < 30%
        'high_dropout' => 40,        // Alert if dropout rate > 40%
        'quiz_fail_rate' => 50,      // Alert if quiz fail rate > 50%
        'inactive_students' => 20,   // Alert if inactive students > 20%
    ];

    /**
     * Instance
     */
    protected static $instance = null;

    /**
     * Constructor
     * 
     * @param MF_Insights_Database $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->hooks();
    }

    /**
     * Register hooks
     */
    private function hooks()
    {
        // Schedule cron event on plugin activation
        add_action('mf_insights_daily_alerts', [$this, 'process_daily_alerts']);

        // AJAX handlers
        add_action('wp_ajax_mf_insights_get_alerts', [$this, 'ajax_get_alerts']);
        add_action('wp_ajax_mf_insights_dismiss_alert', [$this, 'ajax_dismiss_alert']);
        add_action('wp_ajax_mf_insights_save_thresholds', [$this, 'ajax_save_thresholds']);
    }

    /**
     * Schedule daily cron event
     */
    public static function schedule_cron()
    {
        if (!wp_next_scheduled('mf_insights_daily_alerts')) {
            wp_schedule_event(time(), 'daily', 'mf_insights_daily_alerts');
        }
    }

    /**
     * Unschedule cron event
     */
    public static function unschedule_cron()
    {
        $timestamp = wp_next_scheduled('mf_insights_daily_alerts');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'mf_insights_daily_alerts');
        }
    }

    /**
     * Get current thresholds
     * 
     * @return array
     */
    public function get_thresholds()
    {
        $saved = get_option('mf_insights_alert_thresholds', []);
        return wp_parse_args($saved, $this->default_thresholds);
    }

    /**
     * Save thresholds
     * 
     * @param array $thresholds
     * @return bool
     */
    public function save_thresholds($thresholds)
    {
        $sanitized = [];
        foreach ($this->default_thresholds as $key => $default) {
            $sanitized[$key] = isset($thresholds[$key]) ? absint($thresholds[$key]) : $default;
        }
        return update_option('mf_insights_alert_thresholds', $sanitized);
    }

    /**
     * Check if value should trigger alert
     * 
     * @param string $type Alert type
     * @param float $value Current value
     * @return bool
     */
    public function should_alert($type, $value)
    {
        $thresholds = $this->get_thresholds();
        $threshold = isset($thresholds[$type]) ? $thresholds[$type] : 0;

        switch ($type) {
            case self::ALERT_LOW_COMPLETION:
                return $value < $threshold;
            case self::ALERT_HIGH_DROPOUT:
            case self::ALERT_QUIZ_FAIL_RATE:
            case self::ALERT_INACTIVE_STUDENTS:
                return $value > $threshold;
            default:
                return false;
        }
    }

    /**
     * Get severity level based on deviation from threshold
     * 
     * @param string $type Alert type
     * @param float $value Current value
     * @return string warning|danger
     */
    public function get_severity($type, $value)
    {
        $thresholds = $this->get_thresholds();
        $threshold = isset($thresholds[$type]) ? $thresholds[$type] : 0;
        $deviation = 0;

        switch ($type) {
            case self::ALERT_LOW_COMPLETION:
                $deviation = $threshold - $value;
                break;
            case self::ALERT_HIGH_DROPOUT:
            case self::ALERT_QUIZ_FAIL_RATE:
            case self::ALERT_INACTIVE_STUDENTS:
                $deviation = $value - $threshold;
                break;
        }

        // If deviation is more than 15%, it's danger level
        return $deviation > 15 ? 'danger' : 'warning';
    }

    /**
     * Get alert label by type
     * 
     * @param string $type Alert type
     * @return string
     */
    public function get_alert_label($type)
    {
        $labels = [
            self::ALERT_LOW_COMPLETION => __('Low Completion Rate', 'lp-mamflow-insights'),
            self::ALERT_HIGH_DROPOUT => __('High Dropout Rate', 'lp-mamflow-insights'),
            self::ALERT_QUIZ_FAIL_RATE => __('High Quiz Fail Rate', 'lp-mamflow-insights'),
            self::ALERT_INACTIVE_STUDENTS => __('High Inactive Students', 'lp-mamflow-insights'),
        ];

        return isset($labels[$type]) ? $labels[$type] : $type;
    }

    /**
     * Get active alerts for a course
     * 
     * @param int $course_id Course ID
     * @return array
     */
    public function get_course_alerts($course_id)
    {
        $alerts = [];
        $thresholds = $this->get_thresholds();

        // Check completion rate
        $completion_rate = $this->db->get_course_completion_rate($course_id);
        if ($this->should_alert(self::ALERT_LOW_COMPLETION, $completion_rate)) {
            $alerts[] = [
                'type' => self::ALERT_LOW_COMPLETION,
                'label' => $this->get_alert_label(self::ALERT_LOW_COMPLETION),
                'value' => $completion_rate,
                'threshold' => $thresholds['low_completion'],
                'severity' => $this->get_severity(self::ALERT_LOW_COMPLETION, $completion_rate),
                'message' => sprintf(
                    __('Completion rate is %s%% (threshold: %s%%)', 'lp-mamflow-insights'),
                    round($completion_rate, 1),
                    $thresholds['low_completion']
                ),
            ];
        }

        // Check dropout rate
        $dropout_rate = $this->db->get_dropoff_rate($course_id);
        if ($this->should_alert(self::ALERT_HIGH_DROPOUT, $dropout_rate)) {
            $alerts[] = [
                'type' => self::ALERT_HIGH_DROPOUT,
                'label' => $this->get_alert_label(self::ALERT_HIGH_DROPOUT),
                'value' => $dropout_rate,
                'threshold' => $thresholds['high_dropout'],
                'severity' => $this->get_severity(self::ALERT_HIGH_DROPOUT, $dropout_rate),
                'message' => sprintf(
                    __('Dropout rate is %s%% (threshold: %s%%)', 'lp-mamflow-insights'),
                    round($dropout_rate, 1),
                    $thresholds['high_dropout']
                ),
            ];
        }

        // Check quiz fail rate
        $quiz_pass_rate = $this->db->get_course_quiz_pass_rate($course_id);
        $quiz_fail_rate = 100 - $quiz_pass_rate;
        if ($this->should_alert(self::ALERT_QUIZ_FAIL_RATE, $quiz_fail_rate)) {
            $alerts[] = [
                'type' => self::ALERT_QUIZ_FAIL_RATE,
                'label' => $this->get_alert_label(self::ALERT_QUIZ_FAIL_RATE),
                'value' => $quiz_fail_rate,
                'threshold' => $thresholds['quiz_fail_rate'],
                'severity' => $this->get_severity(self::ALERT_QUIZ_FAIL_RATE, $quiz_fail_rate),
                'message' => sprintf(
                    __('Quiz fail rate is %s%% (threshold: %s%%)', 'lp-mamflow-insights'),
                    round($quiz_fail_rate, 1),
                    $thresholds['quiz_fail_rate']
                ),
            ];
        }

        return $alerts;
    }

    /**
     * Get all courses with active alerts
     * 
     * @return array
     */
    public function get_all_alerts()
    {
        $all_alerts = [];

        // Get all published courses
        $courses = get_posts([
            'post_type' => 'lp_course',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);

        foreach ($courses as $course_id) {
            $course_alerts = $this->get_course_alerts($course_id);

            if (!empty($course_alerts)) {
                $all_alerts[] = [
                    'course_id' => $course_id,
                    'course_title' => get_the_title($course_id),
                    'alerts' => $course_alerts,
                ];
            }
        }

        return $all_alerts;
    }

    /**
     * Process daily alerts (WP Cron)
     */
    public function process_daily_alerts()
    {
        $all_alerts = $this->get_all_alerts();

        if (empty($all_alerts)) {
            return;
        }

        // Check if email alerts are enabled
        $email_enabled = get_option('mf_insights_email_alerts_enabled', false);
        if (!$email_enabled) {
            return;
        }

        $this->send_alert_email($all_alerts);
    }

    /**
     * Send alert email to admin
     * 
     * @param array $all_alerts
     * @return bool
     */
    private function send_alert_email($all_alerts)
    {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');

        $subject = sprintf(
            __('[%s] Mamflow Insight: %d courses need attention', 'lp-mamflow-insights'),
            $site_name,
            count($all_alerts)
        );

        // Build email body
        $body = sprintf(
            __("Hello,\n\nThis is an automated alert from Mamflow Insight.\n\n%d course(s) have metrics that need your attention:\n\n", 'lp-mamflow-insights'),
            count($all_alerts)
        );

        foreach ($all_alerts as $course_data) {
            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $body .= sprintf("📚 %s\n", $course_data['course_title']);
            $body .= sprintf("   %s\n\n", get_permalink($course_data['course_id']));

            foreach ($course_data['alerts'] as $alert) {
                $icon = $alert['severity'] === 'danger' ? '🔴' : '🟡';
                $body .= sprintf("   %s %s\n", $icon, $alert['message']);
            }
            $body .= "\n";
        }

        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= sprintf(
            __("View dashboard: %s\n\n", 'lp-mamflow-insights'),
            admin_url('admin.php?page=mf-insights')
        );
        $body .= __("—\nMamflow Insight Plugin", 'lp-mamflow-insights');

        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        return wp_mail($admin_email, $subject, $body, $headers);
    }

    /**
     * AJAX: Get alerts for dashboard
     */
    public function ajax_get_alerts()
    {
        check_ajax_referer('mf_insights_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'lp-mamflow-insights')]);
        }

        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;

        if ($course_id) {
            $alerts = $this->get_course_alerts($course_id);
        } else {
            $alerts = $this->get_all_alerts();
        }

        wp_send_json_success(['alerts' => $alerts]);
    }

    /**
     * AJAX: Dismiss alert (optional - store in transient)
     */
    public function ajax_dismiss_alert()
    {
        check_ajax_referer('mf_insights_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'lp-mamflow-insights')]);
        }

        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $alert_type = isset($_POST['alert_type']) ? sanitize_text_field($_POST['alert_type']) : '';

        if (!$course_id || !$alert_type) {
            wp_send_json_error(['message' => __('Invalid parameters', 'lp-mamflow-insights')]);
        }

        // Store dismissed alert for 24 hours
        $dismissed = get_transient('mf_insights_dismissed_alerts') ?: [];
        $key = $course_id . '_' . $alert_type;
        $dismissed[$key] = time();
        set_transient('mf_insights_dismissed_alerts', $dismissed, DAY_IN_SECONDS);

        wp_send_json_success(['message' => __('Alert dismissed', 'lp-mamflow-insights')]);
    }

    /**
     * AJAX: Save thresholds
     */
    public function ajax_save_thresholds()
    {
        check_ajax_referer('mf_insights_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'lp-mamflow-insights')]);
        }

        $thresholds = isset($_POST['thresholds']) ? (array) $_POST['thresholds'] : [];
        $this->save_thresholds($thresholds);

        wp_send_json_success([
            'message' => __('Thresholds saved', 'lp-mamflow-insights'),
            'thresholds' => $this->get_thresholds(),
        ]);
    }

    /**
     * Render alert settings form
     */
    public function render_settings()
    {
        $thresholds = $this->get_thresholds();
        $email_enabled = get_option('mf_insights_email_alerts_enabled', false);
        ?>
        <div class="mf-alert-settings">
            <h3><?php esc_html_e('Alert Thresholds', 'lp-mamflow-insights'); ?></h3>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="threshold_low_completion">
                            <?php esc_html_e('Low Completion Rate', 'lp-mamflow-insights'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" id="threshold_low_completion" 
                               name="thresholds[low_completion]" 
                               value="<?php echo esc_attr($thresholds['low_completion']); ?>"
                               min="0" max="100" step="1" class="small-text"> %
                        <p class="description">
                            <?php esc_html_e('Alert when completion rate falls below this value.', 'lp-mamflow-insights'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="threshold_high_dropout">
                            <?php esc_html_e('High Dropout Rate', 'lp-mamflow-insights'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" id="threshold_high_dropout" 
                               name="thresholds[high_dropout]" 
                               value="<?php echo esc_attr($thresholds['high_dropout']); ?>"
                               min="0" max="100" step="1" class="small-text"> %
                        <p class="description">
                            <?php esc_html_e('Alert when dropout rate exceeds this value.', 'lp-mamflow-insights'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="threshold_quiz_fail_rate">
                            <?php esc_html_e('Quiz Fail Rate', 'lp-mamflow-insights'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" id="threshold_quiz_fail_rate" 
                               name="thresholds[quiz_fail_rate]" 
                               value="<?php echo esc_attr($thresholds['quiz_fail_rate']); ?>"
                               min="0" max="100" step="1" class="small-text"> %
                        <p class="description">
                            <?php esc_html_e('Alert when quiz fail rate exceeds this value.', 'lp-mamflow-insights'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="threshold_inactive_students">
                            <?php esc_html_e('Inactive Students', 'lp-mamflow-insights'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" id="threshold_inactive_students" 
                               name="thresholds[inactive_students]" 
                               value="<?php echo esc_attr($thresholds['inactive_students']); ?>"
                               min="0" max="100" step="1" class="small-text"> %
                        <p class="description">
                            <?php esc_html_e('Alert when inactive student percentage exceeds this value.', 'lp-mamflow-insights'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <h3><?php esc_html_e('Email Notifications', 'lp-mamflow-insights'); ?></h3>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="email_alerts_enabled">
                            <?php esc_html_e('Enable Daily Email Alerts', 'lp-mamflow-insights'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="email_alerts_enabled" 
                                   name="email_alerts_enabled" 
                                   value="1" <?php checked($email_enabled); ?>>
                            <?php esc_html_e('Send daily email digest of active alerts', 'lp-mamflow-insights'); ?>
                        </label>
                        <p class="description">
                            <?php 
                            printf(
                                esc_html__('Emails will be sent to: %s', 'lp-mamflow-insights'),
                                '<code>' . esc_html(get_option('admin_email')) . '</code>'
                            ); 
                            ?>
                        </p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="button" class="button button-primary" id="mf-save-alert-settings">
                    <?php esc_html_e('Save Settings', 'lp-mamflow-insights'); ?>
                </button>
                <span class="spinner"></span>
            </p>
        </div>
        <?php
    }

    /**
     * Get instance
     * 
     * @param MF_Insights_Database $db Database handler
     * @return MF_Insights_Alerts
     */
    public static function instance($db = null)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }
}
