<!-- Left Sidebar -->
<div class="col-lg-2 d-none d-md-none d-lg-block">

    <aside class="sidebar-left" role="complementary">   
        <div class="sidebar-widget">
        <?php if ( is_active_sidebar( 'left-sidebar' ) ) : ?>
			<?php dynamic_sidebar( 'left-sidebar' ); ?>
			<?php else : ?> 
        	<?php endif; ?>
		</div>
    </aside>

</div>
<!-- /end sidebar -->
