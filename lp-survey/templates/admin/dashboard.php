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

$per_page = 20;
$current_page = max(1, absint($_GET['paged'] ?? 1));
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
$item_id = isset($_GET['item_id']) ? absint($_GET['item_id']) : 0;
$offset = ($current_page - 1) * $per_page;

$stats = LP_Survey_Dashboard::get_overview_stats();

// Determine view mode
if (empty($active_tab)) {
    $view_mode = 'recent';
} elseif ($item_id > 0) {
    $view_mode = 'detail';
} else {
    $view_mode = 'grouped';
}

// Fetch data based on mode
if ($view_mode === 'recent') {
    $total_items = LP_Survey_Dashboard::get_total_responses_count();
    $data_list = LP_Survey_Dashboard::get_recent_responses($per_page, $offset);
} elseif ($view_mode === 'grouped') {
    $total_items = LP_Survey_Dashboard::get_total_grouped_items_count($active_tab);
    $data_list = LP_Survey_Dashboard::get_grouped_items_stats($active_tab, $per_page, $offset);
} else {
    $total_items = LP_Survey_Dashboard::get_total_responses_count($active_tab, $item_id);
    $data_list = LP_Survey_Dashboard::get_recent_responses($per_page, $offset, $active_tab, $item_id);

    // Get item stats for detail header
    $item_title = ($active_tab === 'lesson') ? LP_Survey_Helpers::get_lesson_title($item_id) : LP_Survey_Helpers::get_course_title($item_id);
    $item_avg_rating = 0;
    $rating_count = 0;
    foreach ($data_list as $row) {
        if ($row->question_type === 'rating') {
            $item_avg_rating += intval($row->answer);
            $rating_count++;
        }
    }
    $item_avg_rating = $rating_count > 0 ? round($item_avg_rating / $rating_count, 1) : 0;
}

$total_pages = ceil($total_items / $per_page);
?>

