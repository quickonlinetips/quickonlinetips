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

/*
 * Default sidebar, shown only while the Sidebar has no widgets.
 * Built from WordPress's own blocks, so it needs no plugin and looks the same
 * as the block widgets people add later in Appearance > Widgets.
 * As soon as any widget is added, this steps aside.
 * A child theme can switch it off: add_filter( 'quickchic_show_default_sidebar', '__return_false' );
 */
function quickchic_default_sidebar() {
	if ( ! apply_filters( 'quickchic_show_default_sidebar', true ) ) {
		return;
	}

	$sections = array(
		'widget_search'          => array( '', '<!-- wp:search ' . wp_json_encode( array(
			'label'      => __( 'Search', 'quickchic' ),
			'showLabel'  => false,
			'buttonText' => __( 'Search', 'quickchic' ),
		) ) . ' /-->' ),
		'widget_recent_entries'  => array( __( 'Recent Posts', 'quickchic' ), '<!-- wp:latest-posts /-->' ),
		'widget_categories'      => array( __( 'Categories', 'quickchic' ), '<!-- wp:categories /-->' ),
		'widget_archive'         => array( __( 'Archives', 'quickchic' ), '<!-- wp:archives /-->' ),
	);

	foreach ( $sections as $class => $section ) {
		echo '<section class="widget ' . esc_attr( $class ) . '">';
		if ( $section[0] ) {
			echo '<h2 class="widget-title">' . esc_html( $section[0] ) . '</h2>';
		}
		echo do_blocks( $section[1] ); // phpcs:ignore WordPress.Security.EscapeOutput -- core blocks escape their own output.
		echo '</section>';
	}
}

// Editor style: posts look in the editor much like they do on the site.
function quickchic_add_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'custom-editor-style.css' );
}
add_action( 'after_setup_theme', 'quickchic_add_editor_styles' );

// Read more link after excerpts
function quickchic_excerpt_more( $more ) {
	return ' &bull; <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read More', 'quickchic' ) . ' &raquo;</a>';
}
add_filter( 'excerpt_more', 'quickchic_excerpt_more' );

// Post date, author, comments and categories line, used in lists and single posts
function quickchic_post_meta( $show_comments = true ) {
	echo '<p class="post-meta">';

	printf(
		'<time datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	/* translators: %s: post author name */
	echo ' | ' . sprintf( esc_html__( 'By %s', 'quickchic' ), esc_html( get_the_author() ) );

	if ( $show_comments && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo ' | ';
		comments_popup_link( __( 'No Comments', 'quickchic' ), __( '1 Comment', 'quickchic' ), __( '% Comments', 'quickchic' ) );
	}

	$categories = get_the_category_list( ', ' );
	if ( $categories ) {
		/* translators: %s: list of categories */
		echo ' | ' . sprintf( esc_html__( 'Filed in: %s', 'quickchic' ), $categories ); // phpcs:ignore WordPress.Security.EscapeOutput -- WordPress builds this list.
	}

	edit_post_link( __( 'Edit', 'quickchic' ), ' | ', '' );

	echo '</p>';
}

// Load stylesheets
function quickchic_scripts() {

	// Theme version from style.css, so browsers fetch fresh CSS after every update.
	$version = wp_get_theme( get_template() )->get( 'Version' );

	// The whole theme is one small stylesheet.
	wp_enqueue_style( 'quickchic', get_stylesheet_uri(), array(), $version );

	// WordPress's own script for threaded comment replies, only where needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quickchic_scripts' );
