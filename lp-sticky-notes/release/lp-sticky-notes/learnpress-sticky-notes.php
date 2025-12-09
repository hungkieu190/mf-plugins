<?php
/**
 * Plugin Name: Sticky Notes Add-on for LearnPress
 * Plugin URI: https://mamflow.com/product/learnpress-notes-addon-lp-sticky-notes/
 * Description: Allow students to take notes and highlight content for each lesson in LearnPress courses
 * Author: Mamflow
 * Version: 1.0.3
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
define('LP_STICKY_NOTES_VERSION', '1.0.3');
define('LP_STICKY_NOTES_FILE', __FILE__);
define('LP_STICKY_NOTES_PATH', plugin_dir_path(__FILE__));
define('LP_STICKY_NOTES_URL', plugin_dir_url(__FILE__));
define('LP_STICKY_NOTES_BASENAME', plugin_basename(__FILE__));

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
	 * LP_Sticky_Notes constructor.
	 */
	protected function __construct()
	{
		$this->includes();
		$this->hooks();
	}

	/**
	 * Include required files
	 */
	private function includes()
	{
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

		// Activation/Deactivation hooks
		register_activation_hook(LP_STICKY_NOTES_FILE, array($this, 'activate'));
		register_deactivation_hook(LP_STICKY_NOTES_FILE, array($this, 'deactivate'));
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
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate()
	{
		flush_rewrite_rules();
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