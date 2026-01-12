<?php
/**
 * Instructor Performance class for LearnPress Mamflow Insight
 * 
 * @package MF_Insights
 */

defined('ABSPATH') || exit;

class MF_Insights_Instructor_Performance
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
        add_action('wp_ajax_mf_insights_get_instructor_data', [$this, 'ajax_get_instructor_data']);
    }

    /**
     * Get all instructors with their course stats
     * 
     * @return array
     */
    public function get_instructors_list()
    {
        global $wpdb;

        // Get all users who are authors of courses
        $instructors = $wpdb->get_results("
            SELECT DISTINCT u.ID, u.display_name, u.user_email
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->posts} p ON u.ID = p.post_author
            WHERE p.post_type = 'lp_course'
            AND p.post_status = 'publish'
            ORDER BY u.display_name ASC
        ");

        // Add stats for each instructor
        foreach ($instructors as $instructor) {
            $instructor->stats = $this->get_instructor_stats($instructor->ID);
        }

        return $instructors;
    }

    /**
     * Get aggregated stats for an instructor
     * 
     * @param int $instructor_id
     * @return object
     */
    public function get_instructor_stats($instructor_id)
    {
        $courses = $this->db->get_instructor_courses($instructor_id);

        $stats = (object) [
            'total_courses' => count($courses),
            'total_students' => 0,
            'avg_completion_rate' => 0,
            'avg_progress' => 0,
            'avg_quiz_pass_rate' => 0,
            'total_completed' => 0,
        ];

        if (empty($courses)) {
            return $stats;
        }

        $completion_rates = [];
        $progress_rates = [];
        $quiz_pass_rates = [];

        foreach ($courses as $course) {
            $enrolled = $this->db->get_enrolled_count($course->ID);
            $stats->total_students += $enrolled;

            $completion_rate = $this->db->get_course_completion_rate($course->ID);
            if ($completion_rate > 0) {
                $completion_rates[] = $completion_rate;
            }

            $progress = $this->db->get_avg_progress($course->ID);
            if ($progress > 0) {
                $progress_rates[] = $progress;
            }

            $quiz_pass = $this->db->get_course_quiz_pass_rate($course->ID);
            if ($quiz_pass > 0) {
                $quiz_pass_rates[] = $quiz_pass;
            }

            // Count completed students
            $stats->total_completed += $this->db->get_completed_count($course->ID);
        }

        // Calculate averages
        if (!empty($completion_rates)) {
            $stats->avg_completion_rate = round(array_sum($completion_rates) / count($completion_rates), 1);
        }
        if (!empty($progress_rates)) {
            $stats->avg_progress = round(array_sum($progress_rates) / count($progress_rates), 1);
        }
        if (!empty($quiz_pass_rates)) {
            $stats->avg_quiz_pass_rate = round(array_sum($quiz_pass_rates) / count($quiz_pass_rates), 1);
        }

        return $stats;
    }

    /**
     * Get detailed course breakdown for an instructor
     * 
     * @param int $instructor_id
     * @return array
     */
    public function get_instructor_courses_detail($instructor_id)
    {
        $courses = $this->db->get_instructor_courses($instructor_id);
        $details = [];

        foreach ($courses as $course) {
            $details[] = [
                'id' => $course->ID,
                'title' => $course->post_title,
                'enrolled' => $this->db->get_enrolled_count($course->ID),
                'completed' => $this->db->get_completed_count($course->ID),
                'completion_rate' => round($this->db->get_course_completion_rate($course->ID), 1),
                'avg_progress' => round($this->db->get_avg_progress($course->ID), 1),
                'quiz_pass_rate' => round($this->db->get_course_quiz_pass_rate($course->ID), 1),
                'dropoff_rate' => round($this->db->get_dropoff_rate($course->ID), 1),
            ];
        }

        return $details;
    }

    /**
     * AJAX: Get instructor data
     */
    public function ajax_get_instructor_data()
    {
        check_ajax_referer('mf_insights_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'lp-mamflow-insights')]);
        }

        $instructor_id = isset($_POST['instructor_id']) ? absint($_POST['instructor_id']) : 0;

        if (!$instructor_id) {
            wp_send_json_error(['message' => __('Invalid instructor ID', 'lp-mamflow-insights')]);
        }

        $instructor = get_userdata($instructor_id);
        if (!$instructor) {
            wp_send_json_error(['message' => __('Instructor not found', 'lp-mamflow-insights')]);
        }

        wp_send_json_success([
            'instructor' => [
                'id' => $instructor->ID,
                'name' => $instructor->display_name,
                'email' => $instructor->user_email,
                'avatar' => get_avatar_url($instructor->ID, ['size' => 96]),
            ],
            'stats' => $this->get_instructor_stats($instructor_id),
            'courses' => $this->get_instructor_courses_detail($instructor_id),
        ]);
    }

    /**
     * Render instructor performance page
     */
    public function render()
    {
        $instructors = $this->get_instructors_list();
        $selected_id = isset($_GET['instructor_id']) ? absint($_GET['instructor_id']) : 0;
        $selected_instructor = null;
        $selected_courses = [];

        if ($selected_id) {
            $selected_instructor = get_userdata($selected_id);
            $selected_courses = $this->get_instructor_courses_detail($selected_id);
        }
        ?>
        <div class="wrap mf-insights-dashboard">
            <h1 class="wp-heading-inline">
                <?php _e('Instructor Performance', 'lp-mamflow-insights'); ?>
            </h1>
            <hr class="wp-header-end">

            <div class="mf-insights-filters">
                <form method="get">
                    <input type="hidden" name="page" value="mf-insights">
                    <input type="hidden" name="tab" value="instructors">
                    <label for="mf-instructor-select">
                        <?php _e('Select Instructor:', 'lp-mamflow-insights'); ?>
                    </label>
                    <select name="instructor_id" id="mf-instructor-select" class="mf-select2-course" style="width: 350px;">
                        <option value="0">
                            <?php _e('-- Select an instructor --', 'lp-mamflow-insights'); ?>
                        </option>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?php echo $instructor->ID; ?>" <?php selected($selected_id, $instructor->ID); ?>>
                                <?php echo esc_html($instructor->display_name); ?>
                                (
                                <?php echo $instructor->stats->total_courses; ?>
                                <?php _e('courses', 'lp-mamflow-insights'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button button-primary">
                        <?php _e('View Performance', 'lp-mamflow-insights'); ?>
                    </button>
                </form>
            </div>

            <?php if ($selected_instructor):
                $stats = $this->get_instructor_stats($selected_id);
                ?>
                <div class="mf-instructor-profile">
                    <div class="mf-instructor-header">
                        <img src="<?php echo esc_url(get_avatar_url($selected_id, ['size' => 80])); ?>"
                            alt="<?php echo esc_attr($selected_instructor->display_name); ?>" class="mf-instructor-avatar">
                        <div class="mf-instructor-info">
                            <h2>
                                <?php echo esc_html($selected_instructor->display_name); ?>
                            </h2>
                            <p>
                                <?php echo esc_html($selected_instructor->user_email); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mf-insights-metrics-grid">
                    <?php $this->render_stat_card(__('Total Courses', 'lp-mamflow-insights'), $stats->total_courses, 'dashicons-book'); ?>
                    <?php $this->render_stat_card(__('Total Students', 'lp-mamflow-insights'), $stats->total_students, 'dashicons-groups'); ?>
                    <?php $this->render_stat_card(__('Completed Students', 'lp-mamflow-insights'), $stats->total_completed, 'dashicons-yes-alt'); ?>
                    <?php $this->render_stat_card(__('Avg Completion Rate', 'lp-mamflow-insights'), $stats->avg_completion_rate . '%', 'dashicons-chart-pie'); ?>
                    <?php $this->render_stat_card(__('Avg Progress', 'lp-mamflow-insights'), $stats->avg_progress . '%', 'dashicons-chart-line'); ?>
                    <?php $this->render_stat_card(__('Avg Quiz Pass Rate', 'lp-mamflow-insights'), $stats->avg_quiz_pass_rate . '%', 'dashicons-welcome-learn-more'); ?>
                </div>

                <?php if (!empty($selected_courses)): ?>
                    <div class="mf-insights-table-container">
                        <h3>
                            <?php _e('Course Breakdown', 'lp-mamflow-insights'); ?>
                        </h3>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>
                                        <?php _e('Course', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Enrolled', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Completed', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Completion %', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Avg Progress', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Quiz Pass %', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Drop-off %', 'lp-mamflow-insights'); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($selected_courses as $course): ?>
                                    <tr>
                                        <td>
                                            <a
                                                href="<?php echo esc_url(admin_url('admin.php?page=mf-insights&course_id=' . $course['id'])); ?>">
                                                <?php echo esc_html($course['title']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php echo $course['enrolled']; ?>
                                        </td>
                                        <td>
                                            <?php echo $course['completed']; ?>
                                        </td>
                                        <td>
                                            <?php echo $course['completion_rate']; ?>%
                                        </td>
                                        <td>
                                            <?php echo $course['avg_progress']; ?>%
                                        </td>
                                        <td>
                                            <?php echo $course['quiz_pass_rate']; ?>%
                                        </td>
                                        <td>
                                            <?php echo $course['dropoff_rate']; ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="mf-instructors-overview">
                    <h3>
                        <?php _e('All Instructors Overview', 'lp-mamflow-insights'); ?>
                    </h3>
                    <?php if (!empty($instructors)): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>
                                        <?php _e('Instructor', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Courses', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Students', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Completed', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Avg Completion', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Avg Progress', 'lp-mamflow-insights'); ?>
                                    </th>
                                    <th>
                                        <?php _e('Actions', 'lp-mamflow-insights'); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($instructors as $instructor): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo esc_url(get_avatar_url($instructor->ID, ['size' => 32])); ?>"
                                                style="vertical-align: middle; border-radius: 50%; margin-right: 8px;">
                                            <?php echo esc_html($instructor->display_name); ?>
                                        </td>
                                        <td>
                                            <?php echo $instructor->stats->total_courses; ?>
                                        </td>
                                        <td>
                                            <?php echo $instructor->stats->total_students; ?>
                                        </td>
                                        <td>
                                            <?php echo $instructor->stats->total_completed; ?>
                                        </td>
                                        <td>
                                            <?php echo $instructor->stats->avg_completion_rate; ?>%
                                        </td>
                                        <td>
                                            <?php echo $instructor->stats->avg_progress; ?>%
                                        </td>
                                        <td>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=mf-insights&tab=instructors&instructor_id=' . $instructor->ID)); ?>"
                                                class="button button-small">
                                                <?php _e('View Details', 'lp-mamflow-insights'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="notice notice-info">
                            <p>
                                <?php _e('No instructors found with published courses.', 'lp-mamflow-insights'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render stat card
     */
    private function render_stat_card($label, $value, $icon)
    {
        ?>
        <div class="mf-insights-card">
            <div class="mf-insights-card-icon"><span class="dashicons <?php echo esc_attr($icon); ?>"></span></div>
            <div class="mf-insights-card-content">
                <p class="mf-insights-card-label">
                    <?php echo esc_html($label); ?>
                </p>
                <h2 class="mf-insights-card-value">
                    <?php echo $value; ?>
                </h2>
            </div>
        </div>
        <?php
    }

    /**
     * Render content only (for tabbed interface)
     */
    public function render_content()
    {
        $instructors = $this->get_instructors_list();
        $selected_id = isset($_GET['instructor_id']) ? absint($_GET['instructor_id']) : 0;
        $selected_instructor = null;
        $selected_courses = [];

        if ($selected_id) {
            $selected_instructor = get_userdata($selected_id);
            $selected_courses = $this->get_instructor_courses_detail($selected_id);
        }
        ?>
        <div class="mf-insights-filters">
            <form method="get">
                <input type="hidden" name="page" value="mf-insights">
                <input type="hidden" name="tab" value="instructors">
                <label for="mf-instructor-select"><?php _e('Select Instructor:', 'lp-mamflow-insights'); ?></label>
                <select name="instructor_id" id="mf-instructor-select" class="mf-select2-course" style="width: 350px;">
                    <option value="0"><?php _e('-- Select an instructor --', 'lp-mamflow-insights'); ?></option>
                    <?php foreach ($instructors as $instructor): ?>
                        <option value="<?php echo $instructor->ID; ?>" <?php selected($selected_id, $instructor->ID); ?>>
                            <?php echo esc_html($instructor->display_name); ?> (<?php echo $instructor->stats->total_courses; ?>
                            <?php _e('courses', 'lp-mamflow-insights'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit"
                    class="button button-primary"><?php _e('View Performance', 'lp-mamflow-insights'); ?></button>
            </form>
        </div>

        <?php if ($selected_instructor):
            $stats = $this->get_instructor_stats($selected_id);
            ?>
            <div class="mf-instructor-profile">
                <div class="mf-instructor-header">
                    <img src="<?php echo esc_url(get_avatar_url($selected_id, ['size' => 80])); ?>"
                        alt="<?php echo esc_attr($selected_instructor->display_name); ?>" class="mf-instructor-avatar">
                    <div class="mf-instructor-info">
                        <h2><?php echo esc_html($selected_instructor->display_name); ?></h2>
                        <p><?php echo esc_html($selected_instructor->user_email); ?></p>
                    </div>
                </div>
            </div>

            <div class="mf-insights-metrics-grid">
                <?php $this->render_stat_card(__('Total Courses', 'lp-mamflow-insights'), $stats->total_courses, 'dashicons-book'); ?>
                <?php $this->render_stat_card(__('Total Students', 'lp-mamflow-insights'), $stats->total_students, 'dashicons-groups'); ?>
                <?php $this->render_stat_card(__('Completed Students', 'lp-mamflow-insights'), $stats->total_completed, 'dashicons-yes-alt'); ?>
                <?php $this->render_stat_card(__('Avg Completion Rate', 'lp-mamflow-insights'), $stats->avg_completion_rate . '%', 'dashicons-chart-pie'); ?>
                <?php $this->render_stat_card(__('Avg Progress', 'lp-mamflow-insights'), $stats->avg_progress . '%', 'dashicons-chart-line'); ?>
                <?php $this->render_stat_card(__('Avg Quiz Pass Rate', 'lp-mamflow-insights'), $stats->avg_quiz_pass_rate . '%', 'dashicons-welcome-learn-more'); ?>
            </div>

            <?php if (!empty($selected_courses)): ?>
                <div class="mf-insights-table-container">
                    <h3><?php _e('Course Breakdown', 'lp-mamflow-insights'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Course', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Enrolled', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Completed', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Completion %', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Avg Progress', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Quiz Pass %', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Drop-off %', 'lp-mamflow-insights'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selected_courses as $course): ?>
                                <tr>
                                    <td><a
                                            href="<?php echo esc_url(admin_url('admin.php?page=mf-insights&tab=course-health&course_id=' . $course['id'])); ?>"><?php echo esc_html($course['title']); ?></a>
                                    </td>
                                    <td><?php echo $course['enrolled']; ?></td>
                                    <td><?php echo $course['completed']; ?></td>
                                    <td><?php echo $course['completion_rate']; ?>%</td>
                                    <td><?php echo $course['avg_progress']; ?>%</td>
                                    <td><?php echo $course['quiz_pass_rate']; ?>%</td>
                                    <td><?php echo $course['dropoff_rate']; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="mf-instructors-overview">
                <h3><?php _e('All Instructors Overview', 'lp-mamflow-insights'); ?></h3>
                <?php if (!empty($instructors)): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Instructor', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Courses', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Students', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Completed', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Avg Completion', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Avg Progress', 'lp-mamflow-insights'); ?></th>
                                <th><?php _e('Actions', 'lp-mamflow-insights'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($instructors as $instructor): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo esc_url(get_avatar_url($instructor->ID, ['size' => 32])); ?>"
                                            style="vertical-align: middle; border-radius: 50%; margin-right: 8px;">
                                        <?php echo esc_html($instructor->display_name); ?>
                                    </td>
                                    <td><?php echo $instructor->stats->total_courses; ?></td>
                                    <td><?php echo $instructor->stats->total_students; ?></td>
                                    <td><?php echo $instructor->stats->total_completed; ?></td>
                                    <td><?php echo $instructor->stats->avg_completion_rate; ?>%</td>
                                    <td><?php echo $instructor->stats->avg_progress; ?>%</td>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=mf-insights&tab=instructors&instructor_id=' . $instructor->ID)); ?>"
                                            class="button button-small">
                                            <?php _e('View Details', 'lp-mamflow-insights'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="notice notice-info">
                        <p><?php _e('No instructors found with published courses.', 'lp-mamflow-insights'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif;
    }
}
