<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Course-level Upsell configuration UI.
 */
class LP_UPPC_Metabox {
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_filter( 'learnpress/course/metabox/tabs', [ $this, 'register_tab' ], 20, 2 );
		add_filter( 'lp/course/meta-box/fields/general', [ $this, 'register_fields' ], 20, 2 );
		add_action( 'learnpress_save_lp_course_metabox', [ $this, 'save_course_metabox' ], 20, 2 );
	}

	public function register_tab( array $tabs, int $post_id ): array {
		$tabs['uppc'] = array(
			'label'    => esc_html__( 'Upsell by Progress', 'lp-upsell-progress-coupon' ),
			'target'   => 'uppc_course_data',
			'icon'     => 'dashicons-awards',
			'priority' => 65,
			'content'  => $this->get_tab_fields( $post_id ),
		);

		return $tabs;
	}

	public function register_fields( array $fields, int $post_id ): array {
		// Inject enable toggle into General tab for quick access.
		$fields['_lp_uppc_enable_course'] = new LP_Meta_Box_Checkbox_Field(
			esc_html__( 'Enable Upsell Progress rules', 'lp-upsell-progress-coupon' ),
			esc_html__( 'Allow this course to trigger progress-based coupons.', 'lp-upsell-progress-coupon' ),
			'no'
		);

		return $fields;
	}

	private function get_tab_fields( int $post_id ): array {
		$default_discount_type = LP_UPPC_Settings::get_setting( 'lp_uppc_default_discount_type', 'percent' );
		$default_discount      = LP_UPPC_Settings::get_setting( 'lp_uppc_default_discount_value', 10 );
		$default_expiry        = LP_UPPC_Settings::get_setting( 'lp_uppc_default_expiry_days', 30 );

		$fields = array(
			'_lp_uppc_rules' => new LP_Meta_Box_Repeater_Field(
				esc_html__( 'Progress rules', 'lp-upsell-progress-coupon' ),
				esc_html__( 'Add progress thresholds and define the coupon that should be generated.', 'lp-upsell-progress-coupon' ),
				[],
				array(
					'add_text'   => esc_html__( 'Add rule', 'lp-upsell-progress-coupon' ),
					'title_text' => esc_html__( 'Rule', 'lp-upsell-progress-coupon' ),
					'fields'     => array(
						'progress'        => new LP_Meta_Box_Text_Field(
							esc_html__( 'Trigger progress (%)', 'lp-upsell-progress-coupon' ),
							esc_html__( 'Coupon fires when learner reaches this completion percentage.', 'lp-upsell-progress-coupon' ),
							'',
							array(
								'type_input'        => 'number',
								'custom_attributes' => array(
									'placeholder' => '75',
									'min'        => '0',
									'max'        => '100',
									'step'       => '1',
								),
							)
						),
						'coupon_type'      => new LP_Meta_Box_Select_Field(
							esc_html__( 'Discount type', 'lp-upsell-progress-coupon' ),
							esc_html__( 'Choose percentage or fixed amount discount.', 'lp-upsell-progress-coupon' ),
							$default_discount_type,
							array(
								'options' => array(
									'percent' => esc_html__( 'Percentage (%)', 'lp-upsell-progress-coupon' ),
									'fixed'   => esc_html__( 'Fixed amount', 'lp-upsell-progress-coupon' ),
								),
							)
						),
						'coupon_amount'    => new LP_Meta_Box_Text_Field(
							esc_html__( 'Discount value', 'lp-upsell-progress-coupon' ),
							esc_html__( 'Specify the discount number applied in the WooCommerce coupon.', 'lp-upsell-progress-coupon' ),
							$default_discount,
							array(
								'type_input'        => 'number',
								'custom_attributes' => array(
									'placeholder' => '10',
									'min'        => '0',
									'step'       => '0.01',
								),
							)
						),
						'expiry_days'      => new LP_Meta_Box_Text_Field(
							esc_html__( 'Coupon expiry (days)', 'lp-upsell-progress-coupon' ),
							esc_html__( 'Days until the coupon expires after being generated.', 'lp-upsell-progress-coupon' ),
							$default_expiry,
							array(
								'type_input'        => 'number',
								'custom_attributes' => array(
									'placeholder' => '30',
									'min'        => '0',
									'step'       => '1',
								),
							)
						),
						'applies_to'       => new LP_Meta_Box_Select_Field(
							esc_html__( 'Coupon applies to', 'lp-upsell-progress-coupon' ),
							esc_html__( 'Select how to determine which course(s) the coupon discounts.', 'lp-upsell-progress-coupon' ),
							'specific',
							array(
								'options' => array(
									'specific' => esc_html__( 'Specific courses', 'lp-upsell-progress-coupon' ),
									'category' => esc_html__( 'Course categories', 'lp-upsell-progress-coupon' ),
									'global'   => esc_html__( 'All other courses', 'lp-upsell-progress-coupon' ),
								),
							)
						),
						'target_courses'   => new LP_Meta_Box_Select_Field(
							esc_html__( 'Select courses', 'lp-upsell-progress-coupon' ),
							esc_html__( 'Choose one or multiple courses to apply the coupon.', 'lp-upsell-progress-coupon' ),
							[],
							array(
								'multiple'    => true,
								'wrapper_class' => 'lp-select-2',
								'options'     => $this->get_course_options( $post_id ),
							)
						),
						'target_categories' => new LP_Meta_Box_Select_Field(
							esc_html__( 'Target categories', 'lp-upsell-progress-coupon' ),
							esc_html__( 'Limit coupon to courses belonging to selected categories.', 'lp-upsell-progress-coupon' ),
							[],
							array(
								'multiple'      => true,
								'wrapper_class' => 'lp-select-2',
								'options'       => $this->get_course_category_options(),
							)
						),
						'notes'            => new LP_Meta_Box_Textarea_Field(
							esc_html__( 'Internal notes', 'lp-upsell-progress-coupon' ),
							esc_html__( 'Optional description for admins about this rule.', 'lp-upsell-progress-coupon' )
						),
					)
				)
			)
		);

		return $fields;
	}

	public function save_course_metabox( int $post_id, WP_Post $post ): void {
		if ( $post->post_type !== LP_COURSE_CPT ) {
			return;
		}

		$enable = LP_Request::get_param( '_lp_uppc_enable_course', 'no', 'text', 'post' );
		update_post_meta( $post_id, '_lp_uppc_enable_course', $enable === 'yes' ? 'yes' : 'no' );

		$data = LP_Request::get_param( '_lp_uppc_rules', [], 'html', 'post' );
		$default_discount_type = LP_UPPC_Settings::get_setting( 'lp_uppc_default_discount_type', 'percent' );
		$default_discount      = (float) LP_UPPC_Settings::get_setting( 'lp_uppc_default_discount_value', 10 );
		$default_expiry        = (int) LP_UPPC_Settings::get_setting( 'lp_uppc_default_expiry_days', 30 );
		if ( empty( $data ) || ! is_array( $data ) ) {
			delete_post_meta( $post_id, '_lp_uppc_rules' );
			return;
		}

		$prepared = array();
		foreach ( $data as $key => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$progress = isset( $row['progress'] ) ? (int) $row['progress'] : null;
			if ( null === $progress || $progress < 0 || $progress > 100 ) {
				continue;
			}

			$coupon_type = in_array( $row['coupon_type'] ?? '', array( 'percent', 'fixed' ), true ) ? $row['coupon_type'] : $default_discount_type;
			$coupon_amount = isset( $row['coupon_amount'] ) ? (float) $row['coupon_amount'] : (float) $default_discount;
			$expiry_days   = isset( $row['expiry_days'] ) ? (int) $row['expiry_days'] : (int) $default_expiry;
			$apply_type    = in_array( $row['applies_to'] ?? 'specific', array( 'specific', 'category', 'global' ), true ) ? $row['applies_to'] : 'specific';

			$prepared[] = array(
				'progress'          => min( 100, max( 0, $progress ) ),
				'coupon_type'       => $coupon_type,
				'coupon_amount'     => max( 0, $coupon_amount ),
				'expiry_days'       => max( 0, $expiry_days ),
				'applies_to'        => $apply_type,
				'target_courses'    => array_filter( array_map( 'absint', $row['target_courses'] ?? array() ) ),
				'target_categories' => array_filter( array_map( 'absint', $row['target_categories'] ?? array() ) ),
				'notes'             => sanitize_textarea_field( $row['notes'] ?? '' ),
			);
		}

		if ( empty( $prepared ) ) {
			delete_post_meta( $post_id, '_lp_uppc_rules' );
			return;
		}

		usort(
			$prepared,
			static function ( $a, $b ) {
				if ( $a['progress'] === $b['progress'] ) {
					return 0;
				}

				return ( $a['progress'] < $b['progress'] ) ? -1 : 1;
			}
		);

		update_post_meta( $post_id, '_lp_uppc_rules', $prepared );
	}

	private function get_course_options( int $current_course_id ): array {
		$args = array(
			'post_type'      => LP_COURSE_CPT,
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 200,
			'exclude'        => array( $current_course_id ),
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		);

		$query   = new WP_Query( $args );
		$options = array();

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $course_id ) {
				$options[ $course_id ] = get_the_title( $course_id ) . ' (#' . $course_id . ')';
			}
		}

		wp_reset_postdata();

		return $options;
	}

	private function get_course_category_options(): array {
		$terms   = get_terms(
			array(
				'taxonomy'   => 'course_category',
				'hide_empty' => false,
			)
		);
		$options = array();

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = $term->name . ' (#' . $term->term_id . ')';
			}
		}

		return $options;
	}
}
