<?php
/**
 * Main plugin class.
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class LP_Advanced_Course_Filter
 */
class LP_Advanced_Course_Filter {
	/**
	 * Plugin instance.
	 *
	 * @var LP_Advanced_Course_Filter|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return LP_Advanced_Course_Filter
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Load class files.
	 *
	 * @return void
	 */
	private function includes() {
		require_once LP_ACF_PATH . 'includes/class-lp-acf-query.php';
		require_once LP_ACF_PATH . 'includes/class-lp-acf-shortcode.php';
		require_once LP_ACF_PATH . 'includes/class-lp-acf-settings.php';
		require_once LP_ACF_PATH . 'includes/class-lp-acf-gutenberg.php';
		require_once LP_ACF_PATH . 'includes/class-lp-acf-elementor.php';
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
		add_action( 'admin_notices', array( $this, 'learnpress_notice' ) );
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'lp-advanced-course-filter', false, dirname( LP_ACF_BASENAME ) . '/languages' );
	}

	/**
	 * Initialize plugin pieces.
	 *
	 * @return void
	 */
	public function init() {
		if ( ! $this->is_learnpress_active() ) {
			return;
		}

		LP_ACF_Shortcode::instance();
		LP_ACF_Settings::instance();
		LP_ACF_Gutenberg::instance();
		LP_ACF_Elementor::instance();
	}

	/**
	 * Show admin notice when LearnPress is missing.
	 *
	 * @return void
	 */
	public function learnpress_notice() {
		if ( $this->is_learnpress_active() || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'Advanced Course Filter for LearnPress requires LearnPress 4.2 or newer.', 'lp-advanced-course-filter' )
		);
	}

	/**
	 * Check LearnPress availability.
	 *
	 * @return bool
	 */
	private function is_learnpress_active() {
		return class_exists( 'LearnPress' ) || defined( 'LP_PLUGIN_FILE' ) || defined( 'LEARNPRESS_VERSION' );
	}
}
