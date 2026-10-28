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

		// Optional logo above the site title (Appearance > Customize > Site Identity).
		add_theme_support( 'custom-logo', array(
			'height'      => 100,
			'width'       => 300,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		// Menus: the menu bar under the header, and one in the footer (Appearance > Menus).
		// The header id stays 'header-nav' so existing sites keep their menu.
		register_nav_menus( array(
			'header-nav' => __( 'Main Menu', 'quickpic' ),
			'footer'     => __( 'Footer Menu', 'quickpic' ),
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

// Widget areas in the green box at the bottom of every page, side by side.
// The ids stay the same so existing widgets stay put.
function quickpic_widgets_init() {
	for ( $i = 1; $i <= 2; $i++ ) {
		register_sidebar( array(
			/* translators: %d: footer column number */
			'name'          => sprintf( __( 'Footer %d', 'quickpic' ), $i ),
			'id'            => 'footer-' . $i,
			'description'   => __( 'A column in the green box at the bottom of every page, under the search box.', 'quickpic' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}
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
