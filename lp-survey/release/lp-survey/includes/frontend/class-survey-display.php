<?php
/**
 * Survey Display Class
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_Display Class
 */
class LP_Survey_Display
{

    /**
     * The single instance of the class
     *
     * @var LP_Survey_Display
     */
    protected static $_instance = null;

    /**
     * Main instance
     *
     * @return LP_Survey_Display
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
        // Add survey container to footer
        add_action('wp_footer', array($this, 'render_survey_container'));

        // Add course completion survey trigger
        add_action('learn-press/after-course-finish-button', array($this, 'add_course_survey_trigger'));
    }

    /**
     * Render survey popup container
     */
    public function render_survey_container()
    {
        if (!is_user_logged_in()) {
            return;
        }

        include LP_SURVEY_PATH . 'templates/frontend/survey-popup.php';
    }

    /**
     * Add course survey trigger
     */
    public function add_course_survey_trigger()
    {
        if (!LP_Survey_Helpers::is_course_survey_enabled()) {
            return;
        }

        $course = LP_Global::course();
        if (!$course) {
            return;
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }

        // Check if user has finished the course
        $user = learn_press_get_current_user();
        $course_data = $user->get_course_data($course->get_id());

        if (!$course_data || $course_data->get_status() !== 'finished') {
            return;
        }

        // Check for pending survey
        $pending = get_user_meta($user_id, '_lp_survey_pending_course_' . $course->get_id(), true);

        if ($pending && is_array($pending)) {
            // Check if not already answered
            if (!LP_Survey_Database::has_user_answered($pending['survey_id'], $user_id)) {
                // Use localStorage to persist across redirects
                ?>
                <script type="text/javascript">
                    (function () {
                        if (typeof (Storage) !== "undefined") {
                            localStorage.setItem('lp_survey_trigger', JSON.stringify({
                                survey_id: <?php echo absint($pending['survey_id']); ?>,
                                type: 'course',
                                ref_id: <?php echo absint($pending['ref_id']); ?>,
                                timestamp: Date.now()
                            }));
                        }
                    })();
                </script>
                <?php
            }

            // Clear the pending marker
            delete_user_meta($user_id, '_lp_survey_pending_course_' . $course->get_id());
        }
    }
}
