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

// License Product ID on mamflow.com
define('LP_STICKY_NOTES_PRODUCT_ID', 47130);

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
		require_once LP_STICKY_NOTES_PATH . 'inc/license/cron-scheduler.php';

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

		// Schedule license check
		LP_Sticky_Notes_Cron::schedule_license_check();

		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate()
	{
		// Clear license cron
		LP_Sticky_Notes_Cron::clear_license_check();

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