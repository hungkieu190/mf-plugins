<?php
/**
 * Admin Settings Template
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$enable_lesson = get_option('mf_lp_survey_enable_lesson_survey', 'yes');
$enable_course = get_option('mf_lp_survey_enable_course_survey', 'yes');
$display_type = get_option('mf_lp_survey_display_type', 'popup');
$allow_skip = get_option('mf_lp_survey_allow_skip', 'yes');
$max_questions = get_option('mf_lp_survey_max_questions', 5);
?>

<div class="wrap lp-survey-settings">
    <h1><?php esc_html_e('Survey Settings', 'lp-survey'); ?></h1>

    <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved successfully.', 'lp-survey'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('lp_survey_settings'); ?>
        <input type="hidden" name="action" value="mf_lp_survey_save_settings">

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label><?php esc_html_e('Enable Lesson Survey', 'lp-survey'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_lesson_survey" value="yes" <?php checked($enable_lesson, 'yes'); ?>>
                            <?php esc_html_e('Show survey after lesson completion', 'lp-survey'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label><?php esc_html_e('Enable Course Survey', 'lp-survey'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_course_survey" value="yes" <?php checked($enable_course, 'yes'); ?>>
                            <?php esc_html_e('Show survey after course completion', 'lp-survey'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label><?php esc_html_e('Display Type', 'lp-survey'); ?></label>
                    </th>
                    <td>
                        <select name="display_type">
                            <option value="popup" <?php selected($display_type, 'popup'); ?>>
                                <?php esc_html_e('Popup', 'lp-survey'); ?>
                            </option>
                            <option value="inline" <?php selected($display_type, 'inline'); ?>>
                                <?php esc_html_e('Inline', 'lp-survey'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php esc_html_e('How the survey should be displayed to users.', 'lp-survey'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label><?php esc_html_e('Allow Skip', 'lp-survey'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="allow_skip" value="yes" <?php checked($allow_skip, 'yes'); ?>>
                            <?php esc_html_e('Allow users to skip survey', 'lp-survey'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label><?php esc_html_e('Maximum Questions', 'lp-survey'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="max_questions" value="<?php echo esc_attr($max_questions); ?>"
                            min="1" max="10" class="small-text">
                        <p class="description">
                            <?php esc_html_e('Maximum number of questions to show in a survey (1-10).', 'lp-survey'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(__('Save Settings', 'lp-survey')); ?>
    </form>
</div>