<?php

// content width
	if ( ! isset( $content_width ) ) {
	$content_width = 1170;
	}

// Setup
if ( ! function_exists( 'quickstrap_setup' ) ) :
function quickstrap_setup() {

	// Register menus
	register_nav_menus( array(
	'header-nav' => 'Header Menu',
	'footer-nav' => 'Footer Menu',
	) );

	// add title tag
	add_theme_support( 'title-tag' );

	// add text domain
	load_theme_textdomain( 'quickstrap', get_template_directory() . '/languages' );

	// Custom Background
	add_theme_support( 'custom-background', apply_filters( 'quickstrap_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );

	// more theme support
	add_theme_support('post-thumbnails');
	add_theme_support('automatic-feed-links');
	add_theme_support( "wp-block-styles");
	add_theme_support( "responsive-embeds");
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( "align-wide");  
}
endif; // quickstrap_setup
add_action( 'after_setup_theme', 'quickstrap_setup' );


// Register our sidebars and widgetized areas
function quickstrap_widgets_init() {
	
   // Right sidebar widgets
	register_sidebar( array(
	        'name' => __( 'Right Sidebar', 'quickstrap' ),
		'id' => 'right-sidebar',
		'before_widget' => '<div class="">',
		'after_widget' => '</p></div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4><p>',
	) );

	
  // Left Sidebar widgets
	register_sidebar( array(
	        'name' => __( 'Left Sidebar - Hides On Small Screens', 'quickstrap' ),
		'description' => 'Hidden on small screens',
		'id' => 'left-sidebar',
		'before_widget' => '<div class="">',
		'after_widget' => '</p></div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4><p>',
	) );

  // Footer widgets
	register_sidebar( array(
	        'name' => __( 'Footer Column 1', 'quickstrap' ),
		'id' => 'footer-1',
		'before_widget' => '<div class="py-3">',
		'after_widget' => '</p></div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4><p>',
	) );
	register_sidebar( array(
	        'name' => __( 'Footer Column 2', 'quickstrap' ),
		'id' => 'footer-2',
		'before_widget' => '<div class="py-3">',
		'after_widget' => '</p></div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4><p>',
	) );
	register_sidebar( array(
	        'name' => __( 'Footer Column 3', 'quickstrap' ),
		'id' => 'footer-3',
		'before_widget' => '<div class="py-3">',
		'after_widget' => '</p></div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4><p>',
	) );
	
  // Top and bottom post widgets
	register_sidebar( array(
	        'name' => __( 'Top of Post', 'quickstrap' ),
		'id' => 'top-post',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );
	register_sidebar( array(
	        'name' => __( 'Bottom of Post', 'quickstrap' ),
		'id' => 'bottom-post',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );
	
}
add_action( 'widgets_init', 'quickstrap_widgets_init' );

// add editor style
	function quickstrap_add_editor_styles() {
 	add_editor_style( 'custom-editor-style.css' );
	}
	add_action( 'admin_init', 'quickstrap_add_editor_styles' );

// read more
function quickstrap_excerpt_more( $more ) {
	return ' &bull;  <a class="read-more" href="'. get_permalink( get_the_ID() ) . '">' . __(' Read More &raquo;', 'quickstrap') . '</a>'; 
	}
	add_filter( 'excerpt_more', 'quickstrap_excerpt_more' );


// credits
function quickstrap_credits() {
    echo '<p class="text-center"><a href="https://wordpress.org/themes/quickstrap/" target="_blank" rel="nofollow">QuickStrap Theme</a>';
	}
	add_action('wp_footer', 'quickstrap_credits');

// load javascript
	function quickstrap_scripts() {
	wp_enqueue_style( 'quickstrap-bootstrap', 
			get_template_directory_uri() . '/css/bootstrap.min.css', 
			array(), 
			'5.3.0'
			);
		wp_enqueue_style( 'quickstrap', 
			get_stylesheet_uri(), 
			array(), 
			'1.3'
			);
	wp_enqueue_style( 'quickstrap-fontawesome', 
			get_template_directory_uri() . '/font-awesome/css/all.min.css', 
			array(), 
			'5.13.0'
			);
	wp_enqueue_script( 'quickstrap-bootstrap', 
			get_template_directory_uri() . '/js/bootstrap.min.js', 
			array(), 
			'5.3.0', 
			true
			);
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ){
        wp_enqueue_script( 'comment-reply' );
    	}
	}
	add_action( 'wp_enqueue_scripts', 'quickstrap_scripts' );
?>
