<!-- Sidebar -->
<div id="sidebar" class="col-lg-4 col-md-4 col-sm-4">

    <aside class="sidebar">
	    <div class="sidebar-widget">
        <?php if ( is_active_sidebar( 'right-sidebar' ) ) : ?>
			<?php dynamic_sidebar( 'right-sidebar' ); ?>
			<?php else : ?> 
        	<?php endif; ?>
		</div>
    </aside>

</div>
<!-- /end sidebar -->
