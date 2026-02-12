<?php
/**
 * Agora Settings Tab
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

$app_id = get_option(MF_LLS_OPT_AGORA_APP_ID, '');
$app_cert = get_option(MF_LLS_OPT_AGORA_APP_CERT, '');
$channel_prefix = get_option(MF_LLS_OPT_AGORA_CHANNEL_PREFIX, 'lls');
?>

<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <label for="mf_lls_agora_app_id">
                    <?php esc_html_e('App ID', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="text" name="mf_lls_agora_app_id" id="mf_lls_agora_app_id"
                    value="<?php echo esc_attr($app_id); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e('Enter your Agora App ID from Agora Console.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_agora_app_cert">
                    <?php esc_html_e('App Certificate', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="password" name="mf_lls_agora_app_cert" id="mf_lls_agora_app_cert"
                    value="<?php echo esc_attr($app_cert); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e('Enter your Agora App Certificate for token generation.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_agora_channel_prefix">
                    <?php esc_html_e('Channel Prefix', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="text" name="mf_lls_agora_channel_prefix" id="mf_lls_agora_channel_prefix"
                    value="<?php echo esc_attr($channel_prefix); ?>" class="regular-text" pattern="[a-z0-9_]+"
                    maxlength="10">
                <p class="description">
                    <?php esc_html_e('Prefix for Agora channel names (lowercase letters, numbers, underscore only).', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"></th>
            <td>
                <button type="button" class="button button-secondary mf-lls-test-connection" data-platform="agora">
                    <?php esc_html_e('Test Configuration', 'lp-live-studio'); ?>
                </button>
                <span class="mf-lls-connection-status"></span>
            </td>
        </tr>

        <tr>
            <th scope="row"></th>
            <td>
                <div class="mf-lls-info-box">
                    <h4>
                        <?php esc_html_e('How to get Agora credentials:', 'lp-live-studio'); ?>
                    </h4>
                    <ol>
                        <li>
                            <?php esc_html_e('Go to Agora Console: https://console.agora.io/', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Create an account or sign in', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Create a new project', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Copy your App ID from project settings', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Enable "Primary Certificate" and copy the certificate', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Make sure to enable "RTC" (Real-Time Communication) service', 'lp-live-studio'); ?>
                        </li>
                    </ol>
                    <p><strong>
                            <?php esc_html_e('Note:', 'lp-live-studio'); ?>
                        </strong>
                        <?php esc_html_e('Agora provides embedded video/audio directly in your site without redirects.', 'lp-live-studio'); ?>
                    </p>
                </div>
            </td>
        </tr>
    </tbody>
</table>