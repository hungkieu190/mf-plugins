<?php
/**
 * Profile integration for LP Lesson Completion Sound
 *
 * @package LP_Lesson_Completion_Sound
 */

defined('ABSPATH') || exit();

/**
 * Class LP_LCS_Profile
 */
class LP_LCS_Profile
{
    /**
     * Instance
     *
     * @var LP_LCS_Profile
     */
    protected static $instance = null;

    /**
     * LP_LCS_Profile constructor.
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
        // Add settings tab to LearnPress profile
        add_filter('learn-press/profile-tabs', array($this, 'add_profile_tab'), 20);
        add_action('learn-press/profile-content-completion-sound', array($this, 'render_profile_content'));

        // Enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

        // Save settings
        add_action('init', array($this, 'save_settings'));
    }

    /**
     * Enqueue profile settings styles
     */
    public function enqueue_styles()
    {
        // Only load on profile page
        if (learn_press_is_profile()) {
            wp_enqueue_style(
                'lp-lcs-profile-settings',
                LP_LCS_URL . 'assets/css/profile-settings.css',
                array(),
                LP_LCS_VERSION
            );
        }
    }

    /**
     * Add profile tab
     *
     * @param array $tabs Existing tabs
     * @return array
     */
    public function add_profile_tab($tabs)
    {
        $tabs['completion-sound'] = array(
            'title' => __('Sound & Effects', 'lp-lesson-completion-sound'),
            'slug' => 'completion-sound',
            'callback' => array($this, 'render_profile_content'),
            'priority' => 50,
            'icon' => '<i class="lp-icon-bullhorn"></i>',
        );

        return $tabs;
    }

    /**
     * Render profile content
     */
    public function render_profile_content()
    {
        // Check license
        $license_handler = LP_Lesson_Completion_Sound::instance()->get_license_handler();
        if (!$license_handler->is_feature_enabled()) {
            $this->render_license_required_notice();
            return;
        }

        $user_id = get_current_user_id();
        $settings = LP_LCS_Settings::get_all_settings($user_id);
        $available_sounds = LP_LCS_Settings::get_available_sounds();

        include LP_LCS_PATH . 'templates/profile-settings.php';
    }

    /**
     * Render license required notice
     */
    private function render_license_required_notice()
    {
        ?>
        <div class="lp-lcs-license-notice">
            <h3><?php esc_html_e('License Required', 'lp-lesson-completion-sound'); ?></h3>
            <p>
                <?php
                printf(
                    esc_html__('Please %sactivate your license%s to use the Lesson Completion Sound features.', 'lp-lesson-completion-sound'),
                    '<a href="' . esc_url(admin_url('admin.php?page=lp-lcs-license')) . '">',
                    '</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save settings
     */
    public function save_settings()
    {
        // Check if form submitted
        if (!isset($_POST['lp_lcs_save_settings'])) {
            return;
        }

        // Verify nonce
        if (!isset($_POST['lp_lcs_settings_nonce']) || !wp_verify_nonce($_POST['lp_lcs_settings_nonce'], 'lp_lcs_save_settings')) {
            return;
        }

        // Check user is logged in
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();

        // Save enable/disable
        $enable = isset($_POST['lp_lcs_enable']) ? 'yes' : 'no';
        LP_LCS_Settings::update_setting('lp_lcs_enable', $enable, $user_id);

        // Save sound selection
        if (isset($_POST['lp_lcs_sound'])) {
            $sound = sanitize_text_field($_POST['lp_lcs_sound']);
            $available_sounds = array_keys(LP_LCS_Settings::get_available_sounds());
            if (in_array($sound, $available_sounds)) {
                LP_LCS_Settings::update_setting('lp_lcs_sound', $sound, $user_id);
            }
        }

        // Save confetti toggle
        $confetti = isset($_POST['lp_lcs_confetti']) ? 'yes' : 'no';
        LP_LCS_Settings::update_setting('lp_lcs_confetti', $confetti, $user_id);

        // Save prevent redirect toggle
        $prevent_redirect = isset($_POST['lp_lcs_prevent_redirect']) ? 'yes' : 'no';
        LP_LCS_Settings::update_setting('lp_lcs_prevent_redirect', $prevent_redirect, $user_id);

        // Redirect to avoid resubmission
        wp_safe_redirect(add_query_arg('lp_lcs_saved', '1', wp_get_referer()));
        exit;
    }

    /**
     * Get instance
     *
     * @return LP_LCS_Profile
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
