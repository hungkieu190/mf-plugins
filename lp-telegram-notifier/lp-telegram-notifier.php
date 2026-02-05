<?php
/**
 * Plugin Name: LP Telegram Notifier
 * Plugin URI: https://mamflow.com/product/lp-telegram-notifier
 * Description: Instant Telegram notifications for LearnPress instructors when students enroll in courses. Requires license activation.
 * Version: 1.0.0
 * Author: MamFlow
 * Author URI: https://mamflow.com
 * Text Domain: lp-telegram-notifier
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
define('LP_TG_NOTIFIER_VERSION', '1.0.0');
define('LP_TG_NOTIFIER_FILE', __FILE__);
define('LP_TG_NOTIFIER_PATH', plugin_dir_path(__FILE__));
define('LP_TG_NOTIFIER_URL', plugin_dir_url(__FILE__));
define('LP_TG_NOTIFIER_BASENAME', plugin_basename(__FILE__));

// License Product ID on mamflow.com.
define('LP_TG_NOTIFIER_PRODUCT_ID', 47313);

/**
 * Main Plugin Class
 *
 * @class LP_Telegram_Notifier
 */
class LP_Telegram_Notifier
{

    /**
     * The single instance of the class
     *
     * @var LP_Telegram_Notifier
     */
    protected static $instance = null;

    /**
     * License handler instance
     *
     * @var MF_LP_TG_License_Handler
     */
    private $license_handler;

    /**
     * Main instance
     *
     * @return LP_Telegram_Notifier
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
        $this->includes();
        $this->load_license_system();
        $this->hooks();
    }

    /**
     * Include required files
     */
    private function includes()
    {
        // License system files.
        require_once LP_TG_NOTIFIER_PATH . 'includes/license/class-license-handler.php';
        require_once LP_TG_NOTIFIER_PATH . 'includes/license/shared-license-page.php';
        require_once LP_TG_NOTIFIER_PATH . 'includes/license/admin-license-page.php';
        require_once LP_TG_NOTIFIER_PATH . 'includes/license/cron-scheduler.php';

        // Core files.
        require_once LP_TG_NOTIFIER_PATH . 'includes/class-telegram-api.php';
        require_once LP_TG_NOTIFIER_PATH . 'includes/class-notification-handler.php';
    }

    /**
     * Load and initialize license system
     */
    private function load_license_system()
    {
        $this->license_handler = new MF_LP_TG_License_Handler(
            array(
                'product_id' => LP_TG_NOTIFIER_PRODUCT_ID,
                'product_name' => 'LP Telegram Notifier',
                'api_url' => 'https://mamflow.com/wp-json/mamflow/v1',
                'option_key' => 'lp_telegram_notifier_license',
            )
        );
    }

    /**
     * Setup hooks
     */
    private function hooks()
    {
        add_action('plugins_loaded', array($this, 'check_learnpress'));
        add_action('init', array($this, 'load_textdomain'));

        // License admin menu.
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_license_menu'), 100);
            add_action('admin_notices', array($this, 'license_notice'));
        }

        // Activation/Deactivation hooks.
        register_activation_hook(LP_TG_NOTIFIER_FILE, array($this, 'activate'));
        register_deactivation_hook(LP_TG_NOTIFIER_FILE, array($this, 'deactivate'));
    }

    /**
     * Add license menu to LearnPress admin
     */
    public function add_license_menu()
    {
        if (!class_exists('LearnPress')) {
            return;
        }

        // Register unified Mamflow license page.
        mamflow_register_license_menu();

        // Register this plugin's tab.
        add_filter('mamflow_license_tabs', array($this, 'register_license_tab'));
    }

    /**
     * Register Telegram Notifier tab in unified license page
     *
     * @param array $tabs Existing tabs.
     * @return array Modified tabs.
     */
    public function register_license_tab($tabs)
    {
        $tabs['telegram-notifier'] = array(
            'title' => esc_html__('Telegram Notifier', 'lp-telegram-notifier'),
            'callback' => 'mf_lp_telegram_render_license_tab',
            'priority' => 20,
        );
        return $tabs;
    }

    /**
     * Show admin notice if license not activated
     */
    public function license_notice()
    {
        // Only show if LearnPress is active.
        if (!class_exists('LearnPress')) {
            return;
        }

        // Don't show on license page.
        if (isset($_GET['page']) && 'mamflow-license' === $_GET['page']) {
            return;
        }

        // Check if license is active.
        if (!$this->license_handler->is_feature_enabled()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e('LP Telegram Notifier:', 'lp-telegram-notifier'); ?></strong>
                    <?php
                    printf(
                        /* translators: %1$s: Opening link tag, %2$s: Closing link tag */
                        esc_html__('Please %1$sactivate your license%2$s to enable Telegram notifications.', 'lp-telegram-notifier'),
                        '<a href="' . esc_url(admin_url('admin.php?page=mamflow-license&tab=telegram-notifier')) . '">',
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
     * @return MF_LP_TG_License_Handler
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

        // Initialize plugin.
        $this->init();
    }

    /**
     * Initialize plugin
     */
    private function init()
    {
        // Initialize notification handler.
        MF_LP_Notification_Handler::get_instance();

        // Add Telegram settings tab to LearnPress.
        add_filter(
            'learn-press/admin/settings-tabs-array',
            function ($tabs) {
                $tabs['telegram'] = require_once LP_TG_NOTIFIER_PATH . 'includes/admin/class-lp-settings-telegram.php';
                return $tabs;
            },
            50
        );
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('lp-telegram-notifier', false, dirname(LP_TG_NOTIFIER_BASENAME) . '/languages');
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Check if LearnPress is active.
        if (!class_exists('LearnPress')) {
            deactivate_plugins(LP_TG_NOTIFIER_BASENAME);
            wp_die(
                sprintf(
                    /* translators: %1$s: Plugin name, %2$s: Required plugin name */
                    esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'lp-telegram-notifier'),
                    esc_html__('LP Telegram Notifier', 'lp-telegram-notifier'),
                    esc_html__('LearnPress', 'lp-telegram-notifier')
                )
            );
        }

        // Schedule license check.
        MF_LP_Telegram_Cron::schedule_license_check();

        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Clear license cron.
        MF_LP_Telegram_Cron::clear_license_check();

        flush_rewrite_rules();
    }

    /**
     * Admin notice for missing LearnPress
     */
    public function admin_notice_missing_learnpress()
    {
        $message = sprintf(
            /* translators: %1$s: Plugin name, %2$s: Required plugin name */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'lp-telegram-notifier'),
            '<strong>' . esc_html__('LP Telegram Notifier', 'lp-telegram-notifier') . '</strong>',
            '<strong>' . esc_html__('LearnPress', 'lp-telegram-notifier') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
    }
}

// Initialize plugin.
LP_Telegram_Notifier::instance();
