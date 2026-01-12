<?php
/**
 * Settings page for LP Survey - integrated into LearnPress Settings
 *
 * @package LP_Survey
 */

defined('ABSPATH') || exit();

/**
 * Check if LearnPress Settings API is available
 */
if (!class_exists('LP_Abstract_Settings_Page')) {
    class LP_Survey_Settings
    {
        public static function instance()
        {
            return new self();
        }
        public static function get_setting($key, $default = '')
        {
            return $default;
        }
    }
    return;
}

/**
 * Class LP_Survey_Settings
 * Extends LP_Abstract_Settings_Page to integrate with LearnPress Settings
 */
class LP_Survey_Settings extends LP_Abstract_Settings_Page
{
    /**
     * Instance
     *
     * @var LP_Survey_Settings
     */
    protected static $instance = null;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->id = 'survey';
        $this->text = esc_html__('Survey', 'lp-survey');

        add_filter('learn-press/admin/settings-tabs-array', array($this, 'register_tab'));

        parent::__construct();
    }

    /**
     * Register tab
     *
     * @param array $tabs
     * @return array
     */
    public function register_tab($tabs)
    {
        $tabs[$this->id] = $this;
        return $tabs;
    }

    /**
     * Get settings for section
     *
     * @param string $section
     * @param string $tab
     * @return array
     */
    public function get_settings($section = '', $tab = '')
    {
        return apply_filters(
            'lp_survey_settings',
            array(
                array(
                    'type' => 'title',
                    'title' => __('Survey Settings', 'lp-survey'),
                    'desc' => __('Configure survey display options for lessons and courses.', 'lp-survey'),
                ),

                array(
                    'title' => __('Display Type', 'lp-survey'),
                    'id' => 'mf_lp_survey_display_type',
                    'default' => 'popup',
                    'type' => 'select',
                    'options' => array(
                        'popup' => __('Popup', 'lp-survey'),
                        'inline' => __('Inline', 'lp-survey'),
                    ),
                    'desc' => __('How the survey should be displayed to users.', 'lp-survey'),
                ),
                array(
                    'title' => __('Allow Skip', 'lp-survey'),
                    'id' => 'mf_lp_survey_allow_skip',
                    'default' => 'yes',
                    'type' => 'checkbox',
                    'desc' => __('Allow students to skip the survey without answering.', 'lp-survey'),
                ),
                array(
                    'title' => __('Maximum Questions', 'lp-survey'),
                    'id' => 'mf_lp_survey_max_questions',
                    'default' => '5',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 10,
                    'desc' => __('Maximum number of questions to show in a survey (1-10).', 'lp-survey'),
                ),
                array(
                    'type' => 'sectionend',
                ),
            )
        );
    }

    /**
     * Get setting value
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function get_setting($key, $default = '')
    {
        return LP_Settings::get_option($key, $default);
    }

    /**
     * Get instance
     *
     * @return LP_Survey_Settings
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
