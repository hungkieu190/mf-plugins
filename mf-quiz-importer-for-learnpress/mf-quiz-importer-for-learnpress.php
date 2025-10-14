<?php
/**
 * Plugin Name: MF Quiz Importer for LearnPress (CSV/XLSX)
 * Description: Import Quiz & Questions vào LearnPress từ CSV/XLSX. Hỗ trợ gán quiz vào Course + Section.
 * Author: Ultra Violet & ChatGPT
 * Version: 1.0.0
 * Text Domain: mf-quiz-importer-for-learnpress
 */

if (!defined('ABSPATH')) exit;

define('MFQI_PLUGIN_FILE', __FILE__);
define('MFQI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MFQI_PLUGIN_URL', plugin_dir_url(__FILE__));

register_activation_hook(__FILE__, function () {
    // Ensure LearnPress is active
    if (!class_exists('LearnPress')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('LearnPress chưa được kích hoạt. Hãy bật LearnPress trước khi bật plugin này.');
    }
});

add_action('plugins_loaded', function () {
    // Load text domain if needed
    load_plugin_textdomain('mf-quiz-importer-for-learnpress', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

require_once MFQI_PLUGIN_DIR . 'inc/class-mfqi-importer.php';
require_once MFQI_PLUGIN_DIR . 'inc/class-mfqi-admin-page.php';

add_action('admin_menu', function () {
    MFQI_Admin_Page::register();
});
