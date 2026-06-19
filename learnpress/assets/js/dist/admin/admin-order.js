/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/js/admin/order/add-courses-to-order.js"
/*!***********************************************************!*\
  !*** ./assets/src/js/admin/order/add-courses-to-order.js ***!
  \***********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _utils_admin_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils-admin.js */ "./assets/src/js/admin/utils-admin.js");
/* harmony import */ var lpAssetsJsPath_utils_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! lpAssetsJsPath/utils.js */ "./assets/src/js/utils.js");


const addCoursesToOrder = () => {
  let elModalSearchCourses;
  let elSearchCoursesResult;
  let elOrderDetails, modalSearchItemsTemplate, modalContainer;
  let elOrderModalFooter, elOrderModalBtnAdd;
  let elListOrderItems;
  let timeOutSearch;
  const idModalSearchItems = '#modal-search-items';
  const idOrderDetails = '#learn-press-order';
  let dataSend = {
    search: '',
    id_not_in: '',
    paged: 1
  };
  const courseIdsNewSelected = [];
  let courseIdsAdded = [];
  const getAllElements = () => {
    elOrderDetails = document.querySelector('#learn-press-order');
    modalSearchItemsTemplate = document.querySelector('#learn-press-modal-search-items');
    modalContainer = document.querySelector('#container-modal-search-items');
  };

  /**
   * Fetch courses from API.
   *
   * @param keySearch
   * @param course_ids_exclude
   * @param paged
   */
  const fetchCoursesAPI = (keySearch = '', course_ids_exclude = [], paged = 1) => {
    let id_not_in = '';
    if (course_ids_exclude.length > 0) {
      id_not_in = course_ids_exclude.join(',');
    }
    dataSend = {
      search: keySearch,
      id_not_in,
      paged
    };
    _utils_admin_js__WEBPACK_IMPORTED_MODULE_0__.AdminUtilsFunctions.fetchCourses(keySearch, dataSend, {
      before() {
        elModalSearchCourses.classList.add('loading');
      },
      success(response) {
        const {
          data,
          status,
          message
        } = response;
        const {
          courses,
          total_pages
        } = data;
        if ('success' !== status) {
          console.error(message);
        } else {
          if (!courses.length) {
            elSearchCoursesResult.innerHTML = '<li class="lp-result-item">No courses found</li>';
            return;
          }
          elSearchCoursesResult.innerHTML = renderSearchResult(courses);
          const paginationHtml = renderPagination(paged, total_pages);
          const searchNav = elModalSearchCourses.querySelector('.search-nav');
          searchNav.innerHTML = paginationHtml;
        }
      },
      error(err) {
        console.error(err);
      },
      completed() {
        elModalSearchCourses.classList.remove('loading');
      }
    });
  };

  /**
   * Get list course ids added.
   */
  const getCoursesAdded = () => {
    courseIdsAdded = [];
    const orderItems = document.querySelectorAll('#learn-press-order .list-order-items tbody .order-item-row');
    orderItems.forEach(orderItem => {
      const orderItemId = parseInt(orderItem.getAttribute('data-id'));
      courseIdsAdded.push(orderItemId);
    });
  };

  /**
   * Add courses to order.
   * @param e
   * @param target
   */
  const addCourses = (e, target) => {
    if (!target.classList.contains('add')) {
      return;
    }
    if (!target.closest(idModalSearchItems)) {
      return;
    }
    elListOrderItems = elOrderDetails.querySelector('.list-order-items');
    e.preventDefault();
    target.disabled = true;
    const dataSend = {
      'lp-ajax': 'add_items_to_order',
      order_id: document.querySelector('#post_ID').value,
      items: courseIdsNewSelected,
      nonce: lpDataAdmin.nonce
    };
    const callBack = {
      success(response) {
        const {
          data,
          messages,
          status
        } = response;
        if ('error' === status) {
          console.error(messages);
          return;
        }
        const {
          item_html,
          order_data
        } = data;
        const elNoItem = elListOrderItems.querySelector('.no-order-items');
        lpAssetsJsPath_utils_js__WEBPACK_IMPORTED_MODULE_1__.lpShowHideEl(elNoItem, 0);
        elNoItem.insertAdjacentHTML('beforebegin', item_html);
        elOrderDetails.querySelector('.order-subtotal').innerHTML = order_data.subtotal_html;
        elOrderDetails.querySelector('.order-total').innerHTML = order_data.total_html;
        //courseIdsAdded.push( ...courseIdsNewSelected );
        courseIdsNewSelected.splice(0, courseIdsNewSelected.length);
      },
      error(err) {
        console.error(err);
      },
      completed() {
        target.disabled = false;
        modalContainer.style.display = 'none';
      }
    };
    _utils_admin_js__WEBPACK_IMPORTED_MODULE_0__.Utils.lpFetchAPI(_utils_admin_js__WEBPACK_IMPORTED_MODULE_0__.Utils.lpAddQueryArgs(_utils_admin_js__WEBPACK_IMPORTED_MODULE_0__.Utils.lpGetCurrentURLNoParam(), dataSend), {}, callBack);
  };

  /**
   * Remove course from order.
   *
   * @param e
   * @param target
   */
  const removeCourse = (e, target) => {
    if (target.tagName !== 'SPAN') {
      return;
    }
    if (!target.closest('.remove-order-item')) {
      return;
    }
    e.preventDefault();
    if (!confirm('Are you sure you want to remove this item?')) {
      return;
    }
    target.disabled = true;
    target.classList.add('dashicons-update');
    const elItemRow = target.closest('.order-item-row');
    const elListOrderItems = target.closest('.list-order-items');
    const orderItemId = parseInt(elItemRow.getAttribute('data-item_id'));
    const courseId = parseInt(elItemRow.getAttribute('data-id'));
    const dataSend = {
      'lp-ajax': 'remove_items_from_order',
      order_id: document.querySelector('#post_ID').value,
      items: orderItemId,
      nonce: lpDataAdmin.nonce
    };
    const callBack = {
      success(response) {
        const {
          data,
          messages,
          status
        } = response;
        if ('error' === status) {
          console.error(messages);
          return;
        }
        const {
          item_html,
          order_data
        } = data;
        const elNoItem = elListOrderItems.querySelector('.no-order-items');
        const orderItems = elListOrderItems.querySelectorAll('.order-item-row');
        orderItems.forEach(orderItem => {
          orderItem.remove();
        });
        if (item_html.length) {
          elNoItem.insertAdjacentHTML('beforebegin', item_html);
        } else {
          lpAssetsJsPath_utils_js__WEBPACK_IMPORTED_MODULE_1__.lpShowHideEl(elNoItem, 1);
        }
        courseIdsNewSelected.splice(courseIdsNewSelected.indexOf(courseId), 1);
        //courseIdsAdded.splice( courseIdsNewSelected.indexOf( courseId ), 1 );
        elOrderDetails.querySelector('.order-subtotal').innerHTML = order_data.subtotal_html;
        elOrderDetails.querySelector('.order-total').innerHTML = order_data.total_html;
      },
      error(err) {
        console.error(err);
      },
      completed() {}
    };
    _utils_admin_js__WEBPACK_IMPORTED_MODULE_0__.Utils.lpFetchAPI(_utils_admin_js__WEBPACK_IMPORTED_MODULE_0__.Utils.lpAddQueryArgs(_utils_admin_js__WEBPACK_IMPORTED_MODULE_0__.Utils.lpGetCurrentURLNoParam(), dataSend), {}, callBack);
  };

  /**
   * Search courses before add Order.
   *
   * @param e
   * @param target
   */
  const searchCourse = (e, target) => {
    if ('search' !== target.name) {
      return;
    }
    const elLPTarget = target.closest(idModalSearchItems);
    if (!elLPTarget) {
      return;
    }
    e.preventDefault();
    const keyword = target.value;
    if (!keyword || keyword && keyword.length > 2) {
      if (undefined !== timeOutSearch) {
        clearTimeout(timeOutSearch);
      }
      timeOutSearch = setTimeout(function () {
        fetchCoursesAPI(keyword, courseIdsAdded, 1);
      }, 800);
    }
  };

  /**
   * Display list courses when search done.
   *
   * @param courses
   */
  const renderSearchResult = courses => {
    let html = '';
    courses.forEach(course => {
      const courseId = parseInt(course.ID);
      const checked = courseIdsNewSelected.includes(courseId) ? 'checked' : '';
      html += `
			<li class="lp-result-item" data-id="${courseId}" data-type="lp_course" data-text="${course.post_title}">
				<label>
					<input type="checkbox" value="${courseId}" name="selectedItems[]" ${checked}>
					<span class="lp-item-text">${course.post_title} (#${courseId})</span>
				</label>
			</li>`;
    });
    return html;
  };

  /**
   * Render pagination.
   *
   * @param currentPage
   * @param maxPage
   */
  const renderPagination = (currentPage, maxPage) => {
    currentPage = parseInt(currentPage);
    maxPage = parseInt(maxPage);
    let html = '';
    if (maxPage <= 1) {
      return html;
    }
    const nextPage = currentPage + 1;
    const prevPage = currentPage - 1;
    let pages = [];
    if (maxPage <= 9) {
      for (let i = 1; i <= maxPage; i++) {
        pages.push(i);
      }
    } else if (currentPage <= 3) {
      // x is ...
      pages = [1, 2, 3, 4, 5, 'x', maxPage];
    } else if (currentPage <= 5) {
      for (let i = 1; i <= currentPage; i++) {
        pages.push(i);
      }
      for (let j = 1; j <= 2; j++) {
        const tempPage = currentPage + j;
        pages.push(tempPage);
      }
      pages.push('x');
      pages.push(maxPage);
    } else {
      pages = [1, 'x'];
      for (let k = 2; k >= 0; k--) {
        const tempPage = currentPage - k;
        pages.push(tempPage);
      }
      const currentToLast = maxPage - currentPage;
      if (currentToLast <= 5) {
        for (let m = currentPage + 1; m <= maxPage; m++) {
          pages.push(m);
        }
      } else {
        for (let n = 1; n <= 2; n++) {
          const tempPage = currentPage + n;
          pages.push(tempPage);
        }
        pages.push('x');
        pages.push(maxPage);
      }
    }
    const maximum = pages.length;
    if (currentPage !== 1) {
      html += `<a class="prev page-numbers button" href="#" data-page="${prevPage}"><</a>`;
    }
    for (let i = 0; i < maximum; i++) {
      if (currentPage === parseInt(pages[i])) {
        html += `<a aria-current="page" class="page-numbers current button disabled" data-page="${pages[i]}">
				${pages[i]}
			</a>`;
      } else if (pages[i] === 'x') {
        html += `<span class="page-numbers dots button disabled">...</span>`;
      } else {
        html += `<a class="page-numbers button" href="#" data-page="${pages[i]}">${pages[i]} </a>`;
      }
    }
    if (currentPage !== maxPage) {
      html += `<a class="next page-numbers button" href="#" data-page="${nextPage}">></a>`;
    }
    return html;
  };
  const showPopupSearchCourses = () => {
    getCoursesAdded();
    modalContainer.style.display = 'block';
    elOrderModalBtnAdd.style.display = 'none';
    elSearchCoursesResult.innerHTML = '';
    fetchCoursesAPI(dataSend.search, courseIdsAdded, dataSend.paged);
  };

  // Events.
  document.addEventListener('click', e => {
    const target = e.target;
    //console.dir( target );
    if (target.id === 'learn-press-add-order-item') {
      e.preventDefault();
      showPopupSearchCourses();
    }
    if (target.classList.contains('close') && target.closest(idModalSearchItems)) {
      e.preventDefault();
      elModalSearchCourses.querySelector('input[name="search"]').value = '';
      dataSend.search = '';
      dataSend.paged = 1;
      modalContainer.style.display = 'none';
    }
    if (target.classList.contains('page-numbers')) {
      if (target.closest(idModalSearchItems)) {
        e.preventDefault();
        const paged = target.getAttribute('data-page');
        fetchCoursesAPI(dataSend.search, dataSend.id_not_in, paged);
      }
    }
    if (target.name === 'selectedItems[]') {
      if (target.closest(idModalSearchItems)) {
        const courseId = parseInt(target.value);
        if (target.checked) {
          courseIdsNewSelected.push(courseId);
        } else {
          const index = courseIdsNewSelected.indexOf(courseId);
          if (index > -1) {
            courseIdsNewSelected.splice(index, 1);
          }
        }
        elOrderModalBtnAdd.style.display = courseIdsNewSelected.length > 0 ? 'block' : 'none';
      }
    }
    addCourses(e, target);
    removeCourse(e, target);
  });
  document.addEventListener('keyup', function (e) {
    const target = e.target;
    searchCourse(e, target);
  });
  lpAssetsJsPath_utils_js__WEBPACK_IMPORTED_MODULE_1__.lpOnElementReady('.lp-order-detail-items', el => {
    getAllElements();
    if (!elOrderDetails) {
      return;
    }
    modalContainer.innerHTML = modalSearchItemsTemplate.innerHTML;
    elModalSearchCourses = modalContainer.querySelector(idModalSearchItems);
    elSearchCoursesResult = elModalSearchCourses.querySelector('.search-results');
    elOrderModalFooter = elModalSearchCourses.querySelector('footer');
    elOrderModalBtnAdd = elOrderModalFooter.querySelector('.add');
    modalContainer.style.display = 'none';
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addCoursesToOrder);

/***/ },

/***/ "./assets/src/js/admin/order/export_invoice.js"
/*!*****************************************************!*\
  !*** ./assets/src/js/admin/order/export_invoice.js ***!
  \*****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ export_invoice)
/* harmony export */ });
/**
 * Export invoice to PDF
 */
function export_invoice() {
  let html2pdf_obj, modal;
  document.addEventListener('click', e => {
    const target = e.target;
    if (target.id === 'lp-invoice__export') {
      html2pdf_obj.save();
    } else if (target.id === 'lp-invoice__update') {
      const elOption = document.querySelector('.export-options__content');
      const fields = elOption.querySelectorAll('input');
      const fieldNameUnChecked = [];
      fields.forEach(field => {
        if (!field.checked) {
          fieldNameUnChecked.push(field.name);
        }
      });
      window.localStorage.setItem('lp_invoice_un_fields', JSON.stringify(fieldNameUnChecked));
      window.localStorage.setItem('lp_invoice_show', 1);
      window.location.reload();
    }
  });
  const exportPDF = () => {
    const pdfOptions = {
      margin: [0, 0, 0, 5],
      filename: document.title,
      image: {
        type: 'webp'
      },
      html2canvas: {
        scale: 2.5
      },
      jsPDF: {
        format: 'a4',
        orientation: 'p'
      }
    };
    const html = document.querySelector('#lp-invoice__content');
    html2pdf_obj = html2pdf().set(pdfOptions).from(html);
  };
  const showInfoFields = () => {
    // Get fields name checked
    const fieldsChecked = window.localStorage.getItem('lp_invoice_un_fields');
    const elOptions = document.querySelector('.export-options__content');
    const elInvoiceFields = document.querySelectorAll('.invoice-field');
    elInvoiceFields.forEach(field => {
      const nameClass = field.classList[1];
      if (fieldsChecked && fieldsChecked.includes(nameClass)) {
        field.remove();
        const elOption = elOptions.querySelector(`[name=${nameClass}]`);
        if (elOption) {
          elOption.checked = false;
        }
      }
    });
    const showInvoice = parseInt(window.localStorage.getItem('lp_invoice_show'));
    if (showInvoice === 1) {
      modal.style.display = 'block';
    }
  };
  document.addEventListener('DOMContentLoaded', () => {
    const elExportSection = document.querySelector('#order-export__section');
    if (!elExportSection.length) {
      const tabs = document.querySelectorAll('.tabs');
      const tab = document.querySelectorAll('.tab');
      const panel = document.querySelectorAll('.panel');
      function onTabClick(event) {
        // deactivate existing active tabs and panel

        for (let i = 0; i < tab.length; i++) {
          tab[i].classList.remove('active');
        }
        for (let i = 0; i < panel.length; i++) {
          panel[i].classList.remove('active');
        }

        // activate new tabs and panel
        event.target.classList.add('active');
        const classString = event.target.getAttribute('data-target');
        document.getElementById('panels').getElementsByClassName(classString)[0].classList.add('active');
      }
      for (let i = 0; i < tab.length; i++) {
        tab[i].addEventListener('click', onTabClick, false);
      }

      // Get the modal
      modal = document.getElementById('myModal');
      // Get the button that opens the modal
      const btn = document.getElementById('order-export__button');
      // Get the <span> element that closes the modal
      const span = document.getElementsByClassName('close')[0];
      // When the user clicks on the button, open the modal
      btn.onclick = function () {
        modal.style.display = 'block';
      };

      // When the user clicks on <span> (x), close the modal
      span.onclick = function () {
        modal.style.display = 'none';
        window.localStorage.setItem('lp_invoice_show', 0);
      };

      // When the user clicks anywhere outside the modal, close it
      window.onclick = function (event) {
        if (event.target === modal) {
          modal.style.display = 'none';
          window.localStorage.setItem('lp_invoice_show', 0);
        }
      };
      showInfoFields();
      exportPDF();
    }
  });
}

/***/ },

/***/ "./assets/src/js/admin/order/refund-order.js"
/*!***************************************************!*\
  !*** ./assets/src/js/admin/order/refund-order.js ***!
  \***************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RefundOrder: () => (/* binding */ RefundOrder),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var lpAssetsJsPath_lpToastify__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! lpAssetsJsPath/lpToastify */ "./assets/src/js/lpToastify.js");
/* harmony import */ var lpAssetsJsPath_utils_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! lpAssetsJsPath/utils.js */ "./assets/src/js/utils.js");




/**
 * Handle admin approve/deny refund actions.
 *
 * @since 4.3.9
 * @version 1.0.0
 */
class RefundOrder {
  constructor() {
    this.isRequesting = false;
    this.isReloading = false;
  }
  static selectors = {
    panel: '.order-data-refund-request',
    action: '.lp-admin-refund-order-action'
  };
  init() {
    this.events();
  }
  events() {
    if (RefundOrder._loadedEvents) {
      return;
    }
    RefundOrder._loadedEvents = this;
    lpAssetsJsPath_utils_js__WEBPACK_IMPORTED_MODULE_2__.eventHandlers('click', [{
      selector: RefundOrder.selectors.action,
      class: this,
      callBack: this.handleAction.name
    }]);
  }
  getPanelData(panel) {
    const orderTotal = parseFloat(panel.dataset.orderTotal || '0');
    return {
      orderId: parseInt(panel.dataset.orderId || '0', 10),
      orderTotal: Number.isNaN(orderTotal) ? 0 : orderTotal,
      orderTotalFormatted: panel.dataset.orderTotalFormatted || '',
      confirmTitle: panel.dataset.confirmTitle || 'Approve refund?',
      confirmText: panel.dataset.confirmText || '',
      messageLabel: panel.dataset.messageLabel || 'Message to payer',
      messagePlaceholder: panel.dataset.messagePlaceholder || '',
      amountLabel: panel.dataset.amountLabel || 'Refund amount',
      amountInvalid: panel.dataset.amountInvalid || 'Invalid refund amount.',
      confirmButton: panel.dataset.confirmButton || 'Approve Refund',
      cancelButton: panel.dataset.cancelButton || 'Cancel'
    };
  }
  setLoadingState(panel, isLoading) {
    panel.querySelectorAll(RefundOrder.selectors.action).forEach(button => {
      button.disabled = isLoading;
    });
  }
  openApproveModal(data) {
    const content = document.createElement('div');
    const messageLabel = document.createElement('label');
    const message = document.createElement('textarea');
    const amountLabel = document.createElement('label');
    const amount = document.createElement('input');
    content.className = 'lp-admin-refund-modal__form';
    if (data.confirmText) {
      const confirmText = document.createElement('p');
      confirmText.className = 'lp-admin-refund-modal__description';
      confirmText.textContent = data.confirmText;
      content.append(confirmText);
    }
    messageLabel.textContent = data.messageLabel;
    messageLabel.htmlFor = 'lp-admin-refund-message';
    messageLabel.className = 'swal2-input-label';
    message.id = 'lp-admin-refund-message';
    message.className = 'swal2-textarea';
    message.placeholder = data.messagePlaceholder;
    amountLabel.textContent = `${data.amountLabel} (${data.orderTotalFormatted})`;
    amountLabel.htmlFor = 'lp-admin-refund-amount';
    amountLabel.className = 'swal2-input-label';
    amount.id = 'lp-admin-refund-amount';
    amount.className = 'swal2-input';
    amount.type = 'number';
    amount.min = '0.01';
    amount.max = data.orderTotal.toString();
    amount.step = '0.01';
    amount.value = data.orderTotal.toFixed(2);
    content.append(messageLabel, message, amountLabel, amount);
    return sweetalert2__WEBPACK_IMPORTED_MODULE_0___default().fire({
      icon: 'warning',
      title: data.confirmTitle,
      html: content,
      showCancelButton: true,
      confirmButtonText: data.confirmButton,
      cancelButtonText: data.cancelButton,
      focusConfirm: false,
      customClass: {
        popup: 'lp-admin-refund-modal',
        htmlContainer: 'lp-admin-refund-modal__content',
        actions: 'lp-admin-refund-modal__actions'
      },
      preConfirm: () => {
        const refundAmount = parseFloat(amount.value);
        if (Number.isNaN(refundAmount) || refundAmount <= 0 || refundAmount > data.orderTotal) {
          sweetalert2__WEBPACK_IMPORTED_MODULE_0___default().showValidationMessage(data.amountInvalid);
          return false;
        }
        return {
          note: message.value.trim(),
          refundAmount
        };
      }
    });
  }
  sendAction(actionButton, panel, refundAction, refundAmount = 0, note = '') {
    const data = this.getPanelData(panel);
    if (!data.orderId) {
      lpAssetsJsPath_lpToastify__WEBPACK_IMPORTED_MODULE_1__.show('Invalid order.', 'error');
      return;
    }
    this.isRequesting = true;
    this.setLoadingState(panel, true);
    lpAssetsJsPath_utils_js__WEBPACK_IMPORTED_MODULE_2__.lpSetLoadingEl(actionButton, 1);
    window.lpAJAXG.fetchAJAX({
      action: 'admin_handle_request_refund',
      order_id: data.orderId,
      refund_action: refundAction,
      refund_amount: refundAmount,
      note
    }, {
      success: response => {
        const {
          status,
          message,
          data
        } = response;
        if (status !== 'success') {
          throw new Error(message);
        }
        lpAssetsJsPath_lpToastify__WEBPACK_IMPORTED_MODULE_1__.show(message, 'success');
        this.isReloading = true;
        window.setTimeout(() => window.location.reload(), 1200);
      },
      error: error => {
        const messageResponse = error?.message || error || 'Refund action failed.';
        lpAssetsJsPath_lpToastify__WEBPACK_IMPORTED_MODULE_1__.show(messageResponse, 'error');
      },
      completed: () => {
        if (this.isReloading) {
          return;
        }
        this.isRequesting = false;
        this.setLoadingState(panel, false);
        lpAssetsJsPath_utils_js__WEBPACK_IMPORTED_MODULE_2__.lpSetLoadingEl(actionButton, 0);
      }
    });
  }
  async handleAction(args) {
    const {
      e,
      target
    } = args;
    e.preventDefault();
    const actionButton = target.closest(RefundOrder.selectors.action);
    const panel = actionButton?.closest(RefundOrder.selectors.panel);
    if (!actionButton || !panel || this.isRequesting) {
      return;
    }
    const refundAction = actionButton.dataset.refundAction || '';
    let amount = '';
    let note = '';
    if ('reject' === refundAction) {
      return this.sendAction(actionButton, panel, refundAction);
    }
    const result = await this.openApproveModal(this.getPanelData(panel));
    if (result.isConfirmed && result.value) {
      amount = result.value.refundAmount;
      note = result.value.note;
      this.sendAction(actionButton, panel, refundAction, amount, note);
    }
  }
}
const refundOrder = () => {
  const refundOrderHandle = new RefundOrder();
  lpAssetsJsPath_utils_js__WEBPACK_IMPORTED_MODULE_2__.lpOnElementReady(RefundOrder.selectors.action, () => {
    refundOrderHandle.init();
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (refundOrder);

/***/ },

/***/ "./assets/src/js/admin/utils-admin.js"
/*!********************************************!*\
  !*** ./assets/src/js/admin/utils-admin.js ***!
  \********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AdminUtilsFunctions: () => (/* binding */ AdminUtilsFunctions),
/* harmony export */   Api: () => (/* reexport safe */ _api_js__WEBPACK_IMPORTED_MODULE_2__["default"]),
/* harmony export */   Utils: () => (/* reexport module object */ _utils_js__WEBPACK_IMPORTED_MODULE_0__)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils.js */ "./assets/src/js/utils.js");
/* harmony import */ var tom_select__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tom-select */ "./node_modules/tom-select/dist/esm/tom-select.complete.js");
/* harmony import */ var _api_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../api.js */ "./assets/src/js/api.js");
/**
 * Library run on Admin
 *
 * @since 4.2.6.9
 * @version 1.0.1
 */



const AdminUtilsFunctions = {
  buildTomSelect(elTomSelect, options, fetchAPI, dataSend, callBackHandleData) {
    if (!elTomSelect) {
      return;
    }
    const optionDefault = {
      plugins: {
        remove_button: {
          title: 'Remove this item'
        },
        dropdown_input: {}
      },
      onInitialize() {},
      onItemAdd(e) {
        // Get list without current item.
        if (fetchAPI) {
          const selectedOptions = Array.from(elTomSelect.selectedOptions);
          const selectedValues = selectedOptions.map(option => option.value);
          selectedValues.push(e);
          dataSend.id_not_in = selectedValues.join(',');
          fetchAPI('', dataSend, callBackHandleData);
        }
      }
    };
    if (fetchAPI) {
      optionDefault.load = (keySearch, callbackTom) => {
        const selectedOptions = Array.from(elTomSelect.selectedOptions);
        const selectedValues = selectedOptions.map(option => option.value);
        dataSend.id_not_in = selectedValues.join(',');
        fetchAPI(keySearch, dataSend, AdminUtilsFunctions.callBackTomSelectSearchAPI(callbackTom, callBackHandleData));
      };
    }
    options = {
      ...optionDefault,
      ...options
    };
    const items_selected = options.options;
    /*if ( options?.options?.length > 20 ) {
    	const chunkSize = 20;
    	const length = options.options.length;
    	let i = 0;
    	const chunkedOptions = { ...options };
    	chunkedOptions.options = items_selected.slice( i, chunkSize );
    		const tomSelect = new TomSelect( elTomSelect, chunkedOptions );
    	i += chunkSize;
    		const interval = setInterval( () => {
    		if ( i > ( length - 1 ) ) {
    			clearInterval( interval );
    		}
    			const optionsSlice = items_selected.slice( i, i + chunkSize );
    		i += chunkSize;
    		tomSelect.addOptions( optionsSlice );
    		tomSelect.setValue( options.items );
    	}, 200 );
    		return tomSelect;
    }*/

    return new tom_select__WEBPACK_IMPORTED_MODULE_1__["default"](elTomSelect, options);
  },
  callBackTomSelectSearchAPI(callbackTom, callBackHandleData) {
    return {
      success: response => {
        const options = callBackHandleData.success(response);
        callbackTom(options);
      }
    };
  },
  fetchCourses(keySearch = '', dataSend = {}, callback) {
    const url = _api_js__WEBPACK_IMPORTED_MODULE_2__["default"].admin.apiSearchCourses;
    dataSend.search = keySearch;
    const params = {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': lpDataAdmin.nonce
      },
      method: 'POST',
      body: JSON.stringify(dataSend)
    };
    _utils_js__WEBPACK_IMPORTED_MODULE_0__.lpFetchAPI(url, params, callback);
  },
  fetchUsers(keySearch = '', dataSend = {}, callback) {
    const url = _api_js__WEBPACK_IMPORTED_MODULE_2__["default"].admin.apiSearchUsers;
    dataSend.search = keySearch;
    const params = {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': lpDataAdmin.nonce
      },
      method: 'POST',
      body: JSON.stringify(dataSend)
    };
    _utils_js__WEBPACK_IMPORTED_MODULE_0__.lpFetchAPI(url, params, callback);
  }
};


/***/ },

/***/ "./assets/src/js/api.js"
/*!******************************!*\
  !*** ./assets/src/js/api.js ***!
  \******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * List API on backend
 *
 * @since 4.2.6
 * @version 1.0.2
 */

const lplistAPI = {};
let lp_rest_url;
if ('undefined' !== typeof lpDataAdmin) {
  lp_rest_url = lpDataAdmin.lp_rest_url;
  lplistAPI.admin = {
    apiAdminNotice: lp_rest_url + 'lp/v1/admin/tools/admin-notices',
    apiAddons: lp_rest_url + 'lp/v1/addon/all',
    apiAddonAction: lp_rest_url + 'lp/v1/addon/action-n',
    apiAddonsPurchase: lp_rest_url + 'lp/v1/addon/info-addons-purchase',
    apiSearchCourses: lp_rest_url + 'lp/v1/admin/tools/search-course',
    apiSearchUsers: lp_rest_url + 'lp/v1/admin/tools/search-user',
    apiAssignUserCourse: lp_rest_url + 'lp/v1/admin/tools/assign-user-course',
    apiUnAssignUserCourse: lp_rest_url + 'lp/v1/admin/tools/unassign-user-course'
  };
}
if ('undefined' !== typeof lpData) {
  lp_rest_url = lpData.lp_rest_url;
  lplistAPI.frontend = {
    apiWidgets: lp_rest_url + 'lp/v1/widgets/api',
    apiCourses: lp_rest_url + 'lp/v1/courses/archive-course',
    // Deprecated API, don't load from v4.3.7
    apiAJAX: lp_rest_url + 'lp/v1/load_content_via_ajax/',
    // Deprecated since 4.3.0
    apiProfileCoverImage: lp_rest_url + 'lp/v1/profile/cover-image'
  };
}
if (lp_rest_url) {
  lplistAPI.apiCourses = lp_rest_url + 'lp/v1/courses/';
  lplistAPI.apiEditCoursesArchiveBlock = lp_rest_url + 'lp/v1/courses/edit-archive-block';
  lplistAPI.apiCoursesSuggest = lp_rest_url + 'lp/v1/courses/courses-suggest';
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (lplistAPI);

/***/ },

/***/ "./assets/src/js/lpToastify.js"
/*!*************************************!*\
  !*** ./assets/src/js/lpToastify.js ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   show: () => (/* binding */ show)
/* harmony export */ });
/* harmony import */ var toastify_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! toastify-js */ "./node_modules/toastify-js/src/toastify.js");
/* harmony import */ var toastify_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(toastify_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var toastify_js_src_toastify_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! toastify-js/src/toastify.css */ "./node_modules/toastify-js/src/toastify.css");
/**
 * Utils functions
 *
 * @param url
 * @param data
 * @param functions
 * @since 4.3.0
 * @version 1.0.0
 */


const argsToastify = {
  text: '',
  gravity: lpData.toast.gravity,
  // `top` or `bottom`
  position: lpData.toast.position,
  // `left`, `center` or `right`
  className: `${lpData.toast.classPrefix}`,
  close: lpData.toast.close == 1,
  stopOnFocus: lpData.toast.stopOnFocus == 1,
  duration: lpData.toast.duration
};
const show = (message, status = 'success', argsCustom) => {
  let args = argsToastify;
  if (argsCustom) {
    args = {
      ...args,
      ...argsCustom
    };
  }
  const toastify = new (toastify_js__WEBPACK_IMPORTED_MODULE_0___default())({
    ...args,
    text: message,
    className: `${lpData.toast.classPrefix} ${status}`
  });
  toastify.showToast();
};

/***/ },

/***/ "./assets/src/js/utils.js"
/*!********************************!*\
  !*** ./assets/src/js/utils.js ***!
  \********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   debounce: () => (/* binding */ debounce),
/* harmony export */   eventHandlers: () => (/* binding */ eventHandlers),
/* harmony export */   getDataOfForm: () => (/* binding */ getDataOfForm),
/* harmony export */   getFieldKeysOfForm: () => (/* binding */ getFieldKeysOfForm),
/* harmony export */   listenElementCreated: () => (/* binding */ listenElementCreated),
/* harmony export */   listenElementViewed: () => (/* binding */ listenElementViewed),
/* harmony export */   lpAddQueryArgs: () => (/* binding */ lpAddQueryArgs),
/* harmony export */   lpAjaxParseJsonOld: () => (/* binding */ lpAjaxParseJsonOld),
/* harmony export */   lpClassName: () => (/* binding */ lpClassName),
/* harmony export */   lpFetchAPI: () => (/* binding */ lpFetchAPI),
/* harmony export */   lpGetCurrentURLNoParam: () => (/* binding */ lpGetCurrentURLNoParam),
/* harmony export */   lpOnElementReady: () => (/* binding */ lpOnElementReady),
/* harmony export */   lpSetLoadingEl: () => (/* binding */ lpSetLoadingEl),
/* harmony export */   lpShowHideEl: () => (/* binding */ lpShowHideEl),
/* harmony export */   mergeDataWithDatForm: () => (/* binding */ mergeDataWithDatForm),
/* harmony export */   toggleCollapse: () => (/* binding */ toggleCollapse)
/* harmony export */ });
/**
 * Utils functions
 *
 * @param url
 * @param data
 * @param functions
 * @since 4.2.5.1
 * @version 1.0.6
 */
const lpClassName = {
  hidden: 'lp-hidden',
  loading: 'loading',
  elCollapse: 'lp-collapse',
  elSectionToggle: '.lp-section-toggle',
  elTriggerToggle: '.lp-trigger-toggle'
};
const lpFetchAPI = (url, data = {}, functions = {}) => {
  if ('function' === typeof functions.before) {
    functions.before();
  }
  fetch(url, {
    method: 'GET',
    ...data
  }).then(response => response.json()).then(response => {
    if ('function' === typeof functions.success) {
      functions.success(response);
    }
  }).catch(err => {
    if ('function' === typeof functions.error) {
      functions.error(err);
    }
  }).finally(() => {
    if ('function' === typeof functions.completed) {
      functions.completed();
    }
  });
};

/**
 * Get current URL without params.
 *
 * @since 4.2.5.1
 */
const lpGetCurrentURLNoParam = () => {
  let currentUrl = window.location.href;
  const hasParams = currentUrl.includes('?');
  if (hasParams) {
    currentUrl = currentUrl.split('?')[0];
  }
  return currentUrl;
};
const lpAddQueryArgs = (endpoint, args) => {
  const url = new URL(endpoint);
  Object.keys(args).forEach(arg => {
    url.searchParams.set(arg, args[arg]);
  });
  return url;
};

/**
 * Listen element viewed.
 *
 * @param el
 * @param callback
 * @since 4.2.5.8
 */
const listenElementViewed = (el, callback) => {
  const observerSeeItem = new IntersectionObserver(function (entries) {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        callback(entry);
      }
    }
  });
  observerSeeItem.observe(el);
};

/**
 * Listen element created.
 *
 * @param callback
 * @since 4.2.5.8
 */
const listenElementCreated = callback => {
  const observerCreateItem = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.addedNodes) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) {
            callback(node);
          }
        });
      }
    });
  });
  observerCreateItem.observe(document, {
    childList: true,
    subtree: true
  });
  // End.
};

/**
 * Listen element created.
 *
 * @param selector
 * @param callback
 * @since 4.2.7.1
 */
const lpOnElementReady = (selector, callback) => {
  const element = document.querySelector(selector);
  if (element) {
    callback(element);
    return;
  }
  const observer = new MutationObserver((mutations, obs) => {
    const element = document.querySelector(selector);
    if (element) {
      obs.disconnect();
      callback(element);
    }
  });
  observer.observe(document.documentElement, {
    childList: true,
    subtree: true
  });
};

// Parse JSON from string with content include LP_AJAX_START.
const lpAjaxParseJsonOld = data => {
  if (typeof data !== 'string') {
    return data;
  }
  const m = String.raw({
    raw: data
  }).match(/<-- LP_AJAX_START -->(.*)<-- LP_AJAX_END -->/s);
  try {
    if (m) {
      data = JSON.parse(m[1].replace(/(?:\r\n|\r|\n)/g, ''));
    } else {
      data = JSON.parse(data);
    }
  } catch (e) {
    data = {};
  }
  return data;
};

// status 0: hide, 1: show
const lpShowHideEl = (el, status = 0) => {
  if (!el) {
    return;
  }
  if (!status) {
    el.classList.add(lpClassName.hidden);
  } else {
    el.classList.remove(lpClassName.hidden);
  }
};

// status 0: hide, 1: show
const lpSetLoadingEl = (el, status) => {
  if (!el) {
    return;
  }
  if (!status) {
    el.classList.remove(lpClassName.loading);
  } else {
    el.classList.add(lpClassName.loading);
  }
};

// Toggle collapse section
const toggleCollapse = (e, target, elTriggerClassName = '', elsExclude = [], callback) => {
  if (!elTriggerClassName) {
    elTriggerClassName = lpClassName.elTriggerToggle;
  }

  // Exclude elements, which should not trigger the collapse toggle
  if (elsExclude && elsExclude.length > 0) {
    for (const elExclude of elsExclude) {
      if (target.closest(elExclude)) {
        return;
      }
    }
  }
  const elTrigger = target.closest(elTriggerClassName);
  if (!elTrigger) {
    return;
  }

  //console.log( 'elTrigger', elTrigger );

  const elSectionToggle = elTrigger.closest(`${lpClassName.elSectionToggle}`);
  if (!elSectionToggle) {
    return;
  }
  elSectionToggle.classList.toggle(`${lpClassName.elCollapse}`);
  if ('function' === typeof callback) {
    callback(elSectionToggle);
  }
};

// Get data of form
const getDataOfForm = form => {
  const dataSend = {};
  const formData = new FormData(form);
  for (const pair of formData.entries()) {
    const key = pair[0];
    const value = formData.getAll(key);
    if (!dataSend.hasOwnProperty(key)) {
      // Convert value array to string.
      dataSend[key] = value.join(',');
    }
  }
  return dataSend;
};

// Get field keys of form
const getFieldKeysOfForm = form => {
  const keys = [];
  const elements = form.elements;
  for (let i = 0; i < elements.length; i++) {
    const name = elements[i].name;
    if (name && !keys.includes(name)) {
      keys.push(name);
    }
  }
  return keys;
};

// Merge data handle with data form.
const mergeDataWithDatForm = (elForm, dataHandle) => {
  const dataForm = getDataOfForm(elForm);
  const keys = getFieldKeysOfForm(elForm);
  keys.forEach(key => {
    if (!dataForm.hasOwnProperty(key)) {
      delete dataHandle[key];
    } else if (dataForm[key][0] === '') {
      delete dataForm[key];
      delete dataHandle[key];
    }
  });
  dataHandle = {
    ...dataHandle,
    ...dataForm
  };
  return dataHandle;
};

/**
 * Event trigger
 * For each list of event handlers, listen event on document.
 *
 * eventName: 'click', 'change', ...
 * eventHandlers = [ { selector: '.lp-button', callBack: function(){}, class: object } ]
 *
 * @param eventName
 * @param eventHandlers
 */
const eventHandlers = (eventName, eventHandlers) => {
  document.addEventListener(eventName, e => {
    const target = e.target;
    let args = {
      e,
      target
    };
    eventHandlers.forEach(eventHandler => {
      args = {
        ...args,
        ...eventHandler
      };

      //console.log( args );

      // Check condition before call back
      if (eventHandler.conditionBeforeCallBack) {
        if (eventHandler.conditionBeforeCallBack(args) !== true) {
          return;
        }
      }

      // Special check for keydown event with checkIsEventEnter = true
      if (eventName === 'keydown' && eventHandler.checkIsEventEnter) {
        if (e.key !== 'Enter') {
          return;
        }
      }
      if (target.closest(eventHandler.selector)) {
        if (eventHandler.class) {
          // Call method of class, function callBack will understand exactly {this} is class object.
          eventHandler.class[eventHandler.callBack](args);
        } else {
          // For send args is objected, {this} is eventHandler object, not class object.
          eventHandler.callBack(args);
        }
      }
    });
  });
};

/**
 * Debounce - delays function execution until after `wait` ms of inactivity.
 *
 * Each call resets the timer. Only the last call in a burst executes.
 *
 * USE CASES:
 * - Search inputs, form validation, window resize
 * - Multiple elements need independent timers
 * - When you need to call with different arguments
 *
 * EXAMPLES:
 * const debouncedSearch = debounce( (query) => fetchResults(query), 300 );
 * searchInput.addEventListener('input', (e) => debouncedSearch(e.target.value));
 *
 * const debouncedResize = debounce( recalculateLayout, 250 );
 * window.addEventListener('resize', debouncedResize);
 *
 * ⚠️ Create ONCE outside event handlers, not inside.
 *
 * @param {Function} func - Function to debounce (can be anonymous)
 * @param {number}   wait - Milliseconds to wait (default: 500)
 * @return {Function} Debounced wrapper function
 * @since 4.3.7
 * @version 1.0.0
 */
const debounce = (func, wait = 500) => {
  let timer;
  return args => {
    clearTimeout(timer);
    timer = setTimeout(() => func(args), wait);
  };
};

/***/ },

/***/ "./node_modules/css-loader/dist/cjs.js!./node_modules/toastify-js/src/toastify.css"
/*!*****************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./node_modules/toastify-js/src/toastify.css ***!
  \*****************************************************************************************/
(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../css-loader/dist/runtime/sourceMaps.js */ "./node_modules/css-loader/dist/runtime/sourceMaps.js");
/* harmony import */ var _css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__);
// Imports


var ___CSS_LOADER_EXPORT___ = _css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default()((_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default()));
// Module
___CSS_LOADER_EXPORT___.push([module.id, `/*!
 * Toastify js 1.12.0
 * https://github.com/apvarun/toastify-js
 * @license MIT licensed
 *
 * Copyright (C) 2018 Varun A P
 */

.toastify {
    padding: 12px 20px;
    color: #ffffff;
    display: inline-block;
    box-shadow: 0 3px 6px -1px rgba(0, 0, 0, 0.12), 0 10px 36px -4px rgba(77, 96, 232, 0.3);
    background: -webkit-linear-gradient(315deg, #73a5ff, #5477f5);
    background: linear-gradient(135deg, #73a5ff, #5477f5);
    position: fixed;
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
    border-radius: 2px;
    cursor: pointer;
    text-decoration: none;
    max-width: calc(50% - 20px);
    z-index: 2147483647;
}

.toastify.on {
    opacity: 1;
}

.toast-close {
    background: transparent;
    border: 0;
    color: white;
    cursor: pointer;
    font-family: inherit;
    font-size: 1em;
    opacity: 0.4;
    padding: 0 5px;
}

.toastify-right {
    right: 15px;
}

.toastify-left {
    left: 15px;
}

.toastify-top {
    top: -150px;
}

.toastify-bottom {
    bottom: -150px;
}

.toastify-rounded {
    border-radius: 25px;
}

.toastify-avatar {
    width: 1.5em;
    height: 1.5em;
    margin: -7px 5px;
    border-radius: 2px;
}

.toastify-center {
    margin-left: auto;
    margin-right: auto;
    left: 0;
    right: 0;
    max-width: fit-content;
    max-width: -moz-fit-content;
}

@media only screen and (max-width: 360px) {
    .toastify-right, .toastify-left {
        margin-left: auto;
        margin-right: auto;
        left: 0;
        right: 0;
        max-width: fit-content;
    }
}
`, "",{"version":3,"sources":["webpack://./node_modules/toastify-js/src/toastify.css"],"names":[],"mappings":"AAAA;;;;;;EAME;;AAEF;IACI,kBAAkB;IAClB,cAAc;IACd,qBAAqB;IACrB,uFAAuF;IACvF,6DAA6D;IAC7D,qDAAqD;IACrD,eAAe;IACf,UAAU;IACV,wDAAwD;IACxD,kBAAkB;IAClB,eAAe;IACf,qBAAqB;IACrB,2BAA2B;IAC3B,mBAAmB;AACvB;;AAEA;IACI,UAAU;AACd;;AAEA;IACI,uBAAuB;IACvB,SAAS;IACT,YAAY;IACZ,eAAe;IACf,oBAAoB;IACpB,cAAc;IACd,YAAY;IACZ,cAAc;AAClB;;AAEA;IACI,WAAW;AACf;;AAEA;IACI,UAAU;AACd;;AAEA;IACI,WAAW;AACf;;AAEA;IACI,cAAc;AAClB;;AAEA;IACI,mBAAmB;AACvB;;AAEA;IACI,YAAY;IACZ,aAAa;IACb,gBAAgB;IAChB,kBAAkB;AACtB;;AAEA;IACI,iBAAiB;IACjB,kBAAkB;IAClB,OAAO;IACP,QAAQ;IACR,sBAAsB;IACtB,2BAA2B;AAC/B;;AAEA;IACI;QACI,iBAAiB;QACjB,kBAAkB;QAClB,OAAO;QACP,QAAQ;QACR,sBAAsB;IAC1B;AACJ","sourcesContent":["/*!\n * Toastify js 1.12.0\n * https://github.com/apvarun/toastify-js\n * @license MIT licensed\n *\n * Copyright (C) 2018 Varun A P\n */\n\n.toastify {\n    padding: 12px 20px;\n    color: #ffffff;\n    display: inline-block;\n    box-shadow: 0 3px 6px -1px rgba(0, 0, 0, 0.12), 0 10px 36px -4px rgba(77, 96, 232, 0.3);\n    background: -webkit-linear-gradient(315deg, #73a5ff, #5477f5);\n    background: linear-gradient(135deg, #73a5ff, #5477f5);\n    position: fixed;\n    opacity: 0;\n    transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);\n    border-radius: 2px;\n    cursor: pointer;\n    text-decoration: none;\n    max-width: calc(50% - 20px);\n    z-index: 2147483647;\n}\n\n.toastify.on {\n    opacity: 1;\n}\n\n.toast-close {\n    background: transparent;\n    border: 0;\n    color: white;\n    cursor: pointer;\n    font-family: inherit;\n    font-size: 1em;\n    opacity: 0.4;\n    padding: 0 5px;\n}\n\n.toastify-right {\n    right: 15px;\n}\n\n.toastify-left {\n    left: 15px;\n}\n\n.toastify-top {\n    top: -150px;\n}\n\n.toastify-bottom {\n    bottom: -150px;\n}\n\n.toastify-rounded {\n    border-radius: 25px;\n}\n\n.toastify-avatar {\n    width: 1.5em;\n    height: 1.5em;\n    margin: -7px 5px;\n    border-radius: 2px;\n}\n\n.toastify-center {\n    margin-left: auto;\n    margin-right: auto;\n    left: 0;\n    right: 0;\n    max-width: fit-content;\n    max-width: -moz-fit-content;\n}\n\n@media only screen and (max-width: 360px) {\n    .toastify-right, .toastify-left {\n        margin-left: auto;\n        margin-right: auto;\n        left: 0;\n        right: 0;\n        max-width: fit-content;\n    }\n}\n"],"sourceRoot":""}]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ },

/***/ "./node_modules/css-loader/dist/runtime/api.js"
/*!*****************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/api.js ***!
  \*****************************************************/
(module) {

"use strict";


/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
*/
module.exports = function (cssWithMappingToString) {
  var list = [];

  // return the list of modules as css string
  list.toString = function toString() {
    return this.map(function (item) {
      var content = "";
      var needLayer = typeof item[5] !== "undefined";
      if (item[4]) {
        content += "@supports (".concat(item[4], ") {");
      }
      if (item[2]) {
        content += "@media ".concat(item[2], " {");
      }
      if (needLayer) {
        content += "@layer".concat(item[5].length > 0 ? " ".concat(item[5]) : "", " {");
      }
      content += cssWithMappingToString(item);
      if (needLayer) {
        content += "}";
      }
      if (item[2]) {
        content += "}";
      }
      if (item[4]) {
        content += "}";
      }
      return content;
    }).join("");
  };

  // import a list of modules into the list
  list.i = function i(modules, media, dedupe, supports, layer) {
    if (typeof modules === "string") {
      modules = [[null, modules, undefined]];
    }
    var alreadyImportedModules = {};
    if (dedupe) {
      for (var k = 0; k < this.length; k++) {
        var id = this[k][0];
        if (id != null) {
          alreadyImportedModules[id] = true;
        }
      }
    }
    for (var _k = 0; _k < modules.length; _k++) {
      var item = [].concat(modules[_k]);
      if (dedupe && alreadyImportedModules[item[0]]) {
        continue;
      }
      if (typeof layer !== "undefined") {
        if (typeof item[5] === "undefined") {
          item[5] = layer;
        } else {
          item[1] = "@layer".concat(item[5].length > 0 ? " ".concat(item[5]) : "", " {").concat(item[1], "}");
          item[5] = layer;
        }
      }
      if (media) {
        if (!item[2]) {
          item[2] = media;
        } else {
          item[1] = "@media ".concat(item[2], " {").concat(item[1], "}");
          item[2] = media;
        }
      }
      if (supports) {
        if (!item[4]) {
          item[4] = "".concat(supports);
        } else {
          item[1] = "@supports (".concat(item[4], ") {").concat(item[1], "}");
          item[4] = supports;
        }
      }
      list.push(item);
    }
  };
  return list;
};

/***/ },

/***/ "./node_modules/css-loader/dist/runtime/sourceMaps.js"
/*!************************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/sourceMaps.js ***!
  \************************************************************/
(module) {

"use strict";


module.exports = function (item) {
  var content = item[1];
  var cssMapping = item[3];
  if (!cssMapping) {
    return content;
  }
  if (typeof btoa === "function") {
    var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(cssMapping))));
    var data = "sourceMappingURL=data:application/json;charset=utf-8;base64,".concat(base64);
    var sourceMapping = "/*# ".concat(data, " */");
    return [content].concat([sourceMapping]).join("\n");
  }
  return [content].join("\n");
};

/***/ },

/***/ "./node_modules/toastify-js/src/toastify.css"
/*!***************************************************!*\
  !*** ./node_modules/toastify-js/src/toastify.css ***!
  \***************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !../../style-loader/dist/runtime/styleDomAPI.js */ "./node_modules/style-loader/dist/runtime/styleDomAPI.js");
/* harmony import */ var _style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../style-loader/dist/runtime/insertBySelector.js */ "./node_modules/style-loader/dist/runtime/insertBySelector.js");
/* harmony import */ var _style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! !../../style-loader/dist/runtime/setAttributesWithoutAttributes.js */ "./node_modules/style-loader/dist/runtime/setAttributesWithoutAttributes.js");
/* harmony import */ var _style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! !../../style-loader/dist/runtime/insertStyleElement.js */ "./node_modules/style-loader/dist/runtime/insertStyleElement.js");
/* harmony import */ var _style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! !../../style-loader/dist/runtime/styleTagTransform.js */ "./node_modules/style-loader/dist/runtime/styleTagTransform.js");
/* harmony import */ var _style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _css_loader_dist_cjs_js_toastify_css__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! !!../../css-loader/dist/cjs.js!./toastify.css */ "./node_modules/css-loader/dist/cjs.js!./node_modules/toastify-js/src/toastify.css");

      
      
      
      
      
      
      
      
      

var options = {};

options.styleTagTransform = (_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default());
options.setAttributes = (_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default());

      options.insert = _style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default().bind(null, "head");
    
options.domAPI = (_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default());
options.insertStyleElement = (_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default());

var update = _style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_css_loader_dist_cjs_js_toastify_css__WEBPACK_IMPORTED_MODULE_6__["default"], options);




       /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_css_loader_dist_cjs_js_toastify_css__WEBPACK_IMPORTED_MODULE_6__["default"] && _css_loader_dist_cjs_js_toastify_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals ? _css_loader_dist_cjs_js_toastify_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals : undefined);


/***/ },

/***/ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js"
/*!****************************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js ***!
  \****************************************************************************/
(module) {

"use strict";


var stylesInDOM = [];
function getIndexByIdentifier(identifier) {
  var result = -1;
  for (var i = 0; i < stylesInDOM.length; i++) {
    if (stylesInDOM[i].identifier === identifier) {
      result = i;
      break;
    }
  }
  return result;
}
function modulesToDom(list, options) {
  var idCountMap = {};
  var identifiers = [];
  for (var i = 0; i < list.length; i++) {
    var item = list[i];
    var id = options.base ? item[0] + options.base : item[0];
    var count = idCountMap[id] || 0;
    var identifier = "".concat(id, " ").concat(count);
    idCountMap[id] = count + 1;
    var indexByIdentifier = getIndexByIdentifier(identifier);
    var obj = {
      css: item[1],
      media: item[2],
      sourceMap: item[3],
      supports: item[4],
      layer: item[5]
    };
    if (indexByIdentifier !== -1) {
      stylesInDOM[indexByIdentifier].references++;
      stylesInDOM[indexByIdentifier].updater(obj);
    } else {
      var updater = addElementStyle(obj, options);
      options.byIndex = i;
      stylesInDOM.splice(i, 0, {
        identifier: identifier,
        updater: updater,
        references: 1
      });
    }
    identifiers.push(identifier);
  }
  return identifiers;
}
function addElementStyle(obj, options) {
  var api = options.domAPI(options);
  api.update(obj);
  var updater = function updater(newObj) {
    if (newObj) {
      if (newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap && newObj.supports === obj.supports && newObj.layer === obj.layer) {
        return;
      }
      api.update(obj = newObj);
    } else {
      api.remove();
    }
  };
  return updater;
}
module.exports = function (list, options) {
  options = options || {};
  list = list || [];
  var lastIdentifiers = modulesToDom(list, options);
  return function update(newList) {
    newList = newList || [];
    for (var i = 0; i < lastIdentifiers.length; i++) {
      var identifier = lastIdentifiers[i];
      var index = getIndexByIdentifier(identifier);
      stylesInDOM[index].references--;
    }
    var newLastIdentifiers = modulesToDom(newList, options);
    for (var _i = 0; _i < lastIdentifiers.length; _i++) {
      var _identifier = lastIdentifiers[_i];
      var _index = getIndexByIdentifier(_identifier);
      if (stylesInDOM[_index].references === 0) {
        stylesInDOM[_index].updater();
        stylesInDOM.splice(_index, 1);
      }
    }
    lastIdentifiers = newLastIdentifiers;
  };
};

/***/ },

/***/ "./node_modules/style-loader/dist/runtime/insertBySelector.js"
/*!********************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/insertBySelector.js ***!
  \********************************************************************/
(module) {

"use strict";


var memo = {};

/* istanbul ignore next  */
function getTarget(target) {
  if (typeof memo[target] === "undefined") {
    var styleTarget = document.querySelector(target);

    // Special case to return head of iframe instead of iframe itself
    if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
      try {
        // This will throw an exception if access to iframe is blocked
        // due to cross-origin restrictions
        styleTarget = styleTarget.contentDocument.head;
      } catch (e) {
        // istanbul ignore next
        styleTarget = null;
      }
    }
    memo[target] = styleTarget;
  }
  return memo[target];
}

/* istanbul ignore next  */
function insertBySelector(insert, style) {
  var target = getTarget(insert);
  if (!target) {
    throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
  }
  target.appendChild(style);
}
module.exports = insertBySelector;

/***/ },

/***/ "./node_modules/style-loader/dist/runtime/insertStyleElement.js"
/*!**********************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/insertStyleElement.js ***!
  \**********************************************************************/
(module) {

"use strict";


/* istanbul ignore next  */
function insertStyleElement(options) {
  var element = document.createElement("style");
  options.setAttributes(element, options.attributes);
  options.insert(element, options.options);
  return element;
}
module.exports = insertStyleElement;

/***/ },

/***/ "./node_modules/style-loader/dist/runtime/setAttributesWithoutAttributes.js"
/*!**********************************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/setAttributesWithoutAttributes.js ***!
  \**********************************************************************************/
(module, __unused_webpack_exports, __webpack_require__) {

"use strict";


/* istanbul ignore next  */
function setAttributesWithoutAttributes(styleElement) {
  var nonce =  true ? __webpack_require__.nc : 0;
  if (nonce) {
    styleElement.setAttribute("nonce", nonce);
  }
}
module.exports = setAttributesWithoutAttributes;

/***/ },

/***/ "./node_modules/style-loader/dist/runtime/styleDomAPI.js"
/*!***************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/styleDomAPI.js ***!
  \***************************************************************/
(module) {

"use strict";


/* istanbul ignore next  */
function apply(styleElement, options, obj) {
  var css = "";
  if (obj.supports) {
    css += "@supports (".concat(obj.supports, ") {");
  }
  if (obj.media) {
    css += "@media ".concat(obj.media, " {");
  }
  var needLayer = typeof obj.layer !== "undefined";
  if (needLayer) {
    css += "@layer".concat(obj.layer.length > 0 ? " ".concat(obj.layer) : "", " {");
  }
  css += obj.css;
  if (needLayer) {
    css += "}";
  }
  if (obj.media) {
    css += "}";
  }
  if (obj.supports) {
    css += "}";
  }
  var sourceMap = obj.sourceMap;
  if (sourceMap && typeof btoa !== "undefined") {
    css += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))), " */");
  }

  // For old IE
  /* istanbul ignore if  */
  options.styleTagTransform(css, styleElement, options.options);
}
function removeStyleElement(styleElement) {
  // istanbul ignore if
  if (styleElement.parentNode === null) {
    return false;
  }
  styleElement.parentNode.removeChild(styleElement);
}

/* istanbul ignore next  */
function domAPI(options) {
  if (typeof document === "undefined") {
    return {
      update: function update() {},
      remove: function remove() {}
    };
  }
  var styleElement = options.insertStyleElement(options);
  return {
    update: function update(obj) {
      apply(styleElement, options, obj);
    },
    remove: function remove() {
      removeStyleElement(styleElement);
    }
  };
}
module.exports = domAPI;

/***/ },

/***/ "./node_modules/style-loader/dist/runtime/styleTagTransform.js"
/*!*********************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/styleTagTransform.js ***!
  \*********************************************************************/
(module) {

"use strict";


/* istanbul ignore next  */
function styleTagTransform(css, styleElement) {
  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = css;
  } else {
    while (styleElement.firstChild) {
      styleElement.removeChild(styleElement.firstChild);
    }
    styleElement.appendChild(document.createTextNode(css));
  }
}
module.exports = styleTagTransform;

/***/ },

/***/ "./node_modules/sweetalert2/dist/sweetalert2.all.js"
/*!**********************************************************!*\
  !*** ./node_modules/sweetalert2/dist/sweetalert2.all.js ***!
  \**********************************************************/
(module) {

/*!
* sweetalert2 v11.26.17
* Released under the MIT License.
*/
(function (global, factory) {
   true ? module.exports = factory() :
  0;
})(this, (function () { 'use strict';

  function _assertClassBrand(e, t, n) {
    if ("function" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n;
    throw new TypeError("Private element is not present on this object");
  }
  function _checkPrivateRedeclaration(e, t) {
    if (t.has(e)) throw new TypeError("Cannot initialize the same private elements twice on an object");
  }
  function _classPrivateFieldGet2(s, a) {
    return s.get(_assertClassBrand(s, a));
  }
  function _classPrivateFieldInitSpec(e, t, a) {
    _checkPrivateRedeclaration(e, t), t.set(e, a);
  }
  function _classPrivateFieldSet2(s, a, r) {
    return s.set(_assertClassBrand(s, a), r), r;
  }

  const RESTORE_FOCUS_TIMEOUT = 100;

  /** @type {GlobalState} */
  const globalState = {};
  const focusPreviousActiveElement = () => {
    if (globalState.previousActiveElement instanceof HTMLElement) {
      globalState.previousActiveElement.focus();
      globalState.previousActiveElement = null;
    } else if (document.body) {
      document.body.focus();
    }
  };

  /**
   * Restore previous active (focused) element
   *
   * @param {boolean} returnFocus
   * @returns {Promise<void>}
   */
  const restoreActiveElement = returnFocus => {
    return new Promise(resolve => {
      if (!returnFocus) {
        return resolve();
      }
      const x = window.scrollX;
      const y = window.scrollY;
      globalState.restoreFocusTimeout = setTimeout(() => {
        focusPreviousActiveElement();
        resolve();
      }, RESTORE_FOCUS_TIMEOUT); // issues/900

      window.scrollTo(x, y);
    });
  };

  const swalPrefix = 'swal2-';

  /**
   * @typedef {Record<SwalClass, string>} SwalClasses
   */

  /**
   * @typedef {'success' | 'warning' | 'info' | 'question' | 'error'} SwalIcon
   * @typedef {Record<SwalIcon, string>} SwalIcons
   */

  /** @type {SwalClass[]} */
  const classNames = ['container', 'shown', 'height-auto', 'iosfix', 'popup', 'modal', 'no-backdrop', 'no-transition', 'toast', 'toast-shown', 'show', 'hide', 'close', 'title', 'html-container', 'actions', 'confirm', 'deny', 'cancel', 'footer', 'icon', 'icon-content', 'image', 'input', 'file', 'range', 'select', 'radio', 'checkbox', 'label', 'textarea', 'inputerror', 'input-label', 'validation-message', 'progress-steps', 'active-progress-step', 'progress-step', 'progress-step-line', 'loader', 'loading', 'styled', 'top', 'top-start', 'top-end', 'top-left', 'top-right', 'center', 'center-start', 'center-end', 'center-left', 'center-right', 'bottom', 'bottom-start', 'bottom-end', 'bottom-left', 'bottom-right', 'grow-row', 'grow-column', 'grow-fullscreen', 'rtl', 'timer-progress-bar', 'timer-progress-bar-container', 'scrollbar-measure', 'icon-success', 'icon-warning', 'icon-info', 'icon-question', 'icon-error', 'draggable', 'dragging'];
  const swalClasses = classNames.reduce((acc, className) => {
    acc[className] = swalPrefix + className;
    return acc;
  }, /** @type {SwalClasses} */{});

  /** @type {SwalIcon[]} */
  const icons = ['success', 'warning', 'info', 'question', 'error'];
  const iconTypes = icons.reduce((acc, icon) => {
    acc[icon] = swalPrefix + icon;
    return acc;
  }, /** @type {SwalIcons} */{});

  const consolePrefix = 'SweetAlert2:';

  /**
   * Capitalize the first letter of a string
   *
   * @param {string} str
   * @returns {string}
   */
  const capitalizeFirstLetter = str => str.charAt(0).toUpperCase() + str.slice(1);

  /**
   * Standardize console warnings
   *
   * @param {string | string[]} message
   */
  const warn = message => {
    console.warn(`${consolePrefix} ${typeof message === 'object' ? message.join(' ') : message}`);
  };

  /**
   * Standardize console errors
   *
   * @param {string} message
   */
  const error = message => {
    console.error(`${consolePrefix} ${message}`);
  };

  /**
   * Private global state for `warnOnce`
   *
   * @type {string[]}
   * @private
   */
  const previousWarnOnceMessages = [];

  /**
   * Show a console warning, but only if it hasn't already been shown
   *
   * @param {string} message
   */
  const warnOnce = message => {
    if (!previousWarnOnceMessages.includes(message)) {
      previousWarnOnceMessages.push(message);
      warn(message);
    }
  };

  /**
   * Show a one-time console warning about deprecated params/methods
   *
   * @param {string} deprecatedParam
   * @param {string?} useInstead
   */
  const warnAboutDeprecation = (deprecatedParam, useInstead = null) => {
    warnOnce(`"${deprecatedParam}" is deprecated and will be removed in the next major release.${useInstead ? ` Use "${useInstead}" instead.` : ''}`);
  };

  /**
   * If `arg` is a function, call it (with no arguments or context) and return the result.
   * Otherwise, just pass the value through
   *
   * @param {(() => *) | *} arg
   * @returns {*}
   */
  const callIfFunction = arg => typeof arg === 'function' ? arg() : arg;

  /**
   * @param {*} arg
   * @returns {boolean}
   */
  const hasToPromiseFn = arg => arg && typeof arg.toPromise === 'function';

  /**
   * @param {*} arg
   * @returns {Promise<*>}
   */
  const asPromise = arg => hasToPromiseFn(arg) ? arg.toPromise() : Promise.resolve(arg);

  /**
   * @param {*} arg
   * @returns {boolean}
   */
  const isPromise = arg => arg && Promise.resolve(arg) === arg;

  /**
   * Gets the popup container which contains the backdrop and the popup itself.
   *
   * @returns {HTMLElement | null}
   */
  const getContainer = () => document.body.querySelector(`.${swalClasses.container}`);

  /**
   * @param {string} selectorString
   * @returns {HTMLElement | null}
   */
  const elementBySelector = selectorString => {
    const container = getContainer();
    return container ? container.querySelector(selectorString) : null;
  };

  /**
   * @param {string} className
   * @returns {HTMLElement | null}
   */
  const elementByClass = className => {
    return elementBySelector(`.${className}`);
  };

  /**
   * @returns {HTMLElement | null}
   */
  const getPopup = () => elementByClass(swalClasses.popup);

  /**
   * @returns {HTMLElement | null}
   */
  const getIcon = () => elementByClass(swalClasses.icon);

  /**
   * @returns {HTMLElement | null}
   */
  const getIconContent = () => elementByClass(swalClasses['icon-content']);

  /**
   * @returns {HTMLElement | null}
   */
  const getTitle = () => elementByClass(swalClasses.title);

  /**
   * @returns {HTMLElement | null}
   */
  const getHtmlContainer = () => elementByClass(swalClasses['html-container']);

  /**
   * @returns {HTMLElement | null}
   */
  const getImage = () => elementByClass(swalClasses.image);

  /**
   * @returns {HTMLElement | null}
   */
  const getProgressSteps = () => elementByClass(swalClasses['progress-steps']);

  /**
   * @returns {HTMLElement | null}
   */
  const getValidationMessage = () => elementByClass(swalClasses['validation-message']);

  /**
   * @returns {HTMLButtonElement | null}
   */
  const getConfirmButton = () => (/** @type {HTMLButtonElement} */elementBySelector(`.${swalClasses.actions} .${swalClasses.confirm}`));

  /**
   * @returns {HTMLButtonElement | null}
   */
  const getCancelButton = () => (/** @type {HTMLButtonElement} */elementBySelector(`.${swalClasses.actions} .${swalClasses.cancel}`));

  /**
   * @returns {HTMLButtonElement | null}
   */
  const getDenyButton = () => (/** @type {HTMLButtonElement} */elementBySelector(`.${swalClasses.actions} .${swalClasses.deny}`));

  /**
   * @returns {HTMLElement | null}
   */
  const getInputLabel = () => elementByClass(swalClasses['input-label']);

  /**
   * @returns {HTMLElement | null}
   */
  const getLoader = () => elementBySelector(`.${swalClasses.loader}`);

  /**
   * @returns {HTMLElement | null}
   */
  const getActions = () => elementByClass(swalClasses.actions);

  /**
   * @returns {HTMLElement | null}
   */
  const getFooter = () => elementByClass(swalClasses.footer);

  /**
   * @returns {HTMLElement | null}
   */
  const getTimerProgressBar = () => elementByClass(swalClasses['timer-progress-bar']);

  /**
   * @returns {HTMLElement | null}
   */
  const getCloseButton = () => elementByClass(swalClasses.close);

  // https://github.com/jkup/focusable/blob/master/index.js
  const focusable = `
  a[href],
  area[href],
  input:not([disabled]),
  select:not([disabled]),
  textarea:not([disabled]),
  button:not([disabled]),
  iframe,
  object,
  embed,
  [tabindex="0"],
  [contenteditable],
  audio[controls],
  video[controls],
  summary
`;
  /**
   * @returns {HTMLElement[]}
   */
  const getFocusableElements = () => {
    const popup = getPopup();
    if (!popup) {
      return [];
    }
    /** @type {NodeListOf<HTMLElement>} */
    const focusableElementsWithTabindex = popup.querySelectorAll('[tabindex]:not([tabindex="-1"]):not([tabindex="0"])');
    const focusableElementsWithTabindexSorted = Array.from(focusableElementsWithTabindex)
    // sort according to tabindex
    .sort((a, b) => {
      const tabindexA = parseInt(a.getAttribute('tabindex') || '0');
      const tabindexB = parseInt(b.getAttribute('tabindex') || '0');
      if (tabindexA > tabindexB) {
        return 1;
      } else if (tabindexA < tabindexB) {
        return -1;
      }
      return 0;
    });

    /** @type {NodeListOf<HTMLElement>} */
    const otherFocusableElements = popup.querySelectorAll(focusable);
    const otherFocusableElementsFiltered = Array.from(otherFocusableElements).filter(el => el.getAttribute('tabindex') !== '-1');
    return [...new Set(focusableElementsWithTabindexSorted.concat(otherFocusableElementsFiltered))].filter(el => isVisible$1(el));
  };

  /**
   * @returns {boolean}
   */
  const isModal = () => {
    return hasClass(document.body, swalClasses.shown) && !hasClass(document.body, swalClasses['toast-shown']) && !hasClass(document.body, swalClasses['no-backdrop']);
  };

  /**
   * @returns {boolean}
   */
  const isToast = () => {
    const popup = getPopup();
    if (!popup) {
      return false;
    }
    return hasClass(popup, swalClasses.toast);
  };

  /**
   * @returns {boolean}
   */
  const isLoading = () => {
    const popup = getPopup();
    if (!popup) {
      return false;
    }
    return popup.hasAttribute('data-loading');
  };

  /**
   * Securely set innerHTML of an element
   * https://github.com/sweetalert2/sweetalert2/issues/1926
   *
   * @param {HTMLElement} elem
   * @param {string} html
   */
  const setInnerHtml = (elem, html) => {
    elem.textContent = '';
    if (html) {
      const parser = new DOMParser();
      const parsed = parser.parseFromString(html, `text/html`);
      const head = parsed.querySelector('head');
      if (head) {
        Array.from(head.childNodes).forEach(child => {
          elem.appendChild(child);
        });
      }
      const body = parsed.querySelector('body');
      if (body) {
        Array.from(body.childNodes).forEach(child => {
          if (child instanceof HTMLVideoElement || child instanceof HTMLAudioElement) {
            elem.appendChild(child.cloneNode(true)); // https://github.com/sweetalert2/sweetalert2/issues/2507
          } else {
            elem.appendChild(child);
          }
        });
      }
    }
  };

  /**
   * @param {HTMLElement} elem
   * @param {string} className
   * @returns {boolean}
   */
  const hasClass = (elem, className) => {
    if (!className) {
      return false;
    }
    const classList = className.split(/\s+/);
    for (let i = 0; i < classList.length; i++) {
      if (!elem.classList.contains(classList[i])) {
        return false;
      }
    }
    return true;
  };

  /**
   * @param {HTMLElement} elem
   * @param {SweetAlertOptions} params
   */
  const removeCustomClasses = (elem, params) => {
    Array.from(elem.classList).forEach(className => {
      if (!Object.values(swalClasses).includes(className) && !Object.values(iconTypes).includes(className) && !Object.values(params.showClass || {}).includes(className)) {
        elem.classList.remove(className);
      }
    });
  };

  /**
   * @param {HTMLElement} elem
   * @param {SweetAlertOptions} params
   * @param {string} className
   */
  const applyCustomClass = (elem, params, className) => {
    removeCustomClasses(elem, params);
    if (!params.customClass) {
      return;
    }
    const customClass = params.customClass[(/** @type {keyof SweetAlertCustomClass} */className)];
    if (!customClass) {
      return;
    }
    if (typeof customClass !== 'string' && !customClass.forEach) {
      warn(`Invalid type of customClass.${className}! Expected string or iterable object, got "${typeof customClass}"`);
      return;
    }
    addClass(elem, customClass);
  };

  /**
   * @param {HTMLElement} popup
   * @param {import('./renderers/renderInput').InputClass | SweetAlertInput} inputClass
   * @returns {HTMLInputElement | null}
   */
  const getInput$1 = (popup, inputClass) => {
    if (!inputClass) {
      return null;
    }
    switch (inputClass) {
      case 'select':
      case 'textarea':
      case 'file':
        return popup.querySelector(`.${swalClasses.popup} > .${swalClasses[inputClass]}`);
      case 'checkbox':
        return popup.querySelector(`.${swalClasses.popup} > .${swalClasses.checkbox} input`);
      case 'radio':
        return popup.querySelector(`.${swalClasses.popup} > .${swalClasses.radio} input:checked`) || popup.querySelector(`.${swalClasses.popup} > .${swalClasses.radio} input:first-child`);
      case 'range':
        return popup.querySelector(`.${swalClasses.popup} > .${swalClasses.range} input`);
      default:
        return popup.querySelector(`.${swalClasses.popup} > .${swalClasses.input}`);
    }
  };

  /**
   * @param {HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement} input
   */
  const focusInput = input => {
    input.focus();

    // place cursor at end of text in text input
    if (input.type !== 'file') {
      // http://stackoverflow.com/a/2345915
      const val = input.value;
      input.value = '';
      input.value = val;
    }
  };

  /**
   * @param {HTMLElement | HTMLElement[] | null} target
   * @param {string | string[] | readonly string[] | undefined} classList
   * @param {boolean} condition
   */
  const toggleClass = (target, classList, condition) => {
    if (!target || !classList) {
      return;
    }
    if (typeof classList === 'string') {
      classList = classList.split(/\s+/).filter(Boolean);
    }
    classList.forEach(className => {
      if (Array.isArray(target)) {
        target.forEach(elem => {
          if (condition) {
            elem.classList.add(className);
          } else {
            elem.classList.remove(className);
          }
        });
      } else {
        if (condition) {
          target.classList.add(className);
        } else {
          target.classList.remove(className);
        }
      }
    });
  };

  /**
   * @param {HTMLElement | HTMLElement[] | null} target
   * @param {string | string[] | readonly string[] | undefined} classList
   */
  const addClass = (target, classList) => {
    toggleClass(target, classList, true);
  };

  /**
   * @param {HTMLElement | HTMLElement[] | null} target
   * @param {string | string[] | readonly string[] | undefined} classList
   */
  const removeClass = (target, classList) => {
    toggleClass(target, classList, false);
  };

  /**
   * Get direct child of an element by class name
   *
   * @param {HTMLElement} elem
   * @param {string} className
   * @returns {HTMLElement | undefined}
   */
  const getDirectChildByClass = (elem, className) => {
    const children = Array.from(elem.children);
    for (let i = 0; i < children.length; i++) {
      const child = children[i];
      if (child instanceof HTMLElement && hasClass(child, className)) {
        return child;
      }
    }
  };

  /**
   * @param {HTMLElement} elem
   * @param {string} property
   * @param {string | number | null | undefined} value
   */
  const applyNumericalStyle = (elem, property, value) => {
    if (value === `${parseInt(`${value}`)}`) {
      value = parseInt(value);
    }
    if (value || parseInt(`${value}`) === 0) {
      elem.style.setProperty(property, typeof value === 'number' ? `${value}px` : (/** @type {string} */value));
    } else {
      elem.style.removeProperty(property);
    }
  };

  /**
   * @param {HTMLElement | null} elem
   * @param {string} display
   */
  const show = (elem, display = 'flex') => {
    if (!elem) {
      return;
    }
    elem.style.display = display;
  };

  /**
   * @param {HTMLElement | null} elem
   */
  const hide = elem => {
    if (!elem) {
      return;
    }
    elem.style.display = 'none';
  };

  /**
   * @param {HTMLElement | null} elem
   * @param {string} display
   */
  const showWhenInnerHtmlPresent = (elem, display = 'block') => {
    if (!elem) {
      return;
    }
    new MutationObserver(() => {
      toggle(elem, elem.innerHTML, display);
    }).observe(elem, {
      childList: true,
      subtree: true
    });
  };

  /**
   * @param {HTMLElement} parent
   * @param {string} selector
   * @param {string} property
   * @param {string} value
   */
  const setStyle = (parent, selector, property, value) => {
    /** @type {HTMLElement | null} */
    const el = parent.querySelector(selector);
    if (el) {
      el.style.setProperty(property, value);
    }
  };

  /**
   * @param {HTMLElement} elem
   * @param {boolean | string | null | undefined} condition
   * @param {string} display
   */
  const toggle = (elem, condition, display = 'flex') => {
    if (condition) {
      show(elem, display);
    } else {
      hide(elem);
    }
  };

  /**
   * borrowed from jquery $(elem).is(':visible') implementation
   *
   * @param {HTMLElement | null} elem
   * @returns {boolean}
   */
  const isVisible$1 = elem => Boolean(elem && (elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length));

  /**
   * @returns {boolean}
   */
  const allButtonsAreHidden = () => !isVisible$1(getConfirmButton()) && !isVisible$1(getDenyButton()) && !isVisible$1(getCancelButton());

  /**
   * @param {HTMLElement} elem
   * @returns {boolean}
   */
  const isScrollable = elem => Boolean(elem.scrollHeight > elem.clientHeight);

  /**
   * @param {HTMLElement} element
   * @param {HTMLElement} stopElement
   * @returns {boolean}
   */
  const selfOrParentIsScrollable = (element, stopElement) => {
    let parent = /** @type {HTMLElement | null} */element;
    while (parent && parent !== stopElement) {
      if (isScrollable(parent)) {
        return true;
      }
      parent = parent.parentElement;
    }
    return false;
  };

  /**
   * borrowed from https://stackoverflow.com/a/46352119
   *
   * @param {HTMLElement} elem
   * @returns {boolean}
   */
  const hasCssAnimation = elem => {
    const style = window.getComputedStyle(elem);
    const animDuration = parseFloat(style.getPropertyValue('animation-duration') || '0');
    const transDuration = parseFloat(style.getPropertyValue('transition-duration') || '0');
    return animDuration > 0 || transDuration > 0;
  };

  /**
   * @param {number} timer
   * @param {boolean} reset
   */
  const animateTimerProgressBar = (timer, reset = false) => {
    const timerProgressBar = getTimerProgressBar();
    if (!timerProgressBar) {
      return;
    }
    if (isVisible$1(timerProgressBar)) {
      if (reset) {
        timerProgressBar.style.transition = 'none';
        timerProgressBar.style.width = '100%';
      }
      setTimeout(() => {
        timerProgressBar.style.transition = `width ${timer / 1000}s linear`;
        timerProgressBar.style.width = '0%';
      }, 10);
    }
  };
  const stopTimerProgressBar = () => {
    const timerProgressBar = getTimerProgressBar();
    if (!timerProgressBar) {
      return;
    }
    const timerProgressBarWidth = parseInt(window.getComputedStyle(timerProgressBar).width);
    timerProgressBar.style.removeProperty('transition');
    timerProgressBar.style.width = '100%';
    const timerProgressBarFullWidth = parseInt(window.getComputedStyle(timerProgressBar).width);
    const timerProgressBarPercent = timerProgressBarWidth / timerProgressBarFullWidth * 100;
    timerProgressBar.style.width = `${timerProgressBarPercent}%`;
  };

  /**
   * Detect Node env
   *
   * @returns {boolean}
   */
  const isNodeEnv = () => typeof window === 'undefined' || typeof document === 'undefined';

  const sweetHTML = `
 <div aria-labelledby="${swalClasses.title}" aria-describedby="${swalClasses['html-container']}" class="${swalClasses.popup}" tabindex="-1">
   <button type="button" class="${swalClasses.close}"></button>
   <ul class="${swalClasses['progress-steps']}"></ul>
   <div class="${swalClasses.icon}"></div>
   <img class="${swalClasses.image}" />
   <h2 class="${swalClasses.title}" id="${swalClasses.title}"></h2>
   <div class="${swalClasses['html-container']}" id="${swalClasses['html-container']}"></div>
   <input class="${swalClasses.input}" id="${swalClasses.input}" />
   <input type="file" class="${swalClasses.file}" />
   <div class="${swalClasses.range}">
     <input type="range" />
     <output></output>
   </div>
   <select class="${swalClasses.select}" id="${swalClasses.select}"></select>
   <div class="${swalClasses.radio}"></div>
   <label class="${swalClasses.checkbox}">
     <input type="checkbox" id="${swalClasses.checkbox}" />
     <span class="${swalClasses.label}"></span>
   </label>
   <textarea class="${swalClasses.textarea}" id="${swalClasses.textarea}"></textarea>
   <div class="${swalClasses['validation-message']}" id="${swalClasses['validation-message']}"></div>
   <div class="${swalClasses.actions}">
     <div class="${swalClasses.loader}"></div>
     <button type="button" class="${swalClasses.confirm}"></button>
     <button type="button" class="${swalClasses.deny}"></button>
     <button type="button" class="${swalClasses.cancel}"></button>
   </div>
   <div class="${swalClasses.footer}"></div>
   <div class="${swalClasses['timer-progress-bar-container']}">
     <div class="${swalClasses['timer-progress-bar']}"></div>
   </div>
 </div>
`.replace(/(^|\n)\s*/g, '');

  /**
   * @returns {boolean}
   */
  const resetOldContainer = () => {
    const oldContainer = getContainer();
    if (!oldContainer) {
      return false;
    }
    oldContainer.remove();
    removeClass([document.documentElement, document.body], [swalClasses['no-backdrop'], swalClasses['toast-shown'],
    // @ts-ignore: 'has-column' is not defined in swalClasses but may be set dynamically
    swalClasses['has-column']]);
    return true;
  };
  const resetValidationMessage$1 = () => {
    if (globalState.currentInstance) {
      globalState.currentInstance.resetValidationMessage();
    }
  };
  const addInputChangeListeners = () => {
    const popup = getPopup();
    if (!popup) {
      return;
    }
    const input = getDirectChildByClass(popup, swalClasses.input);
    const file = getDirectChildByClass(popup, swalClasses.file);
    /** @type {HTMLInputElement | null} */
    const range = popup.querySelector(`.${swalClasses.range} input`);
    /** @type {HTMLOutputElement | null} */
    const rangeOutput = popup.querySelector(`.${swalClasses.range} output`);
    const select = getDirectChildByClass(popup, swalClasses.select);
    /** @type {HTMLInputElement | null} */
    const checkbox = popup.querySelector(`.${swalClasses.checkbox} input`);
    const textarea = getDirectChildByClass(popup, swalClasses.textarea);
    if (input) {
      input.oninput = resetValidationMessage$1;
    }
    if (file) {
      file.onchange = resetValidationMessage$1;
    }
    if (select) {
      select.onchange = resetValidationMessage$1;
    }
    if (checkbox) {
      checkbox.onchange = resetValidationMessage$1;
    }
    if (textarea) {
      textarea.oninput = resetValidationMessage$1;
    }
    if (range && rangeOutput) {
      range.oninput = () => {
        resetValidationMessage$1();
        rangeOutput.value = range.value;
      };
      range.onchange = () => {
        resetValidationMessage$1();
        rangeOutput.value = range.value;
      };
    }
  };

  /**
   * @param {string | HTMLElement} target
   * @returns {HTMLElement}
   */
  const getTarget = target => {
    if (typeof target === 'string') {
      const element = document.querySelector(target);
      if (!element) {
        throw new Error(`Target element "${target}" not found`);
      }
      return /** @type {HTMLElement} */element;
    }
    return target;
  };

  /**
   * @param {SweetAlertOptions} params
   */
  const setupAccessibility = params => {
    const popup = getPopup();
    if (!popup) {
      return;
    }
    popup.setAttribute('role', params.toast ? 'alert' : 'dialog');
    popup.setAttribute('aria-live', params.toast ? 'polite' : 'assertive');
    if (!params.toast) {
      popup.setAttribute('aria-modal', 'true');
    }
  };

  /**
   * @param {HTMLElement} targetElement
   */
  const setupRTL = targetElement => {
    if (window.getComputedStyle(targetElement).direction === 'rtl') {
      addClass(getContainer(), swalClasses.rtl);
      globalState.isRTL = true;
    }
  };

  /**
   * Add modal + backdrop to DOM
   *
   * @param {SweetAlertOptions} params
   */
  const init = params => {
    // Clean up the old popup container if it exists
    const oldContainerExisted = resetOldContainer();
    if (isNodeEnv()) {
      error('SweetAlert2 requires document to initialize');
      return;
    }
    const container = document.createElement('div');
    container.className = swalClasses.container;
    if (oldContainerExisted) {
      addClass(container, swalClasses['no-transition']);
    }
    setInnerHtml(container, sweetHTML);
    container.dataset['swal2Theme'] = params.theme;
    const targetElement = getTarget(params.target || 'body');
    targetElement.appendChild(container);
    if (params.topLayer) {
      container.setAttribute('popover', '');
      container.showPopover();
    }
    setupAccessibility(params);
    setupRTL(targetElement);
    addInputChangeListeners();
  };

  /**
   * @param {HTMLElement | object | string} param
   * @param {HTMLElement} target
   */
  const parseHtmlToContainer = (param, target) => {
    // DOM element
    if (param instanceof HTMLElement) {
      target.appendChild(param);
    }

    // Object
    else if (typeof param === 'object') {
      handleObject(param, target);
    }

    // Plain string
    else if (param) {
      setInnerHtml(target, param);
    }
  };

  /**
   * @param {object} param
   * @param {HTMLElement} target
   */
  const handleObject = (param, target) => {
    // JQuery element(s)
    if ('jquery' in param) {
      handleJqueryElem(target, param);
    }

    // For other objects use their string representation
    else {
      setInnerHtml(target, param.toString());
    }
  };

  /**
   * @param {HTMLElement} target
   * @param {any} elem
   */
  const handleJqueryElem = (target, elem) => {
    target.textContent = '';
    if (0 in elem) {
      for (let i = 0; i in elem; i++) {
        target.appendChild(elem[i].cloneNode(true));
      }
    } else {
      target.appendChild(elem.cloneNode(true));
    }
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderActions = (instance, params) => {
    const actions = getActions();
    const loader = getLoader();
    if (!actions || !loader) {
      return;
    }

    // Actions (buttons) wrapper
    if (!params.showConfirmButton && !params.showDenyButton && !params.showCancelButton) {
      hide(actions);
    } else {
      show(actions);
    }

    // Custom class
    applyCustomClass(actions, params, 'actions');

    // Render all the buttons
    renderButtons(actions, loader, params);

    // Loader
    setInnerHtml(loader, params.loaderHtml || '');
    applyCustomClass(loader, params, 'loader');
  };

  /**
   * @param {HTMLElement} actions
   * @param {HTMLElement} loader
   * @param {SweetAlertOptions} params
   */
  function renderButtons(actions, loader, params) {
    const confirmButton = getConfirmButton();
    const denyButton = getDenyButton();
    const cancelButton = getCancelButton();
    if (!confirmButton || !denyButton || !cancelButton) {
      return;
    }

    // Render buttons
    renderButton(confirmButton, 'confirm', params);
    renderButton(denyButton, 'deny', params);
    renderButton(cancelButton, 'cancel', params);
    handleButtonsStyling(confirmButton, denyButton, cancelButton, params);
    if (params.reverseButtons) {
      if (params.toast) {
        actions.insertBefore(cancelButton, confirmButton);
        actions.insertBefore(denyButton, confirmButton);
      } else {
        actions.insertBefore(cancelButton, loader);
        actions.insertBefore(denyButton, loader);
        actions.insertBefore(confirmButton, loader);
      }
    }
  }

  /**
   * @param {HTMLElement} confirmButton
   * @param {HTMLElement} denyButton
   * @param {HTMLElement} cancelButton
   * @param {SweetAlertOptions} params
   */
  function handleButtonsStyling(confirmButton, denyButton, cancelButton, params) {
    if (!params.buttonsStyling) {
      removeClass([confirmButton, denyButton, cancelButton], swalClasses.styled);
      return;
    }
    addClass([confirmButton, denyButton, cancelButton], swalClasses.styled);

    // Apply custom background colors to action buttons
    if (params.confirmButtonColor) {
      confirmButton.style.setProperty('--swal2-confirm-button-background-color', params.confirmButtonColor);
    }
    if (params.denyButtonColor) {
      denyButton.style.setProperty('--swal2-deny-button-background-color', params.denyButtonColor);
    }
    if (params.cancelButtonColor) {
      cancelButton.style.setProperty('--swal2-cancel-button-background-color', params.cancelButtonColor);
    }

    // Apply the outline color to action buttons
    applyOutlineColor(confirmButton);
    applyOutlineColor(denyButton);
    applyOutlineColor(cancelButton);
  }

  /**
   * @param {HTMLElement} button
   */
  function applyOutlineColor(button) {
    const buttonStyle = window.getComputedStyle(button);
    if (buttonStyle.getPropertyValue('--swal2-action-button-focus-box-shadow')) {
      // If the button already has a custom outline color, no need to change it
      return;
    }
    const outlineColor = buttonStyle.backgroundColor.replace(/rgba?\((\d+), (\d+), (\d+).*/, 'rgba($1, $2, $3, 0.5)');
    button.style.setProperty('--swal2-action-button-focus-box-shadow', buttonStyle.getPropertyValue('--swal2-outline').replace(/ rgba\(.*/, ` ${outlineColor}`));
  }

  /**
   * @param {HTMLElement} button
   * @param {'confirm' | 'deny' | 'cancel'} buttonType
   * @param {SweetAlertOptions} params
   */
  function renderButton(button, buttonType, params) {
    const buttonName = /** @type {'Confirm' | 'Deny' | 'Cancel'} */capitalizeFirstLetter(buttonType);
    toggle(button, params[`show${buttonName}Button`], 'inline-block');
    setInnerHtml(button, params[`${buttonType}ButtonText`] || ''); // Set caption text
    button.setAttribute('aria-label', params[`${buttonType}ButtonAriaLabel`] || ''); // ARIA label

    // Add buttons custom classes
    button.className = swalClasses[buttonType];
    applyCustomClass(button, params, `${buttonType}Button`);
  }

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderCloseButton = (instance, params) => {
    const closeButton = getCloseButton();
    if (!closeButton) {
      return;
    }
    setInnerHtml(closeButton, params.closeButtonHtml || '');

    // Custom class
    applyCustomClass(closeButton, params, 'closeButton');
    toggle(closeButton, params.showCloseButton);
    closeButton.setAttribute('aria-label', params.closeButtonAriaLabel || '');
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderContainer = (instance, params) => {
    const container = getContainer();
    if (!container) {
      return;
    }
    handleBackdropParam(container, params.backdrop);
    handlePositionParam(container, params.position);
    handleGrowParam(container, params.grow);

    // Custom class
    applyCustomClass(container, params, 'container');
  };

  /**
   * @param {HTMLElement} container
   * @param {SweetAlertOptions['backdrop']} backdrop
   */
  function handleBackdropParam(container, backdrop) {
    if (typeof backdrop === 'string') {
      container.style.background = backdrop;
    } else if (!backdrop) {
      addClass([document.documentElement, document.body], swalClasses['no-backdrop']);
    }
  }

  /**
   * @param {HTMLElement} container
   * @param {SweetAlertOptions['position']} position
   */
  function handlePositionParam(container, position) {
    if (!position) {
      return;
    }
    if (position in swalClasses) {
      addClass(container, swalClasses[position]);
    } else {
      warn('The "position" parameter is not valid, defaulting to "center"');
      addClass(container, swalClasses.center);
    }
  }

  /**
   * @param {HTMLElement} container
   * @param {SweetAlertOptions['grow']} grow
   */
  function handleGrowParam(container, grow) {
    if (!grow) {
      return;
    }
    addClass(container, swalClasses[`grow-${grow}`]);
  }

  /**
   * This module contains `WeakMap`s for each effectively-"private  property" that a `Swal` has.
   * For example, to set the private property "foo" of `this` to "bar", you can `privateProps.foo.set(this, 'bar')`
   * This is the approach that Babel will probably take to implement private methods/fields
   *   https://github.com/tc39/proposal-private-methods
   *   https://github.com/babel/babel/pull/7555
   * Once we have the changes from that PR in Babel, and our core class fits reasonable in *one module*
   *   then we can use that language feature.
   */

  var privateProps = {
    innerParams: new WeakMap(),
    domCache: new WeakMap()
  };

  /// <reference path="../../../../sweetalert2.d.ts"/>


  /** @type {InputClass[]} */
  const inputClasses = ['input', 'file', 'range', 'select', 'radio', 'checkbox', 'textarea'];

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderInput = (instance, params) => {
    const popup = getPopup();
    if (!popup) {
      return;
    }
    const innerParams = privateProps.innerParams.get(instance);
    const rerender = !innerParams || params.input !== innerParams.input;
    inputClasses.forEach(inputClass => {
      const inputContainer = getDirectChildByClass(popup, swalClasses[inputClass]);
      if (!inputContainer) {
        return;
      }

      // set attributes
      setAttributes(inputClass, params.inputAttributes);

      // set class
      inputContainer.className = swalClasses[inputClass];
      if (rerender) {
        hide(inputContainer);
      }
    });
    if (params.input) {
      if (rerender) {
        showInput(params);
      }
      // set custom class
      setCustomClass(params);
    }
  };

  /**
   * @param {SweetAlertOptions} params
   */
  const showInput = params => {
    if (!params.input) {
      return;
    }
    if (!renderInputType[params.input]) {
      error(`Unexpected type of input! Expected ${Object.keys(renderInputType).join(' | ')}, got "${params.input}"`);
      return;
    }
    const inputContainer = getInputContainer(params.input);
    if (!inputContainer) {
      return;
    }
    const input = renderInputType[params.input](inputContainer, params);
    show(inputContainer);

    // input autofocus
    if (params.inputAutoFocus) {
      setTimeout(() => {
        focusInput(input);
      });
    }
  };

  /**
   * @param {HTMLInputElement} input
   */
  const removeAttributes = input => {
    for (let i = 0; i < input.attributes.length; i++) {
      const attrName = input.attributes[i].name;
      if (!['id', 'type', 'value', 'style'].includes(attrName)) {
        input.removeAttribute(attrName);
      }
    }
  };

  /**
   * @param {InputClass} inputClass
   * @param {SweetAlertOptions['inputAttributes']} inputAttributes
   */
  const setAttributes = (inputClass, inputAttributes) => {
    const popup = getPopup();
    if (!popup) {
      return;
    }
    const input = getInput$1(popup, inputClass);
    if (!input) {
      return;
    }
    removeAttributes(input);
    for (const attr in inputAttributes) {
      input.setAttribute(attr, inputAttributes[attr]);
    }
  };

  /**
   * @param {SweetAlertOptions} params
   */
  const setCustomClass = params => {
    if (!params.input) {
      return;
    }
    const inputContainer = getInputContainer(params.input);
    if (inputContainer) {
      applyCustomClass(inputContainer, params, 'input');
    }
  };

  /**
   * @param {HTMLInputElement | HTMLTextAreaElement} input
   * @param {SweetAlertOptions} params
   */
  const setInputPlaceholder = (input, params) => {
    if (!input.placeholder && params.inputPlaceholder) {
      input.placeholder = params.inputPlaceholder;
    }
  };

  /**
   * @param {Input} input
   * @param {Input} prependTo
   * @param {SweetAlertOptions} params
   */
  const setInputLabel = (input, prependTo, params) => {
    if (params.inputLabel) {
      const label = document.createElement('label');
      const labelClass = swalClasses['input-label'];
      label.setAttribute('for', input.id);
      label.className = labelClass;
      if (typeof params.customClass === 'object') {
        addClass(label, params.customClass.inputLabel);
      }
      label.innerText = params.inputLabel;
      prependTo.insertAdjacentElement('beforebegin', label);
    }
  };

  /**
   * @param {SweetAlertInput} inputType
   * @returns {HTMLElement | undefined}
   */
  const getInputContainer = inputType => {
    const popup = getPopup();
    if (!popup) {
      return;
    }
    return getDirectChildByClass(popup, swalClasses[(/** @type {SwalClass} */inputType)] || swalClasses.input);
  };

  /**
   * @param {HTMLInputElement | HTMLOutputElement | HTMLTextAreaElement} input
   * @param {SweetAlertOptions['inputValue']} inputValue
   */
  const checkAndSetInputValue = (input, inputValue) => {
    if (['string', 'number'].includes(typeof inputValue)) {
      input.value = `${inputValue}`;
    } else if (!isPromise(inputValue)) {
      warn(`Unexpected type of inputValue! Expected "string", "number" or "Promise", got "${typeof inputValue}"`);
    }
  };

  /** @type {Record<SweetAlertInput, (input: Input | HTMLElement, params: SweetAlertOptions) => Input>} */
  const renderInputType = {};

  /**
   * @param {Input | HTMLElement} input
   * @param {SweetAlertOptions} params
   * @returns {Input}
   */
  renderInputType.text = renderInputType.email = renderInputType.password = renderInputType.number = renderInputType.tel = renderInputType.url = renderInputType.search = renderInputType.date = renderInputType['datetime-local'] = renderInputType.time = renderInputType.week = renderInputType.month = /** @type {(input: Input | HTMLElement, params: SweetAlertOptions) => Input} */
  (input, params) => {
    const inputElement = /** @type {HTMLInputElement} */input;
    checkAndSetInputValue(inputElement, params.inputValue);
    setInputLabel(inputElement, inputElement, params);
    setInputPlaceholder(inputElement, params);
    inputElement.type = /** @type {string} */params.input;
    return inputElement;
  };

  /**
   * @param {Input | HTMLElement} input
   * @param {SweetAlertOptions} params
   * @returns {Input}
   */
  renderInputType.file = (input, params) => {
    const inputElement = /** @type {HTMLInputElement} */input;
    setInputLabel(inputElement, inputElement, params);
    setInputPlaceholder(inputElement, params);
    return inputElement;
  };

  /**
   * @param {Input | HTMLElement} range
   * @param {SweetAlertOptions} params
   * @returns {Input}
   */
  renderInputType.range = (range, params) => {
    const rangeContainer = /** @type {HTMLElement} */range;
    const rangeInput = rangeContainer.querySelector('input');
    const rangeOutput = rangeContainer.querySelector('output');
    if (rangeInput) {
      checkAndSetInputValue(rangeInput, params.inputValue);
      rangeInput.type = /** @type {string} */params.input;
      setInputLabel(rangeInput, /** @type {Input} */range, params);
    }
    if (rangeOutput) {
      checkAndSetInputValue(rangeOutput, params.inputValue);
    }
    return /** @type {Input} */range;
  };

  /**
   * @param {Input | HTMLElement} select
   * @param {SweetAlertOptions} params
   * @returns {Input}
   */
  renderInputType.select = (select, params) => {
    const selectElement = /** @type {HTMLSelectElement} */select;
    selectElement.textContent = '';
    if (params.inputPlaceholder) {
      const placeholder = document.createElement('option');
      setInnerHtml(placeholder, params.inputPlaceholder);
      placeholder.value = '';
      placeholder.disabled = true;
      placeholder.selected = true;
      selectElement.appendChild(placeholder);
    }
    setInputLabel(selectElement, selectElement, params);
    return selectElement;
  };

  /**
   * @param {Input | HTMLElement} radio
   * @returns {Input}
   */
  renderInputType.radio = radio => {
    const radioElement = /** @type {HTMLElement} */radio;
    radioElement.textContent = '';
    return /** @type {Input} */radio;
  };

  /**
   * @param {Input | HTMLElement} checkboxContainer
   * @param {SweetAlertOptions} params
   * @returns {Input}
   */
  renderInputType.checkbox = (checkboxContainer, params) => {
    const popup = getPopup();
    if (!popup) {
      throw new Error('Popup not found');
    }
    const checkbox = getInput$1(popup, 'checkbox');
    if (!checkbox) {
      throw new Error('Checkbox input not found');
    }
    checkbox.value = '1';
    checkbox.checked = Boolean(params.inputValue);
    const containerElement = /** @type {HTMLElement} */checkboxContainer;
    const label = containerElement.querySelector('span');
    if (label) {
      const placeholderOrLabel = params.inputPlaceholder || params.inputLabel;
      if (placeholderOrLabel) {
        setInnerHtml(label, placeholderOrLabel);
      }
    }
    return checkbox;
  };

  /**
   * @param {Input | HTMLElement} textarea
   * @param {SweetAlertOptions} params
   * @returns {Input}
   */
  renderInputType.textarea = (textarea, params) => {
    const textareaElement = /** @type {HTMLTextAreaElement} */textarea;
    checkAndSetInputValue(textareaElement, params.inputValue);
    setInputPlaceholder(textareaElement, params);
    setInputLabel(textareaElement, textareaElement, params);

    /**
     * @param {HTMLElement} el
     * @returns {number}
     */
    const getMargin = el => parseInt(window.getComputedStyle(el).marginLeft) + parseInt(window.getComputedStyle(el).marginRight);

    // https://github.com/sweetalert2/sweetalert2/issues/2291
    setTimeout(() => {
      // https://github.com/sweetalert2/sweetalert2/issues/1699
      if ('MutationObserver' in window) {
        const popup = getPopup();
        if (!popup) {
          return;
        }
        const initialPopupWidth = parseInt(window.getComputedStyle(popup).width);
        const textareaResizeHandler = () => {
          // check if texarea is still in document (i.e. popup wasn't closed in the meantime)
          if (!document.body.contains(textareaElement)) {
            return;
          }
          const textareaWidth = textareaElement.offsetWidth + getMargin(textareaElement);
          const popupElement = getPopup();
          if (popupElement) {
            if (textareaWidth > initialPopupWidth) {
              popupElement.style.width = `${textareaWidth}px`;
            } else {
              applyNumericalStyle(popupElement, 'width', params.width);
            }
          }
        };
        new MutationObserver(textareaResizeHandler).observe(textareaElement, {
          attributes: true,
          attributeFilter: ['style']
        });
      }
    });
    return textareaElement;
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderContent = (instance, params) => {
    const htmlContainer = getHtmlContainer();
    if (!htmlContainer) {
      return;
    }
    showWhenInnerHtmlPresent(htmlContainer);
    applyCustomClass(htmlContainer, params, 'htmlContainer');

    // Content as HTML
    if (params.html) {
      parseHtmlToContainer(params.html, htmlContainer);
      show(htmlContainer, 'block');
    }

    // Content as plain text
    else if (params.text) {
      htmlContainer.textContent = params.text;
      show(htmlContainer, 'block');
    }

    // No content
    else {
      hide(htmlContainer);
    }
    renderInput(instance, params);
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderFooter = (instance, params) => {
    const footer = getFooter();
    if (!footer) {
      return;
    }
    showWhenInnerHtmlPresent(footer);
    toggle(footer, Boolean(params.footer), 'block');
    if (params.footer) {
      parseHtmlToContainer(params.footer, footer);
    }

    // Custom class
    applyCustomClass(footer, params, 'footer');
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderIcon = (instance, params) => {
    const innerParams = privateProps.innerParams.get(instance);
    const icon = getIcon();
    if (!icon) {
      return;
    }

    // if the given icon already rendered, apply the styling without re-rendering the icon
    if (innerParams && params.icon === innerParams.icon) {
      // Custom or default content
      setContent(icon, params);
      applyStyles(icon, params);
      return;
    }
    if (!params.icon && !params.iconHtml) {
      hide(icon);
      return;
    }
    if (params.icon && Object.keys(iconTypes).indexOf(params.icon) === -1) {
      error(`Unknown icon! Expected "success", "error", "warning", "info" or "question", got "${params.icon}"`);
      hide(icon);
      return;
    }
    show(icon);

    // Custom or default content
    setContent(icon, params);
    applyStyles(icon, params);

    // Animate icon
    addClass(icon, params.showClass && params.showClass.icon);

    // Re-adjust the success icon on system theme change
    const colorSchemeQueryList = window.matchMedia('(prefers-color-scheme: dark)');
    colorSchemeQueryList.addEventListener('change', adjustSuccessIconBackgroundColor);
  };

  /**
   * @param {HTMLElement} icon
   * @param {SweetAlertOptions} params
   */
  const applyStyles = (icon, params) => {
    for (const [iconType, iconClassName] of Object.entries(iconTypes)) {
      if (params.icon !== iconType) {
        removeClass(icon, iconClassName);
      }
    }
    addClass(icon, params.icon && iconTypes[params.icon]);

    // Icon color
    setColor(icon, params);

    // Success icon background color
    adjustSuccessIconBackgroundColor();

    // Custom class
    applyCustomClass(icon, params, 'icon');
  };

  // Adjust success icon background color to match the popup background color
  const adjustSuccessIconBackgroundColor = () => {
    const popup = getPopup();
    if (!popup) {
      return;
    }
    const popupBackgroundColor = window.getComputedStyle(popup).getPropertyValue('background-color');
    /** @type {NodeListOf<HTMLElement>} */
    const successIconParts = popup.querySelectorAll('[class^=swal2-success-circular-line], .swal2-success-fix');
    for (let i = 0; i < successIconParts.length; i++) {
      successIconParts[i].style.backgroundColor = popupBackgroundColor;
    }
  };

  /**
   *
   * @param {SweetAlertOptions} params
   * @returns {string}
   */
  const successIconHtml = params => `
  ${params.animation ? '<div class="swal2-success-circular-line-left"></div>' : ''}
  <span class="swal2-success-line-tip"></span> <span class="swal2-success-line-long"></span>
  <div class="swal2-success-ring"></div>
  ${params.animation ? '<div class="swal2-success-fix"></div>' : ''}
  ${params.animation ? '<div class="swal2-success-circular-line-right"></div>' : ''}
`;
  const errorIconHtml = `
  <span class="swal2-x-mark">
    <span class="swal2-x-mark-line-left"></span>
    <span class="swal2-x-mark-line-right"></span>
  </span>
`;

  /**
   * @param {HTMLElement} icon
   * @param {SweetAlertOptions} params
   */
  const setContent = (icon, params) => {
    if (!params.icon && !params.iconHtml) {
      return;
    }
    let oldContent = icon.innerHTML;
    let newContent = '';
    if (params.iconHtml) {
      newContent = iconContent(params.iconHtml);
    } else if (params.icon === 'success') {
      newContent = successIconHtml(params);
      oldContent = oldContent.replace(/ style=".*?"/g, ''); // undo adjustSuccessIconBackgroundColor()
    } else if (params.icon === 'error') {
      newContent = errorIconHtml;
    } else if (params.icon) {
      const defaultIconHtml = {
        question: '?',
        warning: '!',
        info: 'i'
      };
      newContent = iconContent(defaultIconHtml[params.icon]);
    }
    if (oldContent.trim() !== newContent.trim()) {
      setInnerHtml(icon, newContent);
    }
  };

  /**
   * @param {HTMLElement} icon
   * @param {SweetAlertOptions} params
   */
  const setColor = (icon, params) => {
    if (!params.iconColor) {
      return;
    }
    icon.style.color = params.iconColor;
    icon.style.borderColor = params.iconColor;
    for (const sel of ['.swal2-success-line-tip', '.swal2-success-line-long', '.swal2-x-mark-line-left', '.swal2-x-mark-line-right']) {
      setStyle(icon, sel, 'background-color', params.iconColor);
    }
    setStyle(icon, '.swal2-success-ring', 'border-color', params.iconColor);
  };

  /**
   * @param {string} content
   * @returns {string}
   */
  const iconContent = content => `<div class="${swalClasses['icon-content']}">${content}</div>`;

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderImage = (instance, params) => {
    const image = getImage();
    if (!image) {
      return;
    }
    if (!params.imageUrl) {
      hide(image);
      return;
    }
    show(image, '');

    // Src, alt
    image.setAttribute('src', params.imageUrl);
    image.setAttribute('alt', params.imageAlt || '');

    // Width, height
    applyNumericalStyle(image, 'width', params.imageWidth);
    applyNumericalStyle(image, 'height', params.imageHeight);

    // Class
    image.className = swalClasses.image;
    applyCustomClass(image, params, 'image');
  };

  let dragging = false;
  let mousedownX = 0;
  let mousedownY = 0;
  let initialX = 0;
  let initialY = 0;

  /**
   * @param {HTMLElement} popup
   */
  const addDraggableListeners = popup => {
    popup.addEventListener('mousedown', down);
    document.body.addEventListener('mousemove', move);
    popup.addEventListener('mouseup', up);
    popup.addEventListener('touchstart', down);
    document.body.addEventListener('touchmove', move);
    popup.addEventListener('touchend', up);
  };

  /**
   * @param {HTMLElement} popup
   */
  const removeDraggableListeners = popup => {
    popup.removeEventListener('mousedown', down);
    document.body.removeEventListener('mousemove', move);
    popup.removeEventListener('mouseup', up);
    popup.removeEventListener('touchstart', down);
    document.body.removeEventListener('touchmove', move);
    popup.removeEventListener('touchend', up);
  };

  /**
   * @param {MouseEvent | TouchEvent} event
   */
  const down = event => {
    const popup = getPopup();
    if (!popup) {
      return;
    }
    const icon = getIcon();
    if (event.target === popup || icon && icon.contains(/** @type {HTMLElement} */event.target)) {
      dragging = true;
      const clientXY = getClientXY(event);
      mousedownX = clientXY.clientX;
      mousedownY = clientXY.clientY;
      initialX = parseInt(popup.style.insetInlineStart) || 0;
      initialY = parseInt(popup.style.insetBlockStart) || 0;
      addClass(popup, 'swal2-dragging');
    }
  };

  /**
   * @param {MouseEvent | TouchEvent} event
   */
  const move = event => {
    const popup = getPopup();
    if (!popup) {
      return;
    }
    if (dragging) {
      let {
        clientX,
        clientY
      } = getClientXY(event);
      const deltaX = clientX - mousedownX;
      // In RTL mode, negate the horizontal delta since insetInlineStart refers to the right edge
      popup.style.insetInlineStart = `${initialX + (globalState.isRTL ? -deltaX : deltaX)}px`;
      popup.style.insetBlockStart = `${initialY + (clientY - mousedownY)}px`;
    }
  };
  const up = () => {
    const popup = getPopup();
    dragging = false;
    removeClass(popup, 'swal2-dragging');
  };

  /**
   * @param {MouseEvent | TouchEvent} event
   * @returns {{ clientX: number, clientY: number }}
   */
  const getClientXY = event => {
    let clientX = 0,
      clientY = 0;
    if (event.type.startsWith('mouse')) {
      clientX = /** @type {MouseEvent} */event.clientX;
      clientY = /** @type {MouseEvent} */event.clientY;
    } else if (event.type.startsWith('touch')) {
      clientX = /** @type {TouchEvent} */event.touches[0].clientX;
      clientY = /** @type {TouchEvent} */event.touches[0].clientY;
    }
    return {
      clientX,
      clientY
    };
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderPopup = (instance, params) => {
    const container = getContainer();
    const popup = getPopup();
    if (!container || !popup) {
      return;
    }

    // Width
    // https://github.com/sweetalert2/sweetalert2/issues/2170
    if (params.toast) {
      applyNumericalStyle(container, 'width', params.width);
      popup.style.width = '100%';
      const loader = getLoader();
      if (loader) {
        popup.insertBefore(loader, getIcon());
      }
    } else {
      applyNumericalStyle(popup, 'width', params.width);
    }

    // Padding
    applyNumericalStyle(popup, 'padding', params.padding);

    // Color
    if (params.color) {
      popup.style.color = params.color;
    }

    // Background
    if (params.background) {
      popup.style.background = params.background;
    }
    hide(getValidationMessage());

    // Classes
    addClasses$1(popup, params);
    if (params.draggable && !params.toast) {
      addClass(popup, swalClasses.draggable);
      addDraggableListeners(popup);
    } else {
      removeClass(popup, swalClasses.draggable);
      removeDraggableListeners(popup);
    }
  };

  /**
   * @param {HTMLElement} popup
   * @param {SweetAlertOptions} params
   */
  const addClasses$1 = (popup, params) => {
    const showClass = params.showClass || {};
    // Default Class + showClass when updating Swal.update({})
    popup.className = `${swalClasses.popup} ${isVisible$1(popup) ? showClass.popup : ''}`;
    if (params.toast) {
      addClass([document.documentElement, document.body], swalClasses['toast-shown']);
      addClass(popup, swalClasses.toast);
    } else {
      addClass(popup, swalClasses.modal);
    }

    // Custom class
    applyCustomClass(popup, params, 'popup');
    // TODO: remove in the next major
    if (typeof params.customClass === 'string') {
      addClass(popup, params.customClass);
    }

    // Icon class (#1842)
    if (params.icon) {
      addClass(popup, swalClasses[`icon-${params.icon}`]);
    }
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderProgressSteps = (instance, params) => {
    const progressStepsContainer = getProgressSteps();
    if (!progressStepsContainer) {
      return;
    }
    const {
      progressSteps,
      currentProgressStep
    } = params;
    if (!progressSteps || progressSteps.length === 0 || currentProgressStep === undefined) {
      hide(progressStepsContainer);
      return;
    }
    show(progressStepsContainer);
    progressStepsContainer.textContent = '';
    if (currentProgressStep >= progressSteps.length) {
      warn('Invalid currentProgressStep parameter, it should be less than progressSteps.length ' + '(currentProgressStep like JS arrays starts from 0)');
    }
    progressSteps.forEach((step, index) => {
      const stepEl = createStepElement(step);
      progressStepsContainer.appendChild(stepEl);
      if (index === currentProgressStep) {
        addClass(stepEl, swalClasses['active-progress-step']);
      }
      if (index !== progressSteps.length - 1) {
        const lineEl = createLineElement(params);
        progressStepsContainer.appendChild(lineEl);
      }
    });
  };

  /**
   * @param {string} step
   * @returns {HTMLLIElement}
   */
  const createStepElement = step => {
    const stepEl = document.createElement('li');
    addClass(stepEl, swalClasses['progress-step']);
    setInnerHtml(stepEl, step);
    return stepEl;
  };

  /**
   * @param {SweetAlertOptions} params
   * @returns {HTMLLIElement}
   */
  const createLineElement = params => {
    const lineEl = document.createElement('li');
    addClass(lineEl, swalClasses['progress-step-line']);
    if (params.progressStepsDistance) {
      applyNumericalStyle(lineEl, 'width', params.progressStepsDistance);
    }
    return lineEl;
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const renderTitle = (instance, params) => {
    const title = getTitle();
    if (!title) {
      return;
    }
    showWhenInnerHtmlPresent(title);
    toggle(title, Boolean(params.title || params.titleText), 'block');
    if (params.title) {
      parseHtmlToContainer(params.title, title);
    }
    if (params.titleText) {
      title.innerText = params.titleText;
    }

    // Custom class
    applyCustomClass(title, params, 'title');
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const render = (instance, params) => {
    var _globalState$eventEmi;
    renderPopup(instance, params);
    renderContainer(instance, params);
    renderProgressSteps(instance, params);
    renderIcon(instance, params);
    renderImage(instance, params);
    renderTitle(instance, params);
    renderCloseButton(instance, params);
    renderContent(instance, params);
    renderActions(instance, params);
    renderFooter(instance, params);
    const popup = getPopup();
    if (typeof params.didRender === 'function' && popup) {
      params.didRender(popup);
    }
    (_globalState$eventEmi = globalState.eventEmitter) === null || _globalState$eventEmi === void 0 || _globalState$eventEmi.emit('didRender', popup);
  };

  /*
   * Global function to determine if SweetAlert2 popup is shown
   */
  const isVisible = () => {
    return isVisible$1(getPopup());
  };

  /*
   * Global function to click 'Confirm' button
   */
  const clickConfirm = () => {
    var _dom$getConfirmButton;
    return (_dom$getConfirmButton = getConfirmButton()) === null || _dom$getConfirmButton === void 0 ? void 0 : _dom$getConfirmButton.click();
  };

  /*
   * Global function to click 'Deny' button
   */
  const clickDeny = () => {
    var _dom$getDenyButton;
    return (_dom$getDenyButton = getDenyButton()) === null || _dom$getDenyButton === void 0 ? void 0 : _dom$getDenyButton.click();
  };

  /*
   * Global function to click 'Cancel' button
   */
  const clickCancel = () => {
    var _dom$getCancelButton;
    return (_dom$getCancelButton = getCancelButton()) === null || _dom$getCancelButton === void 0 ? void 0 : _dom$getCancelButton.click();
  };

  /** @type {Record<DismissReason, DismissReason>} */
  const DismissReason = Object.freeze({
    cancel: 'cancel',
    backdrop: 'backdrop',
    close: 'close',
    esc: 'esc',
    timer: 'timer'
  });

  /**
   * @param {GlobalState} globalState
   */
  const removeKeydownHandler = globalState => {
    if (globalState.keydownTarget && globalState.keydownHandlerAdded && globalState.keydownHandler) {
      const handler = /** @type {EventListenerOrEventListenerObject} */ /** @type {unknown} */globalState.keydownHandler;
      globalState.keydownTarget.removeEventListener('keydown', handler, {
        capture: globalState.keydownListenerCapture
      });
      globalState.keydownHandlerAdded = false;
    }
  };

  /**
   * @param {GlobalState} globalState
   * @param {SweetAlertOptions} innerParams
   * @param {(dismiss: DismissReason) => void} dismissWith
   */
  const addKeydownHandler = (globalState, innerParams, dismissWith) => {
    removeKeydownHandler(globalState);
    if (!innerParams.toast) {
      /** @type {(this: HTMLElement, event: KeyboardEvent) => void} */
      const handler = e => keydownHandler(innerParams, e, dismissWith);
      globalState.keydownHandler = handler;
      const target = innerParams.keydownListenerCapture ? window : getPopup();
      if (target) {
        globalState.keydownTarget = target;
        globalState.keydownListenerCapture = innerParams.keydownListenerCapture;
        const eventHandler = /** @type {EventListenerOrEventListenerObject} */ /** @type {unknown} */handler;
        globalState.keydownTarget.addEventListener('keydown', eventHandler, {
          capture: globalState.keydownListenerCapture
        });
        globalState.keydownHandlerAdded = true;
      }
    }
  };

  /**
   * @param {number} index
   * @param {number} increment
   */
  const setFocus = (index, increment) => {
    var _dom$getPopup;
    const focusableElements = getFocusableElements();
    // search for visible elements and select the next possible match
    if (focusableElements.length) {
      index = index + increment;

      // shift + tab when .swal2-popup is focused
      if (index === -2) {
        index = focusableElements.length - 1;
      }

      // rollover to first item
      if (index === focusableElements.length) {
        index = 0;

        // go to last item
      } else if (index === -1) {
        index = focusableElements.length - 1;
      }
      focusableElements[index].focus();
      return;
    }
    // no visible focusable elements, focus the popup
    (_dom$getPopup = getPopup()) === null || _dom$getPopup === void 0 || _dom$getPopup.focus();
  };
  const arrowKeysNextButton = ['ArrowRight', 'ArrowDown'];
  const arrowKeysPreviousButton = ['ArrowLeft', 'ArrowUp'];

  /**
   * @param {SweetAlertOptions} innerParams
   * @param {KeyboardEvent} event
   * @param {(dismiss: DismissReason) => void} dismissWith
   */
  const keydownHandler = (innerParams, event, dismissWith) => {
    if (!innerParams) {
      return; // This instance has already been destroyed
    }

    // Ignore keydown during IME composition
    // https://developer.mozilla.org/en-US/docs/Web/API/Document/keydown_event#ignoring_keydown_during_ime_composition
    // https://github.com/sweetalert2/sweetalert2/issues/720
    // https://github.com/sweetalert2/sweetalert2/issues/2406
    if (event.isComposing || event.keyCode === 229) {
      return;
    }
    if (innerParams.stopKeydownPropagation) {
      event.stopPropagation();
    }

    // ENTER
    if (event.key === 'Enter') {
      handleEnter(event, innerParams);
    }

    // TAB
    else if (event.key === 'Tab') {
      handleTab(event);
    }

    // ARROWS - switch focus between buttons
    else if ([...arrowKeysNextButton, ...arrowKeysPreviousButton].includes(event.key)) {
      handleArrows(event.key);
    }

    // ESC
    else if (event.key === 'Escape') {
      handleEsc(event, innerParams, dismissWith);
    }
  };

  /**
   * @param {KeyboardEvent} event
   * @param {SweetAlertOptions} innerParams
   */
  const handleEnter = (event, innerParams) => {
    // https://github.com/sweetalert2/sweetalert2/issues/2386
    if (!callIfFunction(innerParams.allowEnterKey)) {
      return;
    }
    const popup = getPopup();
    if (!popup || !innerParams.input) {
      return;
    }
    const input = getInput$1(popup, innerParams.input);
    if (event.target && input && event.target instanceof HTMLElement && event.target.outerHTML === input.outerHTML) {
      if (['textarea', 'file'].includes(innerParams.input)) {
        return; // do not submit
      }
      clickConfirm();
      event.preventDefault();
    }
  };

  /**
   * @param {KeyboardEvent} event
   */
  const handleTab = event => {
    const targetElement = event.target;
    const focusableElements = getFocusableElements();
    let btnIndex = -1;
    for (let i = 0; i < focusableElements.length; i++) {
      if (targetElement === focusableElements[i]) {
        btnIndex = i;
        break;
      }
    }

    // Cycle to the next button
    if (!event.shiftKey) {
      setFocus(btnIndex, 1);
    }

    // Cycle to the prev button
    else {
      setFocus(btnIndex, -1);
    }
    event.stopPropagation();
    event.preventDefault();
  };

  /**
   * @param {string} key
   */
  const handleArrows = key => {
    const actions = getActions();
    const confirmButton = getConfirmButton();
    const denyButton = getDenyButton();
    const cancelButton = getCancelButton();
    if (!actions || !confirmButton || !denyButton || !cancelButton) {
      return;
    }
    /** @type HTMLElement[] */
    const buttons = [confirmButton, denyButton, cancelButton];
    if (document.activeElement instanceof HTMLElement && !buttons.includes(document.activeElement)) {
      return;
    }
    const sibling = arrowKeysNextButton.includes(key) ? 'nextElementSibling' : 'previousElementSibling';
    let buttonToFocus = document.activeElement;
    if (!buttonToFocus) {
      return;
    }
    for (let i = 0; i < actions.children.length; i++) {
      buttonToFocus = buttonToFocus[sibling];
      if (!buttonToFocus) {
        return;
      }
      if (buttonToFocus instanceof HTMLButtonElement && isVisible$1(buttonToFocus)) {
        break;
      }
    }
    if (buttonToFocus instanceof HTMLButtonElement) {
      buttonToFocus.focus();
    }
  };

  /**
   * @param {KeyboardEvent} event
   * @param {SweetAlertOptions} innerParams
   * @param {(dismiss: DismissReason) => void} dismissWith
   */
  const handleEsc = (event, innerParams, dismissWith) => {
    event.preventDefault();
    if (callIfFunction(innerParams.allowEscapeKey)) {
      dismissWith(DismissReason.esc);
    }
  };

  /**
   * This module contains `WeakMap`s for each effectively-"private  property" that a `Swal` has.
   * For example, to set the private property "foo" of `this` to "bar", you can `privateProps.foo.set(this, 'bar')`
   * This is the approach that Babel will probably take to implement private methods/fields
   *   https://github.com/tc39/proposal-private-methods
   *   https://github.com/babel/babel/pull/7555
   * Once we have the changes from that PR in Babel, and our core class fits reasonable in *one module*
   *   then we can use that language feature.
   */

  var privateMethods = {
    swalPromiseResolve: new WeakMap(),
    swalPromiseReject: new WeakMap()
  };

  // From https://developer.paciellogroup.com/blog/2018/06/the-current-state-of-modal-dialog-accessibility/
  // Adding aria-hidden="true" to elements outside of the active modal dialog ensures that
  // elements not within the active modal dialog will not be surfaced if a user opens a screen
  // reader’s list of elements (headings, form controls, landmarks, etc.) in the document.

  const setAriaHidden = () => {
    const container = getContainer();
    const bodyChildren = Array.from(document.body.children);
    bodyChildren.forEach(el => {
      if (el.contains(container)) {
        return;
      }
      if (el.hasAttribute('aria-hidden')) {
        el.setAttribute('data-previous-aria-hidden', el.getAttribute('aria-hidden') || '');
      }
      el.setAttribute('aria-hidden', 'true');
    });
  };
  const unsetAriaHidden = () => {
    const bodyChildren = Array.from(document.body.children);
    bodyChildren.forEach(el => {
      if (el.hasAttribute('data-previous-aria-hidden')) {
        el.setAttribute('aria-hidden', el.getAttribute('data-previous-aria-hidden') || '');
        el.removeAttribute('data-previous-aria-hidden');
      } else {
        el.removeAttribute('aria-hidden');
      }
    });
  };

  // @ts-ignore
  const isSafariOrIOS = typeof window !== 'undefined' && Boolean(window.GestureEvent); // true for Safari desktop + all iOS browsers https://stackoverflow.com/a/70585394

  /**
   * Fix iOS scrolling
   * http://stackoverflow.com/q/39626302
   */
  const iOSfix = () => {
    if (isSafariOrIOS && !hasClass(document.body, swalClasses.iosfix)) {
      const offset = document.body.scrollTop;
      document.body.style.top = `${offset * -1}px`;
      addClass(document.body, swalClasses.iosfix);
      lockBodyScroll();
    }
  };

  /**
   * https://github.com/sweetalert2/sweetalert2/issues/1246
   */
  const lockBodyScroll = () => {
    const container = getContainer();
    if (!container) {
      return;
    }
    /** @type {boolean} */
    let preventTouchMove;
    /**
     * @param {TouchEvent} event
     */
    container.ontouchstart = event => {
      preventTouchMove = shouldPreventTouchMove(event);
    };
    /**
     * @param {TouchEvent} event
     */
    container.ontouchmove = event => {
      if (preventTouchMove) {
        event.preventDefault();
        event.stopPropagation();
      }
    };
  };

  /**
   * @param {TouchEvent} event
   * @returns {boolean}
   */
  const shouldPreventTouchMove = event => {
    const target = event.target;
    const container = getContainer();
    const htmlContainer = getHtmlContainer();
    if (!container || !htmlContainer) {
      return false;
    }
    if (isStylus(event) || isZoom(event)) {
      return false;
    }
    if (target === container) {
      return true;
    }
    if (!isScrollable(container) && target instanceof HTMLElement && !selfOrParentIsScrollable(target, htmlContainer) &&
    // #2823
    target.tagName !== 'INPUT' &&
    // #1603
    target.tagName !== 'TEXTAREA' &&
    // #2266
    !(isScrollable(htmlContainer) &&
    // #1944
    htmlContainer.contains(target))) {
      return true;
    }
    return false;
  };

  /**
   * https://github.com/sweetalert2/sweetalert2/issues/1786
   *
   * @param {TouchEvent} event
   * @returns {boolean}
   */
  const isStylus = event => {
    return Boolean(event.touches && event.touches.length &&
    // @ts-ignore - touchType is not a standard property
    event.touches[0].touchType === 'stylus');
  };

  /**
   * https://github.com/sweetalert2/sweetalert2/issues/1891
   *
   * @param {TouchEvent} event
   * @returns {boolean}
   */
  const isZoom = event => {
    return event.touches && event.touches.length > 1;
  };
  const undoIOSfix = () => {
    if (hasClass(document.body, swalClasses.iosfix)) {
      const offset = parseInt(document.body.style.top, 10);
      removeClass(document.body, swalClasses.iosfix);
      document.body.style.top = '';
      document.body.scrollTop = offset * -1;
    }
  };

  /**
   * Measure scrollbar width for padding body during modal show/hide
   * https://github.com/twbs/bootstrap/blob/master/js/src/modal.js
   *
   * @returns {number}
   */
  const measureScrollbar = () => {
    const scrollDiv = document.createElement('div');
    scrollDiv.className = swalClasses['scrollbar-measure'];
    document.body.appendChild(scrollDiv);
    const scrollbarWidth = scrollDiv.getBoundingClientRect().width - scrollDiv.clientWidth;
    document.body.removeChild(scrollDiv);
    return scrollbarWidth;
  };

  /**
   * Remember state in cases where opening and handling a modal will fiddle with it.
   * @type {number | null}
   */
  let previousBodyPadding = null;

  /**
   * @param {string} initialBodyOverflow
   */
  const replaceScrollbarWithPadding = initialBodyOverflow => {
    // for queues, do not do this more than once
    if (previousBodyPadding !== null) {
      return;
    }
    // if the body has overflow
    if (document.body.scrollHeight > window.innerHeight || initialBodyOverflow === 'scroll' // https://github.com/sweetalert2/sweetalert2/issues/2663
    ) {
      // add padding so the content doesn't shift after removal of scrollbar
      previousBodyPadding = parseInt(window.getComputedStyle(document.body).getPropertyValue('padding-right'));
      document.body.style.paddingRight = `${previousBodyPadding + measureScrollbar()}px`;
    }
  };
  const undoReplaceScrollbarWithPadding = () => {
    if (previousBodyPadding !== null) {
      document.body.style.paddingRight = `${previousBodyPadding}px`;
      previousBodyPadding = null;
    }
  };

  /**
   * @param {SweetAlert} instance
   * @param {HTMLElement} container
   * @param {boolean} returnFocus
   * @param {(() => void) | undefined} didClose
   */
  function removePopupAndResetState(instance, container, returnFocus, didClose) {
    if (isToast()) {
      triggerDidCloseAndDispose(instance, didClose);
    } else {
      restoreActiveElement(returnFocus).then(() => triggerDidCloseAndDispose(instance, didClose));
      removeKeydownHandler(globalState);
    }

    // workaround for https://github.com/sweetalert2/sweetalert2/issues/2088
    // for some reason removing the container in Safari will scroll the document to bottom
    if (isSafariOrIOS) {
      container.setAttribute('style', 'display:none !important');
      container.removeAttribute('class');
      container.innerHTML = '';
    } else {
      container.remove();
    }
    if (isModal()) {
      undoReplaceScrollbarWithPadding();
      undoIOSfix();
      unsetAriaHidden();
    }
    removeBodyClasses();
  }

  /**
   * Remove SweetAlert2 classes from body
   */
  function removeBodyClasses() {
    removeClass([document.documentElement, document.body], [swalClasses.shown, swalClasses['height-auto'], swalClasses['no-backdrop'], swalClasses['toast-shown']]);
  }

  /**
   * Instance method to close sweetAlert
   *
   * @param {SweetAlertResult | undefined} resolveValue
   * @this {SweetAlert}
   */
  function close(resolveValue) {
    resolveValue = prepareResolveValue(resolveValue);
    const swalPromiseResolve = privateMethods.swalPromiseResolve.get(this);
    const didClose = triggerClosePopup(this);
    if (this.isAwaitingPromise) {
      // A swal awaiting for a promise (after a click on Confirm or Deny) cannot be dismissed anymore #2335
      if (!resolveValue.isDismissed) {
        handleAwaitingPromise(this);
        swalPromiseResolve(resolveValue);
      }
    } else if (didClose) {
      // Resolve Swal promise
      swalPromiseResolve(resolveValue);
    }
  }

  /**
   * @param {SweetAlert} instance
   * @returns {boolean}
   */
  const triggerClosePopup = instance => {
    const popup = getPopup();
    if (!popup) {
      return false;
    }
    const innerParams = privateProps.innerParams.get(instance);
    if (!innerParams || hasClass(popup, innerParams.hideClass.popup)) {
      return false;
    }
    removeClass(popup, innerParams.showClass.popup);
    addClass(popup, innerParams.hideClass.popup);
    const backdrop = getContainer();
    removeClass(backdrop, innerParams.showClass.backdrop);
    addClass(backdrop, innerParams.hideClass.backdrop);
    handlePopupAnimation(instance, popup, innerParams);
    return true;
  };

  /**
   * @param {Error | string} error
   * @this {SweetAlert}
   */
  function rejectPromise(error) {
    const rejectPromise = privateMethods.swalPromiseReject.get(this);
    handleAwaitingPromise(this);
    if (rejectPromise) {
      // Reject Swal promise
      rejectPromise(error);
    }
  }

  /**
   * @param {SweetAlert} instance
   */
  const handleAwaitingPromise = instance => {
    if (instance.isAwaitingPromise) {
      // @ts-ignore
      delete instance.isAwaitingPromise;
      // The instance might have been previously partly destroyed, we must resume the destroy process in this case #2335
      if (!privateProps.innerParams.get(instance)) {
        instance._destroy();
      }
    }
  };

  /**
   * @param {SweetAlertResult | undefined} resolveValue
   * @returns {SweetAlertResult}
   */
  const prepareResolveValue = resolveValue => {
    // When user calls Swal.close()
    if (typeof resolveValue === 'undefined') {
      return {
        isConfirmed: false,
        isDenied: false,
        isDismissed: true
      };
    }
    return Object.assign({
      isConfirmed: false,
      isDenied: false,
      isDismissed: false
    }, resolveValue);
  };

  /**
   * @param {SweetAlert} instance
   * @param {HTMLElement} popup
   * @param {SweetAlertOptions} innerParams
   */
  const handlePopupAnimation = (instance, popup, innerParams) => {
    var _globalState$eventEmi;
    const container = getContainer();
    // If animation is supported, animate
    const animationIsSupported = hasCssAnimation(popup);
    if (typeof innerParams.willClose === 'function') {
      innerParams.willClose(popup);
    }
    (_globalState$eventEmi = globalState.eventEmitter) === null || _globalState$eventEmi === void 0 || _globalState$eventEmi.emit('willClose', popup);
    if (animationIsSupported && container) {
      animatePopup(instance, popup, container, Boolean(innerParams.returnFocus), innerParams.didClose);
    } else if (container) {
      // Otherwise, remove immediately
      removePopupAndResetState(instance, container, Boolean(innerParams.returnFocus), innerParams.didClose);
    }
  };

  /**
   * @param {SweetAlert} instance
   * @param {HTMLElement} popup
   * @param {HTMLElement} container
   * @param {boolean} returnFocus
   * @param {(() => void) | undefined} didClose
   */
  const animatePopup = (instance, popup, container, returnFocus, didClose) => {
    globalState.swalCloseEventFinishedCallback = removePopupAndResetState.bind(null, instance, container, returnFocus, didClose);
    /**
     * @param {AnimationEvent | TransitionEvent} e
     */
    const swalCloseAnimationFinished = function (e) {
      if (e.target === popup) {
        var _globalState$swalClos;
        (_globalState$swalClos = globalState.swalCloseEventFinishedCallback) === null || _globalState$swalClos === void 0 || _globalState$swalClos.call(globalState);
        delete globalState.swalCloseEventFinishedCallback;
        popup.removeEventListener('animationend', swalCloseAnimationFinished);
        popup.removeEventListener('transitionend', swalCloseAnimationFinished);
      }
    };
    popup.addEventListener('animationend', swalCloseAnimationFinished);
    popup.addEventListener('transitionend', swalCloseAnimationFinished);
  };

  /**
   * @param {SweetAlert} instance
   * @param {(() => void) | undefined} didClose
   */
  const triggerDidCloseAndDispose = (instance, didClose) => {
    setTimeout(() => {
      var _globalState$eventEmi2;
      if (typeof didClose === 'function') {
        didClose.bind(instance.params)();
      }
      (_globalState$eventEmi2 = globalState.eventEmitter) === null || _globalState$eventEmi2 === void 0 || _globalState$eventEmi2.emit('didClose');
      // instance might have been destroyed already
      if (instance._destroy) {
        instance._destroy();
      }
    });
  };

  /**
   * Shows loader (spinner), this is useful with AJAX requests.
   * By default the loader be shown instead of the "Confirm" button.
   *
   * @param {HTMLButtonElement | null} [buttonToReplace]
   */
  const showLoading = buttonToReplace => {
    let popup = getPopup();
    if (!popup) {
      new Swal();
    }
    popup = getPopup();
    if (!popup) {
      return;
    }
    const loader = getLoader();
    if (isToast()) {
      hide(getIcon());
    } else {
      replaceButton(popup, buttonToReplace);
    }
    show(loader);
    popup.setAttribute('data-loading', 'true');
    popup.setAttribute('aria-busy', 'true');
    popup.focus();
  };

  /**
   * @param {HTMLElement} popup
   * @param {HTMLButtonElement | null} [buttonToReplace]
   */
  const replaceButton = (popup, buttonToReplace) => {
    const actions = getActions();
    const loader = getLoader();
    if (!actions || !loader) {
      return;
    }
    if (!buttonToReplace && isVisible$1(getConfirmButton())) {
      buttonToReplace = getConfirmButton();
    }
    show(actions);
    if (buttonToReplace) {
      hide(buttonToReplace);
      loader.setAttribute('data-button-to-replace', buttonToReplace.className);
      actions.insertBefore(loader, buttonToReplace);
    }
    addClass([popup, actions], swalClasses.loading);
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const handleInputOptionsAndValue = (instance, params) => {
    if (params.input === 'select' || params.input === 'radio') {
      handleInputOptions(instance, params);
    } else if (['text', 'email', 'number', 'tel', 'textarea'].some(i => i === params.input) && (hasToPromiseFn(params.inputValue) || isPromise(params.inputValue))) {
      showLoading(getConfirmButton());
      handleInputValue(instance, params);
    }
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} innerParams
   * @returns {SweetAlertInputValue}
   */
  const getInputValue = (instance, innerParams) => {
    const input = instance.getInput();
    if (!input) {
      return null;
    }
    switch (innerParams.input) {
      case 'checkbox':
        return getCheckboxValue(input);
      case 'radio':
        return getRadioValue(input);
      case 'file':
        return getFileValue(input);
      default:
        return innerParams.inputAutoTrim ? input.value.trim() : input.value;
    }
  };

  /**
   * @param {HTMLInputElement} input
   * @returns {number}
   */
  const getCheckboxValue = input => input.checked ? 1 : 0;

  /**
   * @param {HTMLInputElement} input
   * @returns {string | null}
   */
  const getRadioValue = input => input.checked ? input.value : null;

  /**
   * @param {HTMLInputElement} input
   * @returns {FileList | File | null}
   */
  const getFileValue = input => input.files && input.files.length ? input.getAttribute('multiple') !== null ? input.files : input.files[0] : null;

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const handleInputOptions = (instance, params) => {
    const popup = getPopup();
    if (!popup) {
      return;
    }
    /**
     * @param {*} inputOptions
     */
    const processInputOptions = inputOptions => {
      if (params.input === 'select') {
        populateSelectOptions(popup, formatInputOptions(inputOptions), params);
      } else if (params.input === 'radio') {
        populateRadioOptions(popup, formatInputOptions(inputOptions), params);
      }
    };
    if (hasToPromiseFn(params.inputOptions) || isPromise(params.inputOptions)) {
      showLoading(getConfirmButton());
      asPromise(params.inputOptions).then(inputOptions => {
        instance.hideLoading();
        processInputOptions(inputOptions);
      });
    } else if (typeof params.inputOptions === 'object') {
      processInputOptions(params.inputOptions);
    } else {
      error(`Unexpected type of inputOptions! Expected object, Map or Promise, got ${typeof params.inputOptions}`);
    }
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertOptions} params
   */
  const handleInputValue = (instance, params) => {
    const input = instance.getInput();
    if (!input) {
      return;
    }
    hide(input);
    asPromise(params.inputValue).then(inputValue => {
      input.value = params.input === 'number' ? `${parseFloat(inputValue) || 0}` : `${inputValue}`;
      show(input);
      input.focus();
      instance.hideLoading();
    }).catch(err => {
      error(`Error in inputValue promise: ${err}`);
      input.value = '';
      show(input);
      input.focus();
      instance.hideLoading();
    });
  };

  /**
   * @param {HTMLElement} popup
   * @param {InputOptionFlattened[]} inputOptions
   * @param {SweetAlertOptions} params
   */
  function populateSelectOptions(popup, inputOptions, params) {
    const select = getDirectChildByClass(popup, swalClasses.select);
    if (!select) {
      return;
    }
    /**
     * @param {HTMLElement} parent
     * @param {string} optionLabel
     * @param {string} optionValue
     */
    const renderOption = (parent, optionLabel, optionValue) => {
      const option = document.createElement('option');
      option.value = optionValue;
      setInnerHtml(option, optionLabel);
      option.selected = isSelected(optionValue, params.inputValue);
      parent.appendChild(option);
    };
    inputOptions.forEach(inputOption => {
      const optionValue = inputOption[0];
      const optionLabel = inputOption[1];
      // <optgroup> spec:
      // https://www.w3.org/TR/html401/interact/forms.html#h-17.6
      // "...all OPTGROUP elements must be specified directly within a SELECT element (i.e., groups may not be nested)..."
      // check whether this is a <optgroup>
      if (Array.isArray(optionLabel)) {
        // if it is an array, then it is an <optgroup>
        const optgroup = document.createElement('optgroup');
        optgroup.label = optionValue;
        optgroup.disabled = false; // not configurable for now
        select.appendChild(optgroup);
        optionLabel.forEach(o => renderOption(optgroup, o[1], o[0]));
      } else {
        // case of <option>
        renderOption(select, optionLabel, optionValue);
      }
    });
    select.focus();
  }

  /**
   * @param {HTMLElement} popup
   * @param {InputOptionFlattened[]} inputOptions
   * @param {SweetAlertOptions} params
   */
  function populateRadioOptions(popup, inputOptions, params) {
    const radio = getDirectChildByClass(popup, swalClasses.radio);
    if (!radio) {
      return;
    }
    inputOptions.forEach(inputOption => {
      const radioValue = inputOption[0];
      const radioLabel = inputOption[1];
      const radioInput = document.createElement('input');
      const radioLabelElement = document.createElement('label');
      radioInput.type = 'radio';
      radioInput.name = swalClasses.radio;
      radioInput.value = radioValue;
      if (isSelected(radioValue, params.inputValue)) {
        radioInput.checked = true;
      }
      const label = document.createElement('span');
      setInnerHtml(label, radioLabel);
      label.className = swalClasses.label;
      radioLabelElement.appendChild(radioInput);
      radioLabelElement.appendChild(label);
      radio.appendChild(radioLabelElement);
    });
    const radios = radio.querySelectorAll('input');
    if (radios.length) {
      radios[0].focus();
    }
  }

  /**
   * Converts `inputOptions` into an array of `[value, label]`s
   *
   * @param {*} inputOptions
   * @typedef {string[]} InputOptionFlattened
   * @returns {InputOptionFlattened[]}
   */
  const formatInputOptions = inputOptions => {
    /** @type {InputOptionFlattened[]} */
    const result = [];
    if (inputOptions instanceof Map) {
      inputOptions.forEach((value, key) => {
        let valueFormatted = value;
        if (typeof valueFormatted === 'object') {
          // case of <optgroup>
          valueFormatted = formatInputOptions(valueFormatted);
        }
        result.push([key, valueFormatted]);
      });
    } else {
      Object.keys(inputOptions).forEach(key => {
        let valueFormatted = inputOptions[key];
        if (typeof valueFormatted === 'object') {
          // case of <optgroup>
          valueFormatted = formatInputOptions(valueFormatted);
        }
        result.push([key, valueFormatted]);
      });
    }
    return result;
  };

  /**
   * @param {string} optionValue
   * @param {SweetAlertInputValue} inputValue
   * @returns {boolean}
   */
  const isSelected = (optionValue, inputValue) => {
    return Boolean(inputValue) && inputValue !== null && inputValue !== undefined && inputValue.toString() === optionValue.toString();
  };

  /**
   * @param {SweetAlert} instance
   */
  const handleConfirmButtonClick = instance => {
    const innerParams = privateProps.innerParams.get(instance);
    instance.disableButtons();
    if (innerParams.input) {
      handleConfirmOrDenyWithInput(instance, 'confirm');
    } else {
      confirm(instance, true);
    }
  };

  /**
   * @param {SweetAlert} instance
   */
  const handleDenyButtonClick = instance => {
    const innerParams = privateProps.innerParams.get(instance);
    instance.disableButtons();
    if (innerParams.returnInputValueOnDeny) {
      handleConfirmOrDenyWithInput(instance, 'deny');
    } else {
      deny(instance, false);
    }
  };

  /**
   * @param {SweetAlert} instance
   * @param {(dismiss: DismissReason) => void} dismissWith
   */
  const handleCancelButtonClick = (instance, dismissWith) => {
    instance.disableButtons();
    dismissWith(DismissReason.cancel);
  };

  /**
   * @param {SweetAlert} instance
   * @param {'confirm' | 'deny'} type
   */
  const handleConfirmOrDenyWithInput = (instance, type) => {
    const innerParams = privateProps.innerParams.get(instance);
    if (!innerParams.input) {
      error(`The "input" parameter is needed to be set when using returnInputValueOn${capitalizeFirstLetter(type)}`);
      return;
    }
    const input = instance.getInput();
    const inputValue = getInputValue(instance, innerParams);
    if (innerParams.inputValidator) {
      handleInputValidator(instance, inputValue, type);
    } else if (input && !input.checkValidity()) {
      instance.enableButtons();
      instance.showValidationMessage(innerParams.validationMessage || input.validationMessage);
    } else if (type === 'deny') {
      deny(instance, inputValue);
    } else {
      confirm(instance, inputValue);
    }
  };

  /**
   * @param {SweetAlert} instance
   * @param {SweetAlertInputValue} inputValue
   * @param {'confirm' | 'deny'} type
   */
  const handleInputValidator = (instance, inputValue, type) => {
    const innerParams = privateProps.innerParams.get(instance);
    instance.disableInput();
    const validationPromise = Promise.resolve().then(() => asPromise(innerParams.inputValidator(inputValue, innerParams.validationMessage)));
    validationPromise.then(validationMessage => {
      instance.enableButtons();
      instance.enableInput();
      if (validationMessage) {
        instance.showValidationMessage(validationMessage);
      } else if (type === 'deny') {
        deny(instance, inputValue);
      } else {
        confirm(instance, inputValue);
      }
    });
  };

  /**
   * @param {SweetAlert} instance
   * @param {*} value
   */
  const deny = (instance, value) => {
    const innerParams = privateProps.innerParams.get(instance);
    if (innerParams.showLoaderOnDeny) {
      showLoading(getDenyButton());
    }
    if (innerParams.preDeny) {
      instance.isAwaitingPromise = true; // Flagging the instance as awaiting a promise so it's own promise's reject/resolve methods doesn't get destroyed until the result from this preDeny's promise is received
      const preDenyPromise = Promise.resolve().then(() => asPromise(innerParams.preDeny(value, innerParams.validationMessage)));
      preDenyPromise.then(preDenyValue => {
        if (preDenyValue === false) {
          instance.hideLoading();
          handleAwaitingPromise(instance);
        } else {
          instance.close(/** @type SweetAlertResult */{
            isDenied: true,
            value: typeof preDenyValue === 'undefined' ? value : preDenyValue
          });
        }
      }).catch(error => rejectWith(instance, error));
    } else {
      instance.close(/** @type SweetAlertResult */{
        isDenied: true,
        value
      });
    }
  };

  /**
   * @param {SweetAlert} instance
   * @param {*} value
   */
  const succeedWith = (instance, value) => {
    instance.close(/** @type SweetAlertResult */{
      isConfirmed: true,
      value
    });
  };

  /**
   *
   * @param {SweetAlert} instance
   * @param {string} error
   */
  const rejectWith = (instance, error) => {
    instance.rejectPromise(error);
  };

  /**
   *
   * @param {SweetAlert} instance
   * @param {*} value
   */
  const confirm = (instance, value) => {
    const innerParams = privateProps.innerParams.get(instance);
    if (innerParams.showLoaderOnConfirm) {
      showLoading();
    }
    if (innerParams.preConfirm) {
      instance.resetValidationMessage();
      instance.isAwaitingPromise = true; // Flagging the instance as awaiting a promise so it's own promise's reject/resolve methods doesn't get destroyed until the result from this preConfirm's promise is received
      const preConfirmPromise = Promise.resolve().then(() => asPromise(innerParams.preConfirm(value, innerParams.validationMessage)));
      preConfirmPromise.then(preConfirmValue => {
        if (isVisible$1(getValidationMessage()) || preConfirmValue === false) {
          instance.hideLoading();
          handleAwaitingPromise(instance);
        } else {
          succeedWith(instance, typeof preConfirmValue === 'undefined' ? value : preConfirmValue);
        }
      }).catch(error => rejectWith(instance, error));
    } else {
      succeedWith(instance, value);
    }
  };

  /**
   * Hides loader and shows back the button which was hidden by .showLoading()
   * @this {SweetAlert}
   */
  function hideLoading() {
    // do nothing if popup is closed
    const innerParams = privateProps.innerParams.get(this);
    if (!innerParams) {
      return;
    }
    const domCache = privateProps.domCache.get(this);
    hide(domCache.loader);
    if (isToast()) {
      if (innerParams.icon) {
        show(getIcon());
      }
    } else {
      showRelatedButton(domCache);
    }
    removeClass([domCache.popup, domCache.actions], swalClasses.loading);
    domCache.popup.removeAttribute('aria-busy');
    domCache.popup.removeAttribute('data-loading');
    domCache.confirmButton.disabled = false;
    domCache.denyButton.disabled = false;
    domCache.cancelButton.disabled = false;
  }

  /**
   * @param {DomCache} domCache
   */
  const showRelatedButton = domCache => {
    const dataButtonToReplace = domCache.loader.getAttribute('data-button-to-replace');
    const buttonToReplace = dataButtonToReplace ? domCache.popup.getElementsByClassName(dataButtonToReplace) : [];
    if (buttonToReplace.length) {
      show(/** @type {HTMLElement} */buttonToReplace[0], 'inline-block');
    } else if (allButtonsAreHidden()) {
      hide(domCache.actions);
    }
  };

  /**
   * Gets the input DOM node, this method works with input parameter.
   *
   * @returns {HTMLInputElement | null}
   * @this {SweetAlert}
   */
  function getInput() {
    const innerParams = privateProps.innerParams.get(this);
    const domCache = privateProps.domCache.get(this);
    if (!domCache) {
      return null;
    }
    return getInput$1(domCache.popup, innerParams.input);
  }

  /**
   * @param {SweetAlert} instance
   * @param {string[]} buttons
   * @param {boolean} disabled
   */
  function setButtonsDisabled(instance, buttons, disabled) {
    const domCache = privateProps.domCache.get(instance);
    buttons.forEach(button => {
      domCache[button].disabled = disabled;
    });
  }

  /**
   * @param {HTMLInputElement | null} input
   * @param {boolean} disabled
   */
  function setInputDisabled(input, disabled) {
    const popup = getPopup();
    if (!popup || !input) {
      return;
    }
    if (input.type === 'radio') {
      /** @type {NodeListOf<HTMLInputElement>} */
      const radios = popup.querySelectorAll(`[name="${swalClasses.radio}"]`);
      for (let i = 0; i < radios.length; i++) {
        radios[i].disabled = disabled;
      }
    } else {
      input.disabled = disabled;
    }
  }

  /**
   * Enable all the buttons
   * @this {SweetAlert}
   */
  function enableButtons() {
    setButtonsDisabled(this, ['confirmButton', 'denyButton', 'cancelButton'], false);
  }

  /**
   * Disable all the buttons
   * @this {SweetAlert}
   */
  function disableButtons() {
    setButtonsDisabled(this, ['confirmButton', 'denyButton', 'cancelButton'], true);
  }

  /**
   * Enable the input field
   * @this {SweetAlert}
   */
  function enableInput() {
    setInputDisabled(this.getInput(), false);
  }

  /**
   * Disable the input field
   * @this {SweetAlert}
   */
  function disableInput() {
    setInputDisabled(this.getInput(), true);
  }

  /**
   * Show block with validation message
   *
   * @param {string} error
   * @this {SweetAlert}
   */
  function showValidationMessage(error) {
    const domCache = privateProps.domCache.get(this);
    const params = privateProps.innerParams.get(this);
    setInnerHtml(domCache.validationMessage, error);
    domCache.validationMessage.className = swalClasses['validation-message'];
    if (params.customClass && params.customClass.validationMessage) {
      addClass(domCache.validationMessage, params.customClass.validationMessage);
    }
    show(domCache.validationMessage);
    const input = this.getInput();
    if (input) {
      input.setAttribute('aria-invalid', 'true');
      input.setAttribute('aria-describedby', swalClasses['validation-message']);
      focusInput(input);
      addClass(input, swalClasses.inputerror);
    }
  }

  /**
   * Hide block with validation message
   *
   * @this {SweetAlert}
   */
  function resetValidationMessage() {
    const domCache = privateProps.domCache.get(this);
    if (domCache.validationMessage) {
      hide(domCache.validationMessage);
    }
    const input = this.getInput();
    if (input) {
      input.removeAttribute('aria-invalid');
      input.removeAttribute('aria-describedby');
      removeClass(input, swalClasses.inputerror);
    }
  }

  const defaultParams = {
    title: '',
    titleText: '',
    text: '',
    html: '',
    footer: '',
    icon: undefined,
    iconColor: undefined,
    iconHtml: undefined,
    template: undefined,
    toast: false,
    draggable: false,
    animation: true,
    theme: 'light',
    showClass: {
      popup: 'swal2-show',
      backdrop: 'swal2-backdrop-show',
      icon: 'swal2-icon-show'
    },
    hideClass: {
      popup: 'swal2-hide',
      backdrop: 'swal2-backdrop-hide',
      icon: 'swal2-icon-hide'
    },
    customClass: {},
    target: 'body',
    color: undefined,
    backdrop: true,
    heightAuto: true,
    allowOutsideClick: true,
    allowEscapeKey: true,
    allowEnterKey: true,
    stopKeydownPropagation: true,
    keydownListenerCapture: false,
    showConfirmButton: true,
    showDenyButton: false,
    showCancelButton: false,
    preConfirm: undefined,
    preDeny: undefined,
    confirmButtonText: 'OK',
    confirmButtonAriaLabel: '',
    confirmButtonColor: undefined,
    denyButtonText: 'No',
    denyButtonAriaLabel: '',
    denyButtonColor: undefined,
    cancelButtonText: 'Cancel',
    cancelButtonAriaLabel: '',
    cancelButtonColor: undefined,
    buttonsStyling: true,
    reverseButtons: false,
    focusConfirm: true,
    focusDeny: false,
    focusCancel: false,
    returnFocus: true,
    showCloseButton: false,
    closeButtonHtml: '&times;',
    closeButtonAriaLabel: 'Close this dialog',
    loaderHtml: '',
    showLoaderOnConfirm: false,
    showLoaderOnDeny: false,
    imageUrl: undefined,
    imageWidth: undefined,
    imageHeight: undefined,
    imageAlt: '',
    timer: undefined,
    timerProgressBar: false,
    width: undefined,
    padding: undefined,
    background: undefined,
    input: undefined,
    inputPlaceholder: '',
    inputLabel: '',
    inputValue: '',
    inputOptions: {},
    inputAutoFocus: true,
    inputAutoTrim: true,
    inputAttributes: {},
    inputValidator: undefined,
    returnInputValueOnDeny: false,
    validationMessage: undefined,
    grow: false,
    position: 'center',
    progressSteps: [],
    currentProgressStep: undefined,
    progressStepsDistance: undefined,
    willOpen: undefined,
    didOpen: undefined,
    didRender: undefined,
    willClose: undefined,
    didClose: undefined,
    didDestroy: undefined,
    scrollbarPadding: true,
    topLayer: false
  };
  const updatableParams = ['allowEscapeKey', 'allowOutsideClick', 'background', 'buttonsStyling', 'cancelButtonAriaLabel', 'cancelButtonColor', 'cancelButtonText', 'closeButtonAriaLabel', 'closeButtonHtml', 'color', 'confirmButtonAriaLabel', 'confirmButtonColor', 'confirmButtonText', 'currentProgressStep', 'customClass', 'denyButtonAriaLabel', 'denyButtonColor', 'denyButtonText', 'didClose', 'didDestroy', 'draggable', 'footer', 'hideClass', 'html', 'icon', 'iconColor', 'iconHtml', 'imageAlt', 'imageHeight', 'imageUrl', 'imageWidth', 'preConfirm', 'preDeny', 'progressSteps', 'returnFocus', 'reverseButtons', 'showCancelButton', 'showCloseButton', 'showConfirmButton', 'showDenyButton', 'text', 'title', 'titleText', 'theme', 'willClose'];

  /** @type {Record<string, string | undefined>} */
  const deprecatedParams = {
    allowEnterKey: undefined
  };
  const toastIncompatibleParams = ['allowOutsideClick', 'allowEnterKey', 'backdrop', 'draggable', 'focusConfirm', 'focusDeny', 'focusCancel', 'returnFocus', 'heightAuto', 'keydownListenerCapture'];

  /**
   * Is valid parameter
   *
   * @param {string} paramName
   * @returns {boolean}
   */
  const isValidParameter = paramName => {
    return Object.prototype.hasOwnProperty.call(defaultParams, paramName);
  };

  /**
   * Is valid parameter for Swal.update() method
   *
   * @param {string} paramName
   * @returns {boolean}
   */
  const isUpdatableParameter = paramName => {
    return updatableParams.indexOf(paramName) !== -1;
  };

  /**
   * Is deprecated parameter
   *
   * @param {string} paramName
   * @returns {string | undefined}
   */
  const isDeprecatedParameter = paramName => {
    return deprecatedParams[paramName];
  };

  /**
   * @param {string} param
   */
  const checkIfParamIsValid = param => {
    if (!isValidParameter(param)) {
      warn(`Unknown parameter "${param}"`);
    }
  };

  /**
   * @param {string} param
   */
  const checkIfToastParamIsValid = param => {
    if (toastIncompatibleParams.includes(param)) {
      warn(`The parameter "${param}" is incompatible with toasts`);
    }
  };

  /**
   * @param {string} param
   */
  const checkIfParamIsDeprecated = param => {
    const isDeprecated = isDeprecatedParameter(param);
    if (isDeprecated) {
      warnAboutDeprecation(param, isDeprecated);
    }
  };

  /**
   * Show relevant warnings for given params
   *
   * @param {SweetAlertOptions} params
   */
  const showWarningsForParams = params => {
    if (params.backdrop === false && params.allowOutsideClick) {
      warn('"allowOutsideClick" parameter requires `backdrop` parameter to be set to `true`');
    }
    if (params.theme && !['light', 'dark', 'auto', 'minimal', 'borderless', 'bootstrap-4', 'bootstrap-4-light', 'bootstrap-4-dark', 'bootstrap-5', 'bootstrap-5-light', 'bootstrap-5-dark', 'material-ui', 'material-ui-light', 'material-ui-dark', 'embed-iframe', 'bulma', 'bulma-light', 'bulma-dark'].includes(params.theme)) {
      warn(`Invalid theme "${params.theme}"`);
    }
    for (const param in params) {
      checkIfParamIsValid(param);
      if (params.toast) {
        checkIfToastParamIsValid(param);
      }
      checkIfParamIsDeprecated(param);
    }
  };

  /**
   * Updates popup parameters.
   *
   * @this {any}
   * @param {SweetAlertOptions} params
   */
  function update(params) {
    const container = getContainer();
    const popup = getPopup();
    const innerParams = privateProps.innerParams.get(this);
    if (!popup || hasClass(popup, innerParams.hideClass.popup)) {
      warn(`You're trying to update the closed or closing popup, that won't work. Use the update() method in preConfirm parameter or show a new popup.`);
      return;
    }
    const validUpdatableParams = filterValidParams(params);
    const updatedParams = Object.assign({}, innerParams, validUpdatableParams);
    showWarningsForParams(updatedParams);
    if (container) {
      container.dataset['swal2Theme'] = updatedParams.theme;
    }
    render(this, updatedParams);
    privateProps.innerParams.set(this, updatedParams);
    Object.defineProperties(this, {
      params: {
        value: Object.assign({}, this.params, params),
        writable: false,
        enumerable: true
      }
    });
  }

  /**
   * @param {SweetAlertOptions} params
   * @returns {SweetAlertOptions}
   */
  const filterValidParams = params => {
    /** @type {Record<string, any>} */
    const validUpdatableParams = {};
    Object.keys(params).forEach(param => {
      if (isUpdatableParameter(param)) {
        const typedParams = /** @type {Record<string, any>} */params;
        validUpdatableParams[param] = typedParams[param];
      } else {
        warn(`Invalid parameter to update: ${param}`);
      }
    });
    return validUpdatableParams;
  };

  /**
   * Dispose the current SweetAlert2 instance
   * @this {SweetAlert}
   */
  function _destroy() {
    var _globalState$eventEmi;
    const domCache = privateProps.domCache.get(this);
    const innerParams = privateProps.innerParams.get(this);
    if (!innerParams) {
      disposeWeakMaps(this); // The WeakMaps might have been partly destroyed, we must recall it to dispose any remaining WeakMaps #2335
      return; // This instance has already been destroyed
    }

    // Check if there is another Swal closing
    if (domCache.popup && globalState.swalCloseEventFinishedCallback) {
      globalState.swalCloseEventFinishedCallback();
      delete globalState.swalCloseEventFinishedCallback;
    }
    if (typeof innerParams.didDestroy === 'function') {
      innerParams.didDestroy();
    }
    (_globalState$eventEmi = globalState.eventEmitter) === null || _globalState$eventEmi === void 0 || _globalState$eventEmi.emit('didDestroy');
    disposeSwal(this);
  }

  /**
   * @param {SweetAlert} instance
   */
  const disposeSwal = instance => {
    disposeWeakMaps(instance);
    // Unset this.params so GC will dispose it (#1569)
    // @ts-ignore
    delete instance.params;
    // Unset globalState props so GC will dispose globalState (#1569)
    delete globalState.keydownHandler;
    delete globalState.keydownTarget;
    // Unset currentInstance
    delete globalState.currentInstance;
  };

  /**
   * @param {SweetAlert} instance
   */
  const disposeWeakMaps = instance => {
    // If the current instance is awaiting a promise result, we keep the privateMethods to call them once the promise result is retrieved #2335
    if (instance.isAwaitingPromise) {
      unsetWeakMaps(privateProps, instance);
      instance.isAwaitingPromise = true;
    } else {
      unsetWeakMaps(privateMethods, instance);
      unsetWeakMaps(privateProps, instance);

      // @ts-ignore
      delete instance.isAwaitingPromise;
      // Unset instance methods
      // @ts-ignore
      delete instance.disableButtons;
      // @ts-ignore
      delete instance.enableButtons;
      // @ts-ignore
      delete instance.getInput;
      // @ts-ignore
      delete instance.disableInput;
      // @ts-ignore
      delete instance.enableInput;
      // @ts-ignore
      delete instance.hideLoading;
      // @ts-ignore
      delete instance.disableLoading;
      // @ts-ignore
      delete instance.showValidationMessage;
      // @ts-ignore
      delete instance.resetValidationMessage;
      // @ts-ignore
      delete instance.close;
      // @ts-ignore
      delete instance.closePopup;
      // @ts-ignore
      delete instance.closeModal;
      // @ts-ignore
      delete instance.closeToast;
      // @ts-ignore
      delete instance.rejectPromise;
      // @ts-ignore
      delete instance.update;
      // @ts-ignore
      delete instance._destroy;
    }
  };

  /**
   * @param {Record<string, WeakMap<any, any>>} obj
   * @param {SweetAlert} instance
   */
  const unsetWeakMaps = (obj, instance) => {
    for (const i in obj) {
      obj[i].delete(instance);
    }
  };

  var instanceMethods = /*#__PURE__*/Object.freeze({
    __proto__: null,
    _destroy: _destroy,
    close: close,
    closeModal: close,
    closePopup: close,
    closeToast: close,
    disableButtons: disableButtons,
    disableInput: disableInput,
    disableLoading: hideLoading,
    enableButtons: enableButtons,
    enableInput: enableInput,
    getInput: getInput,
    handleAwaitingPromise: handleAwaitingPromise,
    hideLoading: hideLoading,
    rejectPromise: rejectPromise,
    resetValidationMessage: resetValidationMessage,
    showValidationMessage: showValidationMessage,
    update: update
  });

  /**
   * @param {SweetAlertOptions} innerParams
   * @param {DomCache} domCache
   * @param {(dismiss: DismissReason) => void} dismissWith
   */
  const handlePopupClick = (innerParams, domCache, dismissWith) => {
    if (innerParams.toast) {
      handleToastClick(innerParams, domCache, dismissWith);
    } else {
      // Ignore click events that had mousedown on the popup but mouseup on the container
      // This can happen when the user drags a slider
      handleModalMousedown(domCache);

      // Ignore click events that had mousedown on the container but mouseup on the popup
      handleContainerMousedown(domCache);
      handleModalClick(innerParams, domCache, dismissWith);
    }
  };

  /**
   * @param {SweetAlertOptions} innerParams
   * @param {DomCache} domCache
   * @param {(dismiss: DismissReason) => void} dismissWith
   */
  const handleToastClick = (innerParams, domCache, dismissWith) => {
    // Closing toast by internal click
    domCache.popup.onclick = () => {
      if (innerParams && (isAnyButtonShown(innerParams) || innerParams.timer || innerParams.input)) {
        return;
      }
      dismissWith(DismissReason.close);
    };
  };

  /**
   * @param {SweetAlertOptions} innerParams
   * @returns {boolean}
   */
  const isAnyButtonShown = innerParams => {
    return Boolean(innerParams.showConfirmButton || innerParams.showDenyButton || innerParams.showCancelButton || innerParams.showCloseButton);
  };
  let ignoreOutsideClick = false;

  /**
   * @param {DomCache} domCache
   */
  const handleModalMousedown = domCache => {
    domCache.popup.onmousedown = () => {
      domCache.container.onmouseup = function (e) {
        domCache.container.onmouseup = () => {};
        // We only check if the mouseup target is the container because usually it doesn't
        // have any other direct children aside of the popup
        if (e.target === domCache.container) {
          ignoreOutsideClick = true;
        }
      };
    };
  };

  /**
   * @param {DomCache} domCache
   */
  const handleContainerMousedown = domCache => {
    domCache.container.onmousedown = e => {
      // prevent the modal text from being selected on double click on the container (allowOutsideClick: false)
      if (e.target === domCache.container) {
        e.preventDefault();
      }
      domCache.popup.onmouseup = function (e) {
        domCache.popup.onmouseup = () => {};
        // We also need to check if the mouseup target is a child of the popup
        if (e.target === domCache.popup || e.target instanceof HTMLElement && domCache.popup.contains(e.target)) {
          ignoreOutsideClick = true;
        }
      };
    };
  };

  /**
   * @param {SweetAlertOptions} innerParams
   * @param {DomCache} domCache
   * @param {(dismiss: DismissReason) => void} dismissWith
   */
  const handleModalClick = (innerParams, domCache, dismissWith) => {
    domCache.container.onclick = e => {
      if (ignoreOutsideClick) {
        ignoreOutsideClick = false;
        return;
      }
      if (e.target === domCache.container && callIfFunction(innerParams.allowOutsideClick)) {
        dismissWith(DismissReason.backdrop);
      }
    };
  };

  /**
   * @param {any} elem
   * @returns {boolean}
   */
  const isJqueryElement = elem => typeof elem === 'object' && elem.jquery;

  /**
   * @param {any} elem
   * @returns {boolean}
   */
  const isElement = elem => elem instanceof Element || isJqueryElement(elem);

  /**
   * @param {any[]} args
   * @returns {SweetAlertOptions}
   */
  const argsToParams = args => {
    /** @type {Record<string, any>} */
    const params = {};
    if (typeof args[0] === 'object' && !isElement(args[0])) {
      Object.assign(params, args[0]);
    } else {
      ['title', 'html', 'icon'].forEach((name, index) => {
        const arg = args[index];
        if (typeof arg === 'string' || isElement(arg)) {
          params[name] = arg;
        } else if (arg !== undefined) {
          error(`Unexpected type of ${name}! Expected "string" or "Element", got ${typeof arg}`);
        }
      });
    }
    return params;
  };

  /**
   * Main method to create a new SweetAlert2 popup
   *
   * @this {new (...args: any[]) => any}
   * @param  {...SweetAlertOptions} args
   * @returns {Promise<SweetAlertResult>}
   */
  function fire(...args) {
    return new this(...args);
  }

  /**
   * Returns an extended version of `Swal` containing `params` as defaults.
   * Useful for reusing Swal configuration.
   *
   * For example:
   *
   * Before:
   * const textPromptOptions = { input: 'text', showCancelButton: true }
   * const {value: firstName} = await Swal.fire({ ...textPromptOptions, title: 'What is your first name?' })
   * const {value: lastName} = await Swal.fire({ ...textPromptOptions, title: 'What is your last name?' })
   *
   * After:
   * const TextPrompt = Swal.mixin({ input: 'text', showCancelButton: true })
   * const {value: firstName} = await TextPrompt('What is your first name?')
   * const {value: lastName} = await TextPrompt('What is your last name?')
   *
   * @param {SweetAlertOptions} mixinParams
   * @returns {SweetAlert}
   * @this {typeof import('../SweetAlert.js').SweetAlert}
   */
  function mixin(mixinParams) {
    // @ts-ignore: 'this' refers to the SweetAlert constructor
    class MixinSwal extends this {
      /**
       * @param {any} params
       * @param {any} priorityMixinParams
       */
      _main(params, priorityMixinParams) {
        return super._main(params, Object.assign({}, mixinParams, priorityMixinParams));
      }
    }
    // @ts-ignore
    return MixinSwal;
  }

  /**
   * If `timer` parameter is set, returns number of milliseconds of timer remained.
   * Otherwise, returns undefined.
   *
   * @returns {number | undefined}
   */
  const getTimerLeft = () => {
    return globalState.timeout && globalState.timeout.getTimerLeft();
  };

  /**
   * Stop timer. Returns number of milliseconds of timer remained.
   * If `timer` parameter isn't set, returns undefined.
   *
   * @returns {number | undefined}
   */
  const stopTimer = () => {
    if (globalState.timeout) {
      stopTimerProgressBar();
      return globalState.timeout.stop();
    }
  };

  /**
   * Resume timer. Returns number of milliseconds of timer remained.
   * If `timer` parameter isn't set, returns undefined.
   *
   * @returns {number | undefined}
   */
  const resumeTimer = () => {
    if (globalState.timeout) {
      const remaining = globalState.timeout.start();
      animateTimerProgressBar(remaining);
      return remaining;
    }
  };

  /**
   * Resume timer. Returns number of milliseconds of timer remained.
   * If `timer` parameter isn't set, returns undefined.
   *
   * @returns {number | undefined}
   */
  const toggleTimer = () => {
    const timer = globalState.timeout;
    return timer && (timer.running ? stopTimer() : resumeTimer());
  };

  /**
   * Increase timer. Returns number of milliseconds of an updated timer.
   * If `timer` parameter isn't set, returns undefined.
   *
   * @param {number} ms
   * @returns {number | undefined}
   */
  const increaseTimer = ms => {
    if (globalState.timeout) {
      const remaining = globalState.timeout.increase(ms);
      animateTimerProgressBar(remaining, true);
      return remaining;
    }
  };

  /**
   * Check if timer is running. Returns true if timer is running
   * or false if timer is paused or stopped.
   * If `timer` parameter isn't set, returns undefined
   *
   * @returns {boolean}
   */
  const isTimerRunning = () => {
    return Boolean(globalState.timeout && globalState.timeout.isRunning());
  };

  let bodyClickListenerAdded = false;
  /** @type {Record<string, any>} */
  const clickHandlers = {};

  /**
   * @this {any}
   * @param {string} attr
   */
  function bindClickHandler(attr = 'data-swal-template') {
    clickHandlers[attr] = this;
    if (!bodyClickListenerAdded) {
      document.body.addEventListener('click', bodyClickListener);
      bodyClickListenerAdded = true;
    }
  }

  /**
   * @param {MouseEvent} event
   */
  const bodyClickListener = event => {
    for (let el = /** @type {any} */event.target; el && el !== document; el = el.parentNode) {
      for (const attr in clickHandlers) {
        const template = el.getAttribute && el.getAttribute(attr);
        if (template) {
          clickHandlers[attr].fire({
            template
          });
          return;
        }
      }
    }
  };

  // Source: https://gist.github.com/mudge/5830382?permalink_comment_id=2691957#gistcomment-2691957

  class EventEmitter {
    constructor() {
      /** @type {Events} */
      this.events = {};
    }

    /**
     * @param {string} eventName
     * @returns {EventHandlers}
     */
    _getHandlersByEventName(eventName) {
      if (typeof this.events[eventName] === 'undefined') {
        // not Set because we need to keep the FIFO order
        // https://github.com/sweetalert2/sweetalert2/pull/2763#discussion_r1748990334
        this.events[eventName] = [];
      }
      return this.events[eventName];
    }

    /**
     * @param {string} eventName
     * @param {EventHandler} eventHandler
     */
    on(eventName, eventHandler) {
      const currentHandlers = this._getHandlersByEventName(eventName);
      if (!currentHandlers.includes(eventHandler)) {
        currentHandlers.push(eventHandler);
      }
    }

    /**
     * @param {string} eventName
     * @param {EventHandler} eventHandler
     */
    once(eventName, eventHandler) {
      /**
       * @param {...any} args
       */
      const onceFn = (...args) => {
        this.removeListener(eventName, onceFn);
        // @ts-ignore
        eventHandler.apply(this, args);
      };
      this.on(eventName, onceFn);
    }

    /**
     * @param {string} eventName
     * @param {...any} args
     */
    emit(eventName, ...args) {
      this._getHandlersByEventName(eventName).forEach(
      /**
       * @param {EventHandler} eventHandler
       */
      eventHandler => {
        try {
          // @ts-ignore
          eventHandler.apply(this, args);
        } catch (error) {
          console.error(error);
        }
      });
    }

    /**
     * @param {string} eventName
     * @param {EventHandler} eventHandler
     */
    removeListener(eventName, eventHandler) {
      const currentHandlers = this._getHandlersByEventName(eventName);
      const index = currentHandlers.indexOf(eventHandler);
      if (index > -1) {
        currentHandlers.splice(index, 1);
      }
    }

    /**
     * @param {string} eventName
     */
    removeAllListeners(eventName) {
      if (this.events[eventName] !== undefined) {
        // https://github.com/sweetalert2/sweetalert2/pull/2763#discussion_r1749239222
        this.events[eventName].length = 0;
      }
    }
    reset() {
      this.events = {};
    }
  }

  globalState.eventEmitter = new EventEmitter();

  /**
   * @param {string} eventName
   * @param {EventHandler} eventHandler
   */
  const on = (eventName, eventHandler) => {
    if (globalState.eventEmitter) {
      globalState.eventEmitter.on(eventName, eventHandler);
    }
  };

  /**
   * @param {string} eventName
   * @param {EventHandler} eventHandler
   */
  const once = (eventName, eventHandler) => {
    if (globalState.eventEmitter) {
      globalState.eventEmitter.once(eventName, eventHandler);
    }
  };

  /**
   * @param {string} [eventName]
   * @param {EventHandler} [eventHandler]
   */
  const off = (eventName, eventHandler) => {
    if (!globalState.eventEmitter) {
      return;
    }

    // Remove all handlers for all events
    if (!eventName) {
      globalState.eventEmitter.reset();
      return;
    }
    if (eventHandler) {
      // Remove a specific handler
      globalState.eventEmitter.removeListener(eventName, eventHandler);
    } else {
      // Remove all handlers for a specific event
      globalState.eventEmitter.removeAllListeners(eventName);
    }
  };

  var staticMethods = /*#__PURE__*/Object.freeze({
    __proto__: null,
    argsToParams: argsToParams,
    bindClickHandler: bindClickHandler,
    clickCancel: clickCancel,
    clickConfirm: clickConfirm,
    clickDeny: clickDeny,
    enableLoading: showLoading,
    fire: fire,
    getActions: getActions,
    getCancelButton: getCancelButton,
    getCloseButton: getCloseButton,
    getConfirmButton: getConfirmButton,
    getContainer: getContainer,
    getDenyButton: getDenyButton,
    getFocusableElements: getFocusableElements,
    getFooter: getFooter,
    getHtmlContainer: getHtmlContainer,
    getIcon: getIcon,
    getIconContent: getIconContent,
    getImage: getImage,
    getInputLabel: getInputLabel,
    getLoader: getLoader,
    getPopup: getPopup,
    getProgressSteps: getProgressSteps,
    getTimerLeft: getTimerLeft,
    getTimerProgressBar: getTimerProgressBar,
    getTitle: getTitle,
    getValidationMessage: getValidationMessage,
    increaseTimer: increaseTimer,
    isDeprecatedParameter: isDeprecatedParameter,
    isLoading: isLoading,
    isTimerRunning: isTimerRunning,
    isUpdatableParameter: isUpdatableParameter,
    isValidParameter: isValidParameter,
    isVisible: isVisible,
    mixin: mixin,
    off: off,
    on: on,
    once: once,
    resumeTimer: resumeTimer,
    showLoading: showLoading,
    stopTimer: stopTimer,
    toggleTimer: toggleTimer
  });

  class Timer {
    /**
     * @param {() => void} callback
     * @param {number} delay
     */
    constructor(callback, delay) {
      this.callback = callback;
      this.remaining = delay;
      this.running = false;
      this.start();
    }

    /**
     * @returns {number}
     */
    start() {
      if (!this.running) {
        this.running = true;
        this.started = new Date();
        this.id = setTimeout(this.callback, this.remaining);
      }
      return this.remaining;
    }

    /**
     * @returns {number}
     */
    stop() {
      if (this.started && this.running) {
        this.running = false;
        clearTimeout(this.id);
        this.remaining -= new Date().getTime() - this.started.getTime();
      }
      return this.remaining;
    }

    /**
     * @param {number} n
     * @returns {number}
     */
    increase(n) {
      const running = this.running;
      if (running) {
        this.stop();
      }
      this.remaining += n;
      if (running) {
        this.start();
      }
      return this.remaining;
    }

    /**
     * @returns {number}
     */
    getTimerLeft() {
      if (this.running) {
        this.stop();
        this.start();
      }
      return this.remaining;
    }

    /**
     * @returns {boolean}
     */
    isRunning() {
      return this.running;
    }
  }

  const swalStringParams = ['swal-title', 'swal-html', 'swal-footer'];

  /**
   * @param {SweetAlertOptions} params
   * @returns {SweetAlertOptions}
   */
  const getTemplateParams = params => {
    const template = typeof params.template === 'string' ? (/** @type {HTMLTemplateElement} */document.querySelector(params.template)) : params.template;
    if (!template) {
      return {};
    }
    /** @type {DocumentFragment} */
    const templateContent = template.content;
    showWarningsForElements(templateContent);
    const result = Object.assign(getSwalParams(templateContent), getSwalFunctionParams(templateContent), getSwalButtons(templateContent), getSwalImage(templateContent), getSwalIcon(templateContent), getSwalInput(templateContent), getSwalStringParams(templateContent, swalStringParams));
    return result;
  };

  /**
   * @param {DocumentFragment} templateContent
   * @returns {Record<string, string | boolean | number>}
   */
  const getSwalParams = templateContent => {
    /** @type {Record<string, string | boolean | number>} */
    const result = {};
    /** @type {HTMLElement[]} */
    const swalParams = Array.from(templateContent.querySelectorAll('swal-param'));
    swalParams.forEach(param => {
      showWarningsForAttributes(param, ['name', 'value']);
      const paramName = /** @type {keyof SweetAlertOptions} */param.getAttribute('name');
      const value = param.getAttribute('value');
      if (!paramName || !value) {
        return;
      }
      if (paramName in defaultParams && typeof defaultParams[(/** @type {keyof typeof defaultParams} */paramName)] === 'boolean') {
        result[paramName] = value !== 'false';
      } else if (paramName in defaultParams && typeof defaultParams[(/** @type {keyof typeof defaultParams} */paramName)] === 'object') {
        result[paramName] = JSON.parse(value);
      } else {
        result[paramName] = value;
      }
    });
    return result;
  };

  /**
   * @param {DocumentFragment} templateContent
   * @returns {Record<string, () => void>}
   */
  const getSwalFunctionParams = templateContent => {
    /** @type {Record<string, () => void>} */
    const result = {};
    /** @type {HTMLElement[]} */
    const swalFunctions = Array.from(templateContent.querySelectorAll('swal-function-param'));
    swalFunctions.forEach(param => {
      const paramName = /** @type {keyof SweetAlertOptions} */param.getAttribute('name');
      const value = param.getAttribute('value');
      if (!paramName || !value) {
        return;
      }
      result[paramName] = new Function(`return ${value}`)();
    });
    return result;
  };

  /**
   * @param {DocumentFragment} templateContent
   * @returns {Record<string, string | boolean>}
   */
  const getSwalButtons = templateContent => {
    /** @type {Record<string, string | boolean>} */
    const result = {};
    /** @type {HTMLElement[]} */
    const swalButtons = Array.from(templateContent.querySelectorAll('swal-button'));
    swalButtons.forEach(button => {
      showWarningsForAttributes(button, ['type', 'color', 'aria-label']);
      const type = button.getAttribute('type');
      if (!type || !['confirm', 'cancel', 'deny'].includes(type)) {
        return;
      }
      result[`${type}ButtonText`] = button.innerHTML;
      result[`show${capitalizeFirstLetter(type)}Button`] = true;
      if (button.hasAttribute('color')) {
        const color = button.getAttribute('color');
        if (color !== null) {
          result[`${type}ButtonColor`] = color;
        }
      }
      if (button.hasAttribute('aria-label')) {
        const ariaLabel = button.getAttribute('aria-label');
        if (ariaLabel !== null) {
          result[`${type}ButtonAriaLabel`] = ariaLabel;
        }
      }
    });
    return result;
  };

  /**
   * @param {DocumentFragment} templateContent
   * @returns {Pick<SweetAlertOptions, 'imageUrl' | 'imageWidth' | 'imageHeight' | 'imageAlt'>}
   */
  const getSwalImage = templateContent => {
    const result = {};
    /** @type {HTMLElement | null} */
    const image = templateContent.querySelector('swal-image');
    if (image) {
      showWarningsForAttributes(image, ['src', 'width', 'height', 'alt']);
      if (image.hasAttribute('src')) {
        result.imageUrl = image.getAttribute('src') || undefined;
      }
      if (image.hasAttribute('width')) {
        result.imageWidth = image.getAttribute('width') || undefined;
      }
      if (image.hasAttribute('height')) {
        result.imageHeight = image.getAttribute('height') || undefined;
      }
      if (image.hasAttribute('alt')) {
        result.imageAlt = image.getAttribute('alt') || undefined;
      }
    }
    return result;
  };

  /**
   * @param {DocumentFragment} templateContent
   * @returns {object}
   */
  const getSwalIcon = templateContent => {
    const result = {};
    /** @type {HTMLElement | null} */
    const icon = templateContent.querySelector('swal-icon');
    if (icon) {
      showWarningsForAttributes(icon, ['type', 'color']);
      if (icon.hasAttribute('type')) {
        result.icon = icon.getAttribute('type');
      }
      if (icon.hasAttribute('color')) {
        result.iconColor = icon.getAttribute('color');
      }
      result.iconHtml = icon.innerHTML;
    }
    return result;
  };

  /**
   * @param {DocumentFragment} templateContent
   * @returns {object}
   */
  const getSwalInput = templateContent => {
    /** @type {Record<string, any>} */
    const result = {};
    /** @type {HTMLElement | null} */
    const input = templateContent.querySelector('swal-input');
    if (input) {
      showWarningsForAttributes(input, ['type', 'label', 'placeholder', 'value']);
      result.input = input.getAttribute('type') || 'text';
      if (input.hasAttribute('label')) {
        result.inputLabel = input.getAttribute('label');
      }
      if (input.hasAttribute('placeholder')) {
        result.inputPlaceholder = input.getAttribute('placeholder');
      }
      if (input.hasAttribute('value')) {
        result.inputValue = input.getAttribute('value');
      }
    }
    /** @type {HTMLElement[]} */
    const inputOptions = Array.from(templateContent.querySelectorAll('swal-input-option'));
    if (inputOptions.length) {
      result.inputOptions = {};
      inputOptions.forEach(option => {
        showWarningsForAttributes(option, ['value']);
        const optionValue = option.getAttribute('value');
        if (!optionValue) {
          return;
        }
        const optionName = option.innerHTML;
        result.inputOptions[optionValue] = optionName;
      });
    }
    return result;
  };

  /**
   * @param {DocumentFragment} templateContent
   * @param {string[]} paramNames
   * @returns {Record<string, string>}
   */
  const getSwalStringParams = (templateContent, paramNames) => {
    /** @type {Record<string, string>} */
    const result = {};
    for (const i in paramNames) {
      const paramName = paramNames[i];
      /** @type {HTMLElement | null} */
      const tag = templateContent.querySelector(paramName);
      if (tag) {
        showWarningsForAttributes(tag, []);
        result[paramName.replace(/^swal-/, '')] = tag.innerHTML.trim();
      }
    }
    return result;
  };

  /**
   * @param {DocumentFragment} templateContent
   */
  const showWarningsForElements = templateContent => {
    const allowedElements = swalStringParams.concat(['swal-param', 'swal-function-param', 'swal-button', 'swal-image', 'swal-icon', 'swal-input', 'swal-input-option']);
    Array.from(templateContent.children).forEach(el => {
      const tagName = el.tagName.toLowerCase();
      if (!allowedElements.includes(tagName)) {
        warn(`Unrecognized element <${tagName}>`);
      }
    });
  };

  /**
   * @param {HTMLElement} el
   * @param {string[]} allowedAttributes
   */
  const showWarningsForAttributes = (el, allowedAttributes) => {
    Array.from(el.attributes).forEach(attribute => {
      if (allowedAttributes.indexOf(attribute.name) === -1) {
        warn([`Unrecognized attribute "${attribute.name}" on <${el.tagName.toLowerCase()}>.`, `${allowedAttributes.length ? `Allowed attributes are: ${allowedAttributes.join(', ')}` : 'To set the value, use HTML within the element.'}`]);
      }
    });
  };

  const SHOW_CLASS_TIMEOUT = 10;

  /**
   * Open popup, add necessary classes and styles, fix scrollbar
   *
   * @param {SweetAlertOptions} params
   */
  const openPopup = params => {
    var _globalState$eventEmi, _globalState$eventEmi2;
    const container = getContainer();
    const popup = getPopup();
    if (!container || !popup) {
      return;
    }
    if (typeof params.willOpen === 'function') {
      params.willOpen(popup);
    }
    (_globalState$eventEmi = globalState.eventEmitter) === null || _globalState$eventEmi === void 0 || _globalState$eventEmi.emit('willOpen', popup);
    const bodyStyles = window.getComputedStyle(document.body);
    const initialBodyOverflow = bodyStyles.overflowY;
    addClasses(container, popup, params);

    // scrolling is 'hidden' until animation is done, after that 'auto'
    setTimeout(() => {
      setScrollingVisibility(container, popup);
    }, SHOW_CLASS_TIMEOUT);
    if (isModal()) {
      // Using ternary instead of ?? operator for Webpack 4 compatibility
      fixScrollContainer(container, params.scrollbarPadding !== undefined ? params.scrollbarPadding : false, initialBodyOverflow);
      setAriaHidden();
    }
    if (!isToast() && !globalState.previousActiveElement) {
      globalState.previousActiveElement = document.activeElement;
    }
    if (typeof params.didOpen === 'function') {
      const didOpen = params.didOpen;
      setTimeout(() => didOpen(popup));
    }
    (_globalState$eventEmi2 = globalState.eventEmitter) === null || _globalState$eventEmi2 === void 0 || _globalState$eventEmi2.emit('didOpen', popup);
  };

  /**
   * @param {Event} event
   */
  const swalOpenAnimationFinished = event => {
    const popup = getPopup();
    if (!popup || event.target !== popup) {
      return;
    }
    const container = getContainer();
    if (!container) {
      return;
    }
    popup.removeEventListener('animationend', swalOpenAnimationFinished);
    popup.removeEventListener('transitionend', swalOpenAnimationFinished);
    container.style.overflowY = 'auto';

    // no-transition is added in init() in case one swal is opened right after another
    removeClass(container, swalClasses['no-transition']);
  };

  /**
   * @param {HTMLElement} container
   * @param {HTMLElement} popup
   */
  const setScrollingVisibility = (container, popup) => {
    if (hasCssAnimation(popup)) {
      container.style.overflowY = 'hidden';
      popup.addEventListener('animationend', swalOpenAnimationFinished);
      popup.addEventListener('transitionend', swalOpenAnimationFinished);
    } else {
      container.style.overflowY = 'auto';
    }
  };

  /**
   * @param {HTMLElement} container
   * @param {boolean} scrollbarPadding
   * @param {string} initialBodyOverflow
   */
  const fixScrollContainer = (container, scrollbarPadding, initialBodyOverflow) => {
    iOSfix();
    if (scrollbarPadding && initialBodyOverflow !== 'hidden') {
      replaceScrollbarWithPadding(initialBodyOverflow);
    }

    // sweetalert2/issues/1247
    setTimeout(() => {
      container.scrollTop = 0;
    });
  };

  /**
   * @param {HTMLElement} container
   * @param {HTMLElement} popup
   * @param {SweetAlertOptions} params
   */
  const addClasses = (container, popup, params) => {
    var _params$showClass;
    if ((_params$showClass = params.showClass) !== null && _params$showClass !== void 0 && _params$showClass.backdrop) {
      addClass(container, params.showClass.backdrop);
    }
    if (params.animation) {
      // this workaround with opacity is needed for https://github.com/sweetalert2/sweetalert2/issues/2059
      popup.style.setProperty('opacity', '0', 'important');
      show(popup, 'grid');
      setTimeout(() => {
        var _params$showClass2;
        // Animate popup right after showing it
        if ((_params$showClass2 = params.showClass) !== null && _params$showClass2 !== void 0 && _params$showClass2.popup) {
          addClass(popup, params.showClass.popup);
        }
        // and remove the opacity workaround
        popup.style.removeProperty('opacity');
      }, SHOW_CLASS_TIMEOUT); // 10ms in order to fix #2062
    } else {
      show(popup, 'grid');
    }
    addClass([document.documentElement, document.body], swalClasses.shown);
    if (params.heightAuto && params.backdrop && !params.toast) {
      addClass([document.documentElement, document.body], swalClasses['height-auto']);
    }
  };

  var defaultInputValidators = {
    /**
     * @param {string} string
     * @param {string} [validationMessage]
     * @returns {Promise<string | void>}
     */
    email: (string, validationMessage) => {
      return /^[a-zA-Z0-9.+_'-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9-]+$/.test(string) ? Promise.resolve() : Promise.resolve(validationMessage || 'Invalid email address');
    },
    /**
     * @param {string} string
     * @param {string} [validationMessage]
     * @returns {Promise<string | void>}
     */
    url: (string, validationMessage) => {
      // taken from https://stackoverflow.com/a/3809435 with a small change from #1306 and #2013
      return /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-z]{2,63}\b([-a-zA-Z0-9@:%_+.~#?&/=]*)$/.test(string) ? Promise.resolve() : Promise.resolve(validationMessage || 'Invalid URL');
    }
  };

  /**
   * @param {SweetAlertOptions} params
   */
  function setDefaultInputValidators(params) {
    // Use default `inputValidator` for supported input types if not provided
    if (params.inputValidator) {
      return;
    }
    if (params.input === 'email') {
      params.inputValidator = defaultInputValidators['email'];
    }
    if (params.input === 'url') {
      params.inputValidator = defaultInputValidators['url'];
    }
  }

  /**
   * @param {SweetAlertOptions} params
   */
  function validateCustomTargetElement(params) {
    // Determine if the custom target element is valid
    if (!params.target || typeof params.target === 'string' && !document.querySelector(params.target) || typeof params.target !== 'string' && !params.target.appendChild) {
      warn('Target parameter is not valid, defaulting to "body"');
      params.target = 'body';
    }
  }

  /**
   * Set type, text and actions on popup
   *
   * @param {SweetAlertOptions} params
   */
  function setParameters(params) {
    setDefaultInputValidators(params);

    // showLoaderOnConfirm && preConfirm
    if (params.showLoaderOnConfirm && !params.preConfirm) {
      warn('showLoaderOnConfirm is set to true, but preConfirm is not defined.\n' + 'showLoaderOnConfirm should be used together with preConfirm, see usage example:\n' + 'https://sweetalert2.github.io/#ajax-request');
    }
    validateCustomTargetElement(params);

    // Replace newlines with <br> in title
    if (typeof params.title === 'string') {
      params.title = params.title.split('\n').join('<br />');
    }
    init(params);
  }

  /** @type {SweetAlert} */
  let currentInstance;
  var _promise = /*#__PURE__*/new WeakMap();
  class SweetAlert {
    /**
     * @param {...(SweetAlertOptions | string)} args
     * @this {SweetAlert}
     */
    constructor(...args) {
      /**
       * @type {Promise<SweetAlertResult>}
       */
      _classPrivateFieldInitSpec(this, _promise, /** @type {Promise<SweetAlertResult>} */Promise.resolve({
        isConfirmed: false,
        isDenied: false,
        isDismissed: true
      }));
      // Prevent run in Node env
      if (typeof window === 'undefined') {
        return;
      }
      currentInstance = this;

      // @ts-ignore
      const outerParams = Object.freeze(this.constructor.argsToParams(args));

      /** @type {Readonly<SweetAlertOptions>} */
      this.params = outerParams;

      /** @type {boolean} */
      this.isAwaitingPromise = false;
      _classPrivateFieldSet2(_promise, this, this._main(currentInstance.params));
    }

    /**
     * @param {any} userParams
     * @param {any} mixinParams
     */
    _main(userParams, mixinParams = {}) {
      showWarningsForParams(Object.assign({}, mixinParams, userParams));
      if (globalState.currentInstance) {
        const swalPromiseResolve = privateMethods.swalPromiseResolve.get(globalState.currentInstance);
        const {
          isAwaitingPromise
        } = globalState.currentInstance;
        globalState.currentInstance._destroy();
        if (!isAwaitingPromise) {
          swalPromiseResolve({
            isDismissed: true
          });
        }
        if (isModal()) {
          unsetAriaHidden();
        }
      }
      globalState.currentInstance = currentInstance;
      const innerParams = prepareParams(userParams, mixinParams);
      setParameters(innerParams);
      Object.freeze(innerParams);

      // clear the previous timer
      if (globalState.timeout) {
        globalState.timeout.stop();
        delete globalState.timeout;
      }

      // clear the restore focus timeout
      clearTimeout(globalState.restoreFocusTimeout);
      const domCache = populateDomCache(currentInstance);
      render(currentInstance, innerParams);
      privateProps.innerParams.set(currentInstance, innerParams);
      return swalPromise(currentInstance, domCache, innerParams);
    }

    // `catch` cannot be the name of a module export, so we define our thenable methods here instead
    /**
     * @param {any} onFulfilled
     */
    then(onFulfilled) {
      return _classPrivateFieldGet2(_promise, this).then(onFulfilled);
    }

    /**
     * @param {any} onFinally
     */
    finally(onFinally) {
      return _classPrivateFieldGet2(_promise, this).finally(onFinally);
    }
  }

  /**
   * @param {SweetAlert} instance
   * @param {DomCache} domCache
   * @param {SweetAlertOptions} innerParams
   * @returns {Promise<SweetAlertResult>}
   */
  const swalPromise = (instance, domCache, innerParams) => {
    return new Promise((resolve, reject) => {
      // functions to handle all closings/dismissals
      /**
       * @param {DismissReason} dismiss
       */
      const dismissWith = dismiss => {
        instance.close({
          isDismissed: true,
          dismiss,
          isConfirmed: false,
          isDenied: false
        });
      };
      privateMethods.swalPromiseResolve.set(instance, resolve);
      privateMethods.swalPromiseReject.set(instance, reject);
      domCache.confirmButton.onclick = () => {
        handleConfirmButtonClick(instance);
      };
      domCache.denyButton.onclick = () => {
        handleDenyButtonClick(instance);
      };
      domCache.cancelButton.onclick = () => {
        handleCancelButtonClick(instance, dismissWith);
      };
      domCache.closeButton.onclick = () => {
        dismissWith(DismissReason.close);
      };
      handlePopupClick(innerParams, domCache, dismissWith);
      addKeydownHandler(globalState, innerParams, dismissWith);
      handleInputOptionsAndValue(instance, innerParams);
      openPopup(innerParams);
      setupTimer(globalState, innerParams, dismissWith);
      initFocus(domCache, innerParams);

      // Scroll container to top on open (#1247, #1946)
      setTimeout(() => {
        domCache.container.scrollTop = 0;
      });
    });
  };

  /**
   * @param {SweetAlertOptions} userParams
   * @param {SweetAlertOptions} mixinParams
   * @returns {SweetAlertOptions}
   */
  const prepareParams = (userParams, mixinParams) => {
    const templateParams = getTemplateParams(userParams);
    const params = Object.assign({}, defaultParams, mixinParams, templateParams, userParams); // precedence is described in #2131
    params.showClass = Object.assign({}, defaultParams.showClass, params.showClass);
    params.hideClass = Object.assign({}, defaultParams.hideClass, params.hideClass);
    if (params.animation === false) {
      params.showClass = {
        backdrop: 'swal2-noanimation'
      };
      params.hideClass = {};
    }
    return params;
  };

  /**
   * @param {SweetAlert} instance
   * @returns {DomCache}
   */
  const populateDomCache = instance => {
    const domCache = /** @type {DomCache} */{
      popup: (/** @type {HTMLElement} */getPopup()),
      container: (/** @type {HTMLElement} */getContainer()),
      actions: (/** @type {HTMLElement} */getActions()),
      confirmButton: (/** @type {HTMLElement} */getConfirmButton()),
      denyButton: (/** @type {HTMLElement} */getDenyButton()),
      cancelButton: (/** @type {HTMLElement} */getCancelButton()),
      loader: (/** @type {HTMLElement} */getLoader()),
      closeButton: (/** @type {HTMLElement} */getCloseButton()),
      validationMessage: (/** @type {HTMLElement} */getValidationMessage()),
      progressSteps: (/** @type {HTMLElement} */getProgressSteps())
    };
    privateProps.domCache.set(instance, domCache);
    return domCache;
  };

  /**
   * @param {GlobalState} globalState
   * @param {SweetAlertOptions} innerParams
   * @param {(dismiss: DismissReason) => void} dismissWith
   */
  const setupTimer = (globalState, innerParams, dismissWith) => {
    const timerProgressBar = getTimerProgressBar();
    hide(timerProgressBar);
    if (innerParams.timer) {
      globalState.timeout = new Timer(() => {
        dismissWith('timer');
        delete globalState.timeout;
      }, innerParams.timer);
      if (innerParams.timerProgressBar && timerProgressBar) {
        show(timerProgressBar);
        applyCustomClass(timerProgressBar, innerParams, 'timerProgressBar');
        setTimeout(() => {
          if (globalState.timeout && globalState.timeout.running) {
            // timer can be already stopped or unset at this point
            animateTimerProgressBar(/** @type {number} */innerParams.timer);
          }
        });
      }
    }
  };

  /**
   * Initialize focus in the popup:
   *
   * 1. If `toast` is `true`, don't steal focus from the document.
   * 2. Else if there is an [autofocus] element, focus it.
   * 3. Else if `focusConfirm` is `true` and confirm button is visible, focus it.
   * 4. Else if `focusDeny` is `true` and deny button is visible, focus it.
   * 5. Else if `focusCancel` is `true` and cancel button is visible, focus it.
   * 6. Else focus the first focusable element in a popup (if any).
   *
   * @param {DomCache} domCache
   * @param {SweetAlertOptions} innerParams
   */
  const initFocus = (domCache, innerParams) => {
    if (innerParams.toast) {
      return;
    }
    // TODO: this is dumb, remove `allowEnterKey` param in the next major version
    if (!callIfFunction(innerParams.allowEnterKey)) {
      warnAboutDeprecation('allowEnterKey');
      blurActiveElement();
      return;
    }
    if (focusAutofocus(domCache)) {
      return;
    }
    if (focusButton(domCache, innerParams)) {
      return;
    }
    setFocus(-1, 1);
  };

  /**
   * @param {DomCache} domCache
   * @returns {boolean}
   */
  const focusAutofocus = domCache => {
    const autofocusElements = Array.from(domCache.popup.querySelectorAll('[autofocus]'));
    for (const autofocusElement of autofocusElements) {
      if (autofocusElement instanceof HTMLElement && isVisible$1(autofocusElement)) {
        autofocusElement.focus();
        return true;
      }
    }
    return false;
  };

  /**
   * @param {DomCache} domCache
   * @param {SweetAlertOptions} innerParams
   * @returns {boolean}
   */
  const focusButton = (domCache, innerParams) => {
    if (innerParams.focusDeny && isVisible$1(domCache.denyButton)) {
      domCache.denyButton.focus();
      return true;
    }
    if (innerParams.focusCancel && isVisible$1(domCache.cancelButton)) {
      domCache.cancelButton.focus();
      return true;
    }
    if (innerParams.focusConfirm && isVisible$1(domCache.confirmButton)) {
      domCache.confirmButton.focus();
      return true;
    }
    return false;
  };
  const blurActiveElement = () => {
    if (document.activeElement instanceof HTMLElement && typeof document.activeElement.blur === 'function') {
      document.activeElement.blur();
    }
  };

  // Assign instance methods from src/instanceMethods/*.js to prototype
  SweetAlert.prototype.disableButtons = disableButtons;
  SweetAlert.prototype.enableButtons = enableButtons;
  SweetAlert.prototype.getInput = getInput;
  SweetAlert.prototype.disableInput = disableInput;
  SweetAlert.prototype.enableInput = enableInput;
  SweetAlert.prototype.hideLoading = hideLoading;
  SweetAlert.prototype.disableLoading = hideLoading;
  SweetAlert.prototype.showValidationMessage = showValidationMessage;
  SweetAlert.prototype.resetValidationMessage = resetValidationMessage;
  SweetAlert.prototype.close = close;
  SweetAlert.prototype.closePopup = close;
  SweetAlert.prototype.closeModal = close;
  SweetAlert.prototype.closeToast = close;
  SweetAlert.prototype.rejectPromise = rejectPromise;
  SweetAlert.prototype.update = update;
  SweetAlert.prototype._destroy = _destroy;

  // Assign static methods from src/staticMethods/*.js to constructor
  Object.assign(SweetAlert, staticMethods);

  // Proxy to instance methods to constructor, for now, for backwards compatibility
  Object.keys(instanceMethods).forEach(key => {
    /**
     * @param {...(SweetAlertOptions | string | undefined)} args
     * @returns {SweetAlertResult | Promise<SweetAlertResult> | undefined}
     */
    // @ts-ignore: Dynamic property assignment for backwards compatibility
    SweetAlert[key] = function (...args) {
      // @ts-ignore
      if (currentInstance && currentInstance[key]) {
        // @ts-ignore
        return currentInstance[key](...args);
      }
      return undefined;
    };
  });
  SweetAlert.DismissReason = DismissReason;
  SweetAlert.version = '11.26.17';

  const Swal = SweetAlert;
  // @ts-ignore
  Swal.default = Swal;

  return Swal;

}));
if (typeof this !== 'undefined' && this.Sweetalert2){this.swal = this.sweetAlert = this.Swal = this.SweetAlert = this.Sweetalert2}
"undefined"!=typeof document&&function(e,t){var n=e.createElement("style");if(e.getElementsByTagName("head")[0].appendChild(n),n.styleSheet)n.styleSheet.disabled||(n.styleSheet.cssText=t);else try{n.innerHTML=t}catch(e){n.innerText=t}}(document,":root{--swal2-outline: 0 0 0 3px rgba(100, 150, 200, 0.5);--swal2-container-padding: 0.625em;--swal2-backdrop: rgba(0, 0, 0, 0.4);--swal2-backdrop-transition: background-color 0.15s;--swal2-width: 32em;--swal2-padding: 0 0 1.25em;--swal2-border: none;--swal2-border-radius: 0.3125rem;--swal2-background: white;--swal2-color: #545454;--swal2-show-animation: swal2-show 0.3s;--swal2-hide-animation: swal2-hide 0.15s forwards;--swal2-icon-zoom: 1;--swal2-icon-animations: true;--swal2-title-padding: 0.8em 1em 0;--swal2-html-container-padding: 1em 1.6em 0.3em;--swal2-input-border: 1px solid #d9d9d9;--swal2-input-border-radius: 0.1875em;--swal2-input-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.06), 0 0 0 3px transparent;--swal2-input-background: transparent;--swal2-input-transition: border-color 0.2s, box-shadow 0.2s;--swal2-input-hover-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.06), 0 0 0 3px transparent;--swal2-input-focus-border: 1px solid #b4dbed;--swal2-input-focus-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.06), 0 0 0 3px rgba(100, 150, 200, 0.5);--swal2-progress-step-background: #add8e6;--swal2-validation-message-background: #f0f0f0;--swal2-validation-message-color: #666;--swal2-footer-border-color: #eee;--swal2-footer-background: transparent;--swal2-footer-color: inherit;--swal2-timer-progress-bar-background: rgba(0, 0, 0, 0.3);--swal2-close-button-position: initial;--swal2-close-button-inset: auto;--swal2-close-button-font-size: 2.5em;--swal2-close-button-color: #ccc;--swal2-close-button-transition: color 0.2s, box-shadow 0.2s;--swal2-close-button-outline: initial;--swal2-close-button-box-shadow: inset 0 0 0 3px transparent;--swal2-close-button-focus-box-shadow: inset var(--swal2-outline);--swal2-close-button-hover-transform: none;--swal2-actions-justify-content: center;--swal2-actions-width: auto;--swal2-actions-margin: 1.25em auto 0;--swal2-actions-padding: 0;--swal2-actions-border-radius: 0;--swal2-actions-background: transparent;--swal2-action-button-transition: background-color 0.2s, box-shadow 0.2s;--swal2-action-button-hover: black 10%;--swal2-action-button-active: black 10%;--swal2-confirm-button-box-shadow: none;--swal2-confirm-button-border-radius: 0.25em;--swal2-confirm-button-background-color: #7066e0;--swal2-confirm-button-color: #fff;--swal2-deny-button-box-shadow: none;--swal2-deny-button-border-radius: 0.25em;--swal2-deny-button-background-color: #dc3741;--swal2-deny-button-color: #fff;--swal2-cancel-button-box-shadow: none;--swal2-cancel-button-border-radius: 0.25em;--swal2-cancel-button-background-color: #6e7881;--swal2-cancel-button-color: #fff;--swal2-toast-show-animation: swal2-toast-show 0.5s;--swal2-toast-hide-animation: swal2-toast-hide 0.1s forwards;--swal2-toast-border: none;--swal2-toast-box-shadow: 0 0 1px hsl(0deg 0% 0% / 0.075), 0 1px 2px hsl(0deg 0% 0% / 0.075), 1px 2px 4px hsl(0deg 0% 0% / 0.075), 1px 3px 8px hsl(0deg 0% 0% / 0.075), 2px 4px 16px hsl(0deg 0% 0% / 0.075)}[data-swal2-theme=dark]{--swal2-dark-theme-black: #19191a;--swal2-dark-theme-white: #e1e1e1;--swal2-background: var(--swal2-dark-theme-black);--swal2-color: var(--swal2-dark-theme-white);--swal2-footer-border-color: #555;--swal2-input-background: color-mix(in srgb, var(--swal2-dark-theme-black), var(--swal2-dark-theme-white) 10%);--swal2-validation-message-background: color-mix( in srgb, var(--swal2-dark-theme-black), var(--swal2-dark-theme-white) 10% );--swal2-validation-message-color: var(--swal2-dark-theme-white);--swal2-timer-progress-bar-background: rgba(255, 255, 255, 0.7)}@media(prefers-color-scheme: dark){[data-swal2-theme=auto]{--swal2-dark-theme-black: #19191a;--swal2-dark-theme-white: #e1e1e1;--swal2-background: var(--swal2-dark-theme-black);--swal2-color: var(--swal2-dark-theme-white);--swal2-footer-border-color: #555;--swal2-input-background: color-mix(in srgb, var(--swal2-dark-theme-black), var(--swal2-dark-theme-white) 10%);--swal2-validation-message-background: color-mix( in srgb, var(--swal2-dark-theme-black), var(--swal2-dark-theme-white) 10% );--swal2-validation-message-color: var(--swal2-dark-theme-white);--swal2-timer-progress-bar-background: rgba(255, 255, 255, 0.7)}}body.swal2-shown:not(.swal2-no-backdrop,.swal2-toast-shown){overflow:hidden}body.swal2-height-auto{height:auto !important}body.swal2-no-backdrop .swal2-container{background-color:rgba(0,0,0,0) !important;pointer-events:none}body.swal2-no-backdrop .swal2-container .swal2-popup{pointer-events:all}body.swal2-no-backdrop .swal2-container .swal2-modal{box-shadow:0 0 10px var(--swal2-backdrop)}body.swal2-toast-shown .swal2-container{box-sizing:border-box;width:360px;max-width:100%;background-color:rgba(0,0,0,0);pointer-events:none}body.swal2-toast-shown .swal2-container.swal2-top{inset:0 auto auto 50%;transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-top-end,body.swal2-toast-shown .swal2-container.swal2-top-right{inset:0 0 auto auto}body.swal2-toast-shown .swal2-container.swal2-top-start,body.swal2-toast-shown .swal2-container.swal2-top-left{inset:0 auto auto 0}body.swal2-toast-shown .swal2-container.swal2-center-start,body.swal2-toast-shown .swal2-container.swal2-center-left{inset:50% auto auto 0;transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-center{inset:50% auto auto 50%;transform:translate(-50%, -50%)}body.swal2-toast-shown .swal2-container.swal2-center-end,body.swal2-toast-shown .swal2-container.swal2-center-right{inset:50% 0 auto auto;transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-start,body.swal2-toast-shown .swal2-container.swal2-bottom-left{inset:auto auto 0 0}body.swal2-toast-shown .swal2-container.swal2-bottom{inset:auto auto 0 50%;transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-end,body.swal2-toast-shown .swal2-container.swal2-bottom-right{inset:auto 0 0 auto}@media print{body.swal2-shown:not(.swal2-no-backdrop,.swal2-toast-shown){overflow-y:scroll !important}body.swal2-shown:not(.swal2-no-backdrop,.swal2-toast-shown)>[aria-hidden=true]{display:none}body.swal2-shown:not(.swal2-no-backdrop,.swal2-toast-shown) .swal2-container{position:static !important}}div:where(.swal2-container){display:grid;position:fixed;z-index:1060;inset:0;box-sizing:border-box;grid-template-areas:\"top-start     top            top-end\" \"center-start  center         center-end\" \"bottom-start  bottom-center  bottom-end\";grid-template-rows:minmax(min-content, auto) minmax(min-content, auto) minmax(min-content, auto);height:100%;padding:var(--swal2-container-padding);overflow-x:hidden;transition:var(--swal2-backdrop-transition);-webkit-overflow-scrolling:touch}div:where(.swal2-container).swal2-backdrop-show,div:where(.swal2-container).swal2-noanimation{background:var(--swal2-backdrop)}div:where(.swal2-container).swal2-backdrop-hide{background:rgba(0,0,0,0) !important}div:where(.swal2-container).swal2-top-start,div:where(.swal2-container).swal2-center-start,div:where(.swal2-container).swal2-bottom-start{grid-template-columns:minmax(0, 1fr) auto auto}div:where(.swal2-container).swal2-top,div:where(.swal2-container).swal2-center,div:where(.swal2-container).swal2-bottom{grid-template-columns:auto minmax(0, 1fr) auto}div:where(.swal2-container).swal2-top-end,div:where(.swal2-container).swal2-center-end,div:where(.swal2-container).swal2-bottom-end{grid-template-columns:auto auto minmax(0, 1fr)}div:where(.swal2-container).swal2-top-start>.swal2-popup{align-self:start}div:where(.swal2-container).swal2-top>.swal2-popup{grid-column:2;place-self:start center}div:where(.swal2-container).swal2-top-end>.swal2-popup,div:where(.swal2-container).swal2-top-right>.swal2-popup{grid-column:3;place-self:start end}div:where(.swal2-container).swal2-center-start>.swal2-popup,div:where(.swal2-container).swal2-center-left>.swal2-popup{grid-row:2;align-self:center}div:where(.swal2-container).swal2-center>.swal2-popup{grid-column:2;grid-row:2;place-self:center center}div:where(.swal2-container).swal2-center-end>.swal2-popup,div:where(.swal2-container).swal2-center-right>.swal2-popup{grid-column:3;grid-row:2;place-self:center end}div:where(.swal2-container).swal2-bottom-start>.swal2-popup,div:where(.swal2-container).swal2-bottom-left>.swal2-popup{grid-column:1;grid-row:3;align-self:end}div:where(.swal2-container).swal2-bottom>.swal2-popup{grid-column:2;grid-row:3;place-self:end center}div:where(.swal2-container).swal2-bottom-end>.swal2-popup,div:where(.swal2-container).swal2-bottom-right>.swal2-popup{grid-column:3;grid-row:3;place-self:end end}div:where(.swal2-container).swal2-grow-row>.swal2-popup,div:where(.swal2-container).swal2-grow-fullscreen>.swal2-popup{grid-column:1/4;width:100%}div:where(.swal2-container).swal2-grow-column>.swal2-popup,div:where(.swal2-container).swal2-grow-fullscreen>.swal2-popup{grid-row:1/4;align-self:stretch}div:where(.swal2-container).swal2-no-transition{transition:none !important}div:where(.swal2-container)[popover]{width:auto;border:0}div:where(.swal2-container) div:where(.swal2-popup){display:none;position:relative;box-sizing:border-box;grid-template-columns:minmax(0, 100%);width:var(--swal2-width);max-width:100%;padding:var(--swal2-padding);border:var(--swal2-border);border-radius:var(--swal2-border-radius);background:var(--swal2-background);color:var(--swal2-color);font-family:inherit;font-size:1rem;container-name:swal2-popup}div:where(.swal2-container) div:where(.swal2-popup):focus{outline:none}div:where(.swal2-container) div:where(.swal2-popup).swal2-loading{overflow-y:hidden}div:where(.swal2-container) div:where(.swal2-popup).swal2-draggable{cursor:grab}div:where(.swal2-container) div:where(.swal2-popup).swal2-draggable div:where(.swal2-icon){cursor:grab}div:where(.swal2-container) div:where(.swal2-popup).swal2-dragging{cursor:grabbing}div:where(.swal2-container) div:where(.swal2-popup).swal2-dragging div:where(.swal2-icon){cursor:grabbing}div:where(.swal2-container) h2:where(.swal2-title){position:relative;max-width:100%;margin:0;padding:var(--swal2-title-padding);color:inherit;font-size:1.875em;font-weight:600;text-align:center;text-transform:none;overflow-wrap:break-word;cursor:initial}div:where(.swal2-container) div:where(.swal2-actions){display:flex;z-index:1;box-sizing:border-box;flex-wrap:wrap;align-items:center;justify-content:var(--swal2-actions-justify-content);width:var(--swal2-actions-width);margin:var(--swal2-actions-margin);padding:var(--swal2-actions-padding);border-radius:var(--swal2-actions-border-radius);background:var(--swal2-actions-background)}div:where(.swal2-container) div:where(.swal2-loader){display:none;align-items:center;justify-content:center;width:2.2em;height:2.2em;margin:0 1.875em;animation:swal2-rotate-loading 1.5s linear 0s infinite normal;border-width:.25em;border-style:solid;border-radius:100%;border-color:#2778c4 rgba(0,0,0,0) #2778c4 rgba(0,0,0,0)}div:where(.swal2-container) button:where(.swal2-styled){margin:.3125em;padding:.625em 1.1em;transition:var(--swal2-action-button-transition);border:none;box-shadow:0 0 0 3px rgba(0,0,0,0);font-weight:500}div:where(.swal2-container) button:where(.swal2-styled):not([disabled]){cursor:pointer}div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-confirm){border-radius:var(--swal2-confirm-button-border-radius);background:initial;background-color:var(--swal2-confirm-button-background-color);box-shadow:var(--swal2-confirm-button-box-shadow);color:var(--swal2-confirm-button-color);font-size:1em}div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-confirm):hover{background-color:color-mix(in srgb, var(--swal2-confirm-button-background-color), var(--swal2-action-button-hover))}div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-confirm):active{background-color:color-mix(in srgb, var(--swal2-confirm-button-background-color), var(--swal2-action-button-active))}div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-deny){border-radius:var(--swal2-deny-button-border-radius);background:initial;background-color:var(--swal2-deny-button-background-color);box-shadow:var(--swal2-deny-button-box-shadow);color:var(--swal2-deny-button-color);font-size:1em}div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-deny):hover{background-color:color-mix(in srgb, var(--swal2-deny-button-background-color), var(--swal2-action-button-hover))}div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-deny):active{background-color:color-mix(in srgb, var(--swal2-deny-button-background-color), var(--swal2-action-button-active))}div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-cancel){border-radius:var(--swal2-cancel-button-border-radius);background:initial;background-color:var(--swal2-cancel-button-background-color);box-shadow:var(--swal2-cancel-button-box-shadow);color:var(--swal2-cancel-button-color);font-size:1em}div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-cancel):hover{background-color:color-mix(in srgb, var(--swal2-cancel-button-background-color), var(--swal2-action-button-hover))}div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-cancel):active{background-color:color-mix(in srgb, var(--swal2-cancel-button-background-color), var(--swal2-action-button-active))}div:where(.swal2-container) button:where(.swal2-styled):focus-visible{outline:none;box-shadow:var(--swal2-action-button-focus-box-shadow)}div:where(.swal2-container) button:where(.swal2-styled)[disabled]:not(.swal2-loading){opacity:.4}div:where(.swal2-container) button:where(.swal2-styled)::-moz-focus-inner{border:0}div:where(.swal2-container) div:where(.swal2-footer){margin:1em 0 0;padding:1em 1em 0;border-top:1px solid var(--swal2-footer-border-color);background:var(--swal2-footer-background);color:var(--swal2-footer-color);font-size:1em;text-align:center;cursor:initial}div:where(.swal2-container) .swal2-timer-progress-bar-container{position:absolute;right:0;bottom:0;left:0;grid-column:auto !important;overflow:hidden;border-bottom-right-radius:var(--swal2-border-radius);border-bottom-left-radius:var(--swal2-border-radius)}div:where(.swal2-container) div:where(.swal2-timer-progress-bar){width:100%;height:.25em;background:var(--swal2-timer-progress-bar-background)}div:where(.swal2-container) img:where(.swal2-image){max-width:100%;margin:2em auto 1em;cursor:initial}div:where(.swal2-container) button:where(.swal2-close){position:var(--swal2-close-button-position);inset:var(--swal2-close-button-inset);z-index:2;align-items:center;justify-content:center;width:1.2em;height:1.2em;margin-top:0;margin-right:0;margin-bottom:-1.2em;padding:0;overflow:hidden;transition:var(--swal2-close-button-transition);border:none;border-radius:var(--swal2-border-radius);outline:var(--swal2-close-button-outline);background:rgba(0,0,0,0);color:var(--swal2-close-button-color);font-family:monospace;font-size:var(--swal2-close-button-font-size);cursor:pointer;justify-self:end}div:where(.swal2-container) button:where(.swal2-close):hover{transform:var(--swal2-close-button-hover-transform);background:rgba(0,0,0,0);color:#f27474}div:where(.swal2-container) button:where(.swal2-close):focus-visible{outline:none;box-shadow:var(--swal2-close-button-focus-box-shadow)}div:where(.swal2-container) button:where(.swal2-close)::-moz-focus-inner{border:0}div:where(.swal2-container) div:where(.swal2-html-container){z-index:1;justify-content:center;margin:0;padding:var(--swal2-html-container-padding);overflow:auto;color:inherit;font-size:1.125em;font-weight:normal;line-height:normal;text-align:center;overflow-wrap:break-word;word-break:break-word;cursor:initial}div:where(.swal2-container) input:where(.swal2-input),div:where(.swal2-container) input:where(.swal2-file),div:where(.swal2-container) textarea:where(.swal2-textarea),div:where(.swal2-container) select:where(.swal2-select),div:where(.swal2-container) div:where(.swal2-radio),div:where(.swal2-container) label:where(.swal2-checkbox){margin:1em 2em 3px}div:where(.swal2-container) input:where(.swal2-input),div:where(.swal2-container) input:where(.swal2-file),div:where(.swal2-container) textarea:where(.swal2-textarea){box-sizing:border-box;width:auto;transition:var(--swal2-input-transition);border:var(--swal2-input-border);border-radius:var(--swal2-input-border-radius);background:var(--swal2-input-background);box-shadow:var(--swal2-input-box-shadow);color:inherit;font-size:1.125em}div:where(.swal2-container) input:where(.swal2-input).swal2-inputerror,div:where(.swal2-container) input:where(.swal2-file).swal2-inputerror,div:where(.swal2-container) textarea:where(.swal2-textarea).swal2-inputerror{border-color:#f27474 !important;box-shadow:0 0 2px #f27474 !important}div:where(.swal2-container) input:where(.swal2-input):hover,div:where(.swal2-container) input:where(.swal2-file):hover,div:where(.swal2-container) textarea:where(.swal2-textarea):hover{box-shadow:var(--swal2-input-hover-box-shadow)}div:where(.swal2-container) input:where(.swal2-input):focus,div:where(.swal2-container) input:where(.swal2-file):focus,div:where(.swal2-container) textarea:where(.swal2-textarea):focus{border:var(--swal2-input-focus-border);outline:none;box-shadow:var(--swal2-input-focus-box-shadow)}div:where(.swal2-container) input:where(.swal2-input)::placeholder,div:where(.swal2-container) input:where(.swal2-file)::placeholder,div:where(.swal2-container) textarea:where(.swal2-textarea)::placeholder{color:#ccc}div:where(.swal2-container) .swal2-range{margin:1em 2em 3px;background:var(--swal2-background)}div:where(.swal2-container) .swal2-range input{width:80%}div:where(.swal2-container) .swal2-range output{width:20%;color:inherit;font-weight:600;text-align:center}div:where(.swal2-container) .swal2-range input,div:where(.swal2-container) .swal2-range output{height:2.625em;padding:0;font-size:1.125em;line-height:2.625em}div:where(.swal2-container) .swal2-input{height:2.625em;padding:0 .75em}div:where(.swal2-container) .swal2-file{width:75%;margin-right:auto;margin-left:auto;background:var(--swal2-input-background);font-size:1.125em}div:where(.swal2-container) .swal2-textarea{height:6.75em;padding:.75em}div:where(.swal2-container) .swal2-select{min-width:50%;max-width:100%;padding:.375em .625em;background:var(--swal2-input-background);color:inherit;font-size:1.125em}div:where(.swal2-container) .swal2-radio,div:where(.swal2-container) .swal2-checkbox{align-items:center;justify-content:center;background:var(--swal2-background);color:inherit}div:where(.swal2-container) .swal2-radio label,div:where(.swal2-container) .swal2-checkbox label{margin:0 .6em;font-size:1.125em}div:where(.swal2-container) .swal2-radio input,div:where(.swal2-container) .swal2-checkbox input{flex-shrink:0;margin:0 .4em}div:where(.swal2-container) label:where(.swal2-input-label){display:flex;justify-content:center;margin:1em auto 0}div:where(.swal2-container) div:where(.swal2-validation-message){align-items:center;justify-content:center;margin:1em 0 0;padding:.625em;overflow:hidden;background:var(--swal2-validation-message-background);color:var(--swal2-validation-message-color);font-size:1em;font-weight:300}div:where(.swal2-container) div:where(.swal2-validation-message)::before{content:\"!\";display:inline-block;width:1.5em;min-width:1.5em;height:1.5em;margin:0 .625em;border-radius:50%;background-color:#f27474;color:#fff;font-weight:600;line-height:1.5em;text-align:center}div:where(.swal2-container) .swal2-progress-steps{flex-wrap:wrap;align-items:center;max-width:100%;margin:1.25em auto;padding:0;background:rgba(0,0,0,0);font-weight:600}div:where(.swal2-container) .swal2-progress-steps li{display:inline-block;position:relative}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step{z-index:20;flex-shrink:0;width:2em;height:2em;border-radius:2em;background:#2778c4;color:#fff;line-height:2em;text-align:center}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step.swal2-active-progress-step{background:#2778c4}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step.swal2-active-progress-step~.swal2-progress-step{background:var(--swal2-progress-step-background);color:#fff}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step.swal2-active-progress-step~.swal2-progress-step-line{background:var(--swal2-progress-step-background)}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step-line{z-index:10;flex-shrink:0;width:2.5em;height:.4em;margin:0 -1px;background:#2778c4}div:where(.swal2-icon){position:relative;box-sizing:content-box;justify-content:center;width:5em;height:5em;margin:2.5em auto .6em;zoom:var(--swal2-icon-zoom);border:.25em solid rgba(0,0,0,0);border-radius:50%;border-color:#000;font-family:inherit;line-height:5em;cursor:default;user-select:none}div:where(.swal2-icon) .swal2-icon-content{display:flex;align-items:center;font-size:3.75em}div:where(.swal2-icon).swal2-error{border-color:#f27474;color:#f27474}div:where(.swal2-icon).swal2-error .swal2-x-mark{position:relative;flex-grow:1}div:where(.swal2-icon).swal2-error [class^=swal2-x-mark-line]{display:block;position:absolute;top:2.3125em;width:2.9375em;height:.3125em;border-radius:.125em;background-color:#f27474}div:where(.swal2-icon).swal2-error [class^=swal2-x-mark-line][class$=left]{left:1.0625em;transform:rotate(45deg)}div:where(.swal2-icon).swal2-error [class^=swal2-x-mark-line][class$=right]{right:1em;transform:rotate(-45deg)}@container swal2-popup style(--swal2-icon-animations:true){div:where(.swal2-icon).swal2-error.swal2-icon-show{animation:swal2-animate-error-icon .5s}div:where(.swal2-icon).swal2-error.swal2-icon-show .swal2-x-mark{animation:swal2-animate-error-x-mark .5s}}div:where(.swal2-icon).swal2-warning{border-color:#f8bb86;color:#f8bb86}@container swal2-popup style(--swal2-icon-animations:true){div:where(.swal2-icon).swal2-warning.swal2-icon-show{animation:swal2-animate-error-icon .5s}div:where(.swal2-icon).swal2-warning.swal2-icon-show .swal2-icon-content{animation:swal2-animate-i-mark .5s}}div:where(.swal2-icon).swal2-info{border-color:#3fc3ee;color:#3fc3ee}@container swal2-popup style(--swal2-icon-animations:true){div:where(.swal2-icon).swal2-info.swal2-icon-show{animation:swal2-animate-error-icon .5s}div:where(.swal2-icon).swal2-info.swal2-icon-show .swal2-icon-content{animation:swal2-animate-i-mark .8s}}div:where(.swal2-icon).swal2-question{border-color:#87adbd;color:#87adbd}@container swal2-popup style(--swal2-icon-animations:true){div:where(.swal2-icon).swal2-question.swal2-icon-show{animation:swal2-animate-error-icon .5s}div:where(.swal2-icon).swal2-question.swal2-icon-show .swal2-icon-content{animation:swal2-animate-question-mark .8s}}div:where(.swal2-icon).swal2-success{border-color:#a5dc86;color:#a5dc86}div:where(.swal2-icon).swal2-success [class^=swal2-success-circular-line]{position:absolute;width:3.75em;height:7.5em;border-radius:50%}div:where(.swal2-icon).swal2-success [class^=swal2-success-circular-line][class$=left]{top:-0.4375em;left:-2.0635em;transform:rotate(-45deg);transform-origin:3.75em 3.75em;border-radius:7.5em 0 0 7.5em}div:where(.swal2-icon).swal2-success [class^=swal2-success-circular-line][class$=right]{top:-0.6875em;left:1.875em;transform:rotate(-45deg);transform-origin:0 3.75em;border-radius:0 7.5em 7.5em 0}div:where(.swal2-icon).swal2-success .swal2-success-ring{position:absolute;z-index:2;top:-0.25em;left:-0.25em;box-sizing:content-box;width:100%;height:100%;border:.25em solid rgba(165,220,134,.3);border-radius:50%}div:where(.swal2-icon).swal2-success .swal2-success-fix{position:absolute;z-index:1;top:.5em;left:1.625em;width:.4375em;height:5.625em;transform:rotate(-45deg)}div:where(.swal2-icon).swal2-success [class^=swal2-success-line]{display:block;position:absolute;z-index:2;height:.3125em;border-radius:.125em;background-color:#a5dc86}div:where(.swal2-icon).swal2-success [class^=swal2-success-line][class$=tip]{top:2.875em;left:.8125em;width:1.5625em;transform:rotate(45deg)}div:where(.swal2-icon).swal2-success [class^=swal2-success-line][class$=long]{top:2.375em;right:.5em;width:2.9375em;transform:rotate(-45deg)}@container swal2-popup style(--swal2-icon-animations:true){div:where(.swal2-icon).swal2-success.swal2-icon-show .swal2-success-line-tip{animation:swal2-animate-success-line-tip .75s}div:where(.swal2-icon).swal2-success.swal2-icon-show .swal2-success-line-long{animation:swal2-animate-success-line-long .75s}div:where(.swal2-icon).swal2-success.swal2-icon-show .swal2-success-circular-line-right{animation:swal2-rotate-success-circular-line 4.25s ease-in}}[class^=swal2]{-webkit-tap-highlight-color:rgba(0,0,0,0)}.swal2-show{animation:var(--swal2-show-animation)}.swal2-hide{animation:var(--swal2-hide-animation)}.swal2-noanimation{transition:none}.swal2-scrollbar-measure{position:absolute;top:-9999px;width:50px;height:50px;overflow:scroll}.swal2-rtl .swal2-close{margin-right:initial;margin-left:0}.swal2-rtl .swal2-timer-progress-bar{right:0;left:auto}.swal2-toast{box-sizing:border-box;grid-column:1/4 !important;grid-row:1/4 !important;grid-template-columns:min-content auto min-content;padding:1em;overflow-y:hidden;border:var(--swal2-toast-border);background:var(--swal2-background);box-shadow:var(--swal2-toast-box-shadow);pointer-events:all}.swal2-toast>*{grid-column:2}.swal2-toast h2:where(.swal2-title){margin:.5em 1em;padding:0;font-size:1em;text-align:initial}.swal2-toast .swal2-loading{justify-content:center}.swal2-toast input:where(.swal2-input){height:2em;margin:.5em;font-size:1em}.swal2-toast .swal2-validation-message{font-size:1em}.swal2-toast div:where(.swal2-footer){margin:.5em 0 0;padding:.5em 0 0;font-size:.8em}.swal2-toast button:where(.swal2-close){grid-column:3/3;grid-row:1/99;align-self:center;width:.8em;height:.8em;margin:0;font-size:2em}.swal2-toast div:where(.swal2-html-container){margin:.5em 1em;padding:0;overflow:initial;font-size:1em;text-align:initial}.swal2-toast div:where(.swal2-html-container):empty{padding:0}.swal2-toast .swal2-loader{grid-column:1;grid-row:1/99;align-self:center;width:2em;height:2em;margin:.25em}.swal2-toast .swal2-icon{grid-column:1;grid-row:1/99;align-self:center;width:2em;min-width:2em;height:2em;margin:0 .5em 0 0}.swal2-toast .swal2-icon .swal2-icon-content{display:flex;align-items:center;font-size:1.8em;font-weight:bold}.swal2-toast .swal2-icon.swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line]{top:.875em;width:1.375em}.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=left]{left:.3125em}.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=right]{right:.3125em}.swal2-toast div:where(.swal2-actions){justify-content:flex-start;height:auto;margin:0;margin-top:.5em;padding:0 .5em}.swal2-toast button:where(.swal2-styled){margin:.25em .5em;padding:.4em .6em;font-size:1em}.swal2-toast .swal2-success{border-color:#a5dc86}.swal2-toast .swal2-success [class^=swal2-success-circular-line]{position:absolute;width:1.6em;height:3em;border-radius:50%}.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=left]{top:-0.8em;left:-0.5em;transform:rotate(-45deg);transform-origin:2em 2em;border-radius:4em 0 0 4em}.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=right]{top:-0.25em;left:.9375em;transform-origin:0 1.5em;border-radius:0 4em 4em 0}.swal2-toast .swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-toast .swal2-success .swal2-success-fix{top:0;left:.4375em;width:.4375em;height:2.6875em}.swal2-toast .swal2-success [class^=swal2-success-line]{height:.3125em}.swal2-toast .swal2-success [class^=swal2-success-line][class$=tip]{top:1.125em;left:.1875em;width:.75em}.swal2-toast .swal2-success [class^=swal2-success-line][class$=long]{top:.9375em;right:.1875em;width:1.375em}@container swal2-popup style(--swal2-icon-animations:true){.swal2-toast .swal2-success.swal2-icon-show .swal2-success-line-tip{animation:swal2-toast-animate-success-line-tip .75s}.swal2-toast .swal2-success.swal2-icon-show .swal2-success-line-long{animation:swal2-toast-animate-success-line-long .75s}}.swal2-toast.swal2-show{animation:var(--swal2-toast-show-animation)}.swal2-toast.swal2-hide{animation:var(--swal2-toast-hide-animation)}@keyframes swal2-show{0%{transform:translate3d(0, -50px, 0) scale(0.9);opacity:0}100%{transform:translate3d(0, 0, 0) scale(1);opacity:1}}@keyframes swal2-hide{0%{transform:translate3d(0, 0, 0) scale(1);opacity:1}100%{transform:translate3d(0, -50px, 0) scale(0.9);opacity:0}}@keyframes swal2-animate-success-line-tip{0%{top:1.1875em;left:.0625em;width:0}54%{top:1.0625em;left:.125em;width:0}70%{top:2.1875em;left:-0.375em;width:3.125em}84%{top:3em;left:1.3125em;width:1.0625em}100%{top:2.8125em;left:.8125em;width:1.5625em}}@keyframes swal2-animate-success-line-long{0%{top:3.375em;right:2.875em;width:0}65%{top:3.375em;right:2.875em;width:0}84%{top:2.1875em;right:0;width:3.4375em}100%{top:2.375em;right:.5em;width:2.9375em}}@keyframes swal2-rotate-success-circular-line{0%{transform:rotate(-45deg)}5%{transform:rotate(-45deg)}12%{transform:rotate(-405deg)}100%{transform:rotate(-405deg)}}@keyframes swal2-animate-error-x-mark{0%{margin-top:1.625em;transform:scale(0.4);opacity:0}50%{margin-top:1.625em;transform:scale(0.4);opacity:0}80%{margin-top:-0.375em;transform:scale(1.15)}100%{margin-top:0;transform:scale(1);opacity:1}}@keyframes swal2-animate-error-icon{0%{transform:rotateX(100deg);opacity:0}100%{transform:rotateX(0deg);opacity:1}}@keyframes swal2-rotate-loading{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}@keyframes swal2-animate-question-mark{0%{transform:rotateY(-360deg)}100%{transform:rotateY(0)}}@keyframes swal2-animate-i-mark{0%{transform:rotateZ(45deg);opacity:0}25%{transform:rotateZ(-25deg);opacity:.4}50%{transform:rotateZ(15deg);opacity:.8}75%{transform:rotateZ(-5deg);opacity:1}100%{transform:rotateX(0);opacity:1}}@keyframes swal2-toast-show{0%{transform:translateY(-0.625em) rotateZ(2deg)}33%{transform:translateY(0) rotateZ(-2deg)}66%{transform:translateY(0.3125em) rotateZ(2deg)}100%{transform:translateY(0) rotateZ(0deg)}}@keyframes swal2-toast-hide{100%{transform:rotateZ(1deg);opacity:0}}@keyframes swal2-toast-animate-success-line-tip{0%{top:.5625em;left:.0625em;width:0}54%{top:.125em;left:.125em;width:0}70%{top:.625em;left:-0.25em;width:1.625em}84%{top:1.0625em;left:.75em;width:.5em}100%{top:1.125em;left:.1875em;width:.75em}}@keyframes swal2-toast-animate-success-line-long{0%{top:1.625em;right:1.375em;width:0}65%{top:1.25em;right:.9375em;width:0}84%{top:.9375em;right:0;width:1.125em}100%{top:.9375em;right:.1875em;width:1.375em}}");

/***/ },

/***/ "./node_modules/toastify-js/src/toastify.js"
/*!**************************************************!*\
  !*** ./node_modules/toastify-js/src/toastify.js ***!
  \**************************************************/
(module) {

/*!
 * Toastify js 1.12.0
 * https://github.com/apvarun/toastify-js
 * @license MIT licensed
 *
 * Copyright (C) 2018 Varun A P
 */
(function(root, factory) {
  if ( true && module.exports) {
    module.exports = factory();
  } else {
    root.Toastify = factory();
  }
})(this, function(global) {
  // Object initialization
  var Toastify = function(options) {
      // Returning a new init object
      return new Toastify.lib.init(options);
    },
    // Library version
    version = "1.12.0";

  // Set the default global options
  Toastify.defaults = {
    oldestFirst: true,
    text: "Toastify is awesome!",
    node: undefined,
    duration: 3000,
    selector: undefined,
    callback: function () {
    },
    destination: undefined,
    newWindow: false,
    close: false,
    gravity: "toastify-top",
    positionLeft: false,
    position: '',
    backgroundColor: '',
    avatar: "",
    className: "",
    stopOnFocus: true,
    onClick: function () {
    },
    offset: {x: 0, y: 0},
    escapeMarkup: true,
    ariaLive: 'polite',
    style: {background: ''}
  };

  // Defining the prototype of the object
  Toastify.lib = Toastify.prototype = {
    toastify: version,

    constructor: Toastify,

    // Initializing the object with required parameters
    init: function(options) {
      // Verifying and validating the input object
      if (!options) {
        options = {};
      }

      // Creating the options object
      this.options = {};

      this.toastElement = null;

      // Validating the options
      this.options.text = options.text || Toastify.defaults.text; // Display message
      this.options.node = options.node || Toastify.defaults.node;  // Display content as node
      this.options.duration = options.duration === 0 ? 0 : options.duration || Toastify.defaults.duration; // Display duration
      this.options.selector = options.selector || Toastify.defaults.selector; // Parent selector
      this.options.callback = options.callback || Toastify.defaults.callback; // Callback after display
      this.options.destination = options.destination || Toastify.defaults.destination; // On-click destination
      this.options.newWindow = options.newWindow || Toastify.defaults.newWindow; // Open destination in new window
      this.options.close = options.close || Toastify.defaults.close; // Show toast close icon
      this.options.gravity = options.gravity === "bottom" ? "toastify-bottom" : Toastify.defaults.gravity; // toast position - top or bottom
      this.options.positionLeft = options.positionLeft || Toastify.defaults.positionLeft; // toast position - left or right
      this.options.position = options.position || Toastify.defaults.position; // toast position - left or right
      this.options.backgroundColor = options.backgroundColor || Toastify.defaults.backgroundColor; // toast background color
      this.options.avatar = options.avatar || Toastify.defaults.avatar; // img element src - url or a path
      this.options.className = options.className || Toastify.defaults.className; // additional class names for the toast
      this.options.stopOnFocus = options.stopOnFocus === undefined ? Toastify.defaults.stopOnFocus : options.stopOnFocus; // stop timeout on focus
      this.options.onClick = options.onClick || Toastify.defaults.onClick; // Callback after click
      this.options.offset = options.offset || Toastify.defaults.offset; // toast offset
      this.options.escapeMarkup = options.escapeMarkup !== undefined ? options.escapeMarkup : Toastify.defaults.escapeMarkup;
      this.options.ariaLive = options.ariaLive || Toastify.defaults.ariaLive;
      this.options.style = options.style || Toastify.defaults.style;
      if(options.backgroundColor) {
        this.options.style.background = options.backgroundColor;
      }

      // Returning the current object for chaining functions
      return this;
    },

    // Building the DOM element
    buildToast: function() {
      // Validating if the options are defined
      if (!this.options) {
        throw "Toastify is not initialized";
      }

      // Creating the DOM object
      var divElement = document.createElement("div");
      divElement.className = "toastify on " + this.options.className;

      // Positioning toast to left or right or center
      if (!!this.options.position) {
        divElement.className += " toastify-" + this.options.position;
      } else {
        // To be depreciated in further versions
        if (this.options.positionLeft === true) {
          divElement.className += " toastify-left";
          console.warn('Property `positionLeft` will be depreciated in further versions. Please use `position` instead.')
        } else {
          // Default position
          divElement.className += " toastify-right";
        }
      }

      // Assigning gravity of element
      divElement.className += " " + this.options.gravity;

      if (this.options.backgroundColor) {
        // This is being deprecated in favor of using the style HTML DOM property
        console.warn('DEPRECATION NOTICE: "backgroundColor" is being deprecated. Please use the "style.background" property.');
      }

      // Loop through our style object and apply styles to divElement
      for (var property in this.options.style) {
        divElement.style[property] = this.options.style[property];
      }

      // Announce the toast to screen readers
      if (this.options.ariaLive) {
        divElement.setAttribute('aria-live', this.options.ariaLive)
      }

      // Adding the toast message/node
      if (this.options.node && this.options.node.nodeType === Node.ELEMENT_NODE) {
        // If we have a valid node, we insert it
        divElement.appendChild(this.options.node)
      } else {
        if (this.options.escapeMarkup) {
          divElement.innerText = this.options.text;
        } else {
          divElement.innerHTML = this.options.text;
        }

        if (this.options.avatar !== "") {
          var avatarElement = document.createElement("img");
          avatarElement.src = this.options.avatar;

          avatarElement.className = "toastify-avatar";

          if (this.options.position == "left" || this.options.positionLeft === true) {
            // Adding close icon on the left of content
            divElement.appendChild(avatarElement);
          } else {
            // Adding close icon on the right of content
            divElement.insertAdjacentElement("afterbegin", avatarElement);
          }
        }
      }

      // Adding a close icon to the toast
      if (this.options.close === true) {
        // Create a span for close element
        var closeElement = document.createElement("button");
        closeElement.type = "button";
        closeElement.setAttribute("aria-label", "Close");
        closeElement.className = "toast-close";
        closeElement.innerHTML = "&#10006;";

        // Triggering the removal of toast from DOM on close click
        closeElement.addEventListener(
          "click",
          function(event) {
            event.stopPropagation();
            this.removeElement(this.toastElement);
            window.clearTimeout(this.toastElement.timeOutValue);
          }.bind(this)
        );

        //Calculating screen width
        var width = window.innerWidth > 0 ? window.innerWidth : screen.width;

        // Adding the close icon to the toast element
        // Display on the right if screen width is less than or equal to 360px
        if ((this.options.position == "left" || this.options.positionLeft === true) && width > 360) {
          // Adding close icon on the left of content
          divElement.insertAdjacentElement("afterbegin", closeElement);
        } else {
          // Adding close icon on the right of content
          divElement.appendChild(closeElement);
        }
      }

      // Clear timeout while toast is focused
      if (this.options.stopOnFocus && this.options.duration > 0) {
        var self = this;
        // stop countdown
        divElement.addEventListener(
          "mouseover",
          function(event) {
            window.clearTimeout(divElement.timeOutValue);
          }
        )
        // add back the timeout
        divElement.addEventListener(
          "mouseleave",
          function() {
            divElement.timeOutValue = window.setTimeout(
              function() {
                // Remove the toast from DOM
                self.removeElement(divElement);
              },
              self.options.duration
            )
          }
        )
      }

      // Adding an on-click destination path
      if (typeof this.options.destination !== "undefined") {
        divElement.addEventListener(
          "click",
          function(event) {
            event.stopPropagation();
            if (this.options.newWindow === true) {
              window.open(this.options.destination, "_blank");
            } else {
              window.location = this.options.destination;
            }
          }.bind(this)
        );
      }

      if (typeof this.options.onClick === "function" && typeof this.options.destination === "undefined") {
        divElement.addEventListener(
          "click",
          function(event) {
            event.stopPropagation();
            this.options.onClick();
          }.bind(this)
        );
      }

      // Adding offset
      if(typeof this.options.offset === "object") {

        var x = getAxisOffsetAValue("x", this.options);
        var y = getAxisOffsetAValue("y", this.options);

        var xOffset = this.options.position == "left" ? x : "-" + x;
        var yOffset = this.options.gravity == "toastify-top" ? y : "-" + y;

        divElement.style.transform = "translate(" + xOffset + "," + yOffset + ")";

      }

      // Returning the generated element
      return divElement;
    },

    // Displaying the toast
    showToast: function() {
      // Creating the DOM object for the toast
      this.toastElement = this.buildToast();

      // Getting the root element to with the toast needs to be added
      var rootElement;
      if (typeof this.options.selector === "string") {
        rootElement = document.getElementById(this.options.selector);
      } else if (this.options.selector instanceof HTMLElement || (typeof ShadowRoot !== 'undefined' && this.options.selector instanceof ShadowRoot)) {
        rootElement = this.options.selector;
      } else {
        rootElement = document.body;
      }

      // Validating if root element is present in DOM
      if (!rootElement) {
        throw "Root element is not defined";
      }

      // Adding the DOM element
      var elementToInsert = Toastify.defaults.oldestFirst ? rootElement.firstChild : rootElement.lastChild;
      rootElement.insertBefore(this.toastElement, elementToInsert);

      // Repositioning the toasts in case multiple toasts are present
      Toastify.reposition();

      if (this.options.duration > 0) {
        this.toastElement.timeOutValue = window.setTimeout(
          function() {
            // Remove the toast from DOM
            this.removeElement(this.toastElement);
          }.bind(this),
          this.options.duration
        ); // Binding `this` for function invocation
      }

      // Supporting function chaining
      return this;
    },

    hideToast: function() {
      if (this.toastElement.timeOutValue) {
        clearTimeout(this.toastElement.timeOutValue);
      }
      this.removeElement(this.toastElement);
    },

    // Removing the element from the DOM
    removeElement: function(toastElement) {
      // Hiding the element
      // toastElement.classList.remove("on");
      toastElement.className = toastElement.className.replace(" on", "");

      // Removing the element from DOM after transition end
      window.setTimeout(
        function() {
          // remove options node if any
          if (this.options.node && this.options.node.parentNode) {
            this.options.node.parentNode.removeChild(this.options.node);
          }

          // Remove the element from the DOM, only when the parent node was not removed before.
          if (toastElement.parentNode) {
            toastElement.parentNode.removeChild(toastElement);
          }

          // Calling the callback function
          this.options.callback.call(toastElement);

          // Repositioning the toasts again
          Toastify.reposition();
        }.bind(this),
        400
      ); // Binding `this` for function invocation
    },
  };

  // Positioning the toasts on the DOM
  Toastify.reposition = function() {

    // Top margins with gravity
    var topLeftOffsetSize = {
      top: 15,
      bottom: 15,
    };
    var topRightOffsetSize = {
      top: 15,
      bottom: 15,
    };
    var offsetSize = {
      top: 15,
      bottom: 15,
    };

    // Get all toast messages on the DOM
    var allToasts = document.getElementsByClassName("toastify");

    var classUsed;

    // Modifying the position of each toast element
    for (var i = 0; i < allToasts.length; i++) {
      // Getting the applied gravity
      if (containsClass(allToasts[i], "toastify-top") === true) {
        classUsed = "toastify-top";
      } else {
        classUsed = "toastify-bottom";
      }

      var height = allToasts[i].offsetHeight;
      classUsed = classUsed.substr(9, classUsed.length-1)
      // Spacing between toasts
      var offset = 15;

      var width = window.innerWidth > 0 ? window.innerWidth : screen.width;

      // Show toast in center if screen with less than or equal to 360px
      if (width <= 360) {
        // Setting the position
        allToasts[i].style[classUsed] = offsetSize[classUsed] + "px";

        offsetSize[classUsed] += height + offset;
      } else {
        if (containsClass(allToasts[i], "toastify-left") === true) {
          // Setting the position
          allToasts[i].style[classUsed] = topLeftOffsetSize[classUsed] + "px";

          topLeftOffsetSize[classUsed] += height + offset;
        } else {
          // Setting the position
          allToasts[i].style[classUsed] = topRightOffsetSize[classUsed] + "px";

          topRightOffsetSize[classUsed] += height + offset;
        }
      }
    }

    // Supporting function chaining
    return this;
  };

  // Helper function to get offset.
  function getAxisOffsetAValue(axis, options) {

    if(options.offset[axis]) {
      if(isNaN(options.offset[axis])) {
        return options.offset[axis];
      }
      else {
        return options.offset[axis] + 'px';
      }
    }

    return '0px';

  }

  function containsClass(elem, yourClass) {
    if (!elem || typeof yourClass !== "string") {
      return false;
    } else if (
      elem.className &&
      elem.className
        .trim()
        .split(/\s+/gi)
        .indexOf(yourClass) > -1
    ) {
      return true;
    } else {
      return false;
    }
  }

  // Setting up the prototype for the init object
  Toastify.lib.init.prototype = Toastify.lib;

  // Returning the Toastify function to be assigned to the window object/module
  return Toastify;
});


/***/ },

/***/ "./node_modules/@orchidjs/sifter/dist/esm/sifter.js"
/*!**********************************************************!*\
  !*** ./node_modules/@orchidjs/sifter/dist/esm/sifter.js ***!
  \**********************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Sifter: () => (/* binding */ Sifter),
/* harmony export */   cmp: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.cmp),
/* harmony export */   getAttr: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.getAttr),
/* harmony export */   getAttrNesting: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.getAttrNesting),
/* harmony export */   getPattern: () => (/* reexport safe */ _orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__.getPattern),
/* harmony export */   iterate: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate),
/* harmony export */   propToArray: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.propToArray),
/* harmony export */   scoreValue: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.scoreValue)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils.js */ "./node_modules/@orchidjs/sifter/dist/esm/utils.js");
/* harmony import */ var _orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @orchidjs/unicode-variants */ "./node_modules/@orchidjs/unicode-variants/dist/esm/index.js");
/* harmony import */ var _types_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./types.js */ "./node_modules/@orchidjs/sifter/dist/esm/types.js");
/**
 * sifter.js
 * Copyright (c) 2013–2020 Brian Reavis & contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 * @author Brian Reavis <brian@thirdroute.com>
 */


class Sifter {
    items; // []|{};
    settings;
    /**
     * Textually searches arrays and hashes of objects
     * by property (or multiple properties). Designed
     * specifically for autocomplete.
     *
     */
    constructor(items, settings) {
        this.items = items;
        this.settings = settings || { diacritics: true };
    }
    ;
    /**
     * Splits a search string into an array of individual
     * regexps to be used to match results.
     *
     */
    tokenize(query, respect_word_boundaries, weights) {
        if (!query || !query.length)
            return [];
        const tokens = [];
        const words = query.split(/\s+/);
        var field_regex;
        if (weights) {
            field_regex = new RegExp('^(' + Object.keys(weights).map(_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__.escape_regex).join('|') + ')\:(.*)$');
        }
        words.forEach((word) => {
            let field_match;
            let field = null;
            let regex = null;
            // look for "field:query" tokens
            if (field_regex && (field_match = word.match(field_regex))) {
                field = field_match[1];
                word = field_match[2];
            }
            if (word.length > 0) {
                if (this.settings.diacritics) {
                    regex = (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__.getPattern)(word) || null;
                }
                else {
                    regex = (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__.escape_regex)(word);
                }
                if (regex && respect_word_boundaries)
                    regex = "\\b" + regex;
            }
            tokens.push({
                string: word,
                regex: regex ? new RegExp(regex, 'iu') : null,
                field: field,
            });
        });
        return tokens;
    }
    ;
    /**
     * Returns a function to be used to score individual results.
     *
     * Good matches will have a higher score than poor matches.
     * If an item is not a match, 0 will be returned by the function.
     *
     * @returns {T.ScoreFn}
     */
    getScoreFunction(query, options) {
        var search = this.prepareSearch(query, options);
        return this._getScoreFunction(search);
    }
    /**
     * @returns {T.ScoreFn}
     *
     */
    _getScoreFunction(search) {
        const tokens = search.tokens, token_count = tokens.length;
        if (!token_count) {
            return function () { return 0; };
        }
        const fields = search.options.fields, weights = search.weights, field_count = fields.length, getAttrFn = search.getAttrFn;
        if (!field_count) {
            return function () { return 1; };
        }
        /**
         * Calculates the score of an object
         * against the search query.
         *
         */
        const scoreObject = (function () {
            if (field_count === 1) {
                return function (token, data) {
                    const field = fields[0].field;
                    return (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.scoreValue)(getAttrFn(data, field), token, weights[field] || 1);
                };
            }
            return function (token, data) {
                var sum = 0;
                // is the token specific to a field?
                if (token.field) {
                    const value = getAttrFn(data, token.field);
                    if (!token.regex && value) {
                        sum += (1 / field_count);
                    }
                    else {
                        sum += (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.scoreValue)(value, token, 1);
                    }
                }
                else {
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(weights, (weight, field) => {
                        sum += (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.scoreValue)(getAttrFn(data, field), token, weight);
                    });
                }
                return sum / field_count;
            };
        })();
        if (token_count === 1) {
            return function (data) {
                return scoreObject(tokens[0], data);
            };
        }
        if (search.options.conjunction === 'and') {
            return function (data) {
                var score, sum = 0;
                for (let token of tokens) {
                    score = scoreObject(token, data);
                    if (score <= 0)
                        return 0;
                    sum += score;
                }
                return sum / token_count;
            };
        }
        else {
            return function (data) {
                var sum = 0;
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(tokens, (token) => {
                    sum += scoreObject(token, data);
                });
                return sum / token_count;
            };
        }
    }
    ;
    /**
     * Returns a function that can be used to compare two
     * results, for sorting purposes. If no sorting should
     * be performed, `null` will be returned.
     *
     * @return function(a,b)
     */
    getSortFunction(query, options) {
        var search = this.prepareSearch(query, options);
        return this._getSortFunction(search);
    }
    _getSortFunction(search) {
        var implicit_score, sort_flds = [];
        const self = this, options = search.options, sort = (!search.query && options.sort_empty) ? options.sort_empty : options.sort;
        if (typeof sort == 'function') {
            return sort.bind(this);
        }
        /**
         * Fetches the specified sort field value
         * from a search result item.
         *
         */
        const get_field = function (name, result) {
            if (name === '$score')
                return result.score;
            return search.getAttrFn(self.items[result.id], name);
        };
        // parse options
        if (sort) {
            for (let s of sort) {
                if (search.query || s.field !== '$score') {
                    sort_flds.push(s);
                }
            }
        }
        // the "$score" field is implied to be the primary
        // sort field, unless it's manually specified
        if (search.query) {
            implicit_score = true;
            for (let fld of sort_flds) {
                if (fld.field === '$score') {
                    implicit_score = false;
                    break;
                }
            }
            if (implicit_score) {
                sort_flds.unshift({ field: '$score', direction: 'desc' });
            }
            // without a search.query, all items will have the same score
        }
        else {
            sort_flds = sort_flds.filter((fld) => fld.field !== '$score');
        }
        // build function
        const sort_flds_count = sort_flds.length;
        if (!sort_flds_count) {
            return null;
        }
        return function (a, b) {
            var result, field;
            for (let sort_fld of sort_flds) {
                field = sort_fld.field;
                let multiplier = sort_fld.direction === 'desc' ? -1 : 1;
                result = multiplier * (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.cmp)(get_field(field, a), get_field(field, b));
                if (result)
                    return result;
            }
            return 0;
        };
    }
    ;
    /**
     * Parses a search query and returns an object
     * with tokens and fields ready to be populated
     * with results.
     *
     */
    prepareSearch(query, optsUser) {
        const weights = {};
        var options = Object.assign({}, optsUser);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.propToArray)(options, 'sort');
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.propToArray)(options, 'sort_empty');
        // convert fields to new format
        if (options.fields) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.propToArray)(options, 'fields');
            const fields = [];
            options.fields.forEach((field) => {
                if (typeof field == 'string') {
                    field = { field: field, weight: 1 };
                }
                fields.push(field);
                weights[field.field] = ('weight' in field) ? field.weight : 1;
            });
            options.fields = fields;
        }
        return {
            options: options,
            query: query.toLowerCase().trim(),
            tokens: this.tokenize(query, options.respect_word_boundaries, weights),
            total: 0,
            items: [],
            weights: weights,
            getAttrFn: (options.nesting) ? _utils_js__WEBPACK_IMPORTED_MODULE_0__.getAttrNesting : _utils_js__WEBPACK_IMPORTED_MODULE_0__.getAttr,
        };
    }
    ;
    /**
     * Searches through all items and returns a sorted array of matches.
     *
     */
    search(query, options) {
        var self = this, score, search;
        search = this.prepareSearch(query, options);
        options = search.options;
        query = search.query;
        // generate result scoring function
        const fn_score = options.score || self._getScoreFunction(search);
        // perform search and sort
        if (query.length) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(self.items, (item, id) => {
                score = fn_score(item);
                if (options.filter === false || score > 0) {
                    search.items.push({ 'score': score, 'id': id });
                }
            });
        }
        else {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(self.items, (_, id) => {
                search.items.push({ 'score': 1, 'id': id });
            });
        }
        const fn_sort = self._getSortFunction(search);
        if (fn_sort)
            search.items.sort(fn_sort);
        // apply limits
        search.total = search.items.length;
        if (typeof options.limit === 'number') {
            search.items = search.items.slice(0, options.limit);
        }
        return search;
    }
    ;
}


//# sourceMappingURL=sifter.js.map

/***/ },

/***/ "./node_modules/@orchidjs/sifter/dist/esm/types.js"
/*!*********************************************************!*\
  !*** ./node_modules/@orchidjs/sifter/dist/esm/types.js ***!
  \*********************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);

//# sourceMappingURL=types.js.map

/***/ },

/***/ "./node_modules/@orchidjs/sifter/dist/esm/utils.js"
/*!*********************************************************!*\
  !*** ./node_modules/@orchidjs/sifter/dist/esm/utils.js ***!
  \*********************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   cmp: () => (/* binding */ cmp),
/* harmony export */   getAttr: () => (/* binding */ getAttr),
/* harmony export */   getAttrNesting: () => (/* binding */ getAttrNesting),
/* harmony export */   iterate: () => (/* binding */ iterate),
/* harmony export */   propToArray: () => (/* binding */ propToArray),
/* harmony export */   scoreValue: () => (/* binding */ scoreValue)
/* harmony export */ });
/* harmony import */ var _orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @orchidjs/unicode-variants */ "./node_modules/@orchidjs/unicode-variants/dist/esm/index.js");

/**
 * A property getter resolving dot-notation
 * @param  {Object}  obj     The root object to fetch property on
 * @param  {String}  name    The optionally dotted property name to fetch
 * @return {Object}          The resolved property value
 */
const getAttr = (obj, name) => {
    if (!obj)
        return;
    return obj[name];
};
/**
 * A property getter resolving dot-notation
 * @param  {Object}  obj     The root object to fetch property on
 * @param  {String}  name    The optionally dotted property name to fetch
 * @return {Object}          The resolved property value
 */
const getAttrNesting = (obj, name) => {
    if (!obj)
        return;
    var part, names = name.split(".");
    while ((part = names.shift()) && (obj = obj[part]))
        ;
    return obj;
};
/**
 * Calculates how close of a match the
 * given value is against a search token.
 *
 */
const scoreValue = (value, token, weight) => {
    var score, pos;
    if (!value)
        return 0;
    value = value + '';
    if (token.regex == null)
        return 0;
    pos = value.search(token.regex);
    if (pos === -1)
        return 0;
    score = token.string.length / value.length;
    if (pos === 0)
        score += 0.5;
    return score * weight;
};
/**
 * Cast object property to an array if it exists and has a value
 *
 */
const propToArray = (obj, key) => {
    var value = obj[key];
    if (typeof value == 'function')
        return value;
    if (value && !Array.isArray(value)) {
        obj[key] = [value];
    }
};
/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
    if (Array.isArray(object)) {
        object.forEach(callback);
    }
    else {
        for (var key in object) {
            if (object.hasOwnProperty(key)) {
                callback(object[key], key);
            }
        }
    }
};
const cmp = (a, b) => {
    if (typeof a === 'number' && typeof b === 'number') {
        return a > b ? 1 : (a < b ? -1 : 0);
    }
    a = (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_0__.asciifold)(a + '').toLowerCase();
    b = (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_0__.asciifold)(b + '').toLowerCase();
    if (a > b)
        return 1;
    if (b > a)
        return -1;
    return 0;
};
//# sourceMappingURL=utils.js.map

/***/ },

/***/ "./node_modules/@orchidjs/unicode-variants/dist/esm/index.js"
/*!*******************************************************************!*\
  !*** ./node_modules/@orchidjs/unicode-variants/dist/esm/index.js ***!
  \*******************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   _asciifold: () => (/* binding */ _asciifold),
/* harmony export */   asciifold: () => (/* binding */ asciifold),
/* harmony export */   code_points: () => (/* binding */ code_points),
/* harmony export */   escape_regex: () => (/* reexport safe */ _regex_js__WEBPACK_IMPORTED_MODULE_0__.escape_regex),
/* harmony export */   generateMap: () => (/* binding */ generateMap),
/* harmony export */   generateSets: () => (/* binding */ generateSets),
/* harmony export */   generator: () => (/* binding */ generator),
/* harmony export */   getPattern: () => (/* binding */ getPattern),
/* harmony export */   initialize: () => (/* binding */ initialize),
/* harmony export */   mapSequence: () => (/* binding */ mapSequence),
/* harmony export */   normalize: () => (/* binding */ normalize),
/* harmony export */   substringsToPattern: () => (/* binding */ substringsToPattern),
/* harmony export */   unicode_map: () => (/* binding */ unicode_map)
/* harmony export */ });
/* harmony import */ var _regex_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./regex.js */ "./node_modules/@orchidjs/unicode-variants/dist/esm/regex.js");
/* harmony import */ var _strings_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./strings.js */ "./node_modules/@orchidjs/unicode-variants/dist/esm/strings.js");


const code_points = [[0, 65535]];
const accent_pat = '[\u0300-\u036F\u{b7}\u{2be}\u{2bc}]';
let unicode_map;
let multi_char_reg;
const max_char_length = 3;
const latin_convert = {};
const latin_condensed = {
    '/': '⁄∕',
    '0': '߀',
    "a": "ⱥɐɑ",
    "aa": "ꜳ",
    "ae": "æǽǣ",
    "ao": "ꜵ",
    "au": "ꜷ",
    "av": "ꜹꜻ",
    "ay": "ꜽ",
    "b": "ƀɓƃ",
    "c": "ꜿƈȼↄ",
    "d": "đɗɖᴅƌꮷԁɦ",
    "e": "ɛǝᴇɇ",
    "f": "ꝼƒ",
    "g": "ǥɠꞡᵹꝿɢ",
    "h": "ħⱨⱶɥ",
    "i": "ɨı",
    "j": "ɉȷ",
    "k": "ƙⱪꝁꝃꝅꞣ",
    "l": "łƚɫⱡꝉꝇꞁɭ",
    "m": "ɱɯϻ",
    "n": "ꞥƞɲꞑᴎлԉ",
    "o": "øǿɔɵꝋꝍᴑ",
    "oe": "œ",
    "oi": "ƣ",
    "oo": "ꝏ",
    "ou": "ȣ",
    "p": "ƥᵽꝑꝓꝕρ",
    "q": "ꝗꝙɋ",
    "r": "ɍɽꝛꞧꞃ",
    "s": "ßȿꞩꞅʂ",
    "t": "ŧƭʈⱦꞇ",
    "th": "þ",
    "tz": "ꜩ",
    "u": "ʉ",
    "v": "ʋꝟʌ",
    "vy": "ꝡ",
    "w": "ⱳ",
    "y": "ƴɏỿ",
    "z": "ƶȥɀⱬꝣ",
    "hv": "ƕ"
};
for (let latin in latin_condensed) {
    let unicode = latin_condensed[latin] || '';
    for (let i = 0; i < unicode.length; i++) {
        let char = unicode.substring(i, i + 1);
        latin_convert[char] = latin;
    }
}
const convert_pat = new RegExp(Object.keys(latin_convert).join('|') + '|' + accent_pat, 'gu');
/**
 * Initialize the unicode_map from the give code point ranges
 */
const initialize = (_code_points) => {
    if (unicode_map !== undefined)
        return;
    unicode_map = generateMap(_code_points || code_points);
};
/**
 * Helper method for normalize a string
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/normalize
 */
const normalize = (str, form = 'NFKD') => str.normalize(form);
/**
 * Remove accents without reordering string
 * calling str.normalize('NFKD') on \u{594}\u{595}\u{596} becomes \u{596}\u{594}\u{595}
 * via https://github.com/krisk/Fuse/issues/133#issuecomment-318692703
 */
const asciifold = (str) => {
    return Array.from(str).reduce(
    /**
     * @param {string} result
     * @param {string} char
     */
    (result, char) => {
        return result + _asciifold(char);
    }, '');
};
const _asciifold = (str) => {
    str = normalize(str)
        .toLowerCase()
        .replace(convert_pat, (/** @type {string} */ char) => {
        return latin_convert[char] || '';
    });
    //return str;
    return normalize(str, 'NFC');
};
/**
 * Generate a list of unicode variants from the list of code points
 */
function* generator(code_points) {
    for (const [code_point_min, code_point_max] of code_points) {
        for (let i = code_point_min; i <= code_point_max; i++) {
            let composed = String.fromCharCode(i);
            let folded = asciifold(composed);
            if (folded == composed.toLowerCase()) {
                continue;
            }
            // skip when folded is a string longer than 3 characters long
            // bc the resulting regex patterns will be long
            // eg:
            // folded صلى الله عليه وسلم length 18 code point 65018
            // folded جل جلاله length 8 code point 65019
            if (folded.length > max_char_length) {
                continue;
            }
            if (folded.length == 0) {
                continue;
            }
            yield { folded: folded, composed: composed, code_point: i };
        }
    }
}
/**
 * Generate a unicode map from the list of code points
 */
const generateSets = (code_points) => {
    const unicode_sets = {};
    const addMatching = (folded, to_add) => {
        /** @type {Set<string>} */
        const folded_set = unicode_sets[folded] || new Set();
        const patt = new RegExp('^' + (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.setToPattern)(folded_set) + '$', 'iu');
        if (to_add.match(patt)) {
            return;
        }
        folded_set.add((0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.escape_regex)(to_add));
        unicode_sets[folded] = folded_set;
    };
    for (let value of generator(code_points)) {
        addMatching(value.folded, value.folded);
        addMatching(value.folded, value.composed);
    }
    return unicode_sets;
};
/**
 * Generate a unicode map from the list of code points
 * ae => (?:(?:ae|Æ|Ǽ|Ǣ)|(?:A|Ⓐ|Ａ...)(?:E|ɛ|Ⓔ...))
 */
const generateMap = (code_points) => {
    const unicode_sets = generateSets(code_points);
    const unicode_map = {};
    let multi_char = [];
    for (let folded in unicode_sets) {
        let set = unicode_sets[folded];
        if (set) {
            unicode_map[folded] = (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.setToPattern)(set);
        }
        if (folded.length > 1) {
            multi_char.push((0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.escape_regex)(folded));
        }
    }
    multi_char.sort((a, b) => b.length - a.length);
    const multi_char_patt = (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.arrayToPattern)(multi_char);
    multi_char_reg = new RegExp('^' + multi_char_patt, 'u');
    return unicode_map;
};
/**
 * Map each element of an array from its folded value to all possible unicode matches
 */
const mapSequence = (strings, min_replacement = 1) => {
    let chars_replaced = 0;
    strings = strings.map((str) => {
        if (unicode_map[str]) {
            chars_replaced += str.length;
        }
        return unicode_map[str] || str;
    });
    if (chars_replaced >= min_replacement) {
        return (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.sequencePattern)(strings);
    }
    return '';
};
/**
 * Convert a short string and split it into all possible patterns
 * Keep a pattern only if min_replacement is met
 *
 * 'abc'
 * 		=> [['abc'],['ab','c'],['a','bc'],['a','b','c']]
 *		=> ['abc-pattern','ab-c-pattern'...]
 */
const substringsToPattern = (str, min_replacement = 1) => {
    min_replacement = Math.max(min_replacement, str.length - 1);
    return (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.arrayToPattern)((0,_strings_js__WEBPACK_IMPORTED_MODULE_1__.allSubstrings)(str).map((sub_pat) => {
        return mapSequence(sub_pat, min_replacement);
    }));
};
/**
 * Convert an array of sequences into a pattern
 * [{start:0,end:3,length:3,substr:'iii'}...] => (?:iii...)
 */
const sequencesToPattern = (sequences, all = true) => {
    let min_replacement = sequences.length > 1 ? 1 : 0;
    return (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.arrayToPattern)(sequences.map((sequence) => {
        let seq = [];
        const len = all ? sequence.length() : sequence.length() - 1;
        for (let j = 0; j < len; j++) {
            seq.push(substringsToPattern(sequence.substrs[j] || '', min_replacement));
        }
        return (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.sequencePattern)(seq);
    }));
};
/**
 * Return true if the sequence is already in the sequences
 */
const inSequences = (needle_seq, sequences) => {
    for (const seq of sequences) {
        if (seq.start != needle_seq.start || seq.end != needle_seq.end) {
            continue;
        }
        if (seq.substrs.join('') !== needle_seq.substrs.join('')) {
            continue;
        }
        let needle_parts = needle_seq.parts;
        const filter = (part) => {
            for (const needle_part of needle_parts) {
                if (needle_part.start === part.start && needle_part.substr === part.substr) {
                    return false;
                }
                if (part.length == 1 || needle_part.length == 1) {
                    continue;
                }
                // check for overlapping parts
                // a = ['::=','==']
                // b = ['::','===']
                // a = ['r','sm']
                // b = ['rs','m']
                if (part.start < needle_part.start && part.end > needle_part.start) {
                    return true;
                }
                if (needle_part.start < part.start && needle_part.end > part.start) {
                    return true;
                }
            }
            return false;
        };
        let filtered = seq.parts.filter(filter);
        if (filtered.length > 0) {
            continue;
        }
        return true;
    }
    return false;
};
class Sequence {
    parts;
    substrs;
    start;
    end;
    constructor() {
        this.parts = [];
        this.substrs = [];
        this.start = 0;
        this.end = 0;
    }
    add(part) {
        if (part) {
            this.parts.push(part);
            this.substrs.push(part.substr);
            this.start = Math.min(part.start, this.start);
            this.end = Math.max(part.end, this.end);
        }
    }
    last() {
        return this.parts[this.parts.length - 1];
    }
    length() {
        return this.parts.length;
    }
    clone(position, last_piece) {
        let clone = new Sequence();
        let parts = JSON.parse(JSON.stringify(this.parts));
        let last_part = parts.pop();
        for (const part of parts) {
            clone.add(part);
        }
        let last_substr = last_piece.substr.substring(0, position - last_part.start);
        let clone_last_len = last_substr.length;
        clone.add({ start: last_part.start, end: last_part.start + clone_last_len, length: clone_last_len, substr: last_substr });
        return clone;
    }
}
/**
 * Expand a regular expression pattern to include unicode variants
 * 	eg /a/ becomes /aⓐａẚàáâầấẫẩãāăằắẵẳȧǡäǟảåǻǎȁȃạậặḁąⱥɐɑAⒶＡÀÁÂẦẤẪẨÃĀĂẰẮẴẲȦǠÄǞẢÅǺǍȀȂẠẬẶḀĄȺⱯ/
 *
 * Issue:
 *  ﺊﺋ [ 'ﺊ = \\u{fe8a}', 'ﺋ = \\u{fe8b}' ]
 *	becomes:	ئئ [ 'ي = \\u{64a}', 'ٔ = \\u{654}', 'ي = \\u{64a}', 'ٔ = \\u{654}' ]
 *
 *	İĲ = IIJ = ⅡJ
 *
 * 	1/2/4
 */
const getPattern = (str) => {
    initialize();
    str = asciifold(str);
    let pattern = '';
    let sequences = [new Sequence()];
    for (let i = 0; i < str.length; i++) {
        let substr = str.substring(i);
        let match = substr.match(multi_char_reg);
        const char = str.substring(i, i + 1);
        const match_str = match ? match[0] : null;
        // loop through sequences
        // add either the char or multi_match
        let overlapping = [];
        let added_types = new Set();
        for (const sequence of sequences) {
            const last_piece = sequence.last();
            if (!last_piece || last_piece.length == 1 || last_piece.end <= i) {
                // if we have a multi match
                if (match_str) {
                    const len = match_str.length;
                    sequence.add({ start: i, end: i + len, length: len, substr: match_str });
                    added_types.add('1');
                }
                else {
                    sequence.add({ start: i, end: i + 1, length: 1, substr: char });
                    added_types.add('2');
                }
            }
            else if (match_str) {
                let clone = sequence.clone(i, last_piece);
                const len = match_str.length;
                clone.add({ start: i, end: i + len, length: len, substr: match_str });
                overlapping.push(clone);
            }
            else {
                // don't add char
                // adding would create invalid patterns: 234 => [2,34,4]
                added_types.add('3');
            }
        }
        // if we have overlapping
        if (overlapping.length > 0) {
            // ['ii','iii'] before ['i','i','iii']
            overlapping = overlapping.sort((a, b) => {
                return a.length() - b.length();
            });
            for (let clone of overlapping) {
                // don't add if we already have an equivalent sequence
                if (inSequences(clone, sequences)) {
                    continue;
                }
                sequences.push(clone);
            }
            continue;
        }
        // if we haven't done anything unique
        // clean up the patterns
        // helps keep patterns smaller
        // if str = 'r₨㎧aarss', pattern will be 446 instead of 655
        if (i > 0 && added_types.size == 1 && !added_types.has('3')) {
            pattern += sequencesToPattern(sequences, false);
            let new_seq = new Sequence();
            const old_seq = sequences[0];
            if (old_seq) {
                new_seq.add(old_seq.last());
            }
            sequences = [new_seq];
        }
    }
    pattern += sequencesToPattern(sequences, true);
    return pattern;
};

//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@orchidjs/unicode-variants/dist/esm/regex.js"
/*!*******************************************************************!*\
  !*** ./node_modules/@orchidjs/unicode-variants/dist/esm/regex.js ***!
  \*******************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   arrayToPattern: () => (/* binding */ arrayToPattern),
/* harmony export */   escape_regex: () => (/* binding */ escape_regex),
/* harmony export */   hasDuplicates: () => (/* binding */ hasDuplicates),
/* harmony export */   maxValueLength: () => (/* binding */ maxValueLength),
/* harmony export */   sequencePattern: () => (/* binding */ sequencePattern),
/* harmony export */   setToPattern: () => (/* binding */ setToPattern),
/* harmony export */   unicodeLength: () => (/* binding */ unicodeLength)
/* harmony export */ });
/**
 * Convert array of strings to a regular expression
 *	ex ['ab','a'] => (?:ab|a)
 * 	ex ['a','b'] => [ab]
 */
const arrayToPattern = (chars) => {
    chars = chars.filter(Boolean);
    if (chars.length < 2) {
        return chars[0] || '';
    }
    return (maxValueLength(chars) == 1) ? '[' + chars.join('') + ']' : '(?:' + chars.join('|') + ')';
};
const sequencePattern = (array) => {
    if (!hasDuplicates(array)) {
        return array.join('');
    }
    let pattern = '';
    let prev_char_count = 0;
    const prev_pattern = () => {
        if (prev_char_count > 1) {
            pattern += '{' + prev_char_count + '}';
        }
    };
    array.forEach((char, i) => {
        if (char === array[i - 1]) {
            prev_char_count++;
            return;
        }
        prev_pattern();
        pattern += char;
        prev_char_count = 1;
    });
    prev_pattern();
    return pattern;
};
/**
 * Convert array of strings to a regular expression
 *	ex ['ab','a'] => (?:ab|a)
 * 	ex ['a','b'] => [ab]
 */
const setToPattern = (chars) => {
    let array = Array.from(chars);
    return arrayToPattern(array);
};
/**
 * https://stackoverflow.com/questions/7376598/in-javascript-how-do-i-check-if-an-array-has-duplicate-values
 */
const hasDuplicates = (array) => {
    return (new Set(array)).size !== array.length;
};
/**
 * https://stackoverflow.com/questions/63006601/why-does-u-throw-an-invalid-escape-error
 */
const escape_regex = (str) => {
    return (str + '').replace(/([\$\(\)\*\+\.\?\[\]\^\{\|\}\\])/gu, '\\$1');
};
/**
 * Return the max length of array values
 */
const maxValueLength = (array) => {
    return array.reduce((longest, value) => Math.max(longest, unicodeLength(value)), 0);
};
const unicodeLength = (str) => {
    return Array.from(str).length;
};
//# sourceMappingURL=regex.js.map

/***/ },

/***/ "./node_modules/@orchidjs/unicode-variants/dist/esm/strings.js"
/*!*********************************************************************!*\
  !*** ./node_modules/@orchidjs/unicode-variants/dist/esm/strings.js ***!
  \*********************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   allSubstrings: () => (/* binding */ allSubstrings)
/* harmony export */ });
/**
 * Get all possible combinations of substrings that add up to the given string
 * https://stackoverflow.com/questions/30169587/find-all-the-combination-of-substrings-that-add-up-to-the-given-string
 */
const allSubstrings = (input) => {
    if (input.length === 1)
        return [[input]];
    let result = [];
    const start = input.substring(1);
    const suba = allSubstrings(start);
    suba.forEach(function (subresult) {
        let tmp = subresult.slice(0);
        tmp[0] = input.charAt(0) + tmp[0];
        result.push(tmp);
        tmp = subresult.slice(0);
        tmp.unshift(input.charAt(0));
        result.push(tmp);
    });
    return result;
};
//# sourceMappingURL=strings.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/constants.js"
/*!*******************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/constants.js ***!
  \*******************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   IS_MAC: () => (/* binding */ IS_MAC),
/* harmony export */   KEY_A: () => (/* binding */ KEY_A),
/* harmony export */   KEY_BACKSPACE: () => (/* binding */ KEY_BACKSPACE),
/* harmony export */   KEY_DELETE: () => (/* binding */ KEY_DELETE),
/* harmony export */   KEY_DOWN: () => (/* binding */ KEY_DOWN),
/* harmony export */   KEY_ESC: () => (/* binding */ KEY_ESC),
/* harmony export */   KEY_LEFT: () => (/* binding */ KEY_LEFT),
/* harmony export */   KEY_RETURN: () => (/* binding */ KEY_RETURN),
/* harmony export */   KEY_RIGHT: () => (/* binding */ KEY_RIGHT),
/* harmony export */   KEY_SHORTCUT: () => (/* binding */ KEY_SHORTCUT),
/* harmony export */   KEY_TAB: () => (/* binding */ KEY_TAB),
/* harmony export */   KEY_UP: () => (/* binding */ KEY_UP)
/* harmony export */ });
const KEY_A = 65;
const KEY_RETURN = 13;
const KEY_ESC = 27;
const KEY_LEFT = 37;
const KEY_UP = 38;
const KEY_RIGHT = 39;
const KEY_DOWN = 40;
const KEY_BACKSPACE = 8;
const KEY_DELETE = 46;
const KEY_TAB = 9;
const IS_MAC = typeof navigator === 'undefined' ? false : /Mac/.test(navigator.userAgent);
const KEY_SHORTCUT = IS_MAC ? 'metaKey' : 'ctrlKey'; // ctrl key or apple key for ma
//# sourceMappingURL=constants.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/contrib/highlight.js"
/*!***************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/contrib/highlight.js ***!
  \***************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   highlight: () => (/* binding */ highlight),
/* harmony export */   removeHighlight: () => (/* binding */ removeHighlight)
/* harmony export */ });
/* harmony import */ var _vanilla_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../vanilla.js */ "./node_modules/tom-select/dist/esm/vanilla.js");
/**
 * highlight v3 | MIT license | Johann Burkard <jb@eaio.com>
 * Highlights arbitrary terms in a node.
 *
 * - Modified by Marshal <beatgates@gmail.com> 2011-6-24 (added regex)
 * - Modified by Brian Reavis <brian@thirdroute.com> 2012-8-27 (cleanup)
 */

const highlight = (element, regex) => {
    if (regex === null)
        return;
    // convet string to regex
    if (typeof regex === 'string') {
        if (!regex.length)
            return;
        regex = new RegExp(regex, 'i');
    }
    // Wrap matching part of text node with highlighting <span>, e.g.
    // Soccer  ->  <span class="highlight">Soc</span>cer  for regex = /soc/i
    const highlightText = (node) => {
        var match = node.data.match(regex);
        if (match && node.data.length > 0) {
            var spannode = document.createElement('span');
            spannode.className = 'highlight';
            var middlebit = node.splitText(match.index);
            middlebit.splitText(match[0].length);
            var middleclone = middlebit.cloneNode(true);
            spannode.appendChild(middleclone);
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_0__.replaceNode)(middlebit, spannode);
            return 1;
        }
        return 0;
    };
    // Recurse element node, looking for child text nodes to highlight, unless element
    // is childless, <script>, <style>, or already highlighted: <span class="hightlight">
    const highlightChildren = (node) => {
        if (node.nodeType === 1 && node.childNodes && !/(script|style)/i.test(node.tagName) && (node.className !== 'highlight' || node.tagName !== 'SPAN')) {
            Array.from(node.childNodes).forEach(element => {
                highlightRecursive(element);
            });
        }
    };
    const highlightRecursive = (node) => {
        if (node.nodeType === 3) {
            return highlightText(node);
        }
        highlightChildren(node);
        return 0;
    };
    highlightRecursive(element);
};
/**
 * removeHighlight fn copied from highlight v5 and
 * edited to remove with(), pass js strict mode, and use without jquery
 */
const removeHighlight = (el) => {
    var elements = el.querySelectorAll("span.highlight");
    Array.prototype.forEach.call(elements, function (el) {
        var parent = el.parentNode;
        parent.replaceChild(el.firstChild, el);
        parent.normalize();
    });
};
//# sourceMappingURL=highlight.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/contrib/microevent.js"
/*!****************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/contrib/microevent.js ***!
  \****************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ MicroEvent)
/* harmony export */ });
/**
 * MicroEvent - to make any js object an event emitter
 *
 * - pure javascript - server compatible, browser compatible
 * - dont rely on the browser doms
 * - super simple - you get it immediatly, no mistery, no magic involved
 *
 * @author Jerome Etienne (https://github.com/jeromeetienne)
 */
/**
 * Execute callback for each event in space separated list of event names
 *
 */
function forEvents(events, callback) {
    events.split(/\s+/).forEach((event) => {
        callback(event);
    });
}
class MicroEvent {
    constructor() {
        this._events = {};
    }
    on(events, fct) {
        forEvents(events, (event) => {
            const event_array = this._events[event] || [];
            event_array.push(fct);
            this._events[event] = event_array;
        });
    }
    off(events, fct) {
        var n = arguments.length;
        if (n === 0) {
            this._events = {};
            return;
        }
        forEvents(events, (event) => {
            if (n === 1) {
                delete this._events[event];
                return;
            }
            const event_array = this._events[event];
            if (event_array === undefined)
                return;
            event_array.splice(event_array.indexOf(fct), 1);
            this._events[event] = event_array;
        });
    }
    trigger(events, ...args) {
        var self = this;
        forEvents(events, (event) => {
            const event_array = self._events[event];
            if (event_array === undefined)
                return;
            event_array.forEach(fct => {
                fct.apply(self, args);
            });
        });
    }
}
;
//# sourceMappingURL=microevent.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/contrib/microplugin.js"
/*!*****************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/contrib/microplugin.js ***!
  \*****************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ MicroPlugin)
/* harmony export */ });
/**
 * microplugin.js
 * Copyright (c) 2013 Brian Reavis & contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 * @author Brian Reavis <brian@thirdroute.com>
 */
function MicroPlugin(Interface) {
    Interface.plugins = {};
    return class extends Interface {
        constructor() {
            super(...arguments);
            this.plugins = {
                names: [],
                settings: {},
                requested: {},
                loaded: {}
            };
        }
        /**
         * Registers a plugin.
         *
         * @param {function} fn
         */
        static define(name, fn) {
            Interface.plugins[name] = {
                'name': name,
                'fn': fn
            };
        }
        /**
         * Initializes the listed plugins (with options).
         * Acceptable formats:
         *
         * List (without options):
         *   ['a', 'b', 'c']
         *
         * List (with options):
         *   [{'name': 'a', options: {}}, {'name': 'b', options: {}}]
         *
         * Hash (with options):
         *   {'a': { ... }, 'b': { ... }, 'c': { ... }}
         *
         * @param {array|object} plugins
         */
        initializePlugins(plugins) {
            var key, name;
            const self = this;
            const queue = [];
            if (Array.isArray(plugins)) {
                plugins.forEach((plugin) => {
                    if (typeof plugin === 'string') {
                        queue.push(plugin);
                    }
                    else {
                        self.plugins.settings[plugin.name] = plugin.options;
                        queue.push(plugin.name);
                    }
                });
            }
            else if (plugins) {
                for (key in plugins) {
                    if (plugins.hasOwnProperty(key)) {
                        self.plugins.settings[key] = plugins[key];
                        queue.push(key);
                    }
                }
            }
            while (name = queue.shift()) {
                self.require(name);
            }
        }
        loadPlugin(name) {
            var self = this;
            var plugins = self.plugins;
            var plugin = Interface.plugins[name];
            if (!Interface.plugins.hasOwnProperty(name)) {
                throw new Error('Unable to find "' + name + '" plugin');
            }
            plugins.requested[name] = true;
            plugins.loaded[name] = plugin.fn.apply(self, [self.plugins.settings[name] || {}]);
            plugins.names.push(name);
        }
        /**
         * Initializes a plugin.
         *
         */
        require(name) {
            var self = this;
            var plugins = self.plugins;
            if (!self.plugins.loaded.hasOwnProperty(name)) {
                if (plugins.requested[name]) {
                    throw new Error('Plugin has circular dependency ("' + name + '")');
                }
                self.loadPlugin(name);
            }
            return plugins.loaded[name];
        }
    };
}
//# sourceMappingURL=microplugin.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/defaults.js"
/*!******************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/defaults.js ***!
  \******************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
    options: [],
    optgroups: [],
    plugins: [],
    delimiter: ',',
    splitOn: null, // regexp or string for splitting up values from a paste command
    persist: true,
    diacritics: true,
    create: null,
    createOnBlur: false,
    createFilter: null,
    highlight: true,
    openOnFocus: true,
    shouldOpen: null,
    maxOptions: 50,
    maxItems: null,
    hideSelected: null,
    duplicates: false,
    addPrecedence: false,
    selectOnTab: false,
    preload: null,
    allowEmptyOption: false,
    //closeAfterSelect: false,
    refreshThrottle: 300,
    loadThrottle: 300,
    loadingClass: 'loading',
    dataAttr: null, //'data-data',
    optgroupField: 'optgroup',
    valueField: 'value',
    labelField: 'text',
    disabledField: 'disabled',
    optgroupLabelField: 'label',
    optgroupValueField: 'value',
    lockOptgroupOrder: false,
    sortField: '$order',
    searchField: ['text'],
    searchConjunction: 'and',
    mode: null,
    wrapperClass: 'ts-wrapper',
    controlClass: 'ts-control',
    dropdownClass: 'ts-dropdown',
    dropdownContentClass: 'ts-dropdown-content',
    itemClass: 'item',
    optionClass: 'option',
    dropdownParent: null,
    controlInput: '<input type="text" autocomplete="off" size="1" />',
    copyClassesToDropdown: false,
    placeholder: null,
    hidePlaceholder: null,
    shouldLoad: function (query) {
        return query.length > 0;
    },
    /*
    load                 : null, // function(query, callback) { ... }
    score                : null, // function(search) { ... }
    onInitialize         : null, // function() { ... }
    onChange             : null, // function(value) { ... }
    onItemAdd            : null, // function(value, $item) { ... }
    onItemRemove         : null, // function(value) { ... }
    onClear              : null, // function() { ... }
    onOptionAdd          : null, // function(value, data) { ... }
    onOptionRemove       : null, // function(value) { ... }
    onOptionClear        : null, // function() { ... }
    onOptionGroupAdd     : null, // function(id, data) { ... }
    onOptionGroupRemove  : null, // function(id) { ... }
    onOptionGroupClear   : null, // function() { ... }
    onDropdownOpen       : null, // function(dropdown) { ... }
    onDropdownClose      : null, // function(dropdown) { ... }
    onType               : null, // function(str) { ... }
    onDelete             : null, // function(values) { ... }
    */
    render: {
    /*
    item: null,
    optgroup: null,
    optgroup_header: null,
    option: null,
    option_create: null
    */
    }
});
//# sourceMappingURL=defaults.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/getSettings.js"
/*!*********************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/getSettings.js ***!
  \*********************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ getSettings)
/* harmony export */ });
/* harmony import */ var _defaults_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./defaults.js */ "./node_modules/tom-select/dist/esm/defaults.js");
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils.js */ "./node_modules/tom-select/dist/esm/utils.js");


function getSettings(input, settings_user) {
    var settings = Object.assign({}, _defaults_js__WEBPACK_IMPORTED_MODULE_0__["default"], settings_user);
    var attr_data = settings.dataAttr;
    var field_label = settings.labelField;
    var field_value = settings.valueField;
    var field_disabled = settings.disabledField;
    var field_optgroup = settings.optgroupField;
    var field_optgroup_label = settings.optgroupLabelField;
    var field_optgroup_value = settings.optgroupValueField;
    var tag_name = input.tagName.toLowerCase();
    var placeholder = input.getAttribute('placeholder') || input.getAttribute('data-placeholder');
    if (!placeholder && !settings.allowEmptyOption) {
        let option = input.querySelector('option[value=""]');
        if (option) {
            placeholder = option.textContent;
        }
    }
    var settings_element = {
        placeholder: placeholder,
        options: [],
        optgroups: [],
        items: [],
        maxItems: null,
    };
    /**
     * Initialize from a <select> element.
     *
     */
    var init_select = () => {
        var tagName;
        var options = settings_element.options;
        var optionsMap = {};
        var group_count = 1;
        let $order = 0;
        var readData = (el) => {
            var data = Object.assign({}, el.dataset); // get plain object from DOMStringMap
            var json = attr_data && data[attr_data];
            if (typeof json === 'string' && json.length) {
                data = Object.assign(data, JSON.parse(json));
            }
            return data;
        };
        var addOption = (option, group) => {
            var value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.hash_key)(option.value);
            if (value == null)
                return;
            if (!value && !settings.allowEmptyOption)
                return;
            // if the option already exists, it's probably been
            // duplicated in another optgroup. in this case, push
            // the current group to the "optgroup" property on the
            // existing option so that it's rendered in both places.
            if (optionsMap.hasOwnProperty(value)) {
                if (group) {
                    var arr = optionsMap[value][field_optgroup];
                    if (!arr) {
                        optionsMap[value][field_optgroup] = group;
                    }
                    else if (!Array.isArray(arr)) {
                        optionsMap[value][field_optgroup] = [arr, group];
                    }
                    else {
                        arr.push(group);
                    }
                }
            }
            else {
                var option_data = readData(option);
                option_data[field_label] = option_data[field_label] || option.textContent;
                option_data[field_value] = option_data[field_value] || value;
                option_data[field_disabled] = option_data[field_disabled] || option.disabled;
                option_data[field_optgroup] = option_data[field_optgroup] || group;
                option_data.$option = option;
                option_data.$order = option_data.$order || ++$order;
                optionsMap[value] = option_data;
                options.push(option_data);
            }
            if (option.selected) {
                settings_element.items.push(value);
            }
        };
        var addGroup = (optgroup) => {
            var id, optgroup_data;
            optgroup_data = readData(optgroup);
            optgroup_data[field_optgroup_label] = optgroup_data[field_optgroup_label] || optgroup.getAttribute('label') || '';
            optgroup_data[field_optgroup_value] = optgroup_data[field_optgroup_value] || group_count++;
            optgroup_data[field_disabled] = optgroup_data[field_disabled] || optgroup.disabled;
            optgroup_data.$order = optgroup_data.$order || ++$order;
            settings_element.optgroups.push(optgroup_data);
            id = optgroup_data[field_optgroup_value];
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.iterate)(optgroup.children, (option) => {
                addOption(option, id);
            });
        };
        settings_element.maxItems = input.hasAttribute('multiple') ? null : 1;
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.iterate)(input.children, (child) => {
            tagName = child.tagName.toLowerCase();
            if (tagName === 'optgroup') {
                addGroup(child);
            }
            else if (tagName === 'option') {
                addOption(child);
            }
        });
    };
    /**
     * Initialize from a <input type="text"> element.
     *
     */
    var init_textbox = () => {
        const data_raw = input.getAttribute(attr_data);
        if (!data_raw) {
            var value = input.value.trim() || '';
            if (!settings.allowEmptyOption && !value.length)
                return;
            const values = value.split(settings.delimiter);
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.iterate)(values, (value) => {
                const option = {};
                option[field_label] = value;
                option[field_value] = value;
                settings_element.options.push(option);
            });
            settings_element.items = values;
        }
        else {
            settings_element.options = JSON.parse(data_raw);
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.iterate)(settings_element.options, (opt) => {
                settings_element.items.push(opt[field_value]);
            });
        }
    };
    if (tag_name === 'select') {
        init_select();
    }
    else {
        init_textbox();
    }
    return Object.assign({}, _defaults_js__WEBPACK_IMPORTED_MODULE_0__["default"], settings_element, settings_user);
}
;
//# sourceMappingURL=getSettings.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/caret_position/plugin.js"
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/caret_position/plugin.js ***!
  \***************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Remove css classes
 *
 */
const removeClasses = (elmts, ...classes) => {
  var norm_classes = classesArray(classes);
  elmts = castAsArray(elmts);
  elmts.map(el => {
    norm_classes.map(cls => {
      el.classList.remove(cls);
    });
  });
};

/**
 * Return arguments
 *
 */
const classesArray = args => {
  var classes = [];
  iterate(args, _classes => {
    if (typeof _classes === 'string') {
      _classes = _classes.trim().split(/[\t\n\f\r\s]/);
    }
    if (Array.isArray(_classes)) {
      classes = classes.concat(_classes);
    }
  });
  return classes.filter(Boolean);
};

/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = arg => {
  if (!Array.isArray(arg)) {
    arg = [arg];
  }
  return arg;
};

/**
 * Get the index of an element amongst sibling nodes of the same type
 *
 */
const nodeIndex = (el, amongst) => {
  if (!el) return -1;
  amongst = amongst || el.nodeName;
  var i = 0;
  while (el = el.previousElementSibling) {
    if (el.matches(amongst)) {
      i++;
    }
  }
  return i;
};

/**
 * Plugin: "dropdown_input" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;

  /**
   * Moves the caret to the specified index.
   *
   * The input must be moved by leaving it in place and moving the
   * siblings, due to the fact that focus cannot be restored once lost
   * on mobile webkit devices
   *
   */
  self.hook('instead', 'setCaret', new_pos => {
    if (self.settings.mode === 'single' || !self.control.contains(self.control_input)) {
      new_pos = self.items.length;
    } else {
      new_pos = Math.max(0, Math.min(self.items.length, new_pos));
      if (new_pos != self.caretPos && !self.isPending) {
        self.controlChildren().forEach((child, j) => {
          if (j < new_pos) {
            self.control_input.insertAdjacentElement('beforebegin', child);
          } else {
            self.control.appendChild(child);
          }
        });
      }
    }
    self.caretPos = new_pos;
  });
  self.hook('instead', 'moveCaret', direction => {
    if (!self.isFocused) return;

    // move caret before or after selected items
    const last_active = self.getLastActive(direction);
    if (last_active) {
      const idx = nodeIndex(last_active);
      self.setCaret(direction > 0 ? idx + 1 : idx);
      self.setActiveItem();
      removeClasses(last_active, 'last-active');

      // move caret left or right of current position
    } else {
      self.setCaret(self.caretPos + direction);
    }
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/change_listener/plugin.js"
/*!****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/change_listener/plugin.js ***!
  \****************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Plugin: "change_listener" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  addEvent(this.input, 'change', () => {
    this.sync();
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/checkbox_options/plugin.js"
/*!*****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/checkbox_options/plugin.js ***!
  \*****************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */
const hash_key = value => {
  if (typeof value === 'undefined' || value === null) return null;
  return get_hash(value);
};
const get_hash = value => {
  if (typeof value === 'boolean') return value ? '1' : '0';
  return value + '';
};

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "checkbox_options" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  var self = this;
  var orig_onOptionSelect = self.onOptionSelect;
  self.settings.hideSelected = false;
  const cbOptions = Object.assign({
    // so that the user may add different ones as well
    className: "tomselect-checkbox",
    // the following default to the historic plugin's values
    checkedClassNames: undefined,
    uncheckedClassNames: undefined
  }, userOptions);
  var UpdateChecked = function UpdateChecked(checkbox, toCheck) {
    if (toCheck) {
      checkbox.checked = true;
      if (cbOptions.uncheckedClassNames) {
        checkbox.classList.remove(...cbOptions.uncheckedClassNames);
      }
      if (cbOptions.checkedClassNames) {
        checkbox.classList.add(...cbOptions.checkedClassNames);
      }
    } else {
      checkbox.checked = false;
      if (cbOptions.checkedClassNames) {
        checkbox.classList.remove(...cbOptions.checkedClassNames);
      }
      if (cbOptions.uncheckedClassNames) {
        checkbox.classList.add(...cbOptions.uncheckedClassNames);
      }
    }
  };

  // update the checkbox for an option
  var UpdateCheckbox = function UpdateCheckbox(option) {
    setTimeout(() => {
      var checkbox = option.querySelector('input.' + cbOptions.className);
      if (checkbox instanceof HTMLInputElement) {
        UpdateChecked(checkbox, option.classList.contains('selected'));
      }
    }, 1);
  };

  // add checkbox to option template
  self.hook('after', 'setupTemplates', () => {
    var orig_render_option = self.settings.render.option;
    self.settings.render.option = (data, escape_html) => {
      var rendered = getDom(orig_render_option.call(self, data, escape_html));
      var checkbox = document.createElement('input');
      if (cbOptions.className) {
        checkbox.classList.add(cbOptions.className);
      }
      checkbox.addEventListener('click', function (evt) {
        preventDefault(evt);
      });
      checkbox.type = 'checkbox';
      const hashed = hash_key(data[self.settings.valueField]);
      UpdateChecked(checkbox, !!(hashed && self.items.indexOf(hashed) > -1));
      rendered.prepend(checkbox);
      return rendered;
    };
  });

  // uncheck when item removed
  self.on('item_remove', value => {
    var option = self.getOption(value);
    if (option) {
      // if dropdown hasn't been opened yet, the option won't exist
      option.classList.remove('selected'); // selected class won't be removed yet
      UpdateCheckbox(option);
    }
  });

  // check when item added
  self.on('item_add', value => {
    var option = self.getOption(value);
    if (option) {
      // if dropdown hasn't been opened yet, the option won't exist
      UpdateCheckbox(option);
    }
  });

  // remove items when selected option is clicked
  self.hook('instead', 'onOptionSelect', (evt, option) => {
    if (option.classList.contains('selected')) {
      option.classList.remove('selected');
      self.removeItem(option.dataset.value);
      self.refreshOptions();
      preventDefault(evt, true);
      return;
    }
    orig_onOptionSelect.call(self, evt, option);
    UpdateCheckbox(option);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/clear_button/plugin.js"
/*!*************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/clear_button/plugin.js ***!
  \*************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "dropdown_header" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const self = this;
  const options = Object.assign({
    className: 'clear-button',
    title: 'Clear All',
    html: data => {
      return `<div class="${data.className}" title="${data.title}">&#10799;</div>`;
    }
  }, userOptions);
  self.on('initialize', () => {
    var button = getDom(options.html(options));
    button.addEventListener('click', evt => {
      if (self.isLocked) return;
      self.clear();
      if (self.settings.mode === 'single' && self.settings.allowEmptyOption) {
        self.addItem('');
      }
      evt.preventDefault();
      evt.stopPropagation();
    });
    self.control.appendChild(button);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/drag_drop/plugin.js"
/*!**********************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/drag_drop/plugin.js ***!
  \**********************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Set attributes of an element
 *
 */
const setAttr = (el, attrs) => {
  iterate(attrs, (val, attr) => {
    if (val == null) {
      el.removeAttribute(attr);
    } else {
      el.setAttribute(attr, '' + val);
    }
  });
};

/**
 * Plugin: "drag_drop" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

const insertAfter = (referenceNode, newNode) => {
  var _referenceNode$parent;
  (_referenceNode$parent = referenceNode.parentNode) == null || _referenceNode$parent.insertBefore(newNode, referenceNode.nextSibling);
};
const insertBefore = (referenceNode, newNode) => {
  var _referenceNode$parent2;
  (_referenceNode$parent2 = referenceNode.parentNode) == null || _referenceNode$parent2.insertBefore(newNode, referenceNode);
};
const isBefore = (referenceNode, newNode) => {
  do {
    var _newNode;
    newNode = (_newNode = newNode) == null ? void 0 : _newNode.previousElementSibling;
    if (referenceNode == newNode) {
      return true;
    }
  } while (newNode && newNode.previousElementSibling);
  return false;
};
function plugin () {
  var self = this;
  if (self.settings.mode !== 'multi') return;
  var orig_lock = self.lock;
  var orig_unlock = self.unlock;
  let sortable = true;
  let drag_item;

  /**
   * Add draggable attribute to item
   */
  self.hook('after', 'setupTemplates', () => {
    var orig_render_item = self.settings.render.item;
    self.settings.render.item = (data, escape) => {
      const item = getDom(orig_render_item.call(self, data, escape));
      setAttr(item, {
        'draggable': 'true'
      });

      // prevent doc_mousedown (see tom-select.ts)
      const mousedown = evt => {
        if (!sortable) preventDefault(evt);
        evt.stopPropagation();
      };
      const dragStart = evt => {
        drag_item = item;
        setTimeout(() => {
          item.classList.add('ts-dragging');
        }, 0);
      };
      const dragOver = evt => {
        evt.preventDefault();
        item.classList.add('ts-drag-over');
        moveitem(item, drag_item);
      };
      const dragLeave = () => {
        item.classList.remove('ts-drag-over');
      };
      const moveitem = (targetitem, dragitem) => {
        if (dragitem === undefined) return;
        if (isBefore(dragitem, item)) {
          insertAfter(targetitem, dragitem);
        } else {
          insertBefore(targetitem, dragitem);
        }
      };
      const dragend = () => {
        var _drag_item;
        document.querySelectorAll('.ts-drag-over').forEach(el => el.classList.remove('ts-drag-over'));
        (_drag_item = drag_item) == null || _drag_item.classList.remove('ts-dragging');
        drag_item = undefined;
        var values = [];
        self.control.querySelectorAll(`[data-value]`).forEach(el => {
          if (el.dataset.value) {
            let value = el.dataset.value;
            if (value) {
              values.push(value);
            }
          }
        });
        self.setValue(values);
      };
      addEvent(item, 'mousedown', mousedown);
      addEvent(item, 'dragstart', dragStart);
      addEvent(item, 'dragenter', dragOver);
      addEvent(item, 'dragover', dragOver);
      addEvent(item, 'dragleave', dragLeave);
      addEvent(item, 'dragend', dragend);
      return item;
    };
  });
  self.hook('instead', 'lock', () => {
    sortable = false;
    return orig_lock.call(self);
  });
  self.hook('instead', 'unlock', () => {
    sortable = true;
    return orig_unlock.call(self);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/dropdown_header/plugin.js"
/*!****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/dropdown_header/plugin.js ***!
  \****************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "dropdown_header" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const self = this;
  const options = Object.assign({
    title: 'Untitled',
    headerClass: 'dropdown-header',
    titleRowClass: 'dropdown-header-title',
    labelClass: 'dropdown-header-label',
    closeClass: 'dropdown-header-close',
    html: data => {
      return '<div class="' + data.headerClass + '">' + '<div class="' + data.titleRowClass + '">' + '<span class="' + data.labelClass + '">' + data.title + '</span>' + '<a class="' + data.closeClass + '">&times;</a>' + '</div>' + '</div>';
    }
  }, userOptions);
  self.on('initialize', () => {
    var header = getDom(options.html(options));
    var close_link = header.querySelector('.' + options.closeClass);
    if (close_link) {
      close_link.addEventListener('click', evt => {
        preventDefault(evt, true);
        self.close();
      });
    }
    self.dropdown.insertBefore(header, self.dropdown.firstChild);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/dropdown_input/plugin.js"
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/dropdown_input/plugin.js ***!
  \***************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

const KEY_ESC = 27;
const KEY_TAB = 9;
 // ctrl key or apple key for ma

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Add css classes
 *
 */
const addClasses = (elmts, ...classes) => {
  var norm_classes = classesArray(classes);
  elmts = castAsArray(elmts);
  elmts.map(el => {
    norm_classes.map(cls => {
      el.classList.add(cls);
    });
  });
};

/**
 * Return arguments
 *
 */
const classesArray = args => {
  var classes = [];
  iterate(args, _classes => {
    if (typeof _classes === 'string') {
      _classes = _classes.trim().split(/[\t\n\f\r\s]/);
    }
    if (Array.isArray(_classes)) {
      classes = classes.concat(_classes);
    }
  });
  return classes.filter(Boolean);
};

/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = arg => {
  if (!Array.isArray(arg)) {
    arg = [arg];
  }
  return arg;
};

/**
 * Plugin: "dropdown_input" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  const self = this;
  self.settings.shouldOpen = true; // make sure the input is shown even if there are no options to display in the dropdown

  self.hook('before', 'setup', () => {
    self.focus_node = self.control;
    addClasses(self.control_input, 'dropdown-input');
    const div = getDom('<div class="dropdown-input-wrap">');
    div.append(self.control_input);
    self.dropdown.insertBefore(div, self.dropdown.firstChild);

    // set a placeholder in the select control
    const placeholder = getDom('<input class="items-placeholder" tabindex="-1" />');
    placeholder.placeholder = self.settings.placeholder || '';
    self.control.append(placeholder);
  });
  self.on('initialize', () => {
    // set tabIndex on control to -1, otherwise [shift+tab] will put focus right back on control_input
    self.control_input.addEventListener('keydown', evt => {
      //addEvent(self.control_input,'keydown' as const,(evt:KeyboardEvent) =>{
      switch (evt.keyCode) {
        case KEY_ESC:
          if (self.isOpen) {
            preventDefault(evt, true);
            self.close();
          }
          self.clearActiveItems();
          return;
        case KEY_TAB:
          self.focus_node.tabIndex = -1;
          break;
      }
      return self.onKeyDown.call(self, evt);
    });
    self.on('blur', () => {
      self.focus_node.tabIndex = self.isDisabled ? -1 : self.tabIndex;
    });

    // give the control_input focus when the dropdown is open
    self.on('dropdown_open', () => {
      self.control_input.focus();
    });

    // prevent onBlur from closing when focus is on the control_input
    const orig_onBlur = self.onBlur;
    self.hook('instead', 'onBlur', evt => {
      if (evt && evt.relatedTarget == self.control_input) return;
      return orig_onBlur.call(self);
    });
    addEvent(self.control_input, 'blur', () => self.onBlur());

    // return focus to control to allow further keyboard input
    self.hook('before', 'close', () => {
      if (!self.isOpen) return;
      self.focus_node.focus({
        preventScroll: true
      });
    });
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/input_autogrow/plugin.js"
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/input_autogrow/plugin.js ***!
  \***************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Plugin: "input_autogrow" (Tom Select)
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;
  self.on('initialize', () => {
    var test_input = document.createElement('span');
    var control = self.control_input;
    test_input.style.cssText = 'position:absolute; top:-99999px; left:-99999px; width:auto; padding:0; white-space:pre; ';
    self.wrapper.appendChild(test_input);
    var transfer_styles = ['letterSpacing', 'fontSize', 'fontFamily', 'fontWeight', 'textTransform'];
    for (const style_name of transfer_styles) {
      // @ts-ignore TS7015 https://stackoverflow.com/a/50506154/697576
      test_input.style[style_name] = control.style[style_name];
    }

    /**
     * Set the control width
     *
     */
    var resize = () => {
      test_input.textContent = control.value;
      control.style.width = test_input.clientWidth + 'px';
    };
    resize();
    self.on('update item_add item_remove', resize);
    addEvent(control, 'input', resize);
    addEvent(control, 'keyup', resize);
    addEvent(control, 'blur', resize);
    addEvent(control, 'update', resize);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/no_active_items/plugin.js"
/*!****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/no_active_items/plugin.js ***!
  \****************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Plugin: "no_active_items" (Tom Select)
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  this.hook('instead', 'setActiveItem', () => {});
  this.hook('instead', 'selectAll', () => {});
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/no_backspace_delete/plugin.js"
/*!********************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/no_backspace_delete/plugin.js ***!
  \********************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Plugin: "input_autogrow" (Tom Select)
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;
  var orig_deleteSelection = self.deleteSelection;
  this.hook('instead', 'deleteSelection', evt => {
    if (self.activeItems.length) {
      return orig_deleteSelection.call(self, evt);
    }
    return false;
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/optgroup_columns/plugin.js"
/*!*****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/optgroup_columns/plugin.js ***!
  \*****************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

const KEY_LEFT = 37;
const KEY_RIGHT = 39;
 // ctrl key or apple key for ma

/**
 * Get the closest node to the evt.target matching the selector
 * Stops at wrapper
 *
 */
const parentMatch = (target, selector, wrapper) => {
  while (target && target.matches) {
    if (target.matches(selector)) {
      return target;
    }
    target = target.parentNode;
  }
};

/**
 * Get the index of an element amongst sibling nodes of the same type
 *
 */
const nodeIndex = (el, amongst) => {
  if (!el) return -1;
  amongst = amongst || el.nodeName;
  var i = 0;
  while (el = el.previousElementSibling) {
    if (el.matches(amongst)) {
      i++;
    }
  }
  return i;
};

/**
 * Plugin: "optgroup_columns" (Tom Select.js)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;
  var orig_keydown = self.onKeyDown;
  self.hook('instead', 'onKeyDown', evt => {
    var index, option, options, optgroup;
    if (!self.isOpen || !(evt.keyCode === KEY_LEFT || evt.keyCode === KEY_RIGHT)) {
      return orig_keydown.call(self, evt);
    }
    self.ignoreHover = true;
    optgroup = parentMatch(self.activeOption, '[data-group]');
    index = nodeIndex(self.activeOption, '[data-selectable]');
    if (!optgroup) {
      return;
    }
    if (evt.keyCode === KEY_LEFT) {
      optgroup = optgroup.previousSibling;
    } else {
      optgroup = optgroup.nextSibling;
    }
    if (!optgroup) {
      return;
    }
    options = optgroup.querySelectorAll('[data-selectable]');
    option = options[Math.min(options.length - 1, index)];
    if (option) {
      self.setActiveOption(option);
    }
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/remove_button/plugin.js"
/*!**************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/remove_button/plugin.js ***!
  \**************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Escapes a string for use within HTML.
 *
 */
const escape_html = str => {
  return (str + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
};

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "remove_button" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const options = Object.assign({
    label: '&times;',
    title: 'Remove',
    className: 'remove',
    append: true
  }, userOptions);

  //options.className = 'remove-single';
  var self = this;

  // override the render method to add remove button to each item
  if (!options.append) {
    return;
  }
  var html = '<a href="javascript:void(0)" class="' + options.className + '" tabindex="-1" title="' + escape_html(options.title) + '">' + options.label + '</a>';
  self.hook('after', 'setupTemplates', () => {
    var orig_render_item = self.settings.render.item;
    self.settings.render.item = (data, escape) => {
      var item = getDom(orig_render_item.call(self, data, escape));
      var close_button = getDom(html);
      item.appendChild(close_button);
      addEvent(close_button, 'mousedown', evt => {
        preventDefault(evt, true);
      });
      addEvent(close_button, 'click', evt => {
        if (self.isLocked) return;

        // propagating will trigger the dropdown to show for single mode
        preventDefault(evt, true);
        if (self.isLocked) return;
        if (!self.shouldDelete([item], evt)) return;
        self.removeItem(item);
        self.refreshOptions(false);
        self.inputState();
      });
      return item;
    };
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/restore_on_backspace/plugin.js"
/*!*********************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/restore_on_backspace/plugin.js ***!
  \*********************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Plugin: "restore_on_backspace" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const self = this;
  const options = Object.assign({
    text: option => {
      return option[self.settings.labelField];
    }
  }, userOptions);
  self.on('item_remove', function (value) {
    if (!self.isFocused) {
      return;
    }
    if (self.control_input.value.trim() === '') {
      var option = self.options[value];
      if (option) {
        self.setTextboxValue(options.text.call(self, option));
      }
    }
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/plugins/virtual_scroll/plugin.js"
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/virtual_scroll/plugin.js ***!
  \***************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.3
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Add css classes
 *
 */
const addClasses = (elmts, ...classes) => {
  var norm_classes = classesArray(classes);
  elmts = castAsArray(elmts);
  elmts.map(el => {
    norm_classes.map(cls => {
      el.classList.add(cls);
    });
  });
};

/**
 * Return arguments
 *
 */
const classesArray = args => {
  var classes = [];
  iterate(args, _classes => {
    if (typeof _classes === 'string') {
      _classes = _classes.trim().split(/[\t\n\f\r\s]/);
    }
    if (Array.isArray(_classes)) {
      classes = classes.concat(_classes);
    }
  });
  return classes.filter(Boolean);
};

/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = arg => {
  if (!Array.isArray(arg)) {
    arg = [arg];
  }
  return arg;
};

/**
 * Plugin: "restore_on_backspace" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  const self = this;
  const orig_canLoad = self.canLoad;
  const orig_clearActiveOption = self.clearActiveOption;
  const orig_loadCallback = self.loadCallback;
  var pagination = {};
  var dropdown_content;
  var loading_more = false;
  var load_more_opt;
  var default_values = [];
  if (!self.settings.shouldLoadMore) {
    // return true if additional results should be loaded
    self.settings.shouldLoadMore = () => {
      const scroll_percent = dropdown_content.clientHeight / (dropdown_content.scrollHeight - dropdown_content.scrollTop);
      if (scroll_percent > 0.9) {
        return true;
      }
      if (self.activeOption) {
        var selectable = self.selectable();
        var index = Array.from(selectable).indexOf(self.activeOption);
        if (index >= selectable.length - 2) {
          return true;
        }
      }
      return false;
    };
  }
  if (!self.settings.firstUrl) {
    throw 'virtual_scroll plugin requires a firstUrl() method';
  }

  // in order for virtual scrolling to work,
  // options need to be ordered the same way they're returned from the remote data source
  self.settings.sortField = [{
    field: '$order'
  }, {
    field: '$score'
  }];

  // can we load more results for given query?
  const canLoadMore = query => {
    if (typeof self.settings.maxOptions === 'number' && dropdown_content.children.length >= self.settings.maxOptions) {
      return false;
    }
    if (query in pagination && pagination[query]) {
      return true;
    }
    return false;
  };
  const clearFilter = (option, value) => {
    if (self.items.indexOf(value) >= 0 || default_values.indexOf(value) >= 0) {
      return true;
    }
    return false;
  };

  // set the next url that will be
  self.setNextUrl = (value, next_url) => {
    pagination[value] = next_url;
  };

  // getUrl() to be used in settings.load()
  self.getUrl = query => {
    if (query in pagination) {
      const next_url = pagination[query];
      pagination[query] = false;
      return next_url;
    }

    // if the user goes back to a previous query
    // we need to load the first page again
    self.clearPagination();
    return self.settings.firstUrl.call(self, query);
  };

  // clear pagination
  self.clearPagination = () => {
    pagination = {};
  };

  // don't clear the active option (and cause unwanted dropdown scroll)
  // while loading more results
  self.hook('instead', 'clearActiveOption', () => {
    if (loading_more) {
      return;
    }
    return orig_clearActiveOption.call(self);
  });

  // override the canLoad method
  self.hook('instead', 'canLoad', query => {
    // first time the query has been seen
    if (!(query in pagination)) {
      return orig_canLoad.call(self, query);
    }
    return canLoadMore(query);
  });

  // wrap the load
  self.hook('instead', 'loadCallback', (options, optgroups) => {
    if (!loading_more) {
      self.clearOptions(clearFilter);
    } else if (load_more_opt) {
      const first_option = options[0];
      if (first_option !== undefined) {
        load_more_opt.dataset.value = first_option[self.settings.valueField];
      }
    }
    orig_loadCallback.call(self, options, optgroups);
    loading_more = false;
  });

  // add templates to dropdown
  //	loading_more if we have another url in the queue
  //	no_more_results if we don't have another url in the queue
  self.hook('after', 'refreshOptions', () => {
    const query = self.lastValue;
    var option;
    if (canLoadMore(query)) {
      option = self.render('loading_more', {
        query: query
      });
      if (option) {
        option.setAttribute('data-selectable', ''); // so that navigating dropdown with [down] keypresses can navigate to this node
        load_more_opt = option;
      }
    } else if (query in pagination && !dropdown_content.querySelector('.no-results')) {
      option = self.render('no_more_results', {
        query: query
      });
    }
    if (option) {
      addClasses(option, self.settings.optionClass);
      dropdown_content.append(option);
    }
  });

  // add scroll listener and default templates
  self.on('initialize', () => {
    default_values = Object.keys(self.options);
    dropdown_content = self.dropdown_content;

    // default templates
    self.settings.render = Object.assign({}, {
      loading_more: () => {
        return `<div class="loading-more-results">Loading more results ... </div>`;
      },
      no_more_results: () => {
        return `<div class="no-more-results">No more results</div>`;
      }
    }, self.settings.render);

    // watch dropdown content scroll position
    dropdown_content.addEventListener('scroll', () => {
      if (!self.settings.shouldLoadMore.call(self)) {
        return;
      }

      // !important: this will get checked again in load() but we still need to check here otherwise loading_more will be set to true
      if (!canLoadMore(self.lastValue)) {
        return;
      }

      // don't call load() too much
      if (loading_more) return;
      loading_more = true;
      self.load.call(self, self.lastValue);
    });
  });
}


//# sourceMappingURL=plugin.js.map


/***/ },

/***/ "./node_modules/tom-select/dist/esm/tom-select.complete.js"
/*!*****************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/tom-select.complete.js ***!
  \*****************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _tom_select_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./tom-select.js */ "./node_modules/tom-select/dist/esm/tom-select.js");
/* harmony import */ var _plugins_change_listener_plugin_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./plugins/change_listener/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/change_listener/plugin.js");
/* harmony import */ var _plugins_checkbox_options_plugin_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./plugins/checkbox_options/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/checkbox_options/plugin.js");
/* harmony import */ var _plugins_clear_button_plugin_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./plugins/clear_button/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/clear_button/plugin.js");
/* harmony import */ var _plugins_drag_drop_plugin_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./plugins/drag_drop/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/drag_drop/plugin.js");
/* harmony import */ var _plugins_dropdown_header_plugin_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./plugins/dropdown_header/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/dropdown_header/plugin.js");
/* harmony import */ var _plugins_caret_position_plugin_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./plugins/caret_position/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/caret_position/plugin.js");
/* harmony import */ var _plugins_dropdown_input_plugin_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./plugins/dropdown_input/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/dropdown_input/plugin.js");
/* harmony import */ var _plugins_input_autogrow_plugin_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./plugins/input_autogrow/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/input_autogrow/plugin.js");
/* harmony import */ var _plugins_no_backspace_delete_plugin_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./plugins/no_backspace_delete/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/no_backspace_delete/plugin.js");
/* harmony import */ var _plugins_no_active_items_plugin_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./plugins/no_active_items/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/no_active_items/plugin.js");
/* harmony import */ var _plugins_optgroup_columns_plugin_js__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./plugins/optgroup_columns/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/optgroup_columns/plugin.js");
/* harmony import */ var _plugins_remove_button_plugin_js__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./plugins/remove_button/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/remove_button/plugin.js");
/* harmony import */ var _plugins_restore_on_backspace_plugin_js__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./plugins/restore_on_backspace/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/restore_on_backspace/plugin.js");
/* harmony import */ var _plugins_virtual_scroll_plugin_js__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./plugins/virtual_scroll/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/virtual_scroll/plugin.js");















_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('change_listener', _plugins_change_listener_plugin_js__WEBPACK_IMPORTED_MODULE_1__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('checkbox_options', _plugins_checkbox_options_plugin_js__WEBPACK_IMPORTED_MODULE_2__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('clear_button', _plugins_clear_button_plugin_js__WEBPACK_IMPORTED_MODULE_3__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('drag_drop', _plugins_drag_drop_plugin_js__WEBPACK_IMPORTED_MODULE_4__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('dropdown_header', _plugins_dropdown_header_plugin_js__WEBPACK_IMPORTED_MODULE_5__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('caret_position', _plugins_caret_position_plugin_js__WEBPACK_IMPORTED_MODULE_6__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('dropdown_input', _plugins_dropdown_input_plugin_js__WEBPACK_IMPORTED_MODULE_7__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('input_autogrow', _plugins_input_autogrow_plugin_js__WEBPACK_IMPORTED_MODULE_8__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('no_backspace_delete', _plugins_no_backspace_delete_plugin_js__WEBPACK_IMPORTED_MODULE_9__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('no_active_items', _plugins_no_active_items_plugin_js__WEBPACK_IMPORTED_MODULE_10__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('optgroup_columns', _plugins_optgroup_columns_plugin_js__WEBPACK_IMPORTED_MODULE_11__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('remove_button', _plugins_remove_button_plugin_js__WEBPACK_IMPORTED_MODULE_12__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('restore_on_backspace', _plugins_restore_on_backspace_plugin_js__WEBPACK_IMPORTED_MODULE_13__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('virtual_scroll', _plugins_virtual_scroll_plugin_js__WEBPACK_IMPORTED_MODULE_14__["default"]);
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"]);
//# sourceMappingURL=tom-select.complete.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/tom-select.js"
/*!********************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/tom-select.js ***!
  \********************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TomSelect)
/* harmony export */ });
/* harmony import */ var _contrib_microevent_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./contrib/microevent.js */ "./node_modules/tom-select/dist/esm/contrib/microevent.js");
/* harmony import */ var _contrib_microplugin_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./contrib/microplugin.js */ "./node_modules/tom-select/dist/esm/contrib/microplugin.js");
/* harmony import */ var _orchidjs_sifter__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @orchidjs/sifter */ "./node_modules/@orchidjs/sifter/dist/esm/sifter.js");
/* harmony import */ var _orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @orchidjs/unicode-variants */ "./node_modules/@orchidjs/unicode-variants/dist/esm/index.js");
/* harmony import */ var _contrib_highlight_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./contrib/highlight.js */ "./node_modules/tom-select/dist/esm/contrib/highlight.js");
/* harmony import */ var _constants_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./constants.js */ "./node_modules/tom-select/dist/esm/constants.js");
/* harmony import */ var _getSettings_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./getSettings.js */ "./node_modules/tom-select/dist/esm/getSettings.js");
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./utils.js */ "./node_modules/tom-select/dist/esm/utils.js");
/* harmony import */ var _vanilla_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./vanilla.js */ "./node_modules/tom-select/dist/esm/vanilla.js");









var instance_i = 0;
class TomSelect extends (0,_contrib_microplugin_js__WEBPACK_IMPORTED_MODULE_1__["default"])(_contrib_microevent_js__WEBPACK_IMPORTED_MODULE_0__["default"]) {
    constructor(input_arg, user_settings) {
        super();
        this.order = 0;
        this.isOpen = false;
        this.isDisabled = false;
        this.isReadOnly = false;
        this.isInvalid = false; // @deprecated 1.8
        this.isValid = true;
        this.isLocked = false;
        this.isFocused = false;
        this.isInputHidden = false;
        this.isSetup = false;
        this.ignoreFocus = false;
        this.ignoreHover = false;
        this.hasOptions = false;
        this.lastValue = '';
        this.caretPos = 0;
        this.loading = 0;
        this.loadedSearches = {};
        this.activeOption = null;
        this.activeItems = [];
        this.optgroups = {};
        this.options = {};
        this.userOptions = {};
        this.items = [];
        this.refreshTimeout = null;
        instance_i++;
        var dir;
        var input = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(input_arg);
        if (input.tomselect) {
            throw new Error('Tom Select already initialized on this element');
        }
        input.tomselect = this;
        // detect rtl environment
        var computedStyle = window.getComputedStyle && window.getComputedStyle(input, null);
        dir = computedStyle.getPropertyValue('direction');
        // setup default state
        const settings = (0,_getSettings_js__WEBPACK_IMPORTED_MODULE_6__["default"])(input, user_settings);
        this.settings = settings;
        this.input = input;
        this.tabIndex = input.tabIndex || 0;
        this.is_select_tag = input.tagName.toLowerCase() === 'select';
        this.rtl = /rtl/i.test(dir);
        this.inputId = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.getId)(input, 'tomselect-' + instance_i);
        this.isRequired = input.required;
        // search system
        this.sifter = new _orchidjs_sifter__WEBPACK_IMPORTED_MODULE_2__.Sifter(this.options, { diacritics: settings.diacritics });
        // option-dependent defaults
        settings.mode = settings.mode || (settings.maxItems === 1 ? 'single' : 'multi');
        if (typeof settings.hideSelected !== 'boolean') {
            settings.hideSelected = settings.mode === 'multi';
        }
        if (typeof settings.hidePlaceholder !== 'boolean') {
            settings.hidePlaceholder = settings.mode !== 'multi';
        }
        // set up createFilter callback
        var filter = settings.createFilter;
        if (typeof filter !== 'function') {
            if (typeof filter === 'string') {
                filter = new RegExp(filter);
            }
            if (filter instanceof RegExp) {
                settings.createFilter = (input) => filter.test(input);
            }
            else {
                settings.createFilter = (value) => {
                    return this.settings.duplicates || !this.options[value];
                };
            }
        }
        this.initializePlugins(settings.plugins);
        this.setupCallbacks();
        this.setupTemplates();
        // Create all elements
        const wrapper = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)('<div>');
        const control = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)('<div>');
        const dropdown = this._render('dropdown');
        const dropdown_content = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(`<div role="listbox" tabindex="-1">`);
        const classes = this.input.getAttribute('class') || '';
        const inputMode = settings.mode;
        var control_input;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(wrapper, settings.wrapperClass, classes, inputMode);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(control, settings.controlClass);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(wrapper, control);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(dropdown, settings.dropdownClass, inputMode);
        if (settings.copyClassesToDropdown) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(dropdown, classes);
        }
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(dropdown_content, settings.dropdownContentClass);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(dropdown, dropdown_content);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(settings.dropdownParent || wrapper).appendChild(dropdown);
        // default controlInput
        if ((0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.isHtmlString)(settings.controlInput)) {
            control_input = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(settings.controlInput);
            // set attributes
            var attrs = ['autocorrect', 'autocapitalize', 'autocomplete', 'spellcheck'];
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(attrs, (attr) => {
                if (input.getAttribute(attr)) {
                    (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(control_input, { [attr]: input.getAttribute(attr) });
                }
            });
            control_input.tabIndex = -1;
            control.appendChild(control_input);
            this.focus_node = control_input;
            // dom element
        }
        else if (settings.controlInput) {
            control_input = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(settings.controlInput);
            this.focus_node = control_input;
        }
        else {
            control_input = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)('<input/>');
            this.focus_node = control;
        }
        this.wrapper = wrapper;
        this.dropdown = dropdown;
        this.dropdown_content = dropdown_content;
        this.control = control;
        this.control_input = control_input;
        this.setup();
    }
    /**
     * set up event bindings.
     *
     */
    setup() {
        const self = this;
        const settings = self.settings;
        const control_input = self.control_input;
        const dropdown = self.dropdown;
        const dropdown_content = self.dropdown_content;
        const wrapper = self.wrapper;
        const control = self.control;
        const input = self.input;
        const focus_node = self.focus_node;
        const passive_event = { passive: true };
        const listboxId = self.inputId + '-ts-dropdown';
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(dropdown_content, {
            id: listboxId
        });
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(focus_node, {
            role: 'combobox',
            'aria-haspopup': 'listbox',
            'aria-expanded': 'false',
            'aria-controls': listboxId
        });
        const control_id = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.getId)(focus_node, self.inputId + '-ts-control');
        const query = "label[for='" + (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.escapeQuery)(self.inputId) + "']";
        const label = document.querySelector(query);
        const label_click = self.focus.bind(self);
        if (label) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(label, 'click', label_click);
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(label, { for: control_id });
            const label_id = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.getId)(label, self.inputId + '-ts-label');
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(focus_node, { 'aria-labelledby': label_id });
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(dropdown_content, { 'aria-labelledby': label_id });
        }
        wrapper.style.width = input.style.width;
        if (self.plugins.names.length) {
            const classes_plugins = 'plugin-' + self.plugins.names.join(' plugin-');
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)([wrapper, dropdown], classes_plugins);
        }
        if ((settings.maxItems === null || settings.maxItems > 1) && self.is_select_tag) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(input, { multiple: 'multiple' });
        }
        if (settings.placeholder) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(control_input, { placeholder: settings.placeholder });
        }
        // if splitOn was not passed in, construct it from the delimiter to allow pasting universally
        if (!settings.splitOn && settings.delimiter) {
            settings.splitOn = new RegExp('\\s*' + (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_3__.escape_regex)(settings.delimiter) + '+\\s*');
        }
        // debounce user defined load() if loadThrottle > 0
        // after initializePlugins() so plugins can create/modify user defined loaders
        if (settings.load && settings.loadThrottle) {
            settings.load = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.loadDebounce)(settings.load, settings.loadThrottle);
        }
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(dropdown, 'mousemove', () => {
            self.ignoreHover = false;
        });
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(dropdown, 'mouseenter', (e) => {
            var target_match = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.parentMatch)(e.target, '[data-selectable]', dropdown);
            if (target_match)
                self.onOptionHover(e, target_match);
        }, { capture: true });
        // clicking on an option should select it
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(dropdown, 'click', (evt) => {
            const option = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.parentMatch)(evt.target, '[data-selectable]');
            if (option) {
                self.onOptionSelect(evt, option);
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt, true);
            }
        });
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(control, 'click', (evt) => {
            var target_match = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.parentMatch)(evt.target, '[data-ts-item]', control);
            if (target_match && self.onItemSelect(evt, target_match)) {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt, true);
                return;
            }
            // retain focus (see control_input mousedown)
            if (control_input.value != '') {
                return;
            }
            self.onClick();
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt, true);
        });
        // keydown on focus_node for arrow_down/arrow_up
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(focus_node, 'keydown', (e) => self.onKeyDown(e));
        // keypress and input/keyup
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(control_input, 'keypress', (e) => self.onKeyPress(e));
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(control_input, 'input', (e) => self.onInput(e));
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(focus_node, 'blur', (e) => self.onBlur(e));
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(focus_node, 'focus', (e) => self.onFocus(e));
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(control_input, 'paste', (e) => self.onPaste(e));
        const doc_mousedown = (evt) => {
            // blur if target is outside of this instance
            // dropdown is not always inside wrapper
            const target = evt.composedPath()[0];
            if (!wrapper.contains(target) && !dropdown.contains(target)) {
                if (self.isFocused) {
                    self.blur();
                }
                self.inputState();
                return;
            }
            // retain focus by preventing native handling. if the
            // event target is the input it should not be modified.
            // otherwise, text selection within the input won't work.
            // Fixes bug #212 which is no covered by tests
            if (target == control_input && self.isOpen) {
                evt.stopPropagation();
                // clicking anywhere in the control should not blur the control_input (which would close the dropdown)
            }
            else {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt, true);
            }
        };
        const win_scroll = () => {
            if (self.isOpen) {
                self.positionDropdown();
            }
        };
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(document, 'mousedown', doc_mousedown);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(window, 'scroll', win_scroll, passive_event);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(window, 'resize', win_scroll, passive_event);
        this._destroy = () => {
            document.removeEventListener('mousedown', doc_mousedown);
            window.removeEventListener('scroll', win_scroll);
            window.removeEventListener('resize', win_scroll);
            if (label)
                label.removeEventListener('click', label_click);
        };
        // store original html and tab index so that they can be
        // restored when the destroy() method is called.
        this.revertSettings = {
            innerHTML: input.innerHTML,
            tabIndex: input.tabIndex
        };
        input.tabIndex = -1;
        input.insertAdjacentElement('afterend', self.wrapper);
        self.sync(false);
        settings.items = [];
        delete settings.optgroups;
        delete settings.options;
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(input, 'invalid', () => {
            if (self.isValid) {
                self.isValid = false;
                self.isInvalid = true;
                self.refreshState();
            }
        });
        self.updateOriginalInput();
        self.refreshItems();
        self.close(false);
        self.inputState();
        self.isSetup = true;
        if (input.disabled) {
            self.disable();
        }
        else if (input.readOnly) {
            self.setReadOnly(true);
        }
        else {
            self.enable(); //sets tabIndex
        }
        self.on('change', this.onChange);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(input, 'tomselected', 'ts-hidden-accessible');
        self.trigger('initialize');
        // preload options
        if (settings.preload === true) {
            self.preload();
        }
    }
    /**
     * Register options and optgroups
     *
     */
    setupOptions(options = [], optgroups = []) {
        // build options table
        this.addOptions(options);
        // build optgroup table
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(optgroups, (optgroup) => {
            this.registerOptionGroup(optgroup);
        });
    }
    /**
     * Sets up default rendering functions.
     */
    setupTemplates() {
        var self = this;
        var field_label = self.settings.labelField;
        var field_optgroup = self.settings.optgroupLabelField;
        var templates = {
            'optgroup': (data) => {
                let optgroup = document.createElement('div');
                optgroup.className = 'optgroup';
                optgroup.appendChild(data.options);
                return optgroup;
            },
            'optgroup_header': (data, escape) => {
                return '<div class="optgroup-header">' + escape(data[field_optgroup]) + '</div>';
            },
            'option': (data, escape) => {
                return '<div>' + escape(data[field_label]) + '</div>';
            },
            'item': (data, escape) => {
                return '<div>' + escape(data[field_label]) + '</div>';
            },
            'option_create': (data, escape) => {
                return '<div class="create">Add <strong>' + escape(data.input) + '</strong>&hellip;</div>';
            },
            'no_results': () => {
                return '<div class="no-results">No results found</div>';
            },
            'loading': () => {
                return '<div class="spinner"></div>';
            },
            'not_loading': () => { },
            'dropdown': () => {
                return '<div></div>';
            }
        };
        self.settings.render = Object.assign({}, templates, self.settings.render);
    }
    /**
     * Maps fired events to callbacks provided
     * in the settings used when creating the control.
     */
    setupCallbacks() {
        var key, fn;
        var callbacks = {
            'initialize': 'onInitialize',
            'change': 'onChange',
            'item_add': 'onItemAdd',
            'item_remove': 'onItemRemove',
            'item_select': 'onItemSelect',
            'clear': 'onClear',
            'option_add': 'onOptionAdd',
            'option_remove': 'onOptionRemove',
            'option_clear': 'onOptionClear',
            'optgroup_add': 'onOptionGroupAdd',
            'optgroup_remove': 'onOptionGroupRemove',
            'optgroup_clear': 'onOptionGroupClear',
            'dropdown_open': 'onDropdownOpen',
            'dropdown_close': 'onDropdownClose',
            'type': 'onType',
            'load': 'onLoad',
            'focus': 'onFocus',
            'blur': 'onBlur'
        };
        for (key in callbacks) {
            fn = this.settings[callbacks[key]];
            if (fn)
                this.on(key, fn);
        }
    }
    /**
     * Sync the Tom Select instance with the original input or select
     *
     */
    sync(get_settings = true) {
        const self = this;
        const settings = get_settings ? (0,_getSettings_js__WEBPACK_IMPORTED_MODULE_6__["default"])(self.input, { delimiter: self.settings.delimiter }) : self.settings;
        self.setupOptions(settings.options, settings.optgroups);
        self.setValue(settings.items || [], true); // silent prevents recursion
        self.lastQuery = null; // so updated options will be displayed in dropdown
    }
    /**
     * Triggered when the main control element
     * has a click event.
     *
     */
    onClick() {
        var self = this;
        if (self.activeItems.length > 0) {
            self.clearActiveItems();
            self.focus();
            return;
        }
        if (self.isFocused && self.isOpen) {
            self.blur();
        }
        else {
            self.focus();
        }
    }
    /**
     * @deprecated v1.7
     *
     */
    onMouseDown() { }
    /**
     * Triggered when the value of the control has been changed.
     * This should propagate the event to the original DOM
     * input / select element.
     */
    onChange() {
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.triggerEvent)(this.input, 'input');
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.triggerEvent)(this.input, 'change');
    }
    /**
     * Triggered on <input> paste.
     *
     */
    onPaste(e) {
        var self = this;
        if (self.isInputHidden || self.isLocked) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            return;
        }
        // If a regex or string is included, this will split the pasted
        // input and create Items for each separate value
        if (!self.settings.splitOn) {
            return;
        }
        // Wait for pasted text to be recognized in value
        setTimeout(() => {
            var pastedText = self.inputValue();
            if (!pastedText.match(self.settings.splitOn)) {
                return;
            }
            var splitInput = pastedText.trim().split(self.settings.splitOn);
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(splitInput, (piece) => {
                const hash = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(piece);
                if (hash) {
                    if (this.options[piece]) {
                        self.addItem(piece);
                    }
                    else {
                        self.createItem(piece);
                    }
                }
            });
        }, 0);
    }
    /**
     * Triggered on <input> keypress.
     *
     */
    onKeyPress(e) {
        var self = this;
        if (self.isLocked) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            return;
        }
        var character = String.fromCharCode(e.keyCode || e.which);
        if (self.settings.create && self.settings.mode === 'multi' && character === self.settings.delimiter) {
            self.createItem();
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            return;
        }
    }
    /**
     * Triggered on <input> keydown.
     *
     */
    onKeyDown(e) {
        var self = this;
        self.ignoreHover = true;
        if (self.isLocked) {
            if (e.keyCode !== _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_TAB) {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            }
            return;
        }
        switch (e.keyCode) {
            // ctrl+A: select all
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_A:
                if ((0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)(_constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_SHORTCUT, e)) {
                    if (self.control_input.value == '') {
                        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                        self.selectAll();
                        return;
                    }
                }
                break;
            // esc: close dropdown
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_ESC:
                if (self.isOpen) {
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e, true);
                    self.close();
                }
                self.clearActiveItems();
                return;
            // down: open dropdown or move selection down
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_DOWN:
                if (!self.isOpen && self.hasOptions) {
                    self.open();
                }
                else if (self.activeOption) {
                    let next = self.getAdjacent(self.activeOption, 1);
                    if (next)
                        self.setActiveOption(next);
                }
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                return;
            // up: move selection up
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_UP:
                if (self.activeOption) {
                    let prev = self.getAdjacent(self.activeOption, -1);
                    if (prev)
                        self.setActiveOption(prev);
                }
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                return;
            // return: select active option
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_RETURN:
                if (self.canSelect(self.activeOption)) {
                    self.onOptionSelect(e, self.activeOption);
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                    // if the option_create=null, the dropdown might be closed
                }
                else if (self.settings.create && self.createItem()) {
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                    // don't submit form when searching for a value
                }
                else if (document.activeElement == self.control_input && self.isOpen) {
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                }
                return;
            // left: modifiy item selection to the left
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_LEFT:
                self.advanceSelection(-1, e);
                return;
            // right: modifiy item selection to the right
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_RIGHT:
                self.advanceSelection(1, e);
                return;
            // tab: select active option and/or create item
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_TAB:
                if (self.settings.selectOnTab) {
                    if (self.canSelect(self.activeOption)) {
                        self.onOptionSelect(e, self.activeOption);
                        // prevent default [tab] behaviour of jump to the next field
                        // if select isFull, then the dropdown won't be open and [tab] will work normally
                        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                    }
                    if (self.settings.create && self.createItem()) {
                        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                    }
                }
                return;
            // delete|backspace: delete items
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_BACKSPACE:
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_DELETE:
                self.deleteSelection(e);
                return;
        }
        // don't enter text in the control_input when active items are selected
        if (self.isInputHidden && !(0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)(_constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_SHORTCUT, e)) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
        }
    }
    /**
     * Triggered on <input> keyup.
     *
     */
    onInput(e) {
        if (this.isLocked) {
            return;
        }
        const value = this.inputValue();
        if (this.lastValue === value)
            return;
        this.lastValue = value;
        if (value == '') {
            this._onInput();
            return;
        }
        if (this.refreshTimeout) {
            window.clearTimeout(this.refreshTimeout);
        }
        this.refreshTimeout = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.timeout)(() => {
            this.refreshTimeout = null;
            this._onInput();
        }, this.settings.refreshThrottle);
    }
    _onInput() {
        const value = this.lastValue;
        if (this.settings.shouldLoad.call(this, value)) {
            this.load(value);
        }
        this.refreshOptions();
        this.trigger('type', value);
    }
    /**
     * Triggered when the user rolls over
     * an option in the autocomplete dropdown menu.
     *
     */
    onOptionHover(evt, option) {
        if (this.ignoreHover)
            return;
        this.setActiveOption(option, false);
    }
    /**
     * Triggered on <input> focus.
     *
     */
    onFocus(e) {
        var self = this;
        var wasFocused = self.isFocused;
        if (self.isDisabled || self.isReadOnly) {
            self.blur();
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            return;
        }
        if (self.ignoreFocus)
            return;
        self.isFocused = true;
        if (self.settings.preload === 'focus')
            self.preload();
        if (!wasFocused)
            self.trigger('focus');
        if (!self.activeItems.length) {
            self.inputState();
            self.refreshOptions(!!self.settings.openOnFocus);
        }
        self.refreshState();
    }
    /**
     * Triggered on <input> blur.
     *
     */
    onBlur(e) {
        if (document.hasFocus() === false)
            return;
        var self = this;
        if (!self.isFocused)
            return;
        self.isFocused = false;
        self.ignoreFocus = false;
        var deactivate = () => {
            self.close();
            self.setActiveItem();
            self.setCaret(self.items.length);
            self.trigger('blur');
        };
        if (self.settings.create && self.settings.createOnBlur) {
            self.createItem(null, deactivate);
        }
        else {
            deactivate();
        }
    }
    /**
     * Triggered when the user clicks on an option
     * in the autocomplete dropdown menu.
     *
     */
    onOptionSelect(evt, option) {
        var value, self = this;
        // should not be possible to trigger a option under a disabled optgroup
        if (option.parentElement && option.parentElement.matches('[data-disabled]')) {
            return;
        }
        if (option.classList.contains('create')) {
            self.createItem(null, () => {
                if (self.settings.closeAfterSelect) {
                    self.close();
                }
            });
        }
        else {
            value = option.dataset.value;
            if (typeof value !== 'undefined') {
                self.lastQuery = null;
                self.addItem(value);
                if (self.settings.closeAfterSelect) {
                    self.close();
                }
                if (!self.settings.hideSelected && evt.type && /click/.test(evt.type)) {
                    self.setActiveOption(option);
                }
            }
        }
    }
    /**
     * Return true if the given option can be selected
     *
     */
    canSelect(option) {
        if (this.isOpen && option && this.dropdown_content.contains(option)) {
            return true;
        }
        return false;
    }
    /**
     * Triggered when the user clicks on an item
     * that has been selected.
     *
     */
    onItemSelect(evt, item) {
        var self = this;
        if (!self.isLocked && self.settings.mode === 'multi') {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt);
            self.setActiveItem(item, evt);
            return true;
        }
        return false;
    }
    /**
     * Determines whether or not to invoke
     * the user-provided option provider / loader
     *
     * Note, there is a subtle difference between
     * this.canLoad() and this.settings.shouldLoad();
     *
     *	- settings.shouldLoad() is a user-input validator.
     *	When false is returned, the not_loading template
     *	will be added to the dropdown
     *
     *	- canLoad() is lower level validator that checks
     * 	the Tom Select instance. There is no inherent user
     *	feedback when canLoad returns false
     *
     */
    canLoad(value) {
        if (!this.settings.load)
            return false;
        if (this.loadedSearches.hasOwnProperty(value))
            return false;
        return true;
    }
    /**
     * Invokes the user-provided option provider / loader.
     *
     */
    load(value) {
        const self = this;
        if (!self.canLoad(value))
            return;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(self.wrapper, self.settings.loadingClass);
        self.loading++;
        const callback = self.loadCallback.bind(self);
        self.settings.load.call(self, value, callback);
    }
    /**
     * Invoked by the user-provided option provider
     *
     */
    loadCallback(options, optgroups) {
        const self = this;
        self.loading = Math.max(self.loading - 1, 0);
        self.lastQuery = null;
        self.clearActiveOption(); // when new results load, focus should be on first option
        self.setupOptions(options, optgroups);
        self.refreshOptions(self.isFocused && !self.isInputHidden);
        if (!self.loading) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(self.wrapper, self.settings.loadingClass);
        }
        self.trigger('load', options, optgroups);
    }
    preload() {
        var classList = this.wrapper.classList;
        if (classList.contains('preloaded'))
            return;
        classList.add('preloaded');
        this.load('');
    }
    /**
     * Sets the input field of the control to the specified value.
     *
     */
    setTextboxValue(value = '') {
        var input = this.control_input;
        var changed = input.value !== value;
        if (changed) {
            input.value = value;
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.triggerEvent)(input, 'update');
            this.lastValue = value;
        }
    }
    /**
     * Returns the value of the control. If multiple items
     * can be selected (e.g. <select multiple>), this returns
     * an array. If only one item can be selected, this
     * returns a string.
     *
     */
    getValue() {
        if (this.is_select_tag && this.input.hasAttribute('multiple')) {
            return this.items;
        }
        return this.items.join(this.settings.delimiter);
    }
    /**
     * Resets the selected items to the given value.
     *
     */
    setValue(value, silent) {
        var events = silent ? [] : ['change'];
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.debounce_events)(this, events, () => {
            this.clear(silent);
            this.addItems(value, silent);
        });
    }
    /**
     * Resets the number of max items to the given value
     *
     */
    setMaxItems(value) {
        if (value === 0)
            value = null; //reset to unlimited items.
        this.settings.maxItems = value;
        this.refreshState();
    }
    /**
     * Sets the selected item.
     *
     */
    setActiveItem(item, e) {
        var self = this;
        var eventName;
        var i, begin, end, swap;
        var last;
        if (self.settings.mode === 'single')
            return;
        // clear the active selection
        if (!item) {
            self.clearActiveItems();
            if (self.isFocused) {
                self.inputState();
            }
            return;
        }
        // modify selection
        eventName = e && e.type.toLowerCase();
        if (eventName === 'click' && (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)('shiftKey', e) && self.activeItems.length) {
            last = self.getLastActive();
            begin = Array.prototype.indexOf.call(self.control.children, last);
            end = Array.prototype.indexOf.call(self.control.children, item);
            if (begin > end) {
                swap = begin;
                begin = end;
                end = swap;
            }
            for (i = begin; i <= end; i++) {
                item = self.control.children[i];
                if (self.activeItems.indexOf(item) === -1) {
                    self.setActiveItemClass(item);
                }
            }
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
        }
        else if ((eventName === 'click' && (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)(_constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_SHORTCUT, e)) || (eventName === 'keydown' && (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)('shiftKey', e))) {
            if (item.classList.contains('active')) {
                self.removeActiveItem(item);
            }
            else {
                self.setActiveItemClass(item);
            }
        }
        else {
            self.clearActiveItems();
            self.setActiveItemClass(item);
        }
        // ensure control has focus
        self.inputState();
        if (!self.isFocused) {
            self.focus();
        }
    }
    /**
     * Set the active and last-active classes
     *
     */
    setActiveItemClass(item) {
        const self = this;
        const last_active = self.control.querySelector('.last-active');
        if (last_active)
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(last_active, 'last-active');
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(item, 'active last-active');
        self.trigger('item_select', item);
        if (self.activeItems.indexOf(item) == -1) {
            self.activeItems.push(item);
        }
    }
    /**
     * Remove active item
     *
     */
    removeActiveItem(item) {
        var idx = this.activeItems.indexOf(item);
        this.activeItems.splice(idx, 1);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(item, 'active');
    }
    /**
     * Clears all the active items
     *
     */
    clearActiveItems() {
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(this.activeItems, 'active');
        this.activeItems = [];
    }
    /**
     * Sets the selected item in the dropdown menu
     * of available options.
     *
     */
    setActiveOption(option, scroll = true) {
        if (option === this.activeOption) {
            return;
        }
        this.clearActiveOption();
        if (!option)
            return;
        this.activeOption = option;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(this.focus_node, { 'aria-activedescendant': option.getAttribute('id') });
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(option, { 'aria-selected': 'true' });
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(option, 'active');
        if (scroll)
            this.scrollToOption(option);
    }
    /**
     * Sets the dropdown_content scrollTop to display the option
     *
     */
    scrollToOption(option, behavior) {
        if (!option)
            return;
        const content = this.dropdown_content;
        const height_menu = content.clientHeight;
        const scrollTop = content.scrollTop || 0;
        const height_item = option.offsetHeight;
        const y = option.getBoundingClientRect().top - content.getBoundingClientRect().top + scrollTop;
        if (y + height_item > height_menu + scrollTop) {
            this.scroll(y - height_menu + height_item, behavior);
        }
        else if (y < scrollTop) {
            this.scroll(y, behavior);
        }
    }
    /**
     * Scroll the dropdown to the given position
     *
     */
    scroll(scrollTop, behavior) {
        const content = this.dropdown_content;
        if (behavior) {
            content.style.scrollBehavior = behavior;
        }
        content.scrollTop = scrollTop;
        content.style.scrollBehavior = '';
    }
    /**
     * Clears the active option
     *
     */
    clearActiveOption() {
        if (this.activeOption) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(this.activeOption, 'active');
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(this.activeOption, { 'aria-selected': null });
        }
        this.activeOption = null;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(this.focus_node, { 'aria-activedescendant': null });
    }
    /**
     * Selects all items (CTRL + A).
     */
    selectAll() {
        const self = this;
        if (self.settings.mode === 'single')
            return;
        const activeItems = self.controlChildren();
        if (!activeItems.length)
            return;
        self.inputState();
        self.close();
        self.activeItems = activeItems;
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(activeItems, (item) => {
            self.setActiveItemClass(item);
        });
    }
    /**
     * Determines if the control_input should be in a hidden or visible state
     *
     */
    inputState() {
        var self = this;
        if (!self.control.contains(self.control_input))
            return;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(self.control_input, { placeholder: self.settings.placeholder });
        if (self.activeItems.length > 0 || (!self.isFocused && self.settings.hidePlaceholder && self.items.length > 0)) {
            self.setTextboxValue();
            self.isInputHidden = true;
        }
        else {
            if (self.settings.hidePlaceholder && self.items.length > 0) {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(self.control_input, { placeholder: '' });
            }
            self.isInputHidden = false;
        }
        self.wrapper.classList.toggle('input-hidden', self.isInputHidden);
    }
    /**
     * Get the input value
     */
    inputValue() {
        return this.control_input.value.trim();
    }
    /**
     * Gives the control focus.
     */
    focus() {
        var self = this;
        if (self.isDisabled || self.isReadOnly)
            return;
        self.ignoreFocus = true;
        if (self.control_input.offsetWidth) {
            self.control_input.focus();
        }
        else {
            self.focus_node.focus();
        }
        setTimeout(() => {
            self.ignoreFocus = false;
            self.onFocus();
        }, 0);
    }
    /**
     * Forces the control out of focus.
     *
     */
    blur() {
        this.focus_node.blur();
        this.onBlur();
    }
    /**
     * Returns a function that scores an object
     * to show how good of a match it is to the
     * provided query.
     *
     * @return {function}
     */
    getScoreFunction(query) {
        return this.sifter.getScoreFunction(query, this.getSearchOptions());
    }
    /**
     * Returns search options for sifter (the system
     * for scoring and sorting results).
     *
     * @see https://github.com/orchidjs/sifter.js
     * @return {object}
     */
    getSearchOptions() {
        var settings = this.settings;
        var sort = settings.sortField;
        if (typeof settings.sortField === 'string') {
            sort = [{ field: settings.sortField }];
        }
        return {
            fields: settings.searchField,
            conjunction: settings.searchConjunction,
            sort: sort,
            nesting: settings.nesting
        };
    }
    /**
     * Searches through available options and returns
     * a sorted array of matches.
     *
     */
    search(query) {
        var result, calculateScore;
        var self = this;
        var options = this.getSearchOptions();
        // validate user-provided result scoring function
        if (self.settings.score) {
            calculateScore = self.settings.score.call(self, query);
            if (typeof calculateScore !== 'function') {
                throw new Error('Tom Select "score" setting must be a function that returns a function');
            }
        }
        // perform search
        if (query !== self.lastQuery) {
            self.lastQuery = query;
            result = self.sifter.search(query, Object.assign(options, { score: calculateScore }));
            self.currentResults = result;
        }
        else {
            result = Object.assign({}, self.currentResults);
        }
        // filter out selected items
        if (self.settings.hideSelected) {
            result.items = result.items.filter((item) => {
                let hashed = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(item.id);
                return !(hashed && self.items.indexOf(hashed) !== -1);
            });
        }
        return result;
    }
    /**
     * Refreshes the list of available options shown
     * in the autocomplete dropdown menu.
     *
     */
    refreshOptions(triggerDropdown = true) {
        var i, j, k, n, optgroup, optgroups, html, has_create_option, active_group;
        var create;
        const groups = {};
        const groups_order = [];
        var self = this;
        var query = self.inputValue();
        const same_query = query === self.lastQuery || (query == '' && self.lastQuery == null);
        var results = self.search(query);
        var active_option = null;
        var show_dropdown = self.settings.shouldOpen || false;
        var dropdown_content = self.dropdown_content;
        if (same_query) {
            active_option = self.activeOption;
            if (active_option) {
                active_group = active_option.closest('[data-group]');
            }
        }
        // build markup
        n = results.items.length;
        if (typeof self.settings.maxOptions === 'number') {
            n = Math.min(n, self.settings.maxOptions);
        }
        if (n > 0) {
            show_dropdown = true;
        }
        // get fragment for group and the position of the group in group_order
        const getGroupFragment = (optgroup, order) => {
            let group_order_i = groups[optgroup];
            if (group_order_i !== undefined) {
                let order_group = groups_order[group_order_i];
                if (order_group !== undefined) {
                    return [group_order_i, order_group.fragment];
                }
            }
            let group_fragment = document.createDocumentFragment();
            group_order_i = groups_order.length;
            groups_order.push({ fragment: group_fragment, order, optgroup });
            return [group_order_i, group_fragment];
        };
        // render and group available options individually
        for (i = 0; i < n; i++) {
            // get option dom element
            let item = results.items[i];
            if (!item)
                continue;
            let opt_value = item.id;
            let option = self.options[opt_value];
            if (option === undefined)
                continue;
            let opt_hash = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.get_hash)(opt_value);
            let option_el = self.getOption(opt_hash, true);
            // toggle 'selected' class
            if (!self.settings.hideSelected) {
                option_el.classList.toggle('selected', self.items.includes(opt_hash));
            }
            optgroup = option[self.settings.optgroupField] || '';
            optgroups = Array.isArray(optgroup) ? optgroup : [optgroup];
            for (j = 0, k = optgroups && optgroups.length; j < k; j++) {
                optgroup = optgroups[j];
                let order = option.$order;
                let self_optgroup = self.optgroups[optgroup];
                if (self_optgroup === undefined) {
                    optgroup = '';
                }
                else {
                    order = self_optgroup.$order;
                }
                const [group_order_i, group_fragment] = getGroupFragment(optgroup, order);
                // nodes can only have one parent, so if the option is in mutple groups, we need a clone
                if (j > 0) {
                    option_el = option_el.cloneNode(true);
                    (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(option_el, { id: option.$id + '-clone-' + j, 'aria-selected': null });
                    option_el.classList.add('ts-cloned');
                    (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(option_el, 'active');
                    // make sure we keep the activeOption in the same group
                    if (self.activeOption && self.activeOption.dataset.value == opt_value) {
                        if (active_group && active_group.dataset.group === optgroup.toString()) {
                            active_option = option_el;
                        }
                    }
                }
                group_fragment.appendChild(option_el);
                if (optgroup != '') {
                    groups[optgroup] = group_order_i;
                }
            }
        }
        // sort optgroups
        if (self.settings.lockOptgroupOrder) {
            groups_order.sort((a, b) => {
                return a.order - b.order;
            });
        }
        // render optgroup headers & join groups
        html = document.createDocumentFragment();
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(groups_order, (group_order) => {
            let group_fragment = group_order.fragment;
            let optgroup = group_order.optgroup;
            if (!group_fragment || !group_fragment.children.length)
                return;
            let group_heading = self.optgroups[optgroup];
            if (group_heading !== undefined) {
                let group_options = document.createDocumentFragment();
                let header = self.render('optgroup_header', group_heading);
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(group_options, header);
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(group_options, group_fragment);
                let group_html = self.render('optgroup', { group: group_heading, options: group_options });
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(html, group_html);
            }
            else {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(html, group_fragment);
            }
        });
        dropdown_content.innerHTML = '';
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(dropdown_content, html);
        // highlight matching terms inline
        if (self.settings.highlight) {
            (0,_contrib_highlight_js__WEBPACK_IMPORTED_MODULE_4__.removeHighlight)(dropdown_content);
            if (results.query.length && results.tokens.length) {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(results.tokens, (tok) => {
                    (0,_contrib_highlight_js__WEBPACK_IMPORTED_MODULE_4__.highlight)(dropdown_content, tok.regex);
                });
            }
        }
        // helper method for adding templates to dropdown
        var add_template = (template) => {
            let content = self.render(template, { input: query });
            if (content) {
                show_dropdown = true;
                dropdown_content.insertBefore(content, dropdown_content.firstChild);
            }
            return content;
        };
        // add loading message
        if (self.loading) {
            add_template('loading');
            // invalid query
        }
        else if (!self.settings.shouldLoad.call(self, query)) {
            add_template('not_loading');
            // add no_results message
        }
        else if (results.items.length === 0) {
            add_template('no_results');
        }
        // add create option
        has_create_option = self.canCreate(query);
        if (has_create_option) {
            create = add_template('option_create');
        }
        // activate
        self.hasOptions = results.items.length > 0 || has_create_option;
        if (show_dropdown) {
            if (results.items.length > 0) {
                if (!active_option && self.settings.mode === 'single' && self.items[0] != undefined) {
                    active_option = self.getOption(self.items[0]);
                }
                if (!dropdown_content.contains(active_option)) {
                    let active_index = 0;
                    if (create && !self.settings.addPrecedence) {
                        active_index = 1;
                    }
                    active_option = self.selectable()[active_index];
                }
            }
            else if (create) {
                active_option = create;
            }
            if (triggerDropdown && !self.isOpen) {
                self.open();
                self.scrollToOption(active_option, 'auto');
            }
            self.setActiveOption(active_option);
        }
        else {
            self.clearActiveOption();
            if (triggerDropdown && self.isOpen) {
                self.close(false); // if create_option=null, we want the dropdown to close but not reset the textbox value
            }
        }
    }
    /**
     * Return list of selectable options
     *
     */
    selectable() {
        return this.dropdown_content.querySelectorAll('[data-selectable]');
    }
    /**
     * Adds an available option. If it already exists,
     * nothing will happen. Note: this does not refresh
     * the options list dropdown (use `refreshOptions`
     * for that).
     *
     * Usage:
     *
     *   this.addOption(data)
     *
     */
    addOption(data, user_created = false) {
        const self = this;
        // @deprecated 1.7.7
        // use addOptions( array, user_created ) for adding multiple options
        if (Array.isArray(data)) {
            self.addOptions(data, user_created);
            return false;
        }
        const key = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(data[self.settings.valueField]);
        if (key === null || self.options.hasOwnProperty(key)) {
            return false;
        }
        data.$order = data.$order || ++self.order;
        data.$id = self.inputId + '-opt-' + data.$order;
        self.options[key] = data;
        self.lastQuery = null;
        if (user_created) {
            self.userOptions[key] = user_created;
            self.trigger('option_add', key, data);
        }
        return key;
    }
    /**
     * Add multiple options
     *
     */
    addOptions(data, user_created = false) {
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(data, (dat) => {
            this.addOption(dat, user_created);
        });
    }
    /**
     * @deprecated 1.7.7
     */
    registerOption(data) {
        return this.addOption(data);
    }
    /**
     * Registers an option group to the pool of option groups.
     *
     * @return {boolean|string}
     */
    registerOptionGroup(data) {
        var key = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(data[this.settings.optgroupValueField]);
        if (key === null)
            return false;
        data.$order = data.$order || ++this.order;
        this.optgroups[key] = data;
        return key;
    }
    /**
     * Registers a new optgroup for options
     * to be bucketed into.
     *
     */
    addOptionGroup(id, data) {
        var hashed_id;
        data[this.settings.optgroupValueField] = id;
        if (hashed_id = this.registerOptionGroup(data)) {
            this.trigger('optgroup_add', hashed_id, data);
        }
    }
    /**
     * Removes an existing option group.
     *
     */
    removeOptionGroup(id) {
        if (this.optgroups.hasOwnProperty(id)) {
            delete this.optgroups[id];
            this.clearCache();
            this.trigger('optgroup_remove', id);
        }
    }
    /**
     * Clears all existing option groups.
     */
    clearOptionGroups() {
        this.optgroups = {};
        this.clearCache();
        this.trigger('optgroup_clear');
    }
    /**
     * Updates an option available for selection. If
     * it is visible in the selected items or options
     * dropdown, it will be re-rendered automatically.
     *
     */
    updateOption(value, data) {
        const self = this;
        var item_new;
        var index_item;
        const value_old = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(value);
        const value_new = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(data[self.settings.valueField]);
        // sanity checks
        if (value_old === null)
            return;
        const data_old = self.options[value_old];
        if (data_old == undefined)
            return;
        if (typeof value_new !== 'string')
            throw new Error('Value must be set in option data');
        const option = self.getOption(value_old);
        const item = self.getItem(value_old);
        data.$order = data.$order || data_old.$order;
        delete self.options[value_old];
        // invalidate render cache
        // don't remove existing node yet, we'll remove it after replacing it
        self.uncacheValue(value_new);
        self.options[value_new] = data;
        // update the option if it's in the dropdown
        if (option) {
            if (self.dropdown_content.contains(option)) {
                const option_new = self._render('option', data);
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.replaceNode)(option, option_new);
                if (self.activeOption === option) {
                    self.setActiveOption(option_new);
                }
            }
            option.remove();
        }
        // update the item if we have one
        if (item) {
            index_item = self.items.indexOf(value_old);
            if (index_item !== -1) {
                self.items.splice(index_item, 1, value_new);
            }
            item_new = self._render('item', data);
            if (item.classList.contains('active'))
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(item_new, 'active');
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.replaceNode)(item, item_new);
        }
        // invalidate last query because we might have updated the sortField
        self.lastQuery = null;
    }
    /**
     * Removes a single option.
     *
     */
    removeOption(value, silent) {
        const self = this;
        value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.get_hash)(value);
        self.uncacheValue(value);
        delete self.userOptions[value];
        delete self.options[value];
        self.lastQuery = null;
        self.trigger('option_remove', value);
        self.removeItem(value, silent);
    }
    /**
     * Clears all options.
     */
    clearOptions(filter) {
        const boundFilter = (filter || this.clearFilter).bind(this);
        this.loadedSearches = {};
        this.userOptions = {};
        this.clearCache();
        const selected = {};
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(this.options, (option, key) => {
            if (boundFilter(option, key)) {
                selected[key] = option;
            }
        });
        this.options = this.sifter.items = selected;
        this.lastQuery = null;
        this.trigger('option_clear');
    }
    /**
     * Used by clearOptions() to decide whether or not an option should be removed
     * Return true to keep an option, false to remove
     *
     */
    clearFilter(option, value) {
        if (this.items.indexOf(value) >= 0) {
            return true;
        }
        return false;
    }
    /**
     * Returns the dom element of the option
     * matching the given value.
     *
     */
    getOption(value, create = false) {
        const hashed = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(value);
        if (hashed === null)
            return null;
        const option = this.options[hashed];
        if (option != undefined) {
            if (option.$div) {
                return option.$div;
            }
            if (create) {
                return this._render('option', option);
            }
        }
        return null;
    }
    /**
     * Returns the dom element of the next or previous dom element of the same type
     * Note: adjacent options may not be adjacent DOM elements (optgroups)
     *
     */
    getAdjacent(option, direction, type = 'option') {
        var self = this, all;
        if (!option) {
            return null;
        }
        if (type == 'item') {
            all = self.controlChildren();
        }
        else {
            all = self.dropdown_content.querySelectorAll('[data-selectable]');
        }
        for (let i = 0; i < all.length; i++) {
            if (all[i] != option) {
                continue;
            }
            if (direction > 0) {
                return all[i + 1];
            }
            return all[i - 1];
        }
        return null;
    }
    /**
     * Returns the dom element of the item
     * matching the given value.
     *
     */
    getItem(item) {
        if (typeof item == 'object') {
            return item;
        }
        var value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(item);
        return value !== null
            ? this.control.querySelector(`[data-value="${(0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addSlashes)(value)}"]`)
            : null;
    }
    /**
     * "Selects" multiple items at once. Adds them to the list
     * at the current caret position.
     *
     */
    addItems(values, silent) {
        var self = this;
        var items = Array.isArray(values) ? values : [values];
        items = items.filter(x => self.items.indexOf(x) === -1);
        const last_item = items[items.length - 1];
        items.forEach(item => {
            self.isPending = (item !== last_item);
            self.addItem(item, silent);
        });
    }
    /**
     * "Selects" an item. Adds it to the list
     * at the current caret position.
     *
     */
    addItem(value, silent) {
        var events = silent ? [] : ['change', 'dropdown_close'];
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.debounce_events)(this, events, () => {
            var item, wasFull;
            const self = this;
            const inputMode = self.settings.mode;
            const hashed = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(value);
            if (hashed && self.items.indexOf(hashed) !== -1) {
                if (inputMode === 'single') {
                    self.close();
                }
                if (inputMode === 'single' || !self.settings.duplicates) {
                    return;
                }
            }
            if (hashed === null || !self.options.hasOwnProperty(hashed))
                return;
            if (inputMode === 'single')
                self.clear(silent);
            if (inputMode === 'multi' && self.isFull())
                return;
            item = self._render('item', self.options[hashed]);
            if (self.control.contains(item)) { // duplicates
                item = item.cloneNode(true);
            }
            wasFull = self.isFull();
            self.items.splice(self.caretPos, 0, hashed);
            self.insertAtCaret(item);
            if (self.isSetup) {
                // update menu / remove the option (if this is not one item being added as part of series)
                if (!self.isPending && self.settings.hideSelected) {
                    let option = self.getOption(hashed);
                    let next = self.getAdjacent(option, 1);
                    if (next) {
                        self.setActiveOption(next);
                    }
                }
                // refreshOptions after setActiveOption(),
                // otherwise setActiveOption() will be called by refreshOptions() with the wrong value
                if (!self.isPending && !self.settings.closeAfterSelect) {
                    self.refreshOptions(self.isFocused && inputMode !== 'single');
                }
                // hide the menu if the maximum number of items have been selected or no options are left
                if (self.settings.closeAfterSelect != false && self.isFull()) {
                    self.close();
                }
                else if (!self.isPending) {
                    self.positionDropdown();
                }
                self.trigger('item_add', hashed, item);
                if (!self.isPending) {
                    self.updateOriginalInput({ silent: silent });
                }
            }
            if (!self.isPending || (!wasFull && self.isFull())) {
                self.inputState();
                self.refreshState();
            }
        });
    }
    /**
     * Removes the selected item matching
     * the provided value.
     *
     */
    removeItem(item = null, silent) {
        const self = this;
        item = self.getItem(item);
        if (!item)
            return;
        var i, idx;
        const value = item.dataset.value;
        i = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.nodeIndex)(item);
        item.remove();
        if (item.classList.contains('active')) {
            idx = self.activeItems.indexOf(item);
            self.activeItems.splice(idx, 1);
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(item, 'active');
        }
        self.items.splice(i, 1);
        self.lastQuery = null;
        if (!self.settings.persist && self.userOptions.hasOwnProperty(value)) {
            self.removeOption(value, silent);
        }
        if (i < self.caretPos) {
            self.setCaret(self.caretPos - 1);
        }
        self.updateOriginalInput({ silent: silent });
        self.refreshState();
        self.positionDropdown();
        self.trigger('item_remove', value, item);
    }
    /**
     * Invokes the `create` method provided in the
     * TomSelect options that should provide the data
     * for the new item, given the user input.
     *
     * Once this completes, it will be added
     * to the item list.
     *
     */
    createItem(input = null, callback = () => { }) {
        // triggerDropdown parameter @deprecated 2.1.1
        if (arguments.length === 3) {
            callback = arguments[2];
        }
        if (typeof callback != 'function') {
            callback = () => { };
        }
        var self = this;
        var caret = self.caretPos;
        var output;
        input = input || self.inputValue();
        if (!self.canCreate(input)) {
            callback();
            return false;
        }
        self.lock();
        var created = false;
        var create = (data) => {
            self.unlock();
            if (!data || typeof data !== 'object')
                return callback();
            var value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(data[self.settings.valueField]);
            if (typeof value !== 'string') {
                return callback();
            }
            self.setTextboxValue();
            self.addOption(data, true);
            self.setCaret(caret);
            self.addItem(value);
            callback(data);
            created = true;
        };
        if (typeof self.settings.create === 'function') {
            output = self.settings.create.call(this, input, create);
        }
        else {
            output = {
                [self.settings.labelField]: input,
                [self.settings.valueField]: input,
            };
        }
        if (!created) {
            create(output);
        }
        return true;
    }
    /**
     * Re-renders the selected item lists.
     */
    refreshItems() {
        var self = this;
        self.lastQuery = null;
        if (self.isSetup) {
            self.addItems(self.items);
        }
        self.updateOriginalInput();
        self.refreshState();
    }
    /**
     * Updates all state-dependent attributes
     * and CSS classes.
     */
    refreshState() {
        const self = this;
        self.refreshValidityState();
        const isFull = self.isFull();
        const isLocked = self.isLocked;
        self.wrapper.classList.toggle('rtl', self.rtl);
        const wrap_classList = self.wrapper.classList;
        wrap_classList.toggle('focus', self.isFocused);
        wrap_classList.toggle('disabled', self.isDisabled);
        wrap_classList.toggle('readonly', self.isReadOnly);
        wrap_classList.toggle('required', self.isRequired);
        wrap_classList.toggle('invalid', !self.isValid);
        wrap_classList.toggle('locked', isLocked);
        wrap_classList.toggle('full', isFull);
        wrap_classList.toggle('input-active', self.isFocused && !self.isInputHidden);
        wrap_classList.toggle('dropdown-active', self.isOpen);
        wrap_classList.toggle('has-options', (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.isEmptyObject)(self.options));
        wrap_classList.toggle('has-items', self.items.length > 0);
    }
    /**
     * Update the `required` attribute of both input and control input.
     *
     * The `required` property needs to be activated on the control input
     * for the error to be displayed at the right place. `required` also
     * needs to be temporarily deactivated on the input since the input is
     * hidden and can't show errors.
     */
    refreshValidityState() {
        var self = this;
        if (!self.input.validity) {
            return;
        }
        self.isValid = self.input.validity.valid;
        self.isInvalid = !self.isValid;
    }
    /**
     * Determines whether or not more items can be added
     * to the control without exceeding the user-defined maximum.
     *
     * @returns {boolean}
     */
    isFull() {
        return this.settings.maxItems !== null && this.items.length >= this.settings.maxItems;
    }
    /**
     * Refreshes the original <select> or <input>
     * element to reflect the current state.
     *
     */
    updateOriginalInput(opts = {}) {
        const self = this;
        var option, label;
        const empty_option = self.input.querySelector('option[value=""]');
        if (self.is_select_tag) {
            const selected = [];
            const has_selected = self.input.querySelectorAll('option:checked').length;
            function AddSelected(option_el, value, label) {
                if (!option_el) {
                    option_el = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)('<option value="' + (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.escape_html)(value) + '">' + (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.escape_html)(label) + '</option>');
                }
                // don't move empty option from top of list
                // fixes bug in firefox https://bugzilla.mozilla.org/show_bug.cgi?id=1725293
                if (option_el != empty_option) {
                    self.input.append(option_el);
                }
                selected.push(option_el);
                // marking empty option as selected can break validation
                // fixes https://github.com/orchidjs/tom-select/issues/303
                if (option_el != empty_option || has_selected > 0) {
                    option_el.selected = true;
                }
                return option_el;
            }
            // unselect all selected options
            self.input.querySelectorAll('option:checked').forEach((option_el) => {
                option_el.selected = false;
            });
            // nothing selected?
            if (self.items.length == 0 && self.settings.mode == 'single') {
                AddSelected(empty_option, "", "");
                // order selected <option> tags for values in self.items
            }
            else {
                self.items.forEach((value) => {
                    option = self.options[value];
                    label = option[self.settings.labelField] || '';
                    if (selected.includes(option.$option)) {
                        const reuse_opt = self.input.querySelector(`option[value="${(0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addSlashes)(value)}"]:not(:checked)`);
                        AddSelected(reuse_opt, value, label);
                    }
                    else {
                        option.$option = AddSelected(option.$option, value, label);
                    }
                });
            }
        }
        else {
            self.input.value = self.getValue();
        }
        if (self.isSetup) {
            if (!opts.silent) {
                self.trigger('change', self.getValue());
            }
        }
    }
    /**
     * Shows the autocomplete dropdown containing
     * the available options.
     */
    open() {
        var self = this;
        if (self.isLocked || self.isOpen || (self.settings.mode === 'multi' && self.isFull()))
            return;
        self.isOpen = true;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(self.focus_node, { 'aria-expanded': 'true' });
        self.refreshState();
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.applyCSS)(self.dropdown, { visibility: 'hidden', display: 'block' });
        self.positionDropdown();
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.applyCSS)(self.dropdown, { visibility: 'visible', display: 'block' });
        self.focus();
        self.trigger('dropdown_open', self.dropdown);
    }
    /**
     * Closes the autocomplete dropdown menu.
     */
    close(setTextboxValue = true) {
        var self = this;
        var trigger = self.isOpen;
        if (setTextboxValue) {
            // before blur() to prevent form onchange event
            self.setTextboxValue();
            if (self.settings.mode === 'single' && self.items.length) {
                self.inputState();
            }
        }
        self.isOpen = false;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(self.focus_node, { 'aria-expanded': 'false' });
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.applyCSS)(self.dropdown, { display: 'none' });
        if (self.settings.hideSelected) {
            self.clearActiveOption();
        }
        self.refreshState();
        if (trigger)
            self.trigger('dropdown_close', self.dropdown);
    }
    /**
     * Calculates and applies the appropriate
     * position of the dropdown if dropdownParent = 'body'.
     * Otherwise, position is determined by css
     */
    positionDropdown() {
        if (this.settings.dropdownParent !== 'body') {
            return;
        }
        var context = this.control;
        var rect = context.getBoundingClientRect();
        var top = context.offsetHeight + rect.top + window.scrollY;
        var left = rect.left + window.scrollX;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.applyCSS)(this.dropdown, {
            width: rect.width + 'px',
            top: top + 'px',
            left: left + 'px'
        });
    }
    /**
     * Resets / clears all selected items
     * from the control.
     *
     */
    clear(silent) {
        var self = this;
        if (!self.items.length)
            return;
        var items = self.controlChildren();
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(items, (item) => {
            self.removeItem(item, true);
        });
        self.inputState();
        if (!silent)
            self.updateOriginalInput();
        self.trigger('clear');
    }
    /**
     * A helper method for inserting an element
     * at the current caret position.
     *
     */
    insertAtCaret(el) {
        const self = this;
        const caret = self.caretPos;
        const target = self.control;
        target.insertBefore(el, target.children[caret] || null);
        self.setCaret(caret + 1);
    }
    /**
     * Removes the current selected item(s).
     *
     */
    deleteSelection(e) {
        var direction, selection, caret, tail;
        var self = this;
        direction = (e && e.keyCode === _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_BACKSPACE) ? -1 : 1;
        selection = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.getSelection)(self.control_input);
        // determine items that will be removed
        const rm_items = [];
        if (self.activeItems.length) {
            tail = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getTail)(self.activeItems, direction);
            caret = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.nodeIndex)(tail);
            if (direction > 0) {
                caret++;
            }
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(self.activeItems, (item) => rm_items.push(item));
        }
        else if ((self.isFocused || self.settings.mode === 'single') && self.items.length) {
            const items = self.controlChildren();
            let rm_item;
            if (direction < 0 && selection.start === 0 && selection.length === 0) {
                rm_item = items[self.caretPos - 1];
            }
            else if (direction > 0 && selection.start === self.inputValue().length) {
                rm_item = items[self.caretPos];
            }
            if (rm_item !== undefined) {
                rm_items.push(rm_item);
            }
        }
        if (!self.shouldDelete(rm_items, e)) {
            return false;
        }
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e, true);
        // perform removal
        if (typeof caret !== 'undefined') {
            self.setCaret(caret);
        }
        while (rm_items.length) {
            self.removeItem(rm_items.pop());
        }
        self.inputState();
        self.positionDropdown();
        self.refreshOptions(false);
        return true;
    }
    /**
     * Return true if the items should be deleted
     */
    shouldDelete(items, evt) {
        const values = items.map(item => item.dataset.value);
        // allow the callback to abort
        if (!values.length || (typeof this.settings.onDelete === 'function' && this.settings.onDelete(values, evt) === false)) {
            return false;
        }
        return true;
    }
    /**
     * Selects the previous / next item (depending on the `direction` argument).
     *
     * > 0 - right
     * < 0 - left
     *
     */
    advanceSelection(direction, e) {
        var last_active, adjacent, self = this;
        if (self.rtl)
            direction *= -1;
        if (self.inputValue().length)
            return;
        // add or remove to active items
        if ((0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)(_constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_SHORTCUT, e) || (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)('shiftKey', e)) {
            last_active = self.getLastActive(direction);
            if (last_active) {
                if (!last_active.classList.contains('active')) {
                    adjacent = last_active;
                }
                else {
                    adjacent = self.getAdjacent(last_active, direction, 'item');
                }
                // if no active item, get items adjacent to the control input
            }
            else if (direction > 0) {
                adjacent = self.control_input.nextElementSibling;
            }
            else {
                adjacent = self.control_input.previousElementSibling;
            }
            if (adjacent) {
                if (adjacent.classList.contains('active')) {
                    self.removeActiveItem(last_active);
                }
                self.setActiveItemClass(adjacent); // mark as last_active !! after removeActiveItem() on last_active
            }
            // move caret to the left or right
        }
        else {
            self.moveCaret(direction);
        }
    }
    moveCaret(direction) { }
    /**
     * Get the last active item
     *
     */
    getLastActive(direction) {
        let last_active = this.control.querySelector('.last-active');
        if (last_active) {
            return last_active;
        }
        var result = this.control.querySelectorAll('.active');
        if (result) {
            return (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getTail)(result, direction);
        }
    }
    /**
     * Moves the caret to the specified index.
     *
     * The input must be moved by leaving it in place and moving the
     * siblings, due to the fact that focus cannot be restored once lost
     * on mobile webkit devices
     *
     */
    setCaret(new_pos) {
        this.caretPos = this.items.length;
    }
    /**
     * Return list of item dom elements
     *
     */
    controlChildren() {
        return Array.from(this.control.querySelectorAll('[data-ts-item]'));
    }
    /**
     * Disables user input on the control. Used while
     * items are being asynchronously created.
     */
    lock() {
        this.setLocked(true);
    }
    /**
     * Re-enables user input on the control.
     */
    unlock() {
        this.setLocked(false);
    }
    /**
     * Disable or enable user input on the control
     */
    setLocked(lock = this.isReadOnly || this.isDisabled) {
        this.isLocked = lock;
        this.refreshState();
    }
    /**
     * Disables user input on the control completely.
     * While disabled, it cannot receive focus.
     */
    disable() {
        this.setDisabled(true);
        this.close();
    }
    /**
     * Enables the control so that it can respond
     * to focus and user input.
     */
    enable() {
        this.setDisabled(false);
    }
    setDisabled(disabled) {
        this.focus_node.tabIndex = disabled ? -1 : this.tabIndex;
        this.isDisabled = disabled;
        this.input.disabled = disabled;
        this.control_input.disabled = disabled;
        this.setLocked();
    }
    setReadOnly(isReadOnly) {
        this.isReadOnly = isReadOnly;
        this.input.readOnly = isReadOnly;
        this.control_input.readOnly = isReadOnly;
        this.setLocked();
    }
    /**
     * Completely destroys the control and
     * unbinds all event listeners so that it can
     * be garbage collected.
     */
    destroy() {
        var self = this;
        var revertSettings = self.revertSettings;
        self.trigger('destroy');
        self.off();
        self.wrapper.remove();
        self.dropdown.remove();
        self.input.innerHTML = revertSettings.innerHTML;
        self.input.tabIndex = revertSettings.tabIndex;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(self.input, 'tomselected', 'ts-hidden-accessible');
        self._destroy();
        delete self.input.tomselect;
    }
    /**
     * A helper method for rendering "item" and
     * "option" templates, given the data.
     *
     */
    render(templateName, data) {
        var id, html;
        const self = this;
        if (typeof this.settings.render[templateName] !== 'function') {
            return null;
        }
        // render markup
        html = self.settings.render[templateName].call(this, data, _utils_js__WEBPACK_IMPORTED_MODULE_7__.escape_html);
        if (!html) {
            return null;
        }
        html = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(html);
        // add mandatory attributes
        if (templateName === 'option' || templateName === 'option_create') {
            if (data[self.settings.disabledField]) {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'aria-disabled': 'true' });
            }
            else {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-selectable': '' });
            }
        }
        else if (templateName === 'optgroup') {
            id = data.group[self.settings.optgroupValueField];
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-group': id });
            if (data.group[self.settings.disabledField]) {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-disabled': '' });
            }
        }
        if (templateName === 'option' || templateName === 'item') {
            const value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.get_hash)(data[self.settings.valueField]);
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-value': value });
            // make sure we have some classes if a template is overwritten
            if (templateName === 'item') {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(html, self.settings.itemClass);
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-ts-item': '' });
            }
            else {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(html, self.settings.optionClass);
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, {
                    role: 'option',
                    id: data.$id
                });
                // update cache
                data.$div = html;
                self.options[value] = data;
            }
        }
        return html;
    }
    /**
     * Type guarded rendering
     *
     */
    _render(templateName, data) {
        const html = this.render(templateName, data);
        if (html == null) {
            throw 'HTMLElement expected';
        }
        return html;
    }
    /**
     * Clears the render cache for a template. If
     * no template is given, clears all render
     * caches.
     *
     */
    clearCache() {
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(this.options, (option) => {
            if (option.$div) {
                option.$div.remove();
                delete option.$div;
            }
        });
    }
    /**
     * Removes a value from item and option caches
     *
     */
    uncacheValue(value) {
        const option_el = this.getOption(value);
        if (option_el)
            option_el.remove();
    }
    /**
     * Determines whether or not to display the
     * create item prompt, given a user input.
     *
     */
    canCreate(input) {
        return this.settings.create && (input.length > 0) && this.settings.createFilter.call(this, input);
    }
    /**
     * Wraps this.`method` so that `new_fn` can be invoked 'before', 'after', or 'instead' of the original method
     *
     * this.hook('instead','onKeyDown',function( arg1, arg2 ...){
     *
     * });
     */
    hook(when, method, new_fn) {
        var self = this;
        var orig_method = self[method];
        self[method] = function () {
            var result, result_new;
            if (when === 'after') {
                result = orig_method.apply(self, arguments);
            }
            result_new = new_fn.apply(self, arguments);
            if (when === 'instead') {
                return result_new;
            }
            if (when === 'before') {
                result = orig_method.apply(self, arguments);
            }
            return result;
        };
    }
}
;
//# sourceMappingURL=tom-select.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/utils.js"
/*!***************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/utils.js ***!
  \***************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addEvent: () => (/* binding */ addEvent),
/* harmony export */   addSlashes: () => (/* binding */ addSlashes),
/* harmony export */   append: () => (/* binding */ append),
/* harmony export */   debounce_events: () => (/* binding */ debounce_events),
/* harmony export */   escape_html: () => (/* binding */ escape_html),
/* harmony export */   getId: () => (/* binding */ getId),
/* harmony export */   getSelection: () => (/* binding */ getSelection),
/* harmony export */   get_hash: () => (/* binding */ get_hash),
/* harmony export */   hash_key: () => (/* binding */ hash_key),
/* harmony export */   isKeyDown: () => (/* binding */ isKeyDown),
/* harmony export */   iterate: () => (/* binding */ iterate),
/* harmony export */   loadDebounce: () => (/* binding */ loadDebounce),
/* harmony export */   preventDefault: () => (/* binding */ preventDefault),
/* harmony export */   timeout: () => (/* binding */ timeout)
/* harmony export */ });
/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */
const hash_key = (value) => {
    if (typeof value === 'undefined' || value === null)
        return null;
    return get_hash(value);
};
const get_hash = (value) => {
    if (typeof value === 'boolean')
        return value ? '1' : '0';
    return value + '';
};
/**
 * Escapes a string for use within HTML.
 *
 */
const escape_html = (str) => {
    return (str + '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
};
/**
 * use setTimeout if timeout > 0
 */
const timeout = (fn, timeout) => {
    if (timeout > 0) {
        return window.setTimeout(fn, timeout);
    }
    fn.call(null);
    return null;
};
/**
 * Debounce the user provided load function
 *
 */
const loadDebounce = (fn, delay) => {
    var timeout;
    return function (value, callback) {
        var self = this;
        if (timeout) {
            self.loading = Math.max(self.loading - 1, 0);
            clearTimeout(timeout);
        }
        timeout = setTimeout(function () {
            timeout = null;
            self.loadedSearches[value] = true;
            fn.call(self, value, callback);
        }, delay);
    };
};
/**
 * Debounce all fired events types listed in `types`
 * while executing the provided `fn`.
 *
 */
const debounce_events = (self, types, fn) => {
    var type;
    var trigger = self.trigger;
    var event_args = {};
    // override trigger method
    self.trigger = function () {
        var type = arguments[0];
        if (types.indexOf(type) !== -1) {
            event_args[type] = arguments;
        }
        else {
            return trigger.apply(self, arguments);
        }
    };
    // invoke provided function
    fn.apply(self, []);
    self.trigger = trigger;
    // trigger queued events
    for (type of types) {
        if (type in event_args) {
            trigger.apply(self, event_args[type]);
        }
    }
};
/**
 * Determines the current selection within a text input control.
 * Returns an object containing:
 *   - start
 *   - length
 *
 * Note: "selectionStart, selectionEnd ... apply only to inputs of types text, search, URL, tel and password"
 * 	- https://developer.mozilla.org/en-US/docs/Web/API/HTMLInputElement/setSelectionRange
 */
const getSelection = (input) => {
    return {
        start: input.selectionStart || 0,
        length: (input.selectionEnd || 0) - (input.selectionStart || 0),
    };
};
/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
    if (evt) {
        evt.preventDefault();
        if (stop) {
            evt.stopPropagation();
        }
    }
};
/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
    target.addEventListener(type, callback, options);
};
/**
 * Return true if the requested key is down
 * Will return false if more than one control character is pressed ( when [ctrl+shift+a] != [ctrl+a] )
 * The current evt may not always set ( eg calling advanceSelection() )
 *
 */
const isKeyDown = (key_name, evt) => {
    if (!evt) {
        return false;
    }
    if (!evt[key_name]) {
        return false;
    }
    var count = (evt.altKey ? 1 : 0) + (evt.ctrlKey ? 1 : 0) + (evt.shiftKey ? 1 : 0) + (evt.metaKey ? 1 : 0);
    if (count === 1) {
        return true;
    }
    return false;
};
/**
 * Get the id of an element
 * If the id attribute is not set, set the attribute with the given id
 *
 */
const getId = (el, id) => {
    const existing_id = el.getAttribute('id');
    if (existing_id) {
        return existing_id;
    }
    el.setAttribute('id', id);
    return id;
};
/**
 * Returns a string with backslashes added before characters that need to be escaped.
 */
const addSlashes = (str) => {
    return str.replace(/[\\"']/g, '\\$&');
};
/**
 *
 */
const append = (parent, node) => {
    if (node)
        parent.append(node);
};
/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
    if (Array.isArray(object)) {
        object.forEach(callback);
    }
    else {
        for (var key in object) {
            if (object.hasOwnProperty(key)) {
                callback(object[key], key);
            }
        }
    }
};
//# sourceMappingURL=utils.js.map

/***/ },

/***/ "./node_modules/tom-select/dist/esm/vanilla.js"
/*!*****************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/vanilla.js ***!
  \*****************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addClasses: () => (/* binding */ addClasses),
/* harmony export */   applyCSS: () => (/* binding */ applyCSS),
/* harmony export */   castAsArray: () => (/* binding */ castAsArray),
/* harmony export */   classesArray: () => (/* binding */ classesArray),
/* harmony export */   escapeQuery: () => (/* binding */ escapeQuery),
/* harmony export */   getDom: () => (/* binding */ getDom),
/* harmony export */   getTail: () => (/* binding */ getTail),
/* harmony export */   isEmptyObject: () => (/* binding */ isEmptyObject),
/* harmony export */   isHtmlString: () => (/* binding */ isHtmlString),
/* harmony export */   nodeIndex: () => (/* binding */ nodeIndex),
/* harmony export */   parentMatch: () => (/* binding */ parentMatch),
/* harmony export */   removeClasses: () => (/* binding */ removeClasses),
/* harmony export */   replaceNode: () => (/* binding */ replaceNode),
/* harmony export */   setAttr: () => (/* binding */ setAttr),
/* harmony export */   triggerEvent: () => (/* binding */ triggerEvent)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils.js */ "./node_modules/tom-select/dist/esm/utils.js");

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = (query) => {
    if (query.jquery) {
        return query[0];
    }
    if (query instanceof HTMLElement) {
        return query;
    }
    if (isHtmlString(query)) {
        var tpl = document.createElement('template');
        tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
        return tpl.content.firstChild;
    }
    return document.querySelector(query);
};
const isHtmlString = (arg) => {
    if (typeof arg === 'string' && arg.indexOf('<') > -1) {
        return true;
    }
    return false;
};
const escapeQuery = (query) => {
    return query.replace(/['"\\]/g, '\\$&');
};
/**
 * Dispatch an event
 *
 */
const triggerEvent = (dom_el, event_name) => {
    var event = document.createEvent('HTMLEvents');
    event.initEvent(event_name, true, false);
    dom_el.dispatchEvent(event);
};
/**
 * Apply CSS rules to a dom element
 *
 */
const applyCSS = (dom_el, css) => {
    Object.assign(dom_el.style, css);
};
/**
 * Add css classes
 *
 */
const addClasses = (elmts, ...classes) => {
    var norm_classes = classesArray(classes);
    elmts = castAsArray(elmts);
    elmts.map(el => {
        norm_classes.map(cls => {
            el.classList.add(cls);
        });
    });
};
/**
 * Remove css classes
 *
 */
const removeClasses = (elmts, ...classes) => {
    var norm_classes = classesArray(classes);
    elmts = castAsArray(elmts);
    elmts.map(el => {
        norm_classes.map(cls => {
            el.classList.remove(cls);
        });
    });
};
/**
 * Return arguments
 *
 */
const classesArray = (args) => {
    var classes = [];
    (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(args, (_classes) => {
        if (typeof _classes === 'string') {
            _classes = _classes.trim().split(/[\t\n\f\r\s]/);
        }
        if (Array.isArray(_classes)) {
            classes = classes.concat(_classes);
        }
    });
    return classes.filter(Boolean);
};
/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = (arg) => {
    if (!Array.isArray(arg)) {
        arg = [arg];
    }
    return arg;
};
/**
 * Get the closest node to the evt.target matching the selector
 * Stops at wrapper
 *
 */
const parentMatch = (target, selector, wrapper) => {
    if (wrapper && !wrapper.contains(target)) {
        return;
    }
    while (target && target.matches) {
        if (target.matches(selector)) {
            return target;
        }
        target = target.parentNode;
    }
};
/**
 * Get the first or last item from an array
 *
 * > 0 - right (last)
 * <= 0 - left (first)
 *
 */
const getTail = (list, direction = 0) => {
    if (direction > 0) {
        return list[list.length - 1];
    }
    return list[0];
};
/**
 * Return true if an object is empty
 *
 */
const isEmptyObject = (obj) => {
    return (Object.keys(obj).length === 0);
};
/**
 * Get the index of an element amongst sibling nodes of the same type
 *
 */
const nodeIndex = (el, amongst) => {
    if (!el)
        return -1;
    amongst = amongst || el.nodeName;
    var i = 0;
    while (el = el.previousElementSibling) {
        if (el.matches(amongst)) {
            i++;
        }
    }
    return i;
};
/**
 * Set attributes of an element
 *
 */
const setAttr = (el, attrs) => {
    (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(attrs, (val, attr) => {
        if (val == null) {
            el.removeAttribute(attr);
        }
        else {
            el.setAttribute(attr, '' + val);
        }
    });
};
/**
 * Replace a node
 */
const replaceNode = (existing, replacement) => {
    if (existing.parentNode)
        existing.parentNode.replaceChild(replacement, existing);
};
//# sourceMappingURL=vanilla.js.map

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			id: moduleId,
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/nonce */
/******/ 	(() => {
/******/ 		__webpack_require__.nc = undefined;
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!********************************************!*\
  !*** ./assets/src/js/admin/admin-order.js ***!
  \********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _order_export_invoice__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./order/export_invoice */ "./assets/src/js/admin/order/export_invoice.js");
/* harmony import */ var _order_add_courses_to_order__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./order/add-courses-to-order */ "./assets/src/js/admin/order/add-courses-to-order.js");
/* harmony import */ var _order_refund_order__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./order/refund-order */ "./assets/src/js/admin/order/refund-order.js");


//import modalSearchCourses from './order/modal-search-courses';


(0,_order_export_invoice__WEBPACK_IMPORTED_MODULE_0__["default"])();
(0,_order_add_courses_to_order__WEBPACK_IMPORTED_MODULE_1__["default"])();
(0,_order_refund_order__WEBPACK_IMPORTED_MODULE_2__["default"])();
})();

/******/ })()
;
//# sourceMappingURL=admin-order.js.map