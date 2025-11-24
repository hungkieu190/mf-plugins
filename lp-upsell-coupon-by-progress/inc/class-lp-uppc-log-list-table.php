<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table', false ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class LP_UPPC_Log_List_Table extends WP_List_Table {
	private $items_data = array();

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'lp_uppc_log',
				'plural'   => 'lp_uppc_logs',
				'ajax'     => false,
			)
		);
	}

	public function prepare_items(): void {
		$this->items_data = $this->get_logs();
		$columns          = $this->get_columns();
		$hidden           = array();
		$sortable         = $this->get_sortable_columns();

		usort( $this->items_data, array( $this, 'sort_items' ) );

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total_items  = count( $this->items_data );

		$this->items_data = array_slice( $this->items_data, ( $current_page - 1 ) * $per_page, $per_page );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->items_data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
	}

	public function get_columns(): array {
		return array(
			'timestamp' => esc_html__( 'Date', 'lp-upsell-progress-coupon' ),
			'user'      => esc_html__( 'Learner', 'lp-upsell-progress-coupon' ),
			'course'    => esc_html__( 'Course', 'lp-upsell-progress-coupon' ),
			'progress'  => esc_html__( 'Progress %', 'lp-upsell-progress-coupon' ),
			'coupon'    => esc_html__( 'Coupon', 'lp-upsell-progress-coupon' ),
			'product'   => esc_html__( 'Product', 'lp-upsell-progress-coupon' ),
		);
	}

	protected function get_sortable_columns(): array {
		return array(
			'timestamp' => array( 'timestamp', true ),
			'progress'  => array( 'threshold', false ),
		);
	}

	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'timestamp':
				return esc_html( $item['display_date'] );
			case 'user':
				return $item['user_html'];
			case 'course':
				return $item['course_html'];
			case 'progress':
				return absint( $item['threshold'] );
			case 'coupon':
				return $item['coupon_html'];
			case 'product':
				return $item['product_html'];
			default:
				return isset( $item[ $column_name ] ) ? esc_html( (string) $item[ $column_name ] ) : '';
		}
	}

	private function get_logs(): array {
		$logs = get_option( 'lp_uppc_logs', array() );
		if ( empty( $logs ) || ! is_array( $logs ) ) {
			return array();
		}

		$data = array();

		foreach ( $logs as $log ) {
			$timestamp  = isset( $log['timestamp'] ) ? (int) $log['timestamp'] : time();
			$user_id    = isset( $log['user_id'] ) ? (int) $log['user_id'] : 0;
			$course_id  = isset( $log['course_id'] ) ? (int) $log['course_id'] : 0;
			$coupon_id  = isset( $log['coupon_id'] ) ? (int) $log['coupon_id'] : 0;
			$coupon_txt = $log['coupon_txt'] ?? '';
			$threshold  = isset( $log['threshold'] ) ? (int) $log['threshold'] : 0;
			$product_id = isset( $log['product_id'] ) ? (int) $log['product_id'] : 0;

			$user_html    = $this->format_user_column( $user_id );
			$course_html  = $this->format_course_column( $course_id );
			$coupon_html  = $this->format_coupon_column( $coupon_id, $coupon_txt );
			$product_html = $this->format_product_column( $product_id );

			$data[] = array(
				'timestamp'    => $timestamp,
				'display_date' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ),
				'user_html'    => $user_html,
				'course_html'  => $course_html,
				'coupon_html'  => $coupon_html,
				'product_html' => $product_html,
				'threshold'    => $threshold,
			);
		}

		return $data;
	}

	private function sort_items( $a, $b ): int {
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'timestamp'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'desc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$result = $a[ $orderby ] <=> $b[ $orderby ];

		return 'asc' === strtolower( $order ) ? $result : -$result;
	}

	private function format_user_column( int $user_id ): string {
		if ( ! $user_id ) {
			return esc_html__( 'Guest', 'lp-upsell-progress-coupon' );
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return esc_html__( 'Unknown user', 'lp-upsell-progress-coupon' );
		}

		$link = get_edit_user_link( $user_id );

		return sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $user->display_name ?: $user->user_login ) );
	}

	private function format_course_column( int $course_id ): string {
		if ( ! $course_id ) {
			return esc_html__( 'N/A', 'lp-upsell-progress-coupon' );
		}

		$title = get_the_title( $course_id );
		if ( ! $title ) {
			return esc_html__( 'Course deleted', 'lp-upsell-progress-coupon' );
		}

		$link = get_edit_post_link( $course_id );

		return sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $title ) );
	}

	private function format_coupon_column( int $coupon_id, string $code ): string {
		$label = $code ?: esc_html__( 'View coupon', 'lp-upsell-progress-coupon' );

		if ( $coupon_id ) {
			$link = get_edit_post_link( $coupon_id );
			if ( $link ) {
				return sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $label ) );
			}
		}

		return esc_html( $label );
	}

	private function format_product_column( int $product_id ): string {
		if ( ! $product_id ) {
			return esc_html__( 'Not mapped', 'lp-upsell-progress-coupon' );
		}

		$title = get_the_title( $product_id );
		if ( ! $title ) {
			return sprintf( '#%d', $product_id );
		}

		$link       = get_edit_post_link( $product_id );
		$view_link  = get_permalink( $product_id );
		$links      = array();
		$links[]    = sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $title ) );
		if ( $view_link ) {
			$links[] = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( $view_link ), esc_html__( 'View', 'lp-upsell-progress-coupon' ) );
		}

		return implode( ' | ', $links );
	}
}
