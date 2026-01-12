<?php
/**
 * Lesson Metabox Class
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_Lesson_Metabox Class
 */
class LP_Survey_Lesson_Metabox
{
    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        add_filter('lp/metabox/lesson/lists', array($this, 'add_settings_field'), 20);
    }

    /**
     * Add survey settings to Lesson Settings metabox
     *
     * @param array $fields
     * @return array
     */
    public function add_settings_field($fields)
    {
        $fields['_lp_survey_enabled'] = new LP_Meta_Box_Checkbox_Field(
            esc_html__('Enable survey popup', 'lp-survey'),
            esc_html__('Show survey after completing this lesson (once per user)', 'lp-survey'),
            '0'
        );

        return $fields;
    }
}
