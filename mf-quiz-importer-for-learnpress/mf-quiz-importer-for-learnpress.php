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

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
define('MF_QUIZ_IMPORTER_VERSION', '1.0.0');
define('MF_QUIZ_IMPORTER_PLUGIN_FILE', __FILE__);
define('MF_QUIZ_IMPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MF_QUIZ_IMPORTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MF_QUIZ_IMPORTER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class MF_Quiz_Importer_For_LearnPress {
    
    /**
     * Single instance of the class
     *
     * @var MF_Quiz_Importer_For_LearnPress
     */
    private static $instance = null;
    
    /**
     * Get single instance
     *
     * @return MF_Quiz_Importer_For_LearnPress
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Check if LearnPress is active
        add_action('plugins_loaded', array($this, 'check_learnpress_active'));
        
        // Load plugin textdomain
        add_action('init', array($this, 'load_textdomain'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        }
    }
    
    /**
     * Check if LearnPress is active
     */
    public function check_learnpress_active() {
        if (!class_exists('LearnPress')) {
            add_action('admin_notices', array($this, 'learnpress_missing_notice'));
            return;
        }
        
        // Initialize plugin components
        $this->init();
    }
    
    /**
     * Initialize plugin components
     */
    private function init() {
        // Include required files
        $this->include_files();
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        // Admin files
        if (is_admin()) {
            require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/admin/class-admin.php';
            require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/admin/class-importer.php';
        }
        
        // Core files
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-quiz-parser.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-quiz-creator.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-question-importer.php';
        require_once MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/class-excel-parser.php';
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'mf-quiz-importer-lp',
            false,
            dirname(MF_QUIZ_IMPORTER_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
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
     * Render admin page
     */
    public function render_admin_page() {
        include MF_QUIZ_IMPORTER_PLUGIN_DIR . 'includes/admin/views/importer-page.php';
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin page
        // Check for both possible hook names
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
        
        wp_localize_script('mf-quiz-importer-admin', 'mfQuizImporter', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mf-quiz-importer-nonce'),
            'pluginUrl' => MF_QUIZ_IMPORTER_PLUGIN_URL,
            'i18n' => array(
                'uploading' => __('Uploading...', 'mf-quiz-importer-lp'),
                'processing' => __('Processing...', 'mf-quiz-importer-lp'),
                'success' => __('Import completed successfully!', 'mf-quiz-importer-lp'),
                'error' => __('An error occurred during import.', 'mf-quiz-importer-lp'),
            )
        ));
    }
    
    /**
     * LearnPress missing notice
     */
    public function learnpress_missing_notice() {
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
function mf_quiz_importer_activate() {
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires PHP version 7.4 or higher.', 'mf-quiz-importer-lp'));
    }
    
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), '5.8', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WordPress version 5.8 or higher.', 'mf-quiz-importer-lp'));
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/mf-quiz-importer';
    
    if (!file_exists($plugin_upload_dir)) {
        wp_mkdir_p($plugin_upload_dir);
    }
    
    // Set default options
    add_option('mf_quiz_importer_version', MF_QUIZ_IMPORTER_VERSION);
    add_option('mf_quiz_importer_settings', array(
        'default_quiz_duration' => 60,
        'default_passing_grade' => 70,
        'default_retake_count' => 0,
        'auto_publish' => false,
    ));
}
register_activation_hook(__FILE__, 'mf_quiz_importer_activate');

/**
 * Plugin deactivation hook
 */
function mf_quiz_importer_deactivate() {
    // Clean up temporary files
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
}
register_deactivation_hook(__FILE__, 'mf_quiz_importer_deactivate');

/**
 * Initialize the plugin
 */
function mf_quiz_importer_init() {
    return MF_Quiz_Importer_For_LearnPress::instance();
}

// Start the plugin
mf_quiz_importer_init();