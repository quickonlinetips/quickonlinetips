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

		// Starter content: on a brand-new site, the Customizer offers ready
		// widgets (as blocks) for the green bottom box, a few pages and a main
		// menu, so the whole design shows. Nothing is saved until the owner
		// clicks Publish, and existing sites never see it.
		$quickpic_heading = function ( $text ) {
			return '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $text ) . '</h2><!-- /wp:heading -->';
		};
		add_theme_support( 'starter-content', array(
			'widgets'   => array(
				'footer-1' => array(
					'about_site' => array( 'block', array( 'content' => $quickpic_heading( __( 'About This Site', 'quickpic' ) ) . '<!-- wp:paragraph --><p>' . esc_html__( 'A few words about you and the pictures you share. Change this in Appearance > Widgets.', 'quickpic' ) . '</p><!-- /wp:paragraph -->' ) ),
				),
				'footer-2' => array(
					'recent' => array( 'block', array( 'content' => $quickpic_heading( __( 'Recent Posts', 'quickpic' ) ) . '<!-- wp:latest-posts /-->' ) ),
				),
				'footer-3' => array(
					'pages' => array( 'block', array( 'content' => $quickpic_heading( __( 'Pages', 'quickpic' ) ) . '<!-- wp:page-list {"className":"qpic-short"} /-->' ) ),
				),
			),
			'posts'     => array( 'about', 'contact' ),
			'nav_menus' => array(
				'header-nav' => array(
					'name'  => __( 'Main Menu', 'quickpic' ),
					'items' => array( 'link_home', 'page_about', 'page_contact' ),
				),
			),
		) );
	}
endif; // quickpic_setup
add_action( 'after_setup_theme', 'quickpic_setup' );

// Widget areas in the green box at the bottom of every page: up to three
// columns side by side. The ids footer-1 and footer-2 stay the same so
// existing widgets stay put; footer-3 is new.
function quickpic_widgets_init() {
	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar( array(
			/* translators: %d: footer column number, 1 to 3 */
			'name'          => sprintf( __( 'Footer %d', 'quickpic' ), $i ),
			'id'            => 'footer-' . $i,
			'description'   => __( 'A column in the green box at the bottom of every page, under the search box. Only columns with widgets are shown.', 'quickpic' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}

	// Below each post: newsletter sign-up, author note, related links, an ad.
	register_sidebar( array(
		'name'          => __( 'Below Posts', 'quickpic' ),
		'id'            => 'after-post',
		'description'   => __( 'Shown under the post on each single post, before the comments. Empty means hidden.', 'quickpic' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'quickpic_widgets_init' );

/*
 * Default bottom box widgets, shown only while all three Footer areas are
 * empty: Recent Posts, Categories and Recent Comments side by side, so a new
 * site (or one whose old widgets were parked in Inactive) never shows a bare
 * green box. Built from WordPress's own blocks, so it needs no plugin and
 * looks the same as the block widgets people add later in Appearance > Widgets.
 * Search is left out because the box always has a search form at the top.
 * As soon as any widget is added, this steps aside.
 * A child theme can switch it off: add_filter( 'quickpic_show_default_widgets', '__return_false' );
 */
function quickpic_default_widgets() {
	if ( ! apply_filters( 'quickpic_show_default_widgets', true ) ) {
		return;
	}

	$columns = array(
		'widget_recent_entries'  => array( __( 'Recent Posts', 'quickpic' ), '<!-- wp:latest-posts /-->' ),
		'widget_categories'      => array( __( 'Categories', 'quickpic' ), '<!-- wp:categories {"className":"qpic-short"} /-->' ),
		'widget_recent_comments' => array( __( 'Recent Comments', 'quickpic' ), '<!-- wp:latest-comments {"displayAvatar":false,"displayDate":false,"displayExcerpt":false} /-->' ),
	);

	echo '<div class="bottom-widgets">';
	foreach ( $columns as $class => $column ) {
		echo '<div class="bottom-column"><section class="widget ' . esc_attr( $class ) . '">';
		echo '<h2 class="widget-title">' . esc_html( $column[0] ) . '</h2>';
		echo do_blocks( $column[1] ); // phpcs:ignore WordPress.Security.EscapeOutput -- core blocks escape their own output.
		echo '</section></div>';
	}
	echo '</div>';
}

// Editor style: posts look in the editor much like they do on the site.
function quickpic_add_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'custom-editor-style.css' );

	// The theme's own colours in the block editor's colour picker,
	// instead of WordPress's general rainbow.
	$colors = quickpic_colors();
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => __( 'Header green', 'quickpic' ), 'slug' => 'brand', 'color' => $colors['brand'] ),
		array( 'name' => __( 'Accent', 'quickpic' ), 'slug' => 'accent', 'color' => $colors['accent'] ),
		array( 'name' => __( 'Text', 'quickpic' ), 'slug' => 'text', 'color' => '#333333' ),
		array( 'name' => __( 'Grey', 'quickpic' ), 'slug' => 'grey', 'color' => '#707070' ),
		array( 'name' => __( 'Light grey', 'quickpic' ), 'slug' => 'light-grey', 'color' => '#f6f6f6' ),
		array( 'name' => __( 'White', 'quickpic' ), 'slug' => 'white', 'color' => '#ffffff' ),
	) );

	// Text sizes in the editor, in step with the theme's 15px post text.
	add_theme_support( 'editor-font-sizes', array(
		array( 'name' => __( 'Small', 'quickpic' ), 'slug' => 'small', 'size' => 13 ),
		array( 'name' => __( 'Normal', 'quickpic' ), 'slug' => 'normal', 'size' => 15.36 ),
		array( 'name' => __( 'Large', 'quickpic' ), 'slug' => 'large', 'size' => 20 ),
		array( 'name' => __( 'Extra large', 'quickpic' ), 'slug' => 'x-large', 'size' => 26 ),
	) );

	// No gradient presets: they don't suit the theme's flat look.
	add_theme_support( 'editor-gradient-presets', array() );
}
add_action( 'after_setup_theme', 'quickpic_add_editor_styles' );

