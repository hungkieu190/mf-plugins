<?php
/**
 * Settings class for LP Lesson Completion Sound
 *
 * @package LP_Lesson_Completion_Sound
 */

defined('ABSPATH') || exit();

/**
 * Class LP_LCS_Settings
 */
class LP_LCS_Settings
{
    /**
     * Instance
     *
     * @var LP_LCS_Settings
     */
    protected static $instance = null;

    /**
     * Default settings
     *
     * @var array
     */
    private $defaults = array(
        'lp_lcs_enable' => 'yes',
        'lp_lcs_sound' => 'ting',
        'lp_lcs_confetti' => 'yes',
        'lp_lcs_custom_sound' => '',
        'lp_lcs_prevent_redirect' => 'no',
    );

    /**
     * LP_LCS_Settings constructor.
     */
    protected function __construct()
    {
        // Constructor
    }

    /**
     * Get setting value
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @param int $user_id User ID (0 for current user)
     * @return mixed
     */
    public static function get_setting($key, $default = '', $user_id = 0)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $value = get_user_meta($user_id, $key, true);

        if ($value === '') {
            $instance = self::instance();
            $value = isset($instance->defaults[$key]) ? $instance->defaults[$key] : $default;
        }

        return $value;
    }

    /**
     * Update setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param int $user_id User ID (0 for current user)
     * @return bool
     */
    public static function update_setting($key, $value, $user_id = 0)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        return update_user_meta($user_id, $key, $value);
    }

    /**
     * Get all user settings
     *
     * @param int $user_id User ID (0 for current user)
     * @return array
     */
    public static function get_all_settings($user_id = 0)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $instance = self::instance();
        $settings = array();

        foreach ($instance->defaults as $key => $default) {
            $settings[$key] = self::get_setting($key, $default, $user_id);
        }

        return $settings;
    }

    /**
     * Get available sounds
     *
     * @return array
     */
    public static function get_available_sounds()
    {
        return array(
            'ting' => __('Ting! (Default)', 'lp-lesson-completion-sound'),
            'success-chime' => __('Success Chime', 'lp-lesson-completion-sound'),
            'magic-sparkle' => __('Magic Sparkle', 'lp-lesson-completion-sound'),
            'pop' => __('Pop!', 'lp-lesson-completion-sound'),
        );
    }

    /**
     * Get instance
     *
     * @return LP_LCS_Settings
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
