<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >

	<header>
			<?php
				if ( function_exists('yoast_breadcrumb') ) {
  				yoast_breadcrumb( '<div id="breadcrumbs" class="small">','</div>' );
				}
			?>

			<div id="breadcrumbs" class="small">
			<?php if (function_exists('rank_math_the_breadcrumbs')) rank_math_the_breadcrumbs(); ?>
			</div>

		    <h1 class="entry-title single-title"><?php the_title(); ?></h1>

			 <div class="entry-meta post-category small mb-2 grey ">

					 <?php if ( in_category( array( 'how-to', '' ) ) || has_tag( array( 'updated', 'tutorials', 'popular' ) )  ) : ?>
    	      <time class="date updated" datetime="<?php the_modified_time('Y-m-d'); ?> <?php the_modified_time('H:i'); ?>">
       		  Updated <?php the_modified_date('F j, Y'); ?>
       		  </time>
				 &bull;

			 <?php else: ?>
				  <time class="date published" datetime="<?php the_time('Y-m-d'); ?> <?php the_time('H:i'); ?>">
                Posted <?php the_date('F j, Y'); ?>
                	</time>

					&bull;

         <?php endif; ?>

					By <span class="entry-author vcard author"><span class="entry-author-name fn" rel="author" >
				QOT</span>  </span>


			<?php if (is_user_logged_in() || current_user_can('edit_post')) { ?>
                <a class="post-edit-link float-right btn btn-light btn-sm"
				   href="https://www.quickonlinetips.com/archives/wp-admin/post.php?post=<?php the_ID(); ?>&amp;action=edit">EDIT</a>
                <?php } ?>

				 <?php if (is_user_logged_in() || current_user_can('edit_post')) { ?>
				<?php
					if ( get_post_status ( $ID ) == 'pending' ) {
					echo '<span class="button">PENDING</span>';
					} else {
					echo '';
					}
				?>

			<?php
			if ( get_post_status ( $ID ) == 'draft' ) {
			echo '<span class="button">DRAFT</span>';
			} else {
			echo '';
			}
			?>
			<?php } ?>

		</div>



    </header>


		<div class="entry-content">
 			<?php the_content();?>


			<?php if (in_category ( 44 )) { ?>
            <div class="py-2 mb-2">
                <i>Note: This is a guest article and represents opinions of the guest author</i>
            </div>
            <?php } ?>

        </div>
		<!-- /entry-content -->

		<footer class="my-3 ">

			      		  <?php get_template_part('template-parts/share/share2'); ?>


		</footer>

</article>


<?php if ( is_active_sidebar( 'bottom-post' ) ) : ?>
        <div class="my-3">
            <?php dynamic_sidebar( 'bottom-post' ); ?>
        </div>
		<?php endif; ?>




        <nav class="my-3">
            <div class="btn btn-lg font-weight-bold  border btn-light  button outline primary"><?php previous_post_link('%link', '&laquo; Previous Post') ?></div>


			<!--
&nbsp;
            <div class="btn btn-lg font-weight-bold  border btn-light  button outline primary"><?php next_post_link('%link', 'Next Post &raquo;') ?></div>
-->

</nav>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
