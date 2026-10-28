<?php if ( is_active_sidebar( 'sidebar' ) ) : ?>
<aside id="sidebar" class="sidebar column right" aria-label="<?php esc_attr_e( 'Sidebar', 'quickchic' ); ?>">
	<?php dynamic_sidebar( 'sidebar' ); ?>
</aside>
<!-- /sidebar -->
<?php endif; ?>
