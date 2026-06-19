<?php
/**
 * Plugin Name: Sticky Notes Add-on for LearnPress
 * Plugin URI: https://mamflow.com/product/learnpress-notes-addon-lp-sticky-notes/
 * Description: Allow students to take notes and highlight content for each lesson in LearnPress courses
 * Author: Mamflow
 * Version: 1.0.6
 * Author URI: https://mamflow.com/
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: lp-sticky-notes
 * Domain Path: /languages/
 * Require_LP_Version: 4.2.0
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();

// Define constants
define('LP_STICKY_NOTES_VERSION', '1.0.6');
define('LP_STICKY_NOTES_FILE', __FILE__);
define('LP_STICKY_NOTES_PATH', plugin_dir_path(__FILE__));
define('LP_STICKY_NOTES_URL', plugin_dir_url(__FILE__));
define('LP_STICKY_NOTES_BASENAME', plugin_basename(__FILE__));

// License Product ID on mamflow.com
define('LP_STICKY_NOTES_PRODUCT_ID', 47130);
define('LP_STICKY_NOTES_PRODUCT_URL', 'https://mamflow.com/product/learnpress-notes-addon-lp-sticky-notes/');

/**
 * Get the LearnPress course item URL for a lesson.
 *
 * @param int $lesson_id Lesson ID.
 * @param int $course_id Course ID.
 * @return string
 */
function lp_sticky_notes_get_lesson_url($lesson_id, $course_id = 0)
{
	$lesson_id = absint($lesson_id);
	$course_id = absint($course_id);

	if (!$lesson_id) {
		return '';
	}

	if (!$course_id) {
		$course_id = lp_sticky_notes_get_course_id_by_lesson($lesson_id);
	}

	$lesson_permalink = get_permalink($lesson_id);

	if ($course_id && function_exists('learn_press_get_item_url')) {
		$url = learn_press_get_item_url($lesson_id, $course_id);
		if (!empty($url) && $url !== $lesson_permalink) {
			return $url;
		}
	}

	if ($course_id) {
		$course_url = get_permalink($course_id);
		$lesson_slug = get_post_field('post_name', $lesson_id);

		if ($course_url && $lesson_slug) {
			return trailingslashit($course_url) . 'lessons/' . $lesson_slug . '/';
		}
	}

	return $lesson_permalink;
}

/**
 * Get the first LearnPress course ID containing a lesson.
 *
 * @param int $lesson_id Lesson ID.
 * @return int
 */
function lp_sticky_notes_get_course_id_by_lesson($lesson_id)
{
	global $wpdb;

	$lesson_id = absint($lesson_id);
	if (!$lesson_id || empty($wpdb->learnpress_sections) || empty($wpdb->learnpress_section_items)) {
		return 0;
	}

	$course_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT s.section_course_id
			FROM {$wpdb->learnpress_sections} AS s
			INNER JOIN {$wpdb->learnpress_section_items} AS si ON si.section_id = s.section_id
			WHERE si.item_id = %d
			LIMIT 1",
			$lesson_id
		)
	);

	return $course_id ? absint($course_id) : 0;
}

/**
 * Class LP_Sticky_Notes
 */
class LP_Sticky_Notes
{
	/**
	 * Instance
	 *
	 * @var LP_Sticky_Notes
	 */
	protected static $instance = null;

	/**
	 * License handler instance
	 *
	 * @var Mamflow_License_Handler
	 */
	private $license_handler;

	/**
	 * LP_Sticky_Notes constructor.
	 */
	protected function __construct()
	{
		$this->includes();
		$this->load_license_system();
		$this->hooks();
	}

