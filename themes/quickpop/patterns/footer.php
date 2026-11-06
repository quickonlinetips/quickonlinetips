<?php
/**
 * Title: Footer
 * Slug: quickpop/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: Navy footer band with the site title, tagline, a menu and the copyright line.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"tagName":"footer","align":"full","className":"qpop-footer","backgroundColor":"navy","textColor":"white","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|50"},"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<footer class="wp-block-group alignfull qpop-footer has-white-color has-navy-background-color has-text-color has-background" style="margin-top:var(--wp--preset--spacing--60);padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"2rem"}}} -->
	<div class="wp-block-group">
		<!-- wp:group {"layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0.5rem"}}} -->
		<div class="wp-block-group">
			<!-- wp:site-title {"level":0,"fontSize":"xx-large"} /-->
			<!-- wp:site-tagline {"style":{"typography":{"fontSize":"1rem"}}} /-->
		</div>
		<!-- /wp:group -->
		<!-- wp:navigation {"overlayMenu":"never","layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0.6rem"}}} /-->
	</div>
	<!-- /wp:group -->
	<!-- wp:separator {"className":"is-style-wide","style":{"color":{"text":"#ffffff33"},"spacing":{"margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}}} -->
	<hr class="wp-block-separator has-text-color has-alpha-channel-opacity is-style-wide" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--30);color:#ffffff33"/>
	<!-- /wp:separator -->
	<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"style":{"typography":{"fontSize":"0.875rem"}}} -->
	<div class="wp-block-group" style="font-size:0.875rem">
		<!-- wp:paragraph -->
		<p><?php echo esc_html( '© ' . wp_date( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph -->
		<p>
		<?php
		printf(
			/* translators: %s: QuickPop theme link. */
			esc_html__( 'Made loud with %s', 'quickpop' ),
			'<a href="' . esc_url( __( 'https://www.quickonlinetips.com/', 'quickpop' ) ) . '">QuickPop</a>'
		);
		?>
		</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</footer>
<!-- /wp:group -->
