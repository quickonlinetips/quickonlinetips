<?php
/*
 * The left sidebar: a narrow column on the left on wide screens.
 * On tablets and phones it used to be hidden, taking its widgets with it;
 * now it moves below the posts and the right sidebar instead.
 * With no widgets it isn't printed at all, and the posts column widens.
 */
if ( ! quickstrap_has_left_sidebar() ) {
	return;
}
?>
<aside id="sidebar-left" class="sidebar sidebar-left col-12 col-lg-2 order-3 order-lg-1" aria-label="<?php esc_attr_e( 'Left sidebar', 'quickstrap' ); ?>">
	<?php dynamic_sidebar( 'left-sidebar' ); ?>
</aside>
<!-- /sidebar-left -->
