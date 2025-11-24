<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin loader.
 */
class LP_UPPC_Plugin {
	private static $instance = null;

	/**
	 * Singleton instance.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		if ( ! $this->is_dependencies_ready() ) {
			return;
		}

		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	private function is_dependencies_ready(): bool {
		if ( ! class_exists( 'LearnPress' ) || ! defined( 'LEARNPRESS_VERSION' ) ) {
			add_action( 'admin_notices', [ $this, 'missing_learnpress_notice' ] );
			return false;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', [ $this, 'missing_woocommerce_notice' ] );
			return false;
		}

		return true;
	}

	public function missing_learnpress_notice(): void {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'LearnPress Upsell Coupon by Progress requires LearnPress to be active.', 'lp-upsell-progress-coupon' )
		);
	}

	public function missing_woocommerce_notice(): void {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'LearnPress Upsell Coupon by Progress requires WooCommerce to be active.', 'lp-upsell-progress-coupon' )
		);
	}

	private function define_constants(): void {
		if ( ! defined( 'LP_UPPC_INC_PATH' ) ) {
			define( 'LP_UPPC_INC_PATH', LP_UPPC_PATH . 'inc/' );
		}

		if ( ! defined( 'LP_UPPC_TEMPLATE_PATH' ) ) {
			define( 'LP_UPPC_TEMPLATE_PATH', LP_UPPC_PATH . 'templates/' );
		}
	}

	private function includes(): void {
		require_once LP_UPPC_INC_PATH . 'class-lp-uppc-helper.php';
		require_once LP_UPPC_INC_PATH . 'class-lp-uppc-settings.php';
		require_once LP_UPPC_INC_PATH . 'class-lp-uppc-metabox.php';
		require_once LP_UPPC_INC_PATH . 'class-lp-uppc-service.php';
		require_once LP_UPPC_INC_PATH . 'class-lp-uppc-log-list-table.php';
		require_once LP_UPPC_INC_PATH . 'class-lp-uppc-email.php';
	}

	private function init_hooks(): void {
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'learn-press/ready', [ $this, 'boot' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
		add_action( 'admin_post_lp_uppc_preview_email', [ $this, 'handle_preview_email' ] );
		add_action( 'wp_ajax_lp_uppc_preview_email', [ $this, 'ajax_preview_email' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'lp-upsell-progress-coupon', false, dirname( LP_UPPC_BASENAME ) . '/languages' );
	}

	public function boot(): void {
		LP_UPPC_Settings::instance();
		LP_UPPC_Metabox::instance();
		LP_UPPC_Service::instance();
		LP_UPPC_Email::instance();
	}

	public function register_admin_pages(): void {
		$capability = apply_filters( 'lp_uppc_logs_capability', 'manage_options' );
		add_submenu_page(
			'learn-press',
			esc_html__( 'Upsell Coupon Logs', 'lp-upsell-progress-coupon' ),
			esc_html__( 'Coupon Logs', 'lp-upsell-progress-coupon' ),
			$capability,
			'lp-uppc-logs',
			[ $this, 'render_logs_page' ]
		);
	}

	public function render_logs_page(): void {
		if ( ! current_user_can( apply_filters( 'lp_uppc_logs_capability', 'manage_options' ) ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'lp-upsell-progress-coupon' ) );
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen ) {
			$screen->add_help_tab(
				array(
					'id'      => 'lp_uppc_logs_help',
					'title'   => esc_html__( 'About Coupon Logs', 'lp-upsell-progress-coupon' ),
					'content' => '<p>' . esc_html__( 'This table lists each coupon issued when learners hit progress milestones. Use it to audit rewards and troubleshoot delivery.', 'lp-upsell-progress-coupon' ) . '</p>',
				)
			);
			$screen->set_help_sidebar(
				'<p><strong>' . esc_html__( 'Helpful links', 'lp-upsell-progress-coupon' ) . '</strong></p>' .
				'<p><a href="' . esc_url( admin_url( 'admin.php?page=learn-press-settings&section=upsell-progress&subtab=email' ) ) . '">' . esc_html__( 'Email settings', 'lp-upsell-progress-coupon' ) . '</a></p>'
			);
		}

		$list_table = new LP_UPPC_Log_List_Table();
		$list_table->prepare_items();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Upsell Coupon Logs', 'lp-upsell-progress-coupon' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Review issued coupons, linked WooCommerce products, and learner progress milestones.', 'lp-upsell-progress-coupon' ) . '</p>';
		settings_errors();
		echo '<form method="post">';
		$list_table->display();
		echo '</form>';
		echo '</div>';
	}

	public function ajax_preview_email(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to preview emails.', 'lp-upsell-progress-coupon' ) ), 403 );
		}

		check_ajax_referer( 'lp_uppc_preview_email', 'nonce' );

		$course_id = isset( $_POST['course_id'] ) ? absint( wp_unslash( $_POST['course_id'] ) ) : 0;
		if ( ! $course_id ) {
			$course_id = (int) LP_UPPC_Settings::get_setting( 'lp_uppc_email_preview_course', 0 );
		}
		if ( ! $course_id ) {
			$course = get_posts(
				array(
					'post_type'      => LP_COURSE_CPT,
					'posts_per_page' => 1,
					'post_status'    => array( 'publish', 'draft' ),
				)
			);
			$course_id = $course ? (int) $course[0]->ID : 0;
		}

		if ( ! $course_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'No course available for preview. Please set a preview course ID.', 'lp-upsell-progress-coupon' ) ), 400 );
		}

		$email   = LP_UPPC_Email::instance();
		$payload = $email->build_preview_payload( $course_id, get_current_user_id() );
		if ( ! $payload ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unable to build preview payload.', 'lp-upsell-progress-coupon' ) ), 500 );
		}

		$composed = $email->compose_email_from_payload( $payload );
		if ( empty( $composed ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unable to compose preview email.', 'lp-upsell-progress-coupon' ) ), 500 );
		}

		wp_send_json_success(
			array(
				'subject' => $composed['subject'],
				'message' => $composed['message'],
			)
		);
	}

	public function enqueue_admin_assets( $hook_suffix ): void {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : $hook_suffix;
		$allowed  = array( 'learn-press_page_learn-press-settings', 'learn-press_page_lp-uppc-logs' );

		if ( ! in_array( $screen_id, $allowed, true ) ) {
			return;
		}

		wp_enqueue_script(
			'lp-uppc-admin',
			LP_UPPC_URL . 'assets/admin/js/lp-uppc-admin.js',
			array( 'jquery', 'jquery-ui-dialog' ),
			LP_UPPC_VERSION,
			true
		);

		wp_localize_script(
			'lp-uppc-admin',
			'lpUPPCAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'previewTitle'  => esc_html__( 'Upsell coupon email preview', 'lp-upsell-progress-coupon' ),
					'loading'       => esc_html__( 'Loading preview…', 'lp-upsell-progress-coupon' ),
					'subjectLabel'  => esc_html__( 'Subject:', 'lp-upsell-progress-coupon' ),
					'emptyMessage'  => esc_html__( 'No message available.', 'lp-upsell-progress-coupon' ),
					'errorGeneric'  => esc_html__( 'Unable to load preview. Please try again.', 'lp-upsell-progress-coupon' ),
				),
			)
		);

		wp_enqueue_style(
			'lp-uppc-admin',
			LP_UPPC_URL . 'assets/admin/css/lp-uppc-admin.css',
			array( 'wp-jquery-ui-dialog' ),
			LP_UPPC_VERSION
		);
	}
	public function handle_preview_email(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this preview.', 'lp-upsell-progress-coupon' ) );
		}

		check_admin_referer( 'lp_uppc_preview_email' );

		$course_id = (int) LP_UPPC_Settings::get_setting( 'lp_uppc_email_preview_course', 0 );
		if ( ! $course_id ) {
			$course = get_posts(
				array(
					'post_type'      => LP_COURSE_CPT,
					'posts_per_page' => 1,
					'post_status'    => array( 'publish', 'draft' ),
				)
			);
			$course_id = $course ? (int) $course[0]->ID : 0;
		}

		if ( ! $course_id ) {
			wp_die( esc_html__( 'No course available for preview. Please create a course or set a preview course ID.', 'lp-upsell-progress-coupon' ) );
		}

		$email       = LP_UPPC_Email::instance();
		$payload     = $email->build_preview_payload( $course_id, get_current_user_id() );
		$composed    = $email->compose_email_from_payload( $payload );
		$subject_txt = $composed ? $composed['subject'] : esc_html__( 'Preview unavailable', 'lp-upsell-progress-coupon' );
		$message     = $composed ? $composed['message'] : '<p>' . esc_html__( 'Unable to build preview email.', 'lp-upsell-progress-coupon' ) . '</p>';

		echo '<div class="wrap lp-uppc-email-preview">';
		echo '<h1>' . esc_html__( 'Upsell Coupon Email Preview', 'lp-upsell-progress-coupon' ) . '</h1>';
		echo '<p><strong>' . esc_html__( 'Subject:', 'lp-upsell-progress-coupon' ) . '</strong> ' . esc_html( $subject_txt ) . '</p>';
		echo '<div class="lp-uppc-email-preview__message" style="border:1px solid #ccd0d4;background:#fff;padding:20px;max-width:800px;">' . wp_kses_post( $message ) . '</div>';
		echo '</div>';

		exit;
	}
}