	/**
	 * Include required files
	 */
	private function includes()
	{
		require_once LP_STICKY_NOTES_PATH . 'inc/license/class-license-handler.php';
		require_once LP_STICKY_NOTES_PATH . 'inc/license/shared-license-page.php';
		require_once LP_STICKY_NOTES_PATH . 'inc/license/admin-license-page.php';

		require_once LP_STICKY_NOTES_PATH . 'inc/class-lp-sticky-notes-database.php';
		require_once LP_STICKY_NOTES_PATH . 'inc/class-lp-sticky-notes-ajax.php';
		require_once LP_STICKY_NOTES_PATH . 'inc/class-lp-sticky-notes-hooks.php';
		require_once LP_STICKY_NOTES_PATH . 'inc/class-lp-sticky-notes-profile.php';
		require_once LP_STICKY_NOTES_PATH . 'inc/class-lp-sticky-notes-admin.php';
		require_once LP_STICKY_NOTES_PATH . 'inc/class-lp-sticky-notes-settings.php';
	}

	/**
	 * Register hooks
	 */
	private function hooks()
	{
		add_action('plugins_loaded', array($this, 'check_learnpress'));
		add_action('init', array($this, 'load_textdomain'));
		add_action('init', array($this, 'maybe_clear_legacy_license_cron'));
		add_action('init', array($this, 'maybe_update_database'));

		// License admin menu
		if (is_admin()) {
			add_action('admin_menu', array($this, 'add_license_menu'), 100);
			add_action('admin_notices', array($this, 'license_notice'));
		}

		// Activation/Deactivation hooks
		register_activation_hook(LP_STICKY_NOTES_FILE, array($this, 'activate'));
		register_deactivation_hook(LP_STICKY_NOTES_FILE, array($this, 'deactivate'));
	}

	/**
	 * Load and initialize license system
	 */
	private function load_license_system()
	{
		// Initialize license handler
		$this->license_handler = new Mamflow_License_Handler([
			'product_id' => LP_STICKY_NOTES_PRODUCT_ID,
			'product_name' => 'Sticky Notes Add-on for LearnPress',
			'api_url' => 'https://mamflow.com/wp-json/mamflow/v1',
			'option_key' => 'lp_sticky_notes_license'
		]);
	}

	/**
	 * Add license menu to LearnPress admin
	 */
	public function add_license_menu()
	{
		if (!class_exists('LearnPress')) {
			return;
		}

		// Register unified Mamflow license page
		mamflow_register_license_menu();

		// Register this plugin's tab
		add_filter('mamflow_license_tabs', array($this, 'register_license_tab'));
	}

	/**
	 * Register Sticky Notes tab in unified license page
	 */
	public function register_license_tab($tabs)
	{
		$tabs['sticky-notes'] = array(
			'title' => esc_html__('Sticky Notes', 'lp-sticky-notes'),
			'callback' => 'lp_sticky_notes_render_license_tab',
			'priority' => 10
		);
		return $tabs;
	}

