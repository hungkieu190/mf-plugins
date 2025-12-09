<?php
/**
 * Hooks handler for Sticky Notes
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();

/**
 * Class LP_Sticky_Notes_Hooks
 */
class LP_Sticky_Notes_Hooks
{
	/**
	 * Instance
	 *
	 * @var LP_Sticky_Notes_Hooks
	 */
	protected static $instance = null;

	/**
	 * LP_Sticky_Notes_Hooks constructor.
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
		// Enqueue scripts and styles
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Add sticky notes section to lesson (works for both normal and finished courses)
		add_action('learn-press/after-main-content', array($this, 'render_sticky_notes_section'), 20);

		// Register shortcode
		add_shortcode('lp_sticky_notes', array($this, 'render_shortcode'));

		// Delete notes when lesson is deleted
		add_action('before_delete_post', array($this, 'delete_notes_on_lesson_delete'));

		// Delete notes when course is deleted
		add_action('before_delete_post', array($this, 'delete_notes_on_course_delete'));

		// Add localized script data
		add_action('wp_footer', array($this, 'add_inline_script'));
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts()
	{
		// Check if user is logged in
		if (!is_user_logged_in()) {
			return;
		}

		// Check if sticky notes are enabled
		$enable = LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_enable', 'yes');
		if ($enable !== 'yes') {
			return;
		}

		// Check if we're on a LearnPress lesson page
		$course_item = LP_Global::course_item();
		if (!$course_item || !($course_item instanceof LP_Lesson)) {
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
			array('jquery'),
			LP_STICKY_NOTES_VERSION,
			true
		);

		// Inject custom styles
		$this->inject_custom_styles();

		// Localize script
		wp_localize_script(
			'lp-sticky-notes',
			'lpStickyNotes',
			array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('lp-sticky-notes-nonce'),
				'lessonId' => get_the_ID(),
				'courseId' => $this->lp_get_current_course_id(),
				'highlightEnable' => LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_highlight_enable', 'yes'),
				'i18n' => array(
					'addNote' => __('Add Note', 'lp-sticky-notes'),
					'editNote' => __('Edit Note', 'lp-sticky-notes'),
					'deleteNote' => __('Delete Note', 'lp-sticky-notes'),
					'saveNote' => __('Save Note', 'lp-sticky-notes'),
					'cancel' => __('Cancel', 'lp-sticky-notes'),
					'confirmDelete' => __('Are you sure you want to delete this note?', 'lp-sticky-notes'),
					'noteContent' => __('Note content...', 'lp-sticky-notes'),
					'highlightText' => __('Highlight text and click "Add Note" to create a highlighted note.', 'lp-sticky-notes'),
					'highlightedText' => __('Highlighted', 'lp-sticky-notes'),
					'noNotes' => __('No notes yet. Start taking notes!', 'lp-sticky-notes'),
					'error' => __('An error occurred. Please try again.', 'lp-sticky-notes'),
					'noteAdded' => __('Note added successfully!', 'lp-sticky-notes'),
					'noteUpdated' => __('Note updated successfully!', 'lp-sticky-notes'),
					'noteDeleted' => __('Note deleted successfully!', 'lp-sticky-notes'),
					'textNote' => __('Text Note', 'lp-sticky-notes'),
					'highlightNote' => __('Highlight Note', 'lp-sticky-notes'),
					'createdAt' => __('Created:', 'lp-sticky-notes'),
					'updatedAt' => __('Updated:', 'lp-sticky-notes'),
					'loadingNotes' => __('Loading notes...', 'lp-sticky-notes'),
				),
			)
		);
	}

	private function lp_get_current_course_id()
	{
		if (!function_exists('learn_press_get_course')) {
			return 0;
		}
		$course = learn_press_get_course();
		return $course ? (int) $course->get_id() : 0;
	}

	/**
	 * Render sticky notes section
	 */
	public function render_sticky_notes_section()
	{
		// Only render once per page load
		static $rendered = false;
		if ($rendered) {
			return;
		}

		if (!is_user_logged_in()) {
			return;
		}

		// Check if sticky notes are enabled
		$enable = LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_enable', 'yes');
		if ($enable !== 'yes') {
			return;
		}

		// Check if we're on a LearnPress lesson page
		$course_item = LP_Global::course_item();
		if (!$course_item || !($course_item instanceof LP_Lesson)) {
			return;
		}

		$course_id = $this->lp_get_current_course_id();

		if (!$course_id) {
			return;
		}

		// Check if user has access to the course
		$user = learn_press_get_current_user();
		if (!$user) {
			return;
		}

		// Allow access for enrolled or finished courses
		if (!$user->has_enrolled_course($course_id)) {
			// If not enrolled, check if finished
			$course_data = $user->get_course_data($course_id);
			if (!$course_data || $course_data->get_status() !== 'finished') {
				return;
			}
		}

		// Get existing notes
		$lesson_id = get_the_ID();
		$db = LP_Sticky_Notes_Database::instance();
		$notes = $db->get_notes_by_lesson($lesson_id);

		$rendered = true;
		include LP_STICKY_NOTES_PATH . 'templates/sticky-notes-section.php';
	}

