<nav class="navbar navbar-dark bg-dark shadow py-3 navbar-expand-lg mb-5">
  <div class="container">
			<a class="navbar-brand" href="<?php echo esc_url( home_url() ); ?>">
                <?php bloginfo('name'); ?>
            </a> 
	 		 	  
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
	  	  
	<div class="collapse navbar-collapse" id="navbarNav">
<!-- Header Menu -->
            <?php wp_nav_menu( array( 
				'theme_location' => 'header-nav', 
				'menu_class' => 'navbar-nav',
				'container' => 'div',
				'depth' => 1,
				// do not fall back to wp_page_menu()
				'fallback_cb' => false
			 ) ); ?>
      </div>
      <!--/.nav-collapse -->	
  </div>    
  <!--/container --> 
</nav>
