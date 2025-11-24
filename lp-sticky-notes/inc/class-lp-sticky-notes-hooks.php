<?php
/**
 * Hooks handler for Sticky Notes
 *
 * @package LP_Sticky_Notes
 */

defined( 'ABSPATH' ) || exit();

/**
 * Class LP_Sticky_Notes_Hooks
 */
class LP_Sticky_Notes_Hooks {
	/**
	 * Instance
	 *
	 * @var LP_Sticky_Notes_Hooks
	 */
	protected static $instance = null;

	/**
	 * LP_Sticky_Notes_Hooks constructor.
	 */
	protected function __construct() {
		$this->hooks();
	}

	/**
	 * Register hooks
	 */
	private function hooks() {
		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add sticky notes section to lesson
		add_action( 'learn-press/after-content-item-summary/lp_lesson', array( $this, 'render_sticky_notes_section' ), 20 );

		// Delete notes when lesson is deleted
		add_action( 'before_delete_post', array( $this, 'delete_notes_on_lesson_delete' ) );

		// Delete notes when course is deleted
		add_action( 'before_delete_post', array( $this, 'delete_notes_on_course_delete' ) );

		// Add localized script data
		add_action( 'wp_footer', array( $this, 'add_inline_script' ) );
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Check if we're on a LearnPress lesson page
		$course_item = LP_Global::course_item();
		if ( ! $course_item || ! ( $course_item instanceof LP_Lesson ) ) {
			return;
		}

		// Enqueue styles
		wp_enqueue_style(
			'lp-sticky-notes',
			LP_STICKY_NOTES_URL . 'assets/css/sticky-notes.css',
			array(),
			LP_STICKY_NOTES_VERSION
		);

		// Enqueue scripts
		wp_enqueue_script(
			'lp-sticky-notes',
			LP_STICKY_NOTES_URL . 'assets/js/sticky-notes.js',
			array( 'jquery' ),
			LP_STICKY_NOTES_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'lp-sticky-notes',
			'lpStickyNotes',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'lp-sticky-notes-nonce' ),
				'lessonId'  => get_the_ID(),
				'courseId'  => $this->lp_get_current_course_id(),
				'i18n'      => array(
					'addNote'           => __( 'Add Note', 'lp-sticky-notes' ),
					'editNote'          => __( 'Edit Note', 'lp-sticky-notes' ),
					'deleteNote'        => __( 'Delete Note', 'lp-sticky-notes' ),
					'saveNote'          => __( 'Save Note', 'lp-sticky-notes' ),
					'cancel'            => __( 'Cancel', 'lp-sticky-notes' ),
					'confirmDelete'     => __( 'Are you sure you want to delete this note?', 'lp-sticky-notes' ),
					'noteContent'       => __( 'Note content...', 'lp-sticky-notes' ),
					'highlightText'     => __( 'Highlight text and click "Add Note" to create a highlighted note.', 'lp-sticky-notes' ),
					'noNotes'           => __( 'No notes yet. Start taking notes!', 'lp-sticky-notes' ),
					'error'             => __( 'An error occurred. Please try again.', 'lp-sticky-notes' ),
					'noteAdded'         => __( 'Note added successfully!', 'lp-sticky-notes' ),
					'noteUpdated'       => __( 'Note updated successfully!', 'lp-sticky-notes' ),
					'noteDeleted'       => __( 'Note deleted successfully!', 'lp-sticky-notes' ),
					'textNote'          => __( 'Text Note', 'lp-sticky-notes' ),
					'highlightNote'     => __( 'Highlight Note', 'lp-sticky-notes' ),
					'createdAt'         => __( 'Created:', 'lp-sticky-notes' ),
					'updatedAt'         => __( 'Updated:', 'lp-sticky-notes' ),
				),
			)
		);
	}

	private function lp_get_current_course_id() {
    if ( ! function_exists( 'learn_press_get_course' ) ) {
        return 0;
    }
    $course = learn_press_get_course();
    return $course ? (int) $course->get_id() : 0;
}

	/**
	 * Render sticky notes section
	 */
	public function render_sticky_notes_section() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$course_id = $this->lp_get_current_course_id();

		if ( ! $course_id ) {
			return;
		}
		// Check if user has access to the course
		$user = learn_press_get_current_user();
		if ( ! $user ) {
			return;
		}

		// Get existing notes
		$lesson_id = get_the_ID();
		$db    = LP_Sticky_Notes_Database::instance();
		$notes = $db->get_notes_by_lesson( $lesson_id );

		include LP_STICKY_NOTES_PATH . 'templates/sticky-notes-section.php';
	}

	
	private function mf_get_current_lesson_id() {
		if ( ! function_exists( 'learn_press_get_course' ) ) {
			return 0;
		}

		$item = class_exists( 'LP_Global' ) ? LP_Global::course_item() : null;
		if ( $item && $item instanceof LP_Course_Item ) {
			if ( defined( 'LP_LESSON_CPT' ) && $item->get_item_type() === LP_LESSON_CPT ) {
				return (int) $item->get_id();
			}
		}

		$course = learn_press_get_course();
		if ( $course ) {
			$current_item = $course->get_current_item();
			if ( $current_item && defined( 'LP_LESSON_CPT' ) && $current_item->get_item_type() === LP_LESSON_CPT ) {
				return (int) $current_item->get_id();
			}
		}

		return 0;
	}


	

	/**
	 * Get course ID from lesson
	 *
	 * @param int $lesson_id Lesson ID
	 * @return int Course ID
	 */
	private function get_course_id_from_lesson( $lesson_id ) {
        $lesson_id = absint( $lesson_id );
		if ( $lesson_id <= 0 ) {
			return 0;
		}


		// Fallback: direct SQL on LP tables
		global $wpdb;
	
		// Ensure LP custom tables are available on this site
		if ( empty( $wpdb->learnpress_sections ) || empty( $wpdb->learnpress_section_items ) ) {
			return 0;
		}

		$sql = $wpdb->prepare(
			"SELECT s.section_course_id
			FROM {$wpdb->learnpress_sections} AS s
			INNER JOIN {$wpdb->learnpress_section_items} AS si
			ON si.section_id = s.section_id
			WHERE si.item_id = %d
			LIMIT 1",
			$lesson_id
		);

		$course_id = (int) $wpdb->get_var( $sql );

		return $course_id ?: 0;
	}

	/**
	 * Delete notes when lesson is deleted
	 *
	 * @param int $post_id Post ID
	 */
	public function delete_notes_on_lesson_delete( $post_id ) {
		if ( get_post_type( $post_id ) !== LP_LESSON_CPT ) {
			return;
		}

		$db = LP_Sticky_Notes_Database::instance();
		$db->delete_notes_by_lesson( $post_id );
	}

	/**
	 * Delete notes when course is deleted
	 *
	 * @param int $post_id Post ID
	 */
	public function delete_notes_on_course_delete( $post_id ) {
		if ( get_post_type( $post_id ) !== LP_COURSE_CPT ) {
			return;
		}

		$db = LP_Sticky_Notes_Database::instance();
		$db->delete_notes_by_course( $post_id );
	}

	/**
	 * Add inline script for highlight functionality
	 */
	public function add_inline_script() {
		// Check if we're on a LearnPress lesson page
		$course_item = LP_Global::course_item();
		if ( ! $course_item || ! ( $course_item instanceof LP_Lesson ) || ! is_user_logged_in() ) {
			return;
		}
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Debug: Check if highlight is working
			console.log('LP Sticky Notes: Inline script loaded');
		});
		</script>
		<?php
	}

	/**
	 * Get instance
	 *
	 * @return LP_Sticky_Notes_Hooks
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}