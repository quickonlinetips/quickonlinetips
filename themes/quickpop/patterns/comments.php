<?php
/**
 * Title: Comments
 * Slug: quickpop/comments
 * Categories: text
 * Description: Comment list with replies and the comment form.
 * Inserter: no
 *
 * @package QuickPop
 */

?>
<!-- wp:comments {"className":"qpop-comments"} -->
<div class="wp-block-comments qpop-comments">
	<!-- wp:comments-title {"level":2,"fontSize":"x-large"} /-->
	<!-- wp:comment-template -->
		<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"1.25rem"}}},"layout":{"type":"default"}} -->
		<div class="wp-block-group" style="margin-bottom:1.25rem">
			<!-- wp:group {"style":{"spacing":{"blockGap":"0.75rem"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
			<div class="wp-block-group">
				<!-- wp:avatar {"size":40} /-->
				<!-- wp:group {"style":{"spacing":{"blockGap":"0.1rem"}},"layout":{"type":"flex","orientation":"vertical"}} -->
				<div class="wp-block-group">
					<!-- wp:comment-author-name {"fontSize":"medium"} /-->
					<!-- wp:comment-date {"style":{"typography":{"fontSize":"0.8125rem"}}} /-->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
			<!-- wp:comment-content /-->
			<!-- wp:group {"style":{"spacing":{"blockGap":"1rem"},"typography":{"fontSize":"0.875rem","fontWeight":"800"}},"layout":{"type":"flex"}} -->
			<div class="wp-block-group" style="font-size:0.875rem;font-weight:800">
				<!-- wp:comment-reply-link /-->
				<!-- wp:comment-edit-link /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	<!-- /wp:comment-template -->
	<!-- wp:comments-pagination {"layout":{"type":"flex","justifyContent":"space-between"}} -->
		<!-- wp:comments-pagination-previous /-->
		<!-- wp:comments-pagination-numbers /-->
		<!-- wp:comments-pagination-next /-->
	<!-- /wp:comments-pagination -->
	<!-- wp:post-comments-form /-->
</div>
<!-- /wp:comments -->
