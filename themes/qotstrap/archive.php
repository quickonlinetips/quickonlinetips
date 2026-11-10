<?php get_header(); ?>

	<header class="header">
	<h1 class="entry-title text-center"><?php single_term_title(); ?></h1>
	<div class="archive-meta text-center"><?php if ( '' != the_archive_description() ) { echo esc_html( the_archive_description() ); } ?>		</div>
	</header>

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
