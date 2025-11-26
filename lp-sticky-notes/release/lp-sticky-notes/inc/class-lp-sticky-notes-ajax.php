<?php
/**
 * AJAX handler for Sticky Notes
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();

/**
 * Class LP_Sticky_Notes_Ajax
 */
class LP_Sticky_Notes_Ajax
{
	/**
	 * Instance
	 *
	 * @var LP_Sticky_Notes_Ajax
	 */
	protected static $instance = null;

	/**
	 * LP_Sticky_Notes_Ajax constructor.
	 */
	protected function __construct()
	{
		$this->hooks();
	}

	/**
	 * Register hooks
	 */
	private function hooks()
	{
		add_action('wp_ajax_lp_sticky_notes_add', array($this, 'add_note'));
		add_action('wp_ajax_lp_sticky_notes_update', array($this, 'update_note'));
		add_action('wp_ajax_lp_sticky_notes_delete', array($this, 'delete_note'));
		add_action('wp_ajax_lp_sticky_notes_get', array($this, 'get_notes'));
		add_action('wp_ajax_lp_sticky_notes_get_single', array($this, 'get_single_note'));
		add_action('wp_ajax_lp_sticky_notes_get_all', array($this, 'get_all_notes'));
	}

	/**
	 * Add a new note
	 */
	public function add_note()
	{
		check_ajax_referer('lp-sticky-notes-nonce', 'nonce');

		if (!is_user_logged_in()) {
			wp_send_json_error(array('message' => __('You must be logged in to add notes.', 'lp-sticky-notes')));
		}

		$lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
		$course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
		$content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
		$note_type = isset($_POST['note_type']) ? sanitize_text_field($_POST['note_type']) : 'text';
		$highlight_text = isset($_POST['highlight_text']) ? wp_kses_post($_POST['highlight_text']) : '';
		$position = isset($_POST['position']) ? json_decode(stripslashes($_POST['position']), true) : null;

		if (!$lesson_id || !$course_id) {
			wp_send_json_error(array('message' => __('Invalid lesson or course ID.', 'lp-sticky-notes')));
		}

		if (empty($content) && empty($highlight_text)) {
			wp_send_json_error(array('message' => __('Note content cannot be empty.', 'lp-sticky-notes')));
		}

		// Check if user is enrolled in the course
		if (!$this->user_can_access_course($course_id)) {
			wp_send_json_error(array('message' => __('You do not have access to this course.', 'lp-sticky-notes')));
		}

		$db = LP_Sticky_Notes_Database::instance();

		$note_id = $db->insert_note(
			array(
				'user_id' => get_current_user_id(),
				'course_id' => $course_id,
				'lesson_id' => $lesson_id,
				'note_type' => $note_type,
				'highlight_text' => $highlight_text,
				'position' => $position,
				'content' => $content,
			)
		);

		if ($note_id) {
			$note = $db->get_note($note_id);
			wp_send_json_success(
				array(
					'message' => __('Note added successfully.', 'lp-sticky-notes'),
					'note' => $note,
				)
			);
		} else {
			wp_send_json_error(array('message' => __('Failed to add note.', 'lp-sticky-notes')));
		}
	}

	/**
	 * Update a note
	 */
	public function update_note()
	{
		check_ajax_referer('lp-sticky-notes-nonce', 'nonce');

		if (!is_user_logged_in()) {
			wp_send_json_error(array('message' => __('You must be logged in to update notes.', 'lp-sticky-notes')));
		}

		$note_id = isset($_POST['note_id']) ? absint($_POST['note_id']) : 0;
		$content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
		$highlight_text = isset($_POST['highlight_text']) ? wp_kses_post($_POST['highlight_text']) : null;
		$position = isset($_POST['position']) ? json_decode(stripslashes($_POST['position']), true) : null;

		if (!$note_id) {
			wp_send_json_error(array('message' => __('Invalid note ID.', 'lp-sticky-notes')));
		}

		$db = LP_Sticky_Notes_Database::instance();

		// Check if user owns the note
		if (!$db->user_owns_note($note_id)) {
			wp_send_json_error(array('message' => __('You do not have permission to update this note.', 'lp-sticky-notes')));
		}

		$update_data = array();
		if (!empty($content)) {
			$update_data['content'] = $content;
		}
		if (isset($_POST['highlight_text'])) {
			$update_data['highlight_text'] = $highlight_text;
		}
		if (isset($_POST['position'])) {
			$update_data['position'] = $position;
		}

		if ($db->update_note($note_id, $update_data)) {
			$note = $db->get_note($note_id);
			wp_send_json_success(
				array(
					'message' => __('Note updated successfully.', 'lp-sticky-notes'),
					'note' => $note,
				)
			);
		} else {
			wp_send_json_error(array('message' => __('Failed to update note.', 'lp-sticky-notes')));
		}
	}

