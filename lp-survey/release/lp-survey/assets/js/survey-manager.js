/**
 * Survey Manager JavaScript
 */
(function ($) {
    'use strict';

    var SurveyManager = {
        /**
         * Initialize
         */
        init: function () {
            this.surveySelector();
            this.addQuestion();
            this.editQuestion();
            this.deleteQuestion();
            this.sortableQuestions();
            this.modalHandlers();
        },

        /**
         * Survey selector change handler
         */
        surveySelector: function () {
            $('#survey-select').on('change', function () {
                var surveyId = $(this).val();
                if (surveyId) {
                    window.location.href = window.location.pathname + '?page=lp-survey-manager&survey_id=' + surveyId;
                }
            });
        },

        /**
         * Add new question
         */
        addQuestion: function () {
            $('#add-question-btn').on('click', function () {
                SurveyManager.openModal('add');
            });

            $('#question-form').on('submit', function (e) {
                e.preventDefault();

                var $form = $(this);
                var questionId = $('#question-id').val();
                var action = questionId ? 'mf_lp_survey_edit_question' : 'mf_lp_survey_add_question';

                var data = {
                    action: action,
                    nonce: lpSurveyManager.nonce,
                    survey_id: $('#question-survey-id').val(),
                    question_id: questionId,
                    type: $('#question-type').val(),
                    content: $('#question-content').val(),
                    order: $('#question-order').val() || 1
                };

                $.ajax({
                    url: lpSurveyManager.ajaxUrl,
                    type: 'POST',
                    data: data,
                    beforeSend: function () {
                        $form.find('.button-primary').prop('disabled', true).text('Saving...');
                    },
                    success: function (response) {
                        if (response.success) {
                            SurveyManager.showNotice(response.data.message, 'success');
                            SurveyManager.closeModal();
                            setTimeout(function () {
                                location.reload();
                            }, 500);
                        } else {
                            SurveyManager.showNotice(response.data.message || lpSurveyManager.strings.error, 'error');
                        }
                    },
                    error: function () {
                        SurveyManager.showNotice(lpSurveyManager.strings.error, 'error');
                    },
                    complete: function () {
                        $form.find('.button-primary').prop('disabled', false).text('Save Question');
                    }
                });
            });
        },

        /**
         * Edit question
         */
        editQuestion: function () {
            $(document).on('click', '.edit-question', function () {
                var $row = $(this).closest('tr');
                var questionId = $(this).data('question-id');
                var content = $row.find('.question-content').text();
                var type = $row.find('.question-type').text().toLowerCase() === 'rating' ? 'rating' : 'text';
                var order = $row.find('.drag-handle').text().trim();

                // Populate form
                $('#question-id').val(questionId);
                $('#question-content').val(content);
                $('#question-type').val(type);
                $('#question-order').val(order);

                // Open modal
                SurveyManager.openModal('edit');
            });
        },

        /**
         * Delete question
         */
        deleteQuestion: function () {
            $(document).on('click', '.delete-question', function () {
                if (!confirm(lpSurveyManager.strings.confirmDelete)) {
                    return;
                }

                var questionId = $(this).data('question-id');
                var $row = $(this).closest('tr');

                $.ajax({
                    url: lpSurveyManager.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'mf_lp_survey_delete_question',
                        nonce: lpSurveyManager.nonce,
                        question_id: questionId
                    },
                    beforeSend: function () {
                        $row.css('opacity', '0.5');
                    },
                    success: function (response) {
                        if (response.success) {
                            SurveyManager.showNotice(response.data.message, 'success');
                            $row.fadeOut(300, function () {
                                $(this).remove();
                                SurveyManager.updateQuestionNumbers();

                                // Show no questions message if table is empty
                                if ($('#questions-list tr').length === 0) {
                                    $('.lp-survey-questions table').replaceWith(
                                        '<p class="no-questions-msg">' + 'No questions yet. Click "Add New Question" to create one.' + '</p>'
                                    );
                                }
                            });
                        } else {
                            SurveyManager.showNotice(response.data.message || lpSurveyManager.strings.error, 'error');
                            $row.css('opacity', '1');
                        }
                    },
                    error: function () {
                        SurveyManager.showNotice(lpSurveyManager.strings.error, 'error');
                        $row.css('opacity', '1');
                    }
                });
            });
        },

        /**
         * Make questions sortable
         */
        sortableQuestions: function () {
            $('#questions-list').sortable({
                handle: '.drag-handle',
                placeholder: 'ui-sortable-placeholder',
                update: function () {
                    SurveyManager.updateQuestionNumbers();
                    SurveyManager.saveOrder();
                }
            });
        },

        /**
         * Update question numbers after reordering
         */
        updateQuestionNumbers: function () {
            $('#questions-list tr').each(function (index) {
                $(this).find('.drag-handle').contents().last().replaceWith((index + 1).toString());
            });
        },

        /**
         * Save question order
         */
        saveOrder: function () {
            var orderedIds = [];
            $('#questions-list tr').each(function () {
                orderedIds.push($(this).data('question-id'));
            });

            $.ajax({
                url: lpSurveyManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mf_lp_survey_reorder_questions',
                    nonce: lpSurveyManager.nonce,
                    ordered_ids: orderedIds
                },
                success: function (response) {
                    if (response.success) {
                        SurveyManager.showNotice(response.data.message, 'success');
                    }
                }
            });
        },

        /**
         * Modal handlers
         */
        modalHandlers: function () {
            // Close on overlay click
            $(document).on('click', '.lp-survey-modal-overlay', function () {
                SurveyManager.closeModal();
            });

            // Close on X button
            $(document).on('click', '.lp-survey-modal-close', function () {
                SurveyManager.closeModal();
            });

            // Close on cancel button
            $(document).on('click', '.cancel-btn', function () {
                SurveyManager.closeModal();
            });

            // Close on ESC key
            $(document).on('keyup', function (e) {
                if (e.key === 'Escape' && $('#question-modal').is(':visible')) {
                    SurveyManager.closeModal();
                }
            });
        },

        /**
         * Open modal
         */
        openModal: function (mode) {
            if (mode === 'add') {
                $('#modal-title').text('Add New Question');
                $('#question-form')[0].reset();
                $('#question-id').val('');
                $('#question-order').val('');
            } else {
                $('#modal-title').text('Edit Question');
            }
            $('#question-modal').fadeIn(200);
        },

        /**
         * Close modal
         */
        closeModal: function () {
            $('#question-modal').fadeOut(200);
            $('#question-form')[0].reset();
        },

        /**
         * Show notification
         */
        showNotice: function (message, type) {
            var $notice = $('<div class="lp-survey-notice ' + type + '">' + message + '</div>');
            $('body').append($notice);

            setTimeout(function () {
                $notice.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        SurveyManager.init();
    });

})(jQuery);
