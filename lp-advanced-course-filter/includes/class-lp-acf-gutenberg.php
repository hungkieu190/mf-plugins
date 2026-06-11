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
					'fields'  => array(
						'type'    => 'array',
						'default' => array( 'search', 'price', 'category', 'tag', 'author', 'level', 'type', 'btn_submit', 'btn_reset' ),
					),
					'categoryDepth' => array(
						'type'    => 'number',
						'default' => 2,
					),
					'showInRest' => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'hideCountZero' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'searchSuggestion' => array(
						'type'    => 'boolean',
						'default' => true,
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
		$layout            = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'sidebar';
		$fields            = $this->sanitize_fields( $attributes['fields'] ?? $this->default_fields() );
		$category_depth    = max( 1, absint( $attributes['categoryDepth'] ?? 2 ) );
		$show_in_rest      = ! empty( $attributes['showInRest'] ) ? 1 : 0;
		$hide_count_zero   = ! isset( $attributes['hideCountZero'] ) || ! empty( $attributes['hideCountZero'] ) ? 1 : 0;
		$search_suggestion = ! isset( $attributes['searchSuggestion'] ) || ! empty( $attributes['searchSuggestion'] ) ? 1 : 0;

		return do_shortcode(
			sprintf(
				'[lp_advanced_course_filter layout="%1$s" target="%2$s" fields="%3$s" category_depth="%4$d" rest="%5$d" hide_count_zero="%6$d" search_suggestion="%7$d"]',
				esc_attr( $layout ),
				'.lp-list-courses-default',
				esc_attr( implode( ',', $fields ) ),
				$category_depth,
				$show_in_rest,
				$hide_count_zero,
				$search_suggestion
			)
		);
	}

	/**
	 * Get default fields.
	 *
	 * @return array
	 */
	private function default_fields() {
		return array( 'search', 'price', 'category', 'tag', 'author', 'level', 'type', 'btn_submit', 'btn_reset' );
	}

	/**
	 * Sanitize fields.
	 *
	 * @param array|string $fields Raw fields.
	 * @return array
	 */
	private function sanitize_fields( $fields ) {
		$fields  = is_array( $fields ) ? $fields : explode( ',', (string) $fields );
		$allowed = $this->default_fields();
		$clean   = array();

		foreach ( $fields as $field ) {
			$field = sanitize_key( $field );
			if ( in_array( $field, $allowed, true ) && ! in_array( $field, $clean, true ) ) {
				$clean[] = $field;
			}
		}

		return $clean ? $clean : $allowed;
	}
}
