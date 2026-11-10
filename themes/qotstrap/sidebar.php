<aside class="col-12-sm col-4-md col-4-lg sidebar  wide4of12 ml-2 mt-3 column singlecol ">

	  <?php if ( is_active_sidebar( 'sidebar-common' ) ) : ?>
            <?php dynamic_sidebar( 'sidebar-common' ); ?>
      <?php endif; ?>

	<?php if (is_single()) { ?>
      <?php if ( is_active_sidebar( 'sidebar-singular' ) ) : ?>
            <?php dynamic_sidebar( 'sidebar-singular' ); ?>
      <?php endif; ?>
     <?php } ?>

		<?php if (is_home()) { ?>
      <?php if ( is_active_sidebar( 'homepage5' ) ) : ?>
            <?php dynamic_sidebar( 'homepage5' ); ?>
      <?php endif; ?>
     <?php } ?>

</aside>
