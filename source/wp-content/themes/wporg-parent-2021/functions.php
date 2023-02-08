<?php

namespace WordPressdotorg\Theme\Parent_2021;

use function WordPressdotorg\MU_Plugins\Global_Fonts\get_font_stylesheet_url;

defined( 'WPINC' ) || die();

require_once __DIR__ . '/inc/gutenberg-tweaks.php';
require_once __DIR__ . '/inc/block-styles.php';

/**
 * Actions and filters.
 */
add_action( 'after_setup_theme', __NAMESPACE__ . '\theme_support', 9 );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
add_filter( 'author_link', __NAMESPACE__ . '\use_wporg_profile_for_author_link', 10, 3 );
add_filter( 'render_block_core/pattern', __NAMESPACE__ . '\prevent_arrow_emoji', 20 );
add_filter( 'the_content', __NAMESPACE__ . '\prevent_arrow_emoji', 20 );
add_filter( 'wp_theme_json_data_theme', __NAMESPACE__ . '\merge_parent_child_theme_json' );

/**
 * Register theme support.
 */
function theme_support() {
	// Alignwide and alignfull classes in the block editor.
	add_theme_support( 'align-wide' );

	// Add support for responsive embedded content.
	// https://github.com/WordPress/gutenberg/issues/26901
	add_theme_support( 'responsive-embeds' );

	// Add support for editor styles.
	$suffix = is_rtl() ? '-rtl' : '';
	add_theme_support( 'editor-style' );
	add_editor_style( get_font_stylesheet_url() );
	add_editor_style( "/build/editor{$suffix}.css" );

	// Add support for post thumbnails.
	add_theme_support( 'post-thumbnails' );

	// Declare that there are no <title> tags and allow WordPress to provide them
	add_theme_support( 'title-tag' );

	// Experimental support for adding blocks inside nav menus
	add_theme_support( 'block-nav-menus' );

	// Remove the default margin-top added when the admin bar is used, this is
	// handled by the theme, in `_site-header.scss`.
	add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) );

	// This theme has one menu location.
	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'wporg' ),
		)
	);

	register_block_pattern_category(
		'wporg',
		array(
			'label' => __( 'WordPress.org', 'wporg' ),
		)
	);
}

/**
 * Enqueue scripts and styles.
 */
function enqueue_assets() {
	wp_enqueue_style(
		'wporg-parent-2021-style',
		get_template_directory_uri() . '/build/style.css',
		array( 'wporg-global-fonts' ),
		filemtime( __DIR__ . '/build/style.css' )
	);
	wp_style_add_data( 'wporg-parent-2021-style', 'rtl', 'replace' );
}

/**
 * Swap out the normal author archive link for the author's wp.org profile link.
 *
 * @param string $link            Overwritten.
 * @param int    $author_id       Unused.
 * @param string $author_nicename Used as the slug in the profiles URL.
 *
 * @return string
 */
function use_wporg_profile_for_author_link( $link, $author_id, $author_nicename ) {
	return sprintf(
		'https://profiles.wordpress.org/%s/',
		$author_nicename
	);
}

/**
 * Add the variation-selector unicode character to any arrow. This will force
 * the twemoji script to skip these characters, leaving them as text.
 *
 * Can be removed once the `wp-exclude-emoji` issue is fixed.
 * See https://core.trac.wordpress.org/ticket/52219.
 *
 * @param string $content Content of the current post.
 * @return string The updated content.
 */
function prevent_arrow_emoji( $content ) {
	return preg_replace( '/([←↑→↓↔↕↖↗↘↙])/u', '\1&#65038;', $content );
}

/**
 * Merge the child theme's theme.json into the parent theme.json.
 *
 * Pull the parent theme's values for array settings out and merge them by slug
 * with the values in the child theme. This prevents values in the child theme
 * from blowing away the parent theme's settings.
 *
 * Additional settings are merged correctly, since they're objects (and merged
 * by key).
 *
 * See https://github.com/WordPress/gutenberg/issues/40557.
 *
 * @param WP_Theme_JSON_Data $theme_json Parsed child theme.json.
 *
 * @return WP_Theme_JSON_Data The updated theme.json settings.
 */
