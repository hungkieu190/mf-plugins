<?php
/**
 * Settings page for LP Sticky Notes - integrated into LearnPress Settings
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();

/**
 * Check if LearnPress Settings API is available
 */
if (!class_exists('LP_Abstract_Settings_Page')) {
    class LP_Sticky_Notes_Settings
    {
        public static function instance()
        {
            return new self();
        }
        public static function get_setting($key, $default = '')
        {
            return $default;
        }
    }
    return;
}

/**
 * Class LP_Sticky_Notes_Settings
 * Extends LP_Abstract_Settings_Page to integrate with LearnPress Settings
 */
class LP_Sticky_Notes_Settings extends LP_Abstract_Settings_Page
{
    /**
     * Instance
     *
     * @var LP_Sticky_Notes_Settings
     */
    protected static $instance = null;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->id = 'sticky-notes';
        $this->text = esc_html__('Sticky Notes', 'lp-sticky-notes');

        add_filter('learn-press/admin/settings-tabs-array', array($this, 'register_tab'));
        add_filter('learn-press/settings/' . $this->id . '/sections', array($this, 'get_sections'));

        parent::__construct();
    }

    /**
     * Register tab
     *
     * @param array $tabs
     * @return array
     */
    public function register_tab($tabs)
    {
        $tabs[$this->id] = $this;
        return $tabs;
    }

    /**
     * Get sections
     *
     * @return array
     */
    public function get_sections()
    {
        // Check license status
        $license_handler = LP_Sticky_Notes::instance()->get_license_handler();
        $is_licensed = $license_handler->is_feature_enabled();

        // If no license, show notice in general tab
        if (!$is_licensed) {
            return array(
                'general' => esc_html__('General', 'lp-sticky-notes'),
            );
        }

        // If licensed, show all tabs
        return array(
            'general' => esc_html__('General', 'lp-sticky-notes'),
            'appearance' => esc_html__('Appearance', 'lp-sticky-notes'),
            'shortcode' => esc_html__('Shortcode', 'lp-sticky-notes'),
        );
    }

    /**
     * Get settings for section
     *
     * @param string $section
     * @param string $tab
     * @return array
     */
    public function get_settings($section = '', $tab = '')
    {
        // Check license status
        $license_handler = LP_Sticky_Notes::instance()->get_license_handler();
        $is_licensed = $license_handler->is_feature_enabled();

        // Default section
        if ('' === $section) {
            $section = 'general';
        }

        // If not licensed, only show general with notice
        if (!$is_licensed && $section === 'general') {
            return $this->license_required_notice();
        }

        // Other tabs require license
        if (!$is_licensed) {
            return array();
        }

        if ('general' === $section) {
            return $this->general_settings();
        }

        if ('appearance' === $section) {
            return $this->appearance_settings();
        }

        if ('shortcode' === $section) {
            return $this->shortcode_settings();
        }

        return array();
    }

    /**
     * License required notice
     *
     * @return array
     */
    private function license_required_notice()
    {
        return array(
            array(
                'type' => 'title',
                'title' => esc_html__('License Required', 'lp-sticky-notes'),
                'desc' => sprintf(
                    '<div class="notice notice-warning inline" style="margin: 20px 0; padding: 15px;"><p><strong>%s</strong><br>%s</p><p><a href="%s" class="button button-primary">%s</a> <a href="%s" class="button" target="_blank">%s</a></p></div>',
                    esc_html__('⚠ License Activation Required', 'lp-sticky-notes'),
                    esc_html__('Please activate your license to access all plugin settings and features.', 'lp-sticky-notes'),
                    esc_url(admin_url('admin.php?page=lp-sticky-notes-license')),
                    esc_html__('Activate License', 'lp-sticky-notes'),
                    'https://mamflow.com/product/learnpress-notes-addon-lp-sticky-notes/',
                    esc_html__('Purchase License', 'lp-sticky-notes')
                ),
            ),
            array(
                'type' => 'sectionend',
            ),
        );
    }

    /**
     * General settings
     *
     * @return array
     */
    private function general_settings()
    {
        return array(
            array(
                'type' => 'title',
                'title' => esc_html__('Sticky Notes Settings', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_enable',
                'title' => esc_html__('Enable Sticky Notes', 'lp-sticky-notes'),
                'type' => 'checkbox',
                'default' => 'yes',
                'desc' => esc_html__('Enable sticky notes feature in lesson pages.', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_highlight_enable',
                'title' => esc_html__('Enable Text Highlighting', 'lp-sticky-notes'),
                'type' => 'checkbox',
                'default' => 'yes',
                'desc' => esc_html__('Allow students to highlight text and add notes.', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_sidebar_position',
                'title' => esc_html__('Sidebar Position', 'lp-sticky-notes'),
                'type' => 'select',
                'default' => 'right',
                'options' => array(
                    'left' => esc_html__('Left', 'lp-sticky-notes'),
                    'right' => esc_html__('Right', 'lp-sticky-notes'),
                ),
                'desc' => esc_html__('Choose where the sticky notes sidebar appears.', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_button_position',
                'title' => esc_html__('Toggle Button Position', 'lp-sticky-notes'),
                'type' => 'select',
                'default' => 'middle-right',
                'options' => array(
                    'top-left' => esc_html__('Top Left', 'lp-sticky-notes'),
                    'top-right' => esc_html__('Top Right', 'lp-sticky-notes'),
                    'middle-left' => esc_html__('Middle Left', 'lp-sticky-notes'),
                    'middle-right' => esc_html__('Middle Right', 'lp-sticky-notes'),
                    'bottom-left' => esc_html__('Bottom Left', 'lp-sticky-notes'),
                    'bottom-right' => esc_html__('Bottom Right', 'lp-sticky-notes'),
                ),
                'desc' => esc_html__('Choose where the toggle button appears on the screen.', 'lp-sticky-notes'),
            ),
            array(
                'type' => 'sectionend',
            ),
        );
    }

    /**
     * Appearance settings
     *
     * @return array
     */
    private function appearance_settings()
    {
        return array(
            array(
                'type' => 'title',
                'title' => esc_html__('Appearance Settings', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_primary_color',
                'title' => esc_html__('Primary Color', 'lp-sticky-notes'),
                'type' => 'color',
                'default' => '#fbbf24',
                'desc' => esc_html__('Main color for sticky notes (button, header background).', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_text_color',
                'title' => esc_html__('Text Color', 'lp-sticky-notes'),
                'type' => 'color',
                'default' => '#92400e',
                'desc' => esc_html__('Color for text in sticky notes.', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_button_size',
                'title' => esc_html__('Button Size', 'lp-sticky-notes'),
                'type' => 'select',
                'default' => '50',
                'options' => array(
                    '40' => esc_html__('Small (40px)', 'lp-sticky-notes'),
                    '50' => esc_html__('Medium (50px)', 'lp-sticky-notes'),
                    '60' => esc_html__('Large (60px)', 'lp-sticky-notes'),
                ),
                'desc' => esc_html__('Size of the toggle button.', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_button_bottom_offset',
                'title' => esc_html__('Button Bottom Offset (px)', 'lp-sticky-notes'),
                'type' => 'number',
                'default' => '20',
                'css' => 'width: 80px;',
                'desc' => esc_html__('Distance from the bottom of the screen for bottom-positioned buttons. Increase this value on mobile to avoid overlapping with the LearnPress navigation buttons (e.g. set 80 to move it higher).', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_sidebar_width',
                'title' => esc_html__('Sidebar Width', 'lp-sticky-notes'),
                'type' => 'select',
                'default' => '380',
                'options' => array(
                    '300' => esc_html__('Narrow (300px)', 'lp-sticky-notes'),
                    '380' => esc_html__('Medium (380px)', 'lp-sticky-notes'),
                    '450' => esc_html__('Wide (450px)', 'lp-sticky-notes'),
                    '500' => esc_html__('Extra Wide (500px)', 'lp-sticky-notes'),
                ),
                'desc' => esc_html__('Width of the sticky notes sidebar.', 'lp-sticky-notes'),
            ),
            array(
                'id' => 'lp_sticky_notes_custom_css',
                'title' => esc_html__('Custom CSS', 'lp-sticky-notes'),
                'type' => 'textarea',
                'default' => '',
                'css' => 'min-height: 150px; font-family: monospace;',
                'desc' => esc_html__('Add custom CSS for advanced styling. Use with caution.', 'lp-sticky-notes'),
            ),
            array(
                'type' => 'sectionend',
            ),
        );
    }

    /**
     * Shortcode settings
     *
     * @return array
     */
    private function shortcode_settings()
    {
        return array(
            array(
                'type' => 'title',
                'title' => esc_html__('Shortcode Guide', 'lp-sticky-notes'),
                'desc' => '
                    <p><strong>' . esc_html__('Use this shortcode to display sticky notes on any page or post:', 'lp-sticky-notes') . '</strong></p>
                    <div style="background: #f1f5f9; padding: 15px; border-radius: 6px; margin: 15px 0;">
                        <code style="font-size: 14px; color: #334155;">[lp_sticky_notes]</code>
                    </div>
                    
                    <h4 style="margin-top: 20px;">' . esc_html__('Usage Examples:', 'lp-sticky-notes') . '</h4>
                    
                    <p><strong>1. ' . esc_html__('Display current user\'s notes (default):', 'lp-sticky-notes') . '</strong></p>
                    <code style="background: #f1f5f9; padding: 8px; display: block; margin: 8px 0;">[lp_sticky_notes]</code>
                    
                    <p><strong>2. ' . esc_html__('Display notes from a specific course:', 'lp-sticky-notes') . '</strong></p>
                    <code style="background: #f1f5f9; padding: 8px; display: block; margin: 8px 0;">[lp_sticky_notes course_id="123"]</code>
                    
                    <p><strong>3. ' . esc_html__('Display notes with all info (course + lesson names):', 'lp-sticky-notes') . '</strong></p>
                    <code style="background: #f1f5f9; padding: 8px; display: block; margin: 8px 0;">[lp_sticky_notes show_course="yes" show_lesson="yes"]</code>
                    
                    <p><strong>4. ' . esc_html__('Display limited number of notes:', 'lp-sticky-notes') . '</strong></p>
                    <code style="background: #f1f5f9; padding: 8px; display: block; margin: 8px 0;">[lp_sticky_notes limit="5"]</code>
                    
                    <h4 style="margin-top: 20px;">' . esc_html__('Available Parameters:', 'lp-sticky-notes') . '</h4>
                    <table class="form-table" style="margin-top: 10px;">
                        <tbody>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;"><code>limit</code></td>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . esc_html__('Number of notes to display (default: 10)', 'lp-sticky-notes') . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;"><code>course_id</code></td>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . esc_html__('Filter notes by specific course ID', 'lp-sticky-notes') . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;"><code>user_id</code></td>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . esc_html__('Display notes from specific user (defaults to current user)', 'lp-sticky-notes') . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;"><code>show_course</code></td>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . esc_html__('Show/hide course name (yes/no, default: yes)', 'lp-sticky-notes') . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px;"><code>show_lesson</code></td>
                                <td style="padding: 10px;">' . esc_html__('Show/hide lesson name with link (yes/no, default: yes)', 'lp-sticky-notes') . '</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <p style="margin-top: 15px; padding: 12px; background: #dbeafe; border-left: 4px solid #2563eb; border-radius: 4px;">
                        <strong>' . esc_html__('Note:', 'lp-sticky-notes') . '</strong> ' . esc_html__('The shortcode displays notes in a responsive grid layout. Empty state will show when no notes are found.', 'lp-sticky-notes') . '
                    </p>
                ',
            ),
            array(
                'type' => 'sectionend',
            ),
        );
    }

    /**
     * License settings
     *
     * @return array
     */
    private function license_settings()
    {
        $license_handler = LP_Sticky_Notes::instance()->get_license_handler();

        // Handle form submissions
        $message = '';
        $message_type = '';

        if (isset($_POST['mamflow_license_action'])) {
            // Verify nonce
            if (
                !isset($_POST['mamflow_license_nonce']) ||
                !wp_verify_nonce($_POST['mamflow_license_nonce'], 'mamflow_license_action')
            ) {
                $message = 'Security check failed. Please try again.';
                $message_type = 'error';
            } else {
                $action = sanitize_text_field($_POST['mamflow_license_action']);

                if ($action === 'activate') {
                    $license_key = sanitize_text_field($_POST['license_key']);
                    $result = $license_handler->activate_license($license_key);

                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';

                } elseif ($action === 'deactivate') {
                    $result = $license_handler->deactivate_license();

                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';
                }
            }
        }

        // Get current license data
        $license_data = $license_handler->get_license_data();
        $is_active = $license_handler->is_feature_enabled();

        // Build license HTML
        ob_start();
        ?>

        <?php if ($message): ?>
            <div class="notice notice-<?php echo esc_attr($message_type); ?> inline" style="margin: 20px 0;">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($is_active): ?>
            <div class="notice notice-success inline" style="margin: 20px 0;">
                <p>
                    <strong><?php echo esc_html__('✓ License Active', 'lp-sticky-notes'); ?></strong><br>
                    <?php
                    $days_remaining = $license_handler->get_days_until_expiration();
                    if ($days_remaining !== null) {
                        if ($days_remaining > 0) {
                            printf(
                                esc_html__('Your license will expire in %s days.', 'lp-sticky-notes'),
                                $days_remaining
                            );
                        } else {
                            echo esc_html__('Your license has expired. Please renew to continue receiving updates.', 'lp-sticky-notes');
                        }
                    }
                    ?>
                </p>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                <input type="hidden" name="mamflow_license_action" value="deactivate">
                <p>
                    <button type="submit" class="button button-secondary"
                        onclick="return confirm('Are you sure you want to deactivate this license?');">
                        <?php echo esc_html__('Deactivate License', 'lp-sticky-notes'); ?>
                    </button>
                </p>
            </form>

        <?php else: ?>
            <div class="notice notice-warning inline" style="margin: 20px 0;">
                <p>
                    <strong><?php echo esc_html__('⚠ No Active License', 'lp-sticky-notes'); ?></strong><br>
                    <?php echo esc_html__('Please enter your license key to activate this plugin and unlock all features.', 'lp-sticky-notes'); ?>
                </p>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                <input type="hidden" name="mamflow_license_action" value="activate">

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="license_key"><?php echo esc_html__('License Key', 'lp-sticky-notes'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="license_key" name="license_key" class="regular-text"
                                placeholder="XXXX-XXXX-XXXX-XXXX" required>
                            <p class="description">
                                <?php echo esc_html__('Enter your license key from your purchase confirmation email.', 'lp-sticky-notes'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php echo esc_html__('Activate License', 'lp-sticky-notes'); ?>
                    </button>
                </p>
            </form>
        <?php endif; ?>

        <hr style="margin: 30px 0;">

        <h3><?php echo esc_html__('Need Help?', 'lp-sticky-notes'); ?></h3>
        <p>
            <?php echo esc_html__('If you don\'t have a license key yet, you can purchase one from:', 'lp-sticky-notes'); ?>
            <a href="https://mamflow.com/product/learnpress-notes-addon-lp-sticky-notes/" target="_blank">
                <?php echo esc_html__('Mamflow Store', 'lp-sticky-notes'); ?>
            </a>
        </p>
        <p>
            <?php echo esc_html__('For support or license issues, please contact:', 'lp-sticky-notes'); ?>
            <a href="mailto:support@mamflow.com">support@mamflow.com</a>
        </p>

        <?php
        $license_html = ob_get_clean();

        return array(
            array(
                'type' => 'title',
                'title' => esc_html__('License Activation', 'lp-sticky-notes'),
                'desc' => '',
            ),
            array(
                'type' => 'html',
                'desc' => $license_html,
            ),
            array(
                'type' => 'sectionend',
            ),
        );
    }

    /**
     * Get setting value
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function get_setting($key, $default = '')
    {
        return LP_Settings::get_option($key, $default);
    }

    /**
     * Get instance
     *
     * @return LP_Sticky_Notes_Settings
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
