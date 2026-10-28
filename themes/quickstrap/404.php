<?php get_header(); ?>

<div id="content" class="<?php echo esc_attr( quickstrap_content_class() ); ?>">
	<main>

		<article id="post-404" class="no-results">
			<h1 class="page-title"><?php esc_html_e( 'Page Not Found', 'quickstrap' ); ?></h1>
			<p><?php esc_html_e( 'The page you were looking for is not here. Try a search, or go to the home page.', 'quickstrap' ); ?></p>
			<?php get_search_form(); ?>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">&larr; <?php esc_html_e( 'Home', 'quickstrap' ); ?></a></p>
		</article>

	</main>
</div>

<?php get_sidebar(); ?>
<?php get_sidebar( 'left' ); ?>
<?php get_footer(); ?>
