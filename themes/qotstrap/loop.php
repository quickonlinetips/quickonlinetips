<?php if (have_posts()): while (have_posts()) : the_post(); ?>


<article class="archive-post hentry my-2 py-2" id="post-<?php the_ID(); ?>">

		<div class="entry-meta text-grey">
	<!--
			<time class="date published small text-secondary" datetime="<?php the_time('Y-m-d'); ?> <?php the_time('H:i'); ?>">
                <?php the_date('d-m-Y'); ?>
                	</time>



			<?php if ( in_category( array( 'how-to', '' ) ) || has_tag( array( 'updated', 'tutorials', 'popular' ) )  ) : ?>
    	      <time class="date updated small text-secondary" datetime="<?php the_modified_time('Y-m-d'); ?> <?php the_modified_time('H:i'); ?>">
       		  <?php the_modified_date(); ?>
       		  </time>

			 <?php else: ?>
				  <time class="date published small text-secondary" datetime="<?php the_time('Y-m-d'); ?> <?php the_time('H:i'); ?>">
                <?php echo get_the_date(); ?>
                	</time>
			<?php endif; ?>

		</div>
	-->

	<?php
	the_post_thumbnail('thumbnail', array('class' => 'float-end ml-2 pull-right mx-2 h50 hide-xs'));
		?>

    <h2 class=" entry-title my-2">
		<a class="" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>">
        <?php the_title(); ?>
    	</a>
    </h2>




	  <div class="entry-summary small">
        		<?php the_excerpt (); ?>
			</div>

</article>



<?php endwhile; ?>
<?php else: ?>

 <div class="text-center p-4 my-4 alert alert-info">
                        No results to show.
			 </div>

<?php endif; ?>
