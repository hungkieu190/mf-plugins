<?php
/**
 * Zoom Settings Tab
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

$auth_type = get_option(MF_LLS_OPT_ZOOM_AUTH_TYPE, 'oauth');
$account_id = get_option(MF_LLS_OPT_ZOOM_ACCOUNT_ID, '');
$api_key = get_option(MF_LLS_OPT_ZOOM_API_KEY, '');
$api_secret = get_option(MF_LLS_OPT_ZOOM_API_SECRET, '');
$webhook_secret = get_option('mf_lls_zoom_webhook_secret', '');
?>

<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <label for="mf_lls_zoom_auth_type">
                    <?php esc_html_e('Authentication Type', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <select name="mf_lls_zoom_auth_type" id="mf_lls_zoom_auth_type">
                    <option value="oauth" <?php selected($auth_type, 'oauth'); ?>>
                        <?php esc_html_e('Server-to-Server OAuth (Recommended)', 'lp-live-studio'); ?>
                    </option>
                    <option value="jwt" <?php selected($auth_type, 'jwt'); ?>>
                        <?php esc_html_e('JWT (Legacy — deprecated June 2023)', 'lp-live-studio'); ?>
                    </option>
                </select>
                <p class="description">
                    <?php esc_html_e('Server-to-Server OAuth is required for all new Zoom integrations.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr id="mf-lls-zoom-account-id-row">
            <th scope="row">
                <label for="mf_lls_zoom_account_id">
                    <?php esc_html_e('Account ID', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="text" name="mf_lls_zoom_account_id" id="mf_lls_zoom_account_id"
                    value="<?php echo esc_attr($account_id); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e('Your Zoom Account ID from Server-to-Server OAuth app.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_zoom_api_key">
                    <?php esc_html_e('Client ID', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="text" name="mf_lls_zoom_api_key" id="mf_lls_zoom_api_key"
                    value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e('Your Zoom Client ID (OAuth) or API Key (JWT).', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_zoom_api_secret">
                    <?php esc_html_e('Client Secret', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="password" name="mf_lls_zoom_api_secret" id="mf_lls_zoom_api_secret"
                    value="<?php echo esc_attr($api_secret); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e('Your Zoom Client Secret (OAuth) or API Secret (JWT).', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_zoom_webhook_secret">
                    <?php esc_html_e( 'Webhook Secret Token', 'lp-live-studio' ); ?>
                </label>
            </th>
            <td>
                <input type="password" name="mf_lls_zoom_webhook_secret" id="mf_lls_zoom_webhook_secret"
                    value="<?php echo esc_attr( $webhook_secret ); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e( 'Secret Token from Zoom App → Feature → Event Subscriptions. Required for webhook signature verification.', 'lp-live-studio' ); ?>
                    <br>
                    <?php
                    printf(
                        /* translators: %s: Webhook endpoint URL */
                        esc_html__( 'Webhook Endpoint URL: %s', 'lp-live-studio' ),
                        '<code>' . esc_html( rest_url( 'mf-lls/v1/webhook/zoom' ) ) . '</code>'
                    );
                    ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"></th>
            <td>
                <button type="button" class="button button-secondary mf-lls-test-connection" data-platform="zoom">
                    <?php esc_html_e( 'Test Connection', 'lp-live-studio' ); ?>
                </button>
                <span class="mf-lls-connection-status"></span>
            </td>
        </tr>

        <tr>
            <th scope="row"></th>
            <td>
                <div class="mf-lls-info-box">
                    <h4><?php esc_html_e('How to get Zoom Server-to-Server OAuth credentials:', 'lp-live-studio'); ?>
                    </h4>
                    <ol>
                        <li><?php esc_html_e('Go to Zoom Marketplace: https://marketplace.zoom.us/', 'lp-live-studio'); ?>
                        </li>
                        <li><?php esc_html_e('Click "Develop" → "Build App" → "Server-to-Server OAuth"', 'lp-live-studio'); ?>
                        </li>
                        <li><?php esc_html_e('Copy your Account ID, Client ID, and Client Secret', 'lp-live-studio'); ?>
                        </li>
                        <li><?php esc_html_e('Add required scopes: meeting:write:admin, meeting:read:admin, user:read:admin', 'lp-live-studio'); ?>
                        </li>
                        <li><?php esc_html_e('Activate the app in your Zoom account', 'lp-live-studio'); ?></li>
                    </ol>
                </div>
            </td>
        </tr>
    </tbody>
</table>