<div class="wrap lp-survey-dashboard">
    <h1><?php esc_html_e('Survey Dashboard', 'lp-survey'); ?></h1>

    <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('All survey data has been reset successfully.', 'lp-survey'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Overview Stats -->
    <?php if ($view_mode === 'recent'): ?>
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
    <?php elseif ($view_mode === 'detail'): ?>
        <div class="lp-survey-stats">
            <div class="lp-survey-stat-box">
                <h3><?php echo esc_html($item_avg_rating); ?> ⭐</h3>
                <p><?php esc_html_e('Average Rating', 'lp-survey'); ?></p>
            </div>
            <div class="lp-survey-stat-box">
                <h3><?php echo esc_html($total_items); ?></h3>
                <p><?php esc_html_e('Total Answers', 'lp-survey'); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="lp-survey-actions" style="margin: 20px 0; display: flex; gap: 10px; align-items: center;">
        <?php
        $export_url = admin_url('admin-post.php?action=mf_lp_survey_export');
        if (!empty($active_tab)) {
            $export_url = add_query_arg('type', $active_tab, $export_url);
        }
        if ($item_id > 0) {
            $export_url = add_query_arg('item_id', $item_id, $export_url);
        }
        $export_url = wp_nonce_url($export_url, 'lp_survey_export');
        ?>
        <a href="<?php echo esc_url($export_url); ?>" class="button button-primary">
            <?php esc_html_e('Export CSV', 'lp-survey'); ?>
        </a>

        <?php if ($view_mode === 'recent'): ?>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mf_lp_survey_reset'), 'lp_survey_reset')); ?>"
                class="button button-secondary"
                onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete ALL survey responses? This action cannot be undone.', 'lp-survey'); ?>');"
                style="color: #d63638; border-color: #d63638;">
                <?php esc_html_e('Reset All Data', 'lp-survey'); ?>
            </a>
        <?php endif; ?>

        <?php if ($view_mode === 'detail'): ?>
            <a href="<?php echo esc_url(remove_query_arg('item_id')); ?>" class="button">&larr;
                <?php esc_html_e('Back to List', 'lp-survey'); ?></a>
        <?php endif; ?>
    </div>

    <!-- Filter Tabs -->
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo esc_url(remove_query_arg(array('tab', 'paged', 'item_id'))); ?>"
            class="nav-tab <?php echo empty($active_tab) ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Recent Responses', 'lp-survey'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'course', remove_query_arg(array('paged', 'item_id')))); ?>"
            class="nav-tab <?php echo $active_tab === 'course' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('by Course', 'lp-survey'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'lesson', remove_query_arg(array('paged', 'item_id')))); ?>"
            class="nav-tab <?php echo $active_tab === 'lesson' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('by Lesson', 'lp-survey'); ?>
        </a>
    </h2>

    <div style="margin: 20px 0;">
        <?php if ($view_mode === 'detail'): ?>
            <h2><?php echo esc_html($item_title); ?></h2>
        <?php endif; ?>

        <?php if (empty($data_list)): ?>
            <p><?php esc_html_e('No survey responses yet.', 'lp-survey'); ?></p>
        <?php else: ?>

            <?php if ($view_mode === 'grouped'): ?>
                <!-- Grouped View -->
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html(ucfirst($active_tab)); ?></th>
                            <th><?php esc_html_e('Total Responses', 'lp-survey'); ?></th>
                            <th><?php esc_html_e('Average Rating', 'lp-survey'); ?></th>
                            <th style="width: 100px;"><?php esc_html_e('Action', 'lp-survey'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_list as $row):
                            $title = ($active_tab === 'lesson') ? LP_Survey_Helpers::get_lesson_title($row->ref_id) : LP_Survey_Helpers::get_course_title($row->ref_id);
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($title); ?></strong></td>
                                <td><?php echo esc_html($row->total_responses); ?></td>
                                <td>
                                    <?php if ($row->avg_rating): ?>
                                        <?php echo esc_html(round($row->avg_rating, 1)); ?> ⭐
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg('item_id', $row->ref_id)); ?>"
                                        class="button button-small">
                                        <?php esc_html_e('View Detail', 'lp-survey'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <!-- Recent or Detail View -->
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date', 'lp-survey'); ?></th>
                            <th><?php esc_html_e('User', 'lp-survey'); ?></th>
                            <?php if ($view_mode === 'recent'): ?>
                                <th><?php esc_html_e('Item', 'lp-survey'); ?></th>
                            <?php endif; ?>
                            <th><?php esc_html_e('Question', 'lp-survey'); ?></th>
                            <th><?php esc_html_e('Answer', 'lp-survey'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_list as $response):
                            $user = get_userdata($response->user_id);
                            ?>
                            <tr>
                                <td><?php echo esc_html(LP_Survey_Helpers::format_date($response->created_at)); ?></td>
                                <td><?php echo esc_html($user ? $user->display_name : 'N/A'); ?></td>
                                <?php if ($view_mode === 'recent'): ?>
                                    <td>
                                        <?php
                                        $item_title = ($response->survey_type === 'lesson') ? LP_Survey_Helpers::get_lesson_title($response->item_ref_id) : LP_Survey_Helpers::get_course_title($response->item_ref_id);
                                        ?>
                                        <strong><?php echo esc_html(ucfirst($response->survey_type)); ?>:</strong>
                                        <?php echo esc_html($item_title); ?>
                                    </td>
                                <?php endif; ?>
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

            <!-- Pagination -->
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span
                        class="displaying-num"><?php printf(_n('%s item', '%s items', $total_items, 'lp-survey'), number_format_i18n($total_items)); ?></span>
                    <span class="pagination-links">
                        <?php
                        $pagination_args = array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $current_page,
                            'type' => 'plain',
                        );

                        if (!empty($active_tab)) {
                            $pagination_args['base'] = add_query_arg(array('paged' => '%#%', 'tab' => $active_tab), admin_url('admin.php?page=lp-survey-dashboard'));
                        }
                        if ($item_id > 0) {
                            $pagination_args['base'] = add_query_arg(array('paged' => '%#%', 'tab' => $active_tab, 'item_id' => $item_id), admin_url('admin.php?page=lp-survey-dashboard'));
                        }

                        $pagination_links = paginate_links($pagination_args);

                        if ($pagination_links) {
                            echo $pagination_links;
                        } else {
                            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
                            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
                            echo '<span class="paging-input"><span class="tablenav-paging-text"> 1 ' . __('of') . ' <span class="total-pages">1</span> </span></span>';
                            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
                            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
                        }
                        ?>
                    </span>
                </div>
                <br class="clear">
            </div>
        <?php endif; ?>
    </div>
</div>