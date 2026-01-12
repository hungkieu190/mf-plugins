/**
 * LP Survey Frontend JavaScript
 */

(function ($) {
    'use strict';

    const LPSurvey = {
        currentSurveyId: 0,
        currentRefId: 0,

        init: function () {
            this.checkTrigger();
            this.initAjaxInterceptor();
            this.bindEvents();
        },



        checkTrigger: function () {
            // 1. Check if there's a pending survey from PHP (transient)
            if (lpSurvey.pendingSurvey && lpSurvey.pendingSurvey.survey_id) {

                const triggerData = {
                    survey_id: lpSurvey.pendingSurvey.survey_id,
                    type: lpSurvey.pendingSurvey.type,
                    ref_id: lpSurvey.pendingSurvey.ref_id,
                    timestamp: Date.now()
                };

                // Store in localStorage as backup for redirects
                if (typeof (Storage) !== "undefined") {
                    localStorage.setItem('lp_survey_trigger', JSON.stringify(triggerData));
                }

                this.currentSurveyId = lpSurvey.pendingSurvey.survey_id;
                this.currentRefId = lpSurvey.pendingSurvey.ref_id || 0;

                this.loadAndShowSurvey(this.currentSurveyId);
                return;
            }

            // 2. Fallback: check localStorage (for persistence across redirects)
            if (typeof (Storage) !== "undefined") {
                const storedTrigger = localStorage.getItem('lp_survey_trigger');
                if (storedTrigger) {
                    try {
                        const triggerData = JSON.parse(storedTrigger);

                        // Check if trigger is recent (within last 60 seconds)
                        const age = Date.now() - triggerData.timestamp;

                        if (age < 60000 && triggerData.survey_id) {
                            this.currentSurveyId = triggerData.survey_id;
                            this.currentRefId = triggerData.ref_id || 0;
                            this.loadAndShowSurvey(triggerData.survey_id);

                            // Clear localStorage after using
                            localStorage.removeItem('lp_survey_trigger');
                            return;
                        } else {
                            localStorage.removeItem('lp_survey_trigger');
                        }
                    } catch (e) {
                        localStorage.removeItem('lp_survey_trigger');
                    }
                }
            }
        },

        /**
         * Intercept LearnPress AJAX completions
         */
        initAjaxInterceptor: function () {
            const self = this;
            $(document).ajaxComplete(function (event, xhr, settings) {
                // Check if it's a LearnPress completion action
                if (settings.data && typeof settings.data === 'string') {
                    if (settings.data.indexOf('lp_course_finish') !== -1 ||
                        settings.data.indexOf('lp_lesson_complete') !== -1) {

                        // We can't easily see the transient yet, but we can poll for it
                        // or just rely on the next page load if it redirects.
                        // However, if it DOES NOT redirect, we want it NOW.
                        setTimeout(() => {
                            self.pollForTransient();
                        }, 500);
                    }
                }
            });
        },

        pollForTransient: function () {
            // This is a bit tricky since we need fresh localized data.
            // But we can check if the server returned any flag in the the result.
            // For now, the most reliable way during redirect is just the next page load.
        },

        bindEvents: function () {
            // Submit survey
            $(document).on('submit', '#lp-survey-form', (e) => {
                e.preventDefault();
                this.submitSurvey();
            });

            // Skip survey
            $(document).on('click', '.lp-survey-skip-btn', (e) => {
                e.preventDefault();
                this.skipSurvey();
            });

            // Rating stars click
            $(document).on('click', '.lp-survey-rating .star', function () {
                const rating = $(this).data('rating');
                const container = $(this).closest('.lp-survey-rating');

                // Update hidden input
                container.find('input[type="hidden"]').val(rating);

                // Update visual stars
                container.find('.star').each(function (index) {
                    if (index < rating) {
                        $(this).addClass('active');
                    } else {
                        $(this).removeClass('active');
                    }
                });
            });

            // Close popup on overlay click
            $(document).on('click', '.lp-survey-overlay', () => {
                if (lpSurvey.canSkip) {
                    this.closePopup();
                }
            });
        },

        loadAndShowSurvey: function (surveyId) {
            $.ajax({
                url: lpSurvey.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mf_lp_survey_get',
                    nonce: lpSurvey.nonce,
                    survey_id: surveyId
                },
                success: (response) => {
                    if (response.success && response.data.questions) {
                        this.renderQuestions(response.data.questions);
                        this.showPopup();
                    }
                },
                error: () => {
                    // Fail silently in production
                }
            });
        },

        renderQuestions: function (questions) {
            const container = $('#lp-survey-questions');
            container.empty();

            questions.forEach((question, index) => {
                let questionHtml = `<div class="lp-survey-question">
					<label>${question.content}</label>`;

                if (question.type === 'rating') {
                    questionHtml += `
						<div class="lp-survey-rating">
							<input type="hidden" name="answer_${question.id}" value="">
							${this.renderStars()}
						</div>`;
                } else {
                    questionHtml += `
						<textarea 
							name="answer_${question.id}" 
							placeholder="${this.getPlaceholder(question.type)}"
						></textarea>`;
                }

                questionHtml += '</div>';
                container.append(questionHtml);
            });
        },

        renderStars: function () {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += `<span class="star" data-rating="${i}">★</span>`;
            }
            return stars;
        },

        getPlaceholder: function (type) {
            if (type === 'text') {
                return 'Share your thoughts...';
            }
            return '';
        },

        submitSurvey: function () {
            const form = $('#lp-survey-form');
            const formData = form.serializeArray();
            const answers = {};

            // Build answers object
            formData.forEach(item => {
                if (item.name.startsWith('answer_')) {
                    const questionId = item.name.replace('answer_', '');
                    answers[questionId] = item.value;
                }
            });

            // Disable submit button
            const submitBtn = $('.lp-survey-submit-btn');
            submitBtn.prop('disabled', true).text('Submitting...');

            const payload = {
                action: 'mf_lp_survey_submit',
                nonce: lpSurvey.nonce,
                survey_id: this.currentSurveyId,
                ref_id: this.currentRefId,
                answers: answers
            };

            $.ajax({
                url: lpSurvey.ajaxUrl,
                type: 'POST',
                data: payload,
                success: (response) => {
                    if (response.success) {
                        this.showSuccess();
                        setTimeout(() => {
                            this.closePopup();
                        }, 2000);
                    } else {
                        alert(response.data.message || 'Failed to submit survey');
                        submitBtn.prop('disabled', false).text('Submit Feedback');
                    }
                },
                error: () => {
                    alert('An error occurred. Please try again.');
                    submitBtn.prop('disabled', false).text('Submit Feedback');
                }
            });
        },

        skipSurvey: function () {
            $.ajax({
                url: lpSurvey.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mf_lp_survey_skip',
                    nonce: lpSurvey.nonce
                },
                success: () => {
                    this.closePopup();
                }
            });
        },

        showPopup: function () {
            $('#lp-survey-id').val(this.currentSurveyId);
            $('#lp-survey-popup').fadeIn(300);
            $('body').css('overflow', 'hidden');
        },

        closePopup: function () {
            $('#lp-survey-popup').fadeOut(300);
            $('body').css('overflow', '');
        },

        showSuccess: function () {
            $('#lp-survey-form').hide();
            $('#lp-survey-success').fadeIn(300);
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        LPSurvey.init();
    });

})(jQuery);
