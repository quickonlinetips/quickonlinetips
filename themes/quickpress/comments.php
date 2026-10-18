<div id="comments" class="comments">
    <?php if (post_password_required()) : ?>
    <p>
        <?php _e( 'Post is password protected. Enter the password to view any comments.', 'quickpress' ); ?>
    </p>

<?php return; endif; ?>
</div>

<?php if (have_comments()) : ?>
<h3 id="comments">
    <?php
	printf( _nx( 'One comment on &ldquo;%2$s&rdquo;', '%1$s comments on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'quickpress' ), number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
	?>
</h3>
<div class="navigation">
    <div class="alignleft">
        <?php previous_comments_link() ?>
    </div>
    <div class="alignright">
        <?php next_comments_link() ?>
    </div>
</div>

<ol class="commentlist">
    <?php wp_list_comments(); ?>
</ol>

<div class="navigation">
    <div class="alignleft">
        <?php previous_comments_link() ?>
    </div>
    <div class="alignright">
        <?php next_comments_link() ?>
    </div>
</div>

<?php elseif ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
<p>
    <?php _e( 'Comments are closed here.', 'quickpress' ); ?>
</p>
<?php endif; ?>

<?php comment_form(); ?>
