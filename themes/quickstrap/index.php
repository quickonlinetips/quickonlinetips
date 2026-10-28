<?php get_header(); ?>

<div id="content" class="<?php echo esc_attr( quickstrap_content_class() ); ?>">
	<main>

		<?php if ( is_home() && ! is_front_page() ) : ?>
			<h1 class="page-title"><?php single_post_title(); ?></h1>
		<?php endif; ?>

		<?php get_template_part( 'loop' ); ?>
		<?php get_template_part( 'pagination' ); ?>

	</main>
</div>

<?php get_sidebar(); ?>
<?php get_sidebar( 'left' ); ?>
<?php get_footer(); ?>
