<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

	<?php quickstrap_post_meta(); ?>

	<div class="entry">

		<?php if ( has_post_thumbnail() ) : ?>
			<a class="post-thumb alignright" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php the_post_thumbnail( 'thumbnail' ); ?>
			</a>
		<?php endif; ?>

		<?php
		if ( quickstrap_show_full_posts() ) {
			// Full post, up to a More block if the post has one.
			the_content( esc_html__( 'Read More', 'quickstrap' ) . ' &raquo;' );
		} else {
			the_excerpt();
		}
		?>

	</div>

</article>

<?php if ( quickstrap_show_between_posts() ) : ?>
	<aside class="between-posts" aria-label="<?php esc_attr_e( 'Between posts', 'quickstrap' ); ?>">
		<?php dynamic_sidebar( 'between-posts' ); ?>
	</aside>
<?php endif; ?>

<?php endwhile; else : ?>

<article class="no-results">
	<h2><?php esc_html_e( 'Nothing Found', 'quickstrap' ); ?></h2>
	<p><?php esc_html_e( 'Nothing matched here. Try a search instead.', 'quickstrap' ); ?></p>
	<?php get_search_form(); ?>
</article>

<?php endif; ?>
