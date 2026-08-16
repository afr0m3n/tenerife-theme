( function ( blocks, blockEditor, element, i18n, ServerSideRender ) {
	'use strict';

	var el = element.createElement;

	blocks.registerBlockType( 'amarilla/featured-vehicles', {
		title: i18n.__( 'Doporučená vozidla', 'amarilla' ),
		description: i18n.__( 'Dynamická sekce tří doporučených vozidel pro úvodní stránku.', 'amarilla' ),
		category: 'theme',
		icon: 'car',
		supports: {
			html: false,
			align: false
		},
		edit: function () {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, { block: 'amarilla/featured-vehicles' } )
			);
		},
		save: function () {
			return null;
		}
	} );
}( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.i18n, window.wp.serverSideRender ) );
