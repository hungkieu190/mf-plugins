<?php
/**
 * Notification Handler
 *
 * @package LP_Telegram_Notifier
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LP_Notification_Handler
 *
 * Handles LearnPress enrollment events and sends Telegram notifications
 */
class MF_LP_Notification_Handler
{

    /**
     * Instance of this class
     *
     * @var MF_LP_Notification_Handler
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return MF_LP_Notification_Handler
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Hook into LearnPress user enrollment.
        add_action('learn-press/user-enrolled-course', array($this, 'mf_handle_user_enrolled'), 10, 3);
    }

    /**
     * Handle user enrolled in course event
     *
     * @param int   $course_id Course ID.
     * @param int   $user_id User ID.
     * @param mixed $return Return value from enrollment.
     */
    public function mf_handle_user_enrolled($course_id, $user_id, $return)
    {
        // Check license before sending notification.
        $license_handler = LP_Telegram_Notifier::instance()->get_license_handler();
        if (!$license_handler->is_feature_enabled()) {
            error_log('LP Telegram Notifier: Notification blocked - license not active');
            return;
        }

        // Check if notifications are enabled.
        $is_enabled = get_option('mf_lp_tg_enabled', 'no');
        if ('yes' !== $is_enabled) {
            return;
        }

        // Get chat ID.
        $chat_id = get_option('mf_lp_tg_chat_id', '');
        if (empty($chat_id)) {
            return;
        }

        // Get user data.
        $user = get_userdata($user_id);
        if (!$user) {
            error_log('LP Telegram Notifier: Invalid user ID ' . $user_id);
            return;
        }

        // Get course data.
        $course = get_post($course_id);
        if (!$course) {
            error_log('LP Telegram Notifier: Invalid course ID ' . $course_id);
            return;
        }

        // Format message.
        $message = $this->mf_format_enrollment_message($user, $course);

        // Send notification (async - don't block enrollment).
        MF_Telegram_API::send_message($chat_id, $message);
    }

    /**
     * Format enrollment notification message
     *
     * @param WP_User $user User object.
     * @param WP_Post $course Course post object.
     * @return string Formatted message.
     */
    private function mf_format_enrollment_message($user, $course)
    {
        // Get current time in Vietnam timezone.
        $timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
        $datetime = new DateTime('now', $timezone);
        $time_string = $datetime->format('d/m/Y H:i');

        // Build message using HTML format.
        $message = "🎓 <b>Học viên mới đăng ký!</b>\n\n";
        $message .= "👤 Học viên: " . esc_html($user->display_name) . "\n";
        $message .= "📧 Email: " . esc_html($user->user_email) . "\n";
        $message .= "📚 Khóa học: " . esc_html($course->post_title) . "\n";
        $message .= "⏰ Thời gian: " . $time_string;

        return $message;
    }
}
