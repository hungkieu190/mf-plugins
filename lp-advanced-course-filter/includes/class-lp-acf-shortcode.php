<?php
/**
 * Shortcode and AJAX handlers.
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class LP_ACF_Shortcode
 */
class LP_ACF_Shortcode {
	/**
	 * Instance.
	 *
	 * @var LP_ACF_Shortcode|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return LP_ACF_Shortcode
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
		add_shortcode( 'lp_advanced_course_filter', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Register frontend assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			'lp-acf-frontend',
			LP_ACF_URL . 'assets/css/frontend.css',
			array(),
			LP_ACF_VERSION
		);

		wp_register_script(
			'lp-acf-frontend',
			LP_ACF_URL . 'assets/js/frontend.js',
			array( 'lp-course-filter' ),
			LP_ACF_VERSION,
			true
		);
	}

	/**
	 * Render shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'layout'            => 'sidebar',
				'target'            => '.lp-list-courses-default',
				'fields'            => 'search,price,category,tag,author,level,type,btn_submit,btn_reset',
				'title'             => '',
				'rest'              => 0,
				'category_depth'    => 2,
				'hide_count_zero'   => 1,
				'search_suggestion' => 1,
			),
			$atts,
			'lp_advanced_course_filter'
		);

		$layout            = in_array( $atts['layout'], array( 'sidebar', 'horizontal' ), true ) ? $atts['layout'] : 'sidebar';
		$target            = sanitize_text_field( wp_unslash( $atts['target'] ) );
		$fields            = $this->sanitize_fields( explode( ',', (string) $atts['fields'] ) );
		$title             = sanitize_text_field( wp_unslash( $atts['title'] ) );
		$use_rest          = ! empty( $atts['rest'] );
		$category_depth    = max( 1, absint( $atts['category_depth'] ) );
		$hide_count_zero   = ! empty( $atts['hide_count_zero'] ) ? 1 : 0;
		$search_suggestion = ! empty( $atts['search_suggestion'] ) ? 1 : 0;

		wp_enqueue_style( 'lp-acf-frontend' );
		wp_enqueue_script( 'lp-course-filter' );
		wp_enqueue_script( 'lp-acf-frontend' );
		if ( $use_rest ) {
			wp_enqueue_script( 'lp-widgets' );
		}

		return $this->render_learnpress_filter(
			array(
				'layout'                    => $layout,
				'title'                     => $title,
				'fields'                    => $fields,
				'class_list_courses_target' => $target,
				'show_in_rest'              => $use_rest ? 1 : 0,
				'number_level_category'     => $category_depth,
				'hide_count_zero'           => $hide_count_zero,
				'search_suggestion'         => $search_suggestion,
			)
		);
	}

	/**
	 * Render the native LearnPress course filter form.
	 *
	 * @param array $args Render arguments.
	 * @return string
	 */
	public function render_learnpress_filter( array $args = array() ) {
		$layout = in_array( $args['layout'] ?? 'sidebar', array( 'sidebar', 'horizontal' ), true ) ? $args['layout'] : 'sidebar';
		$fields = ! empty( $args['fields'] ) && is_array( $args['fields'] )
			? $this->sanitize_fields( $args['fields'] )
			: $this->default_fields();
		$target = ! empty( $args['class_list_courses_target'] ) ? sanitize_text_field( $args['class_list_courses_target'] ) : '.lp-list-courses-default';

		$instance = array(
			'title'                     => $args['title'] ?? '',
			'number_level_category'     => max( 1, absint( $args['number_level_category'] ?? 2 ) ),
			'class_list_courses_target' => $target,
			'show_in_rest'              => ! empty( $args['show_in_rest'] ) ? 1 : 0,
			'hide_count_zero'           => isset( $args['hide_count_zero'] ) ? absint( $args['hide_count_zero'] ) : 1,
			'search_suggestion'         => isset( $args['search_suggestion'] ) ? absint( $args['search_suggestion'] ) : 1,
			'fields'                    => $fields,
		);

		$data = array_merge(
			array(
				'params_url'                => function_exists( 'lp_archive_skeleton_get_args' ) ? lp_archive_skeleton_get_args() : array(),
				'class_wrapper_form'        => 'lp-form-course-filter lp-acf-native-filter lp-acf-native-filter--' . $layout,
				'class_list_courses_target' => $target,
			),
			$instance
		);

		$wrapper_class = 'lp-acf lp-acf--' . $layout . ' lp-acf--native learnpress-widget-wrapper';
		if ( ! empty( $instance['show_in_rest'] ) ) {
			$wrapper_class .= ' learnpress-widget-wrapper__restapi';
		}

		ob_start();
		printf(
			'<div class="%1$s" data-widget="%2$s">',
			esc_attr( $wrapper_class ),
			esc_attr(
				wp_json_encode(
					array(
						'widget'   => 'learnpress_widget_course_filter',
						'instance' => wp_json_encode( $instance ),
					)
				)
			)
		);
		if ( ! empty( $instance['show_in_rest'] ) && function_exists( 'lp_skeleton_animation_html' ) ) {
			lp_skeleton_animation_html( 5 );
		} else {
			do_action( 'learn-press/filter-courses/layout', $data );
		}
		echo '<div class="lp-widget-loading-change"></div>';
		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Get supported LearnPress native filter fields.
	 *
	 * @return array
	 */
	public function default_fields() {
		return array( 'search', 'price', 'category', 'tag', 'author', 'level', 'type', 'btn_submit', 'btn_reset' );
	}

	/**
	 * Sanitize field list while preserving order.
	 *
	 * @param array $fields Raw field keys.
	 * @return array
	 */
	public function sanitize_fields( array $fields ) {
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
