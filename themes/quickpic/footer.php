</div> <!-- end row -->

<!-- Bottom Box -->
<div id="bottombox">

	
	<div id="adbox">
        <?php get_search_form(); ?>
    </div>

    <div style="clear:both;"></div>

    <div id="" class="row">
        <div id="" class="column">
            <!-- Widgetized footer  1 -->
            <ul>
                <li>
                    <div>
					<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                    <?php dynamic_sidebar( 'footer-1' ); ?>
                    <?php endif; ?>
                	</div>
				</li>
            </ul>
        </div>

        <div id="" class="column">
            <!-- Widgetized footer 2 -->
            <ul>
                <li>
                    <div>
					<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
                    <?php dynamic_sidebar( 'footer-2' ); ?>
                    <?php endif; ?>
					</div>
                </li>
            </ul>
        </div>
        <div style="clear:both;"></div>
    </div>
    <!-- /Boxes -->
</div>
<!-- /Bottom Box -->

<footer id="footer">

    <p>
        &copy;
        <?php _e('Copyright', 'quickpic'); ?>
        <?php echo date('Y'); ?> -
        <?php bloginfo('name'); ?>
    </p>
    <?php wp_footer(); ?>

</footer>
</div>
</div>

</body>

</html>
