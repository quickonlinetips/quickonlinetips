<div id="comments" class="comments">
      <?php if (post_password_required()) : ?>
      <p>
            <?php _e( 'Post is password protected. Enter the password to view any comments.', 'qotstrap' ); ?>
      </p>
      <?php return; endif; ?>
      <?php if (have_comments()) : ?>

	<h3><?php comments_number(); ?></h3>

	 <?php if (is_user_logged_in() || current_user_can('edit_post')) { ?>
              <a class="float-right btn btn-light btn-sm" href="https://www.quickonlinetips.com/archives/wp-admin/edit-comments.php?p=<?php the_ID(); ?>" target="_blank"><i class="fas fa-edit"></i>EDIT</a>
          <?php } ?>



      <ol class="commentlist">

		   <?php
        wp_list_comments( array(
            'avatar_size' => 60,
            'max_depth'   => 5,
        ) );
    ?>
      </ol>



      <nav class=""> <!-- Hiding comments nav -->
      <ul class="pagination ">
            <li class="previous mr-2 page-item btn font-weight-bold text-uppercase">
                  <?php previous_comments_link() ?>
            </li>
            <li class="next ml-2 page-item btn font-weight-bold text-uppercase">
                  <?php next_comments_link() ?>
            </li>
      </ul>

</nav>
<?php elseif ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
<p>
      <?php _e( 'Comments are closed here.', 'qotstrap' ); ?>
</p>
<?php endif; ?>




	<div class="bg-light p-3">
<?php comment_form(); ?>
	</div>

</div>
