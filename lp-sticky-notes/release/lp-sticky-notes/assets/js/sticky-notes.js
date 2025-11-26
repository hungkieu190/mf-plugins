/**
 * Sticky Notes Frontend JavaScript
 *
 * @package LP_Sticky_Notes
 */

(function ($) {
	'use strict';

	var LP_Sticky_Notes = {
		init: function () {
			// Update lesson ID first before loading notes
			this.updateCurrentLesson();

			this.bindEvents();
			this.loadNotes();
			this.initLearnPressEvents();
		},

		initLearnPressEvents: function () {
			var self = this;
			var isUpdating = false;

			// Monitor the course content area for changes
			var targetNode = document.querySelector('.course-item-content, .content-item-only, #learn-press-content-item');

			if (targetNode) {
				var observer = new MutationObserver(function (mutations) {
					if (isUpdating) return;

					// Check if this is a meaningful change (new lesson loaded)
					var hasSignificantChange = mutations.some(function (mutation) {
						return mutation.addedNodes.length > 0 ||
							mutation.removedNodes.length > 0 ||
							(mutation.type === 'attributes' && mutation.attributeName === 'data-id');
					});

					if (hasSignificantChange) {
						isUpdating = true;

						setTimeout(function () {
							var newLessonId = self.getCurrentLessonId();

							if (newLessonId && newLessonId !== lpStickyNotes.lessonId) {
								lpStickyNotes.lessonId = newLessonId;
								self.loadNotes();
							}

							isUpdating = false;
						}, 300);
					}
				});

				observer.observe(targetNode, {
					childList: true,
					subtree: true,
					attributes: true,
					attributeFilter: ['data-id', 'class']
				});
			}

			// Backup: Also monitor URL hash changes
			window.addEventListener('hashchange', function () {
				setTimeout(function () {
					self.updateCurrentLesson();
					self.loadNotes();
				}, 500);
			});

			// Backup: Monitor popstate (browser back/forward)
			window.addEventListener('popstate', function () {
				setTimeout(function () {
					self.updateCurrentLesson();
					self.loadNotes();
				}, 500);
			});
		},

		updateCurrentLesson: function () {
			// Update lesson ID from current URL or data attribute
			var lessonId = this.getCurrentLessonId();
			if (lessonId && lessonId !== lpStickyNotes.lessonId) {
				lpStickyNotes.lessonId = lessonId;
			}
		},

		getCurrentLessonId: function () {
			// Try to get lesson ID from various sources

			// 1. From LearnPress global object (most reliable)
			if (typeof LP !== 'undefined' && LP.course) {
				if (LP.course.current_item && LP.course.current_item.id) {
					return parseInt(LP.course.current_item.id);
				}
			}

			// 2. From data attribute on course item content
			var $courseItemContent = jQuery('.course-item-content, .content-item-only, #learn-press-content-item');
			if ($courseItemContent.length) {
				var dataId = $courseItemContent.data('id') || $courseItemContent.attr('data-id');
				if (dataId) {
					return parseInt(dataId);
				}
			}

			// 3. From current item in curriculum
			var $currentItem = jQuery('.course-item.item-current, .course-item.current, li.course-item.viewing, .course-item.active');
			if ($currentItem.length) {
				var itemId = $currentItem.data('id') || $currentItem.data('item-id') || $currentItem.attr('data-id');
				if (itemId) {
					return parseInt(itemId);
				}
			}

			// 4. From URL parameter
			var urlParams = new URLSearchParams(window.location.search);
			var itemId = urlParams.get('item-id');
			if (itemId) {
				return parseInt(itemId);
			}

			// 5. From URL hash
			var hash = window.location.hash;
			if (hash && hash.includes('item-')) {
				var match = hash.match(/item-(\d+)/);
				if (match && match[1]) {
					return parseInt(match[1]);
				}
			}

			// 6. Fallback to original value
			return lpStickyNotes.lessonId;
		},

		bindEvents: function () {
			var self = this;

			// Add note button
			$(document).on('click', '#lp-btn-add-text-note', function (e) {
				e.preventDefault();

				// Open sidebar if closed
				self.openSidebar();

				self.showNoteForm();
			});

			// Cancel note form
			$(document).on('click', '#lp-btn-cancel-note', function (e) {
				e.preventDefault();
				self.hideNoteForm();
			});

			// Submit note form
			$(document).on('submit', '#lp-note-form', function (e) {
				e.preventDefault();
				self.saveNote();
			});

			// Edit note
			$(document).on('click', '.lp-btn-edit-note', function (e) {
				e.preventDefault();
				var noteId = $(this).data('note-id');
				self.editNote(noteId);
			});

			// Delete note
			$(document).on('click', '.lp-btn-delete-note', function (e) {
				e.preventDefault();
				var noteId = $(this).data('note-id');
				self.confirmDeleteNote(noteId);
			});

			// View All Notes button
			$(document).on('click', '#lp-btn-view-all-notes', function (e) {
				e.preventDefault();
				self.showAllNotesModal();
			});

			// Close All Notes modal
			$(document).on('click', '#lp-all-notes-modal-close, .lp-all-notes-modal-overlay', function (e) {
				e.preventDefault();
				self.closeAllNotesModal();
			});

			// Text selection for highlighting
			this.initTextSelection();
		},

		initTextSelection: function () {
			var self = this;
			var selectionTimeout;

			$(document).on('mouseup', function (e) {
				// Only trigger on lesson content area, not on sticky notes section
				if ($(e.target).closest('#lp-sticky-notes-sidebar').length) {
					return;
				}

				clearTimeout(selectionTimeout);
				selectionTimeout = setTimeout(function () {
					var selection = window.getSelection();
					var selectedText = selection.toString().trim();

					if (selectedText.length > 0 && selectedText.length < 2000) { // Reasonable text length
						var range = selection.getRangeAt(0);
						var rect = range.getBoundingClientRect();
						var scrollTop = $(window).scrollTop();

						// Remove existing popup
						$('.lp-highlight-popup').remove();

						// Create popup
						var popup = $('<div class="lp-highlight-popup">' +
							'<button type="button" class="lp-btn-highlight-note">' +
							'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> ' + lpStickyNotes.i18n.addNote +
							'</button>' +
							'</div>');

						popup.css({
							position: 'absolute',
							left: rect.left + (rect.width / 2) - 50 + 'px',
							top: rect.top + scrollTop - 50 + 'px',
							zIndex: 10000
						});

						$('body').append(popup);

						// Bind click event
						popup.find('.lp-btn-highlight-note').on('click', function (e) {
							e.stopPropagation(); // Prevent click from bubbling to document and closing the sidebar.
							self.createHighlightNote(selectedText, range);
							popup.remove();
						});
						// Remove popup when clicking elsewhere
						$(document).one('click', function (e) {
							if (!$(e.target).closest('.lp-highlight-popup').length) {
								popup.remove();
							}
						});
					}
				}, 100);
			});
		},

		createHighlightNote: function (selectedText, range) {
			// Open sidebar if closed
			this.openSidebar();

			// Scroll to the highlighted text
			var rect = range.getBoundingClientRect();
			var scrollTop = $(window).scrollTop();
			var targetTop = rect.top + scrollTop - 100; // 100px offset
			$('html, body').animate({ scrollTop: targetTop }, 300);

			// Store highlight data
			$('#lp-highlight-text').val(selectedText);
			$('#lp-note-type').val('highlight');

			// Show highlight preview
			$('#lp-highlight-preview .lp-highlight-text').text(selectedText);
			$('#lp-highlight-preview').show();

			// Show form
			this.showNoteForm();
		},

		showNoteForm: function () {
			$('.lp-note-form-wrapper').slideDown();
			if ($('#lp-highlight-preview').is(':visible')) {
				$('#lp-highlight-preview').focus();
			} else {
				$('#lp-note-content').focus();
			}
		},

		hideNoteForm: function () {
			$('.lp-note-form-wrapper').slideUp();
			this.resetForm();
		},

		resetForm: function () {
			$('#lp-note-form')[0].reset();
			$('#lp-note-id').val('');
			$('#lp-note-type').val('text');
			$('#lp-highlight-text').val('');
			$('#lp-note-position').val('');
			$('#lp-highlight-preview').hide();
		},

		loadNotes: function () {
			var self = this;

			$.ajax({
				url: lpStickyNotes.ajaxUrl,
				type: 'POST',
				data: {
					action: 'lp_sticky_notes_get',
					lesson_id: lpStickyNotes.lessonId,
					course_id: lpStickyNotes.courseId,
					nonce: lpStickyNotes.nonce
				},
				success: function (response) {
					if (response.success) {
						self.renderNotes(response.data.notes);
					}
				},
				error: function () {
					console.error(lpStickyNotes.i18n.error);
				}
			});
		},

		renderNotes: function (notes) {
			var self = this;
			var $list = $('#lp-sticky-notes-list');
			$list.empty();

			if (notes.length === 0) {
				$list.html('<div class="lp-no-notes">' +
					'<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/></svg>' +
					'<p>' + lpStickyNotes.i18n.noNotes + '</p>' +
					'</div>');
				return;
			}

			notes.forEach(function (note) {
				var noteHtml = self.generateNoteHtml(note);
				$list.append(noteHtml);
			});
		},

		generateNoteHtml: function (note) {
			var highlightHtml = '';
			if (note.note_type === 'highlight' && note.highlight_text) {
				highlightHtml = '<div class="lp-note-highlight">' +
					'<strong>' + lpStickyNotes.i18n.highlightedText + ':</strong>' +
					'<div class="lp-highlight-text">' + note.highlight_text + '</div>' +
					'</div>';
			}

			return '<div class="lp-note-item" data-note-id="' + note.id + '" data-note-type="' + note.note_type + '">' +
				'<div class="lp-note-header">' +
				'<span class="lp-note-type-badge lp-note-type-' + note.note_type + '">' +
				(note.note_type === 'highlight' ? lpStickyNotes.i18n.highlightNote : lpStickyNotes.i18n.textNote) +
				'</span>' +
				'<span class="lp-note-date">' + this.formatDate(note.created_at) + '</span>' +
				'</div>' +
				highlightHtml +
				'<div class="lp-note-content">' + note.content + '</div>' +
				'<div class="lp-note-actions">' +
				'<button type="button" class="lp-btn-edit-note" data-note-id="' + note.id + '">' +
				'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> ' + lpStickyNotes.i18n.editNote +
				'</button>' +
				'<button type="button" class="lp-btn-delete-note" data-note-id="' + note.id + '">' +
				'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> ' + lpStickyNotes.i18n.deleteNote +
				'</button>' +
				'</div>' +
				'</div>';
		},

		saveNote: function () {
			var self = this;
			var $form = $('#lp-note-form');
			var formData = new FormData($form[0]);

			formData.append('action', 'lp_sticky_notes_add');
			formData.append('lesson_id', lpStickyNotes.lessonId);
			formData.append('course_id', lpStickyNotes.courseId);
			formData.append('nonce', lpStickyNotes.nonce);

			// Show loading
			$form.addClass('lp-loading');

			$.ajax({
				url: lpStickyNotes.ajaxUrl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					$form.removeClass('lp-loading');

					if (response.success) {
						self.hideNoteForm();
						self.loadNotes();
						self.showMessage(lpStickyNotes.i18n.noteAdded, 'success');
					} else {
						self.showMessage(response.data.message || lpStickyNotes.i18n.error, 'error');
					}
				},
				error: function () {
					$form.removeClass('lp-loading');
					self.showMessage(lpStickyNotes.i18n.error, 'error');
				}
			});
		},

		editNote: function (noteId) {
			var self = this;

			$.ajax({
				url: lpStickyNotes.ajaxUrl,
				type: 'POST',
				data: {
					action: 'lp_sticky_notes_get_single',
					note_id: noteId,
					nonce: lpStickyNotes.nonce
				},
				success: function (response) {
					if (response.success) {
						var note = response.data.note;
						$('#lp-note-id').val(note.id);
						$('#lp-note-content').val(note.content);
						$('#lp-note-type').val(note.note_type);

						if (note.note_type === 'highlight' && note.highlight_text) {
							$('#lp-highlight-text').val(note.highlight_text);
							$('#lp-highlight-preview .lp-highlight-text').text(note.highlight_text);
							$('#lp-highlight-preview').show();
						}

						if (note.position) {
							$('#lp-note-position').val(JSON.stringify(note.position));
						}

						self.showNoteForm();
					}
				},
				error: function () {
					self.showMessage(lpStickyNotes.i18n.error, 'error');
				}
			});
		},

		confirmDeleteNote: function (noteId) {
			var self = this;

			if (confirm(lpStickyNotes.i18n.confirmDelete)) {
				// Find and fade out the note element immediately for smooth UX
				var $noteElement = $('.lp-note-item[data-note-id="' + noteId + '"]');
				$noteElement.fadeOut(300);

				$.ajax({
					url: lpStickyNotes.ajaxUrl,
					type: 'POST',
					data: {
						action: 'lp_sticky_notes_delete',
						note_id: noteId,
						nonce: lpStickyNotes.nonce
					},
					success: function (response) {
						if (response.success) {
							// Remove the note element from DOM
							$noteElement.remove();

							// Reload sidebar notes
							self.loadNotes();

							// If modal is open, reload it too
							if ($('#lp-all-notes-modal').is(':visible')) {
								self.loadAllNotes();
							}

							self.showMessage(lpStickyNotes.i18n.noteDeleted, 'success');
						} else {
							// If delete failed, show the note again
							$noteElement.fadeIn(300);
							self.showMessage(response.data.message || lpStickyNotes.i18n.error, 'error');
						}
					},
					error: function () {
						// If error, show the note again
						$noteElement.fadeIn(300);
						self.showMessage(lpStickyNotes.i18n.error, 'error');
					}
				});
			}
		},

		showMessage: function (message, type) {
			// Create toast notification
			var toastClass = type === 'success' ? 'lp-toast-success' : 'lp-toast-error';
			var toast = $('<div class="lp-toast ' + toastClass + '">' +
				'<span class="lp-toast-message">' + message + '</span>' +
				'<button type="button" class="lp-toast-close">&times;</button>' +
				'</div>');

			// Add to container
			if (!$('.lp-toast-container').length) {
				$('body').append('<div class="lp-toast-container"></div>');
			}
			$('.lp-toast-container').append(toast);

			// Show toast
			setTimeout(function () {
				toast.addClass('show');
			}, 100);

			// Auto hide after 3 seconds
			setTimeout(function () {
				toast.removeClass('show');
				setTimeout(function () {
					toast.remove();
				}, 300);
			}, 3000);

			// Close button
			toast.find('.lp-toast-close').on('click', function () {
				toast.removeClass('show');
				setTimeout(function () {
					toast.remove();
				}, 300);
			});
		},

		formatDate: function (dateString) {
			var date = new Date(dateString);
			return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
		},

		initSidebarToggle: function () {
			var self = this;

			// Toggle button click
			$(document).on('click', '#lp-sticky-notes-toggle', function (e) {
				e.preventDefault();
				self.toggleSidebar();
			});

			// Close button click
			$(document).on('click', '#lp-sticky-notes-close', function (e) {
				e.preventDefault();
				self.closeSidebar();
			});

			// Close sidebar when clicking outside
			$(document).on('click', function (e) {
				if (!$(e.target).closest('#lp-sticky-notes-sidebar, #lp-sticky-notes-toggle').length) {
					self.closeSidebar();
				}
			});

			// Close on escape key
			$(document).on('keydown', function (e) {
				if (e.keyCode === 27) { // Escape key
					self.closeSidebar();
				}
			});
		},

		toggleSidebar: function () {
			var $sidebar = $('#lp-sticky-notes-sidebar');
			var $toggle = $('#lp-sticky-notes-toggle');

			if ($sidebar.hasClass('open')) {
				this.closeSidebar();
			} else {
				this.openSidebar();
			}
		},

		openSidebar: function () {
			var $sidebar = $('#lp-sticky-notes-sidebar');
			var $toggle = $('#lp-sticky-notes-toggle');

			$sidebar.addClass('open');
			$toggle.hide(); // Hide toggle when sidebar is open
		},

		showAllNotesModal: function () {
			var self = this;
			var $modal = $('#lp-all-notes-modal');

			$modal.fadeIn(300);
			$('body').addClass('lp-modal-open');

			// Load all notes
			this.loadAllNotes();
		},

		closeAllNotesModal: function () {
			var $modal = $('#lp-all-notes-modal');

			$modal.fadeOut(300);
			$('body').removeClass('lp-modal-open');
		},

		loadAllNotes: function () {
			var self = this;
			var $modalBody = $('#lp-all-notes-modal-body');

			// Show loading
			$modalBody.html('<div class="lp-loading">' +
				'<svg width="40" height="40" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>' +
				'<p>' + lpStickyNotes.i18n.loadingNotes + '</p>' +
				'</div>');

			$.ajax({
				url: lpStickyNotes.ajaxUrl,
				type: 'POST',
				data: {
					action: 'lp_sticky_notes_get_all',
					nonce: lpStickyNotes.nonce
				},
				success: function (response) {
					if (response.success) {
						self.renderAllNotes(response.data.grouped_notes);
					} else {
						$modalBody.html('<div class="lp-no-notes"><p>' + (response.data.message || lpStickyNotes.i18n.error) + '</p></div>');
					}
				},
				error: function () {
					$modalBody.html('<div class="lp-no-notes"><p>' + lpStickyNotes.i18n.error + '</p></div>');
				}
			});
		},

		renderAllNotes: function (groupedNotes) {
			var self = this;
			var $modalBody = $('#lp-all-notes-modal-body');

			if (!groupedNotes || groupedNotes.length === 0) {
				$modalBody.html('<div class="lp-no-notes">' +
					'<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/></svg>' +
					'<p>' + lpStickyNotes.i18n.noNotes + '</p>' +
					'</div>');
				return;
			}

			var html = '';
			groupedNotes.forEach(function (group) {
				html += '<div class="lp-notes-group">';
				html += '<div class="lp-notes-group-header">';
				html += '<h4><a href="' + group.lesson_url + '" class="lp-lesson-link">' + group.lesson_title + '</a></h4>';
				if (group.course_title) {
					html += '<span class="lp-course-name">' + group.course_title + '</span>';
				}
				html += '</div>';
				html += '<div class="lp-notes-group-list">';

				group.notes.forEach(function (note) {
					html += self.generateNoteHtml(note);
				});

				html += '</div>';
				html += '</div>';
			});

			$modalBody.html(html);
		},

		closeSidebar: function () {
			var $sidebar = $('#lp-sticky-notes-sidebar');
			var $toggle = $('#lp-sticky-notes-toggle');

			$sidebar.removeClass('open');
			$toggle.show(); // Show toggle when sidebar is closed
		}
	};

	// Initialize when document is ready
	$(document).ready(function () {
		LP_Sticky_Notes.init();
		LP_Sticky_Notes.initSidebarToggle();
	});

})(jQuery);