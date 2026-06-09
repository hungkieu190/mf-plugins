<?php
/**
 * Mamflow License Handler
 * 
 * Reusable class for handling license activation, validation, and feature gating.
 * Drop this file into any commercial plugin to integrate with Mamflow License System.
 * 
 * @package Mamflow_License_Handler
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Mamflow_License_Handler {
    
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
                'expires_at' => isset($body['expires_at']) ? $this->normalize_expiration($body['expires_at']) : null,
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
     * Check license status manually.
     *
     * @return bool True if license is valid, false otherwise.
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

        if (is_wp_error($response)) {
            return $license_data['status'] === 'active';
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (200 !== $status_code || !is_array($body)) {
            return $license_data['status'] === 'active';
        }

        $is_valid = $this->is_valid_api_response($body);

        if (null === $is_valid) {
            return $license_data['status'] === 'active';
        }

        $license_data['status'] = $is_valid ? 'active' : 'invalid';
        $license_data['last_check'] = current_time('timestamp');

        if (isset($body['expires_at'])) {
            $license_data['expires_at'] = $this->normalize_expiration($body['expires_at']);
        }

        update_option($this->option_key, $license_data);

        return $is_valid;
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
     * Normalize lifetime/no-expiration values from the API.
     *
     * @param mixed $expires_at Raw expiration value.
     * @return string|null
     */
    private function normalize_expiration($expires_at) {
        if (empty($expires_at)) {
            return null;
        }

        $value = strtolower(trim((string) $expires_at));

        if (in_array($value, ['lifetime', 'never', 'none', 'null', '0000-00-00', '0000-00-00 00:00:00'], true)) {
            return null;
        }

        return (string) $expires_at;
    }

    /**
     * Interpret API validation responses while remaining compatible with older response shapes.
     *
     * @param array $body Decoded API response.
     * @return bool|null True valid, false invalid, null inconclusive.
     */
    private function is_valid_api_response($body) {
        if (isset($body['valid'])) {
            return (bool) $body['valid'];
        }

        if (isset($body['success']) && true === (bool) $body['success']) {
            return true;
        }

        if (isset($body['status'])) {
            $status = strtolower((string) $body['status']);

            if (in_array($status, ['active', 'valid'], true)) {
                return true;
            }

            if (in_array($status, ['expired', 'invalid', 'not_found', 'refunded', 'banned', 'revoked', 'domain_mismatch'], true)) {
                return false;
            }
        }

        if (isset($body['message']) && false !== stripos((string) $body['message'], 'license is valid')) {
            return true;
        }

        return null;
    }
    
    /**
     * Get days until license expires
     * 
     * @return int|null Days until expiration, null if lifetime
     */
    public function get_days_until_expiration() {
        $license_data = $this->get_license_data();
        
        $expires_at = isset($license_data['expires_at']) ? $this->normalize_expiration($license_data['expires_at']) : null;

        if (!$license_data || empty($expires_at)) {
            return null; // Lifetime license
        }
        
        $expires_timestamp = strtotime($expires_at);
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
        
        $expires_at = isset($license_data['expires_at']) ? $this->normalize_expiration($license_data['expires_at']) : null;

        if (!$license_data || empty($expires_at)) {
            return false; // Lifetime license doesn't expire
        }
        
        return strtotime($expires_at) < current_time('timestamp');
    }
}
