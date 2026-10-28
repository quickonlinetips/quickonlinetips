<?php get_header(); ?>

<div id="content" class="content column left">
	<main>

		<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<h1 class="entry-title"><?php the_title(); ?></h1>

			<?php quickpress_post_meta( false ); ?>

			<div class="entry">

				<?php if ( has_post_thumbnail() && ! is_attachment() ) : ?>
					<figure class="post-image"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>

				<?php the_content(); ?>

				<?php if ( is_attachment() ) : ?>
					<nav class="navigation image-nav">
						<span class="alignleft"><?php previous_image_link( false, '&larr; ' . __( 'Previous', 'quickpress' ) ); ?></span>
						<span class="alignright"><?php next_image_link( false, __( 'Next', 'quickpress' ) . ' &rarr;' ); ?></span>
					</nav>
				<?php endif; ?>

				<?php
				wp_link_pages( array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'quickpress' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
				) );
				?>

				<?php the_tags( '<p class="post-tags">' . esc_html__( 'Tags: ', 'quickpress' ), ', ', '</p>' ); ?>

			</div>

			<?php if ( ! is_attachment() ) : ?>
				<nav class="navigation post-nav" aria-label="<?php esc_attr_e( 'Posts', 'quickpress' ); ?>">
					<div class="alignleft"><?php previous_post_link( '&laquo; %link' ); ?></div>
					<div class="alignright"><?php next_post_link( '%link &raquo;' ); ?></div>
				</nav>
			<?php endif; ?>

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
