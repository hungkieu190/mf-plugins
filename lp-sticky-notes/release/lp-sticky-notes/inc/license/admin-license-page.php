<?php
/**
 * Admin License Settings Page
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
        $plugin = LP_Sticky_Notes::instance();
        $license_handler = $plugin->get_license_handler();

        $message = '';
        $message_type = '';

        if (isset($_POST['mamflow_license_action'])) {
            if (
                !isset($_POST['mamflow_license_nonce']) ||
                !wp_verify_nonce($_POST['mamflow_license_nonce'], 'mamflow_license_action')
            ) {
                $message = __('Security check failed. Please try again.', 'lp-sticky-notes');
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
                    $message = $is_valid
                        ? __('License is valid and active.', 'lp-sticky-notes')
                        : __('License validation failed. Verify the key and try again.', 'lp-sticky-notes');
                    $message_type = $is_valid ? 'success' : 'error';
                }
            }
        }

        $license_data = $license_handler->get_license_data();
        $is_active = $license_handler->is_feature_enabled();
        ?>

        <div class="mamflow-license-panel">
            <div class="mamflow-license-heading">
                <div>
                    <h2><?php esc_html_e('LearnPress Sticky Notes', 'lp-sticky-notes'); ?></h2>
                    <p><?php esc_html_e('Manage access for student note review and Sticky Notes settings.', 'lp-sticky-notes'); ?></p>
                </div>
                <span class="mamflow-status-badge <?php echo $is_active ? 'is-success' : 'is-warning'; ?>">
                    <?php echo $is_active ? esc_html__('Active', 'lp-sticky-notes') : esc_html__('Action required', 'lp-sticky-notes'); ?>
                </span>
            </div>

            <?php if ($message): ?>
                <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
                    <p><?php echo esc_html($message); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($license_data && $is_active): ?>
                <div class="mamflow-license-section">
                    <h3><?php esc_html_e('License status', 'lp-sticky-notes'); ?></h3>
                    <table class="widefat striped mamflow-license-table">
                        <tbody>
                            <tr>
                                <th scope="row"><?php esc_html_e('License key', 'lp-sticky-notes'); ?></th>
                                <td><code><?php echo esc_html($license_data['license_key']); ?></code></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Domain', 'lp-sticky-notes'); ?></th>
                                <td><?php echo esc_html($license_data['domain']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Status', 'lp-sticky-notes'); ?></th>
                                <td><span class="mamflow-status-text is-success"><?php esc_html_e('Active', 'lp-sticky-notes'); ?></span></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Expiration', 'lp-sticky-notes'); ?></th>
                                <td>
                                    <?php
                                    $days = $license_handler->get_days_until_expiration();
                                    if (null !== $days) {
                                        printf(
                                            esc_html__('%1$s (%2$s days remaining)', 'lp-sticky-notes'),
                                            esc_html(date_i18n(get_option('date_format'), strtotime($license_data['expires_at']))),
                                            esc_html(number_format_i18n($days))
                                        );
                                    } else {
                                        esc_html_e('Lifetime license', 'lp-sticky-notes');
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Last checked', 'lp-sticky-notes'); ?></th>
                                <td>
                                    <?php
                                    printf(
                                        esc_html__('%s ago', 'lp-sticky-notes'),
                                        esc_html(human_time_diff($license_data['last_check'], current_time('timestamp')))
                                    );
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mamflow-license-actions">
                        <form method="post">
                            <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                            <input type="hidden" name="mamflow_license_action" value="check">
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e('Check status', 'lp-sticky-notes'); ?>
                            </button>
                        </form>

                        <form method="post">
                            <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                            <input type="hidden" name="mamflow_license_action" value="deactivate">
                            <button type="submit" class="button" onclick="return confirm('<?php echo esc_js(__('Deactivate this license?', 'lp-sticky-notes')); ?>');">
                                <?php esc_html_e('Deactivate license', 'lp-sticky-notes'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="mamflow-license-section">
                    <h3><?php esc_html_e('Activate license', 'lp-sticky-notes'); ?></h3>
                    <p><?php esc_html_e('Enter your license key to unlock student note review, settings, and product updates.', 'lp-sticky-notes'); ?></p>

                    <form method="post" class="mamflow-license-form">
                        <?php wp_nonce_field('mamflow_license_action', 'mamflow_license_nonce'); ?>
                        <input type="hidden" name="mamflow_license_action" value="activate">

                        <label for="license_key"><?php esc_html_e('License key', 'lp-sticky-notes'); ?></label>
                        <input
                            type="text"
                            id="license_key"
                            name="license_key"
                            class="regular-text"
                            placeholder="MAMF-XXXX-XXXX-XXXX-XXXX"
                            value="<?php echo isset($license_data['license_key']) ? esc_attr($license_data['license_key']) : ''; ?>"
                            required
                        >
                        <p class="description">
                            <?php esc_html_e('Use the key from your Mamflow order confirmation email.', 'lp-sticky-notes'); ?>
                        </p>

                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Activate license', 'lp-sticky-notes'); ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="mamflow-license-help">
                <h3><?php esc_html_e('Support', 'lp-sticky-notes'); ?></h3>
                <p>
                    <?php esc_html_e('Need the key again?', 'lp-sticky-notes'); ?>
                    <a href="https://mamflow.com/my-account/" target="_blank"><?php esc_html_e('Open your Mamflow account', 'lp-sticky-notes'); ?></a>.
                    <?php esc_html_e('For license issues, contact', 'lp-sticky-notes'); ?>
                    <a href="mailto:support@mamflow.com">support@mamflow.com</a>.
                </p>
            </div>
        </div>
        <?php
    }
}
