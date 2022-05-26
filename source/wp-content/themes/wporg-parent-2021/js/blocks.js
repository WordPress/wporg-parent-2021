/**
 * Custom block-related functionality.
 *
 * Any custom block types should be added either as mu-plugins (if they'll apply to multiple sites), or to the
 * individual theme they're used in. This should only be used for styles & variations, which are tied directly
 * into the CSS of the theme.
 */

/**
 * Register the columns style as a variation too, so it's available as an option
 * when inserting the block, pre-filled with content.
 */
wp.blocks.registerBlockVariation( 'core/columns', {
	name: 'left-heading',
	icon: wp.element.createElement(
		'svg',
		{
			width: '48',
			height: '48',
			viewBox: '0 0 48 48',
			xmlns: 'http://www.w3.org/2000/svg',
		},
		[
			wp.element.createElement( 'path', {
				fillRule: 'evenodd',
				clipRule: 'evenodd',
				// eslint-disable-next-line id-length
				d:
					'M39 12C40.1046 12 41 12.8954 41 14V34C41 35.1046 40.1046 36 39 36H9C7.89543 36 7 35.1046 7 34V14C7 12.8954 7.89543 12 9 12H39ZM39 34V14H20V34H39ZM18 34H9V14H18V34Z',
			} ),
			wp.element.createElement( 'path', {
				stroke: 'currentColor',
				strokeWidth: '2px',
				strokeLinecap: 'round',
				d: 'M11 18h5M24 18h10m-10 4h12m-12 4h8', // eslint-disable-line id-length
			} ),
		]
	),
	title: 'Column with Left Heading',
	scope: [ 'block', 'transform' ],
	attributes: { align: 'full', className: 'is-style-two-column-display' },
	innerBlocks: [
		[ 'core/column', {}, [ [ 'core/heading', {} ] ] ],
		[ 'core/column', {}, [ [ 'core/paragraph', {} ] ] ],
	],
	isActive: ( blockAttributes ) => blockAttributes.className?.includes( 'is-style-two-column-display' ),
} );

/**
 * Add wide alignment support to the code block.
 */
wp.hooks.addFilter(
	'blocks.registerBlockType',
	'wporg/add-alignwide-support',
	( settings, name ) => {
		if ( name !== 'core/code' ) {
			return settings;
		}

		settings.supports = {
			...settings.supports,
			align: [ 'wide' ]
		};

		return settings;
	}
);
