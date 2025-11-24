<?php
/**
 * Plugin Name: LearnPress Upsell Coupon by Progress
 * Plugin URI: https://mamflow.com/
 * Description: Automatically reward learners with WooCommerce coupons once they reach configurable progress milestones in their current LearnPress course.
 * Version: 1.0.0
 * Author: Mamflow
 * Author URI: https://mamflow.com/
 * Requires PHP: 7.4
 * Text Domain: lp-upsell-progress-coupon
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!defined('LP_UPPC_VERSION')) {
	define('LP_UPPC_VERSION', '1.0.0');
}

if (!defined('LP_UPPC_FILE')) {
	define('LP_UPPC_FILE', __FILE__);
}

if (!defined('LP_UPPC_PATH')) {
	define('LP_UPPC_PATH', plugin_dir_path(LP_UPPC_FILE));
}

if (!defined('LP_UPPC_URL')) {
	define('LP_UPPC_URL', plugin_dir_url(LP_UPPC_FILE));
}

if (!defined('LP_UPPC_BASENAME')) {
	define('LP_UPPC_BASENAME', plugin_basename(LP_UPPC_FILE));
}

require_once LP_UPPC_PATH . 'inc/class-lp-uppc-plugin.php';

register_activation_hook(LP_UPPC_FILE, function () {
	if (!class_exists('LearnPress') || !class_exists('WooCommerce')) {
		deactivate_plugins(plugin_basename(LP_UPPC_FILE));
		wp_die(esc_html__('LearnPress Upsell Coupon by Progress requires LearnPress and WooCommerce to be installed and active.', 'lp-upsell-progress-coupon'));
	}
});

add_action('plugins_loaded', static function () {
	if (!class_exists('LearnPress') || !class_exists('WooCommerce')) {
		add_action('admin_notices', function () {
			?>
			<div class="error">
				<p><?php esc_html_e('LearnPress Upsell Coupon by Progress has been deactivated because it requires LearnPress and WooCommerce.', 'lp-upsell-progress-coupon'); ?>
				</p>
			</div>
			<?php
		});

		if (!function_exists('deactivate_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		deactivate_plugins(plugin_basename(LP_UPPC_FILE));

		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}

		return;
	}

	LP_UPPC_Plugin::instance();
});
