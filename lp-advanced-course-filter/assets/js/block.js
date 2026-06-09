(function (blocks, element, components, blockEditor, serverSideRender) {
	'use strict';

	var el = element.createElement;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var RangeControl = components.RangeControl;
	var ServerSideRender = serverSideRender;

	blocks.registerBlockType('lp-advanced-course-filter/filter', {
		title: 'Advanced Course Filter',
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
						{ title: 'Filter settings', initialOpen: true },
						el(SelectControl, {
							label: 'Layout',
							value: props.attributes.layout,
							options: [
								{ label: 'Sidebar', value: 'sidebar' },
								{ label: 'Horizontal', value: 'horizontal' }
							],
							onChange: function (value) {
								props.setAttributes({ layout: value });
							}
						}),
						el(RangeControl, {
							label: 'Courses per page',
							value: props.attributes.perPage,
							min: 1,
							max: 48,
							onChange: function (value) {
								props.setAttributes({ perPage: value });
							}
						}),
						el(RangeControl, {
							label: 'Columns',
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
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor, window.wp.serverSideRender);
