<?php
/**
 * Rosetta customizations.
 */

namespace WordPressdotorg\Theme\Parent_2021\Rosetta_Styles;

defined( 'WPINC' ) || die();

add_filter( 'wp_theme_json_data_user', __NAMESPACE__ . '\inject_i18n_customizations' );
add_filter( 'wporg_preload_heading_font', __NAMESPACE__ . '\update_preload_heading_font' );
add_filter( 'wporg_preload_body_font', __NAMESPACE__ . '\update_preload_body_font' );

/**
 * Update the font to preload for headings.
 *
 * This does not impact loading the font, just the `preload` hint.
 *
 * @param array $font_pair {
 *     An array with [$font, $subset].
 *
 *     @type string $0 Font name(s).
 *     @type string $1 Subset(s).
 * }
 *
 * @return string[] Updated (font, subset) pair.
 */
function update_preload_heading_font( $font_pair ) {
	// First check if it should be a different font.
	if ( 'ja' === get_locale() ) {
		$font_pair = [ 'Noto Serif JP', 'cjk' ];
	} else if ( 'zh_CN' === get_locale() ) {
		$font_pair = [ 'Noto Serif SC', 'cjk' ];
	} else if ( 'ckb' === get_locale() ) {
		$font_pair = [ 'Noto Kufi', 'arabic' ];
	}
	return $font_pair;
}

/**
 * Update the font to preload for body text.
 *
 * This does not impact loading the font, just the `preload` hint.
 *
 * @param array $font_pair {
 *     An array with [$font, $subset].
 *
 *     @type string $0 Font name(s).
 *     @type string $1 Subset(s).
 * }
 *
 * @return array{string,string} Updated (font, subset) pair.
 */
function update_preload_body_font( $font_pair ) {
	if ( 'ckb' === get_locale() ) {
		$font_pair = [ 'Noto Kufi', 'arabic' ];
	}
	return $font_pair;
}

/**
 * Inject customizations for Rosetta sites.
 *
 * @param WP_Theme_JSON_Data $theme_json Class to access and update the underlying data.
 *
 * @return WP_Theme_JSON_Data The updated user settings.
 */
function inject_i18n_customizations( $theme_json ) {
	$locale_settings = get_locale_settings( get_locale() );
	$locale_styles = get_locale_styles( get_locale() );
	if ( ! $locale_settings && ! $locale_styles ) {
		return $theme_json;
	}

	$config = array(
		'version' => 2,
	);

	if ( $locale_settings ) {
		$config['settings'] = $locale_settings;
	}

	if ( $locale_styles ) {
		$config['styles'] = $locale_styles;
	}

	return $theme_json->update_with( $config, 'custom' );
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
function get_locale_settings( $locale ) {
	switch ( $locale ) {
		case 'ca':
		case 'fr_FR':
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
							'fontFamily' => 'var(--wp--preset--font-family--noto-serif-jp)',
							'text-wrap' => 'unset',
						],
					],
				],
				'typography' => [
					'fontFamilies' => [
						[
							'fontFamily' => '"Noto Serif JP", serif',
							'slug' => 'noto-serif-jp',
							'name' => 'Noto Serif JP',
						],
					],
					'fontSizes' => [
						[
							'slug' => 'heading-cta',
							'size' => '70px',
						],
						[
							'slug' => 'heading-1',
							'size' => '60px',
						],
						[
							'slug' => 'heading-2',
							'size' => '40px',
						],
					],
				],
			];
		case 'ckb':
			return [
				'custom' => [
					'heading' => [
						'typography' => [
							'fontFamily' => 'var(--wp--preset--font-family--noto-kufi)',
						],
					],
				],
				'typography' => [
					'fontFamilies' => [
						[
							'fontFamily' => '"Noto Kufi Arabic", sans-serif',
							'slug' => 'noto-kufi',
							'name' => 'Noto Kufi Arabic',
						],
						// Unset courier-prime & anton, elements using these styles should fall back to Noto Kufi.
						[
							'fontFamily' => '',
							'slug' => 'courier-prime',
						],
						[
							'fontFamily' => '',
							'slug' => 'anton',
						],
					],
				],
			];
		case 'vi':
			return [
				'typography' => [
					'fontFamilies' => [
						// Unset courier-prime, does not support Vietnamese diacritics.
						[
							'fontFamily' => '',
							'slug' => 'courier-prime',
						],
					],
				],
			];
		case 'zh_CN':
			return [
				'custom' => [
					'heading' => [
						'typography' => [
							'fontFamily' => 'var(--wp--preset--font-family--noto-serif-sc)'
						],
					],
				],
				'typography' => [
					'fontFamilies' => [
						[
							'fontFamily' => '"Noto Serif SC", serif',
							'slug' => 'noto-serif-sc',
							'name' => 'Noto Serif SC',
						],
					],
				],
			];
	}
	return false;
}

/**
 * Get a theme.json-shaped array with custom values for a given locale.
 *
 * The returned array should match the structure of "styles" in a theme.json
 * file. This can be used to override block, element, and sitewide styles.
 * The `css` key can also be used to inject CSS code into all child themes.
 *
 * @param string $locale The current site locale.
 *
 * @return array An array of styles mirroring a theme.json "styles" object.
 */
function get_locale_styles( $locale ) {
	switch ( $locale ) {
		case 'ja':
			return [
				'css' => 'body { font-feature-settings: "palt"; }',
			];
		case 'ckb':
			return [
				// Force any inline inter styles to use Noto Kufi (used for global header, `.has-inter-font-family`, etc).
				'css' => <<<CSS
* {
	--wp--preset--font-family--inter: var(--wp--preset--font-family--noto-kufi) !important;
	--wp--preset--font-family--eb-garamond: var(--wp--preset--font-family--noto-kufi) !important;
}
span.global-footer__code_is_poetry {
	font-family: var(--wp--preset--font-family--noto-kufi) !important;
}
CSS,
				'typography' => [
					'fontFamily' => 'var(--wp--preset--font-family--noto-kufi)',
				],
				'blocks' => [
					'core/button' => [
						'typography' => [
							'fontFamily' => 'var(--wp--preset--font-family--noto-kufi)',
						],
					],
				],
			];
		case 'zh_CN':
			return [
				// Force any inline-styled headings to use Noto Serif SC.
				'css' => <<<CSS
* {
	--wp--preset--font-family--eb-garamond: var(--wp--preset--font-family--noto-serif-sc) !important;
}
span.global-footer__code_is_poetry {
	font-family: var(--wp--preset--font-family--noto-serif-sc) !important;
}
CSS,
			];
	}
}
