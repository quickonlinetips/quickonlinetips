<?php
/**
 * Comments list and comment form.
 *
 * @package QuickPic
 */

// Password-protected post: WordPress shows the password form in the post, so stop here.
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			printf(
				/* translators: 1: number of comments, 2: post title */
				esc_html( _nx( 'One comment on &ldquo;%2$s&rdquo;', '%1$s comments on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'quickpic' ) ),
				esc_html( number_format_i18n( get_comments_number() ) ),
				'<span>' . esc_html( get_the_title() ) . '</span>'
			);
			?>
		</h2>

		<?php the_comments_navigation(); ?>

		<ol class="commentlist">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'avatar_size' => 32,
				'short_ping'  => true,
			) );
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed here.', 'quickpic' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>

</div>
