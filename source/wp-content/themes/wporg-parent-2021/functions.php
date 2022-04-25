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
	add_theme_support( 'editor-styles' );
	add_editor_style( get_font_stylesheet_url() );

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
