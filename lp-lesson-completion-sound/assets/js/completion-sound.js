/**
 * LP Lesson Completion Sound - Main JavaScript
 *
 * @package LP_Lesson_Completion_Sound
 */

(function ($) {
    'use strict';

    var LPCompletionSound = {
        isProcessing: false,
        hasIntercepted: false,

        /**
         * Initialize
         */
        init: function () {
            if (!lpCompletionSound.enabled) {
                return;
            }

            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function () {
            var self = this;

            // Intercept "Yes" button click
            $(document).on('click', '.lp-modal-footer .btn-yes', function (e) {
                // Only intercept the first click
                if (self.hasIntercepted) {
                    return; // Let it through
                }

                // Get the form that triggered the modal
                var $form = $('form[name="learn-press-form-complete-lesson"]');

                if ($form.length === 0) {
                    // No form found, let default behavior happen
                    return;
                }

                // Prevent default and stop propagation
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();

                self.hasIntercepted = true;
                self.isProcessing = true;

                // Play celebration
                self.playCelebration();

                // Check if user wants to prevent redirect
                if (lpCompletionSound.preventRedirect === 'yes' || lpCompletionSound.preventRedirect === '1') {
                    // Complete lesson via AJAX and reload page
                    setTimeout(function () {
                        self.completeLessonAndReload($form);
                    }, 2000);
                } else {
                    // Normal flow: delay then let LearnPress handle it
                    setTimeout(function () {
                        // Close modal to trigger LearnPress's default behavior
                        $('.lp-overlay').fadeOut(300, function () {
                            $(this).remove();
                        });

                        // Submit the form
                        $form.submit();

                        // Reset after delay
                        setTimeout(function () {
                            self.isProcessing = false;
                            self.hasIntercepted = false;
                        }, 1000);
                    }, 2000); // 2 second delay
                }

                return false;
            });

            // Backup: Listen for LearnPress AJAX events
            $(document).on('learn-press/lesson-completed', function (e, response) {
                if (response && response.status === 'completed' && !self.isProcessing) {
                    self.playCelebration();
                }
            });

            // Backup: Quiz completion
            $(document).on('learn-press/quiz-completed', function (e, response) {
                if (response && response.graduation === 'passed' && !self.isProcessing) {
                    self.playCelebration();
                }
            });
        },

        /**
         * Complete lesson via AJAX and reload page to show updated UI
         */
        completeLessonAndReload: function ($form) {
            var self = this;

            // Get form data
            var formData = $form.serialize();
            var actionUrl = $form.attr('action');

            // Submit via AJAX
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                success: function (response) {
                    // Close the modal
                    $('.lp-overlay').fadeOut(300, function () {
                        $(this).remove();
                    });

                    // Trigger LearnPress event for other plugins
                    $(document).trigger('learn-press/lesson-completed', [response]);

                    // Reload page to show updated UI (completed status, message, etc.)
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                },
                error: function (xhr, status, error) {
                    console.error('Error completing lesson:', error);

                    // Close modal anyway
                    $('.lp-overlay').fadeOut(300, function () {
                        $(this).remove();
                    });

                    // Try to reload anyway to see if it completed
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                }
            });
        },

        /**
         * Play celebration (sound + confetti)
         */
        playCelebration: function () {
            this.playSound();

            if (lpCompletionSound.confetti) {
                this.showConfetti();
            }
        },

        /**
         * Play completion sound
         */
        playSound: function () {
            try {
                var audio = new Audio(lpCompletionSound.soundUrl);
                audio.volume = 1.0;
                audio.play().catch(function () {
                    // Silently fail if browser blocks autoplay
                });
            } catch (error) {
                // Silently fail
            }
        },

        /**
         * Show confetti animation
         */
        showConfetti: function () {
            if (typeof confetti === 'undefined') {
                return;
            }

            try {
                var count = 200;
                var defaults = {
                    origin: { y: 0.7 },
                    zIndex: 9999
                };

                function fire(particleRatio, opts) {
                    confetti(Object.assign({}, defaults, opts, {
                        particleCount: Math.floor(count * particleRatio)
                    }));
                }

                // Multiple bursts for better effect
                fire(0.25, { spread: 26, startVelocity: 55 });
                fire(0.2, { spread: 60 });
                fire(0.35, { spread: 100, decay: 0.91, scalar: 0.8 });
                fire(0.1, { spread: 120, startVelocity: 25, decay: 0.92, scalar: 1.2 });
                fire(0.1, { spread: 120, startVelocity: 45 });
            } catch (error) {
                // Silently fail
            }
        }
    };

    // Initialize on script load
    LPCompletionSound.init();

    // Re-initialize on document ready (backup)
    $(document).ready(function () {
        LPCompletionSound.init();
    });

    // Expose globally for manual triggering
    window.LPCompletionSound = LPCompletionSound;

})(jQuery);
