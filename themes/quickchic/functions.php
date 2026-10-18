<?php

// content width
	if ( ! isset( $content_width ) ) {
	$content_width = 920;
	}

// Setup
if ( ! function_exists( 'quickchic_setup' ) ) :
function quickchic_setup() {

	// add title tag
	add_theme_support( 'title-tag' );

	// add text domain
	load_theme_textdomain( 'quickchic', get_template_directory() . '/languages' );

	// Custom Background
	add_theme_support( 'custom-background', apply_filters( 'quickchic_custom_background_args', array(
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
endif; // quickchic_setup
add_action( 'after_setup_theme', 'quickchic_setup' );

// Register sidebar widgets
function quickchic_widgets_init() {
	register_sidebar(array(
        'name' => __( 'Sidebar', 'quickchic' ),
        'id' => 'sidebar',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
}
add_action( 'widgets_init', 'quickchic_widgets_init' );

// add editor style
	function quickchic_add_editor_styles() {
 	add_editor_style( 'custom-editor-style.css' );
	}
	add_action( 'admin_init', 'quickchic_add_editor_styles' );

// read more
function quickchic_excerpt_more( $more ) {
	return ' &bull;  <a class="read-more" href="'. get_permalink( get_the_ID() ) . '">' . __(' Read More &raquo;', 'quickchic') . '</a>'; 
	}
	add_filter( 'excerpt_more', 'quickchic_excerpt_more' );

// credits
function quickchic_credits() {
    echo '<p class="center">
	<a href="https://wordpress.org/themes/quickchic/" target="_blank" rel="nofollow noopener noreferrer">QuickChic Theme</a> &bull; Powered by WordPress
	</p>';
	}
	add_action('wp_footer', 'quickchic_credits');
 
// load javascript
	function quickchic_scripts() {

		wp_enqueue_style( 'quickchic-normalize', 
			get_template_directory_uri() . '/css/normalize.css', 
			array(), 
			'8.0.1'
			);
		
		wp_enqueue_style( 'quickchic', 
					 get_stylesheet_uri(),
					 array(), 
			         '1.6.2'
					);
		
		wp_enqueue_style( 'quickchic-quickgrid', 
			get_template_directory_uri() . '/css/quickgrid.css', 
			array(), 
			'1.0'
			);	
		
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ){
        wp_enqueue_script( 'comment-reply' );
    	}
	}
	add_action( 'wp_enqueue_scripts', 'quickchic_scripts' );
?>