<?php
/**
 * Title: Big idea
 * Slug: quickpop/big-idea
 * Categories: quickpop, text
 * Keywords: callout, summary, key point, highlight
 * Description: A yellow callout with a small label for the key point of a post.
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"backgroundColor":"pop","textColor":"navy","style":{"spacing":{"blockGap":"0.25rem"}},"layout":{"type":"default"}} -->
<div class="wp-block-group has-navy-color has-pop-background-color has-text-color has-background">
	<!-- wp:paragraph {"className":"qpop-label"} -->
	<p class="qpop-label"><?php esc_html_e( 'The big idea', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size"><?php esc_html_e( 'Sum up the whole post in one bold sentence that readers will remember.', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
