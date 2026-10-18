<div id="sidebar" class="sidebar column right">
    <aside>

        <!-- Remove this box for pure 3-columns -->
        <div id="adbox">
            <ul>
                <li>
                    <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
                    <?php dynamic_sidebar( 'sidebar-1' ); ?>
					    <?php else : ?> 					    	
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <!-- /Remove this box for pure 3-columns -->

        <!-- Start sub-columns -->
		<div class="row"> 
		
        <!-- Left_sidebar starts -->
        <div id="" class="column subcol">
            <ul>
                <li>
                    <?php if ( is_active_sidebar( 'sidebar-2' ) ) : ?>
                    <?php dynamic_sidebar( 'sidebar-2' ); ?>
					
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <!-- /l_sidebar-->

        <!-- Right_sidebar starts -->
        <div id="" class="column subcol">
            <ul>
                <li>
                    <?php if ( is_active_sidebar( 'sidebar-3' ) ) : ?>
					<?php dynamic_sidebar( 'sidebar-3' ); ?> 
					
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <!-- /r_sidebar-->

		</div>   <!-- End sub-columns -->

    </aside>
</div>
<!-- /sidebar-->
