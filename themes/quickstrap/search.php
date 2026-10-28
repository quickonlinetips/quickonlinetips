<?php get_header(); ?>

<div id="content" class="<?php echo esc_attr( quickstrap_content_class() ); ?>">
	<main>

		<header class="page-header">
			<h1 class="page-title">
				<?php
				/* translators: %s: search words */
				printf( esc_html__( 'Search Results for: %s', 'quickstrap' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
				?>
			</h1>
		</header>

		<?php get_template_part( 'loop' ); ?>
		<?php get_template_part( 'pagination' ); ?>

	</main>
</div>

<?php get_sidebar(); ?>
<?php get_sidebar( 'left' ); ?>
<?php get_footer(); ?>
