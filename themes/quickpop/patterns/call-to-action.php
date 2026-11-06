<?php
/**
 * Title: Call to action
 * Slug: quickpop/call-to-action
 * Categories: quickpop, call-to-action
 * Keywords: newsletter, subscribe, cta, signup
 * Description: Navy box with a yellow label, a short pitch and a button, for a newsletter or an offer.
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"backgroundColor":"navy","textColor":"white","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"},"blockGap":"1rem"},"shadow":"var:preset|shadow|pop"},"layout":{"type":"default"}} -->
<div class="wp-block-group has-white-color has-navy-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);box-shadow:var(--wp--preset--shadow--pop)">
	<!-- wp:paragraph {"className":"qpop-label","textColor":"pop"} -->
	<p class="qpop-label has-pop-color has-text-color"><?php esc_html_e( 'Never miss a story', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"level":2,"textColor":"white","fontSize":"xx-large"} -->
	<h2 class="wp-block-heading has-white-color has-text-color has-xx-large-font-size"><?php esc_html_e( 'Get the best posts every week', 'quickpop' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'One friendly email with the newest stories. No spam, unsubscribe any time.', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button {"backgroundColor":"pop","textColor":"navy","style":{"shadow":"none"}} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-navy-color has-pop-background-color has-text-color has-background wp-element-button" style="box-shadow:none"><?php esc_html_e( 'Subscribe', 'quickpop' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
