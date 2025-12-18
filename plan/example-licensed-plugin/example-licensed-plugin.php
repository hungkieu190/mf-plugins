<?php
/**
 * Plugin Name: Example Licensed Plugin
 * Plugin URI: https://mamflow.com
 * Description: Example plugin demonstrating client-side license integration with Mamflow License System
 * Version: 1.0.0
 * Author: Mamflow
 * Author URI: https://mamflow.com
 * Text Domain: example-licensed-plugin
 * 
 * @package Example_Licensed_Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EXAMPLE_LICENSED_PLUGIN_VERSION', '1.0.0');
define('EXAMPLE_LICENSED_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXAMPLE_LICENSED_PLUGIN_URL', plugin_dir_url(__FILE__));

// Product ID on mamflow.com (this would be your actual WooCommerce product ID)
define('EXAMPLE_LICENSED_PLUGIN_PRODUCT_ID', 123);

/**
 * Main plugin class
 */
class Example_Licensed_Plugin {
    
    private static $instance = null;
    private $license_handler;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_license_handler();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once EXAMPLE_LICENSED_PLUGIN_DIR . 'includes/class-license-handler.php';
        require_once EXAMPLE_LICENSED_PLUGIN_DIR . 'includes/admin-license-page.php';
        require_once EXAMPLE_LICENSED_PLUGIN_DIR . 'includes/cron-scheduler.php';
    }
    
    /**
     * Initialize license handler
     */
    private function init_license_handler() {
        $this->license_handler = new Mamflow_License_Handler([
            'product_id' => EXAMPLE_LICENSED_PLUGIN_PRODUCT_ID,
            'product_name' => 'Example Licensed Plugin',
            'api_url' => 'https://mamflow.com/wp-json/mamflow/v1',
            'option_key' => 'example_licensed_plugin_license'
        ]);
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin menu
        if (is_admin()) {
            add_action('admin_menu', [$this, 'add_admin_menu']);
        }
        
        // Plugin activation/deactivation
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Example: Add admin notice if license not activated
        add_action('admin_notices', [$this, 'license_notice']);
        
        // Example premium feature
        add_action('wp_footer', [$this, 'premium_feature']);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Schedule daily license check
        Example_Licensed_Plugin_Cron::schedule_license_check();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled cron
        Example_Licensed_Plugin_Cron::clear_license_check();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'License Settings',
            'Example Plugin License',
            'manage_options',
            'example-plugin-license',
            'example_licensed_plugin_render_license_page'
        );
    }
    
    /**
     * Show admin notice if license not activated
     */
    public function license_notice() {
        if (!$this->license_handler->is_feature_enabled()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong>Example Licensed Plugin:</strong> 
                    Please <a href="<?php echo admin_url('options-general.php?page=example-plugin-license'); ?>">activate your license</a> to unlock premium features.
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Example premium feature (only shows if license is valid)
     */
    public function premium_feature() {
        // Check if feature is enabled (license valid)
        if (!$this->license_handler->is_feature_enabled()) {
            return; // Feature locked
        }
        
        // Premium feature code here
        echo '<!-- Example Premium Feature Active -->';
        echo '<style>.premium-badge { background: gold; padding: 5px 10px; position: fixed; bottom: 10px; right: 10px; border-radius: 5px; }</style>';
        echo '<div class="premium-badge">Premium Active</div>';
    }
    
    /**
     * Get license handler instance
     */
    public function get_license_handler() {
        return $this->license_handler;
    }
}

// Initialize plugin
Example_Licensed_Plugin::get_instance();
