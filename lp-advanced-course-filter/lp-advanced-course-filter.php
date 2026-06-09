<?php
/**
 * Plugin Name: Advanced Course Filter for LearnPress
 * Plugin URI: https://mamflow.com/
 * Description: AJAX course filters for LearnPress with category, price, level, rating, and live search.
 * Author: Mamflow
 * Version: 1.0.0
 * Author URI: https://mamflow.com/
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Text Domain: lp-advanced-course-filter
 * Domain Path: /languages/
 * Require_LP_Version: 4.2.0
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

define( 'LP_ACF_VERSION', '1.0.0' );
define( 'LP_ACF_FILE', __FILE__ );
define( 'LP_ACF_PATH', plugin_dir_path( __FILE__ ) );
define( 'LP_ACF_URL', plugin_dir_url( __FILE__ ) );
define( 'LP_ACF_BASENAME', plugin_basename( __FILE__ ) );

require_once LP_ACF_PATH . 'includes/class-lp-acf.php';

/**
 * Get the main plugin instance.
 *
 * @return LP_Advanced_Course_Filter
 */
function lp_acf() {
	return LP_Advanced_Course_Filter::instance();
}

lp_acf();
