(function (blocks, element, components, blockEditor, serverSideRender, i18n) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var RangeControl = components.RangeControl;
	var ServerSideRender = serverSideRender;

	blocks.registerBlockType('lp-advanced-course-filter/filter', {
		title: __('Advanced Course Filter', 'lp-advanced-course-filter'),
		icon: 'filter',
		category: 'widgets',
		attributes: {
			layout: {
				type: 'string',
				default: 'sidebar'
			},
			perPage: {
				type: 'number',
				default: 9
			},
			columns: {
				type: 'number',
				default: 3
			}
		},
		edit: function (props) {
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
						el(RangeControl, {
							label: __('Courses per page', 'lp-advanced-course-filter'),
							value: props.attributes.perPage,
							min: 1,
							max: 48,
							onChange: function (value) {
								props.setAttributes({ perPage: value });
							}
						}),
						el(RangeControl, {
							label: __('Columns', 'lp-advanced-course-filter'),
							value: props.attributes.columns,
							min: 1,
							max: 4,
							onChange: function (value) {
								props.setAttributes({ columns: value });
							}
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
