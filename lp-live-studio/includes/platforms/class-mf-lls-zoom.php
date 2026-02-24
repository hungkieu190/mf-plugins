<?php
/**
 * Zoom Platform Class
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Zoom
 *
 * Zoom integration using Server-to-Server OAuth (Zoom API v2+).
 * Docs: https://developers.zoom.us/docs/internal-apps/
 */
class MF_LLS_Zoom extends MF_LLS_Platform
{

    /**
     * Zoom API base URL
     */
    const API_URL = 'https://api.zoom.us/v2';

    /**
     * Zoom OAuth token endpoint
     */
    const TOKEN_URL = 'https://zoom.us/oauth/token';

    /**
     * Transient key for cached access token
     */
    const TOKEN_TRANSIENT = 'mf_lls_zoom_access_token';

    /**
     * REST namespace for webhook
     */
    const REST_NAMESPACE = 'mf-lls/v1';

    // -------------------------------------------------------------------------
    // Abstract interface implementation
    // -------------------------------------------------------------------------

    /**
     * Create a new Zoom scheduled meeting.
     *
     * @param array $args {
     *     @type string $title             Meeting topic.
     *     @type string $start_time        Datetime string (Y-m-d H:i:s).
     *     @type int    $duration          Duration in minutes.
     *     @type int    $participant_limit Max participants (0 = unlimited).
     * }
     * @return array|WP_Error Keys: meeting_id, join_url, host_url, platform_data.
     */
    public function create_room($args)
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $timezone = get_option('timezone_string') ?: 'UTC';
        $start_utc = gmdate('Y-m-d\TH:i:s\Z', strtotime($args['start_time']));

        $body = array(
            'topic' => sanitize_text_field($args['title']),
            'type' => 2, // Scheduled
            'start_time' => $start_utc,
            'duration' => absint($args['duration']),
            'timezone' => $timezone,
            'settings' => array(
                'host_video' => true,
                'participant_video' => true,
                'join_before_host' => false,
                'mute_upon_entry' => true,
                'auto_recording' => 'cloud',
                'waiting_room' => false,
            ),
        );

        $response = wp_remote_post(
            self::API_URL . '/users/me/meetings',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode($body),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (201 !== $code) {
            return new WP_Error(
                'zoom_create_failed',
                isset($data['message']) ? esc_html($data['message']) : esc_html__('Unknown Zoom API error.', 'lp-live-studio'),
                array('http_code' => $code)
            );
        }

        return array(
            'meeting_id' => (string) $data['id'],
            'join_url' => esc_url_raw($data['join_url']),
            'host_url' => esc_url_raw($data['start_url']),
            'platform_data' => $data,
        );
    }

