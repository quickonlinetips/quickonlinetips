<?php
// The Full width template has no sidebars.
if ( quickstrap_is_full_width() ) {
	return;
}
?>
<aside id="sidebar" class="<?php echo esc_attr( quickstrap_column_class( 'right' ) ); ?>" aria-label="<?php esc_attr_e( 'Sidebar', 'quickstrap' ); ?>">
	<?php
	if ( is_active_sidebar( 'right-sidebar' ) ) {
		dynamic_sidebar( 'right-sidebar' );
	} else {
		// No widgets yet (a new site, or widgets parked in Inactive): show a few useful blocks.
		quickstrap_default_sidebar();
	}
	?>
</aside>
<!-- /sidebar -->
