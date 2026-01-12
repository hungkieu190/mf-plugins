<?php
/**
 * Course Health Dashboard class
 * 
 * @package MF_Insights
 */

defined('ABSPATH') || exit;

class MF_Insights_Course_Health
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
            <h1 class="wp-heading-inline"><?php _e('Course Health Dashboard', 'lp-mamflow-insights'); ?></h1>
            <hr class="wp-header-end">

            <div class="mf-insights-filters">
                <form method="get">
                    <input type="hidden" name="page" value="mf-insights">
                    <input type="hidden" name="tab" value="course-health">
                    <label for="mf-course-select"><?php _e('Select Course:', 'lp-mamflow-insights'); ?></label>
                    <select name="course_id" id="mf-course-select" class="mf-select2-course" style="width: 350px;">
                        <option value="0"><?php _e('-- Search or select a course --', 'lp-mamflow-insights'); ?></option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course->ID; ?>" <?php selected($course_id, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit"
                        class="button button-primary"><?php _e('View Analytics', 'lp-mamflow-insights'); ?></button>
                </form>
            </div>


            <?php if ($course_id):
                $enrolled = $this->db->get_enrolled_count($course_id);
                $completion_rate = $this->db->get_course_completion_rate($course_id);
                $avg_progress = $this->db->get_avg_progress($course_id);
                $dropoff_rate = $this->db->get_dropoff_rate($course_id);
                $pass_rate = $this->db->get_course_quiz_pass_rate($course_id);

                // Get alerts for this course
                $alerts_handler = mf_insights()->get_alerts();
                $course_alerts = $alerts_handler ? $alerts_handler->get_course_alerts($course_id) : [];
                ?>

                <?php if (!empty($course_alerts)): ?>
                    <div class="mf-insights-alerts">
                        <h3 class="mf-alerts-title">
                            <span class="dashicons dashicons-warning"></span>
                            <?php printf(
                                _n('%d Alert', '%d Alerts', count($course_alerts), 'lp-mamflow-insights'),
                                count($course_alerts)
                            ); ?>
                        </h3>
                        <div class="mf-alerts-list">
                            <?php foreach ($course_alerts as $alert): ?>
                                <div class="mf-alert-item mf-alert-<?php echo esc_attr($alert['severity']); ?>">
                                    <span
                                        class="mf-alert-icon dashicons dashicons-<?php echo $alert['severity'] === 'danger' ? 'dismiss' : 'info'; ?>"></span>
                                    <span class="mf-alert-message"><?php echo esc_html($alert['message']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="mf-insights-metrics-grid">
                    <?php $this->render_metric_card(__('Enrolled Students', 'lp-mamflow-insights'), $enrolled, 'dashicons-groups'); ?>
                    <?php $this->render_metric_card(__('Completion Rate', 'lp-mamflow-insights'), $completion_rate . '%', 'dashicons-yes-alt', $this->get_health_color($completion_rate, 'high_good')); ?>
                    <?php $this->render_metric_card(__('Average Progress', 'lp-mamflow-insights'), $avg_progress . '%', 'dashicons-chart-line'); ?>
                    <?php $this->render_metric_card(__('Drop-off Rate', 'lp-mamflow-insights'), $dropoff_rate . '%', 'dashicons-warning', $this->get_health_color($dropoff_rate, 'low_good')); ?>
                    <?php $this->render_metric_card(__('Quiz Pass Rate', 'lp-mamflow-insights'), $pass_rate . '%', 'dashicons-welcome-learn-more'); ?>
                </div>

                <div class="mf-insights-charts-row">
                    <div class="mf-insights-chart-container">
                        <h3><?php _e('Student Engagement Overview', 'lp-mamflow-insights'); ?></h3>
                        <canvas id="engagementChart"></canvas>
                    </div>
                </div>

                <script>         window.mf_insights_data = {             completion: <?php echo $completion_rate; ?>,             progress: <?php echo $avg_progress; ?>,             dropoff: <?php echo $dropoff_rate; ?>,             pass: <?php echo $pass_rate; ?>         };
                </script>

                <div class="mf-insights-export-actions">
                    <h3><?php _e('Export Data', 'lp-mamflow-insights'); ?></h3>
                    <div class="mf-export-buttons">
                        <button type="button" class="button mf-export-btn" data-course-id="<?php echo esc_attr($course_id); ?>"
                            data-export-type="course">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export Course Summary', 'lp-mamflow-insights'); ?>
                        </button>
                        <button type="button" class="button mf-export-btn" data-course-id="<?php echo esc_attr($course_id); ?>"
                            data-export-type="lessons">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export Lessons Data', 'lp-mamflow-insights'); ?>
                        </button>
                        <button type="button" class="button mf-export-btn" data-course-id="<?php echo esc_attr($course_id); ?>"
                            data-export-type="students">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export Students Data', 'lp-mamflow-insights'); ?>
                        </button>
                    </div>
                </div>

            <?php else: ?>
                <div class="notice notice-info">
                    <p><?php _e('Please select a course to view analytics.', 'lp-mamflow-insights'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_metric_card($label, $value, $icon, $color = '')
    {
        $style = $color ? "style='color: {$color}'" : "";
        ?>
        <div class="mf-insights-card">
            <div class="mf-insights-card-icon"><span class="dashicons <?php echo esc_attr($icon); ?>"></span></div>
            <div class="mf-insights-card-content">
                <p class="mf-insights-card-label"><?php echo esc_html($label); ?></p>
                <h2 class="mf-insights-card-value" <?php echo $style; ?>><?php echo $value; ?></h2>
            </div>
        </div>
        <?php
    }

    private function get_health_color($value, $type)
    {
        if ($type === 'high_good') {
            if ($value >= 70)
                return '#28a745';
            if ($value >= 40)
                return '#ffc107';
            return '#dc3545';
        } else {
            if ($value <= 20)
                return '#28a745';
            if ($value <= 50)
                return '#ffc107';
            return '#dc3545';
        }
    }

    /**
     * Render content only (for tabbed interface)
     */
    public function render_content()
    {
        $instructor_id = get_current_user_id();
        $courses = $this->db->get_instructor_courses($instructor_id);
        $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : (isset($courses[0]) ? $courses[0]->ID : 0);
        ?>
        <div class="mf-insights-filters">
            <form method="get">
                <input type="hidden" name="page" value="mf-insights">
                <input type="hidden" name="tab" value="course-health">
                <label for="mf-course-select"><?php _e('Select Course:', 'lp-mamflow-insights'); ?></label>
                <select name="course_id" id="mf-course-select" class="mf-select2-course" style="width: 350px;">
                    <option value="0"><?php _e('-- Search or select a course --', 'lp-mamflow-insights'); ?></option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course->ID; ?>" <?php selected($course_id, $course->ID); ?>>
                            <?php echo esc_html($course->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit"
                    class="button button-primary"><?php _e('View Analytics', 'lp-mamflow-insights'); ?></button>
            </form>
        </div>

        <?php if ($course_id):
            $enrolled = $this->db->get_enrolled_count($course_id);
            $completion_rate = $this->db->get_course_completion_rate($course_id);
            $avg_progress = $this->db->get_avg_progress($course_id);
            $dropoff_rate = $this->db->get_dropoff_rate($course_id);
            $pass_rate = $this->db->get_course_quiz_pass_rate($course_id);

            $alerts_handler = mf_insights()->get_alerts();
            $course_alerts = $alerts_handler ? $alerts_handler->get_course_alerts($course_id) : [];
            ?>

            <?php if (!empty($course_alerts)): ?>
                <div class="mf-insights-alerts">
                    <h3 class="mf-alerts-title">
                        <span class="dashicons dashicons-warning"></span>
                        <?php printf(_n('%d Alert', '%d Alerts', count($course_alerts), 'lp-mamflow-insights'), count($course_alerts)); ?>
                    </h3>
                    <div class="mf-alerts-list">
                        <?php foreach ($course_alerts as $alert): ?>
                            <div class="mf-alert-item mf-alert-<?php echo esc_attr($alert['severity']); ?>">
                                <span
                                    class="mf-alert-icon dashicons dashicons-<?php echo $alert['severity'] === 'danger' ? 'dismiss' : 'info'; ?>"></span>
                                <span class="mf-alert-message"><?php echo esc_html($alert['message']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mf-insights-metrics-grid">
                <?php $this->render_metric_card(__('Enrolled Students', 'lp-mamflow-insights'), $enrolled, 'dashicons-groups'); ?>
                <?php $this->render_metric_card(__('Completion Rate', 'lp-mamflow-insights'), $completion_rate . '%', 'dashicons-yes-alt', $this->get_health_color($completion_rate, 'high_good')); ?>
                <?php $this->render_metric_card(__('Average Progress', 'lp-mamflow-insights'), $avg_progress . '%', 'dashicons-chart-line'); ?>
                <?php $this->render_metric_card(__('Drop-off Rate', 'lp-mamflow-insights'), $dropoff_rate . '%', 'dashicons-warning', $this->get_health_color($dropoff_rate, 'low_good')); ?>
                <?php $this->render_metric_card(__('Quiz Pass Rate', 'lp-mamflow-insights'), $pass_rate . '%', 'dashicons-welcome-learn-more'); ?>
            </div>

            <div class="mf-insights-charts-row">
                <div class="mf-insights-chart-container">
                    <h3><?php _e('Student Engagement Overview', 'lp-mamflow-insights'); ?></h3>
                    <canvas id="engagementChart"></canvas>
                </div>
            </div>

            <script>
                            window.mf_insights_data = {
                                completion: <?php echo $completion_rate; ?>,
                                progress: <?php echo $avg_progress; ?>,
                                dropoff: <?php echo $dropoff_rate; ?>,
                                pass: <?php echo $pass_rate; ?>
                            };
            </script>

            <div class="mf-insights-export-actions">
                <h3><?php _e('Export Data', 'lp-mamflow-insights'); ?></h3>
                <div class="mf-export-buttons">
                    <button type="button" class="button mf-export-btn" data-course-id="<?php echo esc_attr($course_id); ?>"
                        data-export-type="course">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export Course Summary', 'lp-mamflow-insights'); ?>
                    </button>
                    <button type="button" class="button mf-export-btn" data-course-id="<?php echo esc_attr($course_id); ?>"
                        data-export-type="lessons">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export Lessons Data', 'lp-mamflow-insights'); ?>
                    </button>
                    <button type="button" class="button mf-export-btn" data-course-id="<?php echo esc_attr($course_id); ?>"
                        data-export-type="students">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export Students Data', 'lp-mamflow-insights'); ?>
                    </button>
                </div>
            </div>

        <?php else: ?>
            <div class="notice notice-info">
                <p><?php _e('Please select a course to view analytics.', 'lp-mamflow-insights'); ?></p>
            </div>
        <?php endif;
    }
}
