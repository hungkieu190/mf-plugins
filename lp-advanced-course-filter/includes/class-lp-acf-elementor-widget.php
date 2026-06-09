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
			'per_page',
			array(
				'label'   => __( 'Courses per page', 'lp-advanced-course-filter' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 9,
				'min'     => 1,
				'max'     => 48,
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'lp-advanced-course-filter' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
				'max'     => 4,
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
		echo do_shortcode(
			sprintf(
				'[lp_advanced_course_filter layout="%1$s" per_page="%2$d" columns="%3$d"]',
				esc_attr( $settings['layout'] ?? 'sidebar' ),
				absint( $settings['per_page'] ?? 9 ),
				absint( $settings['columns'] ?? 3 )
			)
		);
	}
}
