<?php
// The Full width template has no sidebars.
if ( quickstrap_is_full_width() ) {
	return;
}
?>
<aside id="sidebar" class="sidebar sidebar-right col-md-4 order-2 order-lg-3" aria-label="<?php esc_attr_e( 'Sidebar', 'quickstrap' ); ?>">
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
