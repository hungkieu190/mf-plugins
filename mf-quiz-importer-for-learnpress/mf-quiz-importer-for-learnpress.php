<?php
/**
 * Plugin Name: Quiz Importer For LearnPress
 * Plugin URI: https://mamflow.com/product/quiz-importer-for-learnpress
 * Description: Import quizzes from various formats (CSV, Excel, JSON) into LearnPress LMS
 * Version: 1.0.0
 * Author: MamFlow
 * Author URI: https://mamflow.com
 * Text Domain: mf-quiz-importer-lp
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MF_QUIZ_IMPORTER_VERSION', '1.0.0');
define('MF_QUIZ_IMPORTER_PLUGIN_FILE', __FILE__);
define('MF_QUIZ_IMPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MF_QUIZ_IMPORTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MF_QUIZ_IMPORTER_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('MF_QUIZ_IMPORTER_PRODUCT_ID', 47166);

/**
 * Main Plugin Class
 */
class MF_Quiz_Importer_For_LearnPress
{

    /**
     * Single instance of the class
     *
     * @var MF_Quiz_Importer_For_LearnPress
     */
    protected static $instance = null;

    /**
     * License handler instance
     *
     * @var MF_LP_QI_License_Handler
     */
    private $license_handler;

    /**
     * Get single instance
     *
     * @return MF_Quiz_Importer_For_LearnPress
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->include_files();
        $this->load_license_system();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function include_files()
    {
        // License files FIRST.
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/license/class-license-handler.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/license/shared-license-page.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/license/admin-license-page.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/license/cron-scheduler.php';

        // Core files.
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-quiz-parser.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-quiz-creator.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-question-importer.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-excel-parser.php';

        // Admin files.
        if (is_admin()) {
            require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/admin/class-admin.php';
            require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/admin/class-importer.php';
        }
    }

    /**
     * Initialize license system
     */
    private function load_license_system()
    {
        $this->license_handler = new MF_LP_QI_License_Handler(
            array(
                'product_id' => MF_QUIZ_IMPORTER_PRODUCT_ID,
                'product_name' => 'Quiz Importer For LearnPress',
                'api_url' => 'https://mamflow.com/wp-json/mamflow/v1',
                'option_key' => 'mf_quiz_importer_license',
            )
        );
    }

