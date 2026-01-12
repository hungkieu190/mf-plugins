<?php
/**
 * Lesson-Level Analytics class
 * 
 * @package MF_Insights
 */

defined('ABSPATH') || exit;

class MF_Insights_Lesson_Analytics
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
                <?php _e('Lesson-Level Analytics', 'lp-mamflow-insights'); ?>
            </h1>
            <hr class="wp-header-end">

            <div class="mf-insights-filters">
                <form method="get">
                    <input type="hidden" name="page" value="mf-insights-lessons">
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
                $lessons = $this->db->get_lessons_analytics($course_id);
                ?>
                <div class="mf-insights-table-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>
                                    <?php _e('Order', 'lp-mamflow-insights'); ?>
                                </th>
                                <th>
                                    <?php _e('Lesson Title', 'lp-mamflow-insights'); ?>
                                </th>
                                <th>
                                    <?php _e('Completed Students', 'lp-mamflow-insights'); ?>
                                </th>
                                <th>
                                    <?php _e('Completion Rate', 'lp-mamflow-insights'); ?>
                                </th>
                                <th>
                                    <?php _e('Status', 'lp-mamflow-insights'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lessons)): ?>
                                <?php foreach ($lessons as $index => $lesson):
                                    $prev_completion = isset($lessons[$index - 1]) ? $lessons[$index - 1]->completion_rate : 100;
                                    $dropoff = $prev_completion - $lesson->completion_rate;
                                    $status_color = $this->get_dropoff_color($dropoff);
                                    ?>
                                    <tr>
                                        <td>
                                            <?php echo esc_html($lesson->item_order); ?>
                                        </td>
                                        <td><strong>
                                                <?php echo esc_html($lesson->post_title); ?>
                                            </strong></td>
                                        <td>
                                            <?php echo esc_html($lesson->completed_count); ?>
                                        </td>
                                        <td>
                                            <div class="mf-progress-bar-container">
                                                <div class="mf-progress-bar"
                                                    style="width: <?php echo $lesson->completion_rate; ?>%; background-color: #3182ce;">
                                                </div>
                                                <span>
                                                    <?php echo $lesson->completion_rate; ?>%
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($dropoff > 15): ?>
                                                <span class="mf-badge badge-danger">
                                                    <?php _e('High Drop-off', 'lp-mamflow-insights'); ?>
                                                </span>
                                            <?php elseif ($dropoff > 5): ?>
                                                <span class="mf-badge badge-warning">
                                                    <?php _e('Moderate Drop-off', 'lp-mamflow-insights'); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="mf-badge badge-success">
                                                    <?php _e('Stable', 'lp-mamflow-insights'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">
                                        <?php _e('No lessons found for this course.', 'lp-mamflow-insights'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mf-insights-charts-row" style="margin-top: 30px;">
                    <div class="mf-insights-chart-container">
                        <h3>
                            <?php _e('Lesson Completion Funnel', 'lp-mamflow-insights'); ?>
                        </h3>
                        <canvas id="lessonFunnelChart"></canvas>
                    </div>
                </div>

                <script>
                    window.mf_lessons_data = {
                        labels: <?php echo json_encode(array_column($lessons, 'post_title')); ?>,
                            rates: <?php echo json_encode(array_column($lessons, 'completion_rate')); ?>
                                };
                </script>

            <?php else: ?>
                <div class="notice notice-info">
                    <p>
                        <?php _e('Please select a course to view lesson analytics.', 'lp-mamflow-insights'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <style>
            .mf-progress-bar-container {
                background: #edf2f7;
                border-radius: 4px;
                height: 20px;
                position: relative;
                overflow: hidden;
                width: 100%;
            }

            .mf-progress-bar {
                height: 100%;
                transition: width 0.3s ease;
            }

            .mf-progress-bar-container span {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                text-align: center;
                font-size: 11px;
                line-height: 20px;
                color: #2d3748;
                font-weight: 600;
            }

            .mf-badge {
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
            }

            .badge-danger {
                background: #fed7d7;
                color: #c53030;
            }

            .badge-warning {
                background: #feebc8;
                color: #c05621;
            }

            .badge-success {
                background: #c6f6d5;
                color: #2f855a;
            }
        </style>
        <?php
    }

    private function get_dropoff_color($dropoff)
    {
        if ($dropoff > 15)
            return '#e53e3e';
        if ($dropoff > 5)
            return '#dd6b20';
        return '#38a169';
    }
}
