<?php
/**
 * Admin Core Class
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LP_Survey_Admin Class
 */
class LP_Survey_Admin
{

    /**
     * The single instance of the class
     *
     * @var LP_Survey_Admin
     */
    protected static $_instance = null;

    /**
     * Main instance
     *
     * @return LP_Survey_Admin
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
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'), 100);

        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        // Initialize dashboard
        LP_Survey_Dashboard::instance();

        // Initialize course metabox
        LP_Survey_Course_Metabox::instance();
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        // Add submenu under LearnPress - only Dashboard
        add_submenu_page(
            'learn_press',
            __('Survey', 'lp-survey'),
            __('Survey', 'lp-survey'),
            'manage_options',
            'lp-survey-dashboard',
            array('LP_Survey_Dashboard', 'render_dashboard_page')
        );

        // Settings will be integrated into LearnPress Settings tab
        // via learn-press/admin/settings-tabs-array filter in LP_Survey_Settings class
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_assets($hook)
    {
        // Only load on LP Survey pages
        if (strpos($hook, 'lp-survey') === false && get_post_type() !== 'lp_course') {
            return;
        }

        // CSS
        wp_enqueue_style(
            'lp-survey-admin',
            LP_SURVEY_URL . 'assets/css/admin.css',
            array(),
            LP_SURVEY_VERSION
        );

        // JS
        wp_enqueue_script(
            'lp-survey-admin',
            LP_SURVEY_URL . 'assets/js/admin.js',
            array('jquery'),
            LP_SURVEY_VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'lp-survey-admin',
            'lpSurveyAdmin',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('lp_survey_admin'),
            )
        );
    }
}