	/**
	 * Render shortcode [lp_sticky_notes]
	 *
	 * @param array $atts Shortcode attributes
	 * @return string
	 */
	public function render_shortcode($atts)
	{
		$atts = shortcode_atts(
			array(
				'user_id' => get_current_user_id(),
				'course_id' => 0,
				'lesson_id' => 0,
				'limit' => 10,
				'show_course' => 'yes',
				'show_lesson' => 'yes',
			),
			$atts,
			'lp_sticky_notes'
		);

		// Only admin/instructor can view other users' notes
		if ($atts['user_id'] != get_current_user_id()) {
			if (!current_user_can('manage_options') && !current_user_can(LP_TEACHER_ROLE)) {
				return '<p>' . esc_html__('You do not have permission to view this content.', 'lp-sticky-notes') . '</p>';
			}
		}

		// Get notes
		$db = LP_Sticky_Notes_Database::instance();
		$notes = $db->get_notes_for_shortcode(
			absint($atts['user_id']),
			absint($atts['course_id']),
			absint($atts['lesson_id']),
			absint($atts['limit'])
		);

		// Start output buffering
		ob_start();
		include LP_STICKY_NOTES_PATH . 'templates/shortcode-notes-list.php';
		return ob_get_clean();
	}


	private function mf_get_current_lesson_id()
	{
		if (!function_exists('learn_press_get_course')) {
			return 0;
		}

		$item = class_exists('LP_Global') ? LP_Global::course_item() : null;
		if ($item && $item instanceof LP_Course_Item) {
			if (defined('LP_LESSON_CPT') && $item->get_item_type() === LP_LESSON_CPT) {
				return (int) $item->get_id();
			}
		}

		$course = learn_press_get_course();
		if ($course) {
			$current_item = $course->get_current_item();
			if ($current_item && defined('LP_LESSON_CPT') && $current_item->get_item_type() === LP_LESSON_CPT) {
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
	private function get_course_id_from_lesson($lesson_id)
	{
		$lesson_id = absint($lesson_id);
		if ($lesson_id <= 0) {
			return 0;
		}


		// Fallback: direct SQL on LP tables
		global $wpdb;

		// Ensure LP custom tables are available on this site
		if (empty($wpdb->learnpress_sections) || empty($wpdb->learnpress_section_items)) {
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

		$course_id = (int) $wpdb->get_var($sql);

		return $course_id ?: 0;
	}

	/**
	 * Delete notes when lesson is deleted
	 *
	 * @param int $post_id Post ID
	 */
	public function delete_notes_on_lesson_delete($post_id)
	{
		if (get_post_type($post_id) !== LP_LESSON_CPT) {
			return;
		}

		$db = LP_Sticky_Notes_Database::instance();
		$db->delete_notes_by_lesson($post_id);
	}

	/**
	 * Delete notes when course is deleted
	 *
	 * @param int $post_id Post ID
	 */
	public function delete_notes_on_course_delete($post_id)
	{
		if (get_post_type($post_id) !== LP_COURSE_CPT) {
			return;
		}

		$db = LP_Sticky_Notes_Database::instance();
		$db->delete_notes_by_course($post_id);
	}

	/**
	 * Add inline script for highlight functionality
	 */
	public function add_inline_script()
	{
		// Check if we're on a LearnPress lesson page
		$course_item = LP_Global::course_item();
		if (!$course_item || !($course_item instanceof LP_Lesson) || !is_user_logged_in()) {
			return;
		}
		?>
		<script type="text/javascript">		jQuery(document).ready(function ($) {			// Debug: Check if highlight is working			console.log('LP Sticky Notes: Inline script loaded');		});
		</script>
		<?php
	}

	/**
	 * Inject custom styles based on settings
	 */
	public function inject_custom_styles()
	{
		$primary_color = LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_primary_color', '#fbbf24');
		$text_color = LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_text_color', '#92400e');
		$button_size = LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_button_size', '50');
		$sidebar_width = LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_sidebar_width', '380');
		$sidebar_position = LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_sidebar_position', 'right');
		$button_position = LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_button_position', 'middle-right');
		$custom_css = LP_Sticky_Notes_Settings::get_setting('lp_sticky_notes_custom_css', '');

		$primary_dark = $primary_color;

		$css_vars = array(
			'--lp-sn-primary-color' => $primary_color,
			'--lp-sn-primary-dark' => $primary_dark,
			'--lp-sn-text-color' => $text_color,
			'--lp-sn-btn-size' => $button_size . 'px',
			'--lp-sn-sidebar-width' => $sidebar_width . 'px',
		);

		// Sidebar Position
		if ($sidebar_position === 'left') {
			$css_vars['--lp-sn-sidebar-right'] = 'auto';
			$css_vars['--lp-sn-sidebar-left'] = '-' . $sidebar_width . 'px';
			$css_vars['--lp-sn-sidebar-open-right'] = 'auto';
			$css_vars['--lp-sn-sidebar-open-left'] = '0';
			$css_vars['--lp-sn-sidebar-border-right'] = '2px solid ' . $primary_color;
			// We need to override the border-left from CSS
			$custom_css .= ' .lp-sticky-notes-sidebar { border-left: none !important; }';
		} else {
			// Default Right
			$css_vars['--lp-sn-sidebar-right'] = '-' . $sidebar_width . 'px';
			$css_vars['--lp-sn-sidebar-left'] = 'auto';
			$css_vars['--lp-sn-sidebar-open-right'] = '0';
			$css_vars['--lp-sn-sidebar-open-left'] = 'auto';
			$css_vars['--lp-sn-sidebar-border-right'] = 'none';
		}

		// Button Position
		$btn_pos_map = array(
			'top-left' => array('top' => '20px', 'bottom' => 'auto', 'left' => '20px', 'right' => 'auto', 'transform' => 'none', 'hover' => 'scale(1.1)'),
			'top-right' => array('top' => '20px', 'bottom' => 'auto', 'left' => 'auto', 'right' => '20px', 'transform' => 'none', 'hover' => 'scale(1.1)'),
			'middle-left' => array('top' => '50%', 'bottom' => 'auto', 'left' => '20px', 'right' => 'auto', 'transform' => 'translateY(-50%)', 'hover' => 'translateY(-50%) scale(1.1)'),
			'middle-right' => array('top' => '50%', 'bottom' => 'auto', 'left' => 'auto', 'right' => '20px', 'transform' => 'translateY(-50%)', 'hover' => 'translateY(-50%) scale(1.1)'),
			'bottom-left' => array('top' => 'auto', 'bottom' => '20px', 'left' => '20px', 'right' => 'auto', 'transform' => 'none', 'hover' => 'scale(1.1)'),
			'bottom-right' => array('top' => 'auto', 'bottom' => '20px', 'left' => 'auto', 'right' => '20px', 'transform' => 'none', 'hover' => 'scale(1.1)'),
		);

		if (isset($btn_pos_map[$button_position])) {
			$pos = $btn_pos_map[$button_position];
			$css_vars['--lp-sn-btn-top'] = $pos['top'];
			$css_vars['--lp-sn-btn-bottom'] = $pos['bottom'];
			$css_vars['--lp-sn-btn-left'] = $pos['left'];
			$css_vars['--lp-sn-btn-right'] = $pos['right'];
			$css_vars['--lp-sn-btn-transform'] = $pos['transform'];
			$css_vars['--lp-sn-btn-transform-hover'] = $pos['hover'];
		}

		// Generate CSS
		$style = ':root {';
		foreach ($css_vars as $key => $value) {
			$style .= sprintf('%s: %s;', $key, $value);
		}
		$style .= '}';

		if (!empty($custom_css)) {
			$style .= $custom_css;
		}

		wp_add_inline_style('lp-sticky-notes', $style);
	}

	/**
	 * Get instance
	 *
	 * @return LP_Sticky_Notes_Hooks
	 */
	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}