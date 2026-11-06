<?php
/**
 * Title: Poster hero
 * Slug: quickpop/poster-hero
 * Categories: quickpop, banner, featured
 * Keywords: hero, poster, header, banner
 * Description: Yellow poster band with a giant number, a big headline, an intro line and a button.
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"align":"full","className":"qpop-poster","backgroundColor":"pop","textColor":"navy","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"},"blockGap":"1.5rem"},"border":{"bottom":{"color":"var:preset|color|navy","width":"3px","style":"solid"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull qpop-poster has-navy-color has-pop-background-color has-text-color has-background" style="border-bottom-color:var(--wp--preset--color--navy);border-bottom-style:solid;border-bottom-width:3px;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph {"className":"qpop-giant"} -->
	<p class="qpop-giant">01</p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph {"align":"wide","className":"qpop-label"} -->
	<p class="alignwide qpop-label"><?php esc_html_e( 'The big guide', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"level":1,"align":"wide","className":"is-style-qpop-poster"} -->
	<h1 class="wp-block-heading alignwide is-style-qpop-poster"><?php echo wp_kses( __( 'Make it<br>pop', 'quickpop' ), array( 'br' => array() ) ); ?></h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"align":"wide","style":{"typography":{"fontStyle":"italic","fontWeight":"500"}},"fontSize":"large"} -->
	<p class="alignwide has-large-font-size" style="font-style:italic;font-weight:500"><?php esc_html_e( 'One short line that tells readers why this page is worth their time.', 'quickpop' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:buttons {"align":"wide"} -->
	<div class="wp-block-buttons alignwide">
		<!-- wp:button {"style":{"shadow":"var:preset|shadow|lift"}} -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" style="box-shadow:var(--wp--preset--shadow--lift)"><?php esc_html_e( 'Start reading', 'quickpop' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
