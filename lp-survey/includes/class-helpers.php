<?php
/**
 * Helper Functions
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_Helpers Class
 */
class LP_Survey_Helpers
{

    /**
     * Check if current user can manage surveys
     *
     * @return bool
     */
    public static function can_manage_surveys()
    {
        return current_user_can('manage_options');
    }

    /**
     * Check if current user can view survey results
     *
     * @param int $course_id Course ID (optional)
     * @return bool
     */
    public static function can_view_survey_results($course_id = 0)
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        // Check if user is instructor of the course
        if ($course_id > 0 && class_exists('LP_Course')) {
            $course = learn_press_get_course($course_id);
            if ($course && $course->get_author_id() == get_current_user_id()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if lesson survey is enabled
     *
     * @return bool
     */
    public static function is_lesson_survey_enabled()
    {
        return get_option('mf_lp_survey_enable_lesson_survey', 'yes') === 'yes';
    }

    /**
     * Check if course survey is enabled
     *
     * @return bool
     */
    public static function is_course_survey_enabled()
    {
        return get_option('mf_lp_survey_enable_course_survey', 'yes') === 'yes';
    }

    /**
     * Get display type
     *
     * @return string popup|inline
     */
    public static function get_display_type()
    {
        return get_option('mf_lp_survey_display_type', 'popup');
    }

    /**
     * Check if survey can be skipped
     *
     * @return bool
     */
    public static function can_skip_survey()
    {
        return get_option('mf_lp_survey_allow_skip', 'yes') === 'yes';
    }

    /**
     * Sanitize survey answer
     *
     * @param string $answer Answer content
     * @param string $type Question type
     * @return string
     */
    public static function sanitize_answer($answer, $type)
    {
        if ($type === 'rating') {
            $answer = absint($answer);
            // Ensure rating is between 1-5
            $answer = max(1, min(5, $answer));
        } else {
            $answer = sanitize_textarea_field($answer);
        }

        return $answer;
    }

    /**
     * Format date for display
     *
     * @param string $date Date string
     * @return string
     */
    public static function format_date($date)
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($date));
    }

    /**
     * Get course ID from lesson ID
     *
     * @param int $lesson_id Lesson ID
     * @return int Course ID
     */
    public static function get_course_id_by_lesson($lesson_id)
    {
        if (!class_exists('LP_Lesson')) {
            return 0;
        }

        $course_id = get_post_meta($lesson_id, '_lp_course', true);
        return absint($course_id);
    }

    /**
     * Get lesson title
     *
     * @param int $lesson_id Lesson ID
     * @return string
     */
    public static function get_lesson_title($lesson_id)
    {
        return get_the_title($lesson_id);
    }

    /**
     * Get course title
     *
     * @param int $course_id Course ID
     * @return string
     */
    public static function get_course_title($course_id)
    {
        return get_the_title($course_id);
    }

    /**
     * Calculate response rate
     *
     * @param int $responses Number of responses
     * @param int $total_users Total users who completed
     * @return float Response rate percentage
     */
    public static function calculate_response_rate($responses, $total_users)
    {
        if ($total_users == 0) {
            return 0;
        }

        return round(($responses / $total_users) * 100, 2);
    }
}
