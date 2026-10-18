<?php get_header(); ?>

<?php get_sidebar ('left'); ?>

<!-- Content -->
<div class="col-lg-6 col-md-8 col-sm-8">

    <?php if ( is_active_sidebar( 'top-post' ) ) : ?>
    <div class="sidebar-widget">
        <?php dynamic_sidebar( 'top-post' ); ?>
    </div>
    <?php endif; ?>


    <main>
        <section>

            <?php if (have_posts()): while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <h1 title="<?php the_title_attribute(); ?>" class="entry-title"><?php the_title(); ?></h1>

                <div class="post-info border-bottom mb-3 text-muted small">
                    <span class="date"><i class="fas fa-pen-nib"></i>
  				<time class="published" datetime="<?php the_time('Y-m-d'); ?> <?php the_time('H:i'); ?>">
				<?php the_date(); ?> 
				</time> 
					</span>  |
                    <?php _e( 'By ', 'quickstrap' ); the_author(); ?>

                    <p>
                        <i class="fas fa-folder-open"></i>
                        <?php _e( 'Filed in: ', 'quickstrap' ); the_category(', '); ?> &nbsp; 
                        <?php the_tags( __( 'Tags: ', 'quickstrap' ), ', ', ''); ?>

                        <?php edit_post_link(); // Edit Post Links ?>
                    </p>
                </div>
                <!-- /post-info -->

                <div class="entry-content">
                    <?php the_content(); ?>
                    <br />
                    <?php wp_link_pages(array(
					'before' => '<div class="page-links"><span class="page-links-title">' . __( '<b>Pages:</b>', 'quickstrap' ) . '</span>', 
					'after' => '</div>',
					'link_before' => '<span>', 
					'link_after'  => '</span>',
					'next_or_number' => 'number'
					)); 
				?>
                </div>
                <!-- /entry-content -->

                <?php comments_template(); ?>
            </article>

            <?php endwhile; ?>
            <?php else: ?>
            <article>
                <h2>
                    <?php _e( 'Content Not Found', 'quickstrap'); ?>
                </h2>
            </article>
            <?php endif; ?>

        </section>
    </main>
    <?php if ( is_active_sidebar( 'bottom-post' ) ) : ?>
    <div class="sidebar-widget">
        <?php dynamic_sidebar( 'bottom-post' ); ?>
    </div>
    <?php endif; ?>
</div>
<!-- /end content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
