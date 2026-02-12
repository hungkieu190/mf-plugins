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
 * Zoom integration using Server-to-Server OAuth
 */
class MF_LLS_Zoom extends MF_LLS_Platform
{

    /**
     * @var string Zoom API base URL
     */
    private $api_url = 'https://api.zoom.us/v2';

    /**
     * Create a new meeting room
     *
     * @param array $args Meeting arguments
     * @return array|WP_Error
     */
    public function create_room($args)
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $body = array(
            'topic' => $args['title'],
            'type' => 2, // Scheduled meeting
            'start_time' => gmdate('Y-m-d\TH:i:s\Z', strtotime($args['start_time'])),
            'duration' => absint($args['duration']),
            'timezone' => get_option('timezone_string') ?: 'UTC',
            'settings' => array(
                'host_video' => true,
                'participant_video' => true,
                'join_before_host' => false,
                'mute_upon_entry' => true,
                'auto_recording' => 'cloud',
            ),
        );

        $response = wp_remote_post($this->api_url . '/users/me/meetings', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($body),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (201 !== wp_remote_retrieve_response_code($response)) {
            return new WP_Error('zoom_api_error', $data['message'] ?? __('Unknown Zoom API error', 'lp-live-studio'));
        }

        return array(
            'meeting_id' => $data['id'],
            'join_url' => $data['join_url'],
            'host_url' => $data['start_url'],
            'platform_data' => $data,
        );
    }

    /**
     * Delete an existing meeting room
     */
    public function delete_room($meeting_id)
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $response = wp_remote_request($this->api_url . "/meetings/{$meeting_id}", array(
            'method' => 'DELETE',
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
            ),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        return 204 === wp_remote_retrieve_response_code($response);
    }

    /**
     * Get join URL
     */
    public function get_join_url($meeting_id)
    {
        // Zoom join URL is static and returned on creation
        return '';
    }

    /**
     * Get host URL
     */
    public function get_host_url($meeting_id)
    {
        return '';
    }

    /**
     * End session
     */
    public function end_session($meeting_id)
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $response = wp_remote_request($this->api_url . "/meetings/{$meeting_id}/status", array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode(array('action' => 'end')),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        return 204 === wp_remote_retrieve_response_code($response);
    }

    /**
     * Get attendance data
     */
    public function get_attendance($meeting_id)
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $response = wp_remote_get($this->api_url . "/report/meetings/{$meeting_id}/participants", array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
            ),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data['participants'] ?? array();
    }

    /**
     * Check configuration
     */
    public function is_configured()
    {
        $client_id = get_option(MF_LLS_OPT_ZOOM_API_KEY);
        $client_secret = get_option(MF_LLS_OPT_ZOOM_API_SECRET);
        $account_id = get_option('mf_lls_zoom_account_id');

        return !empty($client_id) && !empty($client_secret) && !empty($account_id);
    }

    /**
     * Test connection
     */
    public function test_connection()
    {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $response = wp_remote_get($this->api_url . '/users/me', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
            ),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        return 200 === wp_remote_retrieve_response_code($response);
    }

    /**
     * Get embed HTML (Redirect button for Zoom)
     */
    public function get_embed_html($meeting_id, $user)
    {
        $url = $this->get_join_url($meeting_id);
        if (!$url) {
            // Fallback to meta if empty (fetched from creation)
            return '';
        }

        ob_start();
        ?>
        <div class="mf-lls-zoom-join">
            <a href="<?php echo esc_url($url); ?>" class="button button-primary" target="_blank">
                <?php esc_html_e('Join Zoom Meeting', 'lp-live-studio'); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get Server-to-Server OAuth Access Token
     *
     * @return string|WP_Error
     */
    private function get_access_token()
    {
        $token = get_transient('mf_lls_zoom_token');
        if ($token) {
            return $token;
        }

        $client_id = get_option(MF_LLS_OPT_ZOOM_API_KEY);
        $client_secret = get_option(MF_LLS_OPT_ZOOM_API_SECRET);
        $account_id = get_option('mf_lls_zoom_account_id');

        if (empty($client_id) || empty($client_secret) || empty($account_id)) {
            return new WP_Error('zoom_config_missing', __('Zoom credentials missing.', 'lp-live-studio'));
        }

        $auth_url = 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id=' . $account_id;
        $response = wp_remote_post($auth_url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
            ),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($data['access_token'])) {
            set_transient('mf_lls_zoom_token', $data['access_token'], $data['expires_in'] - 60);
            return $data['access_token'];
        }

        return new WP_Error('zoom_auth_failed', $data['reason'] ?? __('Zoom Authentication failed.', 'lp-live-studio'));
    }
}
