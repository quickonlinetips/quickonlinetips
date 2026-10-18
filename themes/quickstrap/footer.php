</div>
<!-- /end row -->
</div>
<!-- /end container -->

           
 
<footer class="container-fluid main-footer bg-light mt-3">
    <div class="row">
        <div class="col-md-12">

 	<?php get_template_part('footer-nav'); ?>

            <div class="footer text-center border-top pt-3" role="contentinfo">
                     &copy;
                    <?php echo date('Y'); ?>
                    <?php bloginfo('name'); ?>
                    <?php _e('- All rights reserved.', 'quickstrap' ); ?>         
            </div>

            <?php wp_footer(); ?>
				
        </div>
        <!-- /end col -->
    </div>
    <!-- /end row -->
</footer>
<!-- /end container-fluid -->

</body>

</html>
