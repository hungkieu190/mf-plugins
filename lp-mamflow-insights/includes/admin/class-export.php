<?php
/**
 * Export handler class for LearnPress Mamflow Insight
 * 
 * @package MF_Insights
 */

defined('ABSPATH') || exit;

class MF_Insights_Export
{
    /**
     * Database handler
     * 
     * @var MF_Insights_Database
     */
    private $db;

    /**
     * Constructor
     * 
     * @param MF_Insights_Database $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->hooks();
    }

    /**
     * Register hooks
     */
    private function hooks()
    {
        add_action('wp_ajax_mf_insights_export_csv', [$this, 'ajax_export_csv']);
    }

    /**
     * AJAX: Export CSV
     */
    public function ajax_export_csv()
    {
        check_ajax_referer('mf_insights_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'lp-mamflow-insights')]);
        }

        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $export_type = isset($_POST['export_type']) ? sanitize_text_field($_POST['export_type']) : '';

        if (!$course_id || !$export_type) {
            wp_send_json_error(['message' => __('Invalid parameters', 'lp-mamflow-insights')]);
        }

        switch ($export_type) {
            case 'course':
                $data = $this->get_course_export_data($course_id);
                $filename = 'course-health-' . $course_id . '-' . date('Y-m-d');
                break;
            case 'lessons':
                $data = $this->get_lessons_export_data($course_id);
                $filename = 'lessons-analytics-' . $course_id . '-' . date('Y-m-d');
                break;
            case 'students':
                $data = $this->get_students_export_data($course_id);
                $filename = 'students-analytics-' . $course_id . '-' . date('Y-m-d');
                break;
            default:
                wp_send_json_error(['message' => __('Invalid export type', 'lp-mamflow-insights')]);
                return;
        }

        wp_send_json_success([
            'filename' => $filename . '.csv',
            'content' => $this->array_to_csv($data),
        ]);
    }

    /**
     * Convert array to CSV string
     * 
     * @param array $data
     * @return string
     */
    private function array_to_csv($data)
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Headers
        fputcsv($output, array_keys($data[0]));

        // Data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Get course export data
     * 
     * @param int $course_id
     * @return array
     */
    private function get_course_export_data($course_id)
    {
        $course = get_post($course_id);
        if (!$course) {
            return [];
        }

        return [
            [
                'Course ID' => $course_id,
                'Course Title' => $course->post_title,
                'Enrolled Students' => $this->db->get_enrolled_count($course_id),
                'Completion Rate (%)' => round($this->db->get_course_completion_rate($course_id), 2),
                'Average Progress (%)' => round($this->db->get_avg_progress($course_id), 2),
                'Drop-off Rate (%)' => round($this->db->get_dropoff_rate($course_id), 2),
                'Quiz Pass Rate (%)' => round($this->db->get_course_quiz_pass_rate($course_id), 2),
                'Export Date' => current_time('mysql'),
            ],
        ];
    }

    /**
     * Get lessons export data
     * 
     * @param int $course_id
     * @return array
     */
    private function get_lessons_export_data($course_id)
    {
        $lessons = $this->db->get_lessons_analytics($course_id);
        $data = [];

        foreach ($lessons as $lesson) {
            $data[] = [
                'Lesson ID' => $lesson->lesson_id,
                'Lesson Title' => $lesson->lesson_title,
                'Completion Rate (%)' => round($lesson->completion_rate, 2),
                'Completed Count' => $lesson->completed_count,
                'Enrolled Count' => $lesson->enrolled_count,
                'Avg Time (minutes)' => round($lesson->avg_time / 60, 2),
                'Drop-off Level' => $lesson->dropoff_level,
            ];
        }

        return $data;
    }

    /**
     * Get students export data
     * 
     * @param int $course_id
     * @return array
     */
    private function get_students_export_data($course_id)
    {
        $students = $this->db->get_students_analytics($course_id);
        $data = [];

        foreach ($students as $student) {
            $user = get_userdata($student->user_id);
            $data[] = [
                'Student ID' => $student->user_id,
                'Student Name' => $user ? $user->display_name : 'Unknown',
                'Email' => $user ? $user->user_email : '',
                'Status' => ucfirst($student->status),
                'Progress (%)' => round($student->progress, 2),
                'Lessons Completed' => $student->lessons_completed,
                'Last Activity' => $student->last_activity ? $student->last_activity : 'N/A',
                'Risk Level' => $student->risk_level,
            ];
        }

        return $data;
    }

    /**
     * Render export buttons
     * 
     * @param int $course_id
     * @param string $export_type
     */
    public function render_export_button($course_id, $export_type)
    {
        $labels = [
            'course' => __('Export Course Data', 'lp-mamflow-insights'),
            'lessons' => __('Export Lessons Data', 'lp-mamflow-insights'),
            'students' => __('Export Students Data', 'lp-mamflow-insights'),
        ];

        $label = isset($labels[$export_type]) ? $labels[$export_type] : __('Export CSV', 'lp-mamflow-insights');
        ?>
        <button type="button" class="button mf-export-btn" data-course-id="<?php echo esc_attr($course_id); ?>"
            data-export-type="<?php echo esc_attr($export_type); ?>">
            <span class="dashicons dashicons-download"></span>
            <?php echo esc_html($label); ?>
        </button>
        <?php
    }
}
