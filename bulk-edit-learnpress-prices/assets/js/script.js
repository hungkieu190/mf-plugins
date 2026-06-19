( function () {
	'use strict';

	var root = document.querySelector( '.belpcp-admin-page' );

	if ( ! root ) {
		return;
	}

	var actionSelect = root.querySelector( '[data-bulk-action]' );
	var filterForm = root.querySelector( '[data-filter-form]' );
	var courseTableRegion = root.querySelector( '[data-course-table-region]' );
	var courseLoadErrors = root.querySelector( '[data-course-load-errors]' );
	var courseLoadErrorText = root.querySelector( '[data-course-load-error-text]' );
	var valueField = root.querySelector( '[data-bulk-value-field]' );
	var valueInput = root.querySelector( '[data-bulk-value]' );
	var valueLabel = root.querySelector( '[data-bulk-value-label]' );
	var saleScheduleFields = Array.prototype.slice.call( root.querySelectorAll( '[data-sale-schedule-field]' ) );
	var saleStartInput = root.querySelector( '[data-sale-start]' );
	var saleEndInput = root.querySelector( '[data-sale-end]' );
	var selectedCounts = Array.prototype.slice.call( root.querySelectorAll( '[data-selected-count]' ) );
	var selectionHint = root.querySelector( '[data-selection-hint]' );
	var previewButton = root.querySelector( '[data-preview-button]' );
	var applyButton = root.querySelector( '[data-apply-button]' );
	var errors = root.querySelector( '[data-bulk-errors]' );
	var errorText = root.querySelector( '[data-bulk-error-text]' );
	var panel = root.querySelector( '.belpcp-bulk-panel' );
	var openBulkActionButton = root.querySelector( '[data-open-bulk-action]' );
	var bulkActionModal = root.querySelector( '[data-bulk-action-modal]' );
	var bulkActionDialog = bulkActionModal ? bulkActionModal.querySelector( '.belpcp-modal__dialog' ) : null;
	var previewSummary = root.querySelector( '[data-preview-summary]' );
	var previewSummaryText = root.querySelector( '[data-preview-summary-text]' );
	var previewTableWrap = root.querySelector( '[data-preview-table-wrap]' );
	var previewRows = root.querySelector( '[data-preview-rows]' );
	var modal = root.querySelector( '[data-confirm-modal]' );
	var modalDialog = modal ? modal.querySelector( '.belpcp-modal__dialog' ) : null;
	var historyModal = root.querySelector( '[data-history-modal]' );
	var historyDialog = historyModal ? historyModal.querySelector( '.belpcp-modal__dialog' ) : null;
	var historyDescription = root.querySelector( '[data-history-description]' );
	var historyErrors = root.querySelector( '[data-history-errors]' );
	var historyErrorText = root.querySelector( '[data-history-error-text]' );
	var historyEmpty = root.querySelector( '[data-history-empty]' );
	var historyTableWrap = root.querySelector( '[data-history-table-wrap]' );
	var historyRows = root.querySelector( '[data-history-rows]' );
	var modalCourseCount = root.querySelector( '[data-modal-course-count]' );
	var modalAction = root.querySelector( '[data-modal-action]' );
	var modalValue = root.querySelector( '[data-modal-value]' );
	var modalPreviewRows = root.querySelector( '[data-modal-preview-rows]' );
	var confirmApplyButton = root.querySelector( '[data-confirm-apply]' );
	var updateReport = root.querySelector( '[data-update-report]' );
	var updateReportText = root.querySelector( '[data-update-report-text]' );
	var updateReportRows = root.querySelector( '[data-update-report-rows]' );
	var updateReportTableWrap = root.querySelector( '[data-update-report-table-wrap]' );
	var previousBulkFocus = null;
	var previousConfirmFocus = null;
	var previousHistoryFocus = null;
	var config = window.BELPCPAdmin || {};
	var strings = config.strings || {};
	var latestPreviewRows = [];

	if ( ! actionSelect || ! valueInput || ! previewButton || ! applyButton || ! selectedCounts.length || ! panel ) {
		return;
	}

	var defaultValueLabel = valueLabel ? valueLabel.textContent : 'Value';

	function getCourseCheckboxes() {
		return Array.prototype.slice.call( root.querySelectorAll( 'tbody input[name="course_ids[]"]' ) );
	}

	function getSelectedCourseCount() {
		return getCourseCheckboxes().filter( function ( checkbox ) {
			return checkbox.checked;
		} ).length;
	}

	function getSelectedCourseIds() {
		return getCourseCheckboxes().filter( function ( checkbox ) {
			return checkbox.checked;
		} ).map( function ( checkbox ) {
			return checkbox.value;
		} );
	}

	function getSelectedRows() {
		return getCourseCheckboxes().filter( function ( checkbox ) {
			return checkbox.checked;
		} ).map( function ( checkbox ) {
			return checkbox.closest( 'tr' );
		} ).filter( Boolean );
	}

	function actionNeedsValue() {
		return actionSelect.value !== 'remove_sale_price';
	}

	function actionNeedsSchedule() {
		return actionSelect.value === 'schedule_sale_price';
	}

	function hasValidValue() {
		if ( ! actionNeedsValue() ) {
			return true;
		}

		return valueInput.value !== '' && ! Number.isNaN( Number( valueInput.value ) ) && Number( valueInput.value ) >= 0;
	}

	function getValidationMessage() {
		var selectedCourses = getSelectedCourseCount();
		var maxSelectedCourses = Number( config.maxSelectedCourses || 0 );

		if ( selectedCourses < 1 ) {
			return panel.dataset.errorNoCourses;
		}

		if ( maxSelectedCourses > 0 && selectedCourses > maxSelectedCourses ) {
			return panel.dataset.errorTooManyCourses;
		}

		if ( actionNeedsValue() && valueInput.value === '' ) {
			return panel.dataset.errorMissingValue;
		}

		if ( ! hasValidValue() ) {
			return panel.dataset.errorInvalidValue;
		}

		if ( actionNeedsSchedule() ) {
			if ( ! saleStartInput || ! saleEndInput || saleStartInput.value === '' || saleEndInput.value === '' ) {
				return panel.dataset.errorMissingSchedule;
			}

			if ( new Date( saleStartInput.value ).getTime() >= new Date( saleEndInput.value ).getTime() ) {
				return panel.dataset.errorInvalidSchedule;
			}
		}

		if ( actionSelect.value === 'decrease_percentage' && Number( valueInput.value ) > 100 ) {
			return panel.dataset.errorInvalidDecrease;
		}

		return '';
	}

	function showError( message ) {
		if ( ! errors || ! errorText ) {
			return;
		}

		errorText.textContent = message;
		errors.hidden = false;
	}

	function hideError() {
		if ( errors ) {
			errors.hidden = true;
		}
	}

	function showCourseLoadError( message ) {
		if ( ! courseLoadErrors || ! courseLoadErrorText ) {
			showError( message );
			return;
		}

		courseLoadErrorText.textContent = message;
		courseLoadErrors.hidden = false;
	}

	function hideCourseLoadError() {
		if ( courseLoadErrors ) {
			courseLoadErrors.hidden = true;
		}
	}

	function showHistoryError( message ) {
		if ( ! historyErrors || ! historyErrorText ) {
			showError( message );
			return;
		}

		historyErrorText.textContent = message;
		historyErrors.hidden = false;
	}

	function hideHistoryError() {
		if ( historyErrors ) {
			historyErrors.hidden = true;
		}
	}

	function resetPreviewState() {
		panel.dataset.previewReady = 'false';
		applyButton.disabled = true;
		latestPreviewRows = [];

		if ( previewSummary ) {
			previewSummary.hidden = true;
		}

		if ( previewTableWrap ) {
			previewTableWrap.hidden = true;
		}

		if ( previewRows ) {
			previewRows.innerHTML = '';
		}
	}

	function resetUpdateReport() {
		if ( updateReport ) {
			updateReport.hidden = true;
			updateReport.classList.remove( 'notice-success', 'notice-error', 'notice-warning' );
		}

		if ( updateReportRows ) {
			updateReportRows.innerHTML = '';
		}

		if ( updateReportTableWrap ) {
			updateReportTableWrap.hidden = true;
		}
	}

	function updateValueField() {
		var needsValue = actionNeedsValue();

		valueInput.disabled = ! needsValue;
		valueInput.required = needsValue;

		if ( valueField ) {
			valueField.classList.toggle( 'is-disabled', ! needsValue );
		}

		if ( valueLabel ) {
			valueLabel.textContent = needsValue && actionSelect.selectedOptions[0].dataset.valueLabel ? actionSelect.selectedOptions[0].dataset.valueLabel : defaultValueLabel;
		}

		if ( ! needsValue ) {
			valueInput.value = '';
		}

		saleScheduleFields.forEach( function ( field ) {
			field.hidden = ! actionNeedsSchedule();
		} );

		if ( saleStartInput ) {
			saleStartInput.disabled = ! actionNeedsSchedule();
			saleStartInput.required = actionNeedsSchedule();
		}

		if ( saleEndInput ) {
			saleEndInput.disabled = ! actionNeedsSchedule();
			saleEndInput.required = actionNeedsSchedule();
		}
	}

	function updateControls() {
		var count = getSelectedCourseCount();
		var hasSelectedCourses = count > 0;

		selectedCounts.forEach( function ( selectedCount ) {
			selectedCount.textContent = String( count );
		} );

		previewButton.disabled = ! hasSelectedCourses;
		if ( openBulkActionButton ) {
			openBulkActionButton.disabled = ! hasSelectedCourses;
		}

		if ( selectionHint ) {
			selectionHint.hidden = hasSelectedCourses;
		}

		if ( ! getValidationMessage() ) {
			hideError();
		}
	}

	function getActionLabel() {
		return actionSelect.selectedOptions[0] ? actionSelect.selectedOptions[0].textContent.trim() : '';
	}

	function getActionValueLabel() {
		if ( ! actionNeedsValue() ) {
			return panel.dataset.emptyValue || '';
		}

		return valueInput.value;
	}

	function getPreviewAfterText() {
		if ( actionSelect.value === 'remove_sale_price' ) {
			return panel.dataset.afterRemoveSale;
		}

		if ( actionNeedsSchedule() ) {
			return getActionLabel() + ': ' + getActionValueLabel() + ' / ' + ( strings.scheduleWindow || 'Sale schedule' ) + ': ' + saleStartInput.value + ' -> ' + saleEndInput.value;
		}

		return getActionLabel() + ': ' + getActionValueLabel();
	}

	function getSaleScheduleText( row ) {
		if ( ! row || ! row.saleScheduleDisplay ) {
			return '';
		}

		return ' / ' + ( strings.scheduleWindow || 'Sale schedule' ) + ': ' + row.saleScheduleDisplay;
	}

	function getCellText( row, index ) {
		return row && row.children[ index ] ? row.children[ index ].textContent.trim() : '--';
	}

	function createPreviewRow( row ) {
		var tr = document.createElement( 'tr' );
		var cells = [
			getCellText( row, 2 ),
			getCellText( row, 3 ),
			getCellText( row, 4 ),
			getPreviewAfterText()
		];

		cells.forEach( function ( value ) {
			var td = document.createElement( 'td' );
			td.textContent = value;
			tr.appendChild( td );
		} );

		return tr;
	}

	function createServerPreviewRow( row ) {
		var tr = document.createElement( 'tr' );
		var messages = []
			.concat( row.errors || [] )
			.concat( row.warnings || [] );
		var cells = [
			row.title || '',
			row.beforeRegularDisplay || '',
			row.beforeSaleDisplay || '',
			( row.afterRegularDisplay || '' ) + ' / ' + ( row.afterSaleDisplay || '' ) + getSaleScheduleText( row ),
			messages.length ? messages.join( ' ' ) : ( row.statusLabel || '' )
		];

		if ( row.status === 'skipped' ) {
			tr.classList.add( 'is-skipped' );
		} else if ( row.warnings && row.warnings.length ) {
			tr.classList.add( 'has-warning' );
		}

		cells.forEach( function ( value ) {
			var td = document.createElement( 'td' );
			td.textContent = value;
			tr.appendChild( td );
		} );

		return tr;
	}

	function renderPreviewRows( target ) {
		if ( ! target ) {
			return;
		}

		target.innerHTML = '';

		if ( latestPreviewRows.length ) {
			latestPreviewRows.forEach( function ( row ) {
				target.appendChild( createServerPreviewRow( row ) );
			} );
			return;
		}

		getSelectedRows().forEach( function ( row ) {
			target.appendChild( createPreviewRow( row ) );
		} );
	}

	function buildPreviewRequestBody() {
		var body = new window.FormData();

		body.append( 'action', config.actions ? config.actions.previewChanges : 'bulk_edit_lp_preview_changes' );
		body.append( 'nonce', config.nonce || '' );
		body.append( 'bulk_action', actionSelect.value );
		body.append( 'bulk_value', valueInput.value );

		if ( actionNeedsSchedule() ) {
			body.append( 'sale_start', saleStartInput ? saleStartInput.value : '' );
			body.append( 'sale_end', saleEndInput ? saleEndInput.value : '' );
		}

		getSelectedCourseIds().forEach( function ( courseId ) {
			body.append( 'course_ids[]', courseId );
		} );

		return body;
	}

	function buildUpdateRequestBody() {
		var body = buildPreviewRequestBody();

		body.set( 'action', config.actions ? config.actions.updatePrices : 'bulk_edit_lp_update_prices' );

		return body;
	}

	function buildLoadCoursesRequestBody( urlParams ) {
		var body = filterForm ? new window.FormData( filterForm ) : new window.FormData();

		body.set( 'action', config.actions ? config.actions.loadCourses : 'bulk_edit_lp_load_courses' );
		body.set( 'nonce', config.nonce || '' );

		if ( urlParams ) {
			urlParams.forEach( function ( value, key ) {
				if ( key !== 'action' && key !== 'nonce' ) {
					body.set( key, value );
				}
			} );
		}

		return body;
	}

	function buildHistoryRequestBody( courseId ) {
		var body = new window.FormData();

		body.append( 'action', config.actions ? config.actions.loadHistory : 'bulk_edit_lp_load_price_history' );
		body.append( 'nonce', config.nonce || '' );
		body.append( 'course_id', courseId );

		return body;
	}

	function setPreviewLoading( isLoading ) {
		previewButton.disabled = isLoading;
		previewButton.classList.toggle( 'is-busy', isLoading );
	}

	function setUpdateLoading( isLoading ) {
		if ( confirmApplyButton ) {
			confirmApplyButton.disabled = isLoading;
			confirmApplyButton.classList.toggle( 'is-busy', isLoading );
		}
	}

	function setCourseLoading( isLoading ) {
		if ( ! courseTableRegion ) {
			return;
		}

		courseTableRegion.setAttribute( 'aria-busy', isLoading ? 'true' : 'false' );
		courseTableRegion.classList.toggle( 'is-loading', isLoading );
	}

	function handlePreviewResponse( data ) {
		var responseData = data && data.data ? data.data : {};

		if ( ! data || ! data.success ) {
			showError( responseData.message || strings.previewFailed || '' );
			return;
		}

		latestPreviewRows = Array.isArray( responseData.rows ) ? responseData.rows : [];

		hideError();

		if ( previewSummary ) {
			previewSummary.hidden = false;
		}

		if ( previewSummaryText ) {
			previewSummaryText.textContent = responseData.summary ?
				responseData.summary.valid + ' ' + strings.ready + ', ' + responseData.summary.skipped + ' ' + strings.skipped + ', ' + responseData.summary.warnings + ' ' + strings.warnings + '.' :
				getSelectedCourseCount() + ' ' + panel.dataset.summaryReady;
		}

		if ( previewTableWrap ) {
			previewTableWrap.hidden = false;
		}

		renderPreviewRows( previewRows );
		panel.dataset.previewReady = responseData.summary && responseData.summary.valid > 0 ? 'true' : 'false';
		applyButton.disabled = panel.dataset.previewReady !== 'true';
	}

	function createReportRow( row ) {
		var tr = document.createElement( 'tr' );
		var messages = []
			.concat( row.errors || [] )
			.concat( row.warnings || [] );
		var cells = [
			row.title || '',
			row.beforeRegularDisplay || '',
			row.beforeSaleDisplay || '',
			( row.currentRegularDisplay || row.afterRegularDisplay || '' ) + ' / ' + ( row.currentSaleDisplay || row.afterSaleDisplay || '' ) + getSaleScheduleText( row ),
			messages.length ? messages.join( ' ' ) : ( row.statusLabel || '' )
		];

		if ( row.status === 'updated' ) {
			tr.classList.add( 'is-updated' );
		} else if ( row.status === 'failed' ) {
			tr.classList.add( 'is-failed' );
		} else if ( row.status === 'skipped' ) {
			tr.classList.add( 'is-skipped' );
		}

		cells.forEach( function ( value ) {
			var td = document.createElement( 'td' );
			td.textContent = value;
			tr.appendChild( td );
		} );

		return tr;
	}

	function createHistoryRow( row ) {
		var tr = document.createElement( 'tr' );
		var cells = [
			row.time || '',
			row.user || '',
			( row.action || '' ) + ( row.bulkValue ? ': ' + row.bulkValue : '' ),
			( row.regularBeforeDisplay || '' ) + ' -> ' + ( row.regularAfterDisplay || '' ),
			( row.saleBeforeDisplay || '' ) + ' -> ' + ( row.saleAfterDisplay || '' ) + ( row.saleScheduleAfter ? ' / ' + row.saleScheduleAfter : '' )
		];

		cells.forEach( function ( value ) {
			var td = document.createElement( 'td' );
			td.textContent = value;
			tr.appendChild( td );
		} );

		return tr;
	}

	function renderUpdateReport( responseData, isSuccess ) {
		var summary = responseData.summary || {};
		var rows = Array.isArray( responseData.rows ) ? responseData.rows : [];

		if ( ! updateReport ) {
			return;
		}

		updateReport.hidden = false;
		updateReport.classList.remove( 'notice-success', 'notice-error', 'notice-warning' );
		updateReport.classList.add( isSuccess && summary.failed < 1 ? 'notice-success' : 'notice-warning' );

		if ( updateReportText ) {
			updateReportText.textContent = isSuccess ?
				( summary.updated || 0 ) + ' ' + strings.updated + ', ' + ( summary.skipped || 0 ) + ' ' + strings.skipped + ', ' + ( summary.failed || 0 ) + ' ' + strings.failed + ', ' + ( summary.warnings || 0 ) + ' ' + strings.warnings + '.' :
				( responseData.message || strings.updateFailed || '' );
		}

		if ( updateReportRows ) {
			updateReportRows.innerHTML = '';
			rows.forEach( function ( row ) {
				updateReportRows.appendChild( createReportRow( row ) );
			} );
		}

		if ( updateReportTableWrap ) {
			updateReportTableWrap.hidden = rows.length < 1;
		}
	}

	function handleLoadCoursesResponse( data, options ) {
		var responseData = data && data.data ? data.data : {};
		options = options || {};

		if ( ! data || ! data.success || ! responseData.html ) {
			showCourseLoadError( responseData.message || strings.loadFailed || '' );
			return;
		}

		hideCourseLoadError();
		resetPreviewState();

		if ( ! options.preserveUpdateReport ) {
			resetUpdateReport();
		}

		if ( courseTableRegion ) {
			courseTableRegion.innerHTML = responseData.html;
		}

		updateControls();
	}

	function resetHistoryState() {
		hideHistoryError();

		if ( historyRows ) {
			historyRows.innerHTML = '';
		}

		if ( historyTableWrap ) {
			historyTableWrap.hidden = true;
		}

		if ( historyEmpty ) {
			historyEmpty.hidden = true;
		}
	}

	function renderHistoryResponse( data ) {
		var responseData = data && data.data ? data.data : {};
		var rows = Array.isArray( responseData.rows ) ? responseData.rows : [];

		if ( ! data || ! data.success ) {
			showHistoryError( responseData.message || strings.historyFailed || '' );
			return;
		}

		if ( historyDescription ) {
			historyDescription.textContent = responseData.title ?
				responseData.title + ': ' + responseData.count + ' ' + ( responseData.count === 1 ? strings.change : strings.changes ) :
				'';
		}

		if ( rows.length < 1 ) {
			if ( historyEmpty ) {
				historyEmpty.hidden = false;
				historyEmpty.textContent = strings.noHistory || historyEmpty.textContent;
			}
			return;
		}

		if ( historyRows ) {
			historyRows.innerHTML = '';
			rows.forEach( function ( row ) {
				historyRows.appendChild( createHistoryRow( row ) );
			} );
		}

		if ( historyTableWrap ) {
			historyTableWrap.hidden = false;
		}
	}

	function loadHistory( courseId ) {
		if ( ! config.ajaxUrl || ! window.fetch || ! window.FormData ) {
			showHistoryError( strings.historyUnavailable || '' );
			return;
		}

		resetHistoryState();

		window.fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: buildHistoryRequestBody( courseId )
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( renderHistoryResponse )
			.catch( function () {
				showHistoryError( strings.historyFailed || '' );
			} );
	}

	function loadCourses( urlParams, options ) {
		options = options || {};

		if ( ! courseTableRegion || ! config.ajaxUrl || ! window.fetch || ! window.FormData ) {
			if ( ! options.silentUnavailable ) {
				showCourseLoadError( strings.loadUnavailable || '' );
			}
			return Promise.resolve( false );
		}

		hideCourseLoadError();
		setCourseLoading( true );

		return window.fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: buildLoadCoursesRequestBody( urlParams )
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( data ) {
				handleLoadCoursesResponse( data, options );
				return data && data.success;
			} )
			.catch( function () {
				if ( ! options.silentError ) {
					showCourseLoadError( strings.loadFailed || '' );
				}
				return false;
			} )
			.finally( function () {
				setCourseLoading( false );
			} );
	}

	function handleUpdateResponse( data ) {
		var responseData = data && data.data ? data.data : {};

		closeModal();

		if ( ! data || ! data.success ) {
			showError( responseData.message || strings.updateFailed || '' );
			renderUpdateReport( responseData, false );
			return;
		}

		hideError();
		renderUpdateReport( responseData, true );
		resetPreviewState();
		updateControls();
		loadCourses( null, {
			preserveUpdateReport: true,
			silentError: true,
			silentUnavailable: true
		} );
	}

	function updateModalSummary() {
		if ( modalCourseCount ) {
			modalCourseCount.textContent = String( getSelectedCourseCount() );
		}

		if ( modalAction ) {
			modalAction.textContent = getActionLabel();
		}

		if ( modalValue ) {
			modalValue.textContent = getActionValueLabel();
		}

		renderPreviewRows( modalPreviewRows );
	}

	function getFocusableElements() {
		var activeModal = null;

		if ( historyModal && ! historyModal.hidden ) {
			activeModal = historyModal;
		} else if ( modal && ! modal.hidden ) {
			activeModal = modal;
		} else if ( bulkActionModal && ! bulkActionModal.hidden ) {
			activeModal = bulkActionModal;
		}

		if ( ! activeModal ) {
			return [];
		}

		return Array.prototype.slice.call(
			activeModal.querySelectorAll( 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])' )
		).filter( function ( element ) {
			return element.offsetParent !== null;
		} );
	}

	function syncModalBodyState() {
		var hasOpenModal = ( historyModal && ! historyModal.hidden ) || ( modal && ! modal.hidden ) || ( bulkActionModal && ! bulkActionModal.hidden );
		document.body.classList.toggle( 'belpcp-modal-open', hasOpenModal );
	}

	function openBulkActionModal() {
		if ( ! bulkActionModal ) {
			return;
		}

		if ( getSelectedCourseCount() < 1 ) {
			showCourseLoadError( panel.dataset.errorNoCourses || '' );
			updateControls();
			return;
		}

		previousBulkFocus = document.activeElement;
		bulkActionModal.hidden = false;
		syncModalBodyState();
		updateControls();

		window.setTimeout( function () {
			( actionSelect || bulkActionDialog || bulkActionModal ).focus();
		}, 0 );
	}

	function closeBulkActionModal() {
		if ( ! bulkActionModal ) {
			return;
		}

		bulkActionModal.hidden = true;
		syncModalBodyState();

		if ( previousBulkFocus && typeof previousBulkFocus.focus === 'function' ) {
			previousBulkFocus.focus();
		}
	}

	function openModal() {
		if ( ! modal || panel.dataset.previewReady !== 'true' ) {
			return;
		}

		previousConfirmFocus = document.activeElement;
		updateModalSummary();
		modal.hidden = false;
		syncModalBodyState();

		window.setTimeout( function () {
			var focusable = getFocusableElements();
			( focusable[0] || modalDialog || modal ).focus();
		}, 0 );
	}

	function closeModal() {
		if ( ! modal ) {
			return;
		}

		modal.hidden = true;
		syncModalBodyState();

		if ( previousConfirmFocus && typeof previousConfirmFocus.focus === 'function' ) {
			previousConfirmFocus.focus();
		}
	}

	function openHistoryModal( courseId ) {
		if ( ! historyModal ) {
			return;
		}

		previousHistoryFocus = document.activeElement;
		historyModal.hidden = false;
		syncModalBodyState();
		loadHistory( courseId );

		window.setTimeout( function () {
			( historyDialog || historyModal ).focus();
		}, 0 );
	}

	function closeHistoryModal() {
		if ( ! historyModal ) {
			return;
		}

		historyModal.hidden = true;
		syncModalBodyState();

		if ( previousHistoryFocus && typeof previousHistoryFocus.focus === 'function' ) {
			previousHistoryFocus.focus();
		}
	}

	root.addEventListener( 'change', function ( event ) {
		if ( event.target.matches( 'input[name="course_ids[]"], #cb-select-all-1, #cb-select-all-2' ) ) {
			resetPreviewState();
			resetUpdateReport();
			updateControls();
		}
	} );

	actionSelect.addEventListener( 'change', function () {
		resetPreviewState();
		resetUpdateReport();
		updateValueField();
		updateControls();
	} );

	valueInput.addEventListener( 'input', function () {
		resetPreviewState();
		resetUpdateReport();
		updateControls();
	} );

	if ( saleStartInput ) {
		saleStartInput.addEventListener( 'input', function () {
			resetPreviewState();
			resetUpdateReport();
			updateControls();
		} );
	}

	if ( saleEndInput ) {
		saleEndInput.addEventListener( 'input', function () {
			resetPreviewState();
			resetUpdateReport();
			updateControls();
		} );
	}

	if ( filterForm && courseTableRegion ) {
		filterForm.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			loadCourses();
		} );

		courseTableRegion.addEventListener( 'click', function ( event ) {
			var link = event.target.closest( '.tablenav-pages a, th a' );
			var expectedPageInput = filterForm.querySelector( 'input[name="page"]' );
			var expectedPage = expectedPageInput ? expectedPageInput.value : '';
			var linkPage;
			var url;

			if ( ! link || ! link.href || ! config.ajaxUrl || ! window.fetch || ! window.FormData ) {
				return;
			}

			url = new URL( link.href, window.location.href );
			linkPage = url.searchParams.get( 'page' );

			if ( linkPage && expectedPage && linkPage !== expectedPage ) {
				return;
			}

			event.preventDefault();
			loadCourses( url.searchParams );
		} );
	}

	previewButton.addEventListener( 'click', function () {
		var validationMessage = getValidationMessage();

		if ( validationMessage ) {
			showError( validationMessage );
			return;
		}

		if ( ! config.ajaxUrl || ! window.fetch || ! window.FormData ) {
			showError( strings.previewUnavailable || '' );
			return;
		}

		hideError();
		setPreviewLoading( true );

		window.fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: buildPreviewRequestBody()
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( handlePreviewResponse )
			.catch( function () {
				showError( strings.previewFailed || '' );
			} )
			.finally( function () {
				setPreviewLoading( false );
			} );
	} );

	applyButton.addEventListener( 'click', openModal );

	if ( openBulkActionButton ) {
		openBulkActionButton.addEventListener( 'click', openBulkActionModal );
	}

	root.addEventListener( 'click', function ( event ) {
		var historyButton = event.target.closest( '[data-view-price-history]' );

		if ( historyButton ) {
			event.preventDefault();
			openHistoryModal( historyButton.dataset.courseId || '' );
			return;
		}

		if ( event.target.closest( '[data-close-modal]' ) ) {
			closeModal();
		}

		if ( event.target.closest( '[data-close-bulk-action]' ) ) {
			closeBulkActionModal();
		}

		if ( event.target.closest( '[data-close-history-modal]' ) ) {
			closeHistoryModal();
		}
	} );

	if ( confirmApplyButton ) {
		confirmApplyButton.addEventListener( 'click', function () {
			var validationMessage = getValidationMessage();

			if ( validationMessage ) {
				showError( validationMessage );
				closeModal();
				return;
			}

			if ( panel.dataset.previewReady !== 'true' ) {
				showError( strings.previewFailed || '' );
				closeModal();
				return;
			}

			if ( ! config.ajaxUrl || ! window.fetch || ! window.FormData ) {
				showError( strings.updateUnavailable || '' );
				closeModal();
				return;
			}

			hideError();
			setUpdateLoading( true );

			window.fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: buildUpdateRequestBody()
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( handleUpdateResponse )
				.catch( function () {
					closeModal();
					showError( strings.updateFailed || '' );
				} )
				.finally( function () {
					setUpdateLoading( false );
				} );
		} );
	}

	document.addEventListener( 'keydown', function ( event ) {
		var focusable;
		var first;
		var last;

		if ( ( ! historyModal || historyModal.hidden ) && ( ! modal || modal.hidden ) && ( ! bulkActionModal || bulkActionModal.hidden ) ) {
			return;
		}

		if ( event.key === 'Escape' ) {
			event.preventDefault();
			if ( historyModal && ! historyModal.hidden ) {
				closeHistoryModal();
			} else if ( modal && ! modal.hidden ) {
				closeModal();
			} else {
				closeBulkActionModal();
			}
			return;
		}

		if ( event.key !== 'Tab' ) {
			return;
		}

		focusable = getFocusableElements();

		if ( focusable.length < 1 ) {
			event.preventDefault();
			return;
		}

		first = focusable[0];
		last = focusable[ focusable.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	updateValueField();
	updateControls();
}() );