function merge_parent_child_theme_json( $theme_json ) {
	// Nothing to merge if this is not a child theme.
	if ( get_template_directory() === get_stylesheet_directory() ) {
		return $theme_json;
	}

	// Build a new theme.json object.
	$new_data = array(
		'version' => 2,
	);

	$child_theme = $theme_json->get_data();

	if ( ! empty( $child_theme['settings'] ) ) {
		$parent_theme_json_file = get_template_directory() . '/theme.json';
		$parent_theme_json_data = wp_json_file_decode( $parent_theme_json_file, array( 'associative' => true ) );
		$parent_theme           = new \WP_Theme_JSON_Gutenberg( $parent_theme_json_data );

		// Get base theme.json settings.
		$parent_settings = $parent_theme->get_settings();
		$child_settings  = $child_theme['settings'];

		// Define the array values here, so they can be updated if the theme.json schema changes.
		$color_keys = [ 'duotone', 'gradient', 'palette' ];
		$typog_keys = [ 'fontFamilies', 'fontSizes' ];
		$space_keys = [ 'spacingSizes' ];

		foreach ( $color_keys as $key ) {
			if ( ! empty( $child_settings['color'][ $key ]['theme'] ) ) {
				$child_settings['color'][ $key ]['theme'] = _merge_by_slug(
					$parent_settings['color'][ $key ]['theme'],
					$child_settings['color'][ $key ]['theme']
				);

			}
		}

		foreach ( $typog_keys as $key ) {
			if ( ! empty( $child_settings['typography'][ $key ]['theme'] ) ) {
				$child_settings['typography'][ $key ]['theme'] = _merge_by_slug(
					$parent_settings['typography'][ $key ]['theme'],
					$child_settings['typography'][ $key ]['theme']
				);
			}
		}

		foreach ( $space_keys as $key ) {
			if ( ! empty( $child_settings['spacing'][ $key ]['theme'] ) ) {
				$child_settings['spacing'][ $key ]['theme'] = _merge_by_slug(
					$parent_settings['spacing'][ $key ]['theme'],
					$child_settings['spacing'][ $key ]['theme']
				);
			}
		}

		$new_data['settings'] = $child_settings;
	}

	return $theme_json->update_with( $new_data );
}

/**
 * Merge two (or more) arrays, de-duplicating by the `slug` key.
 *
 * If any values in later arrays have slugs matching earlier items, the earlier
 * items are overwritten with the later value.
 *
 * @param array ...$arrays A list of arrays of associative arrays, each item
 *                         must have a `slug` key.
 *
 * @return array The combined array, unique by `slug`. Empty if any item is
 *               missing a slug.
 */
function _merge_by_slug( ...$arrays ) {
	$combined = array_merge( ...$arrays );
	$result   = [];

	foreach ( $combined as $value ) {
		if ( ! isset( $value['slug'] ) ) {
			return [];
		}

		$found = array_search( $value['slug'], wp_list_pluck( $result, 'slug' ), true );
		if ( false !== $found ) {
			$result[ $found ] = $value;
		} else {
			$result[] = $value;
		}
	}
	return $result;
}

/**
 * Increase the "Big image" threshold so our full-size high-DPI-ready images are
 * not scaled down.
 */
add_filter(
	'big_image_size_threshold',
	function() {
		// 3200 = 2 × 1600px for full-width images on wide screens.
		return 3200;
	}
);

/**
 * Make posts and pages available for export from the staging site, so the import script can
 * fetch them to a local dev environment.
 */
add_filter(
	'wporg_export_context_post_types',
	function( $types ) {
		return array_merge(
			$types,
			[
				'post',
				'page',
			]
		);
	}
);
