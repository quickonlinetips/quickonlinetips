</div> <!-- end row -->

<!-- Bottom Box -->
<div id="bottombox">

	
	<div id="adbox">
        <?php get_search_form(); ?>
    </div>

    <?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) ) : ?>
    <div class="bottom-widgets">
        <?php for ( $quickpic_i = 1; $quickpic_i <= 2; $quickpic_i++ ) : ?>
            <?php if ( is_active_sidebar( 'footer-' . $quickpic_i ) ) : ?>
                <div class="bottom-column">
                    <?php dynamic_sidebar( 'footer-' . $quickpic_i ); ?>
                </div>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
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
    <p class="credits">
        <a href="https://wordpress.org/themes/quickpic/" rel="nofollow">QuickPic Theme</a> &bull; <?php esc_html_e( 'Powered by WordPress', 'quickpic' ); ?>
    </p>
    <?php wp_footer(); ?>

</footer>
</div>
</div>

</body>

</html>
