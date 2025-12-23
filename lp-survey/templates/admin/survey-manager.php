<?php
/**
 * Survey Manager Template
 *
 * @package LP_Survey
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get all surveys
$surveys = LP_Survey_Database::mf_get_all_surveys();

// Get selected survey ID
$selected_survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;

// If no survey selected but surveys exist, select the first one
if ($selected_survey_id === 0 && !empty($surveys)) {
    $selected_survey_id = $surveys[0]->id;
}

// Get questions for selected survey
$questions = array();
$selected_survey = null;
if ($selected_survey_id > 0) {
    $questions = LP_Survey_Database::get_questions($selected_survey_id);
    foreach ($surveys as $survey) {
        if ($survey->id == $selected_survey_id) {
            $selected_survey = $survey;
            break;
        }
    }
}
?>

<div class="wrap lp-survey-manager">
    <h1><?php esc_html_e('Survey Questions Manager', 'lp-survey'); ?></h1>

    <?php if (empty($surveys)): ?>
        <div class="notice notice-warning">
            <p><?php esc_html_e('No surveys found. Please activate the plugin to create default surveys.', 'lp-survey'); ?>
            </p>
        </div>
    <?php else: ?>

        <!-- Survey Selector -->
        <div class="lp-survey-selector" style="margin: 20px 0;">
            <label for="survey-select">
                <strong><?php esc_html_e('Select Survey:', 'lp-survey'); ?></strong>
            </label>
            <select id="survey-select" name="survey_id" style="margin-left: 10px;">
                <?php foreach ($surveys as $survey): ?>
                    <?php
                    $survey_label = '';
                    if ($survey->ref_id == 0) {
                        $survey_label = sprintf(
                            __('Default %s Survey', 'lp-survey'),
                            ucfirst($survey->type)
                        );
                    } else {
                        $item_title = $survey->type === 'lesson'
                            ? LP_Survey_Helpers::get_lesson_title($survey->ref_id)
                            : LP_Survey_Helpers::get_course_title($survey->ref_id);
                        $survey_label = sprintf(
                            __('%s: %s', 'lp-survey'),
                            ucfirst($survey->type),
                            $item_title
                        );
                    }
                    ?>
                    <option value="<?php echo esc_attr($survey->id); ?>" <?php selected($selected_survey_id, $survey->id); ?>>
                        <?php echo esc_html($survey_label); ?> (<?php echo esc_html($survey->question_count); ?>
                        <?php esc_html_e('questions', 'lp-survey'); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($selected_survey): ?>

            <!-- Add New Question Button -->
            <div class="lp-survey-actions" style="margin: 20px 0;">
                <button type="button" class="button button-primary" id="add-question-btn">
                    <?php esc_html_e('+ Add New Question', 'lp-survey'); ?>
                </button>
            </div>

            <!-- Questions List -->
            <div class="lp-survey-questions">
                <h2><?php esc_html_e('Questions', 'lp-survey'); ?></h2>

                <?php if (empty($questions)): ?>
                    <p class="no-questions-msg">
                        <?php esc_html_e('No questions yet. Click "Add New Question" to create one.', 'lp-survey'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;"><?php esc_html_e('Order', 'lp-survey'); ?></th>
                                <th><?php esc_html_e('Question', 'lp-survey'); ?></th>
                                <th style="width: 100px;"><?php esc_html_e('Type', 'lp-survey'); ?></th>
                                <th style="width: 150px;"><?php esc_html_e('Actions', 'lp-survey'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="questions-list" class="ui-sortable">
                            <?php foreach ($questions as $question): ?>
                                <tr data-question-id="<?php echo esc_attr($question->id); ?>">
                                    <td class="drag-handle" style="cursor: move; text-align: center;">
                                        <span class="dashicons dashicons-menu"></span>
                                        <?php echo esc_html($question->question_order); ?>
                                    </td>
                                    <td class="question-content"><?php echo esc_html($question->content); ?></td>
                                    <td class="question-type">
                                        <?php echo esc_html($question->type === 'rating' ? __('Rating', 'lp-survey') : __('Text', 'lp-survey')); ?>
                                    </td>
                                    <td>
                                        <button type="button" class="button button-small edit-question"
                                            data-question-id="<?php echo esc_attr($question->id); ?>">
                                            <?php esc_html_e('Edit', 'lp-survey'); ?>
                                        </button>
                                        <button type="button" class="button button-small button-link-delete delete-question"
                                            data-question-id="<?php echo esc_attr($question->id); ?>">
                                            <?php esc_html_e('Delete', 'lp-survey'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    <?php endif; ?>
</div>

<!-- Add/Edit Question Modal -->
<div id="question-modal" class="lp-survey-modal" style="display: none;">
    <div class="lp-survey-modal-overlay"></div>
    <div class="lp-survey-modal-content">
        <div class="lp-survey-modal-header">
            <h2 id="modal-title"><?php esc_html_e('Add Question', 'lp-survey'); ?></h2>
            <button type="button" class="lp-survey-modal-close">&times;</button>
        </div>
        <div class="lp-survey-modal-body">
            <form id="question-form">
                <input type="hidden" name="survey_id" id="question-survey-id"
                    value="<?php echo esc_attr($selected_survey_id); ?>">
                <input type="hidden" name="question_id" id="question-id" value="">
                <input type="hidden" name="order" id="question-order" value="">

                <p>
                    <label for="question-type">
                        <strong><?php esc_html_e('Question Type:', 'lp-survey'); ?></strong>
                    </label><br>
                    <select name="type" id="question-type" class="widefat">
                        <option value="rating"><?php esc_html_e('Rating (1-5 stars)', 'lp-survey'); ?></option>
                        <option value="text"><?php esc_html_e('Text Response', 'lp-survey'); ?></option>
                    </select>
                </p>

                <p>
                    <label for="question-content">
                        <strong><?php esc_html_e('Question Content:', 'lp-survey'); ?></strong>
                    </label><br>
                    <textarea name="content" id="question-content" class="widefat" rows="4" required></textarea>
                </p>

                <p class="submit-actions">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Question', 'lp-survey'); ?>
                    </button>
                    <button type="button" class="button cancel-btn">
                        <?php esc_html_e('Cancel', 'lp-survey'); ?>
                    </button>
                </p>
            </form>
        </div>
    </div>
</div>