<?php get_header(); ?>

<?php if ( is_active_sidebar( 'archives-top' ) ) : ?>
            <div class="">
                  <?php dynamic_sidebar( 'archives-top' ); ?>
            </div>
            <?php endif; ?>


<?php get_template_part('loop'); ?>


<?php if ( is_active_sidebar( 'archives-bot' ) ) : ?>
            <div class="">
                  <?php dynamic_sidebar( 'archives-bot' ); ?>
            </div>
        <?php endif; ?>

<?php get_template_part('template-parts/nav/pagination'); ?>

<?php get_footer(); ?>
