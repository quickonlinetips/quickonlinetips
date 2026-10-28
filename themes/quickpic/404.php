<?php get_header(); ?>

<div id="content" class="content column">
	<main>

		<article id="post-404" class="no-results">
			<h1 class="page-title"><?php esc_html_e( 'Page Not Found', 'quickpic' ); ?></h1>
			<p><?php esc_html_e( 'The page you were looking for is not here. Try a search, or go to the home page.', 'quickpic' ); ?></p>
			<?php get_search_form(); ?>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">&larr; <?php esc_html_e( 'Home', 'quickpic' ); ?></a></p>
		</article>

	</main>
</div>

<?php get_footer(); ?>
