<?php
/**
 * Profile integration for Sticky Notes
 *
 * @package LP_Sticky_Notes
 */

defined( 'ABSPATH' ) || exit();

/**
 * Class LP_Sticky_Notes_Profile
 */
class LP_Sticky_Notes_Profile {
	/**
	 * Instance
	 *
	 * @var LP_Sticky_Notes_Profile
	 */
	protected static $instance = null;

	/**
	 * LP_Sticky_Notes_Profile constructor.
	 */
	protected function __construct() {
		$this->hooks();
	}

	/**
	 * Register hooks
	 */
	private function hooks() {
		// Add My Notes tab to profile
		add_filter( 'learn-press/profile-tabs', array( $this, 'add_notes_tab' ), 20 );

		// Enqueue scripts for profile page
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_profile_scripts' ) );
	}

	/**
	 * Add My Notes tab to profile
	 *
	 * @param array $tabs Profile tabs
	 * @return array
	 */
	public function add_notes_tab( $tabs ) {
		$tabs['my-notes'] = array(
			'title'    => __( 'My Notes', 'lp-sticky-notes' ),
			'slug'     => 'my-notes',
			'callback' => array( $this, 'render_notes_tab' ),
			'priority' => 25,
			'icon'     => '<i class="fas fa-sticky-note"></i>',
		);

		return $tabs;
	}

	/**
	 * Render My Notes tab content
	 */
	public function render_notes_tab() {
		$profile = LP_Profile::instance();
		$user    = $profile->get_user();

		if ( ! $user ) {
			return;
		}

		// Get filter parameters
		$course_id = isset( $_GET['filter_course'] ) ? absint( $_GET['filter_course'] ) : '';

		// Get user's notes
		$db    = LP_Sticky_Notes_Database::instance();
		$notes = $db->get_user_notes( $user->get_id(), $course_id );

		// Get user's courses for filter
		$user_courses = $this->get_user_courses( $user->get_id() );

		include LP_STICKY_NOTES_PATH . 'templates/profile-notes-tab.php';
	}

	/**
	 * Get user's enrolled courses
	 *
	 * @param int $user_id User ID
	 * @return array
	 */
	private function get_user_courses( $user_id ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT DISTINCT p.ID, p.post_title
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->prefix}learnpress_user_items ui ON p.ID = ui.item_id
			WHERE ui.user_id = %d
			AND p.post_type = %s
			AND p.post_status = 'publish'
			ORDER BY p.post_title ASC",
			$user_id,
			LP_COURSE_CPT
		);

		return $wpdb->get_results( $query );
	}

	/**
	 * Enqueue scripts for profile page
	 */
	public function enqueue_profile_scripts() {
		// Check if we're on the profile page
		if ( ! learn_press_is_profile() ) {
			return;
		}

		// Check if we're on the my-notes tab
		$profile = LP_Profile::instance();
		if ( ! $profile || $profile->get_current_tab() !== 'my-notes' ) {
			return;
		}

		// Enqueue styles
		wp_enqueue_style(
			'lp-sticky-notes-profile',
			LP_STICKY_NOTES_URL . 'assets/css/profile-notes.css',
			array(),
			LP_STICKY_NOTES_VERSION
		);

		// Enqueue scripts
		wp_enqueue_script(
			'lp-sticky-notes-profile',
			LP_STICKY_NOTES_URL . 'assets/js/profile-notes.js',
			array( 'jquery' ),
			LP_STICKY_NOTES_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'lp-sticky-notes-profile',
			'lpStickyNotesProfile',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'lp-sticky-notes-nonce' ),
				'i18n'    => array(
					'confirmDelete' => __( 'Are you sure you want to delete this note?', 'lp-sticky-notes' ),
					'noteDeleted'   => __( 'Note deleted successfully!', 'lp-sticky-notes' ),
					'error'         => __( 'An error occurred. Please try again.', 'lp-sticky-notes' ),
					'viewLesson'    => __( 'View Lesson', 'lp-sticky-notes' ),
					'deleteNote'    => __( 'Delete Note', 'lp-sticky-notes' ),
					'editNote'      => __( 'Edit Note', 'lp-sticky-notes' ),
				),
			)
		);
	}

	/**
	 * Get instance
	 *
	 * @return LP_Sticky_Notes_Profile
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}