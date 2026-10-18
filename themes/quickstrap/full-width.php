<?php
/*
Template Name: Full Width
*/
?>

    <?php get_header(); ?>

    <!-- Content -->
    <div class="col-md-12">

        <main role="main">
            <section>

                <?php if (have_posts()): while (have_posts()) : the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <h1>
                        <?php the_title(); ?>
                    </h1>
                    <?php the_content(); ?>
                    <?php comments_template( '', true ); // Remove if you don't want comments ?>

                    <br class="clear">
                    <?php edit_post_link(); ?>

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

    <?php get_footer(); ?>
