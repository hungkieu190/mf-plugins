<?php
/**
 * Plugin Name: Bulk Edit LearnPress Course Prices
 * Plugin URI:  https://mamflow.com/
 * Description: Powerful bulk editing tool for LearnPress course prices. Edit regular price and sale price of hundreds of courses in seconds.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author:      Mamflow
 * Author URI:  https://mamflow.com/
 * Text Domain: bulk-edit-learnpress-prices
 * Domain Path: /languages
 *
 * @package Bulk_Edit_LearnPress_Prices
 */

defined( 'ABSPATH' ) || exit;

define( 'BELPCP_VERSION', '1.0.0' );
define( 'BELPCP_FILE', __FILE__ );
define( 'BELPCP_DIR', plugin_dir_path( __FILE__ ) );
define( 'BELPCP_URL', plugin_dir_url( __FILE__ ) );
define( 'BELPCP_BASENAME', plugin_basename( __FILE__ ) );
define( 'BELPCP_TEXT_DOMAIN', 'bulk-edit-learnpress-prices' );
define( 'BELPCP_COURSE_POST_TYPE', 'lp_course' );
define( 'BELPCP_MAX_SELECTED_COURSES', 200 );
define( 'BELPCP_REGULAR_PRICE_META_KEY', '_lp_regular_price' );
define( 'BELPCP_ACTIVE_PRICE_META_KEY', '_lp_price' );
define( 'BELPCP_SALE_PRICE_META_KEY', '_lp_sale_price' );
define( 'BELPCP_SALE_START_META_KEY', '_lp_sale_start' );
define( 'BELPCP_SALE_END_META_KEY', '_lp_sale_end' );
define( 'BELPCP_PRICE_HISTORY_META_KEY', '_belpcp_price_history' );
define( 'BELPCP_MAX_PRICE_HISTORY_ITEMS', 100 );

require_once BELPCP_DIR . 'includes/functions.php';
require_once BELPCP_DIR . 'includes/class-bulk-edit-lp-price.php';

register_activation_hook( __FILE__, array( 'Bulk_Edit_LP_Price', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Bulk_Edit_LP_Price', 'deactivate' ) );

belpcp_get_plugin()->register_hooks();
