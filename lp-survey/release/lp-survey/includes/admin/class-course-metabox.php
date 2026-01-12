<?php
/**
 * Course Metabox - Per-course settings
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_Course_Metabox Class
 */
class LP_Survey_Course_Metabox
{

    /**
     * The single instance of the class
     *
     * @var LP_Survey_Course_Metabox
     */
    protected static $_instance = null;

    /**
     * Main instance
     *
     * @return LP_Survey_Course_Metabox
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
        add_filter('learnpress/course/metabox/tabs', array($this, 'register_tab'), 20, 2);
        add_action('learnpress_save_lp_course_metabox', array($this, 'save_metabox'), 20, 2);
    }

    /**
     * Register Survey tab in Course Settings
     *
     * @param array $tabs
     * @param int $post_id
     * @return array
     */
    public function register_tab($tabs, $post_id)
    {
        $tabs['survey'] = array(
            'label' => esc_html__('Survey', 'lp-survey'),
            'target' => 'survey_course_data',
            'icon' => 'dashicons-clipboard',
            'priority' => 100,
            'content' => array(
                '_lp_survey_enabled' => new LP_Meta_Box_Checkbox_Field(
                    esc_html__('Enable survey popup', 'lp-survey'),
                    esc_html__('Show survey after completing this course (once per user)', 'lp-survey'),
                    '0'
                ),
            ),
        );

        return $tabs;
    }

    /**
     * Save Survey settings
     *
     * @param int $post_id
     * @param WP_Post $post
     */
    public function save_metabox($post_id, $post)
    {
        if ($post->post_type !== 'lp_course') {
            return;
        }

        $enabled = isset($_POST['_lp_survey_enabled']) ? '1' : '0';
        update_post_meta($post_id, '_lp_survey_enabled', $enabled);
    }
}
