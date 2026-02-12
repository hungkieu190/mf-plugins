<?php
/**
 * Agora Platform Class
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Agora
 *
 * Agora.io integration for low-latency video/audio
 */
class MF_LLS_Agora extends MF_LLS_Platform
{

    /**
     * Create a new room (Generate channel name)
     */
    public function create_room($args)
    {
        $channel_name = 'mf_lls_' . uniqid();
        return array(
            'meeting_id' => $channel_name,
            'join_url' => home_url('/live-room/' . $channel_name), // Custom frontend route
            'host_url' => home_url('/live-room/' . $channel_name . '?role=host'),
            'platform_data' => array('channel' => $channel_name),
        );
    }

    /**
     * Delete room
     */
    public function delete_room($meeting_id)
    {
        return true;
    }

    /**
     * Get join URL
     */
    public function get_join_url($meeting_id)
    {
        return get_post_meta(get_the_ID(), '_mf_lls_join_url', true);
    }

    /**
     * Get host URL
     */
    public function get_host_url($meeting_id)
    {
        return get_post_meta(get_the_ID(), '_mf_lls_host_url', true);
    }

    /**
     * End session
     */
    public function end_session($meeting_id)
    {
        return true;
    }

    /**
     * Get attendance
     */
    public function get_attendance($meeting_id)
    {
        return array();
    }

    /**
     * Check configuration
     */
    public function is_configured()
    {
        return !empty(get_option(MF_LLS_OPT_AGORA_APP_ID));
    }

    /**
     * Test connection
     */
    public function test_connection()
    {
        return true;
    }

    /**
     * Get embed HTML (Agora SDK implementation)
     */
    public function get_embed_html($meeting_id, $user)
    {
        ob_start();
        ?>
        <div id="mf-lls-agora-container" data-channel="<?php echo esc_attr($meeting_id); ?>">
            <!-- Agora video will be injected here by JS -->
        </div>
        <?php
        return ob_get_clean();
    }
}
