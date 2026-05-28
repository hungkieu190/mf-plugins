<?php
/**
 * Database handler for Sticky Notes
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();

/**
 * Class LP_Sticky_Notes_Database
 */
class LP_Sticky_Notes_Database
{
	/**
	 * Instance
	 *
	 * @var LP_Sticky_Notes_Database
	 */
	protected static $instance = null;

	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $table_name = 'learnpress_sticky_notes';

	/**
	 * LP_Sticky_Notes_Database constructor.
	 */
	protected function __construct()
	{
		// Constructor
	}

	/**
	 * Create database tables
	 */
	public static function create_tables()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . self::$table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			course_id bigint(20) unsigned NOT NULL,
			lesson_id bigint(20) unsigned NOT NULL,
			note_type varchar(20) NOT NULL DEFAULT 'text',
			highlight_text text DEFAULT NULL,
			position longtext DEFAULT NULL,
			content text NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY course_id (course_id),
			KEY lesson_id (lesson_id),
			KEY note_type (note_type)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	}

	/**
	 * Get table name with prefix
	 *
	 * @return string
	 */
	public static function get_table_name()
	{
		global $wpdb;
		return $wpdb->prefix . self::$table_name;
	}

	/**
	 * Insert a new note
	 *
	 * @param array $data Note data
	 * @return int|false Note ID or false on failure
	 */
	public function insert_note($data)
	{
		global $wpdb;

		$defaults = array(
			'user_id' => get_current_user_id(),
			'course_id' => 0,
			'lesson_id' => 0,
			'note_type' => 'text',
			'highlight_text' => null,
			'position' => null,
			'content' => '',
		);

		$data = wp_parse_args($data, $defaults);

		// Sanitize data
		$data['user_id'] = absint($data['user_id']);
		$data['course_id'] = absint($data['course_id']);
		$data['lesson_id'] = absint($data['lesson_id']);
		$data['note_type'] = sanitize_text_field($data['note_type']);
		$data['highlight_text'] = !empty($data['highlight_text']) ? wp_kses_post($data['highlight_text']) : null;
		$data['position'] = !empty($data['position']) ? wp_json_encode($data['position']) : null;
		$data['content'] = wp_kses_post($data['content']);

		$result = $wpdb->insert(
			self::get_table_name(),
			$data,
			array('%d', '%d', '%d', '%s', '%s', '%s', '%s')
		);

		if ($result) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Update a note
	 *
	 * @param int   $note_id Note ID
	 * @param array $data    Note data
	 * @return bool
	 */
	public function update_note($note_id, $data)
	{
		global $wpdb;

		$note_id = absint($note_id);
		if (!$note_id) {
			return false;
		}

		// Sanitize data
		$update_data = array();
		if (isset($data['content'])) {
			$update_data['content'] = wp_kses_post($data['content']);
		}
		if (isset($data['highlight_text'])) {
			$update_data['highlight_text'] = !empty($data['highlight_text']) ? wp_kses_post($data['highlight_text']) : null;
		}
		if (isset($data['position'])) {
			$update_data['position'] = !empty($data['position']) ? wp_json_encode($data['position']) : null;
		}

		if (empty($update_data)) {
			return false;
		}

		$result = $wpdb->update(
			self::get_table_name(),
			$update_data,
			array('id' => $note_id),
			array('%s'),
			array('%d')
		);

		return $result !== false;
	}

	/**
	 * Delete a note
	 *
	 * @param int $note_id Note ID
	 * @return bool
	 */
	public function delete_note($note_id)
	{
		global $wpdb;

		$note_id = absint($note_id);
		if (!$note_id) {
			return false;
		}

		$result = $wpdb->delete(
			self::get_table_name(),
			array('id' => $note_id),
			array('%d')
		);

		return $result !== false;
	}

	/**
	 * Get notes by lesson
	 *
	 * @param int $lesson_id Lesson ID
	 * @param int $user_id   User ID (optional, defaults to current user)
	 * @return array
	 */
	public function get_notes_by_lesson($lesson_id, $user_id = 0)
	{
		global $wpdb;

		$lesson_id = absint($lesson_id);
		$user_id = $user_id ? absint($user_id) : get_current_user_id();

		if (!$lesson_id || !$user_id) {
			return array();
		}

		$query = $wpdb->prepare(
			"SELECT * FROM " . self::get_table_name() . "
			WHERE lesson_id = %d AND user_id = %d
			ORDER BY created_at ASC",
			$lesson_id,
			$user_id
		);

		$results = $wpdb->get_results($query);

		// Decode JSON position field
		if ($results) {
			foreach ($results as $note) {
				if (!empty($note->position)) {
					$note->position = json_decode($note->position);
				}
			}
		}

		return $results ? $results : array();
	}

	/**
	 * Get notes by course
	 *
	 * @param int $course_id Course ID
	 * @param int $user_id   User ID (optional, defaults to current user)
	 * @return array
	 */
	public function get_notes_by_course($course_id, $user_id = 0)
	{
		global $wpdb;

		$course_id = absint($course_id);
		$user_id = $user_id ? absint($user_id) : get_current_user_id();

		if (!$course_id || !$user_id) {
			return array();
		}

		$query = $wpdb->prepare(
			"SELECT n.*, p.post_title as lesson_title
			FROM " . self::get_table_name() . " n
			LEFT JOIN {$wpdb->posts} p ON n.lesson_id = p.ID
			WHERE n.course_id = %d AND n.user_id = %d
			ORDER BY n.created_at DESC",
			$course_id,
			$user_id
		);

		$results = $wpdb->get_results($query);

		// Decode JSON position field
		if ($results) {
			foreach ($results as $note) {
				if (!empty($note->position)) {
					$note->position = json_decode($note->position);
				}
			}
		}

		return $results ? $results : array();
	}

	/**
	 * Get all notes for a user
	 *
	 * @param int    $user_id   User ID (optional, defaults to current user)
	 * @param string $course_id Course ID filter (optional)
	 * @return array
	 */
	public function get_user_notes($user_id = 0, $course_id = '')
	{
		global $wpdb;

		$user_id = $user_id ? absint($user_id) : get_current_user_id();

		if (!$user_id) {
			return array();
		}

		$where = $wpdb->prepare("n.user_id = %d", $user_id);

		if ($course_id) {
			$where .= $wpdb->prepare(" AND n.course_id = %d", absint($course_id));
		}

		$query = "SELECT n.*, 
			p.post_title as lesson_title,
			c.post_title as course_title
			FROM " . self::get_table_name() . " n
			LEFT JOIN {$wpdb->posts} p ON n.lesson_id = p.ID
			LEFT JOIN {$wpdb->posts} c ON n.course_id = c.ID
			WHERE {$where}
			ORDER BY n.created_at DESC";

		$results = $wpdb->get_results($query);

		// Decode JSON position field
		if ($results) {
			foreach ($results as $note) {
				if (!empty($note->position)) {
					$note->position = json_decode($note->position);
				}
			}
		}

		return $results ? $results : array();
	}

	/**
	 * Delete notes by lesson
	 *
	 * @param int $lesson_id Lesson ID
	 * @return bool
	 */
	public function delete_notes_by_lesson($lesson_id)
	{
		global $wpdb;

		$lesson_id = absint($lesson_id);
		if (!$lesson_id) {
			return false;
		}

		$result = $wpdb->delete(
			self::get_table_name(),
			array('lesson_id' => $lesson_id),
			array('%d')
		);

		return $result !== false;
	}

	/**
	 * Delete notes by course
	 *
	 * @param int $course_id Course ID
	 * @return bool
	 */
	public function delete_notes_by_course($course_id)
	{
		global $wpdb;

		$course_id = absint($course_id);
		if (!$course_id) {
			return false;
		}

		$result = $wpdb->delete(
			self::get_table_name(),
			array('course_id' => $course_id),
			array('%d')
		);

		return $result !== false;
	}

	/**
	 * Get single note
	 *
	 * @param int $note_id Note ID
	 * @return object|null
	 */
	public function get_note($note_id)
	{
		global $wpdb;

		$note_id = absint($note_id);
		if (!$note_id) {
			return null;
		}

		$query = $wpdb->prepare(
			"SELECT * FROM " . self::get_table_name() . " WHERE id = %d",
			$note_id
		);

		$note = $wpdb->get_row($query);

		if ($note && !empty($note->position)) {
			$note->position = json_decode($note->position);
		}

		return $note;
	}

	/**
	 * Get notes for shortcode display
	 *
	 * @param int $user_id   User ID
	 * @param int $course_id Course ID filter
	 * @param int $lesson_id Lesson ID filter
	 * @param int $limit     Number of notes to return
	 * @return array
	 */
	public function get_notes_for_shortcode($user_id, $course_id = 0, $lesson_id = 0, $limit = 10)
	{
		global $wpdb;

		$user_id = absint($user_id);
		if (!$user_id) {
			return array();
		}

		$where = array();
		$params = array();

		$where[] = 'n.user_id = %d';
		$params[] = $user_id;

		if ($course_id) {
			$where[] = 'n.course_id = %d';
			$params[] = $course_id;
		}

		if ($lesson_id) {
			$where[] = 'n.lesson_id = %d';
			$params[] = $lesson_id;
		}

		$where_clause = implode(' AND ', $where);

		$sql = "SELECT n.*, 
			p.post_title as lesson_title,
			c.post_title as course_title
		FROM " . self::get_table_name() . " n
		LEFT JOIN {$wpdb->posts} p ON n.lesson_id = p.ID
		LEFT JOIN {$wpdb->posts} c ON n.course_id = c.ID
		WHERE {$where_clause}
		ORDER BY n.created_at DESC
		LIMIT %d";

		$params[] = absint($limit);

		$query = $wpdb->prepare($sql, $params);
		$results = $wpdb->get_results($query);

		// Decode JSON position field
		if ($results) {
			foreach ($results as $note) {
				if (!empty($note->position)) {
					$note->position = json_decode($note->position);
				}
			}
		}

		return $results ? $results : array();
	}

	/**
	 * Check if user owns note
	 *
	 * @param int $note_id Note ID
	 * @param int $user_id User ID (optional, defaults to current user)
	 * @return bool
	 */
	public function user_owns_note($note_id, $user_id = 0)
	{
		$note = $this->get_note($note_id);
		$user_id = $user_id ? absint($user_id) : get_current_user_id();

		return $note && absint($note->user_id) === $user_id;
	}

	/**
	 * Get instance
	 *
	 * @return LP_Sticky_Notes_Database
	 */
	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}