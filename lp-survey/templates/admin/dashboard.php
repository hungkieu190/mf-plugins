<?php
/**
 * Admin Dashboard Template
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$stats = LP_Survey_Dashboard::get_overview_stats();
$recent_responses = LP_Survey_Dashboard::get_recent_responses(20);
?>

<div class="wrap lp-survey-dashboard">
    <h1><?php esc_html_e('Survey Dashboard', 'lp-survey'); ?></h1>

    <!-- Overview Stats -->
    <div class="lp-survey-stats">
        <div class="lp-survey-stat-box">
            <h3><?php echo esc_html($stats['total_surveys']); ?></h3>
            <p><?php esc_html_e('Total Surveys', 'lp-survey'); ?></p>
        </div>
        <div class="lp-survey-stat-box">
            <h3><?php echo esc_html($stats['total_responses']); ?></h3>
            <p><?php esc_html_e('Total Responses', 'lp-survey'); ?></p>
        </div>
        <div class="lp-survey-stat-box">
            <h3><?php echo esc_html($stats['average_rating']); ?> ⭐</h3>
            <p><?php esc_html_e('Average Rating', 'lp-survey'); ?></p>
        </div>
        <div class="lp-survey-stat-box">
            <h3><?php echo esc_html($stats['recent_responses']); ?></h3>
            <p><?php esc_html_e('Last 30 Days', 'lp-survey'); ?></p>
        </div>
    </div>

    <!-- Export Button -->
    <div class="lp-survey-actions" style="margin: 20px 0;">
        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mf_lp_survey_export'), 'lp_survey_export')); ?>"
            class="button button-primary">
            <?php esc_html_e('Export to CSV', 'lp-survey'); ?>
        </a>
    </div>

    <!-- Recent Responses Table -->
    <h2><?php esc_html_e('Recent Responses', 'lp-survey'); ?></h2>

    <?php if (empty($recent_responses)): ?>
        <p><?php esc_html_e('No survey responses yet.', 'lp-survey'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date', 'lp-survey'); ?></th>
                    <th><?php esc_html_e('User', 'lp-survey'); ?></th>
                    <th><?php esc_html_e('Survey', 'lp-survey'); ?></th>
                    <th><?php esc_html_e('Question', 'lp-survey'); ?></th>
                    <th><?php esc_html_e('Answer', 'lp-survey'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_responses as $response):
                    $user = get_userdata($response->user_id);
                    $item_title = '';
                    if ($response->survey_type === 'lesson') {
                        $item_title = LP_Survey_Helpers::get_lesson_title($response->ref_id);
                    } elseif ($response->survey_type === 'course') {
                        $item_title = LP_Survey_Helpers::get_course_title($response->ref_id);
                    }
                    ?>
                    <tr>
                        <td><?php echo esc_html(LP_Survey_Helpers::format_date($response->created_at)); ?></td>
                        <td><?php echo esc_html($user ? $user->display_name : 'N/A'); ?></td>
                        <td>
                            <strong><?php echo esc_html(ucfirst($response->survey_type)); ?>:</strong>
                            <?php echo esc_html($item_title); ?>
                        </td>
                        <td><?php echo esc_html(wp_trim_words($response->question, 10)); ?></td>
                        <td>
                            <?php if ($response->question_type === 'rating'): ?>
                                <?php
                                $stars = str_repeat('⭐', intval($response->answer));
                                echo esc_html($stars . ' (' . $response->answer . '/5)');
                                ?>
                            <?php else: ?>
                                <?php echo esc_html(wp_trim_words($response->answer, 15)); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>