(function () {
	'use strict';

	function serialize(wrapper, page) {
		var form = wrapper.querySelector('.lp-acf__filters');
		var sort = wrapper.querySelector('[name="orderby"]');
		var data = new FormData(form);

		data.append('action', 'lp_acf_filter_courses');
		data.append('nonce', LPACF.nonce);
		data.append('per_page', wrapper.dataset.perPage || '9');
		data.append('paged', page || '1');

		if (sort) {
			data.append('orderby', sort.value);
		}

		return data;
	}

	function setLoading(wrapper, loading) {
		wrapper.classList.toggle('is-loading', loading);
	}

	function renderActive(wrapper) {
		var active = wrapper.querySelector('.lp-acf__active');
		var controls = wrapper.querySelectorAll('.lp-acf__filters input:checked, .lp-acf__filters input[type="search"]');
		var html = [];

		controls.forEach(function (control) {
			if (control.type === 'search' && control.value.trim()) {
				html.push('<button type="button" data-name="search">' + escapeHtml(control.value.trim()) + '</button>');
			} else if ((control.type === 'checkbox' || control.type === 'radio') && control.value !== 'all' && control.value !== '0') {
				html.push('<button type="button" data-name="' + escapeHtml(control.name) + '" data-value="' + escapeHtml(control.value) + '">' + escapeHtml(control.dataset.label || control.value) + '</button>');
			}
		});

		active.innerHTML = html.join('');
	}

	function escapeHtml(value) {
		return String(value).replace(/[&<>"']/g, function (char) {
			return {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			}[char];
		});
	}

	function request(wrapper, page, append) {
		var results = wrapper.querySelector('.lp-acf__results');
		var summary = wrapper.querySelector('.lp-acf__summary');

		setLoading(wrapper, true);

		fetch(LPACF.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: serialize(wrapper, page)
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				if (!payload.success) {
					throw new Error('Request failed');
				}

				if (append) {
					var temp = document.createElement('div');
					var oldButton = results.querySelector('.lp-acf__load-more');
					temp.innerHTML = payload.data.html;
					if (oldButton) {
						oldButton.remove();
					}
					Array.prototype.slice.call(temp.children).forEach(function (child) {
						results.appendChild(child);
					});
					var nextButton = results.querySelector('.lp-acf__load-more');
					if (nextButton) {
						nextButton.dataset.page = String(page);
						if (page >= parseInt(nextButton.dataset.total, 10)) {
							nextButton.remove();
						}
					}
				} else {
					results.innerHTML = payload.data.html;
				}

				if (summary) {
					summary.textContent = payload.data.summary;
				}

				renderActive(wrapper);
			})
			.catch(function () {
				results.innerHTML = '<div class="lp-acf__no-results"><p>' + escapeHtml(LPACF.i18n.error) + '</p></div>';
			})
			.finally(function () {
				setLoading(wrapper, false);
			});
	}

	function debounce(fn, wait) {
		var timeout;
		return function () {
			var args = arguments;
			clearTimeout(timeout);
			timeout = setTimeout(function () {
				fn.apply(null, args);
			}, wait);
		};
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.lp-acf').forEach(function (wrapper) {
			var form = wrapper.querySelector('.lp-acf__filters');
			var sort = wrapper.querySelector('[name="orderby"]');
			var debounced = debounce(function () {
				request(wrapper, 1, false);
			}, 350);

			form.addEventListener('submit', function (event) {
				event.preventDefault();
				request(wrapper, 1, false);
			});

			form.addEventListener('change', function () {
				request(wrapper, 1, false);
			});

			form.addEventListener('input', function (event) {
				if (event.target.matches('input[type="search"]')) {
					debounced();
				}
			});

			form.addEventListener('reset', function () {
				setTimeout(function () {
					request(wrapper, 1, false);
				}, 0);
			});

			if (sort) {
				sort.addEventListener('change', function () {
					request(wrapper, 1, false);
				});
			}

			wrapper.addEventListener('click', function (event) {
				var activeButton = event.target.closest('.lp-acf__active button');
				var loadMore = event.target.closest('.lp-acf__load-more');

				if (activeButton) {
					var name = activeButton.dataset.name;
					var value = activeButton.dataset.value;
					if (name === 'search') {
						form.querySelector('[name="search"]').value = '';
					} else {
						var input = Array.prototype.slice.call(form.elements).find(function (element) {
							return element.name === name && element.value === value;
						});
						if (input) {
							input.checked = false;
						}
					}
					request(wrapper, 1, false);
				}

				if (loadMore) {
					var nextPage = parseInt(loadMore.dataset.page, 10) + 1;
					loadMore.dataset.page = String(nextPage);
					request(wrapper, nextPage, true);
				}
			});

			renderActive(wrapper);
		});
	});
})();