    /**
     * Get license handler
     *
     * @return MF_LP_QI_License_Handler
     */
    public function get_license_handler()
    {
        return $this->license_handler;
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Check if LearnPress is active.
        add_action('plugins_loaded', array($this, 'check_learnpress_active'));

        // Load plugin textdomain.
        add_action('init', array($this, 'load_textdomain'));

        // Admin hooks.
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_menu', array($this, 'add_license_menu'), 100);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
            add_action('admin_notices', array($this, 'license_notice'));
        }
    }

    /**
     * Check if LearnPress is active
     */
    public function check_learnpress_active()
    {
        if (!class_exists('LearnPress')) {
            add_action('admin_notices', array($this, 'learnpress_missing_notice'));
        }
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain()
    {
        load_plugin_textdomain(
            'mf-quiz-importer-lp',
            false,
            dirname(MF_QUIZ_IMPORTER_PLUGIN_BASENAME) . '/languages'
        );
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            'learn_press',
            __('Quiz Importer', 'mf-quiz-importer-lp'),
            __('Quiz Importer', 'mf-quiz-importer-lp'),
            'manage_options',
            'mf-quiz-importer',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Add Mamflow license menu and register tab
     */
    public function add_license_menu()
    {
        if (!class_exists('LearnPress')) {
            return;
        }

        mamflow_register_license_menu();
        add_filter('mamflow_license_tabs', array($this, 'register_license_tab'));
    }

    /**
     * Register license tab for this plugin
     *
     * @param array $tabs Existing license tabs.
     * @return array
     */
    public function register_license_tab($tabs)
    {
        $tabs['quiz-importer'] = array(
            'title' => esc_html__('Quiz Importer for LP', 'mf-quiz-importer-lp'),
            'callback' => 'mf_quiz_importer_render_license_tab',
            'priority' => 20,
        );
        return $tabs;
    }

    /**
     * Show admin notice when license is not active
     */
    public function license_notice()
    {
        if (!class_exists('LearnPress')) {
            return;
        }

        // Do not show on the license page itself.
        if (isset($_GET['page']) && 'mamflow-license' === $_GET['page']) { // phpcs:ignore WordPress.Security.NonceVerification
            return;
        }

        if (!$this->license_handler->is_feature_enabled()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e('Quiz Importer For LearnPress:', 'mf-quiz-importer-lp'); ?></strong>
                    <?php
                    printf(
                        esc_html__('Please %1$sactivate your license%2$s to enable quiz import features.', 'mf-quiz-importer-lp'),
                        '<a href="' . esc_url(admin_url('admin.php?page=mamflow-license&tab=quiz-importer')) . '">',
                        '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Render admin page — gated by license
     */
    public function render_admin_page()
    {
        // Check license FIRST.
        if (!$this->license_handler->is_feature_enabled()) {
            $this->render_license_gate();
            return;
        }

        include MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/admin/views/importer-page.php';
    }

    /**
     * Render license gate overlay for the importer page
     */
    private function render_license_gate()
    {
        $license_url = esc_url(admin_url('admin.php?page=mamflow-license&tab=quiz-importer'));
        $dashboard_url = esc_url(admin_url('index.php'));
        ?>
        <div style="position: relative; min-height: 500px; overflow: hidden;">
            <!-- Blurred background preview -->
            <div style="filter: blur(5px); pointer-events: none; padding: 20px; opacity: 0.4;">
                <h2><?php esc_html_e('Quiz Importer', 'mf-quiz-importer-lp'); ?></h2>
                <p><?php esc_html_e('Import quizzes from CSV, Excel, JSON into LearnPress LMS...', 'mf-quiz-importer-lp'); ?>
                </p>
                <div style="background:#f0f0f0; height:200px; border-radius:4px;"></div>
            </div>

            <!-- License gate overlay -->
            <div
                style="position: absolute; inset: 0; background: rgba(255,255,255,0.95); display: flex; align-items: center; justify-content: center; z-index: 10;">
                <div
                    style="max-width: 480px; text-align: center; padding: 40px; background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    <a href="<?php echo $dashboard_url; ?>"
                        style="position: absolute; top: 12px; right: 16px; font-size: 20px; text-decoration: none; color: #666; line-height: 1;">&times;</a>
                    <div style="font-size: 48px; margin-bottom: 16px;">🔒</div>
                    <h2 style="margin-top: 0; color: #1d2327;">
                        <?php esc_html_e('License Required', 'mf-quiz-importer-lp'); ?>
                    </h2>
                    <p style="color: #50575e; margin-bottom: 24px;">
                        <?php esc_html_e('Activate your license to unlock full quiz import capabilities:', 'mf-quiz-importer-lp'); ?>
                    </p>
                    <ul style="text-align: left; color: #50575e; margin-bottom: 28px; padding-left: 20px;">
                        <li><?php esc_html_e('Import from CSV, Excel, JSON formats', 'mf-quiz-importer-lp'); ?></li>
                        <li><?php esc_html_e('Bulk quiz creation in LearnPress', 'mf-quiz-importer-lp'); ?></li>
                        <li><?php esc_html_e('Multiple question types support', 'mf-quiz-importer-lp'); ?></li>
                        <li><?php esc_html_e('Automatic scoring & grading settings', 'mf-quiz-importer-lp'); ?></li>
                    </ul>
                    <a href="<?php echo $license_url; ?>" class="button button-primary"
                        style="font-size: 14px; padding: 8px 24px; height: auto;">
                        <?php esc_html_e('Activate License Now', 'mf-quiz-importer-lp'); ?>
                    </a>
                    <p style="margin-top: 16px; font-size: 12px; color: #8c8f94;">
                        <a href="https://mamflow.com/product/quiz-importer-for-learnpress" target="_blank">
                            <?php esc_html_e('Purchase a license at mamflow.com', 'mf-quiz-importer-lp'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'mf-quiz-importer') === false) {
            return;
        }

        wp_enqueue_style(
            'mf-quiz-importer-admin',
            MF_QUIZ_IMPORTER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            MF_QUIZ_IMPORTER_VERSION
        );

        wp_enqueue_script(
            'mf-quiz-importer-admin',
            MF_QUIZ_IMPORTER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            MF_QUIZ_IMPORTER_VERSION,
            true
        );

        wp_localize_script(
            'mf-quiz-importer-admin',
            'mfQuizImporter',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mf-quiz-importer-nonce'),
                'pluginUrl' => MF_QUIZ_IMPORTER_PLUGIN_URL,
                'i18n' => array(
                    'uploading' => __('Uploading...', 'mf-quiz-importer-lp'),
                    'processing' => __('Processing...', 'mf-quiz-importer-lp'),
                    'success' => __('Import completed successfully!', 'mf-quiz-importer-lp'),
                    'error' => __('An error occurred during import.', 'mf-quiz-importer-lp'),
                ),
            )
        );
    }

    /**
     * LearnPress missing notice
     */
    public function learnpress_missing_notice()
    {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                echo sprintf(
                    __('<strong>Quiz Importer For LearnPress</strong> requires <strong>LearnPress</strong> plugin to be installed and activated. Please install %s first.', 'mf-quiz-importer-lp'),
                    '<a href="' . admin_url('plugin-install.php?s=learnpress&tab=search&type=term') . '">LearnPress</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }
}

/**
 * Plugin activation hook
 */
function mf_quiz_importer_activate()
{
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires PHP version 7.4 or higher.', 'mf-quiz-importer-lp'));
    }

    if (version_compare(get_bloginfo('version'), '5.8', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WordPress version 5.8 or higher.', 'mf-quiz-importer-lp'));
    }

    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/mf-quiz-importer';

    if (!file_exists($plugin_upload_dir)) {
        wp_mkdir_p($plugin_upload_dir);
    }

    add_option('mf_quiz_importer_version', MF_QUIZ_IMPORTER_VERSION);
    add_option(
        'mf_quiz_importer_settings',
        array(
            'default_quiz_duration' => 60,
            'default_passing_grade' => 70,
            'default_retake_count' => 0,
            'auto_publish' => false,
        )
    );

    // Schedule daily license check.
    require_once plugin_dir_path(__FILE__) . 'includes/license/cron-scheduler.php';
    MF_LP_QI_Cron::schedule_license_check();
}
register_activation_hook(__FILE__, 'mf_quiz_importer_activate');

/**
 * Plugin deactivation hook
 */
function mf_quiz_importer_deactivate()
{
    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/mf-quiz-importer/temp';

    if (file_exists($plugin_upload_dir)) {
        $files = glob($plugin_upload_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // Clear scheduled license check.
    MF_LP_QI_Cron::clear_license_check();
}
register_deactivation_hook(__FILE__, 'mf_quiz_importer_deactivate');

/**
 * Initialize the plugin
 */
function mf_quiz_importer_init()
{
    return MF_Quiz_Importer_For_LearnPress::instance();
}

mf_quiz_importer_init();