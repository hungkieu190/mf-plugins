/**
 * Admin JavaScript for Student Notes page
 *
 * @package LP_Sticky_Notes
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Toggle full note view
        $('.view-full-note').on('click', function (e) {
            e.preventDefault();
            var noteId = $(this).data('note-id');
            var $fullContent = $('#note-content-' + noteId);

            if ($fullContent.is(':visible')) {
                $fullContent.hide();
                $(this).text($(this).data('show-text') || 'View Full');
            } else {
                // Hide all other expanded notes
                $('.full-note-content').hide();
                $('.view-full-note').text($('.view-full-note').first().data('show-text') || 'View Full');

                // Show this note
                $fullContent.show();
                $(this).text($(this).data('hide-text') || 'Hide');
            }
        });

        // Store button text for toggling
        $('.view-full-note').each(function () {
            $(this).data('show-text', $(this).text());
            $(this).data('hide-text', 'Hide');
        });
    });

})(jQuery);
