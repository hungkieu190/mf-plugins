<?php
/**
 * Telegram API Handler
 *
 * @package LP_Telegram_Notifier
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_Telegram_API
 *
 * Handles communication with Telegram Bot API
 */
class MF_Telegram_API
{

    /**
     * Telegram Bot API base URL
     */
    const API_BASE_URL = 'https://api.telegram.org/bot';

    /**
     * Send message to Telegram
     *
     * @param string $chat_id Telegram chat ID.
     * @param string $message Message content.
     * @param string $parse_mode Message parse mode (HTML, Markdown, or empty).
     * @return bool True on success, false on failure.
     */
    public static function send_message($chat_id, $message, $parse_mode = 'HTML')
    {
        // Get bot token from settings.
        $bot_token = get_option('learn_press_mf_lp_tg_bot_token', '');

        if (empty($bot_token) || empty($chat_id) || empty($message)) {
            error_log('LP Telegram Notifier: Missing configuration or message content.');
            return false;
        }

        // Build API URL.
        $api_url = self::API_BASE_URL . $bot_token . '/sendMessage';

        // Prepare request body.
        $body = array(
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => $parse_mode,
        );

        // Send request to Telegram API.
        $response = wp_remote_post(
            $api_url,
            array(
                'timeout' => 5,
                'body' => $body,
            )
        );

        // Check for errors.
        if (is_wp_error($response)) {
            error_log('LP Telegram Notifier: API request failed - ' . $response->get_error_message());
            return false;
        }

        // Get response code.
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        // Log response for debugging.
        if (200 !== $response_code) {
            error_log('LP Telegram Notifier: API returned error code ' . $response_code . ' - ' . $response_body);
            return false;
        }

        return true;
    }

    /**
     * Test connection to Telegram API
     *
     * @param string $bot_token Bot token to test.
     * @param string $chat_id Chat ID to test.
     * @return array Array with 'success' (bool) and 'message' (string).
     */
    public static function test_connection($bot_token, $chat_id)
    {
        if (empty($bot_token) || empty($chat_id)) {
            return array(
                'success' => false,
                'message' => __('Bot Token and Chat ID are required.', 'lp-telegram-notifier'),
            );
        }

        // Build API URL.
        $api_url = self::API_BASE_URL . $bot_token . '/sendMessage';

        // Test message.
        $test_message = '✅ ' . __('Connection successful! LP Telegram Notifier is ready.', 'lp-telegram-notifier');

        // Prepare request body.
        $body = array(
            'chat_id' => $chat_id,
            'text' => $test_message,
            'parse_mode' => 'HTML',
        );

        // Send request.
        $response = wp_remote_post(
            $api_url,
            array(
                'timeout' => 5,
                'body' => $body,
            )
        );

        // Check for errors.
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => sprintf(
                    /* translators: %s: Error message */
                    __('Connection failed: %s', 'lp-telegram-notifier'),
                    $response->get_error_message()
                ),
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        if (200 !== $response_code || empty($response_body['ok'])) {
            $error_description = isset($response_body['description']) ? $response_body['description'] : __('Unknown error', 'lp-telegram-notifier');
            return array(
                'success' => false,
                'message' => sprintf(
                    /* translators: %s: Error description */
                    __('Telegram API error: %s', 'lp-telegram-notifier'),
                    $error_description
                ),
            );
        }

        return array(
            'success' => true,
            'message' => __('Test message sent successfully! Check your Telegram chat.', 'lp-telegram-notifier'),
        );
    }
}
