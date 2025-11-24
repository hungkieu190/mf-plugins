<?php
/**
 * Template for My Notes tab in profile
 *
 * @package LP_Sticky_Notes
 */

defined( 'ABSPATH' ) || exit();
?>

<div class="lp-profile-notes-wrapper">
	<div class="lp-profile-notes-header">
		<h2><?php esc_html_e( 'My Notes', 'lp-sticky-notes' ); ?></h2>
		
		<!-- Filter by Course -->
		<div class="lp-notes-filter">
			<form method="get" class="lp-notes-filter-form">
				<input type="hidden" name="user" value="<?php echo esc_attr( $user->get_username() ); ?>">
				<input type="hidden" name="tab" value="my-notes">
				
				<label for="filter_course"><?php esc_html_e( 'Filter by Course:', 'lp-sticky-notes' ); ?></label>
				<select name="filter_course" id="filter_course" onchange="this.form.submit()">
					<option value=""><?php esc_html_e( 'All Courses', 'lp-sticky-notes' ); ?></option>
					<?php foreach ( $user_courses as $course ) : ?>
						<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course_id, $course->ID ); ?>>
							<?php echo esc_html( $course->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</form>
		</div>
	</div>

	<div class="lp-profile-notes-stats">
		<div class="lp-stat-item">
			<span class="lp-stat-number"><?php echo count( $notes ); ?></span>
			<span class="lp-stat-label"><?php esc_html_e( 'Total Notes', 'lp-sticky-notes' ); ?></span>
		</div>
		<div class="lp-stat-item">
			<span class="lp-stat-number">
				<?php echo count( array_filter( $notes, function( $note ) { return $note->note_type === 'highlight'; } ) ); ?>
			</span>
			<span class="lp-stat-label"><?php esc_html_e( 'Highlights', 'lp-sticky-notes' ); ?></span>
		</div>
		<div class="lp-stat-item">
			<span class="lp-stat-number">
				<?php echo count( array_unique( array_column( $notes, 'course_id' ) ) ); ?>
			</span>
			<span class="lp-stat-label"><?php esc_html_e( 'Courses', 'lp-sticky-notes' ); ?></span>
		</div>
	</div>

	<div class="lp-profile-notes-list">
		<?php if ( empty( $notes ) ) : ?>
			<div class="lp-no-notes">
				<i class="lp-icon-note-empty"></i>
				<h3><?php esc_html_e( 'No notes found', 'lp-sticky-notes' ); ?></h3>
				<p><?php esc_html_e( 'Start taking notes in your lessons to see them here!', 'lp-sticky-notes' ); ?></p>
			</div>
		<?php else : ?>
			<?php
			$current_course = '';
			foreach ( $notes as $note ) :
				// Group by course
				if ( $current_course !== $note->course_title ) :
					if ( $current_course !== '' ) :
						echo '</div>'; // Close previous course group
					endif;
					$current_course = $note->course_title;
					?>
					<div class="lp-notes-course-group">
						<h3 class="lp-course-title">
							<i class="lp-icon-course"></i>
							<?php echo esc_html( $note->course_title ); ?>
						</h3>
						<div class="lp-course-notes">
				<?php endif; ?>

				<div class="lp-profile-note-item" data-note-id="<?php echo esc_attr( $note->id ); ?>">
					<div class="lp-note-meta">
						<span class="lp-note-type-badge lp-note-type-<?php echo esc_attr( $note->note_type ); ?>">
							<?php echo $note->note_type === 'highlight' ? esc_html__( 'Highlight', 'lp-sticky-notes' ) : esc_html__( 'Text', 'lp-sticky-notes' ); ?>
						</span>
						<span class="lp-note-lesson">
							<i class="lp-icon-lesson"></i>
							<?php echo esc_html( $note->lesson_title ); ?>
						</span>
						<span class="lp-note-date">
							<?php echo esc_html( human_time_diff( strtotime( $note->created_at ), current_time( 'timestamp' ) ) ); ?>
							<?php esc_html_e( 'ago', 'lp-sticky-notes' ); ?>
						</span>
					</div>

					<?php if ( $note->note_type === 'highlight' && ! empty( $note->highlight_text ) ) : ?>
						<div class="lp-note-highlight">
							<strong><?php esc_html_e( 'Highlighted:', 'lp-sticky-notes' ); ?></strong>
							<div class="lp-highlight-text"><?php echo wp_kses_post( $note->highlight_text ); ?></div>
						</div>
					<?php endif; ?>

					<div class="lp-note-content">
						<?php echo wp_kses_post( wpautop( $note->content ) ); ?>
					</div>

					<div class="lp-note-actions">
						<a href="<?php echo esc_url( get_permalink( $note->lesson_id ) ); ?>" class="lp-btn lp-btn-view">
							<i class="lp-icon-eye"></i>
							<?php esc_html_e( 'View Lesson', 'lp-sticky-notes' ); ?>
						</a>
						<button type="button" class="lp-btn lp-btn-delete" data-note-id="<?php echo esc_attr( $note->id ); ?>">
							<i class="lp-icon-trash"></i>
							<?php esc_html_e( 'Delete', 'lp-sticky-notes' ); ?>
						</button>
					</div>
				</div>

			<?php
			endforeach;
			if ( $current_course !== '' ) :
				echo '</div></div>'; // Close last course group
			endif;
			?>
		<?php endif; ?>
	</div>
</div>