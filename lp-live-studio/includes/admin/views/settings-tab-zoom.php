<?php
/**
 * Zoom Settings Tab
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

$api_key = get_option(MF_LLS_OPT_ZOOM_API_KEY, '');
$api_secret = get_option(MF_LLS_OPT_ZOOM_API_SECRET, '');
$auth_type = get_option(MF_LLS_OPT_ZOOM_AUTH_TYPE, 'oauth');
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
                        <?php esc_html_e('JWT (Legacy)', 'lp-live-studio'); ?>
                    </option>
                </select>
                <p class="description">
                    <?php esc_html_e('Server-to-Server OAuth is recommended for new integrations.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_zoom_api_key">
                    <?php esc_html_e('API Key / Client ID', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="text" name="mf_lls_zoom_api_key" id="mf_lls_zoom_api_key"
                    value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e('Enter your Zoom API Key or Client ID from Zoom Marketplace.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_zoom_api_secret">
                    <?php esc_html_e('API Secret / Client Secret', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="password" name="mf_lls_zoom_api_secret" id="mf_lls_zoom_api_secret"
                    value="<?php echo esc_attr($api_secret); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e('Enter your Zoom API Secret or Client Secret.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"></th>
            <td>
                <button type="button" class="button button-secondary mf-lls-test-connection" data-platform="zoom">
                    <?php esc_html_e('Test Connection', 'lp-live-studio'); ?>
                </button>
                <span class="mf-lls-connection-status"></span>
            </td>
        </tr>

        <tr>
            <th scope="row"></th>
            <td>
                <div class="mf-lls-info-box">
                    <h4>
                        <?php esc_html_e('How to get Zoom credentials:', 'lp-live-studio'); ?>
                    </h4>
                    <ol>
                        <li>
                            <?php esc_html_e('Go to Zoom Marketplace: https://marketplace.zoom.us/', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Click "Develop" → "Build App"', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Choose "Server-to-Server OAuth" app type', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Copy your Client ID and Client Secret', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Add required scopes: meeting:write, meeting:read, user:read', 'lp-live-studio'); ?>
                        </li>
                    </ol>
                </div>
            </td>
        </tr>
    </tbody>
</table>