<?php
/**
 * Plugin Name: MF Quiz Importer for LearnPress (CSV/XLSX)
 * Description: Import Quiz & Questions to LearnPress from CSV/XLSX. Supports assigning quiz to Course + Section.
 * Author: mamflow
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
        wp_die('LearnPress is not activated. Please activate LearnPress before activating this plugin.');
    }
});

add_action('plugins_loaded', function () {
    // Load text domain if needed
    load_plugin_textdomain('mf-quiz-importer-for-learnpress', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Include admin page class
require_once MFQI_PLUGIN_DIR . 'inc/class-mfqi-admin-page.php';

// Register admin menu
add_action('admin_menu', [MFQI_Admin_Page::class, 'register_quiz_importer_menu']);

// Register early request handler for file downloads
add_action('admin_init', [MFQI_Admin_Page::class, 'handle_early_requests']);
