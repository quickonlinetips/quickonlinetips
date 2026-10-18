<?php get_header(); ?>

<?php get_sidebar ('left'); ?>

<!-- Content -->
<div class="col-lg-6 col-md-8 col-sm-8">

    <main role="main">
        <section>

            <?php if (have_posts()): while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <h1 class="border-bottom pb-2 mb-3"><?php the_title(); ?></h1>
                
                <?php edit_post_link(); ?>
				
                <?php the_content(); ?>
                <?php comments_template( '', true ); // Remove if you don't want comments ?>

                <br class="clear"> 

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
</div>
<!-- /end content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
