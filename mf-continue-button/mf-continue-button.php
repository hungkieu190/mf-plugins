<?php
/**
 * Plugin Name: MF Continue Button for LearnPress
 * Description: Adds a "Continue Learning" button that jumps learners to the last lesson/quiz they viewed in a course. Includes settings to auto-inject on course cards and on the profile dashboard.
 * Version:     1.1.0
 * Author:      MF Dev
 * Text Domain: mf-continue-button
 *
 * Note: Comments are in English as requested.
 */

if ( ! defined('ABSPATH') ) exit;

define('MF_CB_PATH', plugin_dir_path(__FILE__));
define('MF_CB_URL',  plugin_dir_url(__FILE__));
define('MF_CB_OPT',  'mf_cb_options');

// Default options (used if option is missing)
function mf_cb_default_options(){
    return array(
        'enable_loop'      => 1,                         // auto inject on course loop/cards
        'enable_dashboard' => 1,                         // auto inject on LP profile dashboard
        'label_continue'   => 'Continue Learning →',     // default label
        'label_fallback'   => 'Start Learning',          // fallback label
        'extra_class'      => '',                        // extra CSS classes on the button
        'only_logged_in'   => 0,                         // show button only for logged-in users (if 1) else show fallback
    );
}

// Get merged options (db + defaults)
function mf_cb_get_options(){
    $opts = get_option(MF_CB_OPT, array());
    return wp_parse_args($opts, mf_cb_default_options());
}

// Basic styles
add_action('wp_enqueue_scripts', function(){
    wp_register_style('mf-cb-style', MF_CB_URL . 'assets/style.css', [], '1.1.0');
});

// Load core
require_once MF_CB_PATH . 'includes/class-mf-continue-button.php';

// Admin settings
if ( is_admin() ) {
    require_once MF_CB_PATH . 'includes/class-mf-cb-admin.php';
    new MF_CB_Admin();
}

// Public shortcode: [mf_continue_button course_id="" label="" fallback="" class=""]
add_shortcode('mf_continue_button', function($atts){
    $opts = mf_cb_get_options();
    $atts = shortcode_atts(array(
        'course_id' => 0,
        'label'     => $opts['label_continue'],
        'fallback'  => $opts['label_fallback'],
        'class'     => $opts['extra_class'],
    ), $atts, 'mf_continue_button');

    return MF_Continue_Button::render_button( $atts );
});
