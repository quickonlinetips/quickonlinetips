<div id="comments" class="comments">
    <?php if (post_password_required()) : ?>
    <p>
        <?php _e( 'Post is password protected. Enter the password to view any comments.', 'quickstrap' ); ?>
    </p>


<?php return; endif; ?>

<?php if (have_comments()) : ?>

<h4 id="comments">
    <i class="far fa-comments text-secondary"></i> 
	<?php
	printf( _nx( 'One comment on &ldquo;%2$s&rdquo;', '%1$s comments on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'quickstrap' ), number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
	?>
</h4>

<ol class="commentlist">
    <?php wp_list_comments(''); ?>
</ol>

<nav>
    <ul class="pagination">
        <li class="page-item p-2">
            <?php previous_comments_link() ?>
        </li>
        <li class="page-item p-2">
            <?php next_comments_link() ?>
        </li>
    </ul>
</nav>

<?php elseif ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
	<p>
  	 <i class="far fa-comment"></i> <?php _e( 'Comments are closed here.', 'quickstrap' ); ?>
	</p>
<?php endif; ?>

<?php comment_form(); ?>
</div> 
