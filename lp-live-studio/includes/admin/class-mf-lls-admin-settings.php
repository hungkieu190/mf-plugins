<?php
/**
 * Admin Settings
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LLS_Admin_Settings
 *
 * Handle admin settings page
 */
class MF_LLS_Admin_Settings
{
    /**
     * @var MF_LLS_Admin_Settings Singleton instance
     */
    protected static $_instance = null;

    /**
     * @var string Current active tab
     */
    private $current_tab = 'general';

    /**
     * Get singleton instance
     *
     * @return MF_LLS_Admin_Settings
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
        add_action('admin_menu', array($this, 'add_menu'), 100);
        add_action('admin_init', array($this, 'save_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // AJAX handlers
        add_action('wp_ajax_mf_lls_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_mf_lls_send_test_email', array($this, 'ajax_send_test_email'));
    }

    /**
     * Add submenu under LearnPress
     */
    public function add_menu()
    {
        add_submenu_page(
            'learn_press',
            esc_html__('Live Studio Settings', 'lp-live-studio'),
            esc_html__('Live Studio', 'lp-live-studio'),
            'manage_options',
            'mf-lls-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_scripts($hook)
    {
        if ('learnpress_page_mf-lls-settings' !== $hook) {
            return;
        }

        // Enqueue admin CSS
        wp_enqueue_style(
            'mf-lls-admin-settings',
            MF_LLS_URL . 'assets/css/admin-settings.css',
            array(),
            MF_LLS_VERSION
        );

        // Enqueue admin JS
        wp_enqueue_script(
            'mf-lls-admin-settings',
            MF_LLS_URL . 'assets/js/admin-settings.js',
            array('jquery'),
            MF_LLS_VERSION,
            true
        );

        wp_localize_script(
            'mf-lls-admin-settings',
            'mfLlsAdmin',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mf_lls_admin_nonce'),
            )
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'lp-live-studio'));
        }

        // Check license status
        $license_handler = MF_LLS_Addon::instance()->get_license_handler();
        $is_licensed = $license_handler->is_feature_enabled();

        $this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

        // If not licensed, show overlay
        if (!$is_licensed) {
            $this->render_license_gate();
            return;
        }

