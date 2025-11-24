(function ($) {
	'use strict';

	function ensureDialog() {
		let $dialog = $('#lp-uppc-preview-dialog');
		if ( $dialog.length ) {
			return $dialog;
		}

		$dialog = $('<div>', {
			id: 'lp-uppc-preview-dialog',
			class: 'lp-uppc-preview-dialog',
			title: lpUPPCAdmin.i18n.previewTitle,
		}).append(
			$('<div>', { class: 'lp-uppc-preview-content' }).append(
				$('<p>', { class: 'lp-uppc-preview-loading' }).text( lpUPPCAdmin.i18n.loading )
			)
		);

		$('body').append( $dialog );

		$dialog.dialog({
			autoOpen: false,
			modal: true,
			width: 720,
			classes: {
				'ui-dialog': 'lp-uppc-preview-ui-dialog',
			},
			closeText: '×'
		});

		return $dialog;
	}

	function openDialog() {
		const $dialog = ensureDialog();
		$dialog.find('.lp-uppc-preview-content').html(
			$('<p>', { class: 'lp-uppc-preview-loading' }).text( lpUPPCAdmin.i18n.loading )
		);
		$dialog.dialog('option', 'title', lpUPPCAdmin.i18n.previewTitle);
		$dialog.dialog('open');
		return $dialog;
	}

	function renderPreview($dialog, payload) {
		const $content = $('<div>', { class: 'lp-uppc-preview-wrapper' });

		if ( payload.subject ) {
			$content.append(
				$('<p>', { class: 'lp-uppc-preview-subject' }).html(
					'<strong>' + lpUPPCAdmin.i18n.subjectLabel + '</strong> ' + payload.subject
				)
			);
		}

		$content.append(
			$('<div>', {
				class: 'lp-uppc-preview-message',
				html: payload.message || '<p>' + lpUPPCAdmin.i18n.emptyMessage + '</p>'
			})
		);

		$dialog.find('.lp-uppc-preview-content').empty().append( $content );
	}

	function renderError($dialog, message) {
		$dialog.find('.lp-uppc-preview-content').html(
			$('<div>', { class: 'notice notice-error' }).append(
				$('<p>').text( message || lpUPPCAdmin.i18n.errorGeneric )
			)
		);
	}

	$(function () {
		$(document).on('click', '.lp-uppc-preview-email', function (event) {
			event.preventDefault();

			const $button = $(this);
			const nonce = $button.data('nonce');
			const courseSelector = $button.data('course-input');
			const courseId = courseSelector ? $(courseSelector).val() : '';

			const $dialog = openDialog();

			$.ajax({
				type: 'POST',
				url: lpUPPCAdmin.ajaxUrl,
				dataType: 'json',
				data: {
					action: 'lp_uppc_preview_email',
					nonce: nonce,
					course_id: courseId
				}
			}).done(function (response) {
				if ( response && response.success && response.data ) {
					renderPreview($dialog, response.data);
				} else {
					renderError($dialog, response && response.data ? response.data.message : null);
				}
			}).fail(function () {
				renderError($dialog);
			});
		});
	});
})(jQuery);
