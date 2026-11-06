<?php
/**
 * Title: About the author
 * Slug: quickpop/about-card
 * Categories: quickpop, about
 * Keywords: author, bio, about
 * Description: A card with a hard shadow for a short author bio at the end of a post.
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"className":"is-style-qpop-card","style":{"spacing":{"blockGap":"0.5rem"}},"layout":{"type":"default"}} -->
<div class="wp-block-group is-style-qpop-card">
	<!-- wp:paragraph {"className":"qpop-label"} -->
	<p class="qpop-label"><?php esc_html_e( 'About the author', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"level":3} -->
	<h3 class="wp-block-heading"><?php esc_html_e( 'Your name here', 'quickpop' ); ?></h3>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'Two lines about who you are, what you write about and where readers can find more of your work.', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
