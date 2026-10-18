<?php if (have_posts()): while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <h2>
        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <?php the_title(); ?>
        </a>
    </h2>

    <!-- post thumbnail -->
    <?php if ( has_post_thumbnail()) : // Check if Thumbnail exists ?>
    <div class="float-right rounded">
        <a class="ml-3" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <?php the_post_thumbnail( 'thumbnail', ['class' => 'rounded'] );  ?>
        </a>
    </div>
    <?php endif; ?>
    <!-- /post thumbnail -->

    <div class="post-info text-muted small">
        <span class="date"> 
	  <time datetime="<?php the_time('Y-m-d'); ?> <?php the_time('H:i'); ?>">
	  <?php echo get_the_date(); ?>
	  </time>
        </span> |
        <span class="author"><?php _e( 'By ', 'quickstrap' ); ?><?php the_author_posts_link(); ?></span> |
        <span class="comments"><?php comments_number( 'Post comment', '1 comment', '% comments' ); ?></span>

        <?php edit_post_link(); ?>
    </div>
    <!-- /post-info -->

    <?php the_excerpt(); ?>

</article> 
<hr>

<?php endwhile; ?>
<?php else: ?>

<article>
    <h2>
        <?php _e( 'Content Not Found', 'quickstrap'); ?>
    </h2>
</article>

<?php endif; ?>
