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

		// Starter content: on a brand-new site, the Customizer offers ready
		// widgets (as blocks), a few pages and a main menu, so the whole design
		// shows. Nothing is saved until the owner clicks Publish, and existing
		// sites never see it.
		$quickpress_heading = function ( $text ) {
			return '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $text ) . '</h2><!-- /wp:heading -->';
		};
		add_theme_support( 'starter-content', array(
			'widgets'   => array(
				'sidebar-1' => array(
					'recent' => array( 'block', array( 'content' => $quickpress_heading( __( 'Recent Posts', 'quickpress' ) ) . '<!-- wp:latest-posts /-->' ) ),
				),
				'sidebar-2' => array(
					'categories' => array( 'block', array( 'content' => $quickpress_heading( __( 'Categories', 'quickpress' ) ) . '<!-- wp:categories {"className":"qp-short"} /-->' ) ),
				),
				'sidebar-3' => array(
					'comments' => array( 'block', array( 'content' => $quickpress_heading( __( 'Recent Comments', 'quickpress' ) ) . '<!-- wp:latest-comments {"displayAvatar":false,"displayDate":false,"displayExcerpt":false} /-->' ) ),
				),
				// Footer columns, so a new site shows the footer too.
				'footer-1'  => array(
					'about_site' => array( 'block', array( 'content' => $quickpress_heading( __( 'About This Site', 'quickpress' ) ) . '<!-- wp:paragraph --><p>' . esc_html__( 'A few words about you and what you write about. Change this in Appearance > Widgets.', 'quickpress' ) . '</p><!-- /wp:paragraph -->' ) ),
				),
				'footer-2'  => array(
					'pages' => array( 'block', array( 'content' => $quickpress_heading( __( 'Pages', 'quickpress' ) ) . '<!-- wp:page-list {"className":"qp-short"} /-->' ) ),
				),
			),
			'posts'     => array( 'about', 'contact' ),
			'nav_menus' => array(
				'header-nav' => array(
					'name'  => __( 'Main Menu', 'quickpress' ),
					'items' => array( 'link_home', 'page_about', 'page_contact' ),
				),
			),
		) );
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

	// Below each post: newsletter sign-up, author note, related links, an ad.
	register_sidebar( array(
		'name'          => __( 'Below Posts', 'quickpress' ),
		'id'            => 'after-post',
		'description'   => __( 'Shown under the post on each single post, before the comments. Empty means hidden.', 'quickpress' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	// Footer: up to three columns. Only the ones with widgets are shown.
	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar( array(
			/* translators: %d: footer column number, 1 to 3 */
			'name'          => sprintf( __( 'Footer %d', 'quickpress' ), $i ),
			'id'            => 'footer-' . $i,
			'description'   => __( 'A column at the bottom of every page. Leave all three empty for no footer widgets.', 'quickpress' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}
}
add_action( 'widgets_init', 'quickpress_widgets_init' );

/*
 * Default sidebar, shown only while all three sidebar areas are empty.
 * Built from WordPress's own blocks, so it needs no plugin and looks the same
 * as the block widgets people add later in Appearance > Widgets.
 * Search is left out because the header already has a search box.
 * As soon as any widget is added, this steps aside.
 * A child theme can switch it off: add_filter( 'quickpress_show_default_sidebar', '__return_false' );
 */
function quickpress_default_sidebar() {
	if ( ! apply_filters( 'quickpress_show_default_sidebar', true ) ) {
		return;
	}

	$full  = array(
		'widget_recent_entries' => array( __( 'Recent Posts', 'quickpress' ), '<!-- wp:latest-posts /-->' ),
	);
	$split = array(
		'widget_categories'      => array( __( 'Categories', 'quickpress' ), '<!-- wp:categories {"className":"qp-short"} /-->' ),
		'widget_recent_comments' => array( __( 'Recent Comments', 'quickpress' ), '<!-- wp:latest-comments {"displayAvatar":false,"displayDate":false,"displayExcerpt":false} /-->' ),
	);

	echo '<div class="sidebar-full">';
	quickpress_default_widgets( $full );
	echo '</div><div class="sidebar-split">';
	foreach ( $split as $class => $section ) {
		echo '<div class="subcol">';
		quickpress_default_widgets( array( $class => $section ) );
		echo '</div>';
	}
	echo '</div>';
}

// Prints default widgets with the same wrappers as real ones.
function quickpress_default_widgets( $sections ) {
	foreach ( $sections as $class => $section ) {
		echo '<section class="widget ' . esc_attr( $class ) . '">';
		echo '<h2 class="widget-title">' . esc_html( $section[0] ) . '</h2>';
		echo do_blocks( $section[1] ); // phpcs:ignore WordPress.Security.EscapeOutput -- core blocks escape their own output.
		echo '</section>';
	}
}

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

/*
 * Customizer: Appearance > Customize > Colours
 * - Header colour: the blue of the header band, menu bar, footer and links.
 * - Accent colour: the pink of widget headings, buttons and highlights.
 * - Dark mode: follow the reader's device setting (on by default) or stay light.
 */
function quickpress_customize_register( $wp_customize ) {

	$wp_customize->add_setting( 'quickpress_brand_color', array(
		'default'           => '#2a6496',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'quickpress_brand_color', array(
		'label'       => __( 'Header colour', 'quickpress' ),
		'description' => __( 'The header band, menu bar, footer and links. Pick a darker colour so white text stays readable on it.', 'quickpress' ),
		'section'     => 'colors',
	) ) );

	$wp_customize->add_setting( 'quickpress_accent_color', array(
		'default'           => '#c32364',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'quickpress_accent_color', array(
		'label'       => __( 'Accent colour', 'quickpress' ),
		'description' => __( 'Widget headings, buttons and page numbers.', 'quickpress' ),
		'section'     => 'colors',
	) ) );

	$wp_customize->add_setting( 'quickpress_dark_mode', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'quickpress_dark_mode', array(
		'type'        => 'checkbox',
		'label'       => __( 'Dark mode for readers who use it', 'quickpress' ),
		'description' => __( 'Turns the site dark when the reader\'s phone or computer is set to dark. Untick to always stay light.', 'quickpress' ),
		'section'     => 'colors',
	) );
}
add_action( 'customize_register', 'quickpress_customize_register' );

// The theme's two colours: the saved one, or the default.
function quickpress_colors() {
	$brand  = sanitize_hex_color( get_theme_mod( 'quickpress_brand_color', '#2a6496' ) );
	$accent = sanitize_hex_color( get_theme_mod( 'quickpress_accent_color', '#c32364' ) );
	return array(
		'brand'  => $brand ? $brand : '#2a6496',
		'accent' => $accent ? $accent : '#c32364',
	);
}

// The owner's colours as CSS, or '' when both are the defaults.
function quickpress_colors_css() {
	$colors = quickpress_colors();
	$css    = '';
	if ( '#2a6496' !== strtolower( $colors['brand'] ) ) {
		$css .= '--qp-brand:' . $colors['brand'] . ';';
	}
	if ( '#c32364' !== strtolower( $colors['accent'] ) ) {
		$css .= '--qp-accent:' . $colors['accent'] . ';';
	}
	return $css ? ':root{' . $css . '}' : '';
}

// Is this post or page using the Full width template?
function quickpress_is_full_width() {
	return is_singular() && is_page_template( 'template-full-width.php' );
}

// Full width pages get a wider content area for embeds and large images.
function quickpress_full_width_content_width() {
	if ( quickpress_is_full_width() ) {
		$GLOBALS['content_width'] = 950;
	}
}
add_action( 'template_redirect', 'quickpress_full_width_content_width' );

// Body classes: dark mode switched off, Full width template.
function quickpress_body_classes( $classes ) {
	if ( ! get_theme_mod( 'quickpress_dark_mode', true ) ) {
		$classes[] = 'qp-light-only';
	}
	if ( quickpress_is_full_width() ) {
		$classes[] = 'qp-full-width';
	}
	return $classes;
}
add_filter( 'body_class', 'quickpress_body_classes' );

// Load stylesheets
function quickpress_scripts() {

	$version = wp_get_theme( get_template() )->get( 'Version' );

	// The whole theme is one small stylesheet.
	wp_enqueue_style( 'quickpress', get_stylesheet_uri(), array(), $version );

	// The owner's colours, if they picked any (a few bytes, no extra file).
	$colors = quickpress_colors_css();
	if ( $colors ) {
		wp_add_inline_style( 'quickpress', $colors );
	}

	// WordPress's own script for threaded comment replies, only where needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quickpress_scripts' );
