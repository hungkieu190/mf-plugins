<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Utility helpers for the Upsell Progress Coupon addon.
 */
class LP_UPPC_Helper
{
	/**
	 * Resolve the WooCommerce product ID linked to a LearnPress course.
	 *
	 * Searches for products that have this course assigned via WooCommerce Payment addon.
	 *
	 * @param int $course_id Course post ID.
	 *
	 * @return int
	 */
	public static function get_course_product_id(int $course_id): int
	{
		$course_id = absint($course_id);
		if (!$course_id) {
			return 0;
		}

		global $wpdb;

		// Find products that have this course assigned
		// WooCommerce Payment addon stores courses in _lp_woo_courses_assigned meta as serialized array
		$sql = $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			WHERE meta_key = '_lp_woo_courses_assigned'
			AND meta_value LIKE %s
			LIMIT 1",
			'%"' . $course_id . '"%'
		);

		$product_id = (int) $wpdb->get_var($sql);

		/**
		 * Allow 3rd-parties to provide a product mapping for a course.
		 *
		 * @param int $product_id
		 * @param int $course_id
		 */
		$product_id = (int) apply_filters('lp_uppc_course_product_id', $product_id, $course_id);

		return $product_id > 0 ? $product_id : 0;
	}

	/**
	 * Write debug log if debug logging is enabled in settings.
	 *
	 * @param string $message Debug message to log.
	 *
	 * @return void
	 */
	public static function debug_log(string $message): void
	{
		if ('yes' === LP_UPPC_Settings::get_setting('lp_uppc_enable_debug_log', 'no')) {
			error_log('[LP_UPPC_DEBUG] ' . $message);
		}
	}
}
