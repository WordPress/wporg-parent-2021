<?php
/**
 * Gutenberg/Core Tweaks
 *
 * This file contains workarounds for issues in WP core or gutenberg, that may be fixed in future updates.
 */

namespace WordPressdotorg\Theme\Parent_2021\Gutenberg_Tweaks;

use WP_Block_Supports, WP_Query;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'get_the_archive_title_prefix', __NAMESPACE__ . '\modify_archive_title_prefix' );
add_filter( 'render_block_data', __NAMESPACE__ . '\custom_query_block_attributes' );
add_action( 'parse_query', __NAMESPACE__ . '\compat_workaround_core_55100' );
add_filter( 'render_block_core/pattern', __NAMESPACE__ . '\convert_inline_style_to_rtl', 20 );
add_filter( 'render_block_core/query-title', __NAMESPACE__ . '\render_index_title', 10, 2 );
add_filter( 'render_block_core/post-author-name', __NAMESPACE__ . '\render_author_prefix', 10, 2 );

/**
 * Blank out the archive title prefix sometimes.
 *
 * We want the prefix when it's used in `query-title-banner`, but not in `local-header`.
 *
 * TODO This filter can be removed if/when this issue is resolved: https://github.com/WordPress/gutenberg/issues/30519
 *
 * @return string
 */
function modify_archive_title_prefix( $prefix ) {
	if ( is_category() || is_post_type_archive() ) {
		$prefix = '';
	}

	return $prefix;
}

/**
 * Support some additional pseudo-attributes for the wp:query block; notably category slugs.
 *
 * This could be removed if https://github.com/WordPress/gutenberg/issues/36785 is resolved.
 *
 * @param array $parsed_block The block being rendered.
 *
 * @return array
 */
function custom_query_block_attributes( $parsed_block ) {
	if ( 'core/query' === $parsed_block['blockName'] ) {
		// If the block has a `category` attribute, then find the corresponding cat ID and set the `categoryIds` attribute.
		// TODO: support multiple?
		if ( isset( $parsed_block['attrs']['query']['category'] ) ) {
			$category = get_category_by_slug( $parsed_block['attrs']['query']['category'] );
			if ( $category ) {
				$parsed_block['attrs']['query']['categoryIds'] = [ $category->term_id ];
			}
		}
		if ( isset( $parsed_block['attrs']['query']['tag'] ) ) {
			$tag = get_term_by( 'slug', $parsed_block['attrs']['query']['tag'], 'post_tag' );
			if ( $tag ) {
				$parsed_block['attrs']['query']['tagIds'] = [ $tag->term_id ];
			}
		}
	}

	return $parsed_block;
}

/**
 * Ensure that WP_Query::get_queried_object() works for /author/xxx requests.
 *
 * @see https://core.trac.wordpress.org/ticket/55100
 *
 * @param WP_Query $query The WP_Query instance.
 */
function compat_workaround_core_55100( $query ) {
	$author_name = $query->get( 'author_name' );
	if ( $author_name ) {
		$author = get_user_by( 'slug', $author_name );
		if ( $author ) {
			$query->set( 'author', $author->ID );
		}
	}
}

/**
 * Flip inline styles and class names when using RTL display.
 *
 * @param string $content Content of the current post.
 * @return string The updated content.
 */
function convert_inline_style_to_rtl( $content ) {
	if ( ! is_rtl() ) {
		return $content;
	}

	/*
	 * Replace initial item with "temp" so that we don't immediately replace it with the original direction.
	 *
	 * For example, the transform works like this:
	 * Start with border-left-color: red; border-right-color: blue;
	 *         -> border-temp-color: red; border-right-color: blue;
	 *         -> border-temp-color: red; border-left-color: blue;
	 *         -> border-right-color: red; border-left-color: blue;
	 */
	$replacements = [
		// Borders.
		'border-left'  => 'border-temp',
		'border-right' => 'border-left',
		'border-temp'  => 'border-right',
		// Padding
		'padding-left'  => 'padding-temp',
		'padding-right' => 'padding-left',
		'padding-temp'  => 'padding-right',
		// Margins.
		'margin-left'  => 'margin-temp',
		'margin-right' => 'margin-left',
		'margin-temp'  => 'margin-right',
		// Text alignment.
		'has-text-align-left'  => 'has-text-align-temp',
		'has-text-align-right' => 'has-text-align-left',
		'has-text-align-temp'  => 'has-text-align-right',
	];

	return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
}

/**
 * Replace the empty header when "type" is our custom "index".
 *
 * @param string   $content Content of the current post.
 * @param WP_Block $block
 * @return string The updated content.
 */
function render_index_title( $content, $block ) {
	if ( isset( $block['attrs']['type'] ) && 'index' === $block['attrs']['type'] ) {
		// Replace `></h` to capture empty tags of all heading levels.
		return str_replace( '></h', '>' . __( 'All posts', 'wporg' ) . '</h', $content );
	}
	return $content;
}

/**
 * Add the "by" string to the post author block in an i18n-friendly way.
 *
 * @param string   $content Content of the current block.
 * @param WP_Block $block
 * @return string The updated content.
 */
function render_author_prefix( $content, $block ) {
	if ( preg_match( '#(<div[^>]*>)(.*)(</div>)#', $content, $matches ) ) {
		// translators: %s is the author name.
		$content = $matches[1] . sprintf( __( 'By %s', 'wporg' ), $matches[2] ) . $matches[3];
	}
	return $content;
}
