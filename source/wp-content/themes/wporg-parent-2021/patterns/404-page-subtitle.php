<?php
/**
 * Title: 404 Page Subtitle
 * Slug: wporg-parent-2021/404-page-subtitle
 * Inserter: no
 */

?>

<!-- wp:paragraph -->
<p>
	<?php
	printf(
		/* translators: %s is the site URL. */
		wp_kses_post( __( 'Go to <a href="%s">the homepage</a> or try searching WordPress.org sites using the field below.', 'wporg' ) ),
		esc_url( get_site_url( '/' ) )
	);
	?>
</p>
<!-- /wp:paragraph -->
