<?php
/**
 * Gutenberg block registration.
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class LP_ACF_Gutenberg
 */
class LP_ACF_Gutenberg {
	/**
	 * Instance.
	 *
	 * @var LP_ACF_Gutenberg|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return LP_ACF_Gutenberg
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
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register a server-side block without a build step.
	 *
	 * @return void
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'lp-acf-block',
			LP_ACF_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n' ),
			LP_ACF_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'lp-acf-block', 'lp-advanced-course-filter', LP_ACF_PATH . 'languages' );
		}

		register_block_type(
			'lp-advanced-course-filter/filter',
			array(
				'api_version'     => 2,
				'editor_script'   => 'lp-acf-block',
				'title'           => __( 'Advanced Course Filter', 'lp-advanced-course-filter' ),
				'category'        => 'widgets',
				'icon'            => 'filter',
				'description'     => __( 'AJAX course filter for LearnPress.', 'lp-advanced-course-filter' ),
				'attributes'      => array(
					'layout'  => array(
						'type'    => 'string',
						'default' => 'sidebar',
					),
					'perPage' => array(
						'type'    => 'number',
						'default' => 9,
					),
					'columns' => array(
						'type'    => 'number',
						'default' => 3,
					),
				),
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render block via shortcode.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_block( $attributes ) {
		$layout   = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'sidebar';
		$per_page = isset( $attributes['perPage'] ) ? absint( $attributes['perPage'] ) : 9;
		$columns  = isset( $attributes['columns'] ) ? absint( $attributes['columns'] ) : 3;

		return do_shortcode(
			sprintf(
				'[lp_advanced_course_filter layout="%1$s" per_page="%2$d" columns="%3$d"]',
				esc_attr( $layout ),
				$per_page,
				$columns
			)
		);
	}
}
