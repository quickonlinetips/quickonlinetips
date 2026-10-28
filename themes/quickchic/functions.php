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
					'categories' => array( 'block', array( 'content' => '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Categories', 'quickchic' ) . '</h2><!-- /wp:heading --><!-- wp:categories {"className":"qc-short"} /-->' ) ),
				),
				// The three footer columns, so a new site shows the whole design.
				'footer-1' => array(
					'about_site' => array( 'block', array( 'content' => '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'About This Site', 'quickchic' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__( 'A few words about you and what you write about. Change this in Appearance > Widgets.', 'quickchic' ) . '</p><!-- /wp:paragraph -->' ) ),
				),
				'footer-2' => array(
					'comments' => array( 'block', array( 'content' => '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Recent Comments', 'quickchic' ) . '</h2><!-- /wp:heading --><!-- wp:latest-comments {"displayAvatar":false,"displayDate":false,"displayExcerpt":false} /-->' ) ),
				),
				'footer-3' => array(
					'pages' => array( 'block', array( 'content' => '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Pages', 'quickchic' ) . '</h2><!-- /wp:heading --><!-- wp:page-list {"className":"qc-short"} /-->' ) ),
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

	// Below each post: newsletter sign-up, author note, related links, an ad.
	register_sidebar( array(
		'name'          => __( 'Below Posts', 'quickchic' ),
		'id'            => 'after-post',
		'description'   => __( 'Shown under the post on each single post, before the comments. Empty means hidden.', 'quickchic' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	// Footer: up to three columns. Only the ones with widgets are shown.
	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar( array(
			/* translators: %d: footer column number, 1 to 3 */
			'name'          => sprintf( __( 'Footer %d', 'quickchic' ), $i ),
			'id'            => 'footer-' . $i,
			'description'   => __( 'A column at the bottom of every page. Leave all three empty for no footer widgets.', 'quickchic' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}
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
		'widget_categories'      => array( __( 'Categories', 'quickchic' ), '<!-- wp:categories {"className":"qc-short"} /-->' ),
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
		array( 'name' => __( 'Small', 'quickchic' ), 'slug' => 'small', 'size' => 13 ),
		array( 'name' => __( 'Normal', 'quickchic' ), 'slug' => 'normal', 'size' => 15 ),
		array( 'name' => __( 'Large', 'quickchic' ), 'slug' => 'large', 'size' => 20 ),
		array( 'name' => __( 'Extra large', 'quickchic' ), 'slug' => 'x-large', 'size' => 26 ),
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

/*
 * Long lists in the theme's own starter widgets (Categories in the sidebar,
 * Pages in the footer): show the first 10 items, then "12 more" that opens
 * the rest in place. Plain HTML <details>, so no JavaScript.
 * Only blocks with the CSS class "qc-short" are shortened, so lists an
 * owner adds themselves always show in full. (Owners can opt in by adding
 * qc-short under the block's Advanced > Additional CSS class.)
 * Change the number in a child theme: add_filter( 'quickchic_list_limit', fn() => 15 );
 */
function quickchic_shorten_long_lists( $block_content, $block ) {
	$blocks = array( 'core/categories', 'core/page-list', 'core/archives' );
	if ( empty( $block['blockName'] ) || ! in_array( $block['blockName'], $blocks, true ) ) {
		return $block_content;
	}
	$classes = isset( $block['attrs']['className'] ) ? ' ' . $block['attrs']['className'] . ' ' : '';
	if ( is_admin() || false === strpos( $classes, ' qc-short ' ) || ! empty( $block['attrs']['displayAsDropdown'] ) ) {
		return $block_content;
	}

	$limit = (int) apply_filters( 'quickchic_list_limit', 10 );
	if ( $limit < 1 || substr_count( $block_content, '<li' ) <= $limit || ! class_exists( 'DOMDocument' ) ) {
		return $block_content;
	}

	$doc       = new DOMDocument();
	$old_state = libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="qc-list">' . $block_content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $old_state );

	$xpath = new DOMXPath( $doc );
	$wrap  = $xpath->query( '//div[@id="qc-list"]' )->item( 0 );
	$list  = $wrap ? $xpath->query( './/ul', $wrap )->item( 0 ) : null;
	if ( ! $list ) {
		return $block_content;
	}

	// Count every line, sub-items included, but never split an item from its own
	// sub-items: show whole top-level items until the next would pass the limit.
	$items = array();
	foreach ( $list->childNodes as $node ) {
		if ( XML_ELEMENT_NODE === $node->nodeType && 'li' === $node->nodeName ) {
			$items[] = $node;
		}
	}
	$shown_lines = 0;
	$keep        = 0;
	foreach ( $items as $item ) {
		$lines = 1 + $item->getElementsByTagName( 'li' )->length;
		if ( $keep > 0 && $shown_lines + $lines > $limit ) {
			break;
		}
		$shown_lines += $lines;
		$keep++;
	}
	$hidden = array_slice( $items, $keep );
	if ( ! $hidden ) {
		return $block_content;
	}
	$extra = 0;
	foreach ( $hidden as $item ) {
		$extra += 1 + $item->getElementsByTagName( 'li' )->length;
	}

	$more_item = $doc->createElement( 'li' );
	$more_item->setAttribute( 'class', 'qc-more' );
	$details = $doc->createElement( 'details' );
	$summary = $doc->createElement( 'summary' );
	/* translators: %d: number of hidden list items */
	$summary->appendChild( $doc->createTextNode( sprintf( _n( '%d more', '%d more', $extra, 'quickchic' ), $extra ) ) );
	$rest = $doc->createElement( 'ul' );
	foreach ( $hidden as $item ) {
		$rest->appendChild( $item ); // Moves the item into the hidden list.
	}
	$details->appendChild( $summary );
	$details->appendChild( $rest );
	$more_item->appendChild( $details );
	$list->appendChild( $more_item );

	$html = '';
	foreach ( $wrap->childNodes as $node ) {
		$html .= $doc->saveHTML( $node );
	}
	return $html;
}
add_filter( 'render_block', 'quickchic_shorten_long_lists', 10, 2 );

// The "Read More" link used after excerpts.
function quickchic_read_more_link() {
	return ' &bull; <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read More', 'quickchic' ) . ' &raquo;</a>';
}

// After an automatic excerpt that was cut short.
function quickchic_excerpt_more( $more ) {
	return quickchic_read_more_link();
}
add_filter( 'excerpt_more', 'quickchic_excerpt_more' );

// After a hand-written excerpt (the Excerpt box in the editor), which
// WordPress shows without a link, so every post in a list ends the same way.
function quickchic_manual_excerpt_more( $excerpt ) {
	if ( is_admin() || is_feed() || ! in_the_loop() || ! has_excerpt() ) {
		return $excerpt;
	}
	return $excerpt . quickchic_read_more_link();
}
add_filter( 'get_the_excerpt', 'quickchic_manual_excerpt_more', 20 );

// Post date, author, comments and categories line, used in lists and single posts
function quickchic_post_meta( $show_comments = true ) {
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
			esc_html__( 'Updated', 'quickchic' ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);
	}

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

	// Theme version plus the file's last change time, so browsers fetch fresh CSS
	// after every update, even when the version number stays the same.
	$version = wp_get_theme( get_template() )->get( 'Version' ) . '.' . filemtime( get_template_directory() . '/style.css' );

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
