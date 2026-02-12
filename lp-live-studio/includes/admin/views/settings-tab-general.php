<?php
/**
 * General Settings Tab
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

$default_platform = get_option(MF_LLS_OPT_DEFAULT_PLATFORM, 'zoom');
$reminder_1h_enabled = get_option('mf_lls_reminder_1h_enabled', '1');
$reminder_15m_enabled = get_option('mf_lls_reminder_15m_enabled', '1');
$rating_enabled = get_option('mf_lls_rating_enabled', '1');
$rating_expire_days = get_option('mf_lls_rating_expire_days', '7');
?>

<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <label for="mf_lls_default_platform">
                    <?php esc_html_e('Default Platform', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <select name="mf_lls_default_platform" id="mf_lls_default_platform">
                    <option value="zoom" <?php selected($default_platform, 'zoom'); ?>>
                        <?php esc_html_e('Zoom', 'lp-live-studio'); ?>
                    </option>
                    <option value="google_meet" <?php selected($default_platform, 'google_meet'); ?>>
                        <?php esc_html_e('Google Meet', 'lp-live-studio'); ?>
                    </option>
                    <option value="agora" <?php selected($default_platform, 'agora'); ?>>
                        <?php esc_html_e('Agora', 'lp-live-studio'); ?>
                    </option>
                </select>
                <p class="description">
                    <?php esc_html_e('Select the default platform for new live sessions.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <?php esc_html_e('Reminder Notifications', 'lp-live-studio'); ?>
            </th>
            <td>
                <fieldset>
                    <label>
                        <input type="checkbox" name="mf_lls_reminder_1h_enabled" value="1" <?php checked($reminder_1h_enabled, '1'); ?>>
                        <?php esc_html_e('Send reminder 1 hour before session', 'lp-live-studio'); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="mf_lls_reminder_15m_enabled" value="1" <?php checked($reminder_15m_enabled, '1'); ?>>
                        <?php esc_html_e('Send reminder 15 minutes before session', 'lp-live-studio'); ?>
                    </label>
                </fieldset>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <?php esc_html_e('Tutor Rating', 'lp-live-studio'); ?>
            </th>
            <td>
                <fieldset>
                    <label>
                        <input type="checkbox" name="mf_lls_rating_enabled" value="1" <?php checked($rating_enabled, '1'); ?>>
                        <?php esc_html_e('Enable tutor rating after live sessions', 'lp-live-studio'); ?>
                    </label>
                    <br><br>
                    <label>
                        <?php esc_html_e('Rating expires after', 'lp-live-studio'); ?>
                        <input type="number" name="mf_lls_rating_expire_days"
                            value="<?php echo esc_attr($rating_expire_days); ?>" min="1" max="30"
                            style="width: 60px;">
                        <?php esc_html_e('days', 'lp-live-studio'); ?>
                    </label>
                </fieldset>
            </td>
        </tr>
    </tbody>
</table>