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
 * Stands in for the opening bracket of a `core/shortcode` block's shortcode.
 *
 * That block wraps its markup in `wpautop()`, so its shortcode is expanded once it
 * has rendered rather than before, and has to survive the pass over the source.
 */
const SHORTCODE_BLOCK_GUARD = "\x02";

/**
 * Point `core/pattern` at a renderer that expands shortcodes before its blocks render.
 *
 * @param array  $args       Arguments the block type is being registered with.
 * @param string $block_type Block type name, including its namespace.
 *
 * @return array The arguments, pointed at this theme's renderer for `core/pattern`.
 */
function use_shortcode_aware_renderer( array $args, string $block_type ): array {
	if ( 'core/pattern' === $block_type ) {
		$args['render_callback'] = __NAMESPACE__ . '\render_pattern';
	}

	return $args;
}
add_filter( 'register_block_type_args', __NAMESPACE__ . '\use_shortcode_aware_renderer', 10, 2 );

/**
 * Track how many patterns are rendering, so nesting is handled.
 *
 * @param int $delta Amount to add to the depth, or 0 to read it.
 *
 * @return int The current depth.
 */
function pattern_depth( int $delta = 0 ): int {
	static $depth = 0;

	$depth = max( 0, $depth + $delta );

	return $depth;
}

/**
 * Render a pattern, expanding the shortcodes in its own markup.
 *
 * Mirrors core's `render_block_core_pattern()` with `do_shortcode()` moved ahead of
 * `do_blocks()`. A pattern's markup is authored, so its shortcodes are meant to run;
 * the post data its blocks pull in — a title, an excerpt, an author name — is not,
 * and never reaches the parser this way.
 *
 * @global \WP_Embed $wp_embed
 *
 * @param array $attributes Block attributes.
 *
 * @return string The rendered pattern.
 */
function render_pattern( array $attributes ): string {
	static $seen_refs = array();

	if ( empty( $attributes['slug'] ) ) {
		return '';
	}

	$slug     = $attributes['slug'];
	$registry = \WP_Block_Patterns_Registry::get_instance();

	if ( ! $registry->is_registered( $slug ) ) {
		return '';
	}

	// A pattern that references itself, directly or through another pattern.
	if ( isset( $seen_refs[ $slug ] ) ) {
		return WP_DEBUG && WP_DEBUG_DISPLAY
			? sprintf( 'Rendering halted for the pattern "%s", which references itself.', esc_html( $slug ) )
			: '';
	}

	$pattern            = $registry->get_registered( $slug );
	$seen_refs[ $slug ] = true;

	pattern_depth( 1 );

	try {
		$content = do_shortcode( guard_shortcode_blocks( (string) $pattern['content'] ) );
		$content = str_replace( SHORTCODE_BLOCK_GUARD, '[', $content );
		$content = do_blocks( $content );

		// Core autoembeds inside its own pattern callback, which this replaces.
		global $wp_embed;
		$content = $wp_embed->autoembed( $content );
	} finally {
		pattern_depth( -1 );
		unset( $seen_refs[ $slug ] );
	}

	return $content;
}

/**
 * Hide the shortcodes in a `core/shortcode` block from the pass over the source.
 *
 * @param string $content The pattern's markup.
 *
 * @return string The markup, with those blocks' opening brackets stood in for.
 */
function guard_shortcode_blocks( string $content ): string {
	return (string) preg_replace_callback(
		'#<!--\s+wp:shortcode\s+-->.*?<!--\s+/wp:shortcode\s+-->#s',
		function ( array $matches ): string {
			return str_replace( '[', SHORTCODE_BLOCK_GUARD, $matches[0] );
		},
		$content
	);
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
	if ( ! pattern_depth() || null === $content ) {
		return $content;
	}

	return do_shortcode( $content );
}
add_filter( 'render_block_core/shortcode', __NAMESPACE__ . '\do_shortcode_block' );
