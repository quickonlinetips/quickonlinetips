<?php get_header(); ?>

<?php get_sidebar ('left'); ?>

<!-- Content -->
<div class="col-lg-6 col-md-8 col-sm-8">

    <main role="main">
        <!-- section -->
        <section>

            <h1>
                <?php printf( __( 'Search Results for: %s', 'quickstrap' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
            <?php get_template_part('loop'); ?>
            <?php get_template_part('pagination'); ?>

        </section>
        <!-- /section -->
    </main>
</div>
<!-- /end content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
