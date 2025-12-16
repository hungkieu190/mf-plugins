<?php
/**
 * Admin License Settings Page
 * 
 * UI for users to enter and manage their license key.
 * 
 * @package LP_Sticky_Notes
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('lp_sticky_notes_render_license_tab')) {
    /**
     * Render Sticky Notes license tab content
     */
    function lp_sticky_notes_render_license_tab()
    {
        // Get license handler instance
        $plugin = LP_Sticky_Notes::instance();
        $license_handler = $plugin->get_license_handler();

        // Handle form submissions
        $message = '';
        $message_type = '';

        if (isset($_POST['mamflow_license_action'])) {
            // Verify nonce
            if (
                !isset($_POST['mamflow_license_nonce']) ||
                !wp_verify_nonce($_POST['mamflow_license_nonce'], 'mamflow_license_action')
            ) {
                $message = 'Security check failed. Please try again.';
                $message_type = 'error';
            } else {
                $action = sanitize_text_field($_POST['mamflow_license_action']);

                if ($action === 'activate') {
                    $license_key = sanitize_text_field($_POST['license_key']);
                    $result = $license_handler->activate_license($license_key);

                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';

                } elseif ($action === 'deactivate') {
                    $result = $license_handler->deactivate_license();

                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';

                } elseif ($action === 'check') {
                    $is_valid = $license_handler->check_license_status();

                    $message = $is_valid ? 'License is valid and active!' : 'License validation failed.';
                    $message_type = $is_valid ? 'success' : 'error';
                }
            }
        }

        // Get current license data
        $license_data = $license_handler->get_license_data();
        $is_active = $license_handler->is_feature_enabled();

        ?>
        <!-- Tab Content: No wrap div needed, already in tab container -->
        <h2>LearnPress Sticky Notes - License</h2>

        <?php if ($message): ?>
            <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 800px;">
            <h2>License Status</h2>

            <?php if ($license_data && $is_active): ?>
                <!-- Active License -->
                <div
                    style="padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #155724;">✓ License Active</h3>

                    <table class="widefat" style="background: white;">
                        <tr>
                            <td><strong>License Key:</strong></td>
                            <td><code><?php echo esc_html($license_data['license_key']); ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>Domain:</strong></td>
                            <td><?php echo esc_html($license_data['domain']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td><span style="color: #155724; font-weight: bold;">Active</span></td>
                        </tr>
                        <tr>
                            <td><strong>Expiration:</strong></td>
                            <td>
                                <?php
                                if ($license_data['expires_at']):
                                    $days = $license_handler->get_days_until_expiration();
                                    echo esc_html(date('F j, Y', strtotime($license_data['expires_at'])));
                                    echo ' <em>(' . $days . ' days remaining)</em>';
                                else:
                                    echo '<em>Lifetime License</em>';
                                endif;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Last Checked:</strong></td>
                            <td><?php echo esc_html(human_time_diff($license_data['last_check'], current_time('timestamp'))) . ' ago'; ?>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 15px;">
                        <form method="post" style="display: inline-block; margin-right: 10px;">
                            <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                            <input type="hidden" name="mamflow_license_action" value="deactivate">
                            <button type="submit" class="button"
                                onclick="return confirm('Are you sure you want to deactivate this license?');">
                                Deactivate License
                            </button>
                        </form>

                        <form method="post" style="display: inline-block;">
                            <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                            <input type="hidden" name="mamflow_license_action" value="check">
                            <button type="submit" class="button">
                                Check License Status
                            </button>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <!-- No Active License -->
                <div
                    style="padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #856404;">⚠ License Not Activated</h3>
                    <p>Please enter your license key below to activate premium features.</p>
                </div>

                <form method="post">
                    <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                    <input type="hidden" name="mamflow_license_action" value="activate">

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="license_key">License Key</label>
                            </th>
                            <td>
                                <input type="text" id="license_key" name="license_key" class="regular-text"
                                    placeholder="MAMF-XXXX-XXXX-XXXX-XXXX"
                                    value="<?php echo isset($license_data['license_key']) ? esc_attr($license_data['license_key']) : ''; ?>"
                                    required>
                                <p class="description">
                                    Enter the license key you received after purchasing this plugin from
                                    <a href="https://mamflow.com" target="_blank">mamflow.com</a>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <button type="submit" class="button button-primary">Activate License</button>
                    </p>
                </form>
            <?php endif; ?>

            <hr>

            <h3>Need Help?</h3>
            <ul>
                <li><strong>Where's my license key?</strong> Check your order confirmation email from mamflow.com</li>
                <li><strong>Lost your key?</strong> <a href="https://mamflow.com/my-account/" target="_blank">Login to your
                        account</a> to retrieve it</li>
                <li><strong>Support:</strong> Contact us at <a href="mailto:support@mamflow.com">support@mamflow.com</a>
                </li>
            </ul>
        </div>

        <style>
            .mamflow-tab-content .card table.widefat td {
                padding: 10px;
            }

            .mamflow-tab-content .card table.widefat tr {
                border-bottom: 1px solid #ddd;
            }

            .mamflow-tab-content .card table.widefat tr:last-child {
                border-bottom: none;
            }
        </style>
        <?php
    }
}
