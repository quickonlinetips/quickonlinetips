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

		// Starter content: on a brand-new site, the Customizer offers ready
		// sidebar widgets (as blocks), a few pages and a main menu. Nothing is
		// saved until the owner clicks Publish, and existing sites never see it.
		add_theme_support( 'starter-content', array(
			'widgets'   => array(
				'sidebar' => array(
					'search'     => array( 'block', array( 'content' => '<!-- wp:search {"showLabel":false} /-->' ) ),
					'recent'     => array( 'block', array( 'content' => '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Recent Posts', 'quickchic' ) . '</h2><!-- /wp:heading --><!-- wp:latest-posts /-->' ) ),
					'categories' => array( 'block', array( 'content' => '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Categories', 'quickchic' ) . '</h2><!-- /wp:heading --><!-- wp:categories {"displayAsDropdown":true} /-->' ) ),
					'archives'   => array( 'block', array( 'content' => '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Archives', 'quickchic' ) . '</h2><!-- /wp:heading --><!-- wp:archives /-->' ) ),
				),
			),
			'posts'     => array( 'about', 'contact' ),
			'nav_menus' => array(
				'primary' => array(
					'name'  => __( 'Main Menu', 'quickchic' ),
					'items' => array( 'link_home', 'page_about', 'page_contact' ),
				),
			),
		) );
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
		'widget_categories'      => array( __( 'Categories', 'quickchic' ), '<!-- wp:categories {"displayAsDropdown":true} /-->' ),
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

	// The theme's own colours in the block editor's colour picker,
	// instead of WordPress's general rainbow.
	$accent = sanitize_hex_color( get_theme_mod( 'quickchic_accent_color', '#c32364' ) );
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => __( 'Accent', 'quickchic' ), 'slug' => 'accent', 'color' => $accent ? $accent : '#c32364' ),
		array( 'name' => __( 'Text', 'quickchic' ), 'slug' => 'text', 'color' => '#333333' ),
		array( 'name' => __( 'Grey', 'quickchic' ), 'slug' => 'grey', 'color' => '#666666' ),
		array( 'name' => __( 'Light grey', 'quickchic' ), 'slug' => 'light-grey', 'color' => '#f6f6f6' ),
		array( 'name' => __( 'White', 'quickchic' ), 'slug' => 'white', 'color' => '#ffffff' ),
	) );

	// Text sizes in the editor, in step with the theme.
	add_theme_support( 'editor-font-sizes', array(
		array( 'name' => __( 'Small', 'quickchic' ), 'slug' => 'small', 'size' => 14 ),
		array( 'name' => __( 'Normal', 'quickchic' ), 'slug' => 'normal', 'size' => 17 ),
		array( 'name' => __( 'Large', 'quickchic' ), 'slug' => 'large', 'size' => 22 ),
		array( 'name' => __( 'Extra large', 'quickchic' ), 'slug' => 'x-large', 'size' => 28 ),
	) );

	// No gradient presets: they don't suit the theme's flat look.
	add_theme_support( 'editor-gradient-presets', array() );
}
add_action( 'after_setup_theme', 'quickchic_add_editor_styles' );

// The owner's accent colour in the editor too.
function quickchic_editor_accent( $settings ) {
	$accent = quickchic_accent_css();
	if ( $accent && isset( $settings['styles'] ) && is_array( $settings['styles'] ) ) {
		$settings['styles'][] = array( 'css' => str_replace( ':root', 'body', $accent ) );
	}
	return $settings;
}
add_filter( 'block_editor_settings_all', 'quickchic_editor_accent' );

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

/*
 * Customizer: Appearance > Customize > Colours
 * - Accent colour: the pink used for links, buttons and borders.
 * - Dark mode: follow the reader's device setting (on by default) or stay light.
 */
function quickchic_customize_register( $wp_customize ) {

	$wp_customize->add_setting( 'quickchic_accent_color', array(
		'default'           => '#c32364',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'quickchic_accent_color', array(
		'label'       => __( 'Accent colour', 'quickchic' ),
		'description' => __( 'Links, buttons, menus and borders.', 'quickchic' ),
		'section'     => 'colors',
	) ) );

	$wp_customize->add_setting( 'quickchic_dark_mode', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'quickchic_dark_mode', array(
		'type'        => 'checkbox',
		'label'       => __( 'Dark mode for readers who use it', 'quickchic' ),
		'description' => __( 'Turns the site dark when the reader\'s phone or computer is set to dark. Untick to always stay light.', 'quickchic' ),
		'section'     => 'colors',
	) );
}
add_action( 'customize_register', 'quickchic_customize_register' );

// The accent colour, or '' when it is the default pink.
function quickchic_accent_css() {
	$accent = sanitize_hex_color( get_theme_mod( 'quickchic_accent_color', '#c32364' ) );
	if ( ! $accent || '#c32364' === strtolower( $accent ) ) {
		return '';
	}
	return ':root{--qc-accent:' . $accent . ';}';
}

// Is this post or page using the Full width template?
function quickchic_is_full_width() {
	return is_singular() && is_page_template( 'template-full-width.php' );
}

// Full width pages get a wider content area for embeds and large images.
function quickchic_full_width_content_width() {
	if ( quickchic_is_full_width() ) {
		$GLOBALS['content_width'] = 960;
	}
}
add_action( 'template_redirect', 'quickchic_full_width_content_width' );

// Body classes: dark mode switched off, Full width template.
function quickchic_body_classes( $classes ) {
	if ( ! get_theme_mod( 'quickchic_dark_mode', true ) ) {
		$classes[] = 'qc-light-only';
	}
	if ( quickchic_is_full_width() ) {
		$classes[] = 'qc-full-width';
	}
	return $classes;
}
add_filter( 'body_class', 'quickchic_body_classes' );

// Load stylesheets
function quickchic_scripts() {

	// Theme version from style.css, so browsers fetch fresh CSS after every update.
	$version = wp_get_theme( get_template() )->get( 'Version' );

	// The whole theme is one small stylesheet.
	wp_enqueue_style( 'quickchic', get_stylesheet_uri(), array(), $version );

	// The owner's accent colour, if they picked one (a few bytes, no extra file).
	$accent = quickchic_accent_css();
	if ( $accent ) {
		wp_add_inline_style( 'quickchic', $accent );
	}

	// WordPress's own script for threaded comment replies, only where needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quickchic_scripts' );
