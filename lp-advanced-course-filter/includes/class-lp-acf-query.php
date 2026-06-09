<?php
/**
 * Course query service.
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class LP_ACF_Query
 */
class LP_ACF_Query {
	/**
	 * Build a WP_Query for filtered courses.
	 *
	 * @param array $raw_args Request arguments.
	 * @return WP_Query
	 */
	public static function get_courses( array $raw_args = array() ) {
		$args = self::sanitize_args( $raw_args );

		$query_args = array(
			'post_type'           => 'lp_course',
			'post_status'         => 'publish',
			'posts_per_page'      => $args['per_page'],
			'paged'               => $args['paged'],
			'ignore_sticky_posts' => true,
			's'                   => $args['search'],
		);

		$tax_query = self::tax_query( $args );
		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query;
		}

		$meta_query = self::meta_query( $args );
		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		self::apply_sorting( $query_args, $args['orderby'] );

		return new WP_Query( apply_filters( 'lp_acf_query_args', $query_args, $args ) );
	}

	/**
	 * Sanitize incoming arguments.
	 *
	 * @param array $raw_args Raw arguments.
	 * @return array
	 */
	public static function sanitize_args( array $raw_args ) {
		$categories = isset( $raw_args['categories'] ) ? (array) $raw_args['categories'] : array();
		$levels     = isset( $raw_args['levels'] ) ? (array) $raw_args['levels'] : array();

		return array(
			'categories' => array_filter( array_map( 'absint', $categories ) ),
			'price'      => isset( $raw_args['price'] ) ? sanitize_key( wp_unslash( $raw_args['price'] ) ) : 'all',
			'levels'     => array_values( array_filter( array_map( 'sanitize_key', wp_unslash( $levels ) ) ) ),
			'rating'     => isset( $raw_args['rating'] ) ? (float) $raw_args['rating'] : 0,
			'search'     => isset( $raw_args['search'] ) ? sanitize_text_field( wp_unslash( $raw_args['search'] ) ) : '',
			'orderby'    => isset( $raw_args['orderby'] ) ? sanitize_key( wp_unslash( $raw_args['orderby'] ) ) : 'date',
			'paged'      => max( 1, isset( $raw_args['paged'] ) ? absint( $raw_args['paged'] ) : 1 ),
			'per_page'   => min( 48, max( 1, isset( $raw_args['per_page'] ) ? absint( $raw_args['per_page'] ) : 9 ) ),
		);
	}

	/**
	 * Build taxonomy query.
	 *
	 * @param array $args Sanitized args.
	 * @return array
	 */
	private static function tax_query( array $args ) {
		$tax_query = array();

		if ( ! empty( $args['categories'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'course_category',
				'field'    => 'term_id',
				'terms'    => $args['categories'],
				'operator' => 'IN',
			);
		}

		return $tax_query;
	}

	/**
	 * Build meta query.
	 *
	 * @param array $args Sanitized args.
	 * @return array
	 */
	private static function meta_query( array $args ) {
		$meta_query = array( 'relation' => 'AND' );

		if ( 'free' === $args['price'] ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => '_lp_price',
					'value'   => '0',
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => '_lp_price',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_lp_price',
					'value'   => '',
					'compare' => '=',
				),
			);
		} elseif ( 'paid' === $args['price'] ) {
			$meta_query[] = array(
				'key'     => '_lp_price',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			);
		}

		if ( ! empty( $args['levels'] ) ) {
			$levels = $args['levels'];
			if ( in_array( 'all', $levels, true ) ) {
				$levels[] = '';
			}

			$meta_query[] = array(
				'key'     => '_lp_level',
				'value'   => $levels,
				'compare' => 'IN',
			);
		}

		if ( $args['rating'] > 0 ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => '_lp_average_rating',
					'value'   => $args['rating'],
					'compare' => '>=',
					'type'    => 'DECIMAL(3,1)',
				),
				array(
					'key'     => '_lp_course_rating',
					'value'   => $args['rating'],
					'compare' => '>=',
					'type'    => 'DECIMAL(3,1)',
				),
				array(
					'key'     => '_lp_rating',
					'value'   => $args['rating'],
					'compare' => '>=',
					'type'    => 'DECIMAL(3,1)',
				),
			);
		}

		return count( $meta_query ) > 1 ? $meta_query : array();
	}

	/**
	 * Apply sorting to query args.
	 *
	 * @param array  $query_args WP_Query args.
	 * @param string $orderby Sort key.
	 * @return void
	 */
	private static function apply_sorting( array &$query_args, $orderby ) {
		switch ( $orderby ) {
			case 'title':
				$query_args['orderby'] = 'title';
				$query_args['order']   = 'ASC';
				break;
			case 'price_low':
				$query_args['meta_key'] = '_lp_price';
				$query_args['orderby']  = 'meta_value_num';
				$query_args['order']    = 'ASC';
				break;
			case 'price_high':
				$query_args['meta_key'] = '_lp_price';
				$query_args['orderby']  = 'meta_value_num';
				$query_args['order']    = 'DESC';
				break;
			default:
				$query_args['orderby'] = 'date';
				$query_args['order']   = 'DESC';
				break;
		}
	}
}
