<?php
/**
 * Student Behavior Analytics class
 * 
 * @package MF_Insights
 */

defined('ABSPATH') || exit;

class MF_Insights_Student_Analytics
{
    /**
     * @var MF_Insights_Database
     */
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function render()
    {
        $instructor_id = get_current_user_id();
        $courses = $this->db->get_instructor_courses($instructor_id);
        $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : (isset($courses[0]) ? $courses[0]->ID : 0);

        ?>
        <div class="wrap mf-insights-dashboard">
            <h1 class="wp-heading-inline">
                <?php _e('Student Behavior Analytics', 'lp-mamflow-insights'); ?>
            </h1>
            <hr class="wp-header-end">

            <div class="mf-insights-filters">
                <form method="get">
                    <input type="hidden" name="page" value="mf-insights-students">
                    <select name="course_id" onchange="this.form.submit()">
                        <option value="0">
                            <?php _e('Select a Course', 'lp-mamflow-insights'); ?>
                        </option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course->ID; ?>" <?php selected($course_id, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if ($course_id):
                $students = $this->db->get_students_analytics($course_id);
                $at_risk_count = count(array_filter($students, function ($s) {
                    return $s->is_at_risk; }));
                ?>
                <div class="mf-insights-metrics-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 20px;">
                    <div class="mf-insights-card">
                        <div class="mf-insights-card-icon"><span class="dashicons dashicons-groups"></span></div>
                        <div class="mf-insights-card-content">
                            <p class="mf-insights-card-label">
                                <?php _e('Total Students', 'lp-mamflow-insights'); ?>
                            </p>
                            <h2 class="mf-insights-card-value">
                                <?php echo count($students); ?>
                            </h2>
                        </div>
                    </div>
                    <div class="mf-insights-card">
                        <div class="mf-insights-card-icon"><span class="dashicons dashicons-warning" style="color: #e53e3e;"></span>
                        </div>
                        <div class="mf-insights-card-content">
                            <p class="mf-insights-card-label">
                                <?php _e('Students At Risk', 'lp-mamflow-insights'); ?>
                            </p>
                            <h2 class="mf-insights-card-value" style="color: #e53e3e;">
                                <?php echo $at_risk_count; ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="mf-insights-table-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>
                                    <?php _e('Student', 'lp-mamflow-insights'); ?>
                                </th>
                                <th>
                                    <?php _e('Progress', 'lp-mamflow-insights'); ?>
                                </th>
                                <th>
                                    <?php _e('Inactive Days', 'lp-mamflow-insights'); ?>
                                </th>
                                <th>
                                    <?php _e('Status', 'lp-mamflow-insights'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?php echo esc_html($student->display_name); ?>
                                            </strong><br>
                                            <small>
                                                <?php echo esc_html($student->user_email); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="mf-progress-bar-container">
                                                <div class="mf-progress-bar"
                                                    style="width: <?php echo $student->progress; ?>%; background-color: #38a169;"></div>
                                                <span>
                                                    <?php echo $student->progress; ?>%
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo $student->inactive_days; ?>
                                            <?php _e('days', 'lp-mamflow-insights'); ?>
                                        </td>
                                        <td>
                                            <?php if ($student->status === 'completed'): ?>
                                                <span class="mf-badge badge-success">
                                                    <?php _e('Completed', 'lp-mamflow-insights'); ?>
                                                </span>
                                            <?php elseif ($student->is_at_risk): ?>
                                                <span class="mf-badge badge-danger">
                                                    <?php _e('At Risk', 'lp-mamflow-insights'); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="mf-badge badge-warning">
                                                    <?php _e('Active', 'lp-mamflow-insights'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">
                                        <?php _e('No students found for this course.', 'lp-mamflow-insights'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="notice notice-info">
                    <p>
                        <?php _e('Please select a course to view student analytics.', 'lp-mamflow-insights'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
