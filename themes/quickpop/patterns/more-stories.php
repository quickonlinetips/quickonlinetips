<?php
/**
 * Title: More stories
 * Slug: quickpop/more-stories
 * Categories: query
 * Description: Three recent posts as cards, for the end of a post.
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"align":"wide","className":"qpop-more","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignwide qpop-more" style="margin-top:var(--wp--preset--spacing--60)">
	<!-- wp:group {"style":{"spacing":{"blockGap":"1rem"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
	<div class="wp-block-group">
		<!-- wp:heading {"fontSize":"x-large","style":{"typography":{"textTransform":"uppercase"}}} -->
		<h2 class="wp-block-heading has-x-large-font-size" style="text-transform:uppercase"><?php esc_html_e( 'More stories', 'quickpop' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:separator {"className":"is-style-wide","style":{"layout":{"selfStretch":"fill","flexSize":null}}} -->
		<hr class="wp-block-separator has-alpha-channel-opacity is-style-wide"/>
		<!-- /wp:separator -->
	</div>
	<!-- /wp:group -->
	<!-- wp:query {"queryId":4,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"className":"qpop-grid"} -->
	<div class="wp-block-query qpop-grid">
		<!-- wp:post-template {"layout":{"type":"grid","minimumColumnWidth":"20rem"}} -->
			<!-- wp:pattern {"slug":"quickpop/post-card"} /-->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->
</div>
<!-- /wp:group -->
