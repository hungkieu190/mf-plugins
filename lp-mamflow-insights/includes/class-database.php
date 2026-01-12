<?php
/**
 * Database handler class for LearnPress Mamflow Insight
 * 
 * @package MF_Insights
 */

defined('ABSPATH') || exit;

class MF_Insights_Database
{
    /**
     * @var wpdb
     */
    protected $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Get LearnPress table name
     */
    public function get_lp_table($table)
    {
        return $this->wpdb->prefix . 'learnpress_' . $table;
    }

    /**
     * Get total enrolled students for a course
     */
    public function get_enrolled_count($course_id)
    {
        return (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM " . $this->get_lp_table('user_items') . " 
            WHERE item_id = %d AND item_type = %s",
            $course_id,
            'lp_course'
        ));
    }

    /**
     * Get total completed students for a course
     */
    public function get_completed_count($course_id)
    {
        return (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM " . $this->get_lp_table('user_items') . " 
            WHERE item_id = %d AND item_type = %s AND status = %s",
            $course_id,
            'lp_course',
            'completed'
        ));
    }

    /**
     * Get Course Completion Rate
     */
    public function get_course_completion_rate($course_id)
    {
        $enrolled = $this->get_enrolled_count($course_id);
        if ($enrolled === 0)
            return 0;

        $completed = $this->get_completed_count($course_id);
        return round(($completed / $enrolled) * 100, 2);
    }

    /**
     * Get average progress for a course
     */
    public function get_avg_progress($course_id)
    {
        $enrolled = $this->get_enrolled_count($course_id);
        if ($enrolled === 0)
            return 0;

        $results = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT AVG(CAST(results AS JSON)->>'$.graduation_score') 
            FROM " . $this->get_lp_table('user_items') . " 
            WHERE item_id = %d AND item_type = %s",
            $course_id,
            'lp_course'
        ));

        if ($results === null) {
            $table_results = $this->wpdb->prefix . 'learnpress_user_item_results';
            if ($this->wpdb->get_var("SHOW TABLES LIKE '{$table_results}'")) {
                $results = $this->wpdb->get_var($this->wpdb->prepare(
                    "SELECT AVG(result) FROM {$table_results} r
                    JOIN " . $this->get_lp_table('user_items') . " ui ON r.user_item_id = ui.user_item_id
                    WHERE ui.item_id = %d AND ui.item_type = %s",
                    $course_id,
                    'lp_course'
                ));
            }
        }

        return $results ? round((float) $results, 2) : 0;
    }

    /**
     * Get Drop-off Rate (Inactive > 30 days)
     */
    public function get_dropoff_rate($course_id, $days = 30)
    {
        $enrolled = $this->get_enrolled_count($course_id);
        if ($enrolled === 0)
            return 0;

        $inactive_date = date('Y-m-d H:i:s', current_time('timestamp') - ($days * DAY_IN_SECONDS));

        $inactive_count = (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM " . $this->get_lp_table('user_items') . " 
            WHERE item_id = %d AND item_type = %s 
            AND status = %s 
            AND start_time < %s 
            AND (end_time IS NULL OR end_time = '0000-00-00 00:00:00')",
            $course_id,
            'lp_course',
            'enrolled',
            $inactive_date
        ));

        return round(($inactive_count / $enrolled) * 100, 2);
    }

    /**
     * Get Quiz Pass Rate for all quizzes in a course
     */
    public function get_course_quiz_pass_rate($course_id)
    {
        $quizzes = $this->wpdb->get_col($this->wpdb->prepare(
            "SELECT item_id FROM " . $this->wpdb->prefix . "learnpress_section_items si
            JOIN " . $this->wpdb->prefix . "learnpress_sections s ON si.section_id = s.section_id
            WHERE s.section_course_id = %d AND si.item_type = %s",
            $course_id,
            'lp_quiz'
        ));

        if (empty($quizzes))
            return 0;

        $quiz_ids = implode(',', array_map('intval', $quizzes));

        $total_attempts = (int) $this->wpdb->get_var(
            "SELECT COUNT(*) FROM " . $this->get_lp_table('user_items') . " 
            WHERE item_id IN ($quiz_ids) AND item_type = 'lp_quiz'"
        );

        if ($total_attempts === 0)
            return 0;

        $passed_attempts = (int) $this->wpdb->get_var(
            "SELECT COUNT(*) FROM " . $this->get_lp_table('user_items') . " 
            WHERE item_id IN ($quiz_ids) AND item_type = 'lp_quiz' AND status = 'completed' AND results LIKE '%\"graduation\":\"passed\"%'"
        );

        return round(($passed_attempts / $total_attempts) * 100, 2);
    }

    /**
     * Get all courses for instructor
     */
    public function get_instructor_courses($instructor_id)
    {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ID, post_title FROM {$this->wpdb->posts} 
            WHERE post_author = %d AND post_type = %s AND post_status = %s",
            $instructor_id,
            'lp_course',
            'publish'
        ));
    }

    /**
     * Get lessons with completion data for a course
     */
    public function get_lessons_analytics($course_id)
    {
        $lessons = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT p.ID, p.post_title, si.item_order 
            FROM {$this->wpdb->posts} p
            JOIN " . $this->wpdb->prefix . "learnpress_section_items si ON p.ID = si.item_id
            JOIN " . $this->wpdb->prefix . "learnpress_sections s ON si.section_id = s.section_id
            WHERE s.section_course_id = %d AND si.item_type = %s
            ORDER BY s.section_order ASC, si.item_order ASC",
            $course_id,
            'lp_lesson'
        ));

        if (empty($lessons))
            return [];

        $enrolled_students = $this->get_enrolled_count($course_id);

        foreach ($lessons as &$lesson) {
            $completed_count = (int) $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM " . $this->get_lp_table('user_items') . " 
                WHERE item_id = %d AND item_type = %s AND status = %s",
                $lesson->ID,
                'lp_lesson',
                'completed'
            ));

            $lesson->completed_count = $completed_count;
            $lesson->completion_rate = $enrolled_students > 0 ? round(($completed_count / $enrolled_students) * 100, 2) : 0;

            // Basic drop-off is (Previous Lesson Completion - Current Lesson Completion)
            // But for now let's just show completion rate.
        }

    }

    /**
     * Get students list with progress and risk status
     */
    public function get_students_analytics($course_id)
    {
        $students = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT ui.user_id, u.display_name, u.user_email, ui.start_time, ui.end_time, ui.status, ui.results
            FROM " . $this->get_lp_table('user_items') . " ui
            JOIN {$this->wpdb->users} u ON ui.user_id = u.ID
            WHERE ui.item_id = %d AND ui.item_type = %s
            ORDER BY ui.start_time DESC",
            $course_id,
            'lp_course'
        ));

        foreach ($students as &$student) {
            // Parse progress from results JSON
            $results = json_decode($student->results, true);
            $student->progress = isset($results['graduation_score']) ? (float) $results['graduation_score'] : 0;

            // Risk calculation: Inactive > 14 days OR progress < 10% after 7 days
            $last_active = $student->start_time; // Fallback
            $inactive_days = round((current_time('timestamp') - strtotime($student->start_time)) / DAY_IN_SECONDS);

            $student->is_at_risk = ($inactive_days > 14 && $student->status !== 'completed');
            $student->inactive_days = $inactive_days;
        }

        return $students;
    }
}
