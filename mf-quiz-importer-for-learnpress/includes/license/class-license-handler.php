<?php
/**
 * License Handler for Quiz Importer For LearnPress
 *
 * Handles license activation, validation, and feature gating.
 * Unique class name to avoid conflicts with other Mamflow plugins.
 *
 * @package MF_LP_QI_License_Handler
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MF_LP_QI_License_Handler
 *
 * IMPORTANT: Class name is unique to avoid conflicts with other Mamflow plugins.
 * Each plugin must have its own license handler class name.
 */
class MF_LP_QI_License_Handler
{

    /**
     * Product ID on mamflow.com
     *
     * @var int
     */
    private $product_id;

    /**
     * Product name
     *
     * @var string
     */
    private $product_name;

    /**
     * API URL
     *
     * @var string
     */
    private $api_url;

    /**
     * Option key for storing license data
     *
     * @var string
     */
    private $option_key;

    /**
     * Constructor
     *
     * @param array $config Configuration array with keys:
     *                      - product_id: WooCommerce product ID on mamflow.com
     *                      - product_name: Human-readable product name
     *                      - api_url: Base API URL
     *                      - option_key: Unique option key for storing license data
     */
    public function __construct($config)
    {
        $this->product_id = $config['product_id'];
        $this->product_name = $config['product_name'];
        $this->api_url = isset($config['api_url']) ? $config['api_url'] : 'https://mamflow.com/wp-json/mamflow/v1';
        $this->option_key = $config['option_key'];
    }

    /**
     * Activate license
     *
     * @param string $license_key License key from user.
     * @return array Response with success status and message.
     */
    public function activate_license($license_key)
    {
        $key = sanitize_text_field($license_key);
        $domain = $this->get_site_domain();

        $response = wp_remote_post(
            $this->api_url . '/activate',
            array(
                'body' => wp_json_encode(
                    array(
                        'license_key' => $key,
                        'domain' => $domain,
                        'product_id' => $this->product_id,
                    )
                ),
                'headers' => array('Content-Type' => 'application/json'),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Connection error: ' . $response->get_error_message(),
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (200 !== $status_code) {
            return array(
                'success' => false,
                'message' => isset($body['message']) ? $body['message'] : 'Activation failed (HTTP ' . $status_code . ')',
            );
        }

        if (isset($body['success']) && $body['success']) {
            update_option(
                $this->option_key,
                array(
                    'license_key' => $key,
                    'status' => 'active',
                    'domain' => $domain,
                    'expires_at' => isset($body['expires_at']) ? $body['expires_at'] : null,
                    'last_check' => current_time('timestamp'),
                    'activation_date' => current_time('mysql'),
                )
            );
        }

        return $body;
    }

    /**
     * Deactivate license
     *
     * @return array Response with success status and message.
     */
    public function deactivate_license()
    {
        $license_data = get_option($this->option_key);

        if (!$license_data || empty($license_data['license_key'])) {
            return array(
                'success' => false,
                'message' => 'No license to deactivate',
            );
        }

        $response = wp_remote_post(
            $this->api_url . '/deactivate',
            array(
                'body' => wp_json_encode(
                    array(
                        'license_key' => $license_data['license_key'],
                        'domain' => $license_data['domain'],
                    )
                ),
                'headers' => array('Content-Type' => 'application/json'),
                'timeout' => 15,
            )
        );

        // Clear local data regardless of API response.
        delete_option($this->option_key);

        if (is_wp_error($response)) {
            return array(
                'success' => true,
                'message' => 'License removed locally (server communication failed)',
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }

    /**
     * Check license status via API (called by cron)
     *
     * @return bool True if license is valid, false otherwise.
     */
    public function check_license_status()
    {
        $license_data = get_option($this->option_key);

        if (!$license_data || empty($license_data['license_key'])) {
            return false;
        }

        $response = wp_remote_post(
            $this->api_url . '/check',
            array(
                'body' => wp_json_encode(
                    array(
                        'license_key' => $license_data['license_key'],
                        'domain' => $license_data['domain'],
                    )
                ),
                'headers' => array('Content-Type' => 'application/json'),
                'timeout' => 15,
            )
        );

        // On connection error, keep current status — but still enforce local expiry.
        if (is_wp_error($response)) {
            if ('active' !== $license_data['status']) {
                return false;
            }
            // Even if status is active, block if locally expired.
            if (!empty($license_data['expires_at']) && strtotime($license_data['expires_at']) < current_time('timestamp')) {
                $license_data['status'] = 'invalid';
                update_option($this->option_key, $license_data);
                return false;
            }
            return true;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Update local cache.
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
     * Main method to gate features in the plugin.
     *
     * @return bool True if license is valid and active.
     */
    public function is_feature_enabled()
    {
        $license_data = get_option($this->option_key);

        if (!$license_data) {
            return false;
        }

        if (!isset($license_data['status']) || 'active' !== $license_data['status']) {
            return false;
        }

        // Local expiry check — works even when cron is disabled.
        // If expires_at is set and has passed, block immediately without API call.
        if (!empty($license_data['expires_at'])) {
            if (strtotime($license_data['expires_at']) < current_time('timestamp')) {
                // Mark invalid locally so subsequent calls are fast.
                $license_data['status'] = 'invalid';
                update_option($this->option_key, $license_data);
                return false;
            }
        }

        // Fallback: If last_check > 72 hours, force API re-check.
        // This covers cron-disabled environments — triggers on page load at most once per 72h.
        $last_check = isset($license_data['last_check']) ? $license_data['last_check'] : 0;
        $hours_since_check = (current_time('timestamp') - $last_check) / HOUR_IN_SECONDS;

        if ($hours_since_check > 72) {
            return $this->check_license_status();
        }

        return true;
    }

    /**
     * Get license data
     *
     * @return array|false License data or false if not set.
     */
    public function get_license_data()
    {
        return get_option($this->option_key, false);
    }

    /**
     * Get current site domain
     *
     * @return string Cleaned domain name.
     */
    private function get_site_domain()
    {
        $url = home_url();

        $domain = preg_replace('#^https?://#i', '', $url);
        $domain = preg_replace('#^www\.#i', '', $domain);
        $domain = strtok($domain, '/');

        return strtolower(trim($domain));
    }

    /**
     * Get days until license expires
     *
     * @return int|null Days until expiration, null if lifetime.
     */
    public function get_days_until_expiration()
    {
        $license_data = $this->get_license_data();

        if (!$license_data || empty($license_data['expires_at'])) {
            return null;
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
    public function is_expired()
    {
        $license_data = $this->get_license_data();

        if (!$license_data || empty($license_data['expires_at'])) {
            return false;
        }

        return strtotime($license_data['expires_at']) < current_time('timestamp');
    }
}
