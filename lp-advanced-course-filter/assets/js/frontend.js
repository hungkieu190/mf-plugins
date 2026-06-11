(function () {
	'use strict';

	function getWidgetInstance(wrapper) {
		var raw = wrapper.getAttribute('data-widget');
		var data;

		if (!raw) {
			return {};
		}

		try {
			data = JSON.parse(raw);
			if (data && typeof data.instance === 'string') {
				return JSON.parse(data.instance);
			}
		} catch (error) {
			return {};
		}

		return {};
	}

	function relocateHorizontalFilter(wrapper) {
		var instance = getWidgetInstance(wrapper);
		var selector = instance.class_list_courses_target || '.lp-list-courses-default';
		var target = document.querySelector(selector);

		if (!target || target.contains(wrapper)) {
			return;
		}

		if (wrapper.nextElementSibling === target) {
			return;
		}

		wrapper.classList.add('lp-acf--relocated');
		target.parentNode.insertBefore(wrapper, target);
	}

	function init() {
		document.querySelectorAll('.lp-acf--native.lp-acf--horizontal').forEach(relocateHorizontalFilter);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
