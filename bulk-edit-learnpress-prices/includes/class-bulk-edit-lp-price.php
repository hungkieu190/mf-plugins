<?php
/**
 * Main plugin controller.
 *
 * @package Bulk_Edit_LearnPress_Prices
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Bulk_Edit_LP_Price' ) ) {
	/**
	 * Core plugin class.
	 */
	class Bulk_Edit_LP_Price {
		/**
		 * Single plugin instance.
		 *
		 * @var Bulk_Edit_LP_Price|null
		 */
		private static $instance = null;

		/**
		 * Whether hooks have been registered.
		 *
		 * @var bool
		 */
		private $hooks_registered = false;

		/**
		 * Admin page hook suffix.
		 *
		 * @var string
		 */
		private $admin_page_hook = '';

		/**
		 * Admin page slug.
		 *
		 * @var string
		 */
		private $admin_page_slug = 'bulk-edit-learnpress-prices';

		/**
		 * Get the single plugin instance.
		 *
		 * @return Bulk_Edit_LP_Price
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Register plugin hooks.
		 *
		 * @return void
		 */
		public function register_hooks() {
			if ( $this->hooks_registered ) {
				return;
			}

			$this->hooks_registered = true;

			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 99 );
			add_action( 'admin_notices', array( $this, 'maybe_render_learnpress_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
			add_action( 'wp_ajax_bulk_edit_lp_load_courses', array( $this, 'ajax_load_courses' ) );
			add_action( 'wp_ajax_bulk_edit_lp_load_price_history', array( $this, 'ajax_load_price_history' ) );
			add_action( 'wp_ajax_bulk_edit_lp_preview_changes', array( $this, 'ajax_preview_changes' ) );
			add_action( 'wp_ajax_bulk_edit_lp_update_prices', array( $this, 'ajax_update_prices' ) );
			add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
		}

		/**
		 * Activation callback.
		 *
		 * @return void
		 */
		public static function activate() {
			update_option( 'belpcp_version', BELPCP_VERSION, false );
		}

		/**
		 * Deactivation callback.
		 *
		 * @return void
		 */
		public static function deactivate() {
			delete_transient( 'belpcp_learnpress_missing_notice_dismissed' );
		}

		/**
		 * Load translation files.
		 *
		 * @return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain(
				'bulk-edit-learnpress-prices',
				false,
				dirname( BELPCP_BASENAME ) . '/languages'
			);
		}

		/**
		 * Check whether LearnPress course support appears available.
		 *
		 * @return bool
		 */
		public function is_learnpress_available() {
			if ( post_type_exists( BELPCP_COURSE_POST_TYPE ) ) {
				return true;
			}

			return class_exists( 'LearnPress' ) || function_exists( 'learn_press_get_course' );
		}

		/**
		 * Render a dependency notice when LearnPress is not available.
		 *
		 * @return void
		 */
		public function maybe_render_learnpress_notice() {
			if ( ! is_admin() || $this->is_learnpress_available() || ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html__( 'Bulk Edit LearnPress Course Prices requires LearnPress to manage course prices. Activate LearnPress to use this plugin.', 'bulk-edit-learnpress-prices' )
			);
		}

		/**
		 * Register the plugin admin page.
		 *
		 * @return void
		 */
		public function register_admin_menu() {
			$page_title = __( 'Bulk Edit LearnPress Course Prices', 'bulk-edit-learnpress-prices' );
			$menu_title = __( 'Bulk Edit Prices', 'bulk-edit-learnpress-prices' );
			$capability = $this->get_required_capability();

			if ( $this->admin_parent_menu_exists( 'learn_press' ) ) {
				$this->admin_page_hook = add_submenu_page(
					'learn_press',
					$page_title,
					$menu_title,
					$capability,
					$this->admin_page_slug,
					array( $this, 'render_admin_page' )
				);
			} else {
				$this->admin_page_hook = add_management_page(
					$page_title,
					$menu_title,
					$capability,
					$this->admin_page_slug,
					array( $this, 'render_admin_page' )
				);
			}

			if ( $this->admin_page_hook ) {
				add_action( 'load-' . $this->admin_page_hook, array( $this, 'register_screen_options' ) );
			}
		}

		/**
		 * Register screen options for the admin page.
		 *
		 * @return void
		 */
		public function register_screen_options() {
			add_screen_option(
				'per_page',
				array(
					'label'   => __( 'Courses per page', 'bulk-edit-learnpress-prices' ),
					'default' => 20,
					'option'  => 'belpcp_courses_per_page',
				)
			);
		}

		/**
		 * Persist plugin screen options.
		 *
		 * @param mixed  $status Screen option save status.
		 * @param string $option Option name.
		 * @param mixed  $value  Submitted value.
		 * @return mixed
		 */
		public function set_screen_option( $status, $option, $value ) {
			if ( 'belpcp_courses_per_page' !== $option ) {
				return $status;
			}

			$value = absint( $value );

			return min( 200, max( 1, $value ) );
		}

		/**
		 * Enqueue admin assets only on the plugin admin page.
		 *
		 * @param string $hook_suffix Current admin page hook suffix.
		 * @return void
		 */
		public function enqueue_admin_assets( $hook_suffix ) {
			if ( $hook_suffix !== $this->admin_page_hook ) {
				return;
			}

			wp_enqueue_style(
				'bulk-edit-learnpress-prices-admin',
				BELPCP_URL . 'assets/css/style.css',
				array(),
				BELPCP_VERSION
			);

			wp_enqueue_script(
				'bulk-edit-learnpress-prices-admin',
				BELPCP_URL . 'assets/js/script.js',
				array(),
				BELPCP_VERSION,
				true
			);

			wp_localize_script(
				'bulk-edit-learnpress-prices-admin',
				'BELPCPAdmin',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'bulk_edit_lp_prices_ajax' ),
					'maxSelectedCourses' => BELPCP_MAX_SELECTED_COURSES,
					'actions' => array(
						'loadCourses'    => 'bulk_edit_lp_load_courses',
						'loadHistory'    => 'bulk_edit_lp_load_price_history',
						'previewChanges' => 'bulk_edit_lp_preview_changes',
						'updatePrices'   => 'bulk_edit_lp_update_prices',
					),
					'strings' => array(
						'previewUnavailable' => __( 'Preview is unavailable in this browser session.', 'bulk-edit-learnpress-prices' ),
						'previewFailed'      => __( 'Unable to generate preview. Refresh the page and try again.', 'bulk-edit-learnpress-prices' ),
						'loadUnavailable'    => __( 'Course loading is unavailable in this browser session.', 'bulk-edit-learnpress-prices' ),
						'loadFailed'         => __( 'Unable to load courses. Refresh the page and try again.', 'bulk-edit-learnpress-prices' ),
						'loadingCourses'     => __( 'Loading courses...', 'bulk-edit-learnpress-prices' ),
						'historyUnavailable' => __( 'Price history is unavailable in this browser session.', 'bulk-edit-learnpress-prices' ),
						'historyFailed'      => __( 'Unable to load price history. Refresh the page and try again.', 'bulk-edit-learnpress-prices' ),
						'noHistory'          => __( 'No price changes have been recorded for this course yet.', 'bulk-edit-learnpress-prices' ),
						'change'             => __( 'change', 'bulk-edit-learnpress-prices' ),
						'changes'            => __( 'changes', 'bulk-edit-learnpress-prices' ),
						'scheduleWindow'     => __( 'Sale schedule', 'bulk-edit-learnpress-prices' ),
						'updateUnavailable'  => __( 'Updates are unavailable in this browser session.', 'bulk-edit-learnpress-prices' ),
						'updateFailed'       => __( 'Unable to apply updates. Refresh the page and try again.', 'bulk-edit-learnpress-prices' ),
						'updated'            => __( 'updated', 'bulk-edit-learnpress-prices' ),
						'failed'             => __( 'failed', 'bulk-edit-learnpress-prices' ),
						'ready'              => __( 'ready', 'bulk-edit-learnpress-prices' ),
						'skipped'            => __( 'skipped', 'bulk-edit-learnpress-prices' ),
						'warnings'           => __( 'warnings', 'bulk-edit-learnpress-prices' ),
					),
				)
			);
		}

		/**
		 * Render the admin page.
		 *
		 * @return void
		 */
		public function render_admin_page() {
			if ( ! $this->current_user_can_manage() ) {
				wp_die(
					esc_html__( 'You do not have permission to manage LearnPress course prices.', 'bulk-edit-learnpress-prices' ),
					esc_html__( 'Permission denied', 'bulk-edit-learnpress-prices' ),
					array( 'response' => 403 )
				);
			}

			$page_title   = __( 'Bulk Edit LearnPress Course Prices', 'bulk-edit-learnpress-prices' );
			$page_slug    = $this->admin_page_slug;
			$nonce_action = 'bulk_edit_lp_prices_admin';
			$nonce_name   = 'bulk_edit_lp_prices_nonce';
			$this->include_list_table();
			$list_table   = class_exists( 'LP_Price_List_Table' ) ? new LP_Price_List_Table( $this, array(), $this->current_user_can_manage() ) : null;

			if ( $list_table ) {
				$list_table->prepare_items();
			}

			$current_filters    = $list_table && method_exists( $list_table, 'get_filters' ) ? $list_table->get_filters() : $this->sanitize_course_filters( $_GET );
			$filter_errors      = $list_table && method_exists( $list_table, 'get_errors' ) ? $list_table->get_errors() : array();
			$category_options   = $this->get_course_category_options();
			$instructor_options = $this->get_instructor_options();
			$status_options     = $this->get_supported_post_statuses();
			$bulk_actions       = $this->get_supported_bulk_actions();

			include BELPCP_DIR . 'templates/admin-page.php';
		}

		/**
		 * Load the admin list table class only when the table is needed.
		 *
		 * @return void
		 */
		private function include_list_table() {
			if ( class_exists( 'LP_Price_List_Table' ) ) {
				return;
			}

			require_once BELPCP_DIR . 'includes/class-lp-price-list-table.php';
		}

		/**
		 * Render notices scoped to the plugin admin page.
		 *
		 * @return void
		 */
		public function render_admin_page_notices() {
			if ( ! $this->is_learnpress_available() ) {
				printf(
					'<div class="notice notice-warning inline"><p>%s</p></div>',
					esc_html__( 'LearnPress is not available. Course filters and price editing will be enabled after LearnPress is active.', 'bulk-edit-learnpress-prices' )
				);
			}

			$status = isset( $_GET['belpcp_status'] ) ? sanitize_key( wp_unslash( $_GET['belpcp_status'] ) ) : '';

			if ( '' === $status ) {
				return;
			}

			$messages = array(
				'updated' => array(
					'class'   => 'notice-success',
					'message' => __( 'Course prices were updated.', 'bulk-edit-learnpress-prices' ),
				),
				'error'   => array(
					'class'   => 'notice-error',
					'message' => __( 'Course prices could not be updated. Review the selected courses and try again.', 'bulk-edit-learnpress-prices' ),
				),
			);

			if ( empty( $messages[ $status ] ) ) {
				return;
			}

			printf(
				'<div class="notice %1$s inline"><p>%2$s</p></div>',
				esc_attr( $messages[ $status ]['class'] ),
				esc_html( $messages[ $status ]['message'] )
			);
		}

		/**
		 * Get the required capability for plugin access.
		 *
		 * @return string
		 */
		public function get_required_capability() {
			$capability = 'manage_options';

			if ( $this->course_capability_exists( 'edit_others_lp_courses' ) ) {
				$capability = 'edit_others_lp_courses';
			}

			/**
			 * Filter the required capability for managing bulk LearnPress price edits.
			 *
			 * @param string $capability Required capability.
			 */
			return (string) apply_filters( 'bulk_edit_lp_price_capability', $capability );
		}

		/**
		 * Check whether the current user can manage this plugin.
		 *
		 * @return bool
		 */
		public function current_user_can_manage() {
			return current_user_can( $this->get_required_capability() );
		}

		/**
		 * Get the admin page hook suffix.
		 *
		 * @return string
		 */
		public function get_admin_page_hook() {
			return $this->admin_page_hook;
		}

		/**
		 * Get the admin page slug.
		 *
		 * @return string
		 */
		public function get_admin_page_slug() {
			return $this->admin_page_slug;
		}

		/**
		 * Get supported course statuses for filtering.
		 *
		 * @return array
		 */
		public function get_supported_post_statuses() {
			$statuses = get_post_stati( array(), 'objects' );
			$allowed  = array();

			foreach ( $statuses as $status => $status_object ) {
				if ( in_array( $status, array( 'trash', 'auto-draft', 'inherit' ), true ) ) {
					continue;
				}

				if ( ! empty( $status_object->internal ) && ! in_array( $status, array( 'draft', 'pending', 'private' ), true ) ) {
					continue;
				}

				$allowed[ $status ] = $status_object->label;
			}

			return apply_filters( 'bulk_edit_lp_price_supported_post_statuses', $allowed );
		}

		/**
		 * Get the default course filter state.
		 *
		 * @return array
		 */
		public function get_default_course_filters() {
			return array(
				'course_type' => 'all',
				'category_id' => 0,
				'min_price'   => '',
				'max_price'   => '',
				'instructor'  => 0,
				'post_status' => 'publish',
				'search'      => '',
				'paged'       => 1,
				'per_page'    => 20,
				'orderby'     => 'title',
				'order'       => 'ASC',
				'errors'      => array(),
			);
		}

		/**
		 * Sanitize course filters from request or caller data.
		 *
		 * @param array $raw_filters Raw filter values.
		 * @return array
		 */
		public function sanitize_course_filters( $raw_filters ) {
			$raw_filters = is_array( $raw_filters ) ? wp_unslash( $raw_filters ) : array();
			$filters     = $this->get_default_course_filters();
			$errors      = array();

			if ( isset( $raw_filters['course_type'] ) ) {
				$course_type = sanitize_key( $raw_filters['course_type'] );
				if ( in_array( $course_type, array( 'all', 'paid', 'free' ), true ) ) {
					$filters['course_type'] = $course_type;
				}
			}

			if ( isset( $raw_filters['category_id'] ) ) {
				$filters['category_id'] = absint( $raw_filters['category_id'] );
			}

			if ( isset( $raw_filters['instructor'] ) ) {
				$filters['instructor'] = absint( $raw_filters['instructor'] );
			}

			if ( isset( $raw_filters['post_status'] ) ) {
				$post_status        = sanitize_key( $raw_filters['post_status'] );
				$supported_statuses = $this->get_supported_post_statuses();

				if ( 'any' === $post_status || isset( $supported_statuses[ $post_status ] ) ) {
					$filters['post_status'] = $post_status;
				}
			}

			if ( isset( $raw_filters['search'] ) ) {
				$filters['search'] = sanitize_text_field( $raw_filters['search'] );
			}

			$filters['min_price'] = $this->sanitize_filter_price( isset( $raw_filters['min_price'] ) ? $raw_filters['min_price'] : '' );
			$filters['max_price'] = $this->sanitize_filter_price( isset( $raw_filters['max_price'] ) ? $raw_filters['max_price'] : '' );

			if ( '' !== $filters['min_price'] && '' !== $filters['max_price'] && (float) $filters['min_price'] > (float) $filters['max_price'] ) {
				$errors[] = __( 'Minimum price cannot be greater than maximum price.', 'bulk-edit-learnpress-prices' );
			}

			if ( isset( $raw_filters['paged'] ) ) {
				$filters['paged'] = max( 1, absint( $raw_filters['paged'] ) );
			}

			if ( isset( $raw_filters['per_page'] ) ) {
				$filters['per_page'] = min( 200, max( 1, absint( $raw_filters['per_page'] ) ) );
			}

			if ( isset( $raw_filters['orderby'] ) ) {
				$orderby = sanitize_key( $raw_filters['orderby'] );
				if ( in_array( $orderby, array( 'ID', 'id', 'title', 'regular_price', 'sale_price', 'status' ), true ) ) {
					$filters['orderby'] = 'id' === $orderby ? 'ID' : $orderby;
				}
			}

			if ( isset( $raw_filters['order'] ) ) {
				$order = strtoupper( sanitize_key( $raw_filters['order'] ) );
				if ( in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
					$filters['order'] = $order;
				}
			}

			$filters['errors'] = $errors;

			return apply_filters( 'bulk_edit_lp_price_sanitized_course_filters', $filters, $raw_filters );
		}

		/**
		 * Build course query arguments from sanitized filters.
		 *
		 * @param array $filters Sanitized filter values.
		 * @param array $args    Optional query behavior overrides.
		 * @return array
		 */
		public function build_course_query_args( $filters, $args = array() ) {
			$filters = wp_parse_args( is_array( $filters ) ? $filters : array(), $this->get_default_course_filters() );
			$args    = wp_parse_args(
				is_array( $args ) ? $args : array(),
				array(
					'fields'        => '',
					'no_found_rows' => false,
					'limit'         => 0,
				)
			);

			$query_args = array(
				'post_type'              => BELPCP_COURSE_POST_TYPE,
				'post_status'            => 'any' === $filters['post_status'] ? array_keys( $this->get_supported_post_statuses() ) : $filters['post_status'],
				'posts_per_page'         => $args['limit'] ? absint( $args['limit'] ) : absint( $filters['per_page'] ),
				'paged'                  => absint( $filters['paged'] ),
				'orderby'                => 'title',
				'order'                  => $filters['order'],
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => (bool) $args['no_found_rows'],
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			);

			if ( $args['fields'] ) {
				$query_args['fields'] = $args['fields'];
			}

			if ( '' !== $filters['search'] ) {
				$query_args['s'] = $filters['search'];
			}

			if ( $filters['instructor'] ) {
				$query_args['author'] = absint( $filters['instructor'] );
			}

			$tax_query = $this->build_course_tax_query( $filters );
			if ( ! empty( $tax_query ) ) {
				$query_args['tax_query'] = $tax_query;
			}

			$meta_query = $this->build_course_meta_query( $filters );
			if ( ! empty( $meta_query ) ) {
				$query_args['meta_query'] = $meta_query;
			}

			switch ( $filters['orderby'] ) {
				case 'ID':
					$query_args['orderby'] = 'ID';
					break;
				case 'regular_price':
					$query_args['meta_key'] = BELPCP_REGULAR_PRICE_META_KEY;
					$query_args['orderby']  = 'meta_value_num';
					break;
				case 'sale_price':
					$query_args['meta_key'] = BELPCP_SALE_PRICE_META_KEY;
					$query_args['orderby']  = 'meta_value_num';
					break;
				case 'status':
					$query_args['orderby'] = 'post_status';
					break;
				default:
					$query_args['orderby'] = 'title';
					break;
			}

			return $this->filter_course_query_args( $query_args, $filters );
		}

		/**
		 * Query paginated courses and return rows plus counts.
		 *
		 * @param array $raw_filters Raw or sanitized filters.
		 * @return array
		 */
		public function query_courses( $raw_filters = array() ) {
			$filters = $this->sanitize_course_filters( $raw_filters );

			if ( ! empty( $filters['errors'] ) ) {
				return array(
					'items'       => array(),
					'total_items' => 0,
					'total_pages' => 0,
					'filters'     => $filters,
					'errors'      => $filters['errors'],
				);
			}

			$query = new WP_Query( $this->build_course_query_args( $filters ) );

			return array(
				'items'       => $this->prepare_course_rows( $query->posts ),
				'total_items' => absint( $query->found_posts ),
				'total_pages' => absint( $query->max_num_pages ),
				'filters'     => $filters,
				'errors'      => array(),
			);
		}

		/**
		 * Query filtered course IDs for future all-filtered operations.
		 *
		 * @param array $raw_filters Raw or sanitized filters.
		 * @param int   $limit       Maximum IDs to return.
		 * @return array
		 */
		public function query_filtered_course_ids( $raw_filters = array(), $limit = 1000 ) {
			$filters = $this->sanitize_course_filters( $raw_filters );

			if ( ! empty( $filters['errors'] ) ) {
				return array();
			}

			$query_args = $this->build_course_query_args(
				$filters,
				array(
					'fields'        => 'ids',
					'no_found_rows' => true,
					'limit'         => min( 5000, max( 1, absint( $limit ) ) ),
				)
			);

			$query_args['paged'] = 1;

			return array_map( 'absint', get_posts( $query_args ) );
		}

		/**
		 * Prepare course row data with required meta only.
		 *
		 * @param array $posts Course post objects.
		 * @return array
		 */
		public function prepare_course_rows( $posts ) {
			$rows = array();

			if ( empty( $posts ) || ! is_array( $posts ) ) {
				return $rows;
			}

			foreach ( $posts as $post ) {
				if ( ! $post instanceof WP_Post ) {
					continue;
				}

				$rows[] = $this->get_course_row( $post );
			}

			return $rows;
		}

		/**
		 * Get normalized row data for a course.
		 *
		 * @param WP_Post $post Course post.
		 * @return array
		 */
		public function get_course_row( WP_Post $post ) {
			$regular_price = $this->get_course_regular_price_raw( $post->ID );
			$sale_price    = $this->get_course_sale_price_raw( $post->ID );
			$sale_start    = $this->get_course_sale_start_raw( $post->ID );
			$sale_end      = $this->get_course_sale_end_raw( $post->ID );

			return array(
				'id'                    => absint( $post->ID ),
				'title'                 => get_the_title( $post ),
				'edit_link'             => get_edit_post_link( $post->ID, 'raw' ),
				'regular_price'         => $this->normalize_price( $regular_price ),
				'regular_price_raw'     => $regular_price,
				'regular_price_display' => $this->format_price( $regular_price ),
				'sale_price'            => $this->normalize_price( $sale_price ),
				'sale_price_raw'        => $sale_price,
				'sale_price_display'    => $this->format_price( $sale_price ),
				'sale_start'            => (string) $sale_start,
				'sale_end'              => (string) $sale_end,
				'sale_schedule_display' => $this->format_sale_schedule_text( $sale_start, $sale_end ),
				'status'                => $post->post_status,
				'status_label'          => $this->get_post_status_label( $post->post_status ),
				'instructor_id'         => absint( $post->post_author ),
				'instructor_name'       => get_the_author_meta( 'display_name', $post->post_author ),
				'price_state'           => $this->get_course_price_state( $post->ID ),
				'price_history_count'   => $this->get_course_price_history_count( $post->ID ),
			);
		}

		/**
		 * Get LearnPress course category taxonomy.
		 *
		 * @return string
		 */
		public function get_course_category_taxonomy() {
			$candidates = apply_filters(
				'bulk_edit_lp_price_course_category_taxonomies',
				array(
					'course_category',
					'lp_course_category',
				)
			);

			foreach ( (array) $candidates as $taxonomy ) {
				$taxonomy = sanitize_key( $taxonomy );
				if ( taxonomy_exists( $taxonomy ) ) {
					return $taxonomy;
				}
			}

			return '';
		}

		/**
		 * Get course category filter options.
		 *
		 * @return array
		 */
		public function get_course_category_options() {
			$taxonomy = $this->get_course_category_taxonomy();

			if ( '' === $taxonomy ) {
				return array();
			}

			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				return array();
			}

			$options = array();

			foreach ( $terms as $term ) {
				$options[ absint( $term->term_id ) ] = $term->name;
			}

			return apply_filters( 'bulk_edit_lp_price_category_options', $options, $taxonomy );
		}

		/**
		 * Query instructor options from course authors.
		 *
		 * @return array
		 */
		public function get_instructor_options() {
			global $wpdb;

			$author_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE post_type = %s AND post_author > 0 ORDER BY post_author ASC",
					BELPCP_COURSE_POST_TYPE
				)
			);

			if ( empty( $author_ids ) ) {
				return array();
			}

			$users   = get_users(
				array(
					'include' => array_map( 'absint', $author_ids ),
					'fields'  => array( 'ID', 'display_name', 'user_login' ),
				)
			);
			$options = array();

			foreach ( $users as $user ) {
				$options[ absint( $user->ID ) ] = $user->display_name ? $user->display_name : $user->user_login;
			}

			asort( $options, SORT_NATURAL | SORT_FLAG_CASE );

			return apply_filters( 'bulk_edit_lp_price_instructor_options', $options );
		}

		/**
		 * Get supported price bulk actions.
		 *
		 * @return array
		 */
		public function get_supported_bulk_actions() {
			$actions = array(
				'set_regular_price'   => __( 'Set Regular Price', 'bulk-edit-learnpress-prices' ),
				'set_sale_price'      => __( 'Set Sale Price', 'bulk-edit-learnpress-prices' ),
				'schedule_sale_price' => __( 'Schedule Sale Price', 'bulk-edit-learnpress-prices' ),
				'remove_sale_price'   => __( 'Remove Sale Price', 'bulk-edit-learnpress-prices' ),
				'increase_percentage' => __( 'Increase Price by Percentage', 'bulk-edit-learnpress-prices' ),
				'decrease_percentage' => __( 'Decrease Price by Percentage', 'bulk-edit-learnpress-prices' ),
			);

			return apply_filters( 'bulk_edit_lp_price_supported_bulk_actions', $actions );
		}

		/**
		 * Normalize a price value.
		 *
		 * @param mixed $value Raw price.
		 * @return string
		 */
		public function normalize_price( $value ) {
			return belpcp_normalize_price( $value );
		}

		/**
		 * Format a price value.
		 *
		 * @param mixed $value Raw price.
		 * @return string
		 */
		public function format_price( $value ) {
			return belpcp_format_price( $value );
		}

		/**
		 * Format a price value for JSON/text responses.
		 *
		 * @param mixed $value Raw price.
		 * @return string
		 */
		public function format_price_text( $value ) {
			$charset = get_bloginfo( 'charset' );
			$charset = $charset ? $charset : 'UTF-8';

			return html_entity_decode( wp_strip_all_tags( $this->format_price( $value ) ), ENT_QUOTES, $charset );
		}

		/**
		 * Determine a course price state.
		 *
		 * @param int $course_id Course post ID.
		 * @return string
		 */
		public function get_course_price_state( $course_id ) {
			return belpcp_get_course_price_state( $course_id );
		}

		/**
		 * Get the regular price value LearnPress shows in the course edit form.
		 *
		 * LearnPress stores the editable regular price in `_lp_regular_price` and
		 * falls back to `_lp_price` for older course data.
		 *
		 * @param int $course_id Course post ID.
		 * @return mixed
		 */
		private function get_course_regular_price_raw( $course_id ) {
			if ( metadata_exists( 'post', $course_id, BELPCP_REGULAR_PRICE_META_KEY ) ) {
				return get_post_meta( $course_id, BELPCP_REGULAR_PRICE_META_KEY, true );
			}

			return get_post_meta( $course_id, BELPCP_ACTIVE_PRICE_META_KEY, true );
		}

		/**
		 * Get the LearnPress sale price value.
		 *
		 * @param int $course_id Course post ID.
		 * @return mixed
		 */
		private function get_course_sale_price_raw( $course_id ) {
			return get_post_meta( $course_id, BELPCP_SALE_PRICE_META_KEY, true );
		}

		/**
		 * Get the LearnPress sale start value.
		 *
		 * @param int $course_id Course post ID.
		 * @return mixed
		 */
		private function get_course_sale_start_raw( $course_id ) {
			return get_post_meta( $course_id, BELPCP_SALE_START_META_KEY, true );
		}

		/**
		 * Get the LearnPress sale end value.
		 *
		 * @param int $course_id Course post ID.
		 * @return mixed
		 */
		private function get_course_sale_end_raw( $course_id ) {
			return get_post_meta( $course_id, BELPCP_SALE_END_META_KEY, true );
		}

		/**
		 * Determine the active `_lp_price` value LearnPress should cache/query.
		 *
		 * @param string $regular_price Normalized regular price.
		 * @param string $sale_price    Normalized sale price.
		 * @param string $sale_start    Sale start datetime.
		 * @param string $sale_end      Sale end datetime.
		 * @return string
		 */
		private function get_course_active_price_value( $regular_price, $sale_price, $sale_start = '', $sale_end = '' ) {
			$regular = '' === (string) $regular_price ? '0.00' : $this->normalize_price( $regular_price );
			$sale    = $this->normalize_price( $sale_price );

			if ( '' !== $sale && (float) $sale <= (float) $regular ) {
				if ( '' !== (string) $sale_start && '' !== (string) $sale_end ) {
					$now   = strtotime( current_time( 'mysql' ) );
					$start = strtotime( $sale_start );
					$end   = strtotime( $sale_end );

					if ( ! $start || ! $end || $now < $start || $now > $end ) {
						return $regular;
					}
				}

				return $sale;
			}

			return $regular;
		}

		/**
		 * Keep LearnPress' active price meta in sync after regular/sale changes.
		 *
		 * @param int    $course_id     Course post ID.
		 * @param string $regular_price Normalized regular price.
		 * @param string $sale_price    Normalized sale price.
		 * @param string $sale_start    Sale start datetime.
		 * @param string $sale_end      Sale end datetime.
		 * @return bool
		 */
		private function sync_course_active_price_meta( $course_id, $regular_price, $sale_price, $sale_start = '', $sale_end = '' ) {
			$active_price  = $this->get_course_active_price_value( $regular_price, $sale_price, $sale_start, $sale_end );
			$current_price = $this->normalize_price( get_post_meta( $course_id, BELPCP_ACTIVE_PRICE_META_KEY, true ) );

			return $this->save_course_price_meta( $course_id, BELPCP_ACTIVE_PRICE_META_KEY, $active_price, $current_price );
		}

		/**
		 * Filter course query arguments before courses are loaded.
		 *
		 * @param array $query_args Course query arguments.
		 * @param array $filters    Sanitized filter state.
		 * @return array
		 */
		public function filter_course_query_args( $query_args, $filters = array() ) {
			return (array) apply_filters( 'bulk_edit_lp_price_course_query_args', $query_args, $filters );
		}

		/**
		 * Filter preview rows before returning them to the caller.
		 *
		 * @param array $rows    Preview rows.
		 * @param array $context Preview context.
		 * @return array
		 */
		public function filter_preview_rows( $rows, $context = array() ) {
			return (array) apply_filters( 'bulk_edit_lp_price_preview_rows', $rows, $context );
		}

		/**
		 * Filter an update result summary.
		 *
		 * @param array $summary Update summary.
		 * @param array $context Update context.
		 * @return array
		 */
		public function filter_update_summary( $summary, $context = array() ) {
			return (array) apply_filters( 'bulk_edit_lp_price_update_summary', $summary, $context );
		}

		/**
		 * Fire before a course price update is applied.
		 *
		 * @param int   $course_id Course post ID.
		 * @param array $changes   Planned changes.
		 * @param array $context   Update context.
		 * @return void
		 */
		public function do_before_course_update( $course_id, $changes = array(), $context = array() ) {
			/**
			 * Fires before a LearnPress course price is updated.
			 *
			 * @param int   $course_id Course post ID.
			 * @param array $changes   Planned changes.
			 * @param array $context   Update context.
			 */
			do_action( 'bulk_edit_lp_price_before_course_update', $course_id, $changes, $context );
		}

		/**
		 * Fire after a course price update is applied.
		 *
		 * @param int   $course_id Course post ID.
		 * @param array $changes   Applied changes.
		 * @param array $result    Update result.
		 * @param array $context   Update context.
		 * @return void
		 */
		public function do_after_course_update( $course_id, $changes = array(), $result = array(), $context = array() ) {
			/**
			 * Fires after a LearnPress course price is updated.
			 *
			 * @param int   $course_id Course post ID.
			 * @param array $changes   Applied changes.
			 * @param array $result    Update result.
			 * @param array $context   Update context.
			 */
			do_action( 'bulk_edit_lp_price_after_course_update', $course_id, $changes, $result, $context );
		}

		/**
		 * Handle AJAX requests for loading courses.
		 *
		 * @return void
		 */
		public function ajax_load_courses() {
			$this->verify_ajax_request();

			$data    = $this->get_ajax_request_data();
			$filters = isset( $data['filters'] ) && is_array( $data['filters'] ) ? $data['filters'] : $data;
			$result  = $this->query_courses( $filters );

			if ( ! empty( $result['errors'] ) ) {
				$this->send_ajax_error(
					__( 'Course filters are invalid.', 'bulk-edit-learnpress-prices' ),
					'invalid_filters',
					400,
					array(
						'errors'  => $result['errors'],
						'filters' => $result['filters'],
					)
				);
			}

			$table_html = $this->render_ajax_course_table( $result['filters'] );

			$this->send_ajax_success(
				array(
					'message'      => __( 'Courses loaded.', 'bulk-edit-learnpress-prices' ),
					'items'        => $result['items'],
					'totalItems'   => $result['total_items'],
					'totalPages'   => $result['total_pages'],
					'filters'      => $result['filters'],
					'html'         => $table_html,
					'emptyMessage' => empty( $result['items'] ) ? __( 'No LearnPress courses match the current filters.', 'bulk-edit-learnpress-prices' ) : '',
					'implemented'  => true,
				)
			);
		}

		/**
		 * Handle AJAX requests for previewing price changes.
		 *
		 * @return void
		 */
		public function ajax_preview_changes() {
			$this->verify_ajax_request();

			$payload = $this->sanitize_bulk_ajax_payload();

			if ( ! empty( $payload['errors'] ) ) {
				$this->send_ajax_error(
					__( 'Preview request is invalid.', 'bulk-edit-learnpress-prices' ),
					'invalid_preview_request',
					400,
					array( 'errors' => $payload['errors'] )
				);
			}

			$preview = $this->build_price_preview( $payload['course_ids'], $payload['action'], $payload['value'] );

			$this->send_ajax_success(
				array(
					'message'     => __( 'Preview generated.', 'bulk-edit-learnpress-prices' ),
					'courseIds'   => $payload['course_ids'],
					'action'      => $payload['action'],
					'value'       => $payload['value'],
					'summary'     => $preview['summary'],
					'rows'        => $preview['rows'],
					'implemented' => true,
				)
			);
		}

		/**
		 * Handle AJAX requests for loading a course price history.
		 *
		 * @return void
		 */
		public function ajax_load_price_history() {
			$this->verify_ajax_request();

			$data      = $this->get_ajax_request_data();
			$course_id = isset( $data['course_id'] ) ? absint( $data['course_id'] ) : 0;

			if ( ! $course_id ) {
				$this->send_ajax_error(
					__( 'Choose a valid course before viewing price history.', 'bulk-edit-learnpress-prices' ),
					'invalid_course',
					400
				);
			}

			$post = get_post( $course_id );

			if ( ! $post || BELPCP_COURSE_POST_TYPE !== $post->post_type ) {
				$this->send_ajax_error(
					__( 'Price history is available only for LearnPress courses.', 'bulk-edit-learnpress-prices' ),
					'invalid_course',
					404
				);
			}

			if ( ! $this->current_user_can_edit_course_price( $course_id ) ) {
				$this->send_ajax_error(
					__( 'You do not have permission to view this course price history.', 'bulk-edit-learnpress-prices' ),
					'forbidden',
					403
				);
			}

			$history = $this->get_course_price_history( $course_id );

			$this->send_ajax_success(
				array(
					'message'   => __( 'Price history loaded.', 'bulk-edit-learnpress-prices' ),
					'courseId'  => $course_id,
					'title'     => get_the_title( $post ),
					'count'     => count( $history ),
					'rows'      => $this->prepare_price_history_rows_for_response( $history ),
				)
			);
		}

		/**
		 * Handle AJAX requests for updating course prices.
		 *
		 * @return void
		 */
		public function ajax_update_prices() {
			$this->verify_ajax_request();

			$payload = $this->sanitize_bulk_ajax_payload();

			if ( ! empty( $payload['errors'] ) ) {
				$this->send_ajax_error(
					__( 'Update request is invalid.', 'bulk-edit-learnpress-prices' ),
					'invalid_update_request',
					400,
					array( 'errors' => $payload['errors'] )
				);
			}

			$result = $this->apply_price_updates( $payload['course_ids'], $payload['action'], $payload['value'] );
			$message = __( 'Course prices were updated.', 'bulk-edit-learnpress-prices' );

			if ( ! empty( $result['summary']['failed'] ) || ( empty( $result['summary']['updated'] ) && ! empty( $result['summary']['skipped'] ) ) ) {
				$message = __( 'Course price update finished with items that need review.', 'bulk-edit-learnpress-prices' );
			}

			$this->send_ajax_success(
				array(
					'message'     => $message,
					'courseIds'   => $payload['course_ids'],
					'action'      => $payload['action'],
					'value'       => $payload['value'],
					'summary'     => $result['summary'],
					'rows'        => $result['rows'],
					'implemented' => true,
				)
			);
		}

		/**
		 * Sanitize a price filter value.
		 *
		 * @param mixed $value Raw filter value.
		 * @return string
		 */
		private function sanitize_filter_price( $value ) {
			$value = is_scalar( $value ) ? trim( (string) $value ) : '';

			if ( '' === $value ) {
				return '';
			}

			$value = preg_replace( '/[^0-9.]/', '', $value );

			if ( '' === $value || ! is_numeric( $value ) ) {
				return '';
			}

			return number_format( max( 0, (float) $value ), 2, '.', '' );
		}

		/**
		 * Build preview rows for a bulk price action without saving anything.
		 *
		 * Percentage actions intentionally update regular prices only. Existing sale prices
		 * are preserved and warned about if the new regular price would make them invalid.
		 *
		 * @param array  $course_ids Course IDs.
		 * @param string $action     Bulk action key.
		 * @param string $value      Sanitized bulk action value.
		 * @return array
		 */
		private function build_price_preview( $course_ids, $action, $value ) {
			$rows    = array();
			$summary = array(
				'selected' => count( $course_ids ),
				'valid'    => 0,
				'skipped'  => 0,
				'warnings' => 0,
			);

			foreach ( $course_ids as $course_id ) {
				$row = $this->build_price_preview_row( $course_id, $action, $value );

				if ( 'valid' === $row['status'] ) {
					$summary['valid']++;
				} else {
					$summary['skipped']++;
				}

				if ( ! empty( $row['warnings'] ) ) {
					$summary['warnings'] += count( $row['warnings'] );
				}

				$rows[] = $row;
			}

			$context = array(
				'action' => $action,
				'value'  => $value,
			);

			return array(
				'summary' => $this->filter_update_summary( $summary, $context ),
				'rows'    => $this->filter_preview_rows( $rows, $context ),
			);
		}

		/**
		 * Apply bulk price updates using the same calculations as preview.
		 *
		 * @param array  $course_ids Course IDs.
		 * @param string $action     Bulk action key.
		 * @param string $value      Sanitized bulk action value.
		 * @return array
		 */
		private function apply_price_updates( $course_ids, $action, $value ) {
			$rows    = array();
			$summary = array(
				'selected' => count( $course_ids ),
				'updated'  => 0,
				'skipped'  => 0,
				'failed'   => 0,
				'warnings' => 0,
			);
			$context = array(
				'action' => $action,
				'value'  => $value,
			);

			foreach ( $course_ids as $course_id ) {
				$preview_row = $this->build_price_preview_row( $course_id, $action, $value );
				$row         = $this->apply_price_update_row( $preview_row, $action, $context );

				if ( 'updated' === $row['status'] ) {
					$summary['updated']++;
				} elseif ( 'failed' === $row['status'] ) {
					$summary['failed']++;
				} else {
					$summary['skipped']++;
				}

				if ( ! empty( $row['warnings'] ) ) {
					$summary['warnings'] += count( $row['warnings'] );
				}

				$rows[] = $row;
			}

			/**
			 * Fires after a bulk LearnPress price update operation finishes.
			 *
			 * @param array $summary Update summary.
			 * @param array $rows    Per-course update rows.
			 * @param array $context Update context.
			 */
			do_action( 'bulk_edit_lp_price_after_bulk_operation', $summary, $rows, $context );

			return array(
				'summary' => $this->filter_update_summary( $summary, $context ),
				'rows'    => $this->filter_preview_rows( $rows, $context ),
			);
		}

		/**
		 * Apply one course update from a validated preview row.
		 *
		 * @param array  $preview_row Preview row.
		 * @param string $action      Bulk action key.
		 * @param array  $context     Update context.
		 * @return array
		 */
		private function apply_price_update_row( $preview_row, $action, $context ) {
			$row       = $preview_row;
			$course_id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;

			if ( 'valid' !== $row['status'] || ! $course_id ) {
				$row['status']      = 'skipped';
				$row['statusLabel'] = __( 'Skipped', 'bulk-edit-learnpress-prices' );
				return $row;
			}

			$changes = array(
				'regular_price' => array(
					'before' => $row['beforeRegular'],
					'after'  => $row['afterRegular'],
				),
				'sale_price'    => array(
					'before' => $row['beforeSale'],
					'after'  => $row['afterSale'],
				),
				'sale_start'    => array(
					'before' => isset( $row['beforeSaleStart'] ) ? $row['beforeSaleStart'] : '',
					'after'  => isset( $row['afterSaleStart'] ) ? $row['afterSaleStart'] : '',
				),
				'sale_end'      => array(
					'before' => isset( $row['beforeSaleEnd'] ) ? $row['beforeSaleEnd'] : '',
					'after'  => isset( $row['afterSaleEnd'] ) ? $row['afterSaleEnd'] : '',
				),
			);

			$this->do_before_course_update( $course_id, $changes, $context );

			$failed = false;

			if ( in_array( $action, array( 'set_regular_price', 'increase_percentage', 'decrease_percentage' ), true ) ) {
				if ( ! $this->save_course_price_meta( $course_id, BELPCP_REGULAR_PRICE_META_KEY, $row['afterRegular'], $row['beforeRegular'] ) ) {
					$failed = true;
				}
			}

			if ( in_array( $action, array( 'set_sale_price', 'schedule_sale_price' ), true ) ) {
				if ( ! $this->save_course_price_meta( $course_id, BELPCP_SALE_PRICE_META_KEY, $row['afterSale'], $row['beforeSale'] ) ) {
					$failed = true;
				}
			}

			if ( 'schedule_sale_price' === $action ) {
				if ( ! $this->save_course_text_meta( $course_id, BELPCP_SALE_START_META_KEY, $row['afterSaleStart'], $row['beforeSaleStart'] ) ) {
					$failed = true;
				}

				if ( ! $this->save_course_text_meta( $course_id, BELPCP_SALE_END_META_KEY, $row['afterSaleEnd'], $row['beforeSaleEnd'] ) ) {
					$failed = true;
				}
			}

			if ( 'remove_sale_price' === $action ) {
				if ( ! $this->delete_course_sale_price_meta( $course_id, $row['beforeSale'] ) ) {
					$failed = true;
				}
			}

			if ( ! $failed && ! $this->sync_course_active_price_meta( $course_id, $row['afterRegular'], $row['afterSale'], $row['afterSaleStart'], $row['afterSaleEnd'] ) ) {
				$failed = true;
			}

			$this->clear_course_price_cache( $course_id );

			if ( ! $failed ) {
				$this->maybe_record_course_price_history( $course_id, $changes, $action, $context );

				/**
				 * Fires after course price metadata is written and cache cleanup is requested.
				 *
				 * @param int    $course_id Course post ID.
				 * @param array  $changes   Applied price changes.
				 * @param string $action    Bulk action key.
				 */
				do_action( 'bulk_edit_lp_price_course_price_meta_updated', $course_id, $changes, $action );
			}

			if ( $failed ) {
				$row['status']        = 'failed';
				$row['statusLabel']   = __( 'Failed', 'bulk-edit-learnpress-prices' );
				$row['errors'][]      = __( 'The price metadata could not be saved.', 'bulk-edit-learnpress-prices' );
				$row['currentRegular'] = $this->normalize_price( $this->get_course_regular_price_raw( $course_id ) );
				$row['currentSale']    = $this->normalize_price( $this->get_course_sale_price_raw( $course_id ) );
			} else {
				$row['status']                = 'updated';
				$row['statusLabel']           = __( 'Updated', 'bulk-edit-learnpress-prices' );
				$row['currentRegular']        = $this->normalize_price( $this->get_course_regular_price_raw( $course_id ) );
				$row['currentSale']           = $this->normalize_price( $this->get_course_sale_price_raw( $course_id ) );
				$row['currentRegularDisplay'] = $this->format_price_text( $row['currentRegular'] );
				$row['currentSaleDisplay']    = '' === $row['currentSale'] ? __( 'None', 'bulk-edit-learnpress-prices' ) : $this->format_price_text( $row['currentSale'] );
			}

			$this->do_after_course_update( $course_id, $changes, $row, $context );

			return $row;
		}

		/**
		 * Save a course price meta value, treating unchanged values as successful.
		 *
		 * @param int    $course_id Course post ID.
		 * @param string $meta_key  Meta key.
		 * @param string $new_value New normalized value.
		 * @param string $old_value Old normalized value from preview.
		 * @return bool
		 */
		private function save_course_price_meta( $course_id, $meta_key, $new_value, $old_value ) {
			if ( (string) $new_value === (string) $old_value ) {
				return true;
			}

			$result = update_post_meta( $course_id, $meta_key, $new_value );

			if ( false !== $result ) {
				return true;
			}

			return (string) $this->normalize_price( get_post_meta( $course_id, $meta_key, true ) ) === (string) $new_value;
		}

		/**
		 * Save a course text meta value, treating unchanged values as successful.
		 *
		 * @param int    $course_id Course post ID.
		 * @param string $meta_key  Meta key.
		 * @param string $new_value New value.
		 * @param string $old_value Old value.
		 * @return bool
		 */
		private function save_course_text_meta( $course_id, $meta_key, $new_value, $old_value ) {
			if ( (string) $new_value === (string) $old_value ) {
				return true;
			}

			$result = update_post_meta( $course_id, $meta_key, sanitize_text_field( $new_value ) );

			if ( false !== $result ) {
				return true;
			}

			return (string) get_post_meta( $course_id, $meta_key, true ) === (string) $new_value;
		}

		/**
		 * Delete the sale price meta value and verify the final empty state.
		 *
		 * @param int    $course_id Course post ID.
		 * @param string $old_value Old normalized sale price from preview.
		 * @return bool
		 */
		private function delete_course_sale_price_meta( $course_id, $old_value ) {
			if ( '' === (string) $old_value ) {
				return true;
			}

			delete_post_meta( $course_id, BELPCP_SALE_PRICE_META_KEY );

			return '' === (string) $this->normalize_price( get_post_meta( $course_id, BELPCP_SALE_PRICE_META_KEY, true ) );
		}

		/**
		 * Record one course price history entry when prices actually changed.
		 *
		 * @param int    $course_id Course post ID.
		 * @param array  $changes   Applied changes.
		 * @param string $action    Bulk action key.
		 * @param array  $context   Update context.
		 * @return void
		 */
		private function maybe_record_course_price_history( $course_id, $changes, $action, $context = array() ) {
			$regular_before = isset( $changes['regular_price']['before'] ) ? $this->normalize_price( $changes['regular_price']['before'] ) : '';
			$regular_after  = isset( $changes['regular_price']['after'] ) ? $this->normalize_price( $changes['regular_price']['after'] ) : '';
			$sale_before    = isset( $changes['sale_price']['before'] ) ? $this->normalize_price( $changes['sale_price']['before'] ) : '';
			$sale_after     = isset( $changes['sale_price']['after'] ) ? $this->normalize_price( $changes['sale_price']['after'] ) : '';
			$sale_start_before = isset( $changes['sale_start']['before'] ) ? sanitize_text_field( $changes['sale_start']['before'] ) : '';
			$sale_start_after  = isset( $changes['sale_start']['after'] ) ? sanitize_text_field( $changes['sale_start']['after'] ) : '';
			$sale_end_before   = isset( $changes['sale_end']['before'] ) ? sanitize_text_field( $changes['sale_end']['before'] ) : '';
			$sale_end_after    = isset( $changes['sale_end']['after'] ) ? sanitize_text_field( $changes['sale_end']['after'] ) : '';

			if ( $regular_before === $regular_after && $sale_before === $sale_after && $sale_start_before === $sale_start_after && $sale_end_before === $sale_end_after ) {
				return;
			}

			$user       = wp_get_current_user();
			$user_id    = $user instanceof WP_User ? absint( $user->ID ) : 0;
			$user_label = $user_id ? $user->display_name : __( 'Unknown user', 'bulk-edit-learnpress-prices' );
			$actions    = $this->get_supported_bulk_actions();
			$history    = $this->get_course_price_history( $course_id );

			array_unshift(
				$history,
				array(
					'time'          => current_time( 'mysql' ),
					'time_gmt'      => current_time( 'mysql', true ),
					'user_id'       => $user_id,
					'user_label'    => $user_label,
					'action'        => sanitize_key( $action ),
					'action_label'  => isset( $actions[ $action ] ) ? $actions[ $action ] : $action,
					'bulk_value'    => isset( $context['value'] ) ? $this->format_bulk_value_for_history( $context['value'] ) : '',
					'regular_before' => $regular_before,
					'regular_after' => $regular_after,
					'sale_before'   => $sale_before,
					'sale_after'    => $sale_after,
					'sale_start_before' => $sale_start_before,
					'sale_start_after'  => $sale_start_after,
					'sale_end_before'   => $sale_end_before,
					'sale_end_after'    => $sale_end_after,
				)
			);

			$history = array_slice( $history, 0, BELPCP_MAX_PRICE_HISTORY_ITEMS );

			update_post_meta( $course_id, BELPCP_PRICE_HISTORY_META_KEY, $history );
		}

		/**
		 * Format an action value for the history table.
		 *
		 * @param mixed $value Bulk action value.
		 * @return string
		 */
		private function format_bulk_value_for_history( $value ) {
			if ( is_array( $value ) ) {
				$parts = array();

				if ( isset( $value['sale_price'] ) ) {
					$parts[] = sprintf(
						/* translators: %s: sale price. */
						__( 'Sale %s', 'bulk-edit-learnpress-prices' ),
						$this->format_price_text( $value['sale_price'] )
					);
				}

				if ( isset( $value['sale_start'], $value['sale_end'] ) ) {
					$parts[] = $this->format_sale_schedule_text( $value['sale_start'], $value['sale_end'] );
				}

				return implode( '; ', array_filter( $parts ) );
			}

			return sanitize_text_field( (string) $value );
		}

		/**
		 * Get sanitized price history rows for a course.
		 *
		 * @param int $course_id Course post ID.
		 * @return array
		 */
		private function get_course_price_history( $course_id ) {
			$history = get_post_meta( $course_id, BELPCP_PRICE_HISTORY_META_KEY, true );

			if ( ! is_array( $history ) ) {
				return array();
			}

			$rows = array();

			foreach ( $history as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				$rows[] = array(
					'time'           => isset( $row['time'] ) ? sanitize_text_field( $row['time'] ) : '',
					'time_gmt'       => isset( $row['time_gmt'] ) ? sanitize_text_field( $row['time_gmt'] ) : '',
					'user_id'        => isset( $row['user_id'] ) ? absint( $row['user_id'] ) : 0,
					'user_label'     => isset( $row['user_label'] ) ? sanitize_text_field( $row['user_label'] ) : '',
					'action'         => isset( $row['action'] ) ? sanitize_key( $row['action'] ) : '',
					'action_label'   => isset( $row['action_label'] ) ? sanitize_text_field( $row['action_label'] ) : '',
					'bulk_value'     => isset( $row['bulk_value'] ) ? sanitize_text_field( $row['bulk_value'] ) : '',
					'regular_before' => isset( $row['regular_before'] ) ? $this->normalize_price( $row['regular_before'] ) : '',
					'regular_after'  => isset( $row['regular_after'] ) ? $this->normalize_price( $row['regular_after'] ) : '',
					'sale_before'    => isset( $row['sale_before'] ) ? $this->normalize_price( $row['sale_before'] ) : '',
					'sale_after'     => isset( $row['sale_after'] ) ? $this->normalize_price( $row['sale_after'] ) : '',
					'sale_start_before' => isset( $row['sale_start_before'] ) ? sanitize_text_field( $row['sale_start_before'] ) : '',
					'sale_start_after'  => isset( $row['sale_start_after'] ) ? sanitize_text_field( $row['sale_start_after'] ) : '',
					'sale_end_before'   => isset( $row['sale_end_before'] ) ? sanitize_text_field( $row['sale_end_before'] ) : '',
					'sale_end_after'    => isset( $row['sale_end_after'] ) ? sanitize_text_field( $row['sale_end_after'] ) : '',
				);
			}

			return $rows;
		}

		/**
		 * Get the number of recorded price history entries for a course.
		 *
		 * @param int $course_id Course post ID.
		 * @return int
		 */
		private function get_course_price_history_count( $course_id ) {
			return count( $this->get_course_price_history( $course_id ) );
		}

		/**
		 * Prepare price history rows for JSON responses.
		 *
		 * @param array $history Price history rows.
		 * @return array
		 */
		private function prepare_price_history_rows_for_response( $history ) {
			$rows = array();

			foreach ( $history as $row ) {
				$rows[] = array(
					'time'                 => $this->format_history_time( isset( $row['time'] ) ? $row['time'] : '' ),
					'user'                 => isset( $row['user_label'] ) ? $row['user_label'] : '',
					'action'               => isset( $row['action_label'] ) ? $row['action_label'] : '',
					'bulkValue'            => isset( $row['bulk_value'] ) ? $row['bulk_value'] : '',
					'regularBefore'        => isset( $row['regular_before'] ) ? $row['regular_before'] : '',
					'regularBeforeDisplay' => $this->format_history_price_text( isset( $row['regular_before'] ) ? $row['regular_before'] : '' ),
					'regularAfter'         => isset( $row['regular_after'] ) ? $row['regular_after'] : '',
					'regularAfterDisplay'  => $this->format_history_price_text( isset( $row['regular_after'] ) ? $row['regular_after'] : '' ),
					'saleBefore'           => isset( $row['sale_before'] ) ? $row['sale_before'] : '',
					'saleBeforeDisplay'    => $this->format_history_price_text( isset( $row['sale_before'] ) ? $row['sale_before'] : '' ),
					'saleAfter'            => isset( $row['sale_after'] ) ? $row['sale_after'] : '',
					'saleAfterDisplay'     => $this->format_history_price_text( isset( $row['sale_after'] ) ? $row['sale_after'] : '' ),
					'saleScheduleBefore'   => $this->format_sale_schedule_text(
						isset( $row['sale_start_before'] ) ? $row['sale_start_before'] : '',
						isset( $row['sale_end_before'] ) ? $row['sale_end_before'] : ''
					),
					'saleScheduleAfter'    => $this->format_sale_schedule_text(
						isset( $row['sale_start_after'] ) ? $row['sale_start_after'] : '',
						isset( $row['sale_end_after'] ) ? $row['sale_end_after'] : ''
					),
				);
			}

			return $rows;
		}

		/**
		 * Format a history timestamp for admin display.
		 *
		 * @param string $time Stored local timestamp.
		 * @return string
		 */
		private function format_history_time( $time ) {
			$timestamp = $time ? strtotime( $time ) : false;

			if ( ! $timestamp ) {
				return '';
			}

			return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
		}

		/**
		 * Format a history price value.
		 *
		 * @param string $price Normalized price.
		 * @return string
		 */
		private function format_history_price_text( $price ) {
			return '' === (string) $price ? __( 'None', 'bulk-edit-learnpress-prices' ) : $this->format_price_text( $price );
		}

		/**
		 * Format a sale schedule range for admin display.
		 *
		 * @param string $sale_start Sale start datetime.
		 * @param string $sale_end   Sale end datetime.
		 * @return string
		 */
		private function format_sale_schedule_text( $sale_start, $sale_end ) {
			if ( '' === (string) $sale_start || '' === (string) $sale_end ) {
				return '';
			}

			$start = $this->format_history_time( $sale_start );
			$end   = $this->format_history_time( $sale_end );

			if ( '' === $start || '' === $end ) {
				return '';
			}

			return sprintf(
				/* translators: 1: sale start datetime, 2: sale end datetime. */
				__( '%1$s to %2$s', 'bulk-edit-learnpress-prices' ),
				$start,
				$end
			);
		}

		/**
		 * Clear price-related caches after a course update.
		 *
		 * @param int $course_id Course post ID.
		 * @return void
		 */
		private function clear_course_price_cache( $course_id ) {
			clean_post_cache( $course_id );
			wp_cache_delete( $course_id, 'post_meta' );

			if ( function_exists( 'learn_press_delete_course_cache' ) ) {
				learn_press_delete_course_cache( $course_id );
			}

			if ( function_exists( 'learn_press_clean_course_cache' ) ) {
				learn_press_clean_course_cache( $course_id );
			}
		}

		/**
		 * Build one preview row for a course.
		 *
		 * @param int    $course_id Course ID.
		 * @param string $action    Bulk action key.
		 * @param string $value     Sanitized bulk action value.
		 * @return array
		 */
		private function build_price_preview_row( $course_id, $action, $value ) {
			$post = get_post( $course_id );

			if ( ! $post || BELPCP_COURSE_POST_TYPE !== $post->post_type ) {
				return $this->get_skipped_preview_row(
					$course_id,
					__( 'Skipped: not a LearnPress course.', 'bulk-edit-learnpress-prices' )
				);
			}

			if ( ! $this->current_user_can_edit_course_price( $course_id ) ) {
				return $this->get_skipped_preview_row(
					$course_id,
					__( 'Skipped: you do not have permission to edit this course.', 'bulk-edit-learnpress-prices' )
				);
			}

			$regular_raw       = $this->get_course_regular_price_raw( $course_id );
			$sale_raw          = $this->get_course_sale_price_raw( $course_id );
			$sale_start_raw    = $this->get_course_sale_start_raw( $course_id );
			$sale_end_raw      = $this->get_course_sale_end_raw( $course_id );
			$regular           = $this->normalize_price( $regular_raw );
			$sale              = $this->normalize_price( $sale_raw );
			$regular_for_math  = '' === $regular ? '0.00' : $regular;
			$sale_for_response = '' === $sale ? '' : $sale;
			$after_regular     = $regular_for_math;
			$after_sale        = $sale_for_response;
			$after_sale_start  = (string) $sale_start_raw;
			$after_sale_end    = (string) $sale_end_raw;
			$warnings          = array();
			$errors            = array();

			switch ( $action ) {
				case 'set_regular_price':
					$after_regular = $this->round_price( $value );
					break;
				case 'set_sale_price':
					$after_sale = $this->round_price( $value );
					break;
				case 'schedule_sale_price':
					$after_sale       = $this->round_price( isset( $value['sale_price'] ) ? $value['sale_price'] : '' );
					$after_sale_start = isset( $value['sale_start'] ) ? (string) $value['sale_start'] : '';
					$after_sale_end   = isset( $value['sale_end'] ) ? (string) $value['sale_end'] : '';
					break;
				case 'remove_sale_price':
					$after_sale = '';
					break;
				case 'increase_percentage':
					$after_regular = $this->calculate_percentage_price( $regular_for_math, $value, 'increase' );
					break;
				case 'decrease_percentage':
					$after_regular = $this->calculate_percentage_price( $regular_for_math, $value, 'decrease' );
					break;
				default:
					$errors[] = __( 'Skipped: unsupported action.', 'bulk-edit-learnpress-prices' );
					break;
			}

			if ( '' !== $after_sale && (float) $after_sale > (float) $after_regular && ! in_array( $action, array( 'increase_percentage', 'decrease_percentage' ), true ) ) {
				$errors[] = __( 'Skipped: sale price cannot be greater than regular price.', 'bulk-edit-learnpress-prices' );
			}

			if ( 'schedule_sale_price' === $action && ( '' === $after_sale_start || '' === $after_sale_end || strtotime( $after_sale_start ) >= strtotime( $after_sale_end ) ) ) {
				$errors[] = __( 'Skipped: sale schedule dates are invalid.', 'bulk-edit-learnpress-prices' );
			}

			if ( in_array( $action, array( 'increase_percentage', 'decrease_percentage' ), true ) && '' !== $sale_for_response && (float) $sale_for_response > (float) $after_regular ) {
				$warnings[] = __( 'Existing sale price is greater than the previewed regular price.', 'bulk-edit-learnpress-prices' );
			}

			$status = empty( $errors ) ? 'valid' : 'skipped';

			return array(
				'id'                    => absint( $course_id ),
				'title'                 => get_the_title( $post ),
				'editLink'              => get_edit_post_link( $course_id, 'raw' ),
				'status'                => $status,
				'statusLabel'           => 'valid' === $status ? __( 'Ready', 'bulk-edit-learnpress-prices' ) : __( 'Skipped', 'bulk-edit-learnpress-prices' ),
				'beforeRegular'         => $regular_for_math,
				'beforeRegularDisplay'  => $this->format_price_text( $regular_for_math ),
				'beforeSale'            => $sale_for_response,
				'beforeSaleDisplay'     => '' === $sale_for_response ? __( 'None', 'bulk-edit-learnpress-prices' ) : $this->format_price_text( $sale_for_response ),
				'beforeSaleStart'       => (string) $sale_start_raw,
				'beforeSaleEnd'         => (string) $sale_end_raw,
				'afterRegular'          => $after_regular,
				'afterRegularDisplay'   => $this->format_price_text( $after_regular ),
				'afterSale'             => $after_sale,
				'afterSaleDisplay'      => '' === $after_sale ? __( 'None', 'bulk-edit-learnpress-prices' ) : $this->format_price_text( $after_sale ),
				'afterSaleStart'        => $after_sale_start,
				'afterSaleEnd'          => $after_sale_end,
				'saleScheduleDisplay'   => $this->format_sale_schedule_text( $after_sale_start, $after_sale_end ),
				'warnings'              => $warnings,
				'errors'                => $errors,
			);
		}

		/**
		 * Build a skipped preview row when a course cannot be loaded.
		 *
		 * @param int    $course_id Course ID.
		 * @param string $message   Skip message.
		 * @return array
		 */
		private function get_skipped_preview_row( $course_id, $message ) {
			return array(
				'id'                   => absint( $course_id ),
				'title'                => sprintf(
					/* translators: %d: course ID. */
					__( 'Course #%d', 'bulk-edit-learnpress-prices' ),
					absint( $course_id )
				),
				'editLink'             => '',
				'status'               => 'skipped',
				'statusLabel'          => __( 'Skipped', 'bulk-edit-learnpress-prices' ),
				'beforeRegular'        => '',
				'beforeRegularDisplay' => __( 'None', 'bulk-edit-learnpress-prices' ),
				'beforeSale'           => '',
				'beforeSaleDisplay'    => __( 'None', 'bulk-edit-learnpress-prices' ),
				'afterRegular'         => '',
				'afterRegularDisplay'  => __( 'None', 'bulk-edit-learnpress-prices' ),
				'afterSale'            => '',
				'afterSaleDisplay'     => __( 'None', 'bulk-edit-learnpress-prices' ),
				'warnings'             => array(),
				'errors'               => array( $message ),
			);
		}

		/**
		 * Check whether the current user can update price metadata for a course.
		 *
		 * @param int $course_id Course post ID.
		 * @return bool
		 */
		private function current_user_can_edit_course_price( $course_id ) {
			if ( ! $this->current_user_can_manage() ) {
				return false;
			}

			return current_user_can( 'edit_post', $course_id );
		}

		/**
		 * Round a price value to the plugin precision.
		 *
		 * @param mixed $value Price value.
		 * @return string
		 */
		private function round_price( $value ) {
			return number_format( max( 0, (float) $value ), 2, '.', '' );
		}

		/**
		 * Calculate a percentage price adjustment.
		 *
		 * @param string $current_price Current regular price.
		 * @param string $percentage    Percentage value.
		 * @param string $direction     increase or decrease.
		 * @return string
		 */
		private function calculate_percentage_price( $current_price, $percentage, $direction ) {
			$current_price = max( 0, (float) $current_price );
			$percentage    = max( 0, (float) $percentage );
			$delta         = $current_price * ( $percentage / 100 );

			if ( 'decrease' === $direction ) {
				return $this->round_price( $current_price - $delta );
			}

			return $this->round_price( $current_price + $delta );
		}

		/**
		 * Render the course list table for an AJAX response.
		 *
		 * @param array $filters Sanitized filters.
		 * @return string
		 */
		private function render_ajax_course_table( $filters ) {
			$this->include_admin_screen_dependencies();
			$this->include_list_table();

			if ( ! class_exists( 'LP_Price_List_Table' ) ) {
				return '';
			}

			$previous_get         = $_GET;
			$previous_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
			$request_args         = array( 'page' => $this->admin_page_slug );

			foreach ( $filters as $key => $value ) {
				if ( 'errors' === $key || '' === $value || null === $value ) {
					continue;
				}

				$request_args[ $key ] = $value;
			}

			$_GET = $request_args;

			$request_url = add_query_arg( $request_args, admin_url( 'admin.php' ) );
			$request_path = wp_parse_url( $request_url, PHP_URL_PATH );
			$request_query = wp_parse_url( $request_url, PHP_URL_QUERY );
			$_SERVER['REQUEST_URI'] = $request_path . ( $request_query ? '?' . $request_query : '' );

			if ( function_exists( 'set_current_screen' ) && ! get_current_screen() ) {
				set_current_screen( $this->admin_page_hook ? $this->admin_page_hook : 'tools_page_' . $this->admin_page_slug );
			}

			$list_table = new LP_Price_List_Table( $this, $filters, $this->current_user_can_manage() );
			$list_table->prepare_items();

			ob_start();
			$list_table->display();

			$table_html = (string) ob_get_clean();

			$_GET = $previous_get;
			if ( '' !== $previous_request_uri ) {
				$_SERVER['REQUEST_URI'] = $previous_request_uri;
			}

			return $table_html;
		}

		/**
		 * Load admin screen dependencies needed by WP_List_Table rendering.
		 *
		 * @return void
		 */
		private function include_admin_screen_dependencies() {
			if ( ! function_exists( 'get_column_headers' ) ) {
				require_once ABSPATH . 'wp-admin/includes/template.php';
			}

			if ( ! function_exists( 'get_current_screen' ) ) {
				require_once ABSPATH . 'wp-admin/includes/screen.php';
			}

			if ( ! class_exists( 'WP_List_Table' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			}
		}

		/**
		 * Verify nonce and capability for an AJAX request.
		 *
		 * @return void
		 */
		private function verify_ajax_request() {
			if ( ! check_ajax_referer( 'bulk_edit_lp_prices_ajax', 'nonce', false ) ) {
				$this->send_ajax_error(
					__( 'Security check failed. Refresh the page and try again.', 'bulk-edit-learnpress-prices' ),
					'invalid_nonce',
					403
				);
			}

			if ( ! $this->current_user_can_manage() ) {
				$this->send_ajax_error(
					__( 'You do not have permission to manage LearnPress course prices.', 'bulk-edit-learnpress-prices' ),
					'forbidden',
					403
				);
			}
		}

		/**
		 * Get unslashed AJAX request data.
		 *
		 * @return array
		 */
		private function get_ajax_request_data() {
			return isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : array();
		}

		/**
		 * Sanitize and validate shared bulk AJAX payload fields.
		 *
		 * @return array
		 */
		private function sanitize_bulk_ajax_payload() {
			$data       = $this->get_ajax_request_data();
			$course_ids = $this->sanitize_selected_course_ids( isset( $data['course_ids'] ) ? $data['course_ids'] : array() );
			$action     = $this->sanitize_bulk_action( isset( $data['bulk_action'] ) ? $data['bulk_action'] : '' );
			$value      = $this->sanitize_bulk_value( $action, isset( $data['bulk_value'] ) ? $data['bulk_value'] : '', $data );
			$errors     = array();

			if ( empty( $course_ids ) ) {
				$errors[] = __( 'Select at least one course.', 'bulk-edit-learnpress-prices' );
			}

			if ( count( $course_ids ) > BELPCP_MAX_SELECTED_COURSES ) {
				$errors[] = sprintf(
					/* translators: %d: maximum selected course count. */
					__( 'Select %d courses or fewer per update request.', 'bulk-edit-learnpress-prices' ),
					BELPCP_MAX_SELECTED_COURSES
				);
			}

			if ( '' === $action ) {
				$errors[] = __( 'Choose a valid bulk action.', 'bulk-edit-learnpress-prices' );
			}

			if ( is_wp_error( $value ) ) {
				$errors[] = $value->get_error_message();
				$value    = '';
			}

			return array(
				'course_ids' => $course_ids,
				'action'     => $action,
				'value'      => $value,
				'errors'     => $errors,
			);
		}

		/**
		 * Sanitize selected course IDs.
		 *
		 * @param mixed $raw_ids Raw course ID value or values.
		 * @return array
		 */
		private function sanitize_selected_course_ids( $raw_ids ) {
			if ( ! is_array( $raw_ids ) ) {
				$raw_ids = array( $raw_ids );
			}

			$course_ids = array();

			foreach ( $raw_ids as $course_id ) {
				$course_id = absint( $course_id );

				if ( $course_id > 0 ) {
					$course_ids[] = $course_id;
				}
			}

			return array_values( array_unique( $course_ids ) );
		}

		/**
		 * Sanitize and validate a bulk action key.
		 *
		 * @param mixed $raw_action Raw action key.
		 * @return string
		 */
		private function sanitize_bulk_action( $raw_action ) {
			$action  = sanitize_key( is_scalar( $raw_action ) ? (string) $raw_action : '' );
			$actions = $this->get_supported_bulk_actions();

			return isset( $actions[ $action ] ) ? $action : '';
		}

		/**
		 * Sanitize and validate a bulk action value.
		 *
		 * @param string $action    Sanitized action key.
		 * @param mixed  $raw_value Raw value.
		 * @param array  $data      Full request data.
		 * @return string|WP_Error
		 */
		private function sanitize_bulk_value( $action, $raw_value, $data = array() ) {
			if ( 'remove_sale_price' === $action ) {
				return '';
			}

			if ( 'schedule_sale_price' === $action ) {
				$sale_price = $this->parse_decimal_price_input( $raw_value );
				if ( is_wp_error( $sale_price ) ) {
					return $sale_price;
				}

				$sale_start = $this->parse_schedule_datetime_input( isset( $data['sale_start'] ) ? $data['sale_start'] : '', 'start' );
				if ( is_wp_error( $sale_start ) ) {
					return $sale_start;
				}

				$sale_end = $this->parse_schedule_datetime_input( isset( $data['sale_end'] ) ? $data['sale_end'] : '', 'end' );
				if ( is_wp_error( $sale_end ) ) {
					return $sale_end;
				}

				if ( strtotime( $sale_start ) >= strtotime( $sale_end ) ) {
					return new WP_Error( 'invalid_schedule_window', __( 'Sale start date must be before sale end date.', 'bulk-edit-learnpress-prices' ) );
				}

				return array(
					'sale_price' => $sale_price,
					'sale_start' => $sale_start,
					'sale_end'   => $sale_end,
				);
			}

			if ( in_array( $action, array( 'set_regular_price', 'set_sale_price' ), true ) ) {
				return $this->parse_decimal_price_input( $raw_value );
			}

			if ( in_array( $action, array( 'increase_percentage', 'decrease_percentage' ), true ) ) {
				return $this->parse_percentage_input( $raw_value, 'decrease_percentage' === $action );
			}

			return new WP_Error( 'invalid_action', __( 'Choose a valid bulk action.', 'bulk-edit-learnpress-prices' ) );
		}

		/**
		 * Parse a sale schedule datetime-local input into a LearnPress-compatible value.
		 *
		 * @param mixed  $raw_value Raw date value.
		 * @param string $position  start or end.
		 * @return string|WP_Error
		 */
		private function parse_schedule_datetime_input( $raw_value, $position ) {
			$value = is_scalar( $raw_value ) ? trim( (string) $raw_value ) : '';

			if ( '' === $value ) {
				return new WP_Error(
					'missing_schedule_' . sanitize_key( $position ),
					'start' === $position ? __( 'Enter a sale start date.', 'bulk-edit-learnpress-prices' ) : __( 'Enter a sale end date.', 'bulk-edit-learnpress-prices' )
				);
			}

			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(?::\d{2})?$/', $value ) ) {
				return new WP_Error( 'invalid_schedule_date', __( 'Enter sale schedule dates using the date and time fields.', 'bulk-edit-learnpress-prices' ) );
			}

			$value    = str_replace( 'T', ' ', $value );
			$timezone = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( wp_timezone_string() );
			$format   = strlen( $value ) > 16 ? 'Y-m-d H:i:s' : 'Y-m-d H:i';
			$date     = DateTimeImmutable::createFromFormat( $format, $value, $timezone );
			$errors   = DateTimeImmutable::getLastErrors();

			if ( ! $date || ( is_array( $errors ) && ( $errors['warning_count'] > 0 || $errors['error_count'] > 0 ) ) ) {
				return new WP_Error( 'invalid_schedule_date', __( 'Enter a valid sale schedule date.', 'bulk-edit-learnpress-prices' ) );
			}

			return $date->format( 'Y-m-d H:i:s' );
		}

		/**
		 * Parse a decimal price input.
		 *
		 * @param mixed $raw_value Raw input.
		 * @return string|WP_Error
		 */
		private function parse_decimal_price_input( $raw_value ) {
			$value = is_scalar( $raw_value ) ? trim( (string) $raw_value ) : '';

			if ( '' === $value ) {
				return new WP_Error( 'missing_value', __( 'Enter a value for the selected action.', 'bulk-edit-learnpress-prices' ) );
			}

			if ( ! preg_match( '/^\d+(?:\.\d+)?$/', $value ) ) {
				return new WP_Error( 'invalid_value', __( 'Enter a numeric value using a period for decimals.', 'bulk-edit-learnpress-prices' ) );
			}

			$value = (float) $value;

			if ( $value < 0 ) {
				return new WP_Error( 'negative_value', __( 'Value cannot be negative.', 'bulk-edit-learnpress-prices' ) );
			}

			return number_format( $value, 2, '.', '' );
		}

		/**
		 * Parse a percentage input.
		 *
		 * Increase percentages above 100 are allowed. Decrease percentages above 100
		 * are rejected so regular prices cannot become negative.
		 *
		 * @param mixed $raw_value      Raw input.
		 * @param bool  $is_decrease    Whether the action decreases prices.
		 * @return string|WP_Error
		 */
		private function parse_percentage_input( $raw_value, $is_decrease = false ) {
			$value = $this->parse_decimal_price_input( $raw_value );

			if ( is_wp_error( $value ) ) {
				return $value;
			}

			if ( $is_decrease && (float) $value > 100 ) {
				return new WP_Error( 'invalid_decrease_percentage', __( 'Decrease percentage cannot be greater than 100.', 'bulk-edit-learnpress-prices' ) );
			}

			return $value;
		}

		/**
		 * Send a standardized AJAX success response.
		 *
		 * @param array $data Response data.
		 * @return void
		 */
		private function send_ajax_success( $data = array() ) {
			wp_send_json_success(
				wp_parse_args(
					$data,
					array(
						'message' => '',
						'code'    => 'success',
					)
				)
			);
		}

		/**
		 * Send a standardized AJAX error response.
		 *
		 * @param string $message     Human-readable error.
		 * @param string $code        Machine-readable error code.
		 * @param int    $status_code HTTP response code.
		 * @param array  $data        Additional response data.
		 * @return void
		 */
		private function send_ajax_error( $message, $code = 'error', $status_code = 400, $data = array() ) {
			wp_send_json_error(
				wp_parse_args(
					$data,
					array(
						'message' => $message,
						'code'    => $code,
					)
				),
				$status_code
			);
		}

		/**
		 * Build taxonomy query clauses.
		 *
		 * @param array $filters Sanitized filters.
		 * @return array
		 */
		private function build_course_tax_query( $filters ) {
			if ( empty( $filters['category_id'] ) ) {
				return array();
			}

			$taxonomy = $this->get_course_category_taxonomy();

			if ( '' === $taxonomy ) {
				return array();
			}

			return array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => array( absint( $filters['category_id'] ) ),
				),
			);
		}

		/**
		 * Build meta query clauses.
		 *
		 * @param array $filters Sanitized filters.
		 * @return array
		 */
		private function build_course_meta_query( $filters ) {
			$meta_query = array();

			if ( in_array( $filters['course_type'], array( 'paid', 'free' ), true ) ) {
				if ( 'paid' === $filters['course_type'] ) {
					$meta_query[] = array(
						'key'     => '_lp_price',
						'value'   => 0,
						'compare' => '>',
						'type'    => 'NUMERIC',
					);
				} else {
					$meta_query[] = array(
						'relation' => 'OR',
						array(
							'key'     => '_lp_price',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_lp_price',
							'value'   => '',
							'compare' => '=',
						),
						array(
							'key'     => '_lp_price',
							'value'   => 0,
							'compare' => '=',
							'type'    => 'NUMERIC',
						),
					);
				}
			}

			if ( '' !== $filters['min_price'] ) {
				$meta_query[] = array(
					'key'     => '_lp_price',
					'value'   => (float) $filters['min_price'],
					'compare' => '>=',
					'type'    => 'NUMERIC',
				);
			}

			if ( '' !== $filters['max_price'] ) {
				$meta_query[] = array(
					'key'     => '_lp_price',
					'value'   => (float) $filters['max_price'],
					'compare' => '<=',
					'type'    => 'NUMERIC',
				);
			}

			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}

			return $meta_query;
		}

		/**
		 * Get a readable post status label.
		 *
		 * @param string $status Post status key.
		 * @return string
		 */
		private function get_post_status_label( $status ) {
			$status_object = get_post_status_object( $status );

			if ( $status_object && ! empty( $status_object->label ) ) {
				return $status_object->label;
			}

			return ucfirst( str_replace( array( '-', '_' ), ' ', (string) $status ) );
		}

		/**
		 * Check whether a course capability is registered.
		 *
		 * @param string $capability Capability name.
		 * @return bool
		 */
		private function course_capability_exists( $capability ) {
			$post_type_object = get_post_type_object( BELPCP_COURSE_POST_TYPE );

			if ( ! $post_type_object || empty( $post_type_object->cap ) ) {
				return false;
			}

			return in_array( $capability, (array) $post_type_object->cap, true );
		}

		/**
		 * Check whether a top-level admin menu exists.
		 *
		 * @param string $slug Parent menu slug.
		 * @return bool
		 */
		private function admin_parent_menu_exists( $slug ) {
			global $menu;

			if ( ! is_array( $menu ) ) {
				return false;
			}

			foreach ( $menu as $menu_item ) {
				if ( isset( $menu_item[2] ) && $slug === $menu_item[2] ) {
					return true;
				}
			}

			return false;
		}
	}
}
