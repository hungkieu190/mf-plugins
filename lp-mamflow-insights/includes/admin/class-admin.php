<?php
/**
 * Admin controller class for LearnPress Mamflow Insight
 * 
 * @package MF_Insights
 */

defined('ABSPATH') || exit;

class MF_Insights_Admin
{
    /**
     * Instance
     */
    protected static $instance = null;

    /**
     * Available tabs
     */
    private $tabs = [];

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->tabs = [
            'course-health' => __('Course Health', 'lp-mamflow-insights'),
            'lessons' => __('Lessons', 'lp-mamflow-insights'),
            'students' => __('Students', 'lp-mamflow-insights'),
            'instructors' => __('Instructors', 'lp-mamflow-insights'),
        ];
        $this->hooks();
    }

    /**
     * Register hooks
     */
    private function hooks()
    {
        add_action('admin_menu', [$this, 'register_admin_menu'], 100);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Register admin menu
     */
    public function register_admin_menu()
    {
        add_submenu_page(
            'learn_press',
            __('Mamflow Insight', 'lp-mamflow-insights'),
            __('Mamflow Insight', 'lp-mamflow-insights'),
            'manage_options',
            'mf-insights',
            [$this, 'render_dashboard']
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_assets($hook)
    {
        if ($hook !== 'learnpress_page_mf-insights') {
            return;
        }

        // Select2 for searchable dropdowns
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);

        wp_enqueue_style('mf-insights-admin', MF_INSIGHTS_URL . 'assets/css/admin.css', ['select2'], MF_INSIGHTS_VERSION);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.1', true);
        wp_enqueue_script('mf-insights-admin', MF_INSIGHTS_URL . 'assets/js/admin.js', ['jquery', 'chart-js', 'select2'], MF_INSIGHTS_VERSION, true);

        wp_localize_script('mf-insights-admin', 'mf_insights', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mf_insights_nonce')
        ]);
    }

    /**
     * Get current tab
     */
    private function get_current_tab()
    {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'course-health';
        return array_key_exists($tab, $this->tabs) ? $tab : 'course-health';
    }

    /**
     * Render dashboard with tabs
     */
    public function render_dashboard()
    {
        // Check license
        $license_handler = mf_insights()->get_license_handler();
        if (!$license_handler || !$license_handler->is_feature_enabled()) {
            $this->render_license_notice();
            return;
        }

        $current_tab = $this->get_current_tab();
        ?>
        <div class="wrap mf-insights-dashboard">
            <h1 class="wp-heading-inline"><?php _e('Mamflow Insight', 'lp-mamflow-insights'); ?></h1>
            <hr class="wp-header-end">

            <nav class="mf-tabs-nav">
                <?php foreach ($this->tabs as $tab_key => $tab_label): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mf-insights&tab=' . $tab_key)); ?>"
                        class="mf-tab-link <?php echo $current_tab === $tab_key ? 'active' : ''; ?>">
                        <?php echo esc_html($tab_label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="mf-tab-content">
                <?php $this->render_tab_content($current_tab); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render tab content
     */
    private function render_tab_content($tab)
    {
        switch ($tab) {
            case 'course-health':
                mf_insights()->get_course_health()->render_content();
                break;
            case 'lessons':
                mf_insights()->get_lesson_analytics()->render();
                break;
            case 'students':
                mf_insights()->get_student_analytics()->render();
                break;
            case 'instructors':
                mf_insights()->get_instructor_performance()->render_content();
                break;
        }
    }

    /**
     * Render license notice
     */
    private function render_license_notice()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Mamflow Insight', 'lp-mamflow-insights'); ?></h1>
            <div class="notice notice-warning" style="padding: 20px; margin: 20px 0;">
                <h2 style="margin-top: 0;"><?php _e('License Required', 'lp-mamflow-insights'); ?></h2>
                <p><?php _e('This feature requires an active license.', 'lp-mamflow-insights'); ?></p>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mamflow-license&tab=mf-insights')); ?>"
                        class="button button-primary">
                        <?php _e('Activate License', 'lp-mamflow-insights'); ?>
                    </a>
                    <a href="https://mamflow.com" class="button" target="_blank">
                        <?php _e('Purchase License', 'lp-mamflow-insights'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Get instance
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
