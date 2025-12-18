<?php
/**
 * Admin functionality for LP Lesson Completion Sound
 *
 * @package LP_Lesson_Completion_Sound
 */

defined('ABSPATH') || exit();

/**
 * Class LP_LCS_Admin
 */
class LP_LCS_Admin
{
    /**
     * Instance
     *
     * @var LP_LCS_Admin
     */
    protected static $instance = null;

    /**
     * LP_LCS_Admin constructor.
     */
    protected function __construct()
    {
        $this->hooks();
    }

    /**
     * Register hooks
     */
    private function hooks()
    {
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . LP_LCS_BASENAME, array($this, 'add_action_links'));
    }

    /**
     * Add action links to plugins page
     *
     * @param array $links Existing links
     * @return array
     */
    public function add_action_links($links)
    {
        $custom_links = array(
            '<a href="' . admin_url('admin.php?page=lp-lcs-license') . '">' . __('License', 'lp-lesson-completion-sound') . '</a>',
        );

        return array_merge($custom_links, $links);
    }

    /**
     * Get instance
     *
     * @return LP_LCS_Admin
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
