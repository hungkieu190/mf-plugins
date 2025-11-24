<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility helpers for the Upsell Progress Coupon addon.
 */
class LP_UPPC_Helper {
	/**
	 * Resolve the WooCommerce product ID linked to a LearnPress course.
	 *
	 * @param int $course_id Course post ID.
	 *
	 * @return int
	 */
	public static function get_course_product_id( int $course_id ): int {
		$course_id = absint( $course_id );
		if ( ! $course_id ) {
			return 0;
		}

		$product_id = (int) get_post_meta( $course_id, '_lp_woo_product_id', true );

		if ( ! $product_id && function_exists( 'LP' ) ) {
			$settings   = LP()->settings ?? null;
			$product_id = $settings ? (int) $settings->get( 'woo_course_product_' . $course_id ) : 0;
		}

		/**
		 * Allow 3rd-parties to provide a product mapping for a course.
		 *
		 * @param int $product_id
		 * @param int $course_id
		 */
		$product_id = (int) apply_filters( 'lp_uppc_course_product_id', $product_id, $course_id );

		return $product_id > 0 ? $product_id : 0;
	}
}
