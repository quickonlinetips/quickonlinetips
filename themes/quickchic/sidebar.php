<?php
// The Full width template has no sidebar.
if ( quickchic_is_full_width() ) {
	return;
}
?>
<aside id="sidebar" class="sidebar column right" aria-label="<?php esc_attr_e( 'Sidebar', 'quickchic' ); ?>">
	<?php
	if ( is_active_sidebar( 'sidebar' ) ) {
		dynamic_sidebar( 'sidebar' );
	} else {
		// No widgets yet (a new site, or widgets parked in Inactive): show a few useful blocks.
		quickchic_default_sidebar();
	}
	?>
</aside>
<!-- /sidebar -->
