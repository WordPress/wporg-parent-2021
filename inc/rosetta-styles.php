<?php
/**
 * Rosetta customizations.
 */

namespace WordPressdotorg\Theme\Parent_2021\Rosetta_Styles;

defined( 'WPINC' ) || die();

add_filter( 'wp_theme_json_data_user', __NAMESPACE__ . '\inject_i18n_customizations' );

/**
 * Inject customizations for Rosetta sites.
 *
 * @param WP_Theme_JSON_Data $theme_json Class to access and update the underlying data.
 *
 * @return WP_Theme_JSON_Data The updated user settings.
 */
function inject_i18n_customizations( $theme_json ) {
	$locale_settings = get_locale_customizations( get_locale() );
	if ( ! $locale_settings ) {
		return $theme_json;
	}

	$config = array(
		'version' => 2,
		'settings' => $locale_settings,
	);

	return new \WP_Theme_JSON( $config, 'custom' );
}

/**
 * Get a theme.json-shaped array with custom values for a given locale.
 *
 * The returned array should match the structure of "settings" in a theme.json
 * file. These will be loaded as the "user" settings, which will override the
 * theme.json values. Rosetta sites can then override any of the generated
 * custom properties (ex, --wp--preset--font-size--normal) in a way that will
 * cascade to any future child themes, and also render correctly in the editor.
 *
 * @param string $locale The current site locale.
 *
 * @return array An array of settings mirroring a theme.json "settings" object.
 */
function get_locale_customizations( $locale ) {
	switch ( $locale ) {
		case 'ca':
		case 'fr':
		case 'it_IT':
		case 'ro_RO':
			return [
				'typography' => [
					'fontSizes' => [
						[
							'slug' => 'heading-cta',
							'size' => '96px',
						],
					],
				],
			];
		case 'ja':
			return [
				'custom' => [
					'heading' => [
						'cta' => [
							'breakpoint' => [
								'small-only' => [
									'typography' => [
										'fontSize' => '50px',
									],
								],
							],
						],
						'typography' => [
							'text-wrap' => 'unset',
						],
					],
				],
				'typography' => [
					'fontSizes' => [
						[
							'slug' => 'heading-cta',
							'size' => '96px',
						],
						[
							'slug' => 'heading-2',
							'size' => '40px',
						],
					],
				],
			];
	}
	return false;
}
