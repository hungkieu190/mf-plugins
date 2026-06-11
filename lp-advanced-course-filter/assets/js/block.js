(function (blocks, element, components, blockEditor, serverSideRender, i18n) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var CheckboxControl = components.CheckboxControl;
	var ServerSideRender = serverSideRender;
	var fieldOptions = [
		{ label: __('Keyword', 'lp-advanced-course-filter'), value: 'search' },
		{ label: __('Price', 'lp-advanced-course-filter'), value: 'price' },
		{ label: __('Course Category', 'lp-advanced-course-filter'), value: 'category' },
		{ label: __('Course Tag', 'lp-advanced-course-filter'), value: 'tag' },
		{ label: __('Author', 'lp-advanced-course-filter'), value: 'author' },
		{ label: __('Level', 'lp-advanced-course-filter'), value: 'level' },
		{ label: __('Type (Online/Offline)', 'lp-advanced-course-filter'), value: 'type' },
		{ label: __('Button Submit', 'lp-advanced-course-filter'), value: 'btn_submit' },
		{ label: __('Button Reset', 'lp-advanced-course-filter'), value: 'btn_reset' }
	];

	blocks.registerBlockType('lp-advanced-course-filter/filter', {
		title: __('Advanced Course Filter', 'lp-advanced-course-filter'),
		icon: 'filter',
		category: 'widgets',
		attributes: {
			layout: {
				type: 'string',
				default: 'sidebar'
			},
			fields: {
				type: 'array',
				default: ['search', 'price', 'category', 'tag', 'author', 'level', 'type', 'btn_submit', 'btn_reset']
			},
			categoryDepth: {
				type: 'number',
				default: 2
			},
			showInRest: {
				type: 'boolean',
				default: false
			},
			hideCountZero: {
				type: 'boolean',
				default: true
			},
			searchSuggestion: {
				type: 'boolean',
				default: true
			}
		},
		edit: function (props) {
			var fields = props.attributes.fields || [];

			return el(
				element.Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __('Filter settings', 'lp-advanced-course-filter'), initialOpen: true },
						el(SelectControl, {
							label: __('Layout', 'lp-advanced-course-filter'),
							value: props.attributes.layout,
							options: [
								{ label: __('Sidebar', 'lp-advanced-course-filter'), value: 'sidebar' },
								{ label: __('Horizontal', 'lp-advanced-course-filter'), value: 'horizontal' }
							],
							onChange: function (value) {
								props.setAttributes({ layout: value });
							}
						}),
						el(components.TextControl, {
							label: __('Category depth', 'lp-advanced-course-filter'),
							type: 'number',
							value: props.attributes.categoryDepth,
							onChange: function (value) {
								props.setAttributes({ categoryDepth: parseInt(value, 10) || 1 });
							}
						}),
						el(ToggleControl, {
							label: __('Load widget via REST', 'lp-advanced-course-filter'),
							checked: !!props.attributes.showInRest,
							onChange: function (value) {
								props.setAttributes({ showInRest: value });
							}
						}),
						el(ToggleControl, {
							label: __('Hide options with zero count', 'lp-advanced-course-filter'),
							checked: props.attributes.hideCountZero !== false,
							onChange: function (value) {
								props.setAttributes({ hideCountZero: value });
							}
						}),
						el(ToggleControl, {
							label: __('Enable keyword search suggestion', 'lp-advanced-course-filter'),
							checked: props.attributes.searchSuggestion !== false,
							onChange: function (value) {
								props.setAttributes({ searchSuggestion: value });
							}
						})
					),
					el(
						PanelBody,
						{ title: __('Fields', 'lp-advanced-course-filter'), initialOpen: false },
						fieldOptions.map(function (field) {
							return el(CheckboxControl, {
								key: field.value,
								label: field.label,
								checked: fields.indexOf(field.value) !== -1,
								onChange: function (checked) {
									var nextFields = fields.filter(function (value) {
										return value !== field.value;
									});
									if (checked) {
										nextFields.push(field.value);
									}
									props.setAttributes({ fields: nextFields });
								}
							});
						})
					)
				),
				el(ServerSideRender, {
					block: 'lp-advanced-course-filter/filter',
					attributes: props.attributes
				})
			);
		},
		save: function () {
			return null;
		}
	});
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor, window.wp.serverSideRender, window.wp.i18n);
