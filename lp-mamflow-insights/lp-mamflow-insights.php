<?php
/**
 * Plugin Name: LearnPress – Mamflow Insight
 * Plugin URI: https://mamflow.com
 * Description: Advanced Analytics & Control Center for LearnPress Instructors.
 * Version: 1.0.0
 * Author: MamFlow
 * Author URI: https://mamflow.com
 * Text Domain: lp-mamflow-insights
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * 
 * @package MF_Insights
 */

defined('ABSPATH') || exit;

// Constants
define('MF_INSIGHTS_VERSION', '1.0.0');
define('MF_INSIGHTS_FILE', __FILE__);
define('MF_INSIGHTS_PATH', plugin_dir_path(__FILE__));
define('MF_INSIGHTS_URL', plugin_dir_url(__FILE__));
define('MF_INSIGHTS_BASENAME', plugin_basename(__FILE__));

// Product ID on mamflow.com
define('MF_INSIGHTS_PRODUCT_ID', 47295);


/**
 * Main MF_Insights Class
 */
class MF_Insights
{

    /**
     * @var MF_Insights Singleton instance
     */
    private static $instance = null;

    /**
     * @var MF_Insights_License_Handler
     */
    private $license_handler = null;

    /**
     * @var MF_Insights_Database
     */
    private $db = null;

    /**
     * @var MF_Insights_Course_Health
     */
    private $course_health = null;

    /**
     * @var MF_Insights_Lesson_Analytics
     */
    private $lesson_analytics = null;

    /**
     * @var MF_Insights_Student_Analytics
     */
    private $student_analytics = null;

    /**
     * @var MF_Insights_Alerts
     */
    private $alerts = null;

    /**
     * @var MF_Insights_Export
     */
    private $export = null;

    /**
     * @var MF_Insights_Instructor_Performance
     */
    private $instructor_performance = null;

    /**
     * Main Instance
     *
     * @return MF_Insights
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        add_action('plugins_loaded', [$this, 'init']);
    }

    /**
     * Initial execution
     */
    public function init()
    {
        if ($this->check_learnpress()) {
            $this->includes();
            $this->load_license_system();
            $this->hooks();
        } else {
            add_action('admin_notices', [$this, 'learnpress_not_active_notice']);
        }
    }

