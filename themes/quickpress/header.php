<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
    <div id="page">

    <header id="header">
			
            <div class="searchform header-search">
                <!-- Search -->
                <?php get_search_form(); ?>
            </div>
			
            <div class="blog-top">
                <?php if (is_home()) { ?>
                <h1>
                    <?php } ?>
                    <a class="blogtitle" href="<?php echo esc_url( home_url() ); ?>">
                        <?php bloginfo('name'); ?>
                    </a>
                    <?php if (is_home()) { ?>
                </h1>
                <?php } ?>
                <div class="description">
                    <?php bloginfo('description'); ?>
                </div>
            </div>
			
			 <div class="searchform header-search-mobile">
                <!-- Search -->
                <?php get_search_form(); ?>
            </div>
			
        </header>

        <nav id="menu-header">
            <!-- Header Menu -->
            <?php wp_nav_menu( array( 
	'theme_location' => 'header-nav', 
	'menu_class' => 'nav navbar-nav',
	'container' => false,
	'depth' => 1,
	// do not fall back to wp_page_menu()
	'fallback_cb' => false
 ) ); ?>
        </nav>

		<div class="row"> <!-- start row -->