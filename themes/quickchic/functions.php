<?php
/**
 * QuickChic functions and setup.
 *
 * Kept deliberately small: no JavaScript, no options panel,
 * just the theme supports WordPress needs.
 *
 * @package QuickChic
 */

// Width of the main content column, used by WordPress for embeds and large images.
if ( ! isset( $content_width ) ) {
	$content_width = 620;
}

// Setup
if ( ! function_exists( 'quickchic_setup' ) ) :
	function quickchic_setup() {

		// Let WordPress write the <title> tag.
		add_theme_support( 'title-tag' );

		// Custom background colour or image (Appearance > Customize).
		add_theme_support( 'custom-background', apply_filters( 'quickchic_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Optional logo above the site title (Appearance > Customize > Site Identity).
		add_theme_support( 'custom-logo', array(
			'height'      => 120,
			'width'       => 300,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		// Menus: one under the site title, one in the footer (Appearance > Menus).
		register_nav_menus( array(
			'primary' => __( 'Main Menu', 'quickchic' ),
			'footer'  => __( 'Footer Menu', 'quickchic' ),
		) );

		// More theme support
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'align-wide' );
	}
endif; // quickchic_setup
add_action( 'after_setup_theme', 'quickchic_setup' );

// Register sidebar widgets
function quickchic_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'quickchic' ),
		'id'            => 'sidebar',
		'description'   => __( 'Shown in the right column, below the site title.', 'quickchic' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'quickchic_widgets_init' );

// Editor style
function quickchic_add_editor_styles() {
	add_editor_style( 'custom-editor-style.css' );
}
add_action( 'admin_init', 'quickchic_add_editor_styles' );

// Read more link after excerpts
function quickchic_excerpt_more( $more ) {
	return ' &bull; <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read More', 'quickchic' ) . ' &raquo;</a>';
}
add_filter( 'excerpt_more', 'quickchic_excerpt_more' );

// Load stylesheets
function quickchic_scripts() {

	// Theme version from style.css, so browsers fetch fresh CSS after every update.
	$version = wp_get_theme( get_template() )->get( 'Version' );

	wp_enqueue_style( 'quickchic-normalize',
		get_template_directory_uri() . '/css/normalize.css',
		array(),
		'8.0.1'
	);

	wp_enqueue_style( 'quickchic',
		get_stylesheet_uri(),
		array(),
		$version
	);

	wp_enqueue_style( 'quickchic-quickgrid',
		get_template_directory_uri() . '/css/quickgrid.css',
		array(),
		'1.0'
	);

	// WordPress's own script for threaded comment replies, only where needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quickchic_scripts' );
