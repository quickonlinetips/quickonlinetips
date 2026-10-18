<?php get_header(); ?>

<?php get_sidebar ('left'); ?>

<!-- Content -->
<div class="col-lg-6 col-md-8 col-sm-8">

    <main role="main">
        <section>
			<h1 class="mb-4 border-bottom"><?php single_cat_title(); ?></h1>
            <?php get_template_part('loop'); ?>
            <?php get_template_part('pagination'); ?>
        </section>
    </main>
</div>
<!-- /end content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
