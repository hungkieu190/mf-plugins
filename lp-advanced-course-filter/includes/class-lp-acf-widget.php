<?php
/**
 * WordPress widget integration.
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class LP_ACF_Widget
 */
class LP_ACF_Widget extends WP_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'lp_advanced_course_filter',
			__( 'LearnPress Advanced Course Filter', 'lp-advanced-course-filter' ),
			array(
				'classname'                   => 'lp_acf_widget',
				'description'                 => __( 'AJAX course filter for LearnPress course sidebars.', 'lp-advanced-course-filter' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Render widget frontend.
	 *
	 * @param array $args Widget display arguments.
	 * @param array $instance Saved instance settings.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) : '';
		$layout   = isset( $instance['layout'] ) && in_array( $instance['layout'], array( 'sidebar', 'horizontal' ), true ) ? $instance['layout'] : 'sidebar';
		$per_page = isset( $instance['per_page'] ) ? max( 1, min( 48, absint( $instance['per_page'] ) ) ) : 9;
		$columns  = isset( $instance['columns'] ) ? max( 1, min( 4, absint( $instance['columns'] ) ) ) : 3;

		echo wp_kses_post( $args['before_widget'] );

		if ( '' !== $title ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		echo do_shortcode(
			sprintf(
				'[lp_advanced_course_filter layout="%1$s" per_page="%2$d" columns="%3$d"]',
				esc_attr( $layout ),
				$per_page,
				$columns
			)
		);

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Render widget admin form.
	 *
	 * @param array $instance Saved instance settings.
	 * @return void
	 */
	public function form( $instance ) {
		$title    = isset( $instance['title'] ) ? $instance['title'] : __( 'Filter Courses', 'lp-advanced-course-filter' );
		$layout   = isset( $instance['layout'] ) ? $instance['layout'] : 'sidebar';
		$per_page = isset( $instance['per_page'] ) ? absint( $instance['per_page'] ) : 9;
		$columns  = isset( $instance['columns'] ) ? absint( $instance['columns'] ) : 3;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'lp-advanced-course-filter' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>"><?php esc_html_e( 'Layout', 'lp-advanced-course-filter' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>">
				<option value="sidebar" <?php selected( $layout, 'sidebar' ); ?>><?php esc_html_e( 'Sidebar', 'lp-advanced-course-filter' ); ?></option>
				<option value="horizontal" <?php selected( $layout, 'horizontal' ); ?>><?php esc_html_e( 'Horizontal', 'lp-advanced-course-filter' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'per_page' ) ); ?>"><?php esc_html_e( 'Courses per page', 'lp-advanced-course-filter' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'per_page' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'per_page' ) ); ?>" type="number" min="1" max="48" value="<?php echo esc_attr( $per_page ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>"><?php esc_html_e( 'Columns', 'lp-advanced-course-filter' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>" type="number" min="1" max="4" value="<?php echo esc_attr( $columns ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget settings.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']    = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['layout']   = isset( $new_instance['layout'] ) && in_array( $new_instance['layout'], array( 'sidebar', 'horizontal' ), true ) ? $new_instance['layout'] : 'sidebar';
		$instance['per_page'] = isset( $new_instance['per_page'] ) ? max( 1, min( 48, absint( $new_instance['per_page'] ) ) ) : 9;
		$instance['columns']  = isset( $new_instance['columns'] ) ? max( 1, min( 4, absint( $new_instance['columns'] ) ) ) : 3;

		return $instance;
	}
}
