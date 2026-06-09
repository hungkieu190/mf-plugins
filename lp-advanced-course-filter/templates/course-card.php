<?php
/**
 * Course card template.
 *
 * @package LP_Advanced_Course_Filter
 */

defined( 'ABSPATH' ) || exit;

$course_id  = get_the_ID();
$price      = get_post_meta( $course_id, '_lp_price', true );
$level      = function_exists( 'learn_press_get_post_level' ) ? learn_press_get_post_level( $course_id ) : get_post_meta( $course_id, '_lp_level', true );
$rating     = function_exists( 'learn_press_get_course_rate' ) ? (float) learn_press_get_course_rate( $course_id ) : (float) get_post_meta( $course_id, '_lp_average_rating', true );
$categories = get_the_term_list( $course_id, 'course_category', '', ', ' );
?>
<article class="lp-acf-card">
	<a class="lp-acf-card__image" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large' ); ?>
		<?php else : ?>
			<span><?php esc_html_e( 'Course', 'lp-advanced-course-filter' ); ?></span>
		<?php endif; ?>
	</a>
	<div class="lp-acf-card__body">
		<?php if ( $categories ) : ?>
			<div class="lp-acf-card__terms"><?php echo wp_kses_post( $categories ); ?></div>
		<?php endif; ?>
		<h3 class="lp-acf-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<div class="lp-acf-card__meta">
			<span><?php echo esc_html( $level ); ?></span>
			<?php if ( $rating > 0 ) : ?>
				<span><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?> / 5</span>
			<?php endif; ?>
		</div>
		<div class="lp-acf-card__footer">
			<strong>
				<?php
				if ( function_exists( 'learn_press_get_course' ) ) {
					$course = learn_press_get_course( $course_id );
					echo $course ? wp_kses_post( $course->get_price_html() ) : esc_html( $price );
				} elseif ( '' === $price || (float) $price <= 0 ) {
					esc_html_e( 'Free', 'lp-advanced-course-filter' );
				} else {
					echo esc_html( $price );
				}
				?>
			</strong>
			<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'View course', 'lp-advanced-course-filter' ); ?></a>
		</div>
	</div>
</article>