    /**
     * Delete an existing Zoom meeting.
     *
     * @param string $meeting_id Zoom meeting ID.
     * @return bool|WP_Error
     */
    public function delete_room($meeting_id)
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $response = wp_remote_request(
            self::API_URL . '/meetings/' . rawurlencode($meeting_id),
            array(
                'method' => 'DELETE',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);

        // 204 = deleted; 404 = already gone — both are acceptable
        if (204 === $code || 404 === $code) {
            return true;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return new WP_Error(
            'zoom_delete_failed',
            isset($data['message']) ? esc_html($data['message']) : esc_html__('Failed to delete Zoom meeting.', 'lp-live-studio'),
            array('http_code' => $code)
        );
    }

    /**
     * Get participant join URL from post meta (stored at creation).
     *
     * @param string $meeting_id Zoom meeting ID.
     * @return string
     */
    public function get_join_url($meeting_id)
    {
        $lesson_id = $this->get_lesson_id_by_meeting_id($meeting_id);
        if (!$lesson_id) {
            return '';
        }
        return (string) get_post_meta($lesson_id, '_mf_lls_join_url', true);
    }

    /**
     * Get host/start URL from post meta (stored at creation).
     *
     * @param string $meeting_id Zoom meeting ID.
     * @return string
     */
    public function get_host_url($meeting_id)
    {
        $lesson_id = $this->get_lesson_id_by_meeting_id($meeting_id);
        if (!$lesson_id) {
            return '';
        }
        return (string) get_post_meta($lesson_id, '_mf_lls_host_url', true);
    }

    /**
     * End an ongoing Zoom meeting via API.
     *
     * @param string $meeting_id Zoom meeting ID.
     * @return bool|WP_Error
     */
    public function end_session($meeting_id)
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $response = wp_remote_request(
            self::API_URL . '/meetings/' . rawurlencode($meeting_id) . '/status',
            array(
                'method' => 'PUT',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode(array('action' => 'end')),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if (204 === $code) {
            return true;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return new WP_Error(
            'zoom_end_failed',
            isset($data['message']) ? esc_html($data['message']) : esc_html__('Failed to end Zoom session.', 'lp-live-studio'),
            array('http_code' => $code)
        );
    }

    /**
     * Get attendance from Zoom Report API.
     * Note: report data is available ~30 min after meeting ends.
     *
     * @param string $meeting_id Zoom meeting ID.
     * @return array|WP_Error
     */
    public function get_attendance($meeting_id)
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $response = wp_remote_get(
            self::API_URL . '/report/meetings/' . rawurlencode($meeting_id) . '/participants',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (200 !== $code) {
            return new WP_Error(
                'zoom_attendance_failed',
                isset($data['message']) ? esc_html($data['message']) : esc_html__('Failed to fetch attendance.', 'lp-live-studio'),
                array('http_code' => $code)
            );
        }

        return isset($data['participants']) ? $data['participants'] : array();
    }

    /**
     * Check if Zoom credentials are fully configured.
     *
     * @return bool
     */
    public function is_configured()
    {
        return !empty(get_option(MF_LLS_OPT_ZOOM_ACCOUNT_ID))
            && !empty(get_option(MF_LLS_OPT_ZOOM_API_KEY))
            && !empty(get_option(MF_LLS_OPT_ZOOM_API_SECRET));
    }

    /**
     * Test connection by calling GET /users/me.
     *
     * @return bool|WP_Error true on success, WP_Error on failure.
     */
    public function test_connection()
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $response = wp_remote_get(
            self::API_URL . '/users/me',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if (200 === $code) {
            return true;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return new WP_Error(
            'zoom_test_failed',
            isset($data['message']) ? esc_html($data['message']) : esc_html__('Zoom API test failed. Check your credentials.', 'lp-live-studio'),
            array('http_code' => $code)
        );
    }

    /**
     * Get meeting embed HTML.
     * Zoom does not support iframe embed — renders join/start button instead.
     *
     * @param string  $meeting_id Zoom meeting ID.
     * @param WP_User $user       Current user object.
     * @return string HTML output.
     */
    public function get_embed_html($meeting_id, $user)
    {
        $lesson_id = $this->get_lesson_id_by_meeting_id($meeting_id);
        if (!$lesson_id) {
            return '';
        }

        $is_tutor = (absint(get_post_field('post_author', $lesson_id)) === $user->ID);
        $target_url = $is_tutor
            ? get_post_meta($lesson_id, '_mf_lls_host_url', true)
            : get_post_meta($lesson_id, '_mf_lls_join_url', true);

        if (empty($target_url)) {
            return '<p>' . esc_html__('Meeting link is not available yet.', 'lp-live-studio') . '</p>';
        }

        ob_start();
        ?>
        <div class="mf-lls-zoom-join">
            <a href="<?php echo esc_url($target_url); ?>" class="mf-lls-join-btn mf-lls-zoom-btn" target="_blank"
                rel="noopener noreferrer">
                <?php echo $is_tutor
                    ? esc_html__('Start Zoom Meeting', 'lp-live-studio')
                    : esc_html__('Join Zoom Meeting', 'lp-live-studio');
                ?>
            </a>
            <p class="mf-lls-join-note">
                <?php esc_html_e('Opens in a new tab. You may be prompted to open the Zoom client.', 'lp-live-studio'); ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // REST API: Zoom Webhook
    // -------------------------------------------------------------------------

    /**
     * Register Zoom webhook REST route.
     * Called by MF_LLS_Addon on rest_api_init.
     */
    public function register_webhook_route()
    {
        register_rest_route(
            self::REST_NAMESPACE,
            '/webhook/zoom',
            array(
                'methods' => 'POST',
                // Zoom sends requests before we can check nonce; signature verified manually inside handler.
                'permission_callback' => '__return_true',
                'callback' => array($this, 'handle_zoom_webhook'),
            )
        );
    }

    /**
     * Handle incoming Zoom webhook POST.
     *
     * @param WP_REST_Request $request Incoming request.
     * @return WP_REST_Response
     */
    public function handle_zoom_webhook(WP_REST_Request $request)
    {
        $payload = json_decode($request->get_body(), true);
        $event = isset($payload['event']) ? sanitize_text_field($payload['event']) : '';

        // One-time URL validation challenge from Zoom Marketplace
        if ('endpoint.url_validation' === $event) {
            return $this->handle_url_validation($payload);
        }

        // Verify signature for all real events
        if (!$this->verify_webhook_signature($request)) {
            return new WP_REST_Response(array('error' => 'invalid_signature'), 401);
        }

        switch ($event) {
            case 'meeting.ended':
                $this->on_meeting_ended($payload);
                break;
            case 'meeting.participant_joined':
                $this->on_participant_joined($payload);
                break;
            case 'meeting.participant_left':
                $this->on_participant_left($payload);
                break;
        }

        return new WP_REST_Response(array('status' => 'ok'), 200);
    }

    // -------------------------------------------------------------------------
    // Private: Webhook helpers
    // -------------------------------------------------------------------------

    /**
     * Respond to Zoom URL validation challenge (one-time setup).
     *
     * @param array $payload Parsed webhook body.
     * @return WP_REST_Response
     */
    private function handle_url_validation($payload)
    {
        $plain_token = isset($payload['payload']['plainToken']) ? $payload['payload']['plainToken'] : '';
        $webhook_secret = get_option('mf_lls_zoom_webhook_secret', '');
        $encrypted = hash_hmac('sha256', $plain_token, $webhook_secret);

        return new WP_REST_Response(
            array(
                'plainToken' => $plain_token,
                'encryptedToken' => $encrypted,
            ),
            200
        );
    }

    /**
     * Verify Zoom webhook HMAC-SHA256 signature.
     *
     * @param WP_REST_Request $request Incoming request.
     * @return bool
     */
    private function verify_webhook_signature(WP_REST_Request $request)
    {
        $secret = get_option('mf_lls_zoom_webhook_secret', '');

        // If no secret configured, skip verification (not recommended in production)
        if (empty($secret)) {
            return true;
        }

        $timestamp = $request->get_header('x-zm-request-timestamp');
        $signature = $request->get_header('x-zm-signature');

        if (empty($timestamp) || empty($signature)) {
            return false;
        }

        $message = 'v0:' . $timestamp . ':' . $request->get_body();
        $expected = 'v0=' . hash_hmac('sha256', $message, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Handle meeting.ended — update lesson status meta.
     *
     * @param array $payload Webhook payload.
     */
    private function on_meeting_ended($payload)
    {
        $meeting_id = (string) ($payload['payload']['object']['id'] ?? '');
        if (empty($meeting_id)) {
            return;
        }

        $lesson_id = $this->get_lesson_id_by_meeting_id($meeting_id);
        if (!$lesson_id) {
            return;
        }

        update_post_meta($lesson_id, '_mf_lls_status', 'ended');

        // Modules 5 and 7 hook into this action
        do_action('mf_lls_session_ended', $lesson_id, 'zoom');
    }

    /**
     * Handle meeting.participant_joined — fire attendance action.
     *
     * @param array $payload Webhook payload.
     */
    private function on_participant_joined($payload)
    {
        $meeting_id = (string) ($payload['payload']['object']['id'] ?? '');
        $participant = $payload['payload']['object']['participant'] ?? array();
        $email = sanitize_email($participant['email'] ?? '');

        if (empty($meeting_id) || empty($email)) {
            return;
        }

        $lesson_id = $this->get_lesson_id_by_meeting_id($meeting_id);
        if (!$lesson_id) {
            return;
        }

        $user = get_user_by('email', $email);
        if (!$user) {
            return;
        }

        // Module 5 (Attendance) hooks into this
        do_action('mf_lls_participant_joined', $user->ID, $lesson_id, 'zoom', $participant);
    }

    /**
     * Handle meeting.participant_left — fire attendance action.
     *
     * @param array $payload Webhook payload.
     */
    private function on_participant_left($payload)
    {
        $meeting_id = (string) ($payload['payload']['object']['id'] ?? '');
        $participant = $payload['payload']['object']['participant'] ?? array();
        $email = sanitize_email($participant['email'] ?? '');

        if (empty($meeting_id) || empty($email)) {
            return;
        }

        $lesson_id = $this->get_lesson_id_by_meeting_id($meeting_id);
        if (!$lesson_id) {
            return;
        }

        $user = get_user_by('email', $email);
        if (!$user) {
            return;
        }

        // Module 5 (Attendance) hooks into this
        do_action('mf_lls_participant_left', $user->ID, $lesson_id, 'zoom', $participant);
    }

    // -------------------------------------------------------------------------
    // Private: Core helpers
    // -------------------------------------------------------------------------

    /**
     * Get Server-to-Server OAuth access token.
     * Cached in transient until near expiry.
     *
     * @return string|WP_Error
     */
    private function get_access_token()
    {
        $cached = get_transient(self::TOKEN_TRANSIENT);
        if ($cached) {
            return $cached;
        }

        $account_id = get_option(MF_LLS_OPT_ZOOM_ACCOUNT_ID);
        $client_id = get_option(MF_LLS_OPT_ZOOM_API_KEY);
        $client_secret = get_option(MF_LLS_OPT_ZOOM_API_SECRET);

        if (empty($account_id) || empty($client_id) || empty($client_secret)) {
            return new WP_Error(
                'zoom_config_missing',
                esc_html__('Zoom credentials are incomplete. Please configure Account ID, Client ID, and Client Secret in Live Studio Settings.', 'lp-live-studio')
            );
        }

        $response = wp_remote_post(
            self::TOKEN_URL . '?grant_type=account_credentials&account_id=' . rawurlencode($account_id),
            array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (200 !== $code || empty($data['access_token'])) {
            $reason = $data['reason'] ?? ($data['error_description'] ?? 'Zoom authentication failed.');
            return new WP_Error('zoom_auth_failed', esc_html($reason));
        }

        // Cache with 60s safety buffer before actual expiry
        $ttl = max(60, (int) $data['expires_in'] - 60);
        set_transient(self::TOKEN_TRANSIENT, $data['access_token'], $ttl);

        return $data['access_token'];
    }

    /**
     * Find lesson post ID by Zoom meeting ID stored in post meta.
     * Uses object cache to avoid repeated DB queries.
     *
     * @param string $meeting_id Zoom meeting ID.
     * @return int|null Post ID or null.
     */
    private function get_lesson_id_by_meeting_id($meeting_id)
    {
        if (empty($meeting_id)) {
            return null;
        }

        $cache_key = 'mf_lls_lesson_by_meeting_' . md5($meeting_id);
        $cached = wp_cache_get($cache_key, 'mf_lls');

        if (false !== $cached) {
            return $cached ?: null;
        }

        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $lesson_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
                '_mf_lls_meeting_id',
                $meeting_id
            )
        );

        $result = $lesson_id ? (int) $lesson_id : 0;

        // Cache result for 5 minutes (0 = not found, avoids repeated queries)
        wp_cache_set($cache_key, $result, 'mf_lls', 300);

        return $result ?: null;
    }
}
