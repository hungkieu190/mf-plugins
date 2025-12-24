<?php
/**
 * Plugin Name: LearnPress – Course & Lesson Survey
 * Plugin URI: https://mamflow.com
 * Description: Collect student feedback immediately after completing lessons or courses in LearnPress
 * Version: 1.0.0
 * Author: MamFlow
 * Author URI: https://mamflow.com
 * Text Domain: lp-survey
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Require_LP_Version: 4.2.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LP_SURVEY_VERSION', '1.0.0');
define('LP_SURVEY_FILE', __FILE__);
define('LP_SURVEY_PATH', plugin_dir_path(__FILE__));
define('LP_SURVEY_URL', plugin_dir_url(__FILE__));
define('LP_SURVEY_BASENAME', plugin_basename(__FILE__));

// License Product ID on mamflow.com
define('LP_SURVEY_PRODUCT_ID', 47233);

/**
 * Main LP_Survey Class
 */
final class LP_Survey
{

    /**
     * The single instance of the class
     *
     * @var LP_Survey
     */
    protected static $_instance = null;

    /**
     * License handler instance
     *
     * @var LP_Survey_License_Handler
     */
    private $license_handler;

    /**
     * Main LP_Survey Instance
     *
     * Ensures only one instance of LP_Survey is loaded or can be loaded.
     *
     * @return LP_Survey - Main instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * LP_Survey Constructor
     */
    public function __construct()
    {
        $this->includes();
        $this->load_license_system();
        $this->init_hooks();
    }

    /**
     * Include required core files
     */
    private function includes()
    {
        // License system
        require_once LP_SURVEY_PATH . 'inc/license/class-license-handler.php';
        require_once LP_SURVEY_PATH . 'inc/license/shared-license-page.php';
        require_once LP_SURVEY_PATH . 'inc/license/admin-license-page.php';
        require_once LP_SURVEY_PATH . 'inc/license/cron-scheduler.php';

        // Core classes
        require_once LP_SURVEY_PATH . 'includes/class-database.php';
        require_once LP_SURVEY_PATH . 'includes/class-helpers.php';
        require_once LP_SURVEY_PATH . 'includes/class-learnpress-hooks.php';

        // Settings - needed for both admin and frontend (LearnPress Settings integration)
        require_once LP_SURVEY_PATH . 'includes/admin/class-settings.php';

        // Admin classes
        if (is_admin()) {
            require_once LP_SURVEY_PATH . 'includes/admin/class-admin.php';
            require_once LP_SURVEY_PATH . 'includes/admin/class-dashboard.php';
            require_once LP_SURVEY_PATH . 'includes/admin/class-course-metabox.php';
            require_once LP_SURVEY_PATH . 'includes/admin/class-lesson-metabox.php';
            require_once LP_SURVEY_PATH . 'includes/admin/class-survey-manager.php';
        }

        // Frontend classes (MUST be always included for AJAX to work!)
        require_once LP_SURVEY_PATH . 'includes/frontend/class-frontend.php';
        require_once LP_SURVEY_PATH . 'includes/frontend/class-survey-display.php';
    }

