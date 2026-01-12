<?php
/**
 * LearnPress Hooks Integration
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_LearnPress_Hooks Class
 */
class LP_Survey_LearnPress_Hooks
{

    /**
     * The single instance of the class
     *
     * @var LP_Survey_LearnPress_Hooks
     */
    protected static $_instance = null;

    /**
     * Main instance
     *
     * @return LP_Survey_LearnPress_Hooks
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
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Intercept LearnPress completion AJAX responses (FILTERS)
        add_filter('lp/lesson/complete/result', array($this, 'on_lesson_ajax_complete'), 10, 3);
        add_filter('lp/course/finish/result', array($this, 'on_course_ajax_complete'), 10, 3);
        add_filter('learn_press_update_user_item_result', array($this, 'on_user_item_update'), 10, 4);

        // Try ACTIONS instead of filters (LearnPress might use do_action)
        add_action('learn-press/user-completed-lesson', array($this, 'on_action_lesson_complete'), 10, 3);
        add_action('learn-press/user-completed-course', array($this, 'on_action_course_complete'), 10, 2);
        add_action('learnpress/user/completed-lesson', array($this, 'on_action_lesson_complete'), 10, 3);
        add_action('learnpress/user/completed-course', array($this, 'on_action_course_complete'), 10, 2);
        add_action('learn_press_user_completed_lesson', array($this, 'on_action_lesson_complete'), 10, 3);
        add_action('learn_press_user_finish_course', array($this, 'on_action_course_complete'), 10, 2);

        // LP4+ hooks
        add_action('learnpress_update_user_item_field', array($this, 'on_update_user_item_field'), 10, 4);
        add_action('lp_user_completed_lesson', array($this, 'on_action_lesson_complete'), 10, 3);

        // Additional course hooks identified from research
        add_action('learn-press/user-course-finished', array($this, 'on_action_course_complete'), 50, 2);
        add_action('learn-press/user-course/finished', array($this, 'on_action_course_object_finished'), 50, 1);
    }

    /**
     * Action handler for course object finished
     */
    public function on_action_course_object_finished($user_course_model)
    {
        if (!$user_course_model || !method_exists($user_course_model, 'get_course_id')) {
            return;
        }

        $course_id = (int) $user_course_model->get_course_id();
        $user_id = (int) $user_course_model->get_user_id();

        if ($course_id && $user_id) {
            $this->on_course_ajax_complete(array(), $course_id, $user_id);
        }
    }

    /**
     * Action hook handler for lesson completion
     */
    public function on_action_lesson_complete($lesson_id, $user_id = null, $course_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        // Call main handler (with empty result since this is an action)
        $this->on_lesson_ajax_complete(array(), $lesson_id, $course_id);
    }

    /**
     * Action hook handler for course completion
     */
    public function on_action_course_complete($course_id, $user_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        // Call main handler
        $this->on_course_ajax_complete(array(), $course_id, $user_id);
    }

    /**
     * LP4+ user item field update hook
     */
    public function on_update_user_item_field($item_id, $field, $value, $user_id)
    {
        // Only trigger for status field change to 'completed'
        if ($field !== 'status' || $value !== 'completed') {
            return;
        }

        // Get item type and ref_id
        $item_type = get_post_type($item_id);

        if ($item_type === 'lp_lesson') {
            // Get course ID
            $course_id = get_post_meta($item_id, '_lp_course', true);
            $this->on_lesson_ajax_complete(array(), $item_id, $course_id);
        } elseif ($item_type === 'lp_course') {
            $this->on_course_ajax_complete(array(), $item_id, $user_id);
        }
    }

