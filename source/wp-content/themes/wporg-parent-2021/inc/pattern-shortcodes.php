<?php
/**
 * Pattern Shortcodes
 *
 * Expand shortcodes authored into a pattern, without exposing the values that
 * the pattern's blocks render to the shortcode parser.
 *
 * @package wporg-parent-2021
 */

declare( strict_types = 1 );

namespace WordPressdotorg\Theme\Parent_2021\Pattern_Shortcodes;

defined( 'ABSPATH' ) || die();

/**
 * Key holding the render depth to restore once a block has finished rendering.
 *
 * Stashing the depth on the block pairs every change with its own undo. Core
 * renders some inner blocks without `render_block_data`, so a block reached that
 * way carries no key and leaves the depth alone.
 */
const MARKER = 'wporgPatternShortcodes';

/**
 * Blocks that render block markup of their own, around a shortcode pass.
 *
 * `core/template-part` expands shortcodes before calling `do_blocks()`;
 * `core/post-content` leaves them to `the_content` at priority 11, after wpautop
 * and wptexturize. Expanding below either would be a second pass, over the first
 * one's output. Only core blocks are listed, so a block outside core that does
 * the same is not covered.
 */
const NESTED_CONTENT_BLOCKS = array(
	'core/post-content',
	'core/template-part',
);

/**
 * Read the current pattern render depth, or set it.
 *
 * @param int|null $set Depth to set, or null to read the current one.
 *
 * @return int The current depth.
 */
function pattern_render_depth( ?int $set = null ): int {
	static $depth = 0;

	if ( null !== $set ) {
		$depth = max( 0, $set );
	}

	return $depth;
}

/**
 * Expand shortcodes in the source markup of the blocks that make up a pattern.
 *
 * Source, never rendered output: shortcode syntax survives `esc_html()`, so
 * parsing output would let an escaped post title reintroduce raw markup. The
 * cost is coverage — shortcodes in block attributes, or below a
 * `core/navigation`, `core/widget-group` or `core/gallery` (which core renders
 * without this filter), are left as authored. So is an enclosing shortcode whose
 * halves land in different chunks of `innerContent`, which splits at every inner
 * block rather than only at block boundaries.
 *
 * @param array $parsed_block The block being rendered.
 *
 * @return array The block, with shortcodes in its own markup expanded.
 */
function do_pattern_source_shortcodes( array $parsed_block ): array {
	$block_name = isset( $parsed_block['blockName'] ) ? $parsed_block['blockName'] : '';

	if ( 'core/pattern' === $block_name ) {
		$parsed_block[ MARKER ] = pattern_render_depth();
		pattern_render_depth( pattern_render_depth() + 1 );

		return $parsed_block;
	}

	if ( in_array( $block_name, NESTED_CONTENT_BLOCKS, true ) ) {
		$parsed_block[ MARKER ] = pattern_render_depth();
		pattern_render_depth( 0 );

		return $parsed_block;
	}

	if ( ! pattern_render_depth() || empty( $parsed_block['innerContent'] ) ) {
		return $parsed_block;
	}

	// Only `innerContent` is rendered. `innerHTML` repeats the same text, so expanding it too would run every shortcode twice.
	foreach ( $parsed_block['innerContent'] as $index => $chunk ) {
		if ( is_string( $chunk ) ) {
			$parsed_block['innerContent'][ $index ] = do_shortcode( $chunk );
		}
	}

	return $parsed_block;
}
add_filter( 'render_block_data', __NAMESPACE__ . '\do_pattern_source_shortcodes' );

/**
 * Put the render depth back once a block that changed it has finished.
 *
 * @param string|null $content      The block's rendered output.
 * @param array       $parsed_block The block.
 *
 * @return string|null The content, unchanged.
 */
function restore_render_depth( ?string $content, array $parsed_block ): ?string {
	if ( isset( $parsed_block[ MARKER ] ) ) {
		pattern_render_depth( (int) $parsed_block[ MARKER ] );
	}

	return $content;
}
add_filter( 'render_block_core/pattern', __NAMESPACE__ . '\restore_render_depth', 10, 2 );
foreach ( NESTED_CONTENT_BLOCKS as $wporg_nested_block ) {
	add_filter( "render_block_{$wporg_nested_block}", __NAMESPACE__ . '\restore_render_depth', 10, 2 );
}
unset( $wporg_nested_block );
