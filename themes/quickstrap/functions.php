<?php
/**
 * QuickStrap functions and setup.
 *
 * A Bootstrap theme kept deliberately small: Bootstrap's stylesheet plus
 * one small theme stylesheet, no options panel, just the theme supports
 * WordPress needs.
 *
 * @package QuickStrap
 */

// Bootstrap and Font Awesome live in vendor/ and are refreshed with
// tools/update-quickstrap-vendor.sh, which also updates these numbers.
define( 'QUICKSTRAP_BOOTSTRAP_VERSION', '5.3.8' );
define( 'QUICKSTRAP_FONTAWESOME_VERSION', '7.3.1' );

// Width of the main content column, used by WordPress for embeds and large images.
if ( ! isset( $content_width ) ) {
	$content_width = 636;
}

// Setup
if ( ! function_exists( 'quickstrap_setup' ) ) :
	function quickstrap_setup() {

		// Let WordPress write the <title> tag.
		add_theme_support( 'title-tag' );

		// Custom background colour or image (Appearance > Customize).
		add_theme_support( 'custom-background', apply_filters( 'quickstrap_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Optional logo beside the site title (Appearance > Customize > Site Identity).
		add_theme_support( 'custom-logo', array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		// Menus: the menu in the dark top bar, and one in the footer (Appearance > Menus).
		// The ids stay the same so existing sites keep their menus.
		register_nav_menus( array(
			'header-nav' => __( 'Main Menu', 'quickstrap' ),
			'footer-nav' => __( 'Footer Menu', 'quickstrap' ),
		) );

		// More theme support
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'align-wide' );
	}
endif; // quickstrap_setup
add_action( 'after_setup_theme', 'quickstrap_setup' );

// Register widget areas. The ids stay the same so existing widgets stay put.
function quickstrap_widgets_init() {
	$wrappers = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	);

	register_sidebar( array_merge( $wrappers, array(
		'name'        => __( 'Right Sidebar', 'quickstrap' ),
		'id'          => 'right-sidebar',
		'description' => __( 'The main sidebar, on the right on wide screens and below the posts on phones.', 'quickstrap' ),
	) ) );

	register_sidebar( array_merge( $wrappers, array(
		'name'        => __( 'Left Sidebar', 'quickstrap' ),
		'id'          => 'left-sidebar',
		'description' => __( 'A narrow column on the left on wide screens. On tablets and phones it moves below the posts and the right sidebar. Leave it empty for a two-column layout.', 'quickstrap' ),
	) ) );

	register_sidebar( array_merge( $wrappers, array(
		'name'        => __( 'Above Posts', 'quickstrap' ),
		'id'          => 'top-post',
		'description' => __( 'Shown above the post title on each single post. Empty means hidden.', 'quickstrap' ),
	) ) );

	register_sidebar( array_merge( $wrappers, array(
		'name'        => __( 'Below Posts', 'quickstrap' ),
		'id'          => 'bottom-post',
		'description' => __( 'Shown under the post on each single post, before the comments: a newsletter sign-up, an author note, related links. Empty means hidden.', 'quickstrap' ),
	) ) );

	// Footer: up to three columns. Only the ones with widgets are shown.
	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar( array_merge( $wrappers, array(
			/* translators: %d: footer column number, 1 to 3 */
			'name'        => sprintf( __( 'Footer %d', 'quickstrap' ), $i ),
			'id'          => 'footer-' . $i,
			'description' => __( 'A column at the bottom of every page. Leave all three empty for no footer widgets.', 'quickstrap' ),
		) ) );
	}
}
add_action( 'widgets_init', 'quickstrap_widgets_init' );

// Main menu links get Bootstrap's nav-link class, so they look like a Bootstrap navbar.
function quickstrap_menu_link_class( $atts, $item, $args ) {
	if ( isset( $args->theme_location ) && 'header-nav' === $args->theme_location ) {
		$atts['class'] = trim( ( isset( $atts['class'] ) ? $atts['class'] : '' ) . ' nav-link' );
		if ( in_array( 'current-menu-item', (array) $item->classes, true ) ) {
			$atts['class'] .= ' active';
		}
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'quickstrap_menu_link_class', 10, 3 );

// Editor style: posts look in the editor much like they do on the site.
function quickstrap_add_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'custom-editor-style.css' );
}
add_action( 'after_setup_theme', 'quickstrap_add_editor_styles' );

