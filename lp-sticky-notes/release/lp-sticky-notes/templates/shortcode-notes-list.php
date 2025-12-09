<?php
/**
 * Shortcode template for displaying notes
 *
 * @package LP_Sticky_Notes
 */

defined('ABSPATH') || exit();

$show_course = isset($atts['show_course']) && $atts['show_course'] === 'yes';
$show_lesson = isset($atts['show_lesson']) && $atts['show_lesson'] === 'yes';
?>

<div class="lp-shortcode-notes-list">
    <?php if (empty($notes)): ?>
        <div class="lp-no-notes-shortcode">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                    stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" opacity="0.5" />
            </svg>
            <p><?php esc_html_e('No notes found.', 'lp-sticky-notes'); ?></p>
        </div>
    <?php else: ?>
        <div class="lp-notes-grid">
            <?php foreach ($notes as $note): ?>
                <div class="lp-note-card">
                    <div class="lp-note-card-header">
                        <span class="lp-note-type-badge note-type-<?php echo esc_attr($note->note_type); ?>">
                            <?php echo $note->note_type === 'highlight' ? esc_html__('Highlight', 'lp-sticky-notes') : esc_html__('Text', 'lp-sticky-notes'); ?>
                        </span>
                        <span class="lp-note-date">
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($note->created_at))); ?>
                        </span>
                    </div>

                    <?php if ($show_course && !empty($note->course_title)): ?>
                        <div class="lp-note-course">
                            <strong><?php esc_html_e('Course:', 'lp-sticky-notes'); ?></strong>
                            <?php echo esc_html($note->course_title); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_lesson && !empty($note->lesson_title)): ?>
                        <div class="lp-note-lesson">
                            <strong><?php esc_html_e('Lesson:', 'lp-sticky-notes'); ?></strong>
                            <a href="<?php echo esc_url(get_permalink($note->lesson_id)); ?>">
                                <?php echo esc_html($note->lesson_title); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($note->note_type === 'highlight' && !empty($note->highlight_text)): ?>
                        <div class="lp-note-highlight">
                            <strong><?php esc_html_e('Highlighted:', 'lp-sticky-notes'); ?></strong>
                            <div class="lp-highlight-text"><?php echo wp_kses_post($note->highlight_text); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="lp-note-content">
                        <?php echo wp_kses_post(wpautop($note->content)); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .lp-shortcode-notes-list {
        margin: 20px 0;
    }

    .lp-no-notes-shortcode {
        text-align: center;
        padding: 40px 20px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .lp-no-notes-shortcode svg {
        color: #9ca3af;
        margin-bottom: 10px;
    }

    .lp-no-notes-shortcode p {
        color: #6b7280;
        margin: 0;
        font-size: 16px;
    }

    .lp-notes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .lp-note-card {
        background: #fefce8;
        border: 1px solid #fbbf24;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .lp-note-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .lp-note-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .lp-note-type-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .lp-note-type-badge.note-type-text {
        background: #dbeafe;
        color: #1e40af;
    }

    .lp-note-type-badge.note-type-highlight {
        background: #fef3c7;
        color: #92400e;
    }

    .lp-note-date {
        font-size: 12px;
        color: #78716c;
    }

    .lp-note-course,
    .lp-note-lesson {
        margin-bottom: 10px;
        font-size: 14px;
        color: #57534e;
    }

    .lp-note-course strong,
    .lp-note-lesson strong {
        display: block;
        margin-bottom: 4px;
        color: #292524;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .lp-note-lesson a {
        color: #2563eb;
        text-decoration: none;
    }

    .lp-note-lesson a:hover {
        text-decoration: underline;
    }

    .lp-note-highlight {
        margin-bottom: 15px;
        padding: 12px;
        background: #fef3c7;
        border-left: 3px solid #f59e0b;
        border-radius: 4px;
    }

    .lp-note-highlight strong {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        color: #92400e;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .lp-highlight-text {
        font-style: italic;
        color: #78350f;
        line-height: 1.6;
    }

    .lp-note-content {
        color: #44403c;
        line-height: 1.8;
        font-size: 14px;
    }

    .lp-note-content p:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .lp-notes-grid {
            grid-template-columns: 1fr;
        }
    }
</style>