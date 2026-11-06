<?php
/**
 * Title: Post card
 * Slug: quickpop/post-card
 * Categories: query
 * Description: One post as a card with a hard shadow: photo, category chips, title, excerpt and date. Used inside the post grids.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"className":"qpop-card","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
<div class="wp-block-group qpop-card">
	<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","sizeSlug":"medium_large"} /-->
	<!-- wp:group {"style":{"spacing":{"padding":{"top":"1.1rem","right":"1.25rem","bottom":"0.4rem","left":"1.25rem"},"blockGap":"0.7rem"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group" style="padding-top:1.1rem;padding-right:1.25rem;padding-bottom:0.4rem;padding-left:1.25rem">
		<!-- wp:post-terms {"term":"category","className":"qpop-chips"} /-->
		<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"lineHeight":"1.02"}},"fontSize":"large"} /-->
		<!-- wp:post-excerpt {"excerptLength":22,"style":{"typography":{"fontSize":"0.9375rem"}}} /-->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"style":{"spacing":{"padding":{"top":"0.4rem","right":"1.25rem","bottom":"1.1rem","left":"1.25rem"}}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
	<div class="wp-block-group" style="padding-top:0.4rem;padding-right:1.25rem;padding-bottom:1.1rem;padding-left:1.25rem">
		<!-- wp:post-date /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
