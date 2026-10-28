<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

	<?php quickpic_post_meta(); ?>

	<div class="entry">

		<?php if ( has_post_thumbnail() ) : ?>
			<a class="post-thumb alignright" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php the_post_thumbnail( 'thumbnail' ); ?>
			</a>
		<?php endif; ?>

		<?php the_excerpt(); ?>

	</div>

</article>

<?php endwhile; else : ?>

<article class="no-results">
	<h2><?php esc_html_e( 'Nothing Found', 'quickpic' ); ?></h2>
	<p><?php esc_html_e( 'Nothing matched here. Try a search instead.', 'quickpic' ); ?></p>
	<?php get_search_form(); ?>
</article>

<?php endif; ?>