    /**
     * Handle lesson AJAX completion
     *
     * @param array $result AJAX result
     * @param int $lesson_id Lesson ID
     * @param int $course_id Course ID
     * @return array Modified result
     */
    public function on_lesson_ajax_complete($result, $lesson_id, $course_id)
    {
        // Check if THIS specific lesson has survey enabled
        $lesson_survey_enabled = get_post_meta($lesson_id, '_lp_survey_enabled', true);

        if ($lesson_survey_enabled !== '1') {
            return $result;
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            return $result;
        }

        // Get survey
        $survey = LP_Survey_Database::get_survey('lesson', $lesson_id);
        if (!$survey) {
            $survey = LP_Survey_Database::get_survey('lesson', 0); // Default
        }

        if (!$survey) {
            return $result;
        }

        // Check not already answered (Option 2: Only once per user)
        if (LP_Survey_Database::has_user_answered($survey->id, $user_id)) {
            return $result;
        }

        // Set transient (30 seconds expiry)
        set_transient(
            'lp_survey_show_for_user_' . $user_id,
            array(
                'survey_id' => $survey->id,
                'type' => 'lesson',
                'ref_id' => $lesson_id,
            ),
            30
        );

        return $result; // Don't modify LearnPress response
    }

    /**
     * Handle course AJAX completion
     *
     * @param array $result AJAX result
     * @param int $course_id Course ID
     * @param int $user_id User ID
     * @return array Modified result
     */
    public function on_course_ajax_complete($result, $course_id, $user_id)
    {
        error_log('LP Survey DEBUG: on_course_ajax_complete - Course ID: ' . $course_id . ', User ID: ' . $user_id);

        // Check if THIS specific course has survey enabled
        $course_survey_enabled = get_post_meta($course_id, '_lp_survey_enabled', true);

        if ($course_survey_enabled !== '1') {
            return $result;
        }

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        // Get survey
        $survey = LP_Survey_Database::get_survey('course', $course_id);
        if (!$survey) {
            $survey = LP_Survey_Database::get_survey('course', 0); // Default
        }

        if (!$survey) {
            return $result;
        }

        // Check not already answered (Option 2: Only once per user)
        if (LP_Survey_Database::has_user_answered($survey->id, $user_id)) {
            return $result;
        }

        // Set transient (30 seconds expiry)
        $set = set_transient(
            'lp_survey_show_for_user_' . $user_id,
            array(
                'survey_id' => $survey->id,
                'type' => 'course',
                'ref_id' => $course_id,
            ),
            30
        );

        error_log('LP Survey DEBUG: Transient set for user ' . $user_id . ': ' . ($set ? 'SUCCESS' : 'FAILED'));

        return $result; // Don't modify LearnPress response
    }

    /**
     * Alternative hook for user item updates
     *
     * @param array $result Update result
     * @param int $item_id Item ID
     * @param int $course_id Course ID
     * @param int $user_id User ID
     * @return array Modified result
     */
    public function on_user_item_update($result, $item_id, $course_id, $user_id)
    {
        error_log('LP Survey DEBUG: ========== FALLBACK FILTER FIRED ==========');
        error_log('LP Survey DEBUG: on_user_item_update called - Item ID: ' . $item_id);
        error_log('LP Survey DEBUG: Result: ' . print_r($result, true));

        // Check if this is a completion (status = completed)
        if (isset($result['status']) && $result['status'] === 'completed') {
            error_log('LP Survey DEBUG: Status is COMPLETED');
            $item_type = get_post_type($item_id);
            error_log('LP Survey DEBUG: Item type: ' . $item_type);

            if ($item_type === 'lp_lesson') {
                error_log('LP Survey DEBUG: Calling lesson handler from fallback');
                $this->on_lesson_ajax_complete($result, $item_id, $course_id);
            } elseif ($item_type === 'lp_course') {
                error_log('LP Survey DEBUG: Calling course handler from fallback');
                $this->on_course_ajax_complete($result, $item_id, $user_id);
            }
        } else {
            error_log('LP Survey DEBUG: Status is NOT completed: ' . (isset($result['status']) ? $result['status'] : 'N/A'));
        }

        return $result;
    }
}
