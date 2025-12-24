<?php
/**
 * Survey Popup Template
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="lp-survey-popup" class="lp-survey-popup" style="display: none;">
    <div class="lp-survey-overlay"></div>
    <div class="lp-survey-modal">
        <div class="lp-survey-header">
            <h3><?php esc_html_e('Your Feedback Matters!', 'lp-survey'); ?></h3>
            <p><?php esc_html_e('Please take a moment to share your thoughts.', 'lp-survey'); ?></p>
        </div>

        <div class="lp-survey-body">
            <form id="lp-survey-form">
                <input type="hidden" name="survey_id" id="lp-survey-id">

                <div id="lp-survey-questions"></div>

                <div class="lp-survey-actions">
                    <button type="submit" class="lp-survey-submit-btn">
                        <?php esc_html_e('Submit Feedback', 'lp-survey'); ?>
                    </button>
                    <?php if (LP_Survey_Helpers::can_skip_survey()): ?>
                        <button type="button" class="lp-survey-skip-btn">
                            <?php esc_html_e('Skip', 'lp-survey'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </form>

            <div id="lp-survey-success" style="display: none;">
                <div class="lp-survey-success-icon">✓</div>
                <h3><?php esc_html_e('Thank You!', 'lp-survey'); ?></h3>
                <p><?php esc_html_e('Your feedback has been submitted successfully.', 'lp-survey'); ?></p>
            </div>
        </div>
    </div>
</div>