// The "Read More" link used after excerpts.
function quickstrap_read_more_link() {
	return ' &bull; <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read More', 'quickstrap' ) . ' &raquo;</a>';
}

// After an automatic excerpt that was cut short.
function quickstrap_excerpt_more( $more ) {
	return quickstrap_read_more_link();
}
add_filter( 'excerpt_more', 'quickstrap_excerpt_more' );

// Post date, author, comments and categories line, used in lists and single posts
function quickstrap_post_meta( $show_comments = true ) {
	echo '<p class="post-meta">';

	printf(
		'<time datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	/* translators: %s: post author name */
	echo ' | ' . sprintf( esc_html__( 'By %s', 'quickstrap' ), esc_html( get_the_author() ) );

	if ( $show_comments && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo ' | ';
		comments_popup_link( __( 'No Comments', 'quickstrap' ), __( '1 Comment', 'quickstrap' ), __( '% Comments', 'quickstrap' ) );
	}

	$categories = get_the_category_list( ', ' );
	if ( $categories ) {
		/* translators: %s: list of categories */
		echo ' | ' . sprintf( esc_html__( 'Filed in: %s', 'quickstrap' ), $categories ); // phpcs:ignore WordPress.Security.EscapeOutput -- WordPress builds this list.
	}

	edit_post_link( __( 'Edit', 'quickstrap' ), ' | ', '' );

	echo '</p>';
}

/*
 * Layout: Bootstrap's grid, three columns on wide screens.
 *   Wide screens (992px and up): left sidebar | posts | right sidebar
 *   Tablets (768px to 991px):    posts | right sidebar, left sidebar below both
 *   Phones:                      posts, right sidebar, left sidebar
 * The posts come first in the page's HTML, which helps screen readers and
 * search engines; Bootstrap's order classes put the columns in place.
 */

// Is this post or page using the Full width template?
function quickstrap_is_full_width() {
	return is_singular() && is_page_template( 'full-width.php' );
}

// Does this page show the left sidebar? Only when it has widgets.
function quickstrap_has_left_sidebar() {
	return ! quickstrap_is_full_width() && is_active_sidebar( 'left-sidebar' );
}

// Bootstrap column classes for the posts column.
function quickstrap_content_class() {
	if ( quickstrap_is_full_width() ) {
		return 'content col-12';
	}
	if ( quickstrap_has_left_sidebar() ) {
		return 'content col-md-8 col-lg-6 order-1 order-lg-2';
	}
	return 'content col-md-8'; // No left sidebar: posts take its space.
}

// The posts column's width for embeds and large images.
function quickstrap_layout_content_width() {
	if ( quickstrap_is_full_width() ) {
		$GLOBALS['content_width'] = 1296;
	} elseif ( ! quickstrap_has_left_sidebar() ) {
		$GLOBALS['content_width'] = 856;
	}
}
add_action( 'template_redirect', 'quickstrap_layout_content_width' );

// Body class for the Full width template.
function quickstrap_body_classes( $classes ) {
	if ( quickstrap_is_full_width() ) {
		$classes[] = 'qs-full-width';
	}
	return $classes;
}
add_filter( 'body_class', 'quickstrap_body_classes' );

// Load styles and scripts
function quickstrap_scripts() {

	// Theme version plus the file's last change time, so browsers fetch fresh CSS
	// after every update, even when the version number stays the same.
	$version = wp_get_theme( get_template() )->get( 'Version' ) . '.' . filemtime( get_template_directory() . '/style.css' );

	// Bootstrap first, then the theme's own small stylesheet on top of it.
	wp_enqueue_style( 'quickstrap-bootstrap', get_template_directory_uri() . '/vendor/bootstrap/bootstrap.min.css', array(), QUICKSTRAP_BOOTSTRAP_VERSION );
	wp_enqueue_style( 'quickstrap', get_stylesheet_uri(), array( 'quickstrap-bootstrap' ), $version );

	wp_enqueue_style( 'quickstrap-fontawesome', get_template_directory_uri() . '/vendor/font-awesome/css/all.min.css', array(), QUICKSTRAP_FONTAWESOME_VERSION );
	wp_enqueue_script( 'quickstrap-bootstrap', get_template_directory_uri() . '/vendor/bootstrap/bootstrap.bundle.min.js', array(), QUICKSTRAP_BOOTSTRAP_VERSION, true );

	// WordPress's own script for threaded comment replies, only where needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quickstrap_scripts' );
