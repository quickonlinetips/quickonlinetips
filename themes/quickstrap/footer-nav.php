<!-- Footer Nav -->
<aside class="footer-nav bg-light">

    <div class="container">
        <div class="row">

            <div class="col-md-4 col-sm-6">
                <div class="footer-widget">
                <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
					<?php dynamic_sidebar( 'footer-1' ); ?>
                <?php endif; ?>
				</div>
            </div>

            <div class="col-md-4 col-sm-6">               
                <div class="footer-widget">
                <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
				<?php dynamic_sidebar( 'footer-2' ); ?>
                <?php endif; ?>
	            </div>
            </div>

            <div class="col-md-4">
                <div class="footer-widget">
                <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
					<?php dynamic_sidebar( 'footer-3' ); ?>
                <?php endif; ?>
				</div>
            </div>

        </div>
        <!-- /row Footer Nav -->
    </div>
    <!-- /container Footer Nav -->
</aside>
