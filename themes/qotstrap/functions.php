<?php
// content width
	if ( ! isset( $content_width ) ) {
	$content_width = 760;
	}

if ( ! function_exists( 'qotstrap_setup' ) ) :
function qotstrap_setup() {

	// add title tag
	add_theme_support( 'title-tag' );

	// more theme support
	add_theme_support('post-thumbnails');
//	add_theme_support('automatic-feed-links');
	add_theme_support( "responsive-embeds");
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( "align-wide");


	// Custom Background
	add_theme_support( 'custom-background', apply_filters( 'qotstrap_custom_background_args', array(
		'default-color' => 'f3f3f3',
		'default-image' => '',
	) ) );

	}
endif; // qotstrap_setup
add_action( 'after_setup_theme', 'qotstrap_setup' );

// Register our sidebars and widgetized areas
function qotstrap_widgets_init() {

  // sidebar widgets
	register_sidebar( array(
	        'name' => __( 'Sidebar', 'qotstrap' ),
		'id' => 'sidebar',
		'before_widget' => '<div class="sidebar-widget mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );

	register_sidebar( array(
	        'name' => __( 'Sidebar Common', 'qotstrap' ),
		'id' => 'sidebar-common',
		'before_widget' => '<div class="sidebar-common-widget mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );

		register_sidebar( array(
	        'name' => __( 'Sidebar Singular', 'qotstrap' ),
		'id' => 'sidebar-singular',
		'before_widget' => '<div class="sidebar-singular-widget mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );
		register_sidebar( array(
	        'name' => __( 'Sidebar Archives', 'qotstrap' ),
		'id' => 'sidebar-archives',
		'before_widget' => '<div class="sidebar-archives-widget mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );
  // Homepage widgets
	register_sidebar( array(
	        'name' => __( 'HomePage1', 'qotstrap' ),
		'id' => 'homepage1',
		'before_widget' => '<div class="homepage-1-widget mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );

	  // Home 1 widgets
	register_sidebar( array(
	        'name' => __( 'Widget1', 'qotstrap' ),
		'id' => 'widget1',
		'before_widget' => '<div class="widget1 mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="sidebar-title">',
		'after_title' => '</h2>',
	) );
	  // Home 2 widgets
	register_sidebar( array(
	        'name' => __( 'Widget2', 'qotstrap' ),
		'id' => 'widget2',
		'before_widget' => '<div class="widget2 mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="sidebar-title">',
		'after_title' => '</h2>',
	) );
		  // Home 3 widgets
	register_sidebar( array(
	        'name' => __( 'Widget3', 'qotstrap' ),
		'id' => 'widget3',
		'before_widget' => '<div class="widget3 mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="sidebar-title">',
		'after_title' => '</h2>',
	) );
			  // Home 4 widgets
	register_sidebar( array(
	        'name' => __( 'Widget4', 'qotstrap' ),
		'id' => 'widget4',
		'before_widget' => '<div class="widget4 mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="sidebar-title">',
		'after_title' => '</h2>',
	) );
		  // Homepage 5 widgets
	register_sidebar( array(
	        'name' => __( 'HomePage5', 'qotstrap' ),
		'id' => 'homepage5',
		'before_widget' => '<div class="homepage-5-widget mb-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );

  // Footer widgets
	register_sidebar( array(
	        'name' => __( 'Footer1', 'qotstrap' ),
		'id' => 'footer1',
		'before_widget' => '<div class="footer1-widget my-2 text-center">',
		'after_widget' => '</div>',
		'before_title' => '<div class="sidebar-title">',
		'after_title' => '</div>',
	) );
	register_sidebar( array(
	        'name' => __( 'Footer 2', 'qotstrap' ),
		'id' => 'footer2',
		'before_widget' => '<div class="footer2-widget my-2 text-center">',
		'after_widget' => '</div>',
		'before_title' => '<div class="sidebar-title">',
		'after_title' => '</div>',
	) );
	register_sidebar( array(
	        'name' => __( 'Footer 3', 'qotstrap' ),
		'id' => 'footer3',
		'before_widget' => '<div class="footer3-widget my-2 text-center">',
		'after_widget' => '</div>',
		'before_title' => '<div class="sidebar-title">',
		'after_title' => '</div>',
	) );

  // Post widgets
	register_sidebar( array(
	        'name' => __( 'Bottom Post', 'qotstrap' ),
		'id' => 'bottom-post',
		'before_widget' => '<div class="bottompost-widget my-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );
	  // Archive widgets
	register_sidebar( array(
	        'name' => __( 'Archives Top', 'qotstrap' ),
		'id' => 'archives-top',
		'before_widget' => '<div class="my-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="">',
		'after_title' => '</h4>',
	) );
	  // Archive2 widgets
	register_sidebar( array(
	        'name' => __( 'Archives Bottom', 'qotstrap' ),
		'id' => 'archives-bot',
		'before_widget' => '<div class="my-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="">',
		'after_title' => '</h4>',
	) );
  // 404 widgets
	register_sidebar( array(
	        'name' => __( '404 Page', 'qotstrap' ),
		'id' => '404-page',
		'before_widget' => '<div class="404-widget my-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );
	  // Subscribe
	register_sidebar( array(
	        'name' => __( 'Subscribe Page', 'qotstrap' ),
		'id' => 'subscribe-page',
		'before_widget' => '<div class="subscribe-widget my-3">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="sidebar-title">',
		'after_title' => '</h4>',
	) );
}
add_action( 'widgets_init', 'qotstrap_widgets_init' );

// Stop Block widgets
 add_filter( 'use_widgets_block_editor', '__return_false' );


// remove junk from head
	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'rsd_link');
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

// Stop redirects
 remove_filter('template_redirect', 'redirect_canonical');

// remove emoji
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Stop image compression
add_filter('jpeg_quality', function($arg){return 100;});
add_filter( 'wp_editor_set_quality', function($arg){return 100;} );

// edit excerpts
	function new_excerpt_length($length) {
		return 25;
	}
	add_filter('excerpt_length', 'new_excerpt_length');
	function new_excerpt_more($more) {
		return '...';
	}
	add_filter('excerpt_more', 'new_excerpt_more');
/*
// Read more link
// Replaces the excerpt "more" text by a link
	function newer_excerpt_more($more) {
		   global $post;
		return '... <a class="moretag" href="'. get_permalink($post->ID) . '"> Read more &raquo;</a>';
	}
	add_filter('excerpt_more', 'newer_excerpt_more');
 */

// theme options
//	require_once ( get_template_directory() . '/theme-options.php' );



// RSS thumbnails
//	function rss_post_thumbnail($content) {
//	global $post;
//	if(has_post_thumbnail($post->ID)) {
//	$content = '<p>' . get_the_post_thumbnail($post->ID) .
//	'</p>' . get_the_excerpt() . '<p>' . 'Read full original article at <a href="' . get_permalink() .
//	'" rel="bookmark" title="Permanent link to \''.get_the_title().'\'">' .get_the_title(). '</a></p><p>&copy;' . date("Y") . ' <a href="' . get_bloginfo('url') . '">' . get_bloginfo('name') . '</a>. All Rights Reserved.</p>';
//	}
//	return $content;
//	}
//	add_filter('the_excerpt_rss', 'rss_post_thumbnail');
//	add_filter('the_content_feed', 'rss_post_thumbnail');

// Category Descriptions
	$filters = array('term_description','category_description','pre_term_description');
	foreach ( $filters as $filter ) {
	remove_filter($filter, 'wptexturize');
	remove_filter($filter, 'convert_chars');
	remove_filter($filter, 'wpautop');
	remove_filter($filter, 'wp_filter_kses');
	remove_filter($filter, 'strip_tags');
	}


// add editor style
function qotstrap_add_editor_styles() {
   	add_editor_style( 'custom-editor-style.css' );
	}
add_action( 'after_setup_theme', 'qotstrap_add_editor_styles' );

// load javascript
//	function qotstrap_scripts() {
 //
//		wp_enqueue_style( 'qotstrap-bootstrap',
//			get_template_directory_uri() . '/css/bootstrap.min.css',
//			array(),
//			'4.4.1'
//			);
//	wp_enqueue_style( 'qotstrap',
//			get_stylesheet_uri(),
//			array(),
//			'1.2'
//			);
//		wp_enqueue_style( 'qotstrap-fontawesome',
//			get_template_directory_uri() . '/font-awesome/css/all.min.css',
//			array(),
//			'5.12.1'
//			);
//		 if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ){        wp_enqueue_script( 'comment-reply' );
//    	}
//	}
//	add_action( 'wp_enqueue_scripts', 'qotstrap_scripts' );

/*
// commemnt links remove
	function filter_get_comment_author_url( $url, $id, $commentr ) {
 	return "";
	 };
	 add_filter( 'get_comment_author_url', 'filter_get_comment_author_url', 10, 3);
*/

// remove links inside comments
//	remove_filter('comment_text', 'make_clickable', 9);

/*
// Remove YARPP CSS
	add_action('wp_print_styles','lm_dequeue_header_styles');
	function lm_dequeue_header_styles()
	{
	  wp_dequeue_style('yarppWidgetCss');
	}
	add_action('get_footer', function () {
	wp_dequeue_style('yarppRelatedCss');
	wp_dequeue_style('yarpp-thumbnails-yarpp-thumbnail');
} );
*/

// remove embed js
//  function my_deregister_scripts(){
//  wp_deregister_script( 'wp-embed' );
//  }
//  add_action( 'wp_footer', 'my_deregister_scripts' );



// yoast noindex subpages
// add_filter("wpseo_robots", function($robots) {
//	if (is_paged() && is_archive())  {
//        return 'noindex,follow';
//    } else {
//        return $robots;
//    }
//});


//  remove block library (works)
//  add_action( 'wp_enqueue_scripts', function() {
//  wp_dequeue_style( 'wp-block-library' );
//  } );

//REMOVE GUTENBERG BLOCK LIBRARY CSS FROM LOADING ON FRONTEND
function remove_wp_block_library_css(){
wp_dequeue_style( 'wp-block-library' );
wp_dequeue_style( 'wp-block-library-theme' );
wp_dequeue_style( 'wc-block-style' ); // REMOVE WOOCOMMERCE BLOCK CSS
wp_dequeue_style( 'global-styles' ); // REMOVE THEME.JSON
}
add_action( 'wp_enqueue_scripts', 'remove_wp_block_library_css', 100 );

// remove SVG junk
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );

/*
// remove search
function fb_filter_query( $query, $error = true ) {

	if ( is_search() ) {
		$query->is_search = false;
		$query->query_vars[s] = false;
		$query->query[s] = false;

		// to error
		if ( $error == true )
			$query->is_404 = true;
	}
}

add_action( 'parse_query', 'fb_filter_query' );
add_filter( 'get_search_form', create_function( '$a', "return null;" ) );
*/

// add custom shortcodes
 // include('shortcodes.php');
 // require_once( get_template_directory() . '/shortcodes.php' );

// Add featured image in feed
// function featuredtoRSS($content) {
// global $post;
// if ( has_post_thumbnail( $post->ID ) ){
// $content = '<div>' . get_the_post_thumbnail( $post->ID, 'medium', array( 'style' => 'margin-bottom: 15px;' ) ) . '</div>' . $content;
// }
// return $content;
// }
//
// add_filter('the_excerpt_rss', 'featuredtoRSS');
// add_filter('the_content_feed', 'featuredtoRSS');


?>
