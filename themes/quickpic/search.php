<?php get_header(); ?>
<div id="content" class="narrowcolumn  column">

    <main>
        <section>

            <h1>
                <?php printf( __( 'Search Results for: %s', 'quickpic' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
            <?php get_template_part('loop'); ?>
            <?php get_template_part('pagination'); ?>

        </section> 
    </main>

</div>

<?php get_footer(); ?>
