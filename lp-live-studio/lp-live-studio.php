<?php
/**
 * Plugin Name: LearnPress - Live Studio
 * Plugin URI: https://mamflow.com/lp-live-studio
 * Description: Add live streaming sessions to LearnPress courses with Zoom, Google Meet, and Agora integration. Includes tutor rating system.
 * Author: Mamflow
 * Version: 1.0.0
 * Author URI: https://mamflow.com
 * Tags: learnpress, live, zoom, google meet, agora, livestream
 * Text Domain: lp-live-studio
 * Domain Path: /languages/
 * Require_LP_Version: 4.2.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
const MF_LLS_FILE = __FILE__;
const MF_LLS_PATH = __DIR__;
define('MF_LLS_URL', plugin_dir_url(__FILE__));

// Product ID on mamflow.com (for license system)
define('MF_LLS_PRODUCT_ID', 47326);

/**
 * Class MF_LLS_Preload
 *
 * Preload checks before loading the addon
 */
class MF_LLS_Preload
{
    /**
     * @var array Plugin info from header
     */
    public static $addon_info = array();

    /**
     * @var MF_LLS_Addon Main addon instance
     */
    public static $addon;

    /**
     * Singleton instance
     *
     * @return MF_LLS_Preload
     */
    public static function instance()
    {
        static $instance;
        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Constructor - Run preload checks
     */
    protected function __construct()
    {
        $can_load = true;

        // Set basename
        define('MF_LLS_BASENAME', plugin_basename(MF_LLS_FILE));

        // Get plugin info from header
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        self::$addon_info = get_file_data(
            MF_LLS_FILE,
            array(
                'Name' => 'Plugin Name',
                'Require_LP_Version' => 'Require_LP_Version',
                'Version' => 'Version',
            )
        );

        // Define version constants
        define('MF_LLS_VERSION', self::$addon_info['Version']);
        define('MF_LLS_REQUIRE_LP_VERSION', self::$addon_info['Require_LP_Version']);

        // Check LearnPress activated
        if (!is_plugin_active('learnpress/learnpress.php')) {
            $can_load = false;
        } elseif (version_compare(MF_LLS_REQUIRE_LP_VERSION, get_option('learnpress_version', '3.0.0'), '>')) {
            $can_load = false;
        }

        if (!$can_load) {
            add_action('admin_notices', array($this, 'show_note_errors_require_lp'));
            deactivate_plugins(MF_LLS_BASENAME);

            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }

            return;
        }

        // Register activation/deactivation hooks
        register_activation_hook(MF_LLS_FILE, array($this, 'on_activation'));
        register_deactivation_hook(MF_LLS_FILE, array($this, 'on_deactivation'));

        // Load addon when LearnPress is ready
        add_action('learn-press/ready', array($this, 'load'));
    }

    /**
     * Load the main addon
     */
    public function load()
    {
        require_once MF_LLS_PATH . '/includes/class-mf-lls-addon.php';
        self::$addon = MF_LLS_Addon::instance();
    }

    /**
     * Activation hook
     */
    public function on_activation()
    {
        require_once MF_LLS_PATH . '/includes/class-mf-lls-activator.php';
        MF_LLS_Activator::activate();
    }

    /**
     * Deactivation hook
     */
    public function on_deactivation()
    {
        require_once MF_LLS_PATH . '/includes/class-mf-lls-deactivator.php';
        MF_LLS_Deactivator::deactivate();
    }

    /**
     * Show admin notice when LearnPress requirement not met
     */
    public function show_note_errors_require_lp()
    {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                echo wp_kses_post(
                    sprintf(
                        'Please activate <strong>LearnPress version %s or later</strong> before activating <strong>%s</strong>',
                        MF_LLS_REQUIRE_LP_VERSION,
                        self::$addon_info['Name']
                    )
                );
                ?>
            </p>
        </div>
        <?php
    }
}

MF_LLS_Preload::instance();
