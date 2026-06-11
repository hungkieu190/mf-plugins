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
		$title             = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) : '';
		$layout            = isset( $instance['layout'] ) && in_array( $instance['layout'], array( 'sidebar', 'horizontal' ), true ) ? $instance['layout'] : 'sidebar';
		$fields            = $this->sanitize_fields( $instance['fields'] ?? $this->default_fields() );
		$category_depth    = max( 1, absint( $instance['category_depth'] ?? 2 ) );
		$show_in_rest      = ! empty( $instance['show_in_rest'] ) ? 1 : 0;
		$hide_count_zero   = isset( $instance['hide_count_zero'] ) ? absint( $instance['hide_count_zero'] ) : 1;
		$search_suggestion = isset( $instance['search_suggestion'] ) ? absint( $instance['search_suggestion'] ) : 1;

		echo wp_kses_post( $args['before_widget'] );

		if ( '' !== $title ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		echo do_shortcode(
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

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Render widget admin form.
	 *
	 * @param array $instance Saved instance settings.
	 * @return void
	 */
	public function form( $instance ) {
		$title             = isset( $instance['title'] ) ? $instance['title'] : __( 'Filter Courses', 'lp-advanced-course-filter' );
		$layout            = isset( $instance['layout'] ) ? $instance['layout'] : 'sidebar';
		$fields            = $this->sanitize_fields( $instance['fields'] ?? $this->default_fields() );
		$category_depth    = max( 1, absint( $instance['category_depth'] ?? 2 ) );
		$show_in_rest      = ! empty( $instance['show_in_rest'] );
		$hide_count_zero   = ! isset( $instance['hide_count_zero'] ) || ! empty( $instance['hide_count_zero'] );
		$search_suggestion = ! isset( $instance['search_suggestion'] ) || ! empty( $instance['search_suggestion'] );
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
			<label for="<?php echo esc_attr( $this->get_field_id( 'category_depth' ) ); ?>"><?php esc_html_e( 'Category depth', 'lp-advanced-course-filter' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'category_depth' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_depth' ) ); ?>" type="number" min="1" value="<?php echo esc_attr( (string) $category_depth ); ?>">
		</p>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'show_in_rest' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_in_rest' ) ); ?>" type="checkbox" value="1" <?php checked( $show_in_rest ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_in_rest' ) ); ?>"><?php esc_html_e( 'Load widget via REST', 'lp-advanced-course-filter' ); ?></label>
		</p>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'hide_count_zero' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_count_zero' ) ); ?>" type="checkbox" value="1" <?php checked( $hide_count_zero ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_count_zero' ) ); ?>"><?php esc_html_e( 'Hide options with zero count', 'lp-advanced-course-filter' ); ?></label>
		</p>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'search_suggestion' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_suggestion' ) ); ?>" type="checkbox" value="1" <?php checked( $search_suggestion ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'search_suggestion' ) ); ?>"><?php esc_html_e( 'Enable keyword search suggestion', 'lp-advanced-course-filter' ); ?></label>
		</p>
		<fieldset>
			<legend><?php esc_html_e( 'Fields', 'lp-advanced-course-filter' ); ?></legend>
			<?php foreach ( $this->field_labels() as $field => $label ) : ?>
				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'fields_' . $field ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'fields' ) ); ?>[]" type="checkbox" value="<?php echo esc_attr( $field ); ?>" <?php checked( in_array( $field, $fields, true ) ); ?>>
					<label for="<?php echo esc_attr( $this->get_field_id( 'fields_' . $field ) ); ?>"><?php echo esc_html( $label ); ?></label>
				</p>
			<?php endforeach; ?>
		</fieldset>
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
		unset( $instance['target'] );
		$instance['category_depth']    = max( 1, absint( $new_instance['category_depth'] ?? 2 ) );
		$instance['show_in_rest']      = ! empty( $new_instance['show_in_rest'] ) ? 1 : 0;
		$instance['hide_count_zero']   = ! empty( $new_instance['hide_count_zero'] ) ? 1 : 0;
		$instance['search_suggestion'] = ! empty( $new_instance['search_suggestion'] ) ? 1 : 0;
		$instance['fields']            = $this->sanitize_fields( $new_instance['fields'] ?? array() );

		return $instance;
	}

	/**
	 * Get LearnPress native filter field labels.
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
	 * Sanitize enabled fields.
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
