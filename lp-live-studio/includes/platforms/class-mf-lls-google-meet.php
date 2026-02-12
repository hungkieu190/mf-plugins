<?php
/**
 * Google Meet Platform Class
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Google_Meet
 *
 * Google Meet integration using Google Calendar API
 */
class MF_LLS_Google_Meet extends MF_LLS_Platform
{

    /**
     * Create a new meeting room (Calendar Event with Meet)
     */
    public function create_room($args)
    {
        // Prepare event with conferenceData
        return array(
            'meeting_id' => 'placeholder_google_meet_id',
            'join_url' => 'https://meet.google.com/abc-defg-hij',
            'host_url' => 'https://meet.google.com/abc-defg-hij',
            'platform_data' => array(),
        );
    }

    /**
     * Delete meeting
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
        return !empty(get_option(MF_LLS_OPT_GOOGLE_CLIENT_ID));
    }

    /**
     * Test connection
     */
    public function test_connection()
    {
        return true;
    }

    /**
     * Get embed HTML
     */
    public function get_embed_html($meeting_id, $user)
    {
        $url = $this->get_join_url($meeting_id);
        ob_start();
        ?>
        <div class="mf-lls-google-join">
            <a href="<?php echo esc_url($url); ?>" class="button button-primary" target="_blank">
                <?php esc_html_e('Join Google Meet', 'lp-live-studio'); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
}
