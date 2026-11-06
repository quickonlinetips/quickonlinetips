<?php
/**
 * Title: Masthead
 * Slug: quickpop/header
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Yellow masthead with the site title, tagline and a pill menu.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:group {"tagName":"header","align":"full","className":"qpop-masthead","backgroundColor":"pop","textColor":"navy","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<header class="wp-block-group alignfull qpop-masthead has-navy-color has-pop-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"1rem"}}} -->
	<div class="wp-block-group">
		<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"0.9rem"}}} -->
		<div class="wp-block-group">
			<!-- wp:site-logo {"width":56,"shouldSyncIcon":false} /-->
			<!-- wp:group {"layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0.35rem"}}} -->
			<div class="wp-block-group">
				<!-- wp:site-title {"level":0,"fontSize":"x-large"} /-->
				<!-- wp:site-tagline /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
		<!-- wp:navigation {"overlayMenu":"never","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"right"},"style":{"spacing":{"blockGap":"4px"}}} /-->
	</div>
	<!-- /wp:group -->
</header>
<!-- /wp:group -->
