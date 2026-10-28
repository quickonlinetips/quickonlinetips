	</div> <!-- end row -->

	<footer id="footer">

		<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
			<div class="footer-widgets">
				<?php for ( $quickpress_i = 1; $quickpress_i <= 3; $quickpress_i++ ) : ?>
					<?php if ( is_active_sidebar( 'footer-' . $quickpress_i ) ) : ?>
						<div class="footer-column">
							<?php dynamic_sidebar( 'footer-' . $quickpress_i ); ?>
						</div>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
		<?php endif; ?>

		<div class="site-info">

		<?php
		if ( has_nav_menu( 'footer' ) ) {
			wp_nav_menu( array(
				'theme_location'       => 'footer',
				'container'            => 'nav',
				'container_class'      => 'footer-menu',
				'container_aria_label' => __( 'Footer menu', 'quickpress' ),
				'depth'                => 1,
			) );
		}
		?>

		<p>
			&copy; <?php esc_html_e( 'Copyright', 'quickpress' ); ?>
			<?php echo esc_html( wp_date( 'Y' ) ); ?> &ndash;
			<?php bloginfo( 'name' ); ?>
		</p>

		<p class="credits">
			<a href="https://wordpress.org/themes/quickpress/" rel="nofollow">QuickPress Theme</a> &bull; <?php esc_html_e( 'Powered by WordPress', 'quickpress' ); ?>
		</p>

		</div>

	</footer>

</div>
<!-- /page -->

<?php wp_footer(); ?>
</body>

</html>
