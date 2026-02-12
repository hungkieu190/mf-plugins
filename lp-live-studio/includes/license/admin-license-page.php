<?php
/**
 * Admin License Settings Page
 * 
 * UI for users to enter and manage their license key for LearnPress Live Studio.
 * 
 * @package LearnPress_Live_Studio
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('mf_lls_render_license_tab')) {
    /**
     * Render LearnPress Live Studio license tab content
     */
    function mf_lls_render_license_tab()
    {
        // Get license handler instance.
        $plugin = MF_LLS_Addon::instance();
        $license_handler = $plugin->get_license_handler();

        // Handle form submissions.
        $message = '';
        $message_type = '';

        if (isset($_POST['mamflow_license_action'])) {
            // Verify nonce.
            if (
                !isset($_POST['mamflow_license_nonce']) ||
                !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mamflow_license_nonce'])), 'mamflow_license_action')
            ) {
                $message = 'Security check failed. Please try again.';
                $message_type = 'error';
            } else {
                $action = sanitize_text_field(wp_unslash($_POST['mamflow_license_action']));

                if ('activate' === $action) {
                    $license_key = sanitize_text_field(wp_unslash($_POST['license_key']));
                    $result = $license_handler->activate_license($license_key);

                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';

                } elseif ('deactivate' === $action) {
                    $result = $license_handler->deactivate_license();

                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';

                } elseif ('check' === $action) {
                    $is_valid = $license_handler->check_license_status();

                    $message = $is_valid ? 'License is valid and active!' : 'License validation failed.';
                    $message_type = $is_valid ? 'success' : 'error';
                }
            }
        }

        // Get current license data.
        $license_data = $license_handler->get_license_data();
        $is_active = $license_handler->is_feature_enabled();

        ?>
        <!-- Tab Content: No wrap div needed, already in tab container -->
        <h2>
            <?php esc_html_e('LearnPress Live Studio - License', 'lp-live-studio'); ?>
        </h2>

        <?php if ($message): ?>
            <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
                <p>
                    <?php echo esc_html($message); ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 800px;">
            <h2>
                <?php esc_html_e('License Status', 'lp-live-studio'); ?>
            </h2>

            <?php if ($license_data && $is_active): ?>
                <!-- Active License -->
                <div
                    style="padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #155724;">✓
                        <?php esc_html_e('License Active', 'lp-live-studio'); ?>
                    </h3>

                    <table class="widefat" style="background: white;">
                        <tr>
                            <td><strong>
                                    <?php esc_html_e('License Key:', 'lp-live-studio'); ?>
                                </strong></td>
                            <td><code><?php echo esc_html($license_data['license_key']); ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>
                                    <?php esc_html_e('Domain:', 'lp-live-studio'); ?>
                                </strong></td>
                            <td>
                                <?php echo esc_html($license_data['domain']); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>
                                    <?php esc_html_e('Status:', 'lp-live-studio'); ?>
                                </strong></td>
                            <td><span style="color: #155724; font-weight: bold;">
                                    <?php esc_html_e('Active', 'lp-live-studio'); ?>
                                </span></td>
                        </tr>
                        <tr>
                            <td><strong>
                                    <?php esc_html_e('Expiration:', 'lp-live-studio'); ?>
                                </strong></td>
                            <td>
                                <?php
                                if ($license_data['expires_at']):
                                    $days = $license_handler->get_days_until_expiration();
                                    echo esc_html(gmdate('F j, Y', strtotime($license_data['expires_at'])));
                                    /* translators: %d: Number of days remaining */
                                    echo ' <em>' . sprintf(esc_html__('(%d days remaining)', 'lp-live-studio'), $days) . '</em>';
                                else:
                                    echo '<em>' . esc_html__('Lifetime License', 'lp-live-studio') . '</em>';
                                endif;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>
                                    <?php esc_html_e('Last Checked:', 'lp-live-studio'); ?>
                                </strong></td>
                            <td>
                                <?php
                                /* translators: %s: Human-readable time difference */
                                echo esc_html(
                                    sprintf(
                                        __('%s ago', 'lp-live-studio'),
                                        human_time_diff($license_data['last_check'], current_time('timestamp'))
                                    )
                                );
                                ?>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 15px;">
                        <form method="post" style="display: inline-block; margin-right: 10px;">
                            <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                            <input type="hidden" name="mamflow_license_action" value="deactivate">
                            <button type="submit" class="button"
                                onclick="return confirm('<?php esc_attr_e('Are you sure you want to deactivate this license?', 'lp-live-studio'); ?>');">
                                <?php esc_html_e('Deactivate License', 'lp-live-studio'); ?>
                            </button>
                        </form>

                        <form method="post" style="display: inline-block;">
                            <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                            <input type="hidden" name="mamflow_license_action" value="check">
                            <button type="submit" class="button">
                                <?php esc_html_e('Check License Status', 'lp-live-studio'); ?>
                            </button>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <!-- No Active License -->
                <div
                    style="padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #856404;">⚠
                        <?php esc_html_e('License Not Activated', 'lp-live-studio'); ?>
                    </h3>
                    <p>
                        <?php esc_html_e('Please enter your license key below to activate premium features.', 'lp-live-studio'); ?>
                    </p>
                </div>

                <form method="post">
                    <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                    <input type="hidden" name="mamflow_license_action" value="activate">

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="license_key">
                                    <?php esc_html_e('License Key', 'lp-live-studio'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" id="license_key" name="license_key" class="regular-text"
                                    placeholder="MAMF-XXXX-XXXX-XXXX-XXXX"
                                    value="<?php echo isset($license_data['license_key']) ? esc_attr($license_data['license_key']) : ''; ?>"
                                    required>
                                <p class="description">
                                    <?php
                                    printf(
                                        /* translators: %s: Link to mamflow.com */
                                        esc_html__('Enter the license key you received after purchasing this plugin from %s', 'lp-live-studio'),
                                        '<a href="https://mamflow.com" target="_blank">mamflow.com</a>'
                                    );
                                    ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Activate License', 'lp-live-studio'); ?>
                        </button>
                    </p>
                </form>
            <?php endif; ?>

            <hr>

            <h3>
                <?php esc_html_e('Need Help?', 'lp-live-studio'); ?>
            </h3>
            <ul>
                <li><strong>
                        <?php esc_html_e("Where's my license key?", 'lp-live-studio'); ?>
                    </strong>
                    <?php esc_html_e('Check your order confirmation email from mamflow.com', 'lp-live-studio'); ?>
                </li>
                <li><strong>
                        <?php esc_html_e('Lost your key?', 'lp-live-studio'); ?>
                    </strong> <a href="https://mamflow.com/my-account/" target="_blank">
                        <?php esc_html_e('Login to your account', 'lp-live-studio'); ?>
                    </a>
                    <?php esc_html_e('to retrieve it', 'lp-live-studio'); ?>
                </li>
                <li><strong>
                        <?php esc_html_e('Support:', 'lp-live-studio'); ?>
                    </strong>
                    <?php esc_html_e('Contact us at', 'lp-live-studio'); ?> <a
                        href="mailto:support@mamflow.com">support@mamflow.com</a>
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
