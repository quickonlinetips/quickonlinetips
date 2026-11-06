<?php
/**
 * Title: Archive header
 * Slug: quickpop/archive-head
 * Categories: query
 * Description: Poster title for category, tag, author and date archives, with the description underneath.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"align":"wide","className":"qpop-poster","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"},"blockGap":"1rem"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignwide qpop-poster" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
	<!-- wp:paragraph {"className":"qpop-label"} -->
	<p class="qpop-label"><?php esc_html_e( 'Browsing', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:query-title {"type":"archive","showPrefix":false,"className":"is-style-qpop-poster","style":{"typography":{"textTransform":"uppercase","lineHeight":"0.9","letterSpacing":"-0.04em"}},"fontSize":"poster"} /-->
	<!-- wp:term-description {"style":{"typography":{"fontSize":"1.125rem"}}} /-->
</div>
<!-- /wp:group -->
