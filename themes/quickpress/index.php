<?php get_header(); ?>
<div id="content" class="content column left">

    <main>
        <section>

            <?php get_template_part('loop'); ?>
            <?php get_template_part('pagination'); ?>

        </section>
    </main>

</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
