<?php
/**
 * Title: Post grid (follows the page)
 * Slug: quickpop/posts-grid
 * Categories: query
 * Description: Posts of the current archive, search or blog page as cards, with page numbers.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:query {"queryId":3,"query":{"perPage":9,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"align":"wide","className":"qpop-grid"} -->
<div class="wp-block-query alignwide qpop-grid">
	<!-- wp:post-template {"layout":{"type":"grid","minimumColumnWidth":"20rem"}} -->
		<!-- wp:pattern {"slug":"quickpop/post-card"} /-->
	<!-- /wp:post-template -->
	<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
		<!-- wp:query-pagination-previous /-->
		<!-- wp:query-pagination-numbers /-->
		<!-- wp:query-pagination-next /-->
	<!-- /wp:query-pagination -->
	<!-- wp:query-no-results -->
		<!-- wp:pattern {"slug":"quickpop/no-results"} /-->
	<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