	/**
	 * Show admin notice if license not activated
	 */
	public function license_notice()
	{
		// Only show if LearnPress is active
		if (!class_exists('LearnPress')) {
			return;
		}

		// Don't show on license page
		if (isset($_GET['page']) && $_GET['page'] === 'mamflow-license') {
			return;
		}

		// Check if license is active
		if (!$this->license_handler->is_feature_enabled()) {
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e('Sticky Notes Add-on for LearnPress:', 'lp-sticky-notes'); ?></strong>
					<?php
					printf(
						esc_html__('Please %sactivate your license%s to unlock all features.', 'lp-sticky-notes'),
						'<a href="' . esc_url(admin_url('admin.php?page=mamflow-license&tab=sticky-notes')) . '">',
						'</a>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get license handler instance
	 *
	 * @return Mamflow_License_Handler
	 */
	public function get_license_handler()
	{
		return $this->license_handler;
	}

	/**
	 * Check if LearnPress is active
	 */
	public function check_learnpress()
	{
		if (!class_exists('LearnPress')) {
			add_action('admin_notices', array($this, 'admin_notice_missing_learnpress'));
			return;
		}

		// Check LearnPress version
		if (version_compare(LEARNPRESS_VERSION, '4.2.0', '<')) {
			add_action('admin_notices', array($this, 'admin_notice_minimum_learnpress_version'));
			return;
		}

		// Initialize plugin
		$this->init();
	}

	/**
	 * Initialize plugin
	 */
	private function init()
	{
		LP_Sticky_Notes_Database::instance();
		LP_Sticky_Notes_Ajax::instance();
		LP_Sticky_Notes_Hooks::instance();
		LP_Sticky_Notes_Profile::instance();
		LP_Sticky_Notes_Admin::instance();
		LP_Sticky_Notes_Settings::instance();
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain()
	{
		load_plugin_textdomain('lp-sticky-notes', false, dirname(LP_STICKY_NOTES_BASENAME) . '/languages');
	}

	/**
	 * Plugin activation
	 */
	public function activate()
	{
		// Check if LearnPress is active
		if (!class_exists('LearnPress')) {
			deactivate_plugins(LP_STICKY_NOTES_BASENAME);
			wp_die(
				sprintf(
					esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'lp-sticky-notes'),
					esc_html__('Sticky Notes Add-on for LearnPress', 'lp-sticky-notes'),
					esc_html__('LearnPress', 'lp-sticky-notes')
				)
			);
		}

		// Check LearnPress version
		if (version_compare(LEARNPRESS_VERSION, '4.2.0', '<')) {
			deactivate_plugins(LP_STICKY_NOTES_BASENAME);
			wp_die(
				sprintf(
					esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'lp-sticky-notes'),
					esc_html__('Sticky Notes Add-on for LearnPress', 'lp-sticky-notes'),
					esc_html__('LearnPress', 'lp-sticky-notes'),
					'4.2.0'
				)
			);
		}

		LP_Sticky_Notes_Database::create_tables();

		$this->clear_license_cron();

		flush_rewrite_rules();
	}

	/**
	 * Update database schema after plugin updates.
	 */
	public function maybe_update_database()
	{
		if (!class_exists('LearnPress')) {
			return;
		}

		LP_Sticky_Notes_Database::maybe_update_schema();
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate()
	{
		$this->clear_license_cron();

		flush_rewrite_rules();
	}

	/**
	 * Clear any license cron events left by older plugin versions.
	 */
	private function clear_license_cron()
	{
		wp_clear_scheduled_hook('lp_sticky_notes_daily_license_check');
	}

	/**
	 * Remove legacy license cron events once after update.
	 */
	public function maybe_clear_legacy_license_cron()
	{
		$cleanup_version = get_option('lp_sticky_notes_license_cron_removed');

		if (LP_STICKY_NOTES_VERSION === $cleanup_version) {
			return;
		}

		$this->clear_license_cron();
		update_option('lp_sticky_notes_license_cron_removed', LP_STICKY_NOTES_VERSION);
	}

	/**
	 * Admin notice for missing LearnPress
	 */
	public function admin_notice_missing_learnpress()
	{
		$message = sprintf(
			esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'lp-sticky-notes'),
			'<strong>' . esc_html__('Sticky Notes Add-on for LearnPress', 'lp-sticky-notes') . '</strong>',
			'<strong>' . esc_html__('LearnPress', 'lp-sticky-notes') . '</strong>'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
	}

	/**
	 * Admin notice for minimum LearnPress version
	 */
	public function admin_notice_minimum_learnpress_version()
	{
		$message = sprintf(
			esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'lp-sticky-notes'),
			'<strong>' . esc_html__('Sticky Notes Add-on for LearnPress', 'lp-sticky-notes') . '</strong>',
			'<strong>' . esc_html__('LearnPress', 'lp-sticky-notes') . '</strong>',
			'4.2.0'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
	}

	/**
	 * Get instance
	 *
	 * @return LP_Sticky_Notes
	 */
	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

// Initialize plugin
LP_Sticky_Notes::instance();
