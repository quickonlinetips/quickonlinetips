<?php get_header(); ?>
<div id="content" class="content column left">
    <main>
        <section>

            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <h1><?php the_title(); ?></h1>
                
                <small><a href="<?php the_permalink() ?>"><?php the_time(get_option('date_format')); ?></a> | <?php _e( 'By ', 'quickpress' ); the_author(); ?> | <?php _e( 'Filed in: ', 'quickpress' ); the_category(', '); ?>. <?php edit_post_link('Edit'); ?>  </small>

                <div class="entry">
                    <?php the_content(); ?>

                    <?php if (is_attachment()) { ?>
                    <span class="alignleft"><?php previous_image_link( false, '&larr; Previous' ); ?></span>
                    <span class="alignright"><?php next_image_link( false, 'Next &rarr;'); ?></span>
                    <?php } ?>
                    <br />

                    <?php wp_link_pages(array(
		'before' => '<div class="page-links"><span class="page-links-title">' . __( '<b>Pages:</b>', 'quickpress' ) . '</span>', 
		'after' => '</div>',
		'link_before' => '<span>', 
		'link_after'  => '</span>',
		'next_or_number' => 'number'
		)); 
		?>

                    <p>
                        <?php the_tags( __( 'Tags: ', 'quickpress' ), ', ', ''); ?>
                    </p>

                    <div class="navigation">
                        <div class="alignleft">
                            <?php previous_post_link('&laquo; %link') ?>
                        </div>
                        <div class="alignright">
                            <?php next_post_link('%link &raquo;') ?>
                        </div>
                    </div>

                    <?php comments_template(); ?>
                </div>
            </article>
            
            <?php endwhile; ?>
            <?php else: ?>
            <article>
                <h2>
                    <?php _e( 'Content Not Found', 'quickpress'); ?>
                </h2>
            </article>
            <?php endif; ?>


        </section>
    </main>
    </div>
    <?php get_sidebar(); ?>
    <?php get_footer(); ?>