// The owner's colours in the editor too.
function quickpic_editor_colors( $settings ) {
	$colors = quickpic_colors_css();
	if ( $colors && isset( $settings['styles'] ) && is_array( $settings['styles'] ) ) {
		$settings['styles'][] = array( 'css' => str_replace( ':root', 'body', $colors ) );
	}
	return $settings;
}
add_filter( 'block_editor_settings_all', 'quickpic_editor_colors' );

// The "Read More" link after excerpts.
function quickpic_excerpt_more( $more ) {
	return ' &bull; <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read More', 'quickpic' ) . ' &raquo;</a>';
}
add_filter( 'excerpt_more', 'quickpic_excerpt_more' );

// Post date, author, comments and categories line, used in lists and single posts
function quickpic_post_meta( $show_comments = true ) {
	echo '<p class="post-meta">';

	printf(
		'<time datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	// On a post's own page, show when it was last updated (if a day or more after publishing).
	if ( is_singular() && get_the_modified_time( 'U' ) - get_the_time( 'U' ) >= DAY_IN_SECONDS ) {
		printf(
			' | <span class="updated-date">%1$s <time datetime="%2$s">%3$s</time></span>',
			esc_html__( 'Updated', 'quickpic' ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);
	}

	/* translators: %s: post author name */
	echo ' | ' . sprintf( esc_html__( 'By %s', 'quickpic' ), esc_html( get_the_author() ) );

	if ( $show_comments && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo ' | ';
		comments_popup_link( __( 'No Comments', 'quickpic' ), __( '1 Comment', 'quickpic' ), __( '% Comments', 'quickpic' ) );
	}

	$categories = get_the_category_list( ', ' );
	if ( $categories ) {
		/* translators: %s: list of categories */
		echo ' | ' . sprintf( esc_html__( 'Filed in: %s', 'quickpic' ), $categories ); // phpcs:ignore WordPress.Security.EscapeOutput -- WordPress builds this list.
	}

	edit_post_link( __( 'Edit', 'quickpic' ), ' | ', '' );

	echo '</p>';
}

/*
 * Customizer: Appearance > Customize > Colours
 * - Header colour: the green of the header band and the bottom box.
 * - Accent colour: links, buttons and page numbers.
 * - Dark mode: follow the reader's device setting (on by default) or stay light.
 */
function quickpic_customize_register( $wp_customize ) {

	$wp_customize->add_setting( 'quickpic_brand_color', array(
		'default'           => '#005500',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'quickpic_brand_color', array(
		'label'       => __( 'Header colour', 'quickpic' ),
		'description' => __( 'The header band and the box at the bottom of every page. Pick a darker colour so white text stays readable on it.', 'quickpic' ),
		'section'     => 'colors',
	) ) );

	$wp_customize->add_setting( 'quickpic_accent_color', array(
		'default'           => '#088000',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'quickpic_accent_color', array(
		'label'       => __( 'Accent colour', 'quickpic' ),
		'description' => __( 'Links, buttons and page numbers.', 'quickpic' ),
		'section'     => 'colors',
	) ) );

	$wp_customize->add_setting( 'quickpic_dark_mode', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'quickpic_dark_mode', array(
		'type'        => 'checkbox',
		'label'       => __( 'Dark mode for readers who use it', 'quickpic' ),
		'description' => __( 'Turns the site dark when the reader\'s phone or computer is set to dark. Untick to always stay light.', 'quickpic' ),
		'section'     => 'colors',
	) );
}
add_action( 'customize_register', 'quickpic_customize_register' );

// The theme's two colours: the saved one, or the default.
function quickpic_colors() {
	$brand  = sanitize_hex_color( get_theme_mod( 'quickpic_brand_color', '#005500' ) );
	$accent = sanitize_hex_color( get_theme_mod( 'quickpic_accent_color', '#088000' ) );
	return array(
		'brand'  => $brand ? $brand : '#005500',
		'accent' => $accent ? $accent : '#088000',
	);
}

// The owner's colours as CSS, or '' when both are the defaults.
function quickpic_colors_css() {
	$colors = quickpic_colors();
	$css    = '';
	if ( '#005500' !== strtolower( $colors['brand'] ) ) {
		$css .= '--qpic-brand:' . $colors['brand'] . ';';
	}
	if ( '#088000' !== strtolower( $colors['accent'] ) ) {
		$css .= '--qpic-accent:' . $colors['accent'] . ';';
	}
	return $css ? ':root{' . $css . '}' : '';
}

// Is this post or page using the Wide template?
function quickpic_is_wide() {
	return is_singular() && is_page_template( 'template-wide.php' );
}

// Wide pages get a wider content area for embeds and large images.
function quickpic_wide_content_width() {
	if ( quickpic_is_wide() ) {
		$GLOBALS['content_width'] = 1060;
	}
}
add_action( 'template_redirect', 'quickpic_wide_content_width' );

// Body classes: dark mode switched off, Wide template.
function quickpic_body_classes( $classes ) {
	if ( ! get_theme_mod( 'quickpic_dark_mode', true ) ) {
		$classes[] = 'qpic-light-only';
	}
	if ( quickpic_is_wide() ) {
		$classes[] = 'qpic-wide';
	}
	return $classes;
}
add_filter( 'body_class', 'quickpic_body_classes' );

// Load stylesheets
function quickpic_scripts() {

	// Theme version plus the file's last change time, so browsers fetch fresh CSS
	// after every update, even when the version number stays the same.
	$version = wp_get_theme( get_template() )->get( 'Version' ) . '.' . filemtime( get_template_directory() . '/style.css' );

	// The whole theme is one small stylesheet.
	wp_enqueue_style( 'quickpic', get_stylesheet_uri(), array(), $version );

	// The owner's colours, if they picked any (a few bytes, no extra file).
	$colors = quickpic_colors_css();
	if ( $colors ) {
		wp_add_inline_style( 'quickpic', $colors );
	}

	// WordPress's own script for threaded comment replies, only where needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quickpic_scripts' );
