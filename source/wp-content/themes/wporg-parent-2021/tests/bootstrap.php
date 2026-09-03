<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package wporg-parent-2021
 */

declare( strict_types = 1 );

/*
 * The WP test suite needs the PHPUnit Polyfills, which Composer installs at the
 * repository root. Walk up to it rather than counting directories, so the path
 * survives the theme being extracted on its own.
 */
$_root = __DIR__;
while ( ! file_exists( $_root . '/vendor/autoload.php' ) && dirname( $_root ) !== $_root ) {
	$_root = dirname( $_root );
}

if ( file_exists( $_root . '/vendor/autoload.php' ) ) {
	require_once $_root . '/vendor/autoload.php';
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, is the wp-env tests environment running?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Load the parts of the theme under test.
 *
 * The theme itself is not activated: these files register their own hooks and
 * depend only on core, so loading them directly keeps the suite independent of
 * the built assets and of the wporg mu-plugins.
 *
 * @return void
 */
function _manually_load_theme(): void {
	require dirname( __DIR__ ) . '/inc/pattern-shortcodes.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_theme' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
