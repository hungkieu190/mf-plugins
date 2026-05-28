<?php
/**
 * Admin interface for viewing student notes
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();

/**
 * Class LP_Sticky_Notes_Admin
 */
class LP_Sticky_Notes_Admin
{
    /**
     * Instance
     *
     * @var LP_Sticky_Notes_Admin
     */
    protected static $instance = null;

    /**
     * LP_Sticky_Notes_Admin constructor.
     */
    protected function __construct()
    {
        $this->hooks();
    }

    /**
     * Register hooks
     */
    private function hooks()
    {
        // Priority 100 to ensure LearnPress menu is already registered
        add_action('admin_menu', array($this, 'register_admin_menu'), 100);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Register admin menu
     */
    public function register_admin_menu()
    {
        add_submenu_page(
            'learn_press',
            __('Student Notes', 'lp-sticky-notes'),
            __('Student Notes', 'lp-sticky-notes'),
            'manage_options',
            'lp-student-notes',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook)
    {
        if ($hook !== 'learnpress_page_lp-student-notes') {
            return;
        }

        wp_enqueue_style(
            'lp-sticky-notes-admin',
            LP_STICKY_NOTES_URL . 'assets/css/admin.css',
            array(),
            LP_STICKY_NOTES_VERSION
        );

        wp_enqueue_script(
            'lp-sticky-notes-admin',
            LP_STICKY_NOTES_URL . 'assets/js/admin.js',
            array('jquery'),
            LP_STICKY_NOTES_VERSION,
            true
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page()
    {
        // Check permissions
        $can_access = current_user_can('manage_options');
        if (!$can_access && defined('LP_TEACHER_ROLE')) {
            $can_access = current_user_can(LP_TEACHER_ROLE);
        }

        if (!$can_access) {
            wp_die(__('You do not have permission to access this page.', 'lp-sticky-notes'));
        }

        // Check license
        $license_handler = LP_Sticky_Notes::instance()->get_license_handler();
        if (!$license_handler->is_feature_enabled()) {
            // Show license required message
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('Student Notes', 'lp-sticky-notes'); ?></h1>
                <div class="notice notice-warning" style="padding: 20px; margin: 20px 0;">
                    <h2 style="margin-top: 0;"><?php esc_html_e('License Required', 'lp-sticky-notes'); ?></h2>
                    <p><?php esc_html_e('This feature requires an active license to access student notes.', 'lp-sticky-notes'); ?>
                    </p>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=learn-press-settings&tab=sticky-notes&section=license')); ?>"
                            class="button button-primary">
                            <?php esc_html_e('Activate License', 'lp-sticky-notes'); ?>
                        </a>
                        <a href="https://mamflow.com/product/learnpress-notes-addon-lp-sticky-notes/" class="button"
                            target="_blank">
                            <?php esc_html_e('Purchase License', 'lp-sticky-notes'); ?>
                        </a>
                    </p>
                </div>
            </div>
            <?php
            return;
        }

        // Get filters from URL
        $student_id = isset($_GET['student_id']) ? absint($_GET['student_id']) : 0;
        $course_id = isset($_GET['course_id']) ? absint($_GET['course_id']) : 0;
        $lesson_id = isset($_GET['lesson_id']) ? absint($_GET['lesson_id']) : 0;
        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 20; // Notes per page

        // Get students with notes
        $students = $this->get_students_with_notes();

        // Get courses
        $courses = $this->get_courses_with_notes();

        // Get total count for pagination
        $total_notes = $this->get_filtered_notes_count($student_id, $course_id, $lesson_id);
        $total_pages = ceil($total_notes / $per_page);

        // Get notes based on filters with pagination
        $notes = $this->get_filtered_notes($student_id, $course_id, $lesson_id, $per_page, $paged);

        include LP_STICKY_NOTES_PATH . 'templates/admin-student-notes.php';
    }

    /**
     * Get list of students who have notes
     *
     * @return array
     */
    private function get_students_with_notes()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'learnpress_sticky_notes';

        $results = $wpdb->get_results(
            "SELECT DISTINCT u.ID, u.display_name, u.user_email
			FROM {$table} n
			INNER JOIN {$wpdb->users} u ON n.user_id = u.ID
			ORDER BY u.display_name ASC"
        );

        return $results;
    }

    /**
     * Get list of courses with notes
     *
     * @return array
     */
    private function get_courses_with_notes()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'learnpress_sticky_notes';

        $results = $wpdb->get_results(
            "SELECT DISTINCT p.ID, p.post_title
			FROM {$table} n
			INNER JOIN {$wpdb->posts} p ON n.course_id = p.ID
			WHERE p.post_type = 'lp_course'
			ORDER BY p.post_title ASC"
        );

        return $results;
    }

    /**
     * Get count of filtered notes
     *
     * @param int $student_id
     * @param int $course_id
     * @param int $lesson_id
     * @return int
     */
    private function get_filtered_notes_count($student_id, $course_id, $lesson_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'learnpress_sticky_notes';

        $where = array('1=1');
        if ($student_id > 0) {
            $where[] = $wpdb->prepare('n.user_id = %d', $student_id);
        }
        if ($course_id > 0) {
            $where[] = $wpdb->prepare('n.course_id = %d', $course_id);
        }
        if ($lesson_id > 0) {
            $where[] = $wpdb->prepare('n.lesson_id = %d', $lesson_id);
        }

        $sql = "SELECT COUNT(*)
                FROM {$table} n
                WHERE " . implode(' AND ', $where);

        return (int) $wpdb->get_var($sql);
    }

    /**
     * Get filtered notes
     *
     * @param int $student_id
     * @param int $course_id
     * @param int $lesson_id
     * @param int $per_page
     * @param int $paged
     * @return array
     */
    private function get_filtered_notes($student_id = 0, $course_id = 0, $lesson_id = 0, $per_page = 20, $paged = 1)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'learnpress_sticky_notes';

        $where = array('1=1');
        $params = array();

        if ($student_id) {
            $where[] = 'n.user_id = %d';
            $params[] = $student_id;
        }

        if ($course_id) {
            $where[] = 'n.course_id = %d';
            $params[] = $course_id;
        }

        if ($lesson_id) {
            $where[] = 'n.lesson_id = %d';
            $params[] = $lesson_id;
        }

        $offset = ($paged - 1) * $per_page;

        $sql = "SELECT n.*, 
				u.display_name as student_name,
				u.user_email as student_email,
				c.post_title as course_title,
				l.post_title as lesson_title
			FROM {$table} n
			INNER JOIN {$wpdb->users} u ON n.user_id = u.ID
			INNER JOIN {$wpdb->posts} c ON n.course_id = c.ID
			INNER JOIN {$wpdb->posts} l ON n.lesson_id = l.ID
			WHERE " . implode(' AND ', $where) . "
			ORDER BY n.created_at DESC
			LIMIT {$per_page} OFFSET {$offset}";

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        $results = $wpdb->get_results($sql);

        return $results;
    }

    /**
     * Get instance
     *
     * @return LP_Sticky_Notes_Admin
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
