<?php
/**
 * Title: Post List
 * Slug: wporg-parent-2021/post-list
 * Categories: wporg
 */

?>
<!-- wp:query {"tagName":"main"} -->
<main class="wp-block-query">
	<!-- wp:post-template -->
		<!-- wp:group {"tagName":"header","className":"entry-header"} -->
		<header class="wp-block-group entry-header">
			<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"heading-3"} /-->

			<!-- wp:group {"className":"entry-meta"} -->
			<div class="wp-block-group entry-meta">
				<!-- wp:post-date /-->
			</div>
			<!-- /wp:group -->
		</header>
		<!-- /wp:group -->

		<!-- wp:group {"tagName":"section","style":{"spacing":{"bottom":{"top":"var:preset|spacing|60"}}}} -->
		<section class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--60)">
			<!-- wp:post-excerpt {"moreText":"<?php esc_html_e( 'Read Post', 'wporg' ); ?>","showMoreOnNewLine":true,"layout":{"inherit":true}} /-->
		</section>
		<!-- /wp:group -->
	<!-- /wp:post-template -->

	<!-- wp:query-pagination -->
		<!-- wp:query-pagination-previous {"label":"<?php esc_html_e( 'Newer Posts', 'wporg' ); ?>"} /-->
		<!-- wp:query-pagination-numbers /-->
		<!-- wp:query-pagination-next {"label":"<?php esc_html_e( 'Older Posts', 'wporg' ); ?>"} /-->
	<!-- /wp:query-pagination -->

</main>
<!-- /wp:query -->
