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
	title: 'Column with Left Heading',
	scope: [ 'block', 'transform' ],
	attributes: { align: 'full', className: 'is-style-two-column-display' },
	innerBlocks: [
		[ 'core/column', {}, [ [ 'core/heading', {} ] ] ],
		[ 'core/column', {}, [ [ 'core/paragraph', {} ] ] ],
	],
	isActive: ( blockAttributes ) => blockAttributes.className?.includes( 'is-style-two-column-display' ),
} );
