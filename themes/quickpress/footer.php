	</div> <!-- end row -->

	<footer id="footer">

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
