<?php get_header(); ?>

<div id="content" class="<?php echo esc_attr( quickstrap_content_class() ); ?>">
	<main>

		<header class="page-header">
			<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
		</header>

		<?php get_template_part( 'loop' ); ?>
		<?php get_template_part( 'pagination' ); ?>

	</main>
</div>

<?php get_sidebar(); ?>
<?php get_sidebar( 'left' ); ?>
<?php get_footer(); ?>
