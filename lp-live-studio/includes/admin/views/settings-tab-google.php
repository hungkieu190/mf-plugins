<?php
/**
 * Google Meet Settings Tab
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

$client_id = get_option(MF_LLS_OPT_GOOGLE_CLIENT_ID, '');
$client_secret = get_option(MF_LLS_OPT_GOOGLE_CLIENT_SECRET, '');
$refresh_token = get_option(MF_LLS_OPT_GOOGLE_REFRESH_TOKEN, '');
$is_connected = !empty($refresh_token);
?>

<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <label for="mf_lls_google_client_id">
                    <?php esc_html_e('Client ID', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="text" name="mf_lls_google_client_id" id="mf_lls_google_client_id"
                    value="<?php echo esc_attr($client_id); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e('Enter your Google Cloud OAuth 2.0 Client ID.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_google_client_secret">
                    <?php esc_html_e('Client Secret', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="password" name="mf_lls_google_client_secret" id="mf_lls_google_client_secret"
                    value="<?php echo esc_attr($client_secret); ?>" class="regular-text">
                <p class="description">
                    <?php esc_html_e('Enter your Google Cloud OAuth 2.0 Client Secret.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <?php esc_html_e('Connection Status', 'lp-live-studio'); ?>
            </th>
            <td>
                <?php if ($is_connected): ?>
                    <span class="mf-lls-status-badge mf-lls-status-connected">
                        <?php esc_html_e('Connected', 'lp-live-studio'); ?>
                    </span>
                    <button type="button" class="button button-secondary mf-lls-disconnect-google">
                        <?php esc_html_e('Disconnect', 'lp-live-studio'); ?>
                    </button>
                <?php else: ?>
                    <span class="mf-lls-status-badge mf-lls-status-disconnected">
                        <?php esc_html_e('Not Connected', 'lp-live-studio'); ?>
                    </span>
                    <button type="button" class="button button-primary mf-lls-connect-google">
                        <?php esc_html_e('Connect Google Account', 'lp-live-studio'); ?>
                    </button>
                <?php endif; ?>
                <p class="description">
                    <?php esc_html_e('You need to connect your Google account to create Google Meet sessions.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"></th>
            <td>
                <div class="mf-lls-info-box">
                    <h4>
                        <?php esc_html_e('How to get Google credentials:', 'lp-live-studio'); ?>
                    </h4>
                    <ol>
                        <li>
                            <?php esc_html_e('Go to Google Cloud Console: https://console.cloud.google.com/', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Create a new project or select existing one', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Enable Google Calendar API', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php esc_html_e('Application type: Web application', 'lp-live-studio'); ?>
                        </li>
                        <li>
                            <?php
                            printf(
                                /* translators: %s: OAuth redirect URI */
                                esc_html__('Add authorized redirect URI: %s', 'lp-live-studio'),
                                '<code>' . esc_url(admin_url('admin.php?page=mf-lls-settings&tab=google&oauth_callback=1')) . '</code>'
                            );
                            ?>
                        </li>
                        <li>
                            <?php esc_html_e('Copy your Client ID and Client Secret', 'lp-live-studio'); ?>
                        </li>
                    </ol>
                </div>
            </td>
        </tr>
    </tbody>
</table>