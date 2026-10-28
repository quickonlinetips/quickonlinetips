<?php get_header(); ?>

<div id="content" class="<?php echo esc_attr( quickstrap_content_class() ); ?>">
	<main>

		<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<h1 class="entry-title"><?php the_title(); ?></h1>

			<div class="entry">

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="post-image"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>

				<?php the_content(); ?>

				<?php
				wp_link_pages( array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'quickstrap' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
				) );
				?>
			</div>

			<?php edit_post_link( __( 'Edit', 'quickstrap' ), '<p class="post-meta">', '</p>' ); ?>

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
<?php get_sidebar( 'left' ); ?>
<?php get_footer(); ?>
