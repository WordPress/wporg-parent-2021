<?php
/**
 * Title: Latest Posts with Heading
 * Slug: wporg-parent-2021/latest-posts-with-heading
 * Categories: query, columns
 */

?>
<!-- wp:columns {"align":"full","className":"is-style-two-column-display"} -->
<div class="wp-block-columns alignfull is-style-two-column-display">
	<!-- wp:column {"className":"is-left-column"} -->
	<div class="wp-block-column is-left-column">
		<!-- wp:heading -->
		<h2><?php esc_html_e( 'Latest Posts', 'wporg' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p><a href="#" data-type="URL" data-id="#"><?php esc_html_e( 'Read More', 'wporg' ); ?></a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:query {"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false}} -->
		<div class="wp-block-query">
			<!-- wp:post-template -->
				<!-- wp:post-title {"style":{"spacing":{"margin":{"top":"0px"}}}} /-->

				<!-- wp:post-date /-->
			<!-- /wp:post-template -->
		</div>
		<!-- /wp:query -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
