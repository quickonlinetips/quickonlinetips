<?php
/*
 * The left sidebar: a narrow column on the left on wide screens.
 * On tablets and phones it used to be hidden, taking its widgets with it;
 * now it moves below the posts and the right sidebar instead.
 * While both sidebars are empty it shows a default Categories list, so a new
 * site shows all three columns. Otherwise, with no widgets it isn't printed
 * at all, and the posts column widens.
 */
if ( ! quickstrap_has_left_sidebar() ) {
	return;
}
?>
<aside id="sidebar-left" class="<?php echo esc_attr( quickstrap_column_class( 'left' ) ); ?>" aria-label="<?php esc_attr_e( 'Left sidebar', 'quickstrap' ); ?>">
	<?php
	if ( is_active_sidebar( 'left-sidebar' ) ) {
		dynamic_sidebar( 'left-sidebar' );
	} else {
		quickstrap_default_sidebar( 'left' );
	}
	?>
</aside>
<!-- /sidebar-left -->