    /**
     * Register hooks
     */
    private function hooks()
    {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'add_license_menu'], 100);
            add_action('admin_notices', [$this, 'license_notice']);
        }
    }

    /**
     * Add license menu to LearnPress admin
     */
    public function add_license_menu()
    {
        if (!class_exists('LearnPress')) {
            return;
        }

        // Register unified Mamflow license page
        mamflow_register_license_menu();

        // Register this plugin's tab
        add_filter('mamflow_license_tabs', [$this, 'register_license_tab']);
    }

    /**
     * Register MF Insights tab in unified license page
     */
    public function register_license_tab($tabs)
    {
        $tabs['mf-insights'] = [
            'title' => esc_html__('Mamflow Insight', 'lp-mamflow-insights'),
            'callback' => 'mf_insights_render_license_tab',
            'priority' => 20
        ];
        return $tabs;
    }

    /**
     * Show admin notice if license not activated
     */
    public function license_notice()
    {
        if (!class_exists('LearnPress')) {
            return;
        }

        if (isset($_GET['page']) && $_GET['page'] === 'mamflow-license') {
            return;
        }

        if (!$this->license_handler || !$this->license_handler->is_feature_enabled()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e('LearnPress Mamflow Insight:', 'lp-mamflow-insights'); ?></strong>
                    <?php
                    printf(
                        esc_html__('Please %sactivate your license%s to unlock all features.', 'lp-mamflow-insights'),
                        '<a href="' . esc_url(admin_url('admin.php?page=mamflow-license&tab=mf-insights')) . '">',
                        '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Check if LearnPress is active
     */
    private function check_learnpress()
    {
        return class_exists('LearnPress');
    }

    /**
     * Show notice if LearnPress is not active
     */
    public function learnpress_not_active_notice()
    {
        ?>
        <div class="error">
            <p>
                <?php esc_html_e('LearnPress – Mamflow Insight requires LearnPress plugin to be active.', 'lp-mamflow-insights'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Include required files
     */
    private function includes()
    {
        // License system first
        require_once MF_INSIGHTS_PATH . 'inc/license/class-license-handler.php';
        require_once MF_INSIGHTS_PATH . 'inc/license/shared-license-page.php';
        require_once MF_INSIGHTS_PATH . 'inc/license/admin-license-page.php';

        require_once MF_INSIGHTS_PATH . 'includes/class-helpers.php';
        require_once MF_INSIGHTS_PATH . 'includes/class-database.php';

        if (is_admin()) {
            require_once MF_INSIGHTS_PATH . 'includes/admin/class-admin.php';
            require_once MF_INSIGHTS_PATH . 'includes/admin/class-course-health.php';
            require_once MF_INSIGHTS_PATH . 'includes/admin/class-lesson-analytics.php';
            require_once MF_INSIGHTS_PATH . 'includes/admin/class-student-analytics.php';
            require_once MF_INSIGHTS_PATH . 'includes/admin/class-alerts.php';
            require_once MF_INSIGHTS_PATH . 'includes/admin/class-export.php';
            require_once MF_INSIGHTS_PATH . 'includes/admin/class-instructor-performance.php';
        }
    }

    /**
     * Load and initialize plugin components
     */
    private function load_license_system()
    {
        $this->db = new MF_Insights_Database();

        $this->license_handler = new MF_Insights_License_Handler([
            'product_id' => MF_INSIGHTS_PRODUCT_ID,
            'product_name' => 'LearnPress Mamflow Insight',
            'option_key' => 'mf_insights_license_data'
        ]);

        if (is_admin()) {
            MF_Insights_Admin::instance();
            $this->course_health = new MF_Insights_Course_Health($this->db);
            $this->lesson_analytics = new MF_Insights_Lesson_Analytics($this->db);
            $this->student_analytics = new MF_Insights_Student_Analytics($this->db);
            $this->alerts = new MF_Insights_Alerts($this->db);
            $this->export = new MF_Insights_Export($this->db);
            $this->instructor_performance = new MF_Insights_Instructor_Performance($this->db);
        }
    }

    /**
     * Get student analytics handler
     * 
     * @return MF_Insights_Student_Analytics
     */
    public function get_student_analytics()
    {
        return $this->student_analytics;
    }

    /**
     * Get lesson analytics handler
     * 
     * @return MF_Insights_Lesson_Analytics
     */
    public function get_lesson_analytics()
    {
        return $this->lesson_analytics;
    }

    /**
     * Get database handler
     * 
     * @return MF_Insights_Database
     */
    public function get_db()
    {
        return $this->db;
    }

    /**
     * Get course health handler
     * 
     * @return MF_Insights_Course_Health
     */
    public function get_course_health()
    {
        return $this->course_health;
    }

    /**
     * Get license handler
     * 
     * @return MF_Insights_License_Handler
     */
    public function get_license_handler()
    {
        return $this->license_handler;
    }

    /**
     * Get alerts handler
     * 
     * @return MF_Insights_Alerts
     */
    public function get_alerts()
    {
        return $this->alerts;
    }

    /**
     * Get export handler
     * 
     * @return MF_Insights_Export
     */
    public function get_export()
    {
        return $this->export;
    }

    /**
     * Get instructor performance handler
     * 
     * @return MF_Insights_Instructor_Performance
     */
    public function get_instructor_performance()
    {
        return $this->instructor_performance;
    }
}

/**
 * Initialize the plugin
 */
function mf_insights()
{
    return MF_Insights::instance();
}

mf_insights();
