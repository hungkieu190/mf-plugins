<?php
/**
 * Elementor widget.
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class LP_ACF_Elementor_Widget
 */
class LP_ACF_Elementor_Widget extends \Elementor\Widget_Base {
	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'lp_advanced_course_filter';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Advanced Course Filter', 'lp-advanced-course-filter' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-filter';
	}

	/**
	 * Get categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'learnpress' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content',
			array(
				'label' => __( 'Content', 'lp-advanced-course-filter' ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'lp-advanced-course-filter' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'sidebar',
				'options' => array(
					'sidebar'    => __( 'Sidebar', 'lp-advanced-course-filter' ),
					'horizontal' => __( 'Horizontal', 'lp-advanced-course-filter' ),
				),
			)
		);

		$this->add_control(
			'category_depth',
			array(
				'label'   => __( 'Category depth', 'lp-advanced-course-filter' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 2,
				'min'     => 1,
				'step'    => 1,
			)
		);

		$this->add_control(
			'show_in_rest',
			array(
				'label'        => __( 'Load widget via REST', 'lp-advanced-course-filter' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'lp-advanced-course-filter' ),
				'label_off'    => __( 'No', 'lp-advanced-course-filter' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'hide_count_zero',
			array(
				'label'        => __( 'Hide options with zero count', 'lp-advanced-course-filter' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'lp-advanced-course-filter' ),
				'label_off'    => __( 'No', 'lp-advanced-course-filter' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'search_suggestion',
			array(
				'label'        => __( 'Enable keyword search suggestion', 'lp-advanced-course-filter' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'lp-advanced-course-filter' ),
				'label_off'    => __( 'No', 'lp-advanced-course-filter' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'fields',
			array(
				'label'       => __( 'Fields', 'lp-advanced-course-filter' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'default'     => $this->default_fields(),
				'options'     => $this->field_labels(),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$fields   = isset( $settings['fields'] ) && is_array( $settings['fields'] ) ? $this->sanitize_fields( $settings['fields'] ) : $this->default_fields();

		echo do_shortcode(
			sprintf(
				'[lp_advanced_course_filter layout="%1$s" target="%2$s" fields="%3$s" category_depth="%4$d" rest="%5$d" hide_count_zero="%6$d" search_suggestion="%7$d"]',
				esc_attr( $settings['layout'] ?? 'sidebar' ),
				'.lp-list-courses-default',
				esc_attr( implode( ',', $fields ) ),
				max( 1, absint( $settings['category_depth'] ?? 2 ) ),
				'yes' === ( $settings['show_in_rest'] ?? '' ) ? 1 : 0,
				'yes' === ( $settings['hide_count_zero'] ?? 'yes' ) ? 1 : 0,
				'yes' === ( $settings['search_suggestion'] ?? 'yes' ) ? 1 : 0
			)
		);
	}

	/**
	 * Get field labels.
	 *
	 * @return array
	 */
	private function field_labels() {
		return array(
			'search'     => __( 'Keyword', 'lp-advanced-course-filter' ),
			'price'      => __( 'Price', 'lp-advanced-course-filter' ),
			'category'   => __( 'Course Category', 'lp-advanced-course-filter' ),
			'tag'        => __( 'Course Tag', 'lp-advanced-course-filter' ),
			'author'     => __( 'Author', 'lp-advanced-course-filter' ),
			'level'      => __( 'Level', 'lp-advanced-course-filter' ),
			'type'       => __( 'Type (Online/Offline)', 'lp-advanced-course-filter' ),
			'btn_submit' => __( 'Button Submit', 'lp-advanced-course-filter' ),
			'btn_reset'  => __( 'Button Reset', 'lp-advanced-course-filter' ),
		);
	}

	/**
	 * Get default fields.
	 *
	 * @return array
	 */
	private function default_fields() {
		return array_keys( $this->field_labels() );
	}

	/**
	 * Sanitize fields.
	 *
	 * @param array $fields Raw fields.
	 * @return array
	 */
	private function sanitize_fields( array $fields ) {
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
