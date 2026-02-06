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
        // Correct hook for LearnPress 4.x+
        add_action('learnpress/user/course-enrolled', array($this, 'mf_handle_user_enrolled'), 10, 3);

        // Async notification handler.
        add_action('mf_lp_tg_send_message_async', array($this, 'mf_process_async_notification'), 10, 2);
    }

    /**
     * Handle user enrolled in course event
     *
     * @param int $order_id Order ID.
     * @param int $course_id Course ID.
     * @param int $user_id User ID.
     */
    public function mf_handle_user_enrolled($order_id, $course_id, $user_id)
    {

        // Check license before sending notification.
        $license_handler = LP_Telegram_Notifier::instance()->get_license_handler();
        $is_licensed = $license_handler->is_feature_enabled();

        if (!$is_licensed) {
            error_log('LP Telegram Notifier: Notification blocked - license not active');
            return;
        }

        // Check if notifications are enabled.
        $is_enabled = get_option('learn_press_mf_lp_tg_enabled', 'no');

        if ('yes' !== $is_enabled) {
            return;
        }

        // Get chat ID.
        $chat_id = get_option('learn_press_mf_lp_tg_chat_id', '');

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
        // Send notification (async using WP-Cron).
        wp_schedule_single_event(time(), 'mf_lp_tg_send_message_async', array($chat_id, $message));
    }

    /**
     * Process async notification
     *
     * @param string $chat_id Chat ID.
     * @param string $message Message content.
     */
    public function mf_process_async_notification($chat_id, $message)
    {
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
        // Get WordPress timezone.
        $timezone = wp_timezone();
        $datetime = new DateTime('now', $timezone);
        $time_string = $datetime->format('d/m/Y H:i');

        // Build message using HTML format.
        $message = "🎓 <b>" . esc_html__('New Student Enrolled!', 'lp-telegram-notifier') . "</b>\n\n";
        $message .= "👤 " . esc_html__('Student:', 'lp-telegram-notifier') . " " . esc_html($user->display_name) . "\n";
        $message .= "📧 " . esc_html__('Email:', 'lp-telegram-notifier') . " " . esc_html($user->user_email) . "\n";
        $message .= "📚 " . esc_html__('Course:', 'lp-telegram-notifier') . " " . esc_html($course->post_title) . "\n";
        $message .= "⏰ " . esc_html__('Time:', 'lp-telegram-notifier') . " " . $time_string;

        return $message;
    }
}
