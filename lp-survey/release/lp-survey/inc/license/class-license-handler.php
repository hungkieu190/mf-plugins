<?php
/**
 * Mamflow License Handler
 * 
 * Reusable class for handling license activation, validation, and feature gating.
 * Drop this file into any commercial plugin to integrate with Mamflow License System.
 * 
 * @package LP_Survey_License_Handler
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class LP_Survey_License_Handler {
    
    private $product_id;
    private $product_name;
    private $api_url;
    private $option_key;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array with keys:
     *                      - product_id: WooCommerce product ID on mamflow.com
     *                      - product_name: Human-readable product name
     *                      - api_url: Base API URL (default: https://mamflow.com/wp-json/mamflow/v1)
     *                      - option_key: Unique option key for storing license data
     */
    public function __construct($config) {
        $this->product_id = $config['product_id'];
        $this->product_name = $config['product_name'];
        $this->api_url = isset($config['api_url']) ? $config['api_url'] : 'https://mamflow.com/wp-json/mamflow/v1';
        $this->option_key = $config['option_key'];
    }
    
    /**
     * Activate license
     * 
     * @param string $license_key License key from user
     * @return array Response with success status and message
     */
    public function activate_license($license_key) {
        // Sanitize key
        $key = sanitize_text_field($license_key);
        
        // Get current site domain
        $domain = $this->get_site_domain();
        
        // Call API
        $response = wp_remote_post($this->api_url . '/activate', [
            'body' => wp_json_encode([
                'license_key' => $key,
                'domain' => $domain,
                'product_id' => $this->product_id
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 15
        ]);
        
        // Handle errors
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $response->get_error_message()
            ];
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        // Handle HTTP errors
        if ($status_code !== 200) {
            return [
                'success' => false,
                'message' => isset($body['message']) ? $body['message'] : 'Activation failed (HTTP ' . $status_code . ')'
            ];
        }
        
        // Store license data locally
        if (isset($body['success']) && $body['success']) {
            update_option($this->option_key, [
                'license_key' => $key,
                'status' => 'active',
                'domain' => $domain,
                'expires_at' => isset($body['expires_at']) ? $body['expires_at'] : null,
                'last_check' => current_time('timestamp'),
                'activation_date' => current_time('mysql')
            ]);
        }
        
        return $body;
    }
    
    /**
     * Deactivate license
     * 
     * @return array Response with success status and message
     */
    public function deactivate_license() {
        $license_data = get_option($this->option_key);
        
        if (!$license_data || empty($license_data['license_key'])) {
            return [
                'success' => false,
                'message' => 'No license to deactivate'
            ];
        }
        
        $response = wp_remote_post($this->api_url . '/deactivate', [
            'body' => wp_json_encode([
                'license_key' => $license_data['license_key'],
                'domain' => $license_data['domain']
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 15
        ]);
        
        // Clear local data regardless of API response
        delete_option($this->option_key);
        
        if (is_wp_error($response)) {
            return [
                'success' => true,
                'message' => 'License removed locally (server communication failed)'
            ];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }
    
    /**
     * Check license status (called by cron)
     * 
     * @return bool True if license is valid, false otherwise
     */
    public function check_license_status() {
        $license_data = get_option($this->option_key);
        
        if (!$license_data || empty($license_data['license_key'])) {
            return false;
        }
        
        $response = wp_remote_post($this->api_url . '/check', [
            'body' => wp_json_encode([
                'license_key' => $license_data['license_key'],
                'domain' => $license_data['domain']
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 15
        ]);
        
        // On connection error, keep current status (don't disable)
        if (is_wp_error($response)) {
            return $license_data['status'] === 'active';
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        // Update local cache
        $license_data['status'] = (isset($body['valid']) && $body['valid']) ? 'active' : 'invalid';
        $license_data['last_check'] = current_time('timestamp');
        
        if (isset($body['expires_at'])) {
            $license_data['expires_at'] = $body['expires_at'];
        }
        
        update_option($this->option_key, $license_data);
        
        return isset($body['valid']) && $body['valid'];
    }
    
    /**
     * Check if premium features are enabled
     * 
     * This is the main method to gate features in your plugin.
     * 
     * @return bool True if license is valid and active
     */
    public function is_feature_enabled() {
        $license_data = get_option($this->option_key);
        
        if (!$license_data) {
            return false;
        }
        
        // Check if status is active
        if (!isset($license_data['status']) || $license_data['status'] !== 'active') {
            return false;
        }
        
        // Fallback: If last check > 72 hours, force re-check
        $last_check = isset($license_data['last_check']) ? $license_data['last_check'] : 0;
        $hours_since_check = (current_time('timestamp') - $last_check) / HOUR_IN_SECONDS;
        
        if ($hours_since_check > 72) {
            // Re-check license status
            return $this->check_license_status();
        }
        
        return true;
    }
    
    /**
     * Get license data
     * 
     * @return array|false License data or false if not set
     */
    public function get_license_data() {
        return get_option($this->option_key, false);
    }
    
    /**
     * Get current site domain
     * 
     * @return string Cleaned domain name
     */
    private function get_site_domain() {
        $url = home_url();
        
        // Remove protocol
        $domain = preg_replace('#^https?://#i', '', $url);
        
        // Remove www
        $domain = preg_replace('#^www\.#i', '', $domain);
        
        // Remove trailing slash and path
        $domain = strtok($domain, '/');
        
        return strtolower(trim($domain));
    }
    
    /**
     * Get days until license expires
     * 
     * @return int|null Days until expiration, null if lifetime
     */
    public function get_days_until_expiration() {
        $license_data = $this->get_license_data();
        
        if (!$license_data || empty($license_data['expires_at'])) {
            return null; // Lifetime license
        }
        
        $expires_timestamp = strtotime($license_data['expires_at']);
        $current_timestamp = current_time('timestamp');
        
        $days = floor(($expires_timestamp - $current_timestamp) / DAY_IN_SECONDS);
        
        return max(0, $days);
    }
    
    /**
     * Check if license is expired
     * 
     * @return bool
     */
    public function is_expired() {
        $license_data = $this->get_license_data();
        
        if (!$license_data || empty($license_data['expires_at'])) {
            return false; // Lifetime license doesn't expire
        }
        
        return strtotime($license_data['expires_at']) < current_time('timestamp');
    }
}
