<?php
/**
 * Admin page template for viewing student notes
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();
?>

<div class="wrap lp-sticky-notes-admin">
    <h1><?php esc_html_e('Student Notes', 'lp-sticky-notes'); ?></h1>

    <style>
        .lp-admin-filters {
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
        }

        .lp-admin-filters .filter-row {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            flex-wrap: wrap;
        }

        .lp-admin-filters .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .lp-admin-filters .filter-group:last-child {
            flex: 0 0 auto;
            display: flex;
            flex-direction: row;
            /* Explicitly set row direction */
            gap: 10px;
            align-items: center;
        }

        .lp-admin-filters label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #1d2327;
            font-size: 13px;
        }

        .lp-admin-filters select {
            width: 100%;
            height: 36px;
            padding: 0 10px;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            font-size: 14px;
            background-color: #fff;
            color: #2c3338;
        }

        .lp-admin-filters select:focus {
            border-color: #2271b1;
            outline: 1px solid #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
        }

        .lp-admin-filters .button-primary {
            height: 36px;
            padding: 0 20px;
            font-size: 14px;
            line-height: 34px;
        }

        .lp-admin-filters .button {
            height: 36px;
            padding: 0 16px;
            font-size: 14px;
            line-height: 34px;
        }

        .lp-notes-stats {
            margin: 15px 0;
            padding: 12px 16px;
            background: #f0f6fc;
            border-left: 4px solid #2271b1;
            border-radius: 2px;
        }

        .lp-notes-stats p {
            margin: 0;
            font-size: 14px;
            color: #1d2327;
            font-weight: 500;
        }

        .lp-no-notes-admin {
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            padding: 40px 20px;
            text-align: center;
            margin: 20px 0;
        }

        .lp-no-notes-admin p {
            color: #646970;
            font-size: 16px;
            margin: 0;
        }

        /* Pagination Styles */
        .lp-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 15px 20px;
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 4px;
        }
        .lp-pagination .pagination-info {
            color: #646970;
            font-size: 13px;
            font-weight: 500;
        }
        .lp-pagination .pagination-links {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .lp-pagination .page-link {
            display: inline-block;
            padding: 6px 12px;
            min-width: 36px;
            text-align: center;
            border: 1px solid #dcdcde;
            border-radius: 3px;
            text-decoration: none;
            font-size: 13px;
            background: #fff;
            color: #2271b1;
            transition: all 0.2s ease;
        }
        .lp-pagination .page-link:hover {
            background: #f6f7f7;
            border-color: #2271b1;
        }
        .lp-pagination .page-link.current {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
            font-weight: 600;
            cursor: default;
        }
        .lp-pagination .page-link.prev,
        .lp-pagination .page-link.next {
            padding: 6px 14px;
        }
    </style>

    <div class="lp-admin-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="lp-student-notes">

            <div class="filter-row">
                <div class="filter-group">
                    <label for="student_id"><?php esc_html_e('Student:', 'lp-sticky-notes'); ?></label>
                    <select name="student_id" id="student_id">
                        <option value="0"><?php esc_html_e('All Students', 'lp-sticky-notes'); ?></option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo esc_attr($student->ID); ?>" <?php selected($student_id, $student->ID); ?>>
                                <?php echo esc_html($student->display_name); ?>
                                (<?php echo esc_html($student->user_email); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="course_id"><?php esc_html_e('Course:', 'lp-sticky-notes'); ?></label>
                    <select name="course_id" id="course_id">
                        <option value="0"><?php esc_html_e('All Courses', 'lp-sticky-notes'); ?></option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($course_id, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Filter', 'lp-sticky-notes'); ?>
                    </button>
                    <a href="?page=lp-student-notes" class="button">
                        <?php esc_html_e('Reset', 'lp-sticky-notes'); ?>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($notes)): ?>
        <div class="lp-no-notes-admin">
            <p><?php esc_html_e('No notes found.', 'lp-sticky-notes'); ?></p>
        </div>
    <?php else: ?>
        <div class="lp-notes-stats">
            <p><?php printf(esc_html__('Showing %d notes', 'lp-sticky-notes'), count($notes)); ?></p>
        </div>

        <table class="wp-list-table widefat fixed striped lp-notes-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Student', 'lp-sticky-notes'); ?></th>
                    <th><?php esc_html_e('Course', 'lp-sticky-notes'); ?></th>
                    <th><?php esc_html_e('Lesson', 'lp-sticky-notes'); ?></th>
                    <th><?php esc_html_e('Type', 'lp-sticky-notes'); ?></th>
                    <th><?php esc_html_e('Content', 'lp-sticky-notes'); ?></th>
                    <th><?php esc_html_e('Created', 'lp-sticky-notes'); ?></th>
                    <th><?php esc_html_e('Actions', 'lp-sticky-notes'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $note): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($note->student_name); ?></strong><br>
                            <small><?php echo esc_html($note->student_email); ?></small>
                        </td>
                        <td><?php echo esc_html($note->course_title); ?></td>
                        <td>
                            <a href="<?php echo esc_url(get_permalink($note->lesson_id)); ?>" target="_blank">
                                <?php echo esc_html($note->lesson_title); ?>
                            </a>
                        </td>
                        <td>
                            <span class="note-type-badge note-type-<?php echo esc_attr($note->note_type); ?>">
                                <?php echo $note->note_type === 'highlight' ? esc_html__('Highlight', 'lp-sticky-notes') : esc_html__('Text', 'lp-sticky-notes'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($note->note_type === 'highlight' && !empty($note->highlight_text)): ?>
                                <div class="highlight-preview">
                                    <strong><?php esc_html_e('Highlighted:', 'lp-sticky-notes'); ?></strong>
                                    <div class="highlight-text">
                                        <?php echo wp_kses_post(wp_trim_words($note->highlight_text, 10)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="note-content-preview">
                                <?php echo wp_kses_post(wp_trim_words($note->content, 20)); ?>
                            </div>
                            <button class="button button-small view-full-note"
                                data-note-id="<?php echo esc_attr($note->id); ?>">
                                <?php esc_html_e('View Full', 'lp-sticky-notes'); ?>
                            </button>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($note->created_at))); ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(get_permalink($note->lesson_id)); ?>" class="button button-small"
                                target="_blank">
                                <?php esc_html_e('Go to Lesson', 'lp-sticky-notes'); ?>
                            </a>
                        </td>
                    </tr>

                    <!-- Hidden full note content for modal -->
                    <tr class="full-note-content" id="note-content-<?php echo esc_attr($note->id); ?>" style="display: none;">
                        <td colspan="7">
                            <div class="note-full-detail">
                                <?php if ($note->note_type === 'highlight' && !empty($note->highlight_text)): ?>
                                    <div class="note-highlight-full">
                                        <strong><?php esc_html_e('Highlighted Text:', 'lp-sticky-notes'); ?></strong>
                                        <div class="highlight-text-full"><?php echo wp_kses_post($note->highlight_text); ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="note-content-full">
                                    <strong><?php esc_html_e('Note:', 'lp-sticky-notes'); ?></strong>
                                    <?php echo wp_kses_post(wpautop($note->content)); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        // Simple pagination after table
        if ($total_pages > 1):
            $current_url = remove_query_arg('paged');
            $range = 2; // Number of pages to show on each side of current page
            ?>
            <div class="lp-pagination">
                <div class="pagination-info">
                    <?php printf(esc_html__('Total: %s items', 'lp-sticky-notes'), number_format_i18n($total_notes)); ?>
                </div>
                <div class="pagination-links">
                    <?php
                    // Calculate page range
                    $start_page = max(1, $paged - $range);
                    $end_page = min($total_pages, $paged + $range);

                    // Previous button
                    if ($paged > 1):
                        ?>
                        <a href="<?php echo esc_url(add_query_arg('paged', $paged - 1, $current_url)); ?>" class="page-link prev">
                            <?php esc_html_e('‹ Previous', 'lp-sticky-notes'); ?>
                        </a>
                    <?php endif; ?>

                    <?php
                    // Page numbers
                    for ($i = $start_page; $i <= $end_page; $i++):
                        if ($i == $paged):
                            ?>
                            <span class="page-link current"><?php echo esc_html($i); ?></span>
                        <?php else: ?>
                            <a href="<?php echo esc_url(add_query_arg('paged', $i, $current_url)); ?>" class="page-link">
                                <?php echo esc_html($i); ?>
                            </a>
                        <?php endif;
                    endfor;
                    ?>

                    <?php
                    // Next button
                    if ($paged < $total_pages):
                        ?>
                        <a href="<?php echo esc_url(add_query_arg('paged', $paged + 1, $current_url)); ?>" class="page-link next">
                            <?php esc_html_e('Next ›', 'lp-sticky-notes'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>