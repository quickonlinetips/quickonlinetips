	</div> <!-- end row -->

	<!-- Bottom box: the green band with a search box and widgets -->
	<aside id="bottombox" class="bottom-box" aria-label="<?php esc_attr_e( 'More from this site', 'quickpic' ); ?>">

		<div id="adbox" class="bottom-search">
			<?php get_search_form(); ?>
		</div>

		<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
			<div class="bottom-widgets">
				<?php for ( $quickpic_i = 1; $quickpic_i <= 3; $quickpic_i++ ) : ?>
					<?php if ( is_active_sidebar( 'footer-' . $quickpic_i ) ) : ?>
						<div class="bottom-column">
							<?php dynamic_sidebar( 'footer-' . $quickpic_i ); ?>
						</div>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
		<?php else : ?>
			<?php // No widgets yet (a new site, or widgets parked in Inactive): show a few useful blocks. ?>
			<?php quickpic_default_widgets(); ?>
		<?php endif; ?>

	</aside>
	<!-- /bottombox -->

	<footer id="footer">

		<div class="site-info">

		<?php
		if ( has_nav_menu( 'footer' ) ) {
			wp_nav_menu( array(
				'theme_location'       => 'footer',
				'container'            => 'nav',
				'container_class'      => 'footer-menu',
				'container_aria_label' => __( 'Footer menu', 'quickpic' ),
				'depth'                => 1,
			) );
		}
		?>

		<p>
			&copy; <?php esc_html_e( 'Copyright', 'quickpic' ); ?>
			<?php echo esc_html( wp_date( 'Y' ) ); ?> &ndash;
			<?php bloginfo( 'name' ); ?>
		</p>

		<p class="credits">
			<a href="https://wordpress.org/themes/quickpic/" rel="nofollow">QuickPic Theme</a> &bull; <?php esc_html_e( 'Powered by WordPress', 'quickpic' ); ?>
		</p>

		</div>

	</footer>

</div>
<!-- /page -->

<?php wp_footer(); ?>
</body>

</html>
