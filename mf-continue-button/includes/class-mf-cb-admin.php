<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Admin settings page for MF Continue Button
 */
class MF_CB_Admin {

    public function __construct(){
        add_action('admin_menu',  array($this, 'add_menu'));
        add_action('admin_init',  array($this, 'register_settings'));
    }

    /** Add submenu under Settings */
    public function add_menu(){
        add_options_page(
            __('MF Continue Button', 'mf-continue-button'),
            __('MF Continue Button', 'mf-continue-button'),
            'manage_options',
            'mf-continue-button',
            array($this, 'render_page')
        );
    }

    /** Register settings using Settings API */
    public function register_settings(){
        register_setting( 'mf_cb_settings_group', MF_CB_OPT, array(
            'type'              => 'array',
            'sanitize_callback' => array($this, 'sanitize_options'),
            'default'           => mf_cb_default_options(),
        ));

        add_settings_section(
            'mf_cb_main_section',
            __('General Settings', 'mf-continue-button'),
            function(){
                echo '<p>' . esc_html__('Configure how the "Continue Learning" button behaves and where it is displayed.', 'mf-continue-button') . '</p>';
            },
            'mf-cb-settings'
        );

        // enable_loop
        add_settings_field(
            'enable_loop',
            __('Auto inject on Course Cards/Loop', 'mf-continue-button'),
            array($this, 'field_checkbox'),
            'mf-cb-settings',
            'mf_cb_main_section',
            array('key' => 'enable_loop', 'label' => __('Enable', 'mf-continue-button'))
        );

        // enable_dashboard
        add_settings_field(
            'enable_dashboard',
            __('Show on Profile Dashboard', 'mf-continue-button'),
            array($this, 'field_checkbox'),
            'mf-cb-settings',
            'mf_cb_main_section',
            array('key' => 'enable_dashboard', 'label' => __('Enable', 'mf-continue-button'))
        );

        // only_logged_in
        add_settings_field(
            'only_logged_in',
            __('Only show for logged-in users', 'mf-continue-button'),
            array($this, 'field_checkbox'),
            'mf-cb-settings',
            'mf_cb_main_section',
            array('key' => 'only_logged_in', 'label' => __('Hide for guests (no fallback button)', 'mf-continue-button'))
        );

        // label_continue
        add_settings_field(
            'label_continue',
            __('Button Label (Continue)', 'mf-continue-button'),
            array($this, 'field_text'),
            'mf-cb-settings',
            'mf_cb_main_section',
            array('key' => 'label_continue', 'placeholder' => 'Continue Learning →')
        );

        // label_fallback
        add_settings_field(
            'label_fallback',
            __('Button Label (Fallback)', 'mf-continue-button'),
            array($this, 'field_text'),
            'mf-cb-settings',
            'mf_cb_main_section',
            array('key' => 'label_fallback', 'placeholder' => 'Start Learning')
        );

        // extra_class
        add_settings_field(
            'extra_class',
            __('Extra CSS classes', 'mf-continue-button'),
            array($this, 'field_text'),
            'mf-cb-settings',
            'mf_cb_main_section',
            array('key' => 'extra_class', 'placeholder' => 'btn btn-primary is-small')
        );
    }

    /** Settings page HTML */
    public function render_page(){
        if ( ! current_user_can('manage_options') ) return;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('MF Continue Button', 'mf-continue-button'); ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields('mf_cb_settings_group');
                    do_settings_sections('mf-cb-settings');
                    submit_button();
                ?>
            </form>
            <hr>
            <p><strong>Shortcode:</strong> <code>[mf_continue_button course_id="" label="" fallback="" class=""]</code></p>
        </div>
        <?php
    }

    /** Checkbox field renderer */
    public function field_checkbox( $args ){
        $opts = mf_cb_get_options();
        $key  = $args['key'];
        $val  = !empty($opts[$key]) ? 1 : 0;
        printf(
            '<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> %4$s</label>',
            esc_attr(MF_CB_OPT),
            esc_attr($key),
            checked(1, $val, false),
            esc_html($args['label'])
        );
    }

    /** Text input field renderer */
    public function field_text( $args ){
        $opts = mf_cb_get_options();
        $key  = $args['key'];
        $val  = isset($opts[$key]) ? $opts[$key] : '';
        printf(
            '<input type="text" class="regular-text" name="%1$s[%2$s]" value="%3$s" placeholder="%4$s" />',
            esc_attr(MF_CB_OPT),
            esc_attr($key),
            esc_attr($val),
            esc_attr($args['placeholder'])
        );
    }

    /** Sanitize options */
    public function sanitize_options( $opts ){
        $defaults = mf_cb_default_options();
        $out = array();

        $out['enable_loop']      = ! empty($opts['enable_loop']) ? 1 : 0;
        $out['enable_dashboard'] = ! empty($opts['enable_dashboard']) ? 1 : 0;
        $out['only_logged_in']   = ! empty($opts['only_logged_in']) ? 1 : 0;

        $out['label_continue']   = isset($opts['label_continue']) ? sanitize_text_field($opts['label_continue']) : $defaults['label_continue'];
        $out['label_fallback']   = isset($opts['label_fallback']) ? sanitize_text_field($opts['label_fallback']) : $defaults['label_fallback'];
        $out['extra_class']      = isset($opts['extra_class']) ? sanitize_text_field($opts['extra_class']) : '';

        return wp_parse_args($out, $defaults);
    }
}
