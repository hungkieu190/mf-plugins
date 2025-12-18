<?php
/**
 * Hooks class for LP Lesson Completion Sound
 *
 * @package LP_Lesson_Completion_Sound
 */

defined('ABSPATH') || exit();

/**
 * Class LP_LCS_Hooks
 */
class LP_LCS_Hooks
{
    /**
     * Instance
     *
     * @var LP_LCS_Hooks
     */
    protected static $instance = null;

    /**
     * LP_LCS_Hooks constructor.
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
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Hook into lesson completion
        add_action('learn-press/user-completed-lesson', array($this, 'handle_lesson_completion'), 10, 3);
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts()
    {
        // Only load on LearnPress pages
        if (!$this->is_learnpress_page()) {
            return;
        }

        // Check license
        $license_handler = LP_Lesson_Completion_Sound::instance()->get_license_handler();
        if (!$license_handler->is_feature_enabled()) {
            return;
        }

        // Check if feature is enabled for current user
        $enable = LP_LCS_Settings::get_setting('lp_lcs_enable', 'yes');
        if ($enable !== 'yes') {
            return;
        }

        // Enqueue confetti library
        wp_enqueue_script(
            'canvas-confetti',
            LP_LCS_URL . 'assets/js/confetti.min.js',
            array(),
            '1.9.2',
            true
        );

        // Enqueue main script
        wp_enqueue_script(
            'lp-completion-sound',
            LP_LCS_URL . 'assets/js/completion-sound.js',
            array('jquery', 'canvas-confetti'),
            LP_LCS_VERSION,
            true
        );

        // Enqueue styles
        wp_enqueue_style(
            'lp-completion-effect',
            LP_LCS_URL . 'assets/css/completion-effect.css',
            array(),
            LP_LCS_VERSION
        );

        // Localize script with settings
        $this->localize_script();
    }

    /**
     * Localize script with user settings
     */
    private function localize_script()
    {
        $sound = LP_LCS_Settings::get_setting('lp_lcs_sound', 'ting');

        // Localize script with settings
        wp_localize_script('lp-completion-sound', 'lpCompletionSound', array(
            'enabled' => LP_LCS_Settings::get_setting('lp_lcs_enable'),
            'sound' => $sound,
            'soundUrl' => LP_LCS_URL . 'assets/sounds/' . $sound . '.mp3',
            'confetti' => LP_LCS_Settings::get_setting('lp_lcs_confetti'),
            'preventRedirect' => LP_LCS_Settings::get_setting('lp_lcs_prevent_redirect'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lp_lcs_nonce')
        ));
    }

    /**
     * Check if current page is a LearnPress page
     *
     * @return bool
     */
    private function is_learnpress_page()
    {
        // Check if we're on a course or lesson page
        if (is_singular('lp_course') || is_singular('lp_lesson')) {
            return true;
        }

        // Check if LearnPress is processing
        if (LP_Global::course_item()) {
            return true;
        }

        return false;
    }

    /**
     * Handle lesson completion
     *
     * @param int $item_id Lesson ID
     * @param int $course_id Course ID
     * @param int $user_id User ID
     */
    public function handle_lesson_completion($item_id, $course_id, $user_id)
    {
        // This hook is mainly for server-side processing if needed
        // The actual sound/confetti is triggered by JavaScript on frontend
        // We can use this for logging or other server-side actions in the future
    }

    /**
     * Get instance
     *
     * @return LP_LCS_Hooks
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
