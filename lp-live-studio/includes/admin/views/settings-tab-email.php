<?php
/**
 * Email Templates Settings Tab
 *
 * @package lp-live-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

$reminder_subject = get_option('mf_lls_email_reminder_subject', __('Live Session Starting Soon: {lesson_title}', 'lp-live-studio'));
$reminder_body = get_option('mf_lls_email_reminder_body', '');
$rating_subject = get_option('mf_lls_email_rating_subject', __('Rate Your Recent Live Session', 'lp-live-studio'));
$rating_body = get_option('mf_lls_email_rating_body', '');

// Default templates
if (empty($reminder_body)) {
    $reminder_body = "Hi {student_name},\n\nYour live session is starting soon!\n\nCourse: {course_title}\nLesson: {lesson_title}\nStart Time: {start_time}\n\nJoin URL: {join_url}\n\nSee you there!";
}

if (empty($rating_body)) {
    $rating_body = "Hi {student_name},\n\nThank you for attending the live session: {lesson_title}\n\nWe'd love to hear your feedback! Please take a moment to rate the session and your tutor.\n\nRate now: {rating_url}\n\nBest regards,\n{site_name}";
}
?>

<table class="form-table">
    <tbody>
        <tr>
            <td colspan="2">
                <h3>
                    <?php esc_html_e('Reminder Email Template', 'lp-live-studio'); ?>
                </h3>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_email_reminder_subject">
                    <?php esc_html_e('Subject', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="text" name="mf_lls_email_reminder_subject" id="mf_lls_email_reminder_subject"
                    value="<?php echo esc_attr($reminder_subject); ?>" class="large-text">
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_email_reminder_body">
                    <?php esc_html_e('Body', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <textarea name="mf_lls_email_reminder_body" id="mf_lls_email_reminder_body" rows="10"
                    class="large-text"><?php echo esc_textarea($reminder_body); ?></textarea>
                <p class="description">
                    <?php esc_html_e('Available variables:', 'lp-live-studio'); ?>
                    <code>{student_name}</code>, <code>{lesson_title}</code>, <code>{course_title}</code>,
                    <code>{start_time}</code>, <code>{join_url}</code>, <code>{site_name}</code>
                </p>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <hr>
                <h3>
                    <?php esc_html_e('Rating Request Email Template', 'lp-live-studio'); ?>
                </h3>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_email_rating_subject">
                    <?php esc_html_e('Subject', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <input type="text" name="mf_lls_email_rating_subject" id="mf_lls_email_rating_subject"
                    value="<?php echo esc_attr($rating_subject); ?>" class="large-text">
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mf_lls_email_rating_body">
                    <?php esc_html_e('Body', 'lp-live-studio'); ?>
                </label>
            </th>
            <td>
                <textarea name="mf_lls_email_rating_body" id="mf_lls_email_rating_body" rows="10"
                    class="large-text"><?php echo esc_textarea($rating_body); ?></textarea>
                <p class="description">
                    <?php esc_html_e('Available variables:', 'lp-live-studio'); ?>
                    <code>{student_name}</code>, <code>{lesson_title}</code>, <code>{tutor_name}</code>,
                    <code>{rating_url}</code>, <code>{site_name}</code>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"></th>
            <td>
                <button type="button" class="button button-secondary mf-lls-send-test-email">
                    <?php esc_html_e('Send Test Email', 'lp-live-studio'); ?>
                </button>
                <p class="description">
                    <?php esc_html_e('Send a test email to your admin email address.', 'lp-live-studio'); ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>