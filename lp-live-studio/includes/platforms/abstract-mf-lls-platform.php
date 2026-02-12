<?php
/**
 * Abstract Platform Class
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Platform
 *
 * Base class for all meeting platforms
 */
abstract class MF_LLS_Platform
{

    /**
     * Create a new meeting room
     *
     * @param array $args Meeting arguments (title, start_time, duration, etc.)
     * @return array|WP_Error Array with meeting_id, join_url, host_url, and raw platform_data
     */
    abstract public function create_room($args);

    /**
     * Delete an existing meeting room
     *
     * @param string $meeting_id
     * @return bool|WP_Error
     */
    abstract public function delete_room($meeting_id);

    /**
     * Get join URL for a participant
     *
     * @param string $meeting_id
     * @return string
     */
    abstract public function get_join_url($meeting_id);

    /**
     * Get host/start URL for the tutor
     *
     * @param string $meeting_id
     * @return string
     */
    abstract public function get_host_url($meeting_id);

    /**
     * End an ongoing session
     *
     * @param string $meeting_id
     * @return bool|WP_Error
     */
    abstract public function end_session($meeting_id);

    /**
     * Get attendance data from the platform
     *
     * @param string $meeting_id
     * @return array|WP_Error
     */
    abstract public function get_attendance($meeting_id);

    /**
     * Check if the platform is correctly configured
     *
     * @return bool
     */
    abstract public function is_configured();

    /**
     * Test connection to the platform API
     *
     * @return bool|WP_Error
     */
    abstract public function test_connection();

    /**
     * Get the HTML to embed the meeting room or a join button
     *
     * @param string  $meeting_id
     * @param WP_User $user
     * @return string HTML content
     */
    abstract public function get_embed_html($meeting_id, $user);
}
