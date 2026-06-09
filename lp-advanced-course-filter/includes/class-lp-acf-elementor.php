<?php
/**
 * Elementor integration.
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class LP_ACF_Elementor
 */
class LP_ACF_Elementor {
	/**
	 * Instance.
	 *
	 * @var LP_ACF_Elementor|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return LP_ACF_Elementor
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
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
	}

	/**
	 * Register Elementor widget when Elementor is active.
	 *
	 * @param object $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_widget( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		if ( ! class_exists( 'LP_ACF_Elementor_Widget' ) ) {
			require_once LP_ACF_PATH . 'includes/class-lp-acf-elementor-widget.php';
		}

		$widgets_manager->register( new LP_ACF_Elementor_Widget() );
	}
}
