<?php
/**
 * Title: Search results
 * Slug: quickpop/search
 * Categories: query
 * Description: Search heading, a search box and the matching posts as cards.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"1.25rem"}},"layout":{"type":"constrained","contentSize":"720px","justifyContent":"left"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:paragraph {"className":"qpop-label"} -->
		<p class="qpop-label"><?php esc_html_e( 'Search results', 'quickpop' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:query-title {"type":"search","fontSize":"xx-large"} /-->
		<!-- wp:search {"label":"<?php echo esc_attr_x( 'Search', 'search form label', 'quickpop' ); ?>","showLabel":false,"placeholder":"<?php echo esc_attr_x( 'Search stories...', 'search placeholder', 'quickpop' ); ?>","buttonText":"<?php echo esc_attr_x( 'Search', 'search button text', 'quickpop' ); ?>"} /-->
	</div>
	<!-- /wp:group -->
	<!-- wp:pattern {"slug":"quickpop/posts-grid"} /-->
</main>
<!-- /wp:group -->
