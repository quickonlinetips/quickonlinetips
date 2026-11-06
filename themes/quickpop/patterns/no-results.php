<?php
/**
 * Title: Nothing found
 * Slug: quickpop/no-results
 * Categories: query
 * Description: Message and search box shown when a search or archive has no posts.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"className":"is-style-qpop-card","layout":{"type":"constrained","contentSize":"640px","justifyContent":"left"}} -->
<div class="wp-block-group is-style-qpop-card">
	<!-- wp:heading {"level":2,"fontSize":"x-large"} -->
	<h2 class="wp-block-heading has-x-large-font-size"><?php esc_html_e( 'Nothing here yet', 'quickpop' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'We could not find any posts that match. Try a different word, or start again from the home page.', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:search {"label":"<?php echo esc_attr_x( 'Search', 'search form label', 'quickpop' ); ?>","showLabel":false,"placeholder":"<?php echo esc_attr_x( 'Search stories...', 'search placeholder', 'quickpop' ); ?>","buttonText":"<?php echo esc_attr_x( 'Search', 'search button text', 'quickpop' ); ?>"} /-->
</div>
<!-- /wp:group -->
