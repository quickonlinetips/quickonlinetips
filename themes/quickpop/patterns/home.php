<?php
/**
 * Title: Home: lead story and poster grid
 * Slug: quickpop/home
 * Categories: query
 * Description: The newest post as a big lead story, then the rest of the posts as cards in a grid with page numbers.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|60"}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50)">
	<!-- wp:pattern {"slug":"quickpop/lead-story"} /-->
	<!-- wp:query {"queryId":2,"query":{"perPage":9,"pages":0,"offset":1,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"align":"wide","className":"qpop-grid"} -->
	<div class="wp-block-query alignwide qpop-grid">
		<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"},"blockGap":"1rem"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
		<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--40)">
			<!-- wp:heading {"fontSize":"xx-large","style":{"typography":{"textTransform":"uppercase","letterSpacing":"-0.03em"}}} -->
			<h2 class="wp-block-heading has-xx-large-font-size" style="letter-spacing:-0.03em;text-transform:uppercase"><?php esc_html_e( 'Latest stories', 'quickpop' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:separator {"className":"is-style-wide","style":{"layout":{"selfStretch":"fill","flexSize":null}}} -->
			<hr class="wp-block-separator has-alpha-channel-opacity is-style-wide"/>
			<!-- /wp:separator -->
		</div>
		<!-- /wp:group -->
		<!-- wp:post-template {"layout":{"type":"grid","minimumColumnWidth":"20rem"}} -->
			<!-- wp:pattern {"slug":"quickpop/post-card"} /-->
		<!-- /wp:post-template -->
		<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
			<!-- wp:query-pagination-previous /-->
			<!-- wp:query-pagination-numbers /-->
			<!-- wp:query-pagination-next /-->
		<!-- /wp:query-pagination -->
		<!-- wp:query-no-results -->
			<!-- wp:paragraph -->
			<p><?php esc_html_e( 'More stories are on their way. Check back soon.', 'quickpop' ); ?></p>
			<!-- /wp:paragraph -->
		<!-- /wp:query-no-results -->
	</div>
	<!-- /wp:query -->
</main>
<!-- /wp:group -->
