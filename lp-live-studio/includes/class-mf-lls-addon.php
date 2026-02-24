<?php
/**
 * Main Addon Class
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Addon
 *
 * Main addon class extending LP_Addon
 */
class MF_LLS_Addon extends LP_Addon
{
    /**
     * @var string Addon version
     */
    public $version = MF_LLS_VERSION;

    /**
     * @var string Required LearnPress version
     */
    public $require_version = MF_LLS_REQUIRE_LP_VERSION;

    /**
     * @var string Plugin file path
     */
    public $plugin_file = MF_LLS_FILE;

    /**
     * @var string Text domain
     */
    public $text_domain = 'lp-live-studio';

    /**
     * @var MF_LLS_Addon Singleton instance
     */
    protected static $_instance = null;

    /**
     * @var MF_LLS_License_Handler License handler instance
     */
    private $license_handler;

    /**
     * Get singleton instance
     *
     * @return MF_LLS_Addon
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load license system first
        $this->load_license_system();

        // Add license menu hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_license_menu'), 100);
            add_action('admin_notices', array($this, 'license_notice'));
        }
    }

    /**
     * Load license system
     */
    private function load_license_system()
    {
        // Require license files
        require_once MF_LLS_PATH . '/includes/license/class-license-handler.php';
        require_once MF_LLS_PATH . '/includes/license/shared-license-page.php';
        require_once MF_LLS_PATH . '/includes/license/admin-license-page.php';
        require_once MF_LLS_PATH . '/includes/license/cron-scheduler.php';

        // Initialize license handler
        $this->license_handler = new MF_LLS_License_Handler(
            array(
                'product_id' => MF_LLS_PRODUCT_ID,
                'product_name' => 'LearnPress Live Studio',
                'api_url' => 'https://mamflow.com/wp-json/mamflow/v1',
                'option_key' => 'mf_lls_license',
            )
        );
    }

    /**
     * Add license menu
     */
    public function add_license_menu()
    {
        if (!class_exists('LearnPress')) {
            return;
        }

        // Register unified Mamflow License page
        mamflow_register_license_menu();

        // Add this plugin's tab
        add_filter('mamflow_license_tabs', array($this, 'register_license_tab'));
    }

    /**
     * Register license tab
     *
     * @param array $tabs Existing tabs
     * @return array Modified tabs
     */
    public function register_license_tab($tabs)
    {
        $tabs['live-studio'] = array(
            'title' => esc_html__('Live Studio', 'lp-live-studio'),
            'callback' => 'mf_lls_render_license_tab',
            'priority' => 20,
        );
        return $tabs;
    }

