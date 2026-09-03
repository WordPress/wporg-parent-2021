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
 * Key set on a `core/pattern` parsed block while its source blocks render.
 *
 * Pairs each depth increment with its own decrement. Core renders some inner
 * blocks without `render_block_data`, so a pattern reached that way never
 * carries the key and cannot pop a depth it never pushed.
 */
const MARKER = 'wporgPatternShortcodes';

/**
 * Track how many `core/pattern` blocks are currently rendering.
 *
 * @param int $delta Amount to add to the depth, or 0 to read it.
 *
 * @return int The current depth.
 */
function pattern_render_depth( int $delta = 0 ): int {
	static $depth = 0;

	$depth = max( 0, $depth + $delta );

	return $depth;
}

/**
 * Expand shortcodes in the source markup of the blocks that make up a pattern.
 *
 * Source, never rendered output: shortcode syntax survives `esc_html()`, so
 * parsing output would let an escaped post title reintroduce raw markup. The
 * cost is coverage — shortcodes in block attributes, split across blocks, or
 * below a `core/navigation`, `core/widget-group` or `core/gallery` (which core
 * renders without this filter) are left as authored.
 *
 * @param array $parsed_block The block being rendered.
 *
 * @return array The block, with shortcodes in its own markup expanded.
 */
function do_pattern_source_shortcodes( array $parsed_block ): array {
	if ( isset( $parsed_block['blockName'] ) && 'core/pattern' === $parsed_block['blockName'] ) {
		$parsed_block[ MARKER ] = true;
		pattern_render_depth( 1 );

		return $parsed_block;
	}

	// `the_content` expands shortcodes itself at priority 11; doing it here would run them before wpautop and wptexturize.
	if ( ! pattern_render_depth() || doing_filter( 'the_content' ) || empty( $parsed_block['innerContent'] ) ) {
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
 * Note that a `core/pattern` block has finished rendering.
 *
 * @param string|null $content      The pattern's rendered output.
 * @param array       $parsed_block The pattern block.
 *
 * @return string|null The content, unchanged.
 */
function end_pattern_render( ?string $content, array $parsed_block ): ?string {
	if ( ! empty( $parsed_block[ MARKER ] ) ) {
		pattern_render_depth( -1 );
	}

	return $content;
}
add_filter( 'render_block_core/pattern', __NAMESPACE__ . '\end_pattern_render', 10, 2 );
