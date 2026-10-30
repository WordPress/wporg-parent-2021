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
			'label'        => _x( 'Shifted Content', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/post-content',
		array(
			'name'         => 'two-column-display',
			'label'        => _x( 'Shifted Content', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/columns',
		array(
			'name'         => 'two-column-display',
			'label'        => _x( 'Left Heading', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/group',
		array(
			'name'         => 'four-columns',
			'label'        => _x( 'Four Columns', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'         => 'fill-on-dark',
			'label'        => _x( 'Fill on dark', 'block style name', 'wporg' ),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'         => 'outline-on-dark',
			'label'        => _x( 'Outline on dark', 'block style name', 'wporg' ),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'         => 'text',
			'label'        => _x( 'Text', 'block style name', 'wporg' ),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'         => 'toggle',
			'label'        => _x( 'Toggle', 'block style name', 'wporg' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'         => 'features',
			'label'        => _x( 'Features', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'         => 'links-list',
			'label'        => _x( 'Links', 'block style name', 'wporg' ),
		)
	);

	register_block_style(
		'core/paragraph',
		array(
			'name'         => 'serif',
			'label'        => _x( 'Serif', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/paragraph',
		array(
			'name'         => 'short-text',
			'label'        => _x( 'Short text', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/heading',
		array(
			'name'         => 'with-arrow',
			'label'        => _x( 'Link & Arrow', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/navigation',
		array(
			'name'         => 'dots',
			'label'        => _x( 'Dots', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/group',
		array(
			'name'         => 'brush-stroke',
			'label'        => _x( 'Brush Stroke', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'         => 'list-long-items',
			'label'        => _x( 'Long items', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/navigation',
		array(
			'name'         => 'dropdown-on-mobile',
			'label'        => _x( 'Dropdown on Mobile', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/search',
		array(
			'name'         => 'on-dark',
			'label'        => _x( 'On dark', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/group',
		array(
			'name'         => 'cards-grid',
			'label'        => _x( 'Cards Grid', 'block style name', 'wporg' ),
		)
	);

	register_block_style(
		'core/table',
		array(
			'name'         => 'borderless',
			'label'        => _x( 'Borderless', 'block style name', 'wporg' ),
		)
	);

	register_block_style(
		'core/navigation',
		array(
			'name'         => 'button-list',
			'label'        => _x( 'Buttons', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/spacer',
		array(
			'name'         => 'dots-background',
			'label'        => _x( 'Dots', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/spacer',
		array(
			'name'         => 'blue-dots-background',
			'label'        => _x( 'Dots (blue)', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
		)
	);

	register_block_style(
		'core/spacer',
		array(
			'name'         => 'dark-dots-background',
			'label'        => _x( 'Dots (dark)', 'block style name', 'wporg' ),
			'style_handle' => STYLE_HANDLE,
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
	wp_style_add_data( STYLE_HANDLE, 'rtl', 'replace' );

	if ( is_admin() ) {
		wp_enqueue_script(
			'wporg-parent-block-tweaks',
			get_template_directory_uri() . '/js/blocks.js',
			array( 'wp-blocks', 'wp-hooks', 'wp-i18n' ),
			filemtime( dirname( __DIR__ ) . '/js/blocks.js' )
		);
	}
}
