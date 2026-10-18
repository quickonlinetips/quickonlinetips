<?php get_header(); ?>

<?php get_sidebar ('left'); ?>

<!-- Content -->
<div class="col-lg-6 col-md-8 col-sm-8">

    <main role="main">
        <section>
            <article id="post-404">
                <h1>
                    <?php _e( 'Page Not Found', 'quickstrap' ); ?>
                </h1>
            </article>
        </section>
    </main>

    <?php get_sidebar(); ?>
    <?php get_footer(); ?>
