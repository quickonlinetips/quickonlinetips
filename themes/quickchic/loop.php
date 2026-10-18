<?php if (have_posts()): while (have_posts()) : the_post(); ?>

<article <?php post_class() ?> id="post-<?php the_ID(); ?>">

    <h2>
        <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
            <?php the_title(); ?>
        </a>
    </h2>
    <small><?php the_time(get_option('date_format')); ?> | 
	<?php _e( 'By ', 'quickchic' ); the_author(); ?> |
   	<?php comments_popup_link('No Comments', '1 Comment', '% Comments'); ?> 
	| <?php _e( 'Filed in: ', 'quickchic' ); the_category(', '); ?>. <?php edit_post_link('Edit'); ?>  </small>


    <div class="entry">

        <!-- post thumbnail -->
        <?php if ( has_post_thumbnail()) : // Check if Thumbnail exists ?>
        <p class="alignright">
            <a class="thumbnail" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php the_post_thumbnail( 'thumbnail' );  ?>
            </a>
        </p>
        <?php endif; ?>
        <!-- /post thumbnail -->

        <?php the_excerpt(); ?>
 
	</div>
 
</article>

<?php endwhile; ?>
<?php else: ?>

<article>
    <h2>
        <?php _e( 'Content Not Found', 'quickchic'); ?>
    </h2>
</article>

<?php endif; ?>