    /**
     * Hook into actions and filters
     */
    private function init_hooks()
    {
        // Activation/Deactivation hooks
        register_activation_hook(LP_SURVEY_FILE, array($this, 'activate'));
        register_deactivation_hook(LP_SURVEY_FILE, array($this, 'deactivate'));

        // Check if LearnPress is active
        add_action('plugins_loaded', array($this, 'check_learnpress'), 11);

        // Load textdomain
        add_action('init', array($this, 'load_textdomain'));

        // Initialize components
        add_action('plugins_loaded', array($this, 'init'), 20);

        // License admin menu
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_license_menu'), 100);
            add_action('admin_notices', array($this, 'license_notice'));
        }
    }

    /**
     * Load and initialize license system
     */
    private function load_license_system()
    {
        // Initialize license handler
        $this->license_handler = new LP_Survey_License_Handler([
            'product_id' => LP_SURVEY_PRODUCT_ID,
            'product_name' => 'LearnPress – Course & Lesson Survey',
            'api_url' => 'https://mamflow.com/wp-json/mamflow/v1',
            'option_key' => 'lp_survey_license'
        ]);
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
        add_filter('mamflow_license_tabs', array($this, 'register_license_tab'));
    }

    /**
     * Register Survey tab in unified license page
     */
    public function register_license_tab($tabs)
    {
        $tabs['survey'] = array(
            'title' => esc_html__('Survey', 'lp-survey'),
            'callback' => 'lp_survey_render_license_tab',
            'priority' => 20
        );
        return $tabs;
    }

    /**
     * Show admin notice if license not activated
     */
    public function license_notice()
    {
        // Only show if LearnPress is active
        if (!class_exists('LearnPress')) {
            return;
        }

        // Don't show on license page
        if (isset($_GET['page']) && $_GET['page'] === 'mamflow-license') {
            return;
        }

        // Check if license is active
        if (!$this->license_handler->is_feature_enabled()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e('LearnPress – Course & Lesson Survey:', 'lp-survey'); ?></strong>
                    <?php
                    printf(
                        esc_html__('Please %sactivate your license%s to unlock all features.', 'lp-survey'),
                        '<a href="' . esc_url(admin_url('admin.php?page=mamflow-license&tab=survey')) . '">',
                        '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Get license handler instance
     *
     * @return LP_Survey_License_Handler
     */
    public function get_license_handler()
    {
        return $this->license_handler;
    }

    /**
     * Check if LearnPress is active
     */
    public function check_learnpress()
    {
        if (!class_exists('LearnPress')) {
            add_action('admin_notices', array($this, 'learnpress_not_active_notice'));
            return;
        }
    }

    /**
     * LearnPress not active notice
     */
    public function learnpress_not_active_notice()
    {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('LearnPress Survey requires LearnPress plugin to be installed and activated.', 'lp-survey'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Initialize plugin components
     */
    public function init()
    {
        if (!class_exists('LearnPress')) {
            return;
        }

        // Initialize settings - it will self-register with LearnPress
        LP_Survey_Settings::instance();

        // Initialize admin
        if (is_admin()) {
            LP_Survey_Admin::instance();
            LP_Survey_Lesson_Metabox::instance();
            LP_Survey_Course_Metabox::instance();
            LP_Survey_Manager::instance();
        }

        // Initialize frontend (MUST be always initialized for AJAX to work!)
        // Note: admin-ajax.php is considered is_admin() = true, but we need Frontend for AJAX handlers
        LP_Survey_Frontend::instance();

        // Initialize LearnPress hooks
        LP_Survey_LearnPress_Hooks::instance();
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('lp-survey', false, dirname(LP_SURVEY_BASENAME) . '/languages');
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Check if LearnPress is active
        if (!class_exists('LearnPress')) {
            deactivate_plugins(LP_SURVEY_BASENAME);
            wp_die(
                sprintf(
                    esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'lp-survey'),
                    esc_html__('LearnPress – Course & Lesson Survey', 'lp-survey'),
                    esc_html__('LearnPress', 'lp-survey')
                )
            );
        }

        // Check LearnPress version
        if (version_compare(LEARNPRESS_VERSION, '4.2.0', '<')) {
            deactivate_plugins(LP_SURVEY_BASENAME);
            wp_die(
                sprintf(
                    esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'lp-survey'),
                    esc_html__('LearnPress – Course & Lesson Survey', 'lp-survey'),
                    esc_html__('LearnPress', 'lp-survey'),
                    '4.2.0'
                )
            );
        }

        // Create database tables
        LP_Survey_Database::create_tables();

        // Set default options
        $this->set_default_options();

        // Schedule license check
        LP_Survey_Cron::schedule_license_check();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Set default plugin options
     */
    private function set_default_options()
    {
        $defaults = array(
            'enable_lesson_survey' => 'yes',
            'enable_course_survey' => 'yes',
            'display_type' => 'popup',
            'allow_skip' => 'yes',
            'max_questions' => 5,
        );

        foreach ($defaults as $key => $value) {
            if (!get_option('mf_lp_survey_' . $key)) {
                update_option('mf_lp_survey_' . $key, $value);
            }
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Clear license cron
        LP_Survey_Cron::clear_license_check();

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

/**
 * Main instance of LP_Survey
 *
 * @return LP_Survey
 */
function LP_Survey()
{
    return LP_Survey::instance();
}

// Initialize plugin
LP_Survey();
