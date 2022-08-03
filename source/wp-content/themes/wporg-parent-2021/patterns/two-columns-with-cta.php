<?php
/**
 * Title: 2 Columns with CTA
 * Slug: wporg-parent-2021/two-columns-with-cta
 * Categories: columns, buttons
 */

?>

<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":"0px"}},"className":"wporg-pattern__two-columns-cta"} -->
<div class="wp-block-columns alignwide wporg-pattern__two-columns-cta">

	<!-- wp:column {"width":"50%","style":{"border":{"right":{"color":"var:preset|color|light-grey-1","width":"1px"}}}} -->
	<div class="wp-block-column" style="border-right-color:var(--wp--preset--color--light-grey-1);border-right-width:1px;flex-basis:50%">
		
	<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|60","bottom":"var:preset|spacing|50","left":"var:preset|spacing|60"}}}} -->
		<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--60)">
			
		<!-- wp:heading {"textAlign":"left","fontSize":"huge"} -->
			<h2 class="has-text-align-left has-huge-font-size">Download and install yourself</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"align":"left"} -->
			<p class="has-text-align-left">For anyone comfortable getting their own hosting and domain.</p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"left"},"style":{"spacing":{"blockGap":"10px"}}} -->
			<div class="wp-block-buttons">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://wordpress.org/latest.zip">Download WordPress 6.0.1</a></div>
				<!-- /wp:button -->

				<!-- wp:button {"textColor":"blue-1","className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-blue-1-color has-text-color wp-element-button" href="https://wordpress.org/latest.tar.gz">Installation guide</a></div>
				<!-- /wp:button --></div>
			<!-- /wp:buttons -->

			<!-- wp:paragraph {"textColor":"charcoal-4","fontSize":"extra-small"} -->
			<p class="has-charcoal-4-color has-text-color has-extra-small-font-size">Recommend PHP 7.4 or greater and MySQL 5.7 or MariaDB version 10.3 or greater.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"width":"50%"} -->
	<div class="wp-block-column" style="flex-basis:50%">
		<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|60","bottom":"var:preset|spacing|50","left":"var:preset|spacing|60"}}}} -->
		<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--60)">
			<!-- wp:heading {"textAlign":"left","fontSize":"huge"} -->
			<h2 class="has-text-align-left has-huge-font-size">Set up with a hosting provider</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"align":"left"} -->
			<p class="has-text-align-left">For anyone looking for the simplest way to start.</p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://wordpress.org/main-test/hosting/">See all recommended hosts</a></div>
				<!-- /wp:button --></div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->