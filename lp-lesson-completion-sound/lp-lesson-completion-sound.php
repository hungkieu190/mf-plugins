<?php
/**
 * Plugin Name: Lesson Completion Sound Add-on for LearnPress
 * Plugin URI: https://mamflow.com/product/learnpress-lesson-completion-sound/
 * Description: Play celebration sounds and confetti effects when students complete lessons to boost motivation and engagement
 * Author: Mamflow
 * Version: 1.0.0
 * Author URI: https://mamflow.com/
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: lp-lesson-completion-sound
 * Domain Path: /languages/
 * Require_LP_Version: 4.2.0
 *
 * @package LP_Lesson_Completion_Sound
 */

defined('ABSPATH') || exit();

// Define constants
define('LP_LCS_VERSION', '1.0.0');
define('LP_LCS_FILE', __FILE__);
define('LP_LCS_PATH', plugin_dir_path(__FILE__));
define('LP_LCS_URL', plugin_dir_url(__FILE__));
define('LP_LCS_BASENAME', plugin_basename(__FILE__));

// License Product ID on mamflow.com
define('LP_LCS_PRODUCT_ID', 47218);

/**
 * Class LP_Lesson_Completion_Sound
 */
class LP_Lesson_Completion_Sound
{
    /**
     * Instance
     *
     * @var LP_Lesson_Completion_Sound
     */
    protected static $instance = null;

    /**
     * License handler instance
     *
     * @var LP_LCS_License_Handler
     */
    private $license_handler;

    /**
     * LP_Lesson_Completion_Sound constructor.
     */
    protected function __construct()
    {
        $this->includes();
        $this->load_license_system();
        $this->hooks();
    }

    /**
     * Include required files
     */
    private function includes()
    {
        require_once LP_LCS_PATH . 'inc/license/class-license-handler.php';
        require_once LP_LCS_PATH . 'inc/license/shared-license-page.php';
        require_once LP_LCS_PATH . 'inc/license/admin-license-page.php';
        require_once LP_LCS_PATH . 'inc/license/cron-scheduler.php';

        require_once LP_LCS_PATH . 'inc/class-lp-lcs-settings.php';
        require_once LP_LCS_PATH . 'inc/class-lp-lcs-hooks.php';
        require_once LP_LCS_PATH . 'inc/class-lp-lcs-profile.php';
        require_once LP_LCS_PATH . 'inc/class-lp-lcs-admin.php';
    }

    /**
     * Register hooks
     */
    private function hooks()
    {
        add_action('plugins_loaded', array($this, 'check_learnpress'));
        add_action('init', array($this, 'load_textdomain'));

        // License admin menu
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_license_menu'), 100);
            add_action('admin_notices', array($this, 'license_notice'));
        }

        // Activation/Deactivation hooks
        register_activation_hook(LP_LCS_FILE, array($this, 'activate'));
        register_deactivation_hook(LP_LCS_FILE, array($this, 'deactivate'));
    }

    /**
     * Load and initialize license system
     */
    private function load_license_system()
    {
        // Initialize license handler
        $this->license_handler = new LP_LCS_License_Handler([
            'product_id' => LP_LCS_PRODUCT_ID,
            'product_name' => 'Lesson Completion Sound Add-on for LearnPress',
            'api_url' => 'https://mamflow.com/wp-json/mamflow/v1',
            'option_key' => 'lp_lcs_license'
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
     * Register Completion Sound tab in unified license page
     */
    public function register_license_tab($tabs)
    {
        $tabs['completion-sound'] = array(
            'title' => esc_html__('Completion Sound', 'lp-lesson-completion-sound'),
            'callback' => 'lp_lcs_render_license_tab',
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
                    <strong><?php esc_html_e('Lesson Completion Sound Add-on for LearnPress:', 'lp-lesson-completion-sound'); ?></strong>
                    <?php
                    printf(
                        esc_html__('Please %sactivate your license%s to unlock all features.', 'lp-lesson-completion-sound'),
                        '<a href="' . esc_url(admin_url('admin.php?page=mamflow-license&tab=completion-sound')) . '">',
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
     * @return LP_LCS_License_Handler
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
            add_action('admin_notices', array($this, 'admin_notice_missing_learnpress'));
            return;
        }

        // Check LearnPress version
        if (version_compare(LEARNPRESS_VERSION, '4.2.0', '<')) {
            add_action('admin_notices', array($this, 'admin_notice_minimum_learnpress_version'));
            return;
        }

        // Initialize plugin
        $this->init();
    }

    /**
     * Initialize plugin
     */
    private function init()
    {
        LP_LCS_Settings::instance();
        LP_LCS_Hooks::instance();
        LP_LCS_Profile::instance();
        LP_LCS_Admin::instance();
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('lp-lesson-completion-sound', false, dirname(LP_LCS_BASENAME) . '/languages');
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Check if LearnPress is active
        if (!class_exists('LearnPress')) {
            deactivate_plugins(LP_LCS_BASENAME);
            wp_die(
                sprintf(
                    esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'lp-lesson-completion-sound'),
                    esc_html__('Lesson Completion Sound Add-on for LearnPress', 'lp-lesson-completion-sound'),
                    esc_html__('LearnPress', 'lp-lesson-completion-sound')
                )
            );
        }

        // Check LearnPress version
        if (version_compare(LEARNPRESS_VERSION, '4.2.0', '<')) {
            deactivate_plugins(LP_LCS_BASENAME);
            wp_die(
                sprintf(
                    esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'lp-lesson-completion-sound'),
                    esc_html__('Lesson Completion Sound Add-on for LearnPress', 'lp-lesson-completion-sound'),
                    esc_html__('LearnPress', 'lp-lesson-completion-sound'),
                    '4.2.0'
                )
            );
        }

        // Schedule license check
        LP_LCS_Cron::schedule_license_check();

        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Clear license cron
        LP_LCS_Cron::clear_license_check();

        flush_rewrite_rules();
    }

    /**
     * Admin notice for missing LearnPress
     */
    public function admin_notice_missing_learnpress()
    {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'lp-lesson-completion-sound'),
            '<strong>' . esc_html__('Lesson Completion Sound Add-on for LearnPress', 'lp-lesson-completion-sound') . '</strong>',
            '<strong>' . esc_html__('LearnPress', 'lp-lesson-completion-sound') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
    }

    /**
     * Admin notice for minimum LearnPress version
     */
    public function admin_notice_minimum_learnpress_version()
    {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'lp-lesson-completion-sound'),
            '<strong>' . esc_html__('Lesson Completion Sound Add-on for LearnPress', 'lp-lesson-completion-sound') . '</strong>',
            '<strong>' . esc_html__('LearnPress', 'lp-lesson-completion-sound') . '</strong>',
            '4.2.0'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
    }

    /**
     * Get instance
     *
     * @return LP_Lesson_Completion_Sound
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

// Initialize plugin
LP_Lesson_Completion_Sound::instance();