    /**
     * Show admin notice if license not active
     */
    public function license_notice()
    {
        if (!class_exists('LearnPress')) {
            return;
        }

        // Don't show on license page itself
        if (isset($_GET['page']) && 'mamflow-license' === $_GET['page']) {
            return;
        }

        if (!$this->license_handler->is_feature_enabled()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e('LearnPress Live Studio:', 'lp-live-studio'); ?></strong>
                    <?php
                    printf(
                        /* translators: %1$s: opening link tag, %2$s: closing link tag */
                        esc_html__('Please %1$sactivate your license%2$s to enable premium features.', 'lp-live-studio'),
                        '<a href="' . esc_url(admin_url('admin.php?page=mamflow-license&tab=live-studio')) . '">',
                        '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Get license handler
     *
     * @return MF_LLS_License_Handler
     */
    public function get_license_handler()
    {
        return $this->license_handler;
    }

    /**
     * Get platform instance
     *
     * @param string $platform_slug zoom|google_meet|agora
     * @return MF_LLS_Platform|null
     */
    public function get_platform($platform_slug)
    {
        $platform = null;

        switch ($platform_slug) {
            case 'zoom':
                if (file_exists(MF_LLS_PATH . '/includes/platforms/class-mf-lls-zoom.php')) {
                    require_once MF_LLS_PATH . '/includes/platforms/class-mf-lls-zoom.php';
                    $platform = new MF_LLS_Zoom();
                }
                break;
            case 'google_meet':
                if (file_exists(MF_LLS_PATH . '/includes/platforms/class-mf-lls-google-meet.php')) {
                    require_once MF_LLS_PATH . '/includes/platforms/class-mf-lls-google-meet.php';
                    $platform = new MF_LLS_Google_Meet();
                }
                break;
            case 'agora':
                if (file_exists(MF_LLS_PATH . '/includes/platforms/class-mf-lls-agora.php')) {
                    require_once MF_LLS_PATH . '/includes/platforms/class-mf-lls-agora.php';
                    $platform = new MF_LLS_Agora();
                }
                break;
        }

        return $platform;
    }

    /**
     * Define addon constants
     */
    protected function _define_constants()
    {
        // Option keys
        define('MF_LLS_OPT_DEFAULT_PLATFORM', 'mf_lls_default_platform');
        define('MF_LLS_OPT_ZOOM_AUTH_TYPE', 'mf_lls_zoom_auth_type');
        define('MF_LLS_OPT_ZOOM_ACCOUNT_ID', 'mf_lls_zoom_account_id');
        define('MF_LLS_OPT_ZOOM_API_KEY', 'mf_lls_zoom_api_key');
        define('MF_LLS_OPT_ZOOM_API_SECRET', 'mf_lls_zoom_api_secret');
        define('MF_LLS_OPT_ZOOM_WEBHOOK_SECRET', 'mf_lls_zoom_webhook_secret');
        define('MF_LLS_OPT_GOOGLE_CLIENT_ID', 'mf_lls_google_client_id');
        define('MF_LLS_OPT_GOOGLE_CLIENT_SECRET', 'mf_lls_google_client_secret');
        define('MF_LLS_OPT_GOOGLE_REFRESH_TOKEN', 'mf_lls_google_refresh_token');
        define('MF_LLS_OPT_AGORA_APP_ID', 'mf_lls_agora_app_id');
        define('MF_LLS_OPT_AGORA_APP_CERT', 'mf_lls_agora_app_cert');
        define('MF_LLS_OPT_AGORA_CHANNEL_PREFIX', 'mf_lls_agora_channel_prefix');
    }

    /**
     * Include addon files
     */
    protected function _includes()
    {
        // Admin
        if (is_admin()) {
            require_once MF_LLS_PATH . '/includes/admin/class-mf-lls-admin-settings.php';
            MF_LLS_Admin_Settings::instance();
        }

        // Platforms (abstract only — concrete classes loaded on demand)
        require_once MF_LLS_PATH . '/includes/platforms/abstract-mf-lls-platform.php';

        // Register platform REST routes (Zoom webhook etc.)
        add_action('rest_api_init', array($this, 'register_platform_rest_routes'));

        // Frontend
        require_once MF_LLS_PATH . '/includes/frontend/class-mf-lls-live-lesson.php';
        new MF_LLS_Live_Lesson();

        // Cron
        require_once MF_LLS_PATH . '/includes/class-mf-lls-cron.php';
        MF_LLS_Cron::instance();

        // Add custom cron schedule
        add_filter('cron_schedules', array($this, 'add_cron_schedules'));
    }

    /**
     * Register REST API routes for all platforms.
     * Loaded on rest_api_init only (not on every page load).
     */
    public function register_platform_rest_routes()
    {
        require_once MF_LLS_PATH . '/includes/platforms/class-mf-lls-zoom.php';
        $zoom = new MF_LLS_Zoom();
        $zoom->register_webhook_route();
    }

    /**
     * Add custom cron schedules
     *
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public function add_cron_schedules($schedules)
    {
        $schedules['mf_lls_5min'] = array(
            'interval' => 300,
            'display' => esc_html__('Every 5 Minutes', 'lp-live-studio'),
        );

        return $schedules;
    }
}
