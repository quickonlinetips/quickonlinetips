<?php
/**
 * Title: Colour boxes
 * Slug: quickpop/pop-boxes
 * Categories: quickpop, text
 * Keywords: boxes, tips, colour, grid, notes
 * Description: Four bright boxes in a grid for tips, warnings, examples and a summary.
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"layout":{"type":"grid","columnCount":2,"minimumColumnWidth":"13rem"},"style":{"spacing":{"blockGap":"14px"}}} -->
<div class="wp-block-group">
	<!-- wp:group {"backgroundColor":"pop","textColor":"navy","layout":{"type":"default"}} -->
	<div class="wp-block-group has-navy-color has-pop-background-color has-text-color has-background">
		<!-- wp:heading {"level":4,"className":"qpop-label"} -->
		<h4 class="wp-block-heading qpop-label"><?php esc_html_e( 'Quick tip', 'quickpop' ); ?></h4>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'The one thing to remember from this section, in a sentence or two.', 'quickpop' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"backgroundColor":"mint","textColor":"navy","layout":{"type":"default"}} -->
	<div class="wp-block-group has-navy-color has-mint-background-color has-text-color has-background">
		<!-- wp:heading {"level":4,"className":"qpop-label"} -->
		<h4 class="wp-block-heading qpop-label"><?php esc_html_e( 'Try this', 'quickpop' ); ?></h4>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'A small step readers can take right now to see it working.', 'quickpop' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"backgroundColor":"pink","textColor":"navy","layout":{"type":"default"}} -->
	<div class="wp-block-group has-navy-color has-pink-background-color has-text-color has-background">
		<!-- wp:heading {"level":4,"className":"qpop-label"} -->
		<h4 class="wp-block-heading qpop-label"><?php esc_html_e( 'Watch out', 'quickpop' ); ?></h4>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'The common mistake, and how to avoid it.', 'quickpop' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"backgroundColor":"sky","textColor":"navy","layout":{"type":"default"}} -->
	<div class="wp-block-group has-navy-color has-sky-background-color has-text-color has-background">
		<!-- wp:heading {"level":4,"className":"qpop-label"} -->
		<h4 class="wp-block-heading qpop-label"><?php esc_html_e( 'Example', 'quickpop' ); ?></h4>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'A real example that shows the idea in action.', 'quickpop' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
