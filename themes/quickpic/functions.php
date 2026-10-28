<?php
/**
 * QuickPic functions and setup.
 *
 * Kept deliberately small: no JavaScript, no options panel,
 * just the theme supports WordPress needs.
 *
 * @package QuickPic
 */

// Width of the post column, used by WordPress for embeds and large images.
if ( ! isset( $content_width ) ) {
	$content_width = 660;
}

// Setup
if ( ! function_exists( 'quickpic_setup' ) ) :
	function quickpic_setup() {

		// Let WordPress write the <title> tag.
		add_theme_support( 'title-tag' );

		// Custom background colour or image (Appearance > Customize).
		add_theme_support( 'custom-background', apply_filters( 'quickpic_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// The menu bar under the header (Appearance > Menus).
		// The id stays 'header-nav' so existing sites keep their menu.
		register_nav_menus( array(
			'header-nav' => __( 'Main Menu', 'quickpic' ),
		) );

		// More theme support
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'align-wide' );
	}
endif; // quickpic_setup
add_action( 'after_setup_theme', 'quickpic_setup' );

// Widget areas in the green box at the bottom of every page.
// The ids stay the same so existing widgets stay put.
function quickpic_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Footer 1', 'quickpic' ),
		'id'            => 'footer-1',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer 2', 'quickpic' ),
		'id'            => 'footer-2',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'quickpic_widgets_init' );

// Editor style: posts look in the editor much like they do on the site.
function quickpic_add_editor_styles() {
	add_editor_style( 'custom-editor-style.css' );
}
add_action( 'admin_init', 'quickpic_add_editor_styles' );

// The "Read More" link after excerpts.
function quickpic_excerpt_more( $more ) {
	return ' &bull; <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read More', 'quickpic' ) . ' &raquo;</a>';
}
add_filter( 'excerpt_more', 'quickpic_excerpt_more' );

// Load stylesheets
function quickpic_scripts() {

	// The theme's version, read from style.css, so it never goes stale.
	$version = wp_get_theme( get_template() )->get( 'Version' );

	wp_enqueue_style( 'quickpic-normalize', get_template_directory_uri() . '/css/normalize.css', array(), '8.0.1' );
	wp_enqueue_style( 'quickpic', get_stylesheet_uri(), array(), $version );
	wp_enqueue_style( 'quickpic-quickgrid', get_template_directory_uri() . '/css/quickgrid.css', array(), '1.0' );

	// WordPress's own script for threaded comment replies, only where needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quickpic_scripts' );
