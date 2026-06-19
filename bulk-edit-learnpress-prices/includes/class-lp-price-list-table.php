<?php
/**
 * Course price list table.
 *
 * @package Bulk_Edit_LearnPress_Prices
 */

defined( 'ABSPATH' ) || exit;

if ( is_admin() && ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( class_exists( 'WP_List_Table' ) && ! class_exists( 'LP_Price_List_Table' ) ) {
	/**
	 * List table shell for LearnPress course prices.
	 */
	class LP_Price_List_Table extends WP_List_Table {
		/**
		 * Plugin controller.
		 *
		 * @var Bulk_Edit_LP_Price|null
		 */
		protected $plugin;

		/**
		 * Current sanitized filters.
		 *
		 * @var array
		 */
		protected $filters = array();

		/**
		 * Validation errors returned by the query layer.
		 *
		 * @var array
		 */
		protected $errors = array();

		/**
		 * Whether the current user can manage price editing workflows.
		 *
		 * @var bool
		 */
		protected $can_manage = false;

		/**
		 * Constructor.
		 *
		 * @param Bulk_Edit_LP_Price|null $plugin     Plugin controller.
		 * @param array                   $filters    Current filters.
		 * @param bool                    $can_manage Whether current user can manage prices.
		 */
		public function __construct( $plugin = null, $filters = array(), $can_manage = false ) {
			parent::__construct(
				array(
					'singular' => 'lp_course',
					'plural'   => 'lp_courses',
					'ajax'     => false,
				)
			);

			$this->plugin     = $plugin;
			$this->filters    = is_array( $filters ) ? $filters : array();
			$this->can_manage = (bool) $can_manage;
		}

		/**
		 * Get table columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				'cb'            => '<input type="checkbox" />',
				'id'            => __( 'Course ID', 'bulk-edit-learnpress-prices' ),
				'title'         => __( 'Course Name', 'bulk-edit-learnpress-prices' ),
				'regular_price' => __( 'Current Regular Price', 'bulk-edit-learnpress-prices' ),
				'sale_price'    => __( 'Current Sale Price', 'bulk-edit-learnpress-prices' ),
				'sale_schedule' => __( 'Sale Schedule', 'bulk-edit-learnpress-prices' ),
				'price_history' => __( 'Price Changes', 'bulk-edit-learnpress-prices' ),
				'status'        => __( 'Status', 'bulk-edit-learnpress-prices' ),
				'instructor'    => __( 'Instructor', 'bulk-edit-learnpress-prices' ),
			);
		}

		/**
		 * Get sortable columns.
		 *
		 * @return array
		 */
		protected function get_sortable_columns() {
			return array(
				'id'            => array( 'id', false ),
				'title'         => array( 'title', true ),
				'regular_price' => array( 'regular_price', false ),
				'sale_price'    => array( 'sale_price', false ),
				'status'        => array( 'status', false ),
			);
		}

		/**
		 * Render the checkbox column.
		 *
		 * @param array $item Course row.
		 * @return string
		 */
		protected function column_cb( $item ) {
			if ( ! $this->can_manage ) {
				return '';
			}

			return sprintf(
				'<input type="checkbox" name="course_ids[]" value="%s" />',
				esc_attr( absint( $item['id'] ) )
			);
		}

		/**
		 * Render the course ID column.
		 *
		 * @param array $item Course row.
		 * @return string
		 */
		protected function column_id( $item ) {
			return (string) absint( $item['id'] );
		}

		/**
		 * Render the course title column.
		 *
		 * @param array $item Course row.
		 * @return string
		 */
		protected function column_title( $item ) {
			$title = isset( $item['title'] ) && '' !== $item['title'] ? $item['title'] : __( '(no title)', 'bulk-edit-learnpress-prices' );

			if ( ! empty( $item['edit_link'] ) ) {
				return sprintf(
					'<strong><a href="%1$s">%2$s</a></strong>',
					esc_url( $item['edit_link'] ),
					esc_html( $title )
				);
			}

			return sprintf( '<strong>%s</strong>', esc_html( $title ) );
		}

		/**
		 * Render the regular price column.
		 *
		 * @param array $item Course row.
		 * @return string
		 */
		protected function column_regular_price( $item ) {
			return isset( $item['regular_price_display'] ) ? wp_kses_post( $item['regular_price_display'] ) : '&mdash;';
		}

		/**
		 * Render the sale price column.
		 *
		 * @param array $item Course row.
		 * @return string
		 */
		protected function column_sale_price( $item ) {
			if ( empty( $item['sale_price'] ) || (float) $item['sale_price'] <= 0 ) {
				return '&mdash;';
			}

			return isset( $item['sale_price_display'] ) ? wp_kses_post( $item['sale_price_display'] ) : '&mdash;';
		}

		/**
		 * Render the sale schedule column.
		 *
		 * @param array $item Course row.
		 * @return string
		 */
		protected function column_sale_schedule( $item ) {
			return ! empty( $item['sale_schedule_display'] ) ? esc_html( $item['sale_schedule_display'] ) : '&mdash;';
		}

		/**
		 * Render the price history column.
		 *
		 * @param array $item Course row.
		 * @return string
		 */
		protected function column_price_history( $item ) {
			$count = isset( $item['price_history_count'] ) ? absint( $item['price_history_count'] ) : 0;

			if ( ! $this->can_manage ) {
				return sprintf(
					/* translators: %d: number of recorded price changes. */
					esc_html( _n( '%d change', '%d changes', $count, 'bulk-edit-learnpress-prices' ) ),
					$count
				);
			}

			return sprintf(
				'<button type="button" class="button button-small" data-view-price-history data-course-id="%1$s">%2$s</button>',
				esc_attr( absint( $item['id'] ) ),
				esc_html(
					sprintf(
						/* translators: %d: number of recorded price changes. */
						_n( '%d change', '%d changes', $count, 'bulk-edit-learnpress-prices' ),
						$count
					)
				)
			);
		}

		/**
		 * Render the status column.
		 *
		 * @param array $item Course row.
		 * @return string
		 */
		protected function column_status( $item ) {
			return isset( $item['status_label'] ) ? esc_html( $item['status_label'] ) : '';
		}

		/**
		 * Render the instructor column.
		 *
		 * @param array $item Course row.
		 * @return string
		 */
		protected function column_instructor( $item ) {
			return isset( $item['instructor_name'] ) ? esc_html( $item['instructor_name'] ) : '';
		}

		/**
		 * Render a default column.
		 *
		 * @param array  $item        Course row.
		 * @param string $column_name Column name.
		 * @return string
		 */
		protected function column_default( $item, $column_name ) {
			if ( isset( $item[ $column_name ] ) && is_scalar( $item[ $column_name ] ) ) {
				return esc_html( (string) $item[ $column_name ] );
			}

			return '';
		}

		/**
		 * No-items message.
		 *
		 * @return void
		 */
		public function no_items() {
			if ( ! empty( $this->errors ) ) {
				echo esc_html( reset( $this->errors ) );
				return;
			}

			esc_html_e( 'No LearnPress courses match the current view.', 'bulk-edit-learnpress-prices' );
		}

		/**
		 * Avoid list-table bulk actions for price mutations.
		 *
		 * @return array
		 */
		protected function get_bulk_actions() {
			return array();
		}

		/**
		 * Prepare items for display.
		 *
		 * @return void
		 */
		public function prepare_items() {
			$columns               = $this->get_columns();
			$hidden                = get_hidden_columns( get_current_screen() );
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable, 'title' );

			if ( ! $this->plugin || ! method_exists( $this->plugin, 'query_courses' ) ) {
				$this->items  = array();
				$this->errors = array( __( 'The course query service is unavailable.', 'bulk-edit-learnpress-prices' ) );
				$this->set_pagination_args(
					array(
						'total_items' => 0,
						'per_page'    => $this->get_items_per_page( 'belpcp_courses_per_page', 20 ),
						'total_pages' => 0,
					)
				);
				return;
			}

			$query_result  = $this->plugin->query_courses( $this->get_request_filters() );
			$this->items   = isset( $query_result['items'] ) ? $query_result['items'] : array();
			$this->errors  = isset( $query_result['errors'] ) ? $query_result['errors'] : array();
			$this->filters = isset( $query_result['filters'] ) ? $query_result['filters'] : array();

			$this->set_pagination_args(
				array(
					'total_items' => isset( $query_result['total_items'] ) ? absint( $query_result['total_items'] ) : 0,
					'per_page'    => isset( $this->filters['per_page'] ) ? absint( $this->filters['per_page'] ) : $this->get_items_per_page( 'belpcp_courses_per_page', 20 ),
					'total_pages' => isset( $query_result['total_pages'] ) ? absint( $query_result['total_pages'] ) : 0,
				)
			);
		}

		/**
		 * Get the sanitized filters used by the current table view.
		 *
		 * @return array
		 */
		public function get_filters() {
			return $this->filters;
		}

		/**
		 * Get validation errors returned while preparing the current table view.
		 *
		 * @return array
		 */
		public function get_errors() {
			return $this->errors;
		}

		/**
		 * Get request filters for the current table view.
		 *
		 * @return array
		 */
		protected function get_request_filters() {
			$request = $this->filters;

			if ( isset( $_GET ) && is_array( $_GET ) ) {
				$request = wp_parse_args( $request, $_GET );
			}

			$request['paged']    = isset( $request['paged'] ) ? max( 1, absint( $request['paged'] ) ) : $this->get_pagenum();
			$request['per_page'] = isset( $request['per_page'] ) ? min( 200, max( 1, absint( $request['per_page'] ) ) ) : $this->get_items_per_page( 'belpcp_courses_per_page', 20 );

			if ( isset( $request['orderby'] ) ) {
				$request['orderby'] = sanitize_key( wp_unslash( $request['orderby'] ) );
			}

			if ( isset( $request['order'] ) ) {
				$request['order'] = sanitize_key( wp_unslash( $request['order'] ) );
			}

			return $request;
		}
	}
}
