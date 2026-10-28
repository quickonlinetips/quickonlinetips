<?php
/**
 * QuickPress functions and setup.
 *
 * Kept deliberately small: no JavaScript, no options panel,
 * just the theme supports WordPress needs.
 *
 * @package QuickPress
 */

// Width of the main content column, used by WordPress for embeds and large images.
if ( ! isset( $content_width ) ) {
	$content_width = 620;
}

// Setup
if ( ! function_exists( 'quickpress_setup' ) ) :
	function quickpress_setup() {

		// Let WordPress write the <title> tag.
		add_theme_support( 'title-tag' );

		// Custom background colour or image (Appearance > Customize).
		add_theme_support( 'custom-background', apply_filters( 'quickpress_custom_background_args', array(
			'default-color' => 'ede8e2',
			'default-image' => '',
		) ) );

		// Optional logo beside the site title (Appearance > Customize > Site Identity).
		add_theme_support( 'custom-logo', array(
			'height'      => 100,
			'width'       => 300,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		// Menus: the menu bar under the header, and one in the footer (Appearance > Menus).
		// The header id stays 'header-nav' so existing sites keep their menu.
		register_nav_menus( array(
			'header-nav' => __( 'Main Menu', 'quickpress' ),
			'footer'     => __( 'Footer Menu', 'quickpress' ),
		) );

		// More theme support
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'align-wide' );
	}
endif; // quickpress_setup
add_action( 'after_setup_theme', 'quickpress_setup' );

// Register sidebar widgets. The ids stay the same so existing widgets stay put.
function quickpress_widgets_init() {
	$areas = array(
		'sidebar-1' => array( __( 'Sidebar Full', 'quickpress' ), __( 'Across the whole right column, at the top.', 'quickpress' ) ),
		'sidebar-2' => array( __( 'Sidebar Split Left', 'quickpress' ), __( 'The left half of the right column, below Sidebar Full.', 'quickpress' ) ),
		'sidebar-3' => array( __( 'Sidebar Split Right', 'quickpress' ), __( 'The right half of the right column, below Sidebar Full.', 'quickpress' ) ),
	);
	foreach ( $areas as $id => $area ) {
		register_sidebar( array(
			'name'          => $area[0],
			'id'            => $id,
			'description'   => $area[1],
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}
}
add_action( 'widgets_init', 'quickpress_widgets_init' );

// Editor style: posts look in the editor much like they do on the site.
function quickpress_add_editor_styles() {
	add_editor_style( 'custom-editor-style.css' );
}
add_action( 'admin_init', 'quickpress_add_editor_styles' );

// The "Read More" link used after excerpts.
function quickpress_read_more_link() {
	return ' &bull; <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read More', 'quickpress' ) . ' &raquo;</a>';
}

// After an automatic excerpt that was cut short.
function quickpress_excerpt_more( $more ) {
	return quickpress_read_more_link();
}
add_filter( 'excerpt_more', 'quickpress_excerpt_more' );

// Post date, author, comments and categories line, used in lists and single posts
function quickpress_post_meta( $show_comments = true ) {
	echo '<p class="post-meta">';

	printf(
		'<time datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	/* translators: %s: post author name */
	echo ' | ' . sprintf( esc_html__( 'By %s', 'quickpress' ), esc_html( get_the_author() ) );

	if ( $show_comments && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo ' | ';
		comments_popup_link( __( 'No Comments', 'quickpress' ), __( '1 Comment', 'quickpress' ), __( '% Comments', 'quickpress' ) );
	}

	$categories = get_the_category_list( ', ' );
	if ( $categories ) {
		/* translators: %s: list of categories */
		echo ' | ' . sprintf( esc_html__( 'Filed in: %s', 'quickpress' ), $categories ); // phpcs:ignore WordPress.Security.EscapeOutput -- WordPress builds this list.
	}

	edit_post_link( __( 'Edit', 'quickpress' ), ' | ', '' );

	echo '</p>';
}

// Load stylesheets
function quickpress_scripts() {

	$version = wp_get_theme( get_template() )->get( 'Version' );

	// The whole theme is one small stylesheet.
	wp_enqueue_style( 'quickpress', get_stylesheet_uri(), array(), $version );

	// WordPress's own script for threaded comment replies, only where needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quickpress_scripts' );
