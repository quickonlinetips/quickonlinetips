<footer class="footer">
	 <div class="container">

         <div class="row ">

			 <div class="xcol column singlecol wide6of12">
                            <?php if ( is_active_sidebar( 'footer1' ) ) : ?>
                            <?php dynamic_sidebar( 'footer1' ); ?>
                            <?php endif; ?>
                        </div>



						<div class="xcol column singlecol wide6of12">
                            <?php if ( is_active_sidebar( 'footer2' ) ) : ?>
                            <?php dynamic_sidebar( 'footer2' ); ?>
                            <?php endif; ?>
                        </div>

			 <!--
					<div class="xcol column singlecol wide4of12">
                            <?php if ( is_active_sidebar( 'footer3' ) ) : ?>
                            <?php dynamic_sidebar( 'footer3' ); ?>
                            <?php endif; ?>
                        </div>
			 -->


			</div>

	</div>
</footer>
