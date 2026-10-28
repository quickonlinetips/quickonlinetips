<?php get_header(); ?>

<div id="content" class="content column left">
	<main>

		<header class="page-header">
			<h1 class="page-title">
				<?php
				/* translators: %s: search words */
				printf( esc_html__( 'Search Results for: %s', 'quickpress' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
				?>
			</h1>
		</header>

		<?php get_template_part( 'loop' ); ?>
		<?php get_template_part( 'pagination' ); ?>

	</main>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