        // Normal settings page
        include MF_LLS_PATH . '/includes/admin/views/settings-page.php';
    }

    /**
     * Render license gate overlay
     */
    private function render_license_gate()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Live Studio Settings', 'lp-live-studio'); ?></h1>

            <!-- Blurred settings preview -->
            <div class="mf-lls-settings-preview" style="filter: blur(5px); opacity: 0.3; pointer-events: none;">
                <?php include MF_LLS_PATH . '/includes/admin/views/settings-page.php'; ?>
            </div>

            <!-- License gate overlay -->
            <div class="mf-lls-license-gate-overlay">
                <div class="mf-lls-license-gate-modal">
                    <a href="<?php echo esc_url(admin_url('index.php')); ?>" class="mf-lls-license-gate-close"
                        title="<?php esc_attr_e('Close and return to Dashboard', 'lp-live-studio'); ?>">
                        <span class="dashicons dashicons-no"></span>
                    </a>
                    <div class="mf-lls-license-gate-icon">
                        <span class="dashicons dashicons-lock" style="font-size: 64px; color: #f0b849;"></span>
                    </div>
                    <h2><?php esc_html_e('License Activation Required', 'lp-live-studio'); ?></h2>
                    <p class="mf-lls-license-gate-message">
                        <?php esc_html_e('Please activate your license to access Live Studio settings and premium features.', 'lp-live-studio'); ?>
                    </p>
                    <div class="mf-lls-license-gate-features">
                        <ul>
                            <li><span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e('Zoom Integration', 'lp-live-studio'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e('Google Meet Integration', 'lp-live-studio'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e('Agora Integration', 'lp-live-studio'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e('Attendance Tracking', 'lp-live-studio'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e('Rating System', 'lp-live-studio'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e('Email Reminders', 'lp-live-studio'); ?></li>
                        </ul>
                    </div>
                    <div class="mf-lls-license-gate-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=mamflow-license&tab=live-studio')); ?>"
                            class="button button-primary button-hero">
                            <span class="dashicons dashicons-admin-network" style="margin-top: 3px;"></span>
                            <?php esc_html_e('Activate License', 'lp-live-studio'); ?>
                        </a>
                        <p class="mf-lls-license-gate-help">
                            <?php
                            printf(
                                /* translators: %s: Link to mamflow.com */
                                esc_html__('Don\'t have a license? %s', 'lp-live-studio'),
                                '<a href="https://mamflow.com/product/lp-live-studio/" target="_blank">' . esc_html__('Purchase Now', 'lp-live-studio') . '</a>'
                            );
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <style>
                .mf-lls-license-gate-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.7);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 999999;
                }

                .mf-lls-license-gate-modal {
                    background: #fff;
                    border-radius: 8px;
                    padding: 40px;
                    max-width: 600px;
                    text-align: center;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                    position: relative;
                }

                .mf-lls-license-gate-close {
                    position: absolute;
                    top: 15px;
                    right: 15px;
                    text-decoration: none;
                    color: #999;
                    transition: color 0.2s;
                }

                .mf-lls-license-gate-close:hover {
                    color: #333;
                }

                .mf-lls-license-gate-close .dashicons {
                    font-size: 24px;
                    width: 24px;
                    height: 24px;
                }

                .mf-lls-license-gate-icon {
                    height: 60px;
                    margin-bottom: 15px;
                }

                .mf-lls-license-gate-modal h2 {
                    font-size: 24px;
                    margin: 0 0 15px;
                    color: #23282d;
                }

                .mf-lls-license-gate-message {
                    font-size: 16px;
                    color: #666;
                    margin-bottom: 30px;
                    line-height: 1.6;
                }

                .mf-lls-license-gate-features {
                    background: #f7f7f7;
                    border-radius: 4px;
                    padding: 20px;
                    margin-bottom: 30px;
                }

                .mf-lls-license-gate-features ul {
                    list-style: none;
                    margin: 0;
                    padding: 0;
                    text-align: left;
                    display: inline-block;
                }

                .mf-lls-license-gate-features li {
                    padding: 8px 0;
                    font-size: 14px;
                    color: #23282d;
                }

                .mf-lls-license-gate-features li .dashicons {
                    color: #46b450;
                    font-size: 18px;
                    margin-right: 8px;
                    vertical-align: middle;
                }

                .mf-lls-license-gate-actions {
                    margin-top: 20px;
                }

                .mf-lls-license-gate-actions .button-hero {
                    padding: 12px 30px;
                    height: auto;
                    font-size: 16px;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }

                .mf-lls-license-gate-help {
                    margin-top: 20px;
                    font-size: 14px;
                    color: #666;
                }

                .mf-lls-license-gate-help a {
                    color: #0073aa;
                    text-decoration: none;
                }

                .mf-lls-license-gate-help a:hover {
                    text-decoration: underline;
                }

                @media (max-width: 782px) {
                    .mf-lls-license-gate-icon {
                        display: none;
                    }

                    .mf-lls-license-gate-modal {
                        padding: 20px;
                        margin: 20px;
                    }
                }
            </style>
        </div>
        <?php
    }

    /**
     * Get settings tabs
     *
     * @return array
     */
    public function get_tabs()
    {
        return array(
            'general' => esc_html__('General', 'lp-live-studio'),
            'zoom' => esc_html__('Zoom', 'lp-live-studio'),
            'google' => esc_html__('Google Meet', 'lp-live-studio'),
            'agora' => esc_html__('Agora', 'lp-live-studio'),
            'email' => esc_html__('Email Templates', 'lp-live-studio'),
        );
    }

    /**
     * Save settings
     */
    public function save_settings()
    {
        if (!isset($_POST['mf_lls_save_settings'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('mf_lls_save_settings', 'mf_lls_settings_nonce');

        $tab = isset($_POST['mf_lls_current_tab']) ? sanitize_text_field($_POST['mf_lls_current_tab']) : 'general';

        switch ($tab) {
            case 'general':
                $this->save_general_settings();
                break;
            case 'zoom':
                $this->save_zoom_settings();
                break;
            case 'google':
                $this->save_google_settings();
                break;
            case 'agora':
                $this->save_agora_settings();
                break;
            case 'email':
                $this->save_email_settings();
                break;
        }

        // Redirect with success message
        $redirect_url = add_query_arg(
            array(
                'page' => 'mf-lls-settings',
                'tab' => $tab,
                'updated' => 'true',
            ),
            admin_url('admin.php')
        );

        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Save general settings
     */
    private function save_general_settings()
    {
        $default_platform = isset($_POST['mf_lls_default_platform']) ? sanitize_text_field($_POST['mf_lls_default_platform']) : 'zoom';

        // Whitelist validation
        $allowed_platforms = array('zoom', 'google_meet', 'agora');
        if (!in_array($default_platform, $allowed_platforms, true)) {
            $default_platform = 'zoom';
        }

        update_option(MF_LLS_OPT_DEFAULT_PLATFORM, $default_platform);
        update_option('mf_lls_reminder_1h_enabled', isset($_POST['mf_lls_reminder_1h_enabled']) ? '1' : '0');
        update_option('mf_lls_reminder_15m_enabled', isset($_POST['mf_lls_reminder_15m_enabled']) ? '1' : '0');
        update_option('mf_lls_rating_enabled', isset($_POST['mf_lls_rating_enabled']) ? '1' : '0');
        update_option('mf_lls_rating_expire_days', absint($_POST['mf_lls_rating_expire_days'] ?? 7));
    }

    /**
     * Save Zoom settings
     */
    private function save_zoom_settings()
    {
        $auth_type = sanitize_text_field($_POST['mf_lls_zoom_auth_type'] ?? 'oauth');
        if (!in_array($auth_type, array('oauth', 'jwt'), true)) {
            $auth_type = 'oauth';
        }

        update_option(MF_LLS_OPT_ZOOM_AUTH_TYPE, $auth_type);
        update_option(MF_LLS_OPT_ZOOM_ACCOUNT_ID, sanitize_text_field($_POST['mf_lls_zoom_account_id'] ?? ''));
        update_option(MF_LLS_OPT_ZOOM_API_KEY, sanitize_text_field($_POST['mf_lls_zoom_api_key'] ?? ''));
        update_option(MF_LLS_OPT_ZOOM_API_SECRET, sanitize_text_field($_POST['mf_lls_zoom_api_secret'] ?? ''));
    }

    /**
     * Save Google Meet settings
     */
    private function save_google_settings()
    {
        update_option(MF_LLS_OPT_GOOGLE_CLIENT_ID, sanitize_text_field($_POST['mf_lls_google_client_id'] ?? ''));
        update_option(MF_LLS_OPT_GOOGLE_CLIENT_SECRET, sanitize_text_field($_POST['mf_lls_google_client_secret'] ?? ''));
    }

    /**
     * Save Agora settings
     */
    private function save_agora_settings()
    {
        update_option(MF_LLS_OPT_AGORA_APP_ID, sanitize_text_field($_POST['mf_lls_agora_app_id'] ?? ''));
        update_option(MF_LLS_OPT_AGORA_APP_CERT, sanitize_text_field($_POST['mf_lls_agora_app_cert'] ?? ''));
        update_option(MF_LLS_OPT_AGORA_CHANNEL_PREFIX, sanitize_text_field($_POST['mf_lls_agora_channel_prefix'] ?? 'lls'));
    }

    /**
     * Save email template settings
     */
    private function save_email_settings()
    {
        update_option('mf_lls_email_reminder_subject', sanitize_text_field($_POST['mf_lls_email_reminder_subject'] ?? ''));
        update_option('mf_lls_email_reminder_body', wp_kses_post($_POST['mf_lls_email_reminder_body'] ?? ''));
        update_option('mf_lls_email_rating_subject', sanitize_text_field($_POST['mf_lls_email_rating_subject'] ?? ''));
        update_option('mf_lls_email_rating_body', wp_kses_post($_POST['mf_lls_email_rating_body'] ?? ''));
    }

    /**
     * Get current tab
     *
     * @return string
     */
    public function get_current_tab()
    {
        return $this->current_tab;
    }

    /**
     * AJAX: Test platform connection
     */
    public function ajax_test_connection()
    {
        check_ajax_referer('mf_lls_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Permission denied.', 'lp-live-studio'));
        }

        $platform = isset($_POST['platform']) ? sanitize_text_field($_POST['platform']) : '';
        $allowed = array('zoom', 'google_meet', 'agora');

        if (!in_array($platform, $allowed, true)) {
            wp_send_json_error(esc_html__('Invalid platform.', 'lp-live-studio'));
        }

        $platform_instance = MF_LLS_Addon::instance()->get_platform($platform);

        if (is_null($platform_instance)) {
            wp_send_json_error(esc_html__('Platform class not found.', 'lp-live-studio'));
        }

        if (!$platform_instance->is_configured()) {
            wp_send_json_error(esc_html__('Platform credentials are not configured. Please save your API keys first.', 'lp-live-studio'));
        }

        $result = $platform_instance->test_connection();

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(esc_html__('Connection successful!', 'lp-live-studio'));
    }

    /**
     * AJAX: Send test email
     */
    public function ajax_send_test_email()
    {
        check_ajax_referer('mf_lls_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Permission denied.', 'lp-live-studio'));
        }

        $admin_email = get_option('admin_email');
        $site_name = get_option('blogname');

        $subject = get_option('mf_lls_email_reminder_subject', __('Live Session Starting Soon: {lesson_title}', 'lp-live-studio'));
        $body = get_option('mf_lls_email_reminder_body', '');

        if (empty($body)) {
            $body = "Hi {student_name},\n\nThis is a test email from LearnPress Live Studio.\n\nIf you received this, your email template is working correctly.\n\nBest regards,\n{site_name}";
        }

        // Replace placeholders with test data
        $placeholders = array(
            '{student_name}' => esc_html__('Test Student', 'lp-live-studio'),
            '{lesson_title}' => esc_html__('Sample Live Session', 'lp-live-studio'),
            '{course_title}' => esc_html__('Sample Course', 'lp-live-studio'),
            '{start_time}' => current_time('d/m/Y H:i'),
            '{join_url}' => esc_url(home_url()),
            '{site_name}' => esc_html($site_name),
        );

        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject);
        $body = str_replace(array_keys($placeholders), array_values($placeholders), $body);

        $headers = array('Content-Type: text/plain; charset=UTF-8');

        $sent = wp_mail($admin_email, $subject, $body, $headers);

        if ($sent) {
            wp_send_json_success(
                sprintf(
                    /* translators: %s: admin email address */
                    esc_html__('Test email sent to %s', 'lp-live-studio'),
                    $admin_email
                )
            );
        } else {
            wp_send_json_error(esc_html__('Failed to send email. Please check your WordPress email configuration.', 'lp-live-studio'));
        }
    }
}
