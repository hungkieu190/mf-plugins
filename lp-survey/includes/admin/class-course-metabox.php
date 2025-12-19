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
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        add_action('save_post_lp_course', array($this, 'save_metabox'));
    }

    /**
     * Add metabox
     */
    public function add_metabox()
    {
        add_meta_box(
            'lp_survey_course_settings',
            __('Survey Settings', 'lp-survey'),
            array($this, 'render_metabox'),
            'lp_course',
            'normal',
            'default'
        );
    }

    public function render_metabox($post)
    {
        $enabled = get_post_meta($post->ID, '_lp_survey_enabled', true);

        wp_nonce_field('lp_survey_course_metabox', 'lp_survey_course_metabox_nonce');
        ?>
        <div class="lp-survey-course-settings">
            <p>
                <label>
                    <input type="checkbox" name="lp_survey_enabled" value="1" <?php checked($enabled, '1'); ?>>
                    <?php _e('Enable survey popup', 'lp-survey'); ?>
                </label>
            </p>
            <p class="description">
                <?php _e('Show survey after completing this course (once per user)', 'lp-survey'); ?>
            </p>
        </div>
        <?php
    }

    public function save_metabox($post_id)
    {
        // Check nonce
        if (!isset($_POST['lp_survey_course_metabox_nonce']) || !wp_verify_nonce($_POST['lp_survey_course_metabox_nonce'], 'lp_survey_course_metabox')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save setting
        $enabled = isset($_POST['lp_survey_enabled']) ? '1' : '0';
        update_post_meta($post_id, '_lp_survey_enabled', $enabled);
    }
}
