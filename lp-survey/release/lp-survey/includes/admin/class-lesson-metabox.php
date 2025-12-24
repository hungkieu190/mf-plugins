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
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        add_action('save_post_lp_lesson', array($this, 'save_metabox'), 10, 2);
    }

    /**
     * Add metabox
     */
    public function add_metabox()
    {
        add_meta_box(
            'lp_survey_lesson_settings',
            __('Survey Settings', 'lp-survey'),
            array($this, 'render_metabox'),
            'lp_lesson',
            'side',
            'default'
        );
    }

    /**
     * Render metabox
     */
    public function render_metabox($post)
    {
        $enabled = get_post_meta($post->ID, '_lp_survey_enabled', true);
        
        wp_nonce_field('lp_survey_lesson_meta', 'lp_survey_lesson_nonce');
        ?>
        <div class="lp-survey-metabox">
            <p>
                <label>
                    <input type="checkbox" name="lp_survey_enabled" value="1" <?php checked($enabled, '1'); ?>>
                    <?php _e('Enable survey popup', 'lp-survey'); ?>
                </label>
            </p>
            <p class="description">
                <?php _e('Show survey after completing this lesson (once per user)', 'lp-survey'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save metabox
     */
    public function save_metabox($post_id, $post)
    {
        // Check nonce
        if (!isset($_POST['lp_survey_lesson_nonce']) || !wp_verify_nonce($_POST['lp_survey_lesson_nonce'], 'lp_survey_lesson_meta')) {
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
