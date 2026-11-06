<?php
/**
 * Title: Single post
 * Slug: quickpop/single-post
 * Categories: text
 * Description: Poster headline, meta line, framed featured image, the post, tags, post links, comments and three more stories.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","className":"qpop-single-head","style":{"spacing":{"blockGap":"1.1rem"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide qpop-single-head">
		<!-- wp:post-terms {"term":"category","className":"qpop-chips"} /-->
		<!-- wp:post-title {"level":1,"style":{"typography":{"fontSize":"clamp(2.5rem, 1.6rem + 3.6vw, 4.75rem)","lineHeight":"0.95","letterSpacing":"-0.035em"}}} /-->
		<!-- wp:group {"style":{"spacing":{"blockGap":"0.4rem 1rem"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
		<div class="wp-block-group">
			<!-- wp:post-author-name {"isLink":true} /-->
			<!-- wp:post-date /-->
			<!-- wp:post-date {"displayType":"modified","className":"qpop-updated"} /-->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
	<!-- wp:post-featured-image {"align":"wide","className":"qpop-frame","sizeSlug":"large"} /-->
	<!-- wp:post-content {"layout":{"type":"constrained"}} /-->
	<!-- wp:post-terms {"term":"post_tag","prefix":"","className":"qpop-chips"} /-->
	<!-- wp:group {"style":{"spacing":{"padding":{"top":"1rem"},"blockGap":"1rem"},"border":{"top":{"color":"var:preset|color|line","width":"3px","style":"solid"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group" style="border-top-color:var(--wp--preset--color--line);border-top-style:solid;border-top-width:3px;padding-top:1rem">
		<!-- wp:post-navigation-link {"type":"previous","showTitle":true,"arrow":"arrow"} /-->
		<!-- wp:post-navigation-link {"showTitle":true,"arrow":"arrow"} /-->
	</div>
	<!-- /wp:group -->
	<!-- wp:pattern {"slug":"quickpop/comments"} /-->
	<!-- wp:pattern {"slug":"quickpop/more-stories"} /-->
</main>
<!-- /wp:group -->
