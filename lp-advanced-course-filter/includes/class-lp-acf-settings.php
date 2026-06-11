<?php
/**
 * Admin settings entry.
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class LP_ACF_Settings
 */
class LP_ACF_Settings {
	/**
	 * Instance.
	 *
	 * @var LP_ACF_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return LP_ACF_Settings
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
		add_action( 'admin_menu', array( $this, 'add_menu' ), 80 );
	}

	/**
	 * Add settings menu.
	 *
	 * @return void
	 */
	public function add_menu() {
		add_submenu_page(
			'learn_press',
			__( 'Advanced Course Filter', 'lp-advanced-course-filter' ),
			__( 'Advanced Filter', 'lp-advanced-course-filter' ),
			'manage_options',
			'lp-advanced-course-filter',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render settings/help page.
	 *
	 * @return void
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Advanced Course Filter for LearnPress', 'lp-advanced-course-filter' ); ?></h1>
			<p><?php esc_html_e( 'Use the shortcode below on any page to render AJAX course filters.', 'lp-advanced-course-filter' ); ?></p>
			<code>[lp_advanced_course_filter layout="sidebar" target=".lp-list-courses-default"]</code>
			<p><?php esc_html_e( 'Place it in the LearnPress archive sidebar to update the native LearnPress course list.', 'lp-advanced-course-filter' ); ?></p>
		</div>
		<?php
	}
}
