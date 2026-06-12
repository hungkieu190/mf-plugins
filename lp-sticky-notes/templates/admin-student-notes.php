<?php
/**
 * Admin page template for viewing student notes
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();

$has_active_filters = $student_id > 0 || $course_id > 0 || $lesson_id > 0;
$current_start = $total_notes > 0 ? (($paged - 1) * $per_page) + 1 : 0;
$current_end = $total_notes > 0 ? min($paged * $per_page, $total_notes) : 0;
$selected_student_label = __('All students', 'lp-sticky-notes');
$selected_course_label = __('All courses', 'lp-sticky-notes');

foreach ($students as $student) {
    if ((int) $student->ID === (int) $student_id) {
        $selected_student_label = sprintf('%s (%s)', $student->display_name, $student->user_email);
        break;
    }
}

foreach ($courses as $course) {
    if ((int) $course->ID === (int) $course_id) {
        $selected_course_label = $course->post_title;
        break;
    }
}
?>

<div class="wrap lp-sticky-notes-admin">
    <div class="lp-admin-header">
        <div>
            <h1><?php esc_html_e('Student Notes', 'lp-sticky-notes'); ?></h1>
            <p><?php esc_html_e('Review notes saved by students across LearnPress courses and lessons.', 'lp-sticky-notes'); ?></p>
        </div>
    </div>

    <div class="lp-admin-context">
        <div class="lp-context-item">
            <span class="lp-context-label"><?php esc_html_e('Total notes', 'lp-sticky-notes'); ?></span>
            <strong><?php echo esc_html(number_format_i18n($total_notes)); ?></strong>
        </div>
        <div class="lp-context-item">
            <span class="lp-context-label"><?php esc_html_e('Students with notes', 'lp-sticky-notes'); ?></span>
            <strong><?php echo esc_html(number_format_i18n($student_count)); ?></strong>
        </div>
        <div class="lp-context-item">
            <span class="lp-context-label"><?php esc_html_e('Courses with notes', 'lp-sticky-notes'); ?></span>
            <strong><?php echo esc_html(number_format_i18n($course_count)); ?></strong>
        </div>
    </div>

    <div class="lp-admin-panel lp-admin-filters">
        <div class="lp-panel-heading">
            <h2><?php esc_html_e('Filters', 'lp-sticky-notes'); ?></h2>
            <?php if ($has_active_filters): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=lp-student-notes')); ?>" class="button">
                    <?php esc_html_e('Clear filters', 'lp-sticky-notes'); ?>
                </a>
            <?php endif; ?>
        </div>

        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="hidden" name="page" value="lp-student-notes">

            <div class="filter-row">
                <div class="filter-group lp-combobox" data-action="lp_sticky_notes_search_students" data-empty-label="<?php esc_attr_e('All students', 'lp-sticky-notes'); ?>">
                    <label for="student_filter_search"><?php esc_html_e('Student', 'lp-sticky-notes'); ?></label>
                    <input type="hidden" name="student_id" id="student_id" value="<?php echo esc_attr($student_id); ?>">
                    <div class="lp-combobox-control">
                        <input
                            type="search"
                            id="student_filter_search"
                            class="lp-combobox-input"
                            value="<?php echo esc_attr($selected_student_label); ?>"
                            data-selected-label="<?php echo esc_attr($selected_student_label); ?>"
                            autocomplete="off"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            placeholder="<?php esc_attr_e('Search students by name or email', 'lp-sticky-notes'); ?>"
                        >
                        <button type="button" class="lp-combobox-clear" aria-label="<?php esc_attr_e('Clear student filter', 'lp-sticky-notes'); ?>">&times;</button>
                    </div>
                    <div class="lp-combobox-options" role="listbox">
                        <button type="button" class="lp-combobox-option" data-value="0" data-label="<?php esc_attr_e('All students', 'lp-sticky-notes'); ?>">
                            <?php esc_html_e('All students', 'lp-sticky-notes'); ?>
                        </button>
                        <?php foreach ($students as $student): ?>
                            <button type="button" class="lp-combobox-option" data-value="<?php echo esc_attr($student->ID); ?>" data-label="<?php echo esc_attr(sprintf('%s (%s)', $student->display_name, $student->user_email)); ?>">
                                <?php echo esc_html($student->display_name); ?>
                                <small><?php echo esc_html($student->user_email); ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="filter-help"><?php esc_html_e('Search by student name or email.', 'lp-sticky-notes'); ?></p>
                </div>

                <div class="filter-group lp-combobox" data-action="lp_sticky_notes_search_courses" data-empty-label="<?php esc_attr_e('All courses', 'lp-sticky-notes'); ?>">
                    <label for="course_filter_search"><?php esc_html_e('Course', 'lp-sticky-notes'); ?></label>
                    <input type="hidden" name="course_id" id="course_id" value="<?php echo esc_attr($course_id); ?>">
                    <div class="lp-combobox-control">
                        <input
                            type="search"
                            id="course_filter_search"
                            class="lp-combobox-input"
                            value="<?php echo esc_attr($selected_course_label); ?>"
                            data-selected-label="<?php echo esc_attr($selected_course_label); ?>"
                            autocomplete="off"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            placeholder="<?php esc_attr_e('Search courses', 'lp-sticky-notes'); ?>"
                        >
                        <button type="button" class="lp-combobox-clear" aria-label="<?php esc_attr_e('Clear course filter', 'lp-sticky-notes'); ?>">&times;</button>
                    </div>
                    <div class="lp-combobox-options" role="listbox">
                        <button type="button" class="lp-combobox-option" data-value="0" data-label="<?php esc_attr_e('All courses', 'lp-sticky-notes'); ?>">
                            <?php esc_html_e('All courses', 'lp-sticky-notes'); ?>
                        </button>
                        <?php foreach ($courses as $course): ?>
                            <button type="button" class="lp-combobox-option" data-value="<?php echo esc_attr($course->ID); ?>" data-label="<?php echo esc_attr($course->post_title); ?>">
                                <?php echo esc_html($course->post_title); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="filter-help"><?php esc_html_e('Search by course title.', 'lp-sticky-notes'); ?></p>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Apply filters', 'lp-sticky-notes'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=lp-student-notes')); ?>" class="button">
                        <?php esc_html_e('Reset', 'lp-sticky-notes'); ?>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($notes)): ?>
        <div class="lp-empty-state">
            <h2><?php esc_html_e('No notes found', 'lp-sticky-notes'); ?></h2>
            <p>
                <?php
                echo $has_active_filters
                    ? esc_html__('No student notes match the selected filters. Clear filters or choose a broader course or student.', 'lp-sticky-notes')
                    : esc_html__('Student notes will appear here after learners save notes inside lessons.', 'lp-sticky-notes');
                ?>
            </p>
            <?php if ($has_active_filters): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=lp-student-notes')); ?>" class="button button-primary">
                    <?php esc_html_e('Clear filters', 'lp-sticky-notes'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="lp-table-toolbar">
            <div class="lp-table-summary">
                <?php
                printf(
                    esc_html__('Showing %1$s-%2$s of %3$s notes', 'lp-sticky-notes'),
                    esc_html(number_format_i18n($current_start)),
                    esc_html(number_format_i18n($current_end)),
                    esc_html(number_format_i18n($total_notes))
                );
                ?>
            </div>
            <label class="lp-table-search" for="lp-note-search">
                <span><?php esc_html_e('Search current page', 'lp-sticky-notes'); ?></span>
                <input type="search" id="lp-note-search" placeholder="<?php esc_attr_e('Student, course, lesson, note...', 'lp-sticky-notes'); ?>">
            </label>
        </div>

        <table class="wp-list-table widefat fixed striped lp-notes-table">
            <thead>
                <tr>
                    <th scope="col" class="sortable" data-sort="text"><?php esc_html_e('Student', 'lp-sticky-notes'); ?></th>
                    <th scope="col" class="sortable" data-sort="text"><?php esc_html_e('Course', 'lp-sticky-notes'); ?></th>
                    <th scope="col" class="sortable" data-sort="text"><?php esc_html_e('Lesson', 'lp-sticky-notes'); ?></th>
                    <th scope="col" class="sortable" data-sort="text"><?php esc_html_e('Type', 'lp-sticky-notes'); ?></th>
                    <th scope="col"><?php esc_html_e('Content', 'lp-sticky-notes'); ?></th>
                    <th scope="col" class="sortable" data-sort="date"><?php esc_html_e('Created', 'lp-sticky-notes'); ?></th>
                    <th scope="col"><?php esc_html_e('Actions', 'lp-sticky-notes'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $note): ?>
                    <tr class="lp-note-row">
                        <td data-sort-value="<?php echo esc_attr($note->student_name); ?>">
                            <strong><?php echo esc_html($note->student_name); ?></strong>
                            <small><?php echo esc_html($note->student_email); ?></small>
                        </td>
                        <td data-sort-value="<?php echo esc_attr($note->course_title); ?>"><?php echo esc_html($note->course_title); ?></td>
                        <td data-sort-value="<?php echo esc_attr($note->lesson_title); ?>">
                            <a href="<?php echo esc_url(lp_sticky_notes_get_lesson_url($note->lesson_id, $note->course_id)); ?>" target="_blank">
                                <?php echo esc_html($note->lesson_title); ?>
                            </a>
                        </td>
                        <td data-sort-value="<?php echo esc_attr($note->note_type); ?>">
                            <span class="note-type-badge note-type-<?php echo esc_attr($note->note_type); ?>">
                                <?php echo $note->note_type === 'highlight' ? esc_html__('Highlight', 'lp-sticky-notes') : esc_html__('Text', 'lp-sticky-notes'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($note->note_type === 'highlight' && !empty($note->highlight_text)): ?>
                                <div class="highlight-preview">
                                    <strong><?php esc_html_e('Highlighted text', 'lp-sticky-notes'); ?></strong>
                                    <div class="highlight-text">
                                        <?php echo wp_kses_post(wp_trim_words($note->highlight_text, 10)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="note-content-preview">
                                <?php echo wp_kses_post(wp_trim_words($note->content, 20)); ?>
                            </div>
                            <button class="button button-small view-full-note" data-note-id="<?php echo esc_attr($note->id); ?>">
                                <?php esc_html_e('View full', 'lp-sticky-notes'); ?>
                            </button>
                        </td>
                        <td data-sort-value="<?php echo esc_attr(strtotime($note->created_at)); ?>">
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($note->created_at))); ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(lp_sticky_notes_get_lesson_url($note->lesson_id, $note->course_id)); ?>" class="button button-small" target="_blank">
                                <?php esc_html_e('Open lesson', 'lp-sticky-notes'); ?>
                            </a>
                        </td>
                    </tr>

                    <tr class="full-note-content" id="note-content-<?php echo esc_attr($note->id); ?>">
                        <td colspan="7">
                            <div class="note-full-detail">
                                <?php if ($note->note_type === 'highlight' && !empty($note->highlight_text)): ?>
                                    <div class="note-highlight-full">
                                        <strong><?php esc_html_e('Highlighted text', 'lp-sticky-notes'); ?></strong>
                                        <div class="highlight-text-full"><?php echo wp_kses_post($note->highlight_text); ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="note-content-full">
                                    <strong><?php esc_html_e('Note', 'lp-sticky-notes'); ?></strong>
                                    <?php echo wp_kses_post(wpautop($note->content)); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <?php
            $current_url = remove_query_arg('paged');
            $range = 2;
            $start_page = max(1, $paged - $range);
            $end_page = min($total_pages, $paged + $range);
            ?>
            <div class="lp-pagination">
                <div class="pagination-info">
                    <?php
                    printf(
                        esc_html__('Page %1$s of %2$s', 'lp-sticky-notes'),
                        esc_html(number_format_i18n($paged)),
                        esc_html(number_format_i18n($total_pages))
                    );
                    ?>
                </div>
                <div class="pagination-links">
                    <?php if ($paged > 1): ?>
                        <a href="<?php echo esc_url(add_query_arg('paged', $paged - 1, $current_url)); ?>" class="page-link prev">
                            <?php esc_html_e('Previous', 'lp-sticky-notes'); ?>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i === $paged): ?>
                            <span class="page-link current" aria-current="page"><?php echo esc_html(number_format_i18n($i)); ?></span>
                        <?php else: ?>
                            <a href="<?php echo esc_url(add_query_arg('paged', $i, $current_url)); ?>" class="page-link">
                                <?php echo esc_html(number_format_i18n($i)); ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($paged < $total_pages): ?>
                        <a href="<?php echo esc_url(add_query_arg('paged', $paged + 1, $current_url)); ?>" class="page-link next">
                            <?php esc_html_e('Next', 'lp-sticky-notes'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
