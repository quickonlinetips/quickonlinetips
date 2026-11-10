<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
            <header>


                  <h1 class="entry-title text-center py-2">
                  <?php the_title(); ?>
                  </h1>
            </header>


		  	<div class="entry-content">
          	<?php the_content();?>
		  	</div>

			<footer class="my-3">
       		 <?php get_template_part('template-parts/share/share2'); ?>

		   <?php if (is_user_logged_in() || current_user_can('edit_post')) { ?>
           <a title="Edit Post" class="post-edit-link" href="https://www.quickonlinetips.com/archives/wp-admin/post.php?post=<?php the_ID(); ?>&amp;action=edit">EDIT</a>
           <?php } ?>

			</footer>


</article>

<?php if ( is_active_sidebar( 'bottom-post' ) ) : ?>
        <div class="my-3">
            <?php dynamic_sidebar( 'bottom-post' ); ?>
        </div>
		<?php endif; ?>


<?php endwhile; endif; ?>

<?php get_footer(); ?>
