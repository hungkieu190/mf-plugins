/**
 * Profile Notes JavaScript
 *
 * @package LP_Sticky_Notes
 */

(function($) {
	'use strict';

	var LP_Profile_Notes = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			var self = this;

			// Delete note from profile
			$(document).on('click', '.lp-btn-delete', function(e) {
				e.preventDefault();
				var noteId = $(this).data('note-id');
				self.confirmDeleteNote(noteId, $(this).closest('.lp-profile-note-item'));
			});
		},

		confirmDeleteNote: function(noteId, $noteItem) {
			var self = this;

			if (confirm(lpStickyNotesProfile.i18n.confirmDelete)) {
				$noteItem.addClass('lp-loading');

				$.ajax({
					url: lpStickyNotesProfile.ajaxUrl,
					type: 'POST',
					data: {
						action: 'lp_sticky_notes_delete',
						note_id: noteId,
						nonce: lpStickyNotesProfile.nonce
					},
					success: function(response) {
						$noteItem.removeClass('lp-loading');

						if (response.success) {
							$noteItem.fadeOut(300, function() {
								$(this).remove();
								self.checkEmptyNotes();
							});
							self.showMessage(lpStickyNotesProfile.i18n.noteDeleted, 'success');
						} else {
							self.showMessage(response.data.message || lpStickyNotesProfile.i18n.error, 'error');
						}
					},
					error: function() {
						$noteItem.removeClass('lp-loading');
						self.showMessage(lpStickyNotesProfile.i18n.error, 'error');
					}
				});
			}
		},

		checkEmptyNotes: function() {
			var $notesList = $('.lp-profile-notes-list');
			var $courseGroups = $notesList.find('.lp-notes-course-group');

			// Remove empty course groups
			$courseGroups.each(function() {
				var $group = $(this);
				var $notes = $group.find('.lp-profile-note-item');

				if ($notes.length === 0) {
					$group.remove();
				}
			});

			// Check if all notes are gone
			if ($notesList.find('.lp-profile-note-item').length === 0) {
				$notesList.html('<div class="lp-no-notes">' +
					'<i class="lp-icon-note-empty"></i>' +
					'<h3>' + lpStickyNotesProfile.i18n.noNotes + '</h3>' +
					'<p>' + lpStickyNotesProfile.i18n.startTakingNotes + '</p>' +
				'</div>');
			}
		},

		showMessage: function(message, type) {
			// Simple message display - you can enhance this with a proper notification system
			alert(message);
		}
	};

	// Initialize when document is ready
	$(document).ready(function() {
		LP_Profile_Notes.init();
	});

})(jQuery);