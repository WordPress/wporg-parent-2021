<?php
/**
 * Pattern Shortcodes
 *
 * Expand shortcodes authored into a pattern, never the values its blocks render.
 *
 * @package wporg-parent-2021
 */

declare( strict_types = 1 );

namespace WordPressdotorg\Theme\Parent_2021\Pattern_Shortcodes;

defined( 'ABSPATH' ) || die();

/**
 * Key holding the depth to restore, stashed on the block that changed it.
 */
const MARKER = 'wporgPatternShortcodes';

/**
 * Core blocks that run their own shortcode pass over markup they hand to `do_blocks()`.
 *
 * Expanding below one of them would be a second pass, over the first one's output.
 */
const NESTED_CONTENT_BLOCKS = array(
	'core/latest-posts',
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
 * parsing output would let an escaped post title reintroduce raw markup. Left as
 * authored, then: shortcodes in block attributes, below a `core/navigation`,
 * `core/widget-group` or `core/gallery`, or split across `innerContent` chunks.
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

	// `core/shortcode` runs `wpautop()` over its own markup, so it is expanded afterwards instead.
	if ( 'core/shortcode' === $block_name || ! pattern_render_depth() || empty( $parsed_block['innerContent'] ) ) {
		return $parsed_block;
	}

	// `innerHTML` repeats this text but is never rendered; expanding both would run every shortcode twice.
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
/**
 * Expand the shortcode a `core/shortcode` block holds, once it has been wrapped.
 *
 * The block has no inner blocks, so its output is the pattern's own markup.
 *
 * @param string|null $content The block's rendered output.
 *
 * @return string|null The output, with its shortcode expanded.
 */
function do_shortcode_block( ?string $content ): ?string {
	if ( ! pattern_render_depth() || null === $content ) {
		return $content;
	}

	return do_shortcode( $content );
}
add_filter( 'render_block_core/shortcode', __NAMESPACE__ . '\do_shortcode_block' );

add_filter( 'render_block_core/pattern', __NAMESPACE__ . '\restore_render_depth', 10, 2 );
foreach ( NESTED_CONTENT_BLOCKS as $wporg_nested_block ) {
	add_filter( "render_block_{$wporg_nested_block}", __NAMESPACE__ . '\restore_render_depth', 10, 2 );
}
unset( $wporg_nested_block );
