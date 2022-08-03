<?php
/**
 * Block Styles & Variations
 *
 * Load the CSS, JS, and register custom styles.
 */

namespace WordPressdotorg\Theme\Parent_2021\Block_Styles;

defined( 'WPINC' ) || die();

const STYLE_HANDLE = 'wporg-parent-block-styles';

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\setup_block_styles' );
add_filter( 'wp_enqueue_scripts', __NAMESPACE__ . '\register_assets' );
add_filter( 'admin_enqueue_scripts', __NAMESPACE__ . '\register_assets' );
add_filter( 'should_load_separate_core_block_assets', '__return_false' );

/**
 * Add our custom block styles & class names.
 */
function setup_block_styles() {
	// Register the two-column class for Group, Post Content, and Columns — this will let each block use the
	// slightly-offset grid, though Columns works slightly differently in that it allows for content in the
	// first column (hence the different name).
	register_block_style(
		'core/group',
		array(
			'name'         => 'two-column-display',
			'label'        => __( 'Shifted Content', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/post-content',
		array(
			'name'         => 'two-column-display',
			'label'        => __( 'Shifted Content', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/columns',
		array(
			'name'         => 'two-column-display',
			'label'        => __( 'Left Heading', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/group',
		array(
			'name'         => 'four-columns',
			'label'        => __( 'Four Columns', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'         => 'fill-on-dark',
			'label'        => __( 'Fill on dark', 'wporg' ),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'         => 'outline-on-dark',
			'label'        => __( 'Outline on dark', 'wporg' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'         => 'features',
			'label'        => __( 'Features', 'wporg' ),
		)
	);
}

/**
 * Add our custom block style assets — CSS for the layout, and JS to register
 * block variations & add custom styles.
 */
function register_assets() {
	wp_register_style(
		STYLE_HANDLE,
		get_template_directory_uri() . '/build/block-styles.css',
		array(),
		filemtime( dirname( __DIR__ ) . '/build/block-styles.css' )
	);

	if ( is_admin() ) {
		wp_enqueue_script(
			'wporg-parent-block-tweaks',
			get_template_directory_uri() . '/js/blocks.js',
			array( 'wp-blocks', 'wp-hooks', 'wp-i18n' ),
			filemtime( dirname( __DIR__ ) . '/js/blocks.js' )
		);
	}
}
