<?php
/**
 * Title: Lead story
 * Slug: quickpop/lead-story
 * Categories: query, featured
 * Description: The newest post as a big cover with its photo, a poster headline and a yellow hard shadow.
 *
 * @package QuickPop
 */

?>
<!-- wp:query {"queryId":1,"query":{"perPage":1,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"align":"wide","className":"qpop-lead"} -->
<div class="wp-block-query alignwide qpop-lead">
	<!-- wp:post-template -->
		<!-- wp:cover {"useFeaturedImage":true,"dimRatio":60,"customOverlayColor":"#1b2333","isUserOverlayColor":true,"minHeight":560,"contentPosition":"bottom left","isDark":true,"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"}}},"layout":{"type":"default"}} -->
		<div class="wp-block-cover is-dark has-custom-content-position is-position-bottom-left" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:560px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-60 has-background-dim" style="background-color:#1b2333"></span><div class="wp-block-cover__inner-container">
			<!-- wp:post-terms {"term":"category","className":"qpop-chips"} /-->
			<!-- wp:post-title {"level":2,"isLink":true,"style":{"typography":{"lineHeight":"0.92","letterSpacing":"-0.035em"},"spacing":{"margin":{"top":"1rem","bottom":"1rem"}}},"fontSize":"poster"} /-->
			<!-- wp:post-excerpt {"excerptLength":30,"style":{"typography":{"fontSize":"1.125rem"}}} /-->
			<!-- wp:post-date {"style":{"color":{"text":"#ffffff"}}} /-->
		</div></div>
		<!-- /wp:cover -->
	<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
