<?php get_header(); ?>

<div id="content" class="content column left">
	<main>

		<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<h1 class="entry-title"><?php the_title(); ?></h1>

			<div class="entry">
				<?php the_content(); ?>

				<?php
				wp_link_pages( array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'quickpress' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
				) );
				?>
			</div>

			<?php edit_post_link( __( 'Edit', 'quickpress' ), '<p class="post-meta">', '</p>' ); ?>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>

		</article>
		<?php endwhile; ?>

	</main>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
