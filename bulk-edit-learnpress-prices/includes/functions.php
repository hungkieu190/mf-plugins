<?php
/**
 * Shared helper functions.
 *
 * @package Bulk_Edit_LearnPress_Prices
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'belpcp_get_plugin' ) ) {
	/**
	 * Get the main plugin instance.
	 *
	 * @return Bulk_Edit_LP_Price|null
	 */
	function belpcp_get_plugin() {
		if ( class_exists( 'Bulk_Edit_LP_Price' ) ) {
			return Bulk_Edit_LP_Price::instance();
		}

		return null;
	}
}

if ( ! function_exists( 'belpcp_normalize_price' ) ) {
	/**
	 * Normalize a price-like value to a decimal string.
	 *
	 * @param mixed $value Raw price value.
	 * @return string
	 */
	function belpcp_normalize_price( $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';

		if ( '' === $value ) {
			return '';
		}

		$value = preg_replace( '/[^0-9.\-]/', '', $value );

		if ( ! is_numeric( $value ) ) {
			return '';
		}

		$price = max( 0, (float) $value );

		return number_format( $price, 2, '.', '' );
	}
}

if ( ! function_exists( 'belpcp_format_price' ) ) {
	/**
	 * Format a normalized price for admin display.
	 *
	 * @param mixed $value Raw price value.
	 * @return string
	 */
	function belpcp_format_price( $value ) {
		$price = belpcp_normalize_price( $value );

		if ( '' === $price ) {
			return __( 'None', 'bulk-edit-learnpress-prices' );
		}

		if ( function_exists( 'learn_press_format_price' ) ) {
			return learn_press_format_price( (float) $price );
		}

		$price_float = (float) $price;
		$decimals    = floor( $price_float ) === $price_float ? 0 : 2;

		return number_format_i18n( $price_float, $decimals );
	}
}

if ( ! function_exists( 'belpcp_get_course_price_state' ) ) {
	/**
	 * Determine the price state of a course.
	 *
	 * @param int $course_id Course post ID.
	 * @return string One of free, paid, or sale.
	 */
	function belpcp_get_course_price_state( $course_id ) {
		$regular_meta_key = defined( 'BELPCP_REGULAR_PRICE_META_KEY' ) ? BELPCP_REGULAR_PRICE_META_KEY : '_lp_regular_price';
		$active_meta_key  = defined( 'BELPCP_ACTIVE_PRICE_META_KEY' ) ? BELPCP_ACTIVE_PRICE_META_KEY : '_lp_price';
		$sale_meta_key    = defined( 'BELPCP_SALE_PRICE_META_KEY' ) ? BELPCP_SALE_PRICE_META_KEY : '_lp_sale_price';
		$regular_raw      = metadata_exists( 'post', $course_id, $regular_meta_key ) ? get_post_meta( $course_id, $regular_meta_key, true ) : get_post_meta( $course_id, $active_meta_key, true );
		$regular_price    = belpcp_normalize_price( $regular_raw );
		$sale_price       = belpcp_normalize_price( get_post_meta( $course_id, $sale_meta_key, true ) );

		if ( '' !== $sale_price && (float) $sale_price > 0 ) {
			return 'sale';
		}

		if ( '' !== $regular_price && (float) $regular_price > 0 ) {
			return 'paid';
		}

		return 'free';
	}
}