	/**
	 * Delete a note
	 */
	public function delete_note()
	{
		check_ajax_referer('lp-sticky-notes-nonce', 'nonce');

		if (!is_user_logged_in()) {
			wp_send_json_error(array('message' => __('You must be logged in to delete notes.', 'lp-sticky-notes')));
		}

		$note_id = isset($_POST['note_id']) ? absint($_POST['note_id']) : 0;

		if (!$note_id) {
			wp_send_json_error(array('message' => __('Invalid note ID.', 'lp-sticky-notes')));
		}

		$db = LP_Sticky_Notes_Database::instance();

		// Check if user owns the note
		if (!$db->user_owns_note($note_id)) {
			wp_send_json_error(array('message' => __('You do not have permission to delete this note.', 'lp-sticky-notes')));
		}

		if ($db->delete_note($note_id)) {
			wp_send_json_success(array('message' => __('Note deleted successfully.', 'lp-sticky-notes')));
		} else {
			wp_send_json_error(array('message' => __('Failed to delete note.', 'lp-sticky-notes')));
		}
	}

	/**
	 * Get notes for a lesson
	 */
	public function get_notes()
	{
		check_ajax_referer('lp-sticky-notes-nonce', 'nonce');

		if (!is_user_logged_in()) {
			wp_send_json_error(array('message' => __('You must be logged in to view notes.', 'lp-sticky-notes')));
		}

		$lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
		$course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;

		if (!$lesson_id) {
			wp_send_json_error(array('message' => __('Invalid lesson ID.', 'lp-sticky-notes')));
		}

		// Check if user is enrolled in the course
		if ($course_id && !$this->user_can_access_course($course_id)) {
			wp_send_json_error(array('message' => __('You do not have access to this course.', 'lp-sticky-notes')));
		}

		$db = LP_Sticky_Notes_Database::instance();
		$notes = $db->get_notes_by_lesson($lesson_id);

		wp_send_json_success(array('notes' => $notes));
	}

	/**
	 * Get a single note
	 */
	public function get_single_note()
	{
		check_ajax_referer('lp-sticky-notes-nonce', 'nonce');

		if (!is_user_logged_in()) {
			wp_send_json_error(array('message' => __('You must be logged in to view notes.', 'lp-sticky-notes')));
		}

		$note_id = isset($_POST['note_id']) ? absint($_POST['note_id']) : 0;

		if (!$note_id) {
			wp_send_json_error(array('message' => __('Invalid note ID.', 'lp-sticky-notes')));
		}

		$db = LP_Sticky_Notes_Database::instance();

		// Check if user owns the note
		if (!$db->user_owns_note($note_id)) {
			wp_send_json_error(array('message' => __('You do not have permission to view this note.', 'lp-sticky-notes')));
		}

		$note = $db->get_note($note_id);

		if ($note) {
			wp_send_json_success(array('note' => $note));
		} else {
			wp_send_json_error(array('message' => __('Note not found.', 'lp-sticky-notes')));
		}
	}

	/**
	 * Get all notes for current user
	 */
	public function get_all_notes()
	{
		check_ajax_referer('lp-sticky-notes-nonce', 'nonce');

		if (!is_user_logged_in()) {
			wp_send_json_error(array('message' => __('You must be logged in to view notes.', 'lp-sticky-notes')));
		}

		$db = LP_Sticky_Notes_Database::instance();
		$notes = $db->get_user_notes();

		// Group notes by lesson
		$grouped_notes = array();
		foreach ($notes as $note) {
			$lesson_id = $note->lesson_id;
			if (!isset($grouped_notes[$lesson_id])) {
				$grouped_notes[$lesson_id] = array(
					'lesson_id' => $lesson_id,
					'lesson_title' => $note->lesson_title,
					'lesson_url' => get_permalink($lesson_id),
					'course_title' => $note->course_title,
					'notes' => array(),
				);
			}
			$grouped_notes[$lesson_id]['notes'][] = $note;
		}

		wp_send_json_success(array('grouped_notes' => array_values($grouped_notes)));
	}

	/**
	 * Check if user can access course
	 *
	 * @param int $course_id Course ID
	 * @return bool
	 */
	private function user_can_access_course($course_id)
	{
		$user = learn_press_get_current_user();

		// Admin and instructor can always access
		if (current_user_can('administrator') || current_user_can(LP_TEACHER_ROLE)) {
			return true;
		}

		// Check if user is enrolled
		$course = learn_press_get_course($course_id);
		if (!$course) {
			return false;
		}

		// Check if course requires enrollment
		if ('no' === $course->get_data('required_enroll')) {
			return true;
		}

		// Check if user is enrolled
		return $user->has_enrolled_course($course_id);
	}

	/**
	 * Get instance
	 *
	 * @return LP_Sticky_Notes_Ajax
	 */
	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}