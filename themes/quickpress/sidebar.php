<aside id="sidebar" class="sidebar column right" aria-label="<?php esc_attr_e( 'Sidebar', 'quickpress' ); ?>">

	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<div class="sidebar-full">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( is_active_sidebar( 'sidebar-2' ) || is_active_sidebar( 'sidebar-3' ) ) : ?>
		<div class="sidebar-split">
			<?php if ( is_active_sidebar( 'sidebar-2' ) ) : ?>
				<div class="subcol">
					<?php dynamic_sidebar( 'sidebar-2' ); ?>
				</div>
			<?php endif; ?>
			<?php if ( is_active_sidebar( 'sidebar-3' ) ) : ?>
				<div class="subcol">
					<?php dynamic_sidebar( 'sidebar-3' ); ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</aside>
<!-- /sidebar -->
