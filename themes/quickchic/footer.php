	</div> <!-- end row -->

	<footer id="footer">

		<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
			<div class="footer-widgets">
				<?php for ( $quickchic_i = 1; $quickchic_i <= 3; $quickchic_i++ ) : ?>
					<?php if ( is_active_sidebar( 'footer-' . $quickchic_i ) ) : ?>
						<div class="footer-column">
							<?php dynamic_sidebar( 'footer-' . $quickchic_i ); ?>
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
				'container_aria_label' => __( 'Footer menu', 'quickchic' ),
				'depth'                => 1,
			) );
		}
		?>

		<p>
			&copy; <?php esc_html_e( 'Copyright', 'quickchic' ); ?>
			<?php echo esc_html( wp_date( 'Y' ) ); ?> &ndash;
			<?php bloginfo( 'name' ); ?>
		</p>

		<p class="credits">
			<a href="https://wordpress.org/themes/quickchic/" rel="nofollow">QuickChic Theme</a> &bull; <?php esc_html_e( 'Powered by WordPress', 'quickchic' ); ?>
		</p>

		</div>

	</footer>

</div>
<!-- /page -->

<?php wp_footer(); ?>
</body>

</html>
