<?php
/**
 * Title: Page not found
 * Slug: quickpop/404
 * Categories: text
 * Description: Giant 404 poster with a search box and a link home.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"tagName":"main","className":"qpop-poster","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"},"blockGap":"1.5rem"}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<main class="wp-block-group qpop-poster" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph {"className":"qpop-giant"} -->
	<p class="qpop-giant">404</p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph {"align":"wide","className":"qpop-label"} -->
	<p class="alignwide qpop-label"><?php esc_html_e( 'Error 404', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"level":1,"align":"wide","className":"is-style-qpop-poster"} -->
	<h1 class="wp-block-heading alignwide is-style-qpop-poster"><?php esc_html_e( 'Lost the plot?', 'quickpop' ); ?></h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"align":"wide","fontSize":"large"} -->
	<p class="alignwide has-large-font-size"><?php esc_html_e( 'This page has moved or never existed. Search for it, or head back to the front page.', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"560px","justifyContent":"left"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:search {"label":"<?php echo esc_attr_x( 'Search', 'search form label', 'quickpop' ); ?>","showLabel":false,"placeholder":"<?php echo esc_attr_x( 'Search stories...', 'search placeholder', 'quickpop' ); ?>","buttonText":"<?php echo esc_attr_x( 'Search', 'search button text', 'quickpop' ); ?>"} /-->
	</div>
	<!-- /wp:group -->
	<!-- wp:buttons {"align":"wide"} -->
	<div class="wp-block-buttons alignwide">
		<!-- wp:button {"className":"is-style-qpop-outline"} -->
		<div class="wp-block-button is-style-qpop-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the front page', 'quickpop' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</main>
<!-- /wp:group -->
