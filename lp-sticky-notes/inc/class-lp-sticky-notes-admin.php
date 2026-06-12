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
        add_action('wp_ajax_lp_sticky_notes_search_students', array($this, 'ajax_search_students'));
        add_action('wp_ajax_lp_sticky_notes_search_courses', array($this, 'ajax_search_courses'));
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
            'read',
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
            LP_STICKY_NOTES_VERSION . '-' . filemtime(LP_STICKY_NOTES_PATH . 'assets/css/admin.css')
        );

        wp_enqueue_script(
            'lp-sticky-notes-admin',
            LP_STICKY_NOTES_URL . 'assets/js/admin.js',
            array('jquery'),
            LP_STICKY_NOTES_VERSION . '-' . filemtime(LP_STICKY_NOTES_PATH . 'assets/js/admin.js'),
            true
        );

        wp_localize_script(
            'lp-sticky-notes-admin',
            'lpStickyNotesAdmin',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('lp_sticky_notes_admin_filters'),
                'i18n' => array(
                    'searching' => __('Searching...', 'lp-sticky-notes'),
                    'noResults' => __('No matches found', 'lp-sticky-notes'),
                    'typeToSearch' => __('Type to search', 'lp-sticky-notes'),
                    'viewFull' => __('View full', 'lp-sticky-notes'),
                    'hide' => __('Hide', 'lp-sticky-notes'),
                ),
            )
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
            <div class="wrap lp-sticky-notes-admin">
                <div class="lp-admin-header">
                    <div>
                        <h1><?php esc_html_e('Student Notes', 'lp-sticky-notes'); ?></h1>
                        <p><?php esc_html_e('Review notes saved by students across LearnPress courses and lessons.', 'lp-sticky-notes'); ?></p>
                    </div>
                </div>
                <div class="lp-empty-state lp-license-required">
                    <h2><?php esc_html_e('License required', 'lp-sticky-notes'); ?></h2>
                    <p><?php esc_html_e('Activate a valid license to view student notes and manage saved lesson annotations.', 'lp-sticky-notes'); ?></p>
                    <div class="filter-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=mamflow-license&tab=sticky-notes')); ?>"
                            class="button button-primary">
                            <?php esc_html_e('Activate License', 'lp-sticky-notes'); ?>
                        </a>
                        <a href="<?php echo esc_url(LP_STICKY_NOTES_PRODUCT_URL); ?>" class="button"
                            target="_blank">
                            <?php esc_html_e('Purchase License', 'lp-sticky-notes'); ?>
                        </a>
                    </div>
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

        // Get filter options. Lists are capped; search loads more via AJAX.
        $students = $this->get_students_with_notes('', 30, $student_id);

        // Get courses with notes. Lists are capped; search loads more via AJAX.
        $courses = $this->get_courses_with_notes('', 30, $course_id);
        $student_count = $this->get_students_with_notes_count();
        $course_count = $this->get_courses_with_notes_count();

        // Get total count for pagination
        $total_notes = $this->get_filtered_notes_count($student_id, $course_id, $lesson_id);
        $total_pages = ceil($total_notes / $per_page);

        // Get notes based on filters with pagination
        $notes = $this->get_filtered_notes($student_id, $course_id, $lesson_id, $per_page, $paged);

        include LP_STICKY_NOTES_PATH . 'templates/admin-student-notes.php';
    }

    /**
     * Search student filter options.
     */
    public function ajax_search_students()
    {
        $this->verify_filter_ajax_request();

        $search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
        $students = $this->get_students_with_notes($search, 30);

        wp_send_json_success(
            array_map(
                function ($student) {
                    return array(
                        'id' => (int) $student->ID,
                        'label' => sprintf('%s (%s)', $student->display_name, $student->user_email),
                    );
                },
                $students
            )
        );
    }

    /**
     * Search course filter options.
     */
    public function ajax_search_courses()
    {
        $this->verify_filter_ajax_request();

        $search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
        $courses = $this->get_courses_with_notes($search, 30);

        wp_send_json_success(
            array_map(
                function ($course) {
                    return array(
                        'id' => (int) $course->ID,
                        'label' => $course->post_title,
                    );
                },
                $courses
            )
        );
    }

    /**
     * Verify AJAX requests for filter option search.
     */
    private function verify_filter_ajax_request()
    {
        check_ajax_referer('lp_sticky_notes_admin_filters', 'nonce');

        $can_access = current_user_can('manage_options');
        if (!$can_access && defined('LP_TEACHER_ROLE')) {
            $can_access = current_user_can(LP_TEACHER_ROLE);
        }

        if (!$can_access) {
            wp_send_json_error(array('message' => __('You do not have permission to search filters.', 'lp-sticky-notes')), 403);
        }

        $license_handler = LP_Sticky_Notes::instance()->get_license_handler();
        if (!$license_handler->is_feature_enabled()) {
            wp_send_json_error(array('message' => __('Activate a valid license to search student notes filters.', 'lp-sticky-notes')), 403);
        }
    }

    /**
     * Get list of students who have notes
     *
     * @return array
     */
    private function get_students_with_notes($search = '', $limit = 30, $include_id = 0)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'learnpress_sticky_notes';
        $where = array();
        $params = array();

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(u.display_name LIKE %s OR u.user_email LIKE %s)';
            $params[] = $like;
            $params[] = $like;
        }

        if ($include_id > 0 && $search !== '') {
            $where[] = 'u.ID = %d';
            $params[] = $include_id;
        }

        $where_sql = '';
        if (!empty($where)) {
            $where_sql = 'WHERE (' . implode(' OR ', $where) . ')';
        }

        $order_sql = 'ORDER BY u.display_name ASC';
        if ($include_id > 0) {
            $order_sql = 'ORDER BY CASE WHEN u.ID = %d THEN 0 ELSE 1 END, u.display_name ASC';
            $params[] = $include_id;
        }

        $params[] = max(1, absint($limit));

        $sql = "SELECT DISTINCT u.ID, u.display_name, u.user_email
			FROM {$table} n
			INNER JOIN {$wpdb->users} u ON n.user_id = u.ID
            {$where_sql}
			{$order_sql}
            LIMIT %d";

        $results = $wpdb->get_results($wpdb->prepare($sql, $params));

        return $results;
    }

    /**
     * Get list of courses with notes
     *
     * @return array
     */
    private function get_courses_with_notes($search = '', $limit = 30, $include_id = 0)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'learnpress_sticky_notes';
        $where = array("p.post_type = 'lp_course'");
        $params = array();

        if ($search !== '') {
            $where[] = 'p.post_title LIKE %s';
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        if ($include_id > 0 && $search !== '') {
            $where[] = 'p.ID = %d';
            $params[] = $include_id;
        }

        $order_sql = 'ORDER BY p.post_title ASC';
        if ($include_id > 0) {
            $order_sql = 'ORDER BY CASE WHEN p.ID = %d THEN 0 ELSE 1 END, p.post_title ASC';
            $params[] = $include_id;
        }

        $params[] = max(1, absint($limit));

        $sql = "SELECT DISTINCT p.ID, p.post_title
			FROM {$table} n
			INNER JOIN {$wpdb->posts} p ON n.course_id = p.ID
			WHERE " . implode(' AND ', $where) . "
			{$order_sql}
            LIMIT %d";

        $results = $wpdb->get_results($wpdb->prepare($sql, $params));

        return $results;
    }

    /**
     * Get count of students who have notes.
     *
     * @return int
     */
    private function get_students_with_notes_count()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'learnpress_sticky_notes';

        return (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT u.ID)
            FROM {$table} n
            INNER JOIN {$wpdb->users} u ON n.user_id = u.ID"
        );
    }

    /**
     * Get count of courses that have notes.
     *
     * @return int
     */
    private function get_courses_with_notes_count()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'learnpress_sticky_notes';

        return (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT p.ID)
            FROM {$table} n
            INNER JOIN {$wpdb->posts} p ON n.course_id = p.ID
            WHERE p.post_type = 'lp_course'"
        );
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
