<?php

// Content width
if ( ! isset( $content_width ) )
	$content_width = 540;

// Setup
if ( ! function_exists( 'quickpress_setup' ) ) :
function quickpress_setup() {

	// Register menus
	register_nav_menus( array(
	'header-nav' => 'Header Menu',
	) );

	// add title tag
	add_theme_support( 'title-tag' );

	// add text domain
	load_theme_textdomain( 'quickpress', get_template_directory() . '/languages' );

	// Custom Background
	add_theme_support( 'custom-background', apply_filters( 'quickpress_custom_background_args', array(
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
endif; // quickpress_setup
add_action( 'after_setup_theme', 'quickpress_setup' );

// Register sidebar widgets
function quickpress_widgets_init() {
register_sidebar(array(
        'name' => __( 'Sidebar Full', 'quickpress' ),
        'id' => 'sidebar-1',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
register_sidebar(array(

        'name' => __( 'Sidebar Split Left', 'quickpress' ),
        'id' => 'sidebar-2',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
)); 
register_sidebar(array(
        'name' => __( 'Sidebar Split Right', 'quickpress' ),
        'id' => 'sidebar-3',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
)); 
}
add_action( 'widgets_init', 'quickpress_widgets_init' );

// add editor style
function quickpress_add_editor_styles() {
    	add_editor_style( 'custom-editor-style.css' );
}
add_action( 'admin_init', 'quickpress_add_editor_styles' );

// read more
function quickpress_excerpt_more( $more ) {
	return ' &bull;  <a class="read-more" href="'. get_permalink( get_the_ID() ) . '">' . __(' Read More &raquo;', 'quickpress') . '</a>'; 
	}
	add_filter( 'excerpt_more', 'quickpress_excerpt_more' );

// credits
function quickpress_credits() {
    echo '<div class="center">
	<a href="https://wordpress.org/themes/quickpress/" target="_blank" rel="nofollow noopener noreferrer">QuickPress Theme</a> &bull; Powered by WordPress
	</div>';
	}
	add_action('wp_footer', 'quickpress_credits');


// load javascript
	function quickpress_scripts() {
		
		wp_enqueue_style( 'quickpress-normalize', 
			get_template_directory_uri() . '/css/normalize.css', 
			array(), 
			'8.0.1'
			);
		
	wp_enqueue_style( 'quickpress', 
					 get_stylesheet_uri(),
					 array(), 
			         '1.9.2'
					);

	wp_enqueue_style( 'quickpress-quickgrid', 
			get_template_directory_uri() . '/css/quickgrid.css', 
			array(), 
			'1.0'
			);		
		
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ){
        wp_enqueue_script( 'comment-reply' );
    	}
	}
	add_action( 'wp_enqueue_scripts', 'quickpress_scripts' );
?>
