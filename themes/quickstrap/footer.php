	</div> <!-- /row -->
	<?php do_action( 'quickstrap_after_content' ); // Full width, below the posts and sidebars. ?>
</div> <!-- /page -->

<?php do_action( 'quickstrap_before_footer' ); ?>

<footer id="colophon" class="site-footer">

	<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
		<div class="footer-widgets">
			<div class="<?php echo esc_attr( quickstrap_container_class() ); ?>">
				<div class="row">
					<?php for ( $quickstrap_i = 1; $quickstrap_i <= 3; $quickstrap_i++ ) : ?>
						<?php if ( is_active_sidebar( 'footer-' . $quickstrap_i ) ) : ?>
							<div class="footer-column col-md">
								<?php dynamic_sidebar( 'footer-' . $quickstrap_i ); ?>
							</div>
						<?php endif; ?>
					<?php endfor; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="site-info">
		<div class="<?php echo esc_attr( quickstrap_container_class() ); ?>">

			<?php
			if ( has_nav_menu( 'footer-nav' ) ) {
				wp_nav_menu( array(
					'theme_location'       => 'footer-nav',
					'container'            => 'nav',
					'container_class'      => 'footer-menu',
					'container_aria_label' => __( 'Footer menu', 'quickstrap' ),
					'depth'                => 1,
				) );
			}
			?>

			<?php do_action( 'quickstrap_footer_start' ); // In the footer band, above the copyright line. ?>

			<p>
				&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?>
				<?php bloginfo( 'name' ); ?>.
				<?php esc_html_e( 'All rights reserved.', 'quickstrap' ); ?>
			</p>

			<p class="credits">
				<a href="https://wordpress.org/themes/quickstrap/" rel="nofollow">QuickStrap Theme</a> &bull; <?php esc_html_e( 'Powered by WordPress', 'quickstrap' ); ?>
			</p>

		</div>
	</div>

</footer>

<?php do_action( 'quickstrap_after_footer' ); ?>

<?php wp_footer(); ?>
</body>

</html>
