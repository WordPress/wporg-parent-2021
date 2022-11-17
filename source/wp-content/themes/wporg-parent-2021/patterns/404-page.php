<?php
/**
 * Title: 404 Page
 * Slug: wporg-parent-2021/404-page
 * Categories: wporg
 */

?>

<!-- wp:group {"className":"site-content-container"} -->
<div class="wp-block-group site-content-container">
	<p class="error404__oops" aria-hidden="true">
		<?php esc_html_e( 'Oops!', 'wporg' ); ?>
	</p>
	
	<!-- wp:heading {"level":1} -->
	<h1><?php esc_html_e( 'This page doesn\'t exist.', 'wporg' ); ?></h1>
	<!-- /wp:heading -->
	
	<!-- wp:paragraph -->
	<p>
		<?php esc_html_e( 'Go to', 'wporg' ); ?>
		<a href="https://wordpress.org"><?php esc_html_e( 'the homepage', 'wporg' ); ?></a>,
		<?php esc_html_e( 'or try searching WordPress.org sites using the field below.', 'wporg' ); ?>
	</p>
	<!-- /wp:paragraph -->
	
	<!-- wp:search {"showLabel":false,"placeholder":"Search sites...","width":100,"widthUnit":"%","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true} /-->
</div>
<!-- /wp:group -->

