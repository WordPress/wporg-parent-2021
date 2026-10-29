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

// The WP test suite reads this as a constant, so bridge it from the environment the workflow sets.
$_config_file = getenv( 'WP_TESTS_CONFIG_FILE_PATH' );

if ( is_string( $_config_file ) && '' !== $_config_file && ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
	define( 'WP_TESTS_CONFIG_FILE_PATH', $_config_file );
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find the WordPress test suite at $_tests_dir. Point WP_TESTS_DIR at the tests/phpunit directory of a wordpress-develop checkout, as .github/workflows/unit-tests.yml does." . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
