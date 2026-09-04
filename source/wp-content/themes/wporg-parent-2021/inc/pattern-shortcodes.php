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
 * Render a pattern, expanding the shortcodes in its own markup.
 *
 * Mirrors core's `render_block_core_pattern()` with `do_shortcode()` moved ahead of
 * `do_blocks()`. A pattern's markup is authored, so its shortcodes are meant to run;
 * the post data its blocks pull in — a title, an excerpt, an author name — is not,
 * and never reaches the parser this way. A `core/shortcode` block is expanded before
 * it wraps its own markup in `wpautop()`, so multi-line inline output gains `<br />`.
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

	try {
		$content = do_blocks( do_shortcode( (string) $pattern['content'] ) );

		// Core autoembeds inside its own pattern callback, which this replaces.
		global $wp_embed;
		$content = $wp_embed->autoembed( $content );
	} finally {
		unset( $seen_refs[ $slug ] );
	}

	return $content;
}
