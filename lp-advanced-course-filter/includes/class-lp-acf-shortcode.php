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
		add_action( 'wp_ajax_lp_acf_filter_courses', array( $this, 'ajax_filter_courses' ) );
		add_action( 'wp_ajax_nopriv_lp_acf_filter_courses', array( $this, 'ajax_filter_courses' ) );
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
			array(),
			LP_ACF_VERSION,
			true
		);

		wp_localize_script(
			'lp-acf-frontend',
			'LPACF',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'lp_acf_filter_courses' ),
				'i18n'    => array(
					'loading' => esc_html__( 'Loading courses...', 'lp-advanced-course-filter' ),
					'error'   => esc_html__( 'Could not load courses. Please try again.', 'lp-advanced-course-filter' ),
				),
			)
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
				'layout'   => 'sidebar',
				'per_page' => 9,
				'columns'  => 3,
			),
			$atts,
			'lp_advanced_course_filter'
		);

		$layout   = in_array( $atts['layout'], array( 'sidebar', 'horizontal' ), true ) ? $atts['layout'] : 'sidebar';
		$per_page = max( 1, min( 48, absint( $atts['per_page'] ) ) );
		$columns  = max( 1, min( 4, absint( $atts['columns'] ) ) );

		wp_enqueue_style( 'lp-acf-frontend' );
		wp_enqueue_script( 'lp-acf-frontend' );

		$query = LP_ACF_Query::get_courses(
			array(
				'per_page' => $per_page,
			)
		);

		ob_start();
		?>
		<div class="lp-acf lp-acf--<?php echo esc_attr( $layout ); ?>" data-per-page="<?php echo esc_attr( $per_page ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>">
			<?php $this->render_filters( $layout ); ?>
			<div class="lp-acf__main">
				<div class="lp-acf__bar">
					<div class="lp-acf__summary" aria-live="polite">
						<?php echo esc_html( sprintf( _n( '%d course found', '%d courses found', (int) $query->found_posts, 'lp-advanced-course-filter' ), (int) $query->found_posts ) ); ?>
					</div>
					<label class="lp-acf__sort">
						<span><?php esc_html_e( 'Sort', 'lp-advanced-course-filter' ); ?></span>
						<select name="orderby">
							<option value="date"><?php esc_html_e( 'Newest', 'lp-advanced-course-filter' ); ?></option>
							<option value="title"><?php esc_html_e( 'Title A-Z', 'lp-advanced-course-filter' ); ?></option>
							<option value="price_low"><?php esc_html_e( 'Price low to high', 'lp-advanced-course-filter' ); ?></option>
							<option value="price_high"><?php esc_html_e( 'Price high to low', 'lp-advanced-course-filter' ); ?></option>
						</select>
					</label>
				</div>
				<div class="lp-acf__active" aria-live="polite"></div>
				<div class="lp-acf__results" style="--lp-acf-columns: <?php echo esc_attr( $columns ); ?>">
					<?php echo wp_kses_post( $this->get_results_html( $query ) ); ?>
				</div>
			</div>
		</div>
		<?php
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Render filter controls.
	 *
	 * @param string $layout Layout key.
	 * @return void
	 */
	private function render_filters( $layout ) {
		$search_id  = wp_unique_id( 'lp-acf-search-' );
		$categories = get_terms(
			array(
				'taxonomy'   => 'course_category',
				'hide_empty' => true,
			)
		);
		$levels = function_exists( 'lp_course_level' ) ? lp_course_level() : array(
			'beginner'     => __( 'Beginner', 'lp-advanced-course-filter' ),
			'intermediate' => __( 'Intermediate', 'lp-advanced-course-filter' ),
			'expert'       => __( 'Expert', 'lp-advanced-course-filter' ),
		);
		unset( $levels['all'] );
		?>
		<form class="lp-acf__filters" data-layout="<?php echo esc_attr( $layout ); ?>">
			<div class="lp-acf__search">
				<label for="<?php echo esc_attr( $search_id ); ?>" class="screen-reader-text"><?php esc_html_e( 'Search courses', 'lp-advanced-course-filter' ); ?></label>
				<input id="<?php echo esc_attr( $search_id ); ?>" type="search" name="search" placeholder="<?php esc_attr_e( 'Search courses', 'lp-advanced-course-filter' ); ?>">
			</div>

			<div class="lp-acf__group">
				<h3><?php esc_html_e( 'Category', 'lp-advanced-course-filter' ); ?></h3>
				<?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
					<?php foreach ( $categories as $category ) : ?>
						<label class="lp-acf__choice">
							<input type="checkbox" name="categories[]" value="<?php echo esc_attr( $category->term_id ); ?>" data-label="<?php echo esc_attr( $category->name ); ?>">
							<span><?php echo esc_html( $category->name ); ?></span>
							<small><?php echo esc_html( $category->count ); ?></small>
						</label>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="lp-acf__empty-filter"><?php esc_html_e( 'No categories yet.', 'lp-advanced-course-filter' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="lp-acf__group">
				<h3><?php esc_html_e( 'Price', 'lp-advanced-course-filter' ); ?></h3>
				<label class="lp-acf__choice"><input type="radio" name="price" value="all" data-label="<?php esc_attr_e( 'All prices', 'lp-advanced-course-filter' ); ?>" checked> <span><?php esc_html_e( 'All', 'lp-advanced-course-filter' ); ?></span></label>
				<label class="lp-acf__choice"><input type="radio" name="price" value="free" data-label="<?php esc_attr_e( 'Free', 'lp-advanced-course-filter' ); ?>"> <span><?php esc_html_e( 'Free', 'lp-advanced-course-filter' ); ?></span></label>
				<label class="lp-acf__choice"><input type="radio" name="price" value="paid" data-label="<?php esc_attr_e( 'Paid', 'lp-advanced-course-filter' ); ?>"> <span><?php esc_html_e( 'Paid', 'lp-advanced-course-filter' ); ?></span></label>
			</div>

			<div class="lp-acf__group">
				<h3><?php esc_html_e( 'Level', 'lp-advanced-course-filter' ); ?></h3>
				<?php foreach ( $levels as $key => $label ) : ?>
					<label class="lp-acf__choice">
						<input type="checkbox" name="levels[]" value="<?php echo esc_attr( $key ); ?>" data-label="<?php echo esc_attr( $label ); ?>">
						<span><?php echo esc_html( $label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>

			<div class="lp-acf__group">
				<h3><?php esc_html_e( 'Rating', 'lp-advanced-course-filter' ); ?></h3>
				<label class="lp-acf__choice"><input type="radio" name="rating" value="0" data-label="<?php esc_attr_e( 'Any rating', 'lp-advanced-course-filter' ); ?>" checked> <span><?php esc_html_e( 'Any', 'lp-advanced-course-filter' ); ?></span></label>
				<label class="lp-acf__choice"><input type="radio" name="rating" value="4" data-label="<?php esc_attr_e( '4 stars and up', 'lp-advanced-course-filter' ); ?>"> <span><?php esc_html_e( '4 stars and up', 'lp-advanced-course-filter' ); ?></span></label>
				<label class="lp-acf__choice"><input type="radio" name="rating" value="4.5" data-label="<?php esc_attr_e( '4.5 stars and up', 'lp-advanced-course-filter' ); ?>"> <span><?php esc_html_e( '4.5 stars and up', 'lp-advanced-course-filter' ); ?></span></label>
			</div>

			<div class="lp-acf__actions">
				<button type="submit"><?php esc_html_e( 'Apply', 'lp-advanced-course-filter' ); ?></button>
				<button type="reset"><?php esc_html_e( 'Reset', 'lp-advanced-course-filter' ); ?></button>
			</div>
		</form>
		<?php
	}

	/**
	 * AJAX filter handler.
	 *
	 * @return void
	 */
	public function ajax_filter_courses() {
		check_ajax_referer( 'lp_acf_filter_courses', 'nonce' );

		$query = LP_ACF_Query::get_courses( $_POST );

		wp_send_json_success(
			array(
				'html'       => $this->get_results_html( $query ),
				'found'      => (int) $query->found_posts,
				'totalPages' => (int) $query->max_num_pages,
				'summary'    => sprintf(
					_n( '%d course found', '%d courses found', (int) $query->found_posts, 'lp-advanced-course-filter' ),
					(int) $query->found_posts
				),
			)
		);
	}

	/**
	 * Render results HTML.
	 *
	 * @param WP_Query $query Course query.
	 * @return string
	 */
	private function get_results_html( WP_Query $query ) {
		ob_start();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				include LP_ACF_PATH . 'templates/course-card.php';
			}

			if ( $query->max_num_pages > 1 ) {
				printf(
					'<button type="button" class="lp-acf__load-more" data-page="1" data-total="%1$d">%2$s</button>',
					esc_attr( $query->max_num_pages ),
					esc_html__( 'Load more', 'lp-advanced-course-filter' )
				);
			}
		} else {
			printf(
				'<div class="lp-acf__no-results"><h3>%1$s</h3><p>%2$s</p></div>',
				esc_html__( 'No courses found', 'lp-advanced-course-filter' ),
				esc_html__( 'Try changing your filters or search keyword.', 'lp-advanced-course-filter' )
			);
		}

		wp_reset_postdata();

		return ob_get_clean();
	}
}
