<?php
/**
 * Profile settings template
 *
 * @package LP_Lesson_Completion_Sound
 */

defined('ABSPATH') || exit();
?>

<div class="lp-lcs-settings-section">
    <?php if (isset($_GET['lp_lcs_saved']) && $_GET['lp_lcs_saved'] == '1'): ?>
        <div class="lp-lcs-success-message">
            <?php esc_html_e('Settings saved successfully!', 'lp-lesson-completion-sound'); ?>
        </div>
    <?php endif; ?>

    <h3><?php esc_html_e('Lesson Completion Sound & Effects', 'lp-lesson-completion-sound'); ?></h3>

    <form method="post" action="">
        <?php wp_nonce_field('lp_lcs_save_settings', 'lp_lcs_settings_nonce'); ?>

        <!-- Enable/Disable -->
        <div class="lp-lcs-setting-row">
            <label>
                <input type="checkbox" name="lp_lcs_enable" value="yes" <?php checked($settings['lp_lcs_enable'], 'yes'); ?>>
                <?php esc_html_e('Enable lesson completion sound', 'lp-lesson-completion-sound'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Play a sound when you complete a lesson.', 'lp-lesson-completion-sound'); ?>
            </p>
        </div>

        <!-- Sound Selection -->
        <div class="lp-lcs-setting-row">
            <label for="lp_lcs_sound">
                <?php esc_html_e('Choose completion sound', 'lp-lesson-completion-sound'); ?>
            </label>
            <select name="lp_lcs_sound" id="lp_lcs_sound">
                <?php foreach ($available_sounds as $key => $label): ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($settings['lp_lcs_sound'], $key); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">
                <?php esc_html_e('Select the sound that will play when you complete a lesson.', 'lp-lesson-completion-sound'); ?>
            </p>
        </div>

        <!-- Confetti Toggle -->
        <div class="lp-lcs-setting-row">
            <label>
                <input type="checkbox" name="lp_lcs_confetti" value="yes" <?php checked($settings['lp_lcs_confetti'], 'yes'); ?>>
                <?php esc_html_e('Show confetti effects', 'lp-lesson-completion-sound'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Display falling confetti animation when completing a lesson.', 'lp-lesson-completion-sound'); ?>
            </p>
        </div>

        <!-- Prevent Auto-Redirect -->
        <div class="lp-lcs-setting-row">
            <label>
                <input type="checkbox" name="lp_lcs_prevent_redirect" value="yes" <?php checked($settings['lp_lcs_prevent_redirect'], 'yes'); ?>>
                <?php esc_html_e('Stay on current lesson after completion', 'lp-lesson-completion-sound'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Prevent automatic redirect to the next lesson. You can manually navigate when ready.', 'lp-lesson-completion-sound'); ?>
            </p>
        </div>

        <!-- Custom Sound Upload (Premium - Hidden for now) -->
        <div class="lp-lcs-setting-row lp-lcs-disabled" style="display: none;">
            <label>
                <input type="checkbox" name="lp_lcs_custom_sound_enable" value="yes" disabled>
                <?php esc_html_e('Use custom sound', 'lp-lesson-completion-sound'); ?>
                <span class="lp-lcs-premium-badge"><?php esc_html_e('Premium', 'lp-lesson-completion-sound'); ?></span>
            </label>
            <p class="description">
                <?php esc_html_e('Upload your own custom completion sound.', 'lp-lesson-completion-sound'); ?>
            </p>
            <div class="lp-lcs-custom-sound-upload">
                <input type="file" name="lp_lcs_custom_sound_file" accept="audio/*" disabled>
                <button type="button" class="lp-lcs-upload-btn" disabled>
                    <?php esc_html_e('Upload Sound', 'lp-lesson-completion-sound'); ?>
                </button>
            </div>
        </div>

        <!-- Save Button -->
        <div class="lp-lcs-setting-row">
            <button type="submit" name="lp_lcs_save_settings" class="lp-lcs-save-button">
                <?php esc_html_e('Save Settings', 'lp-lesson-completion-sound'); ?>
            </button>
        </div>
    </form>
</div>