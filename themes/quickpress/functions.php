<?php
/**
 * QuickPress functions and setup.
 *
 * Kept deliberately small: no JavaScript, no options panel,
 * just the theme supports WordPress needs.
 *
 * @package QuickPress
 */

// Icons for social links in the Footer Menu.
require get_template_directory() . '/inc/social-icons.php';

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
	add_theme_support( 'editor-styles' );
	add_editor_style( 'custom-editor-style.css' );

	// The theme's own colours in the block editor's colour picker,
	// instead of WordPress's general rainbow.
	$colors = quickpress_colors();
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => __( 'Header blue', 'quickpress' ), 'slug' => 'brand', 'color' => $colors['brand'] ),
		array( 'name' => __( 'Accent', 'quickpress' ), 'slug' => 'accent', 'color' => $colors['accent'] ),
		array( 'name' => __( 'Text', 'quickpress' ), 'slug' => 'text', 'color' => '#333333' ),
		array( 'name' => __( 'Grey', 'quickpress' ), 'slug' => 'grey', 'color' => '#707070' ),
		array( 'name' => __( 'Light grey', 'quickpress' ), 'slug' => 'light-grey', 'color' => '#f6f6f6' ),
		array( 'name' => __( 'White', 'quickpress' ), 'slug' => 'white', 'color' => '#ffffff' ),
	) );

	// Text sizes in the editor, in step with the theme's 14.5px post text.
	add_theme_support( 'editor-font-sizes', array(
		array( 'name' => __( 'Small', 'quickpress' ), 'slug' => 'small', 'size' => 12.5 ),
		array( 'name' => __( 'Normal', 'quickpress' ), 'slug' => 'normal', 'size' => 14.5 ),
		array( 'name' => __( 'Large', 'quickpress' ), 'slug' => 'large', 'size' => 19 ),
		array( 'name' => __( 'Extra large', 'quickpress' ), 'slug' => 'x-large', 'size' => 25 ),
	) );

	// No gradient presets: they don't suit the theme's flat look.
	add_theme_support( 'editor-gradient-presets', array() );
}
add_action( 'after_setup_theme', 'quickpress_add_editor_styles' );

/*
 * Block styles: extra looks in the block editor's Styles panel.
 * The CSS lives in style.css and custom-editor-style.css, so nothing extra loads.
 */
function quickpress_block_styles() {
	register_block_style( 'core/quote', array(
		'name'  => 'qp-accent',
		'label' => __( 'Accent bar', 'quickpress' ),
	) );
	register_block_style( 'core/group', array(
		'name'  => 'qp-soft-box',
		'label' => __( 'Soft box', 'quickpress' ),
	) );
}
add_action( 'init', 'quickpress_block_styles' );

/*
 * Block patterns: ready-made boxes in the editor's Patterns tab, under QuickPress.
 * Handy in a post or in the Below Posts widget area.
 */
function quickpress_block_patterns() {
	register_block_pattern_category( 'quickpress', array( 'label' => __( 'QuickPress', 'quickpress' ) ) );

	register_block_pattern( 'quickpress/newsletter-box', array(
		'title'       => __( 'Newsletter sign-up box', 'quickpress' ),
		'description' => __( 'A soft box asking readers to subscribe, with a button.', 'quickpress' ),
		'categories'  => array( 'quickpress' ),
		'content'     => '<!-- wp:group {"className":"is-style-qp-soft-box"} --><div class="wp-block-group is-style-qp-soft-box">'
			. '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Enjoyed this post?', 'quickpress' ) . '</h3><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>' . esc_html__( 'Get new posts by email. No spam, unsubscribe any time.', 'quickpress' ) . '</p><!-- /wp:paragraph -->'
			. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Subscribe', 'quickpress' ) . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
			. '</div><!-- /wp:group -->',
	) );

	register_block_pattern( 'quickpress/about-author', array(
		'title'       => __( 'About the author', 'quickpress' ),
		'description' => __( 'A soft box with a short note about the writer.', 'quickpress' ),
		'categories'  => array( 'quickpress' ),
		'content'     => '<!-- wp:group {"className":"is-style-qp-soft-box"} --><div class="wp-block-group is-style-qp-soft-box">'
			. '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'About the author', 'quickpress' ) . '</h3><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>' . esc_html__( 'A few words about you: what you write about, and why readers should come back.', 'quickpress' ) . '</p><!-- /wp:paragraph -->'
			. '</div><!-- /wp:group -->',
	) );
}
add_action( 'init', 'quickpress_block_patterns' );

// The owner's colours in the editor too.
function quickpress_editor_colors( $settings ) {
	$colors = quickpress_colors_css();
	if ( $colors && isset( $settings['styles'] ) && is_array( $settings['styles'] ) ) {
		$settings['styles'][] = array( 'css' => str_replace( ':root', 'body', $colors ) );
	}
	return $settings;
}
add_filter( 'block_editor_settings_all', 'quickpress_editor_colors' );

/*
 * Long lists in the theme's own starter widgets (Categories in the sidebar,
 * Pages in the footer): show the first 10 items, then "12 more" that opens
 * the rest in place. Plain HTML <details>, so no JavaScript.
 * Only blocks with the CSS class "qp-short" are shortened, so lists an
 * owner adds themselves always show in full. (Owners can opt in by adding
 * qp-short under the block's Advanced > Additional CSS class.)
 * Change the number in a child theme: add_filter( 'quickpress_list_limit', fn() => 15 );
 */
function quickpress_shorten_long_lists( $block_content, $block ) {
	$blocks = array( 'core/categories', 'core/page-list', 'core/archives' );
	if ( empty( $block['blockName'] ) || ! in_array( $block['blockName'], $blocks, true ) ) {
		return $block_content;
	}
	$classes = isset( $block['attrs']['className'] ) ? ' ' . $block['attrs']['className'] . ' ' : '';
	// qc-short is QuickChic's name for the same thing, so lists set up under
	// QuickChic keep shortening after switching to QuickPress.
	$marked = false !== strpos( $classes, ' qp-short ' ) || false !== strpos( $classes, ' qc-short ' );
	if ( is_admin() || ! $marked || ! empty( $block['attrs']['displayAsDropdown'] ) ) {
		return $block_content;
	}

	$limit = (int) apply_filters( 'quickpress_list_limit', 10 );
	if ( $limit < 1 || substr_count( $block_content, '<li' ) <= $limit || ! class_exists( 'DOMDocument' ) ) {
		return $block_content;
	}

	$doc       = new DOMDocument();
	$old_state = libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="qp-list">' . $block_content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $old_state );

	$xpath = new DOMXPath( $doc );
	$wrap  = $xpath->query( '//div[@id="qp-list"]' )->item( 0 );
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
	$more_item->setAttribute( 'class', 'qp-more' );
	$details = $doc->createElement( 'details' );
	$summary = $doc->createElement( 'summary' );
	/* translators: %d: number of hidden list items */
	$summary->appendChild( $doc->createTextNode( sprintf( _n( '%d more', '%d more', $extra, 'quickpress' ), $extra ) ) );
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
add_filter( 'render_block', 'quickpress_shorten_long_lists', 10, 2 );

// The "Read More" link used after excerpts.
function quickpress_read_more_link() {
	return ' &bull; <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read More', 'quickpress' ) . ' &raquo;</a>';
}

// After an automatic excerpt that was cut short.
function quickpress_excerpt_more( $more ) {
	return quickpress_read_more_link();
}
add_filter( 'excerpt_more', 'quickpress_excerpt_more' );

// WordPress leaves out the link in two cases where the post does go on:
// a hand-written excerpt (the Excerpt box in the editor), and a post with a
// More block early on, whose excerpt stops there. Add it in both, so every
// post that continues ends with Read More.
function quickpress_manual_excerpt_more( $excerpt ) {
	if ( is_admin() || is_feed() || ! in_the_loop() || false !== strpos( $excerpt, 'class="read-more"' ) ) {
		return $excerpt;
	}
	if ( has_excerpt() || false !== strpos( get_post_field( 'post_content', get_the_ID() ), '<!--more' ) ) {
		return $excerpt . quickpress_read_more_link();
	}
	return $excerpt;
}
add_filter( 'get_the_excerpt', 'quickpress_manual_excerpt_more', 20 );

// Post date, author, comments and categories line, used in lists and single posts
function quickpress_post_meta( $show_comments = true ) {
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
			esc_html__( 'Updated', 'quickpress' ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);
	}

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

/*
 * Customizer: Appearance > Customize > Post Lists
 * - Show excerpts (default) or full posts on the home page and archives.
 * - Excerpt length in words (WordPress's default is 55).
 * Search results always show excerpts.
 */
function quickpress_customize_post_lists( $wp_customize ) {

	$wp_customize->add_section( 'quickpress_post_lists', array(
		'title'       => __( 'Post Lists', 'quickpress' ),
		'description' => __( 'How posts appear on the home page and in category, tag, date and author archives.', 'quickpress' ),
		'priority'    => 45,
	) );

	$wp_customize->add_setting( 'quickpress_list_display', array(
		'default'           => 'excerpt',
		'sanitize_callback' => 'quickpress_sanitize_list_display',
	) );
	$wp_customize->add_control( 'quickpress_list_display', array(
		'type'        => 'radio',
		'label'       => __( 'Show', 'quickpress' ),
		'description' => __( 'With full posts, add a More block in a post to choose where it breaks.', 'quickpress' ),
		'section'     => 'quickpress_post_lists',
		'choices'     => array(
			'excerpt' => __( 'A short excerpt with Read More', 'quickpress' ),
			'full'    => __( 'The full post', 'quickpress' ),
		),
	) );

	$wp_customize->add_setting( 'quickpress_excerpt_length', array(
		'default'           => 55,
		'sanitize_callback' => 'quickpress_sanitize_excerpt_length',
	) );
	$wp_customize->add_control( 'quickpress_excerpt_length', array(
		'type'        => 'number',
		'label'       => __( 'Excerpt length (words)', 'quickpress' ),
		'description' => __( 'Between 10 and 200. WordPress uses 55.', 'quickpress' ),
		'section'     => 'quickpress_post_lists',
		'input_attrs' => array( 'min' => 10, 'max' => 200, 'step' => 5 ),
	) );
}
add_action( 'customize_register', 'quickpress_customize_post_lists' );

function quickpress_sanitize_list_display( $value ) {
	return 'full' === $value ? 'full' : 'excerpt';
}

function quickpress_sanitize_excerpt_length( $value ) {
	return max( 10, min( 200, absint( $value ) ) );
}

// Should this post list show full posts? Search results always use excerpts.
function quickpress_show_full_posts() {
	return ! is_search() && 'full' === get_theme_mod( 'quickpress_list_display', 'excerpt' );
}

// The excerpt length chosen in Customize > Post Lists.
function quickpress_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return quickpress_sanitize_excerpt_length( get_theme_mod( 'quickpress_excerpt_length', 55 ) );
}
add_filter( 'excerpt_length', 'quickpress_excerpt_length' );

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

	// Theme version plus the file's last change time, so browsers fetch fresh CSS
	// after every update, even when the version number stays the same.
	$version = wp_get_theme( get_template() )->get( 'Version' ) . '.' . filemtime( get_template_directory() . '/style.css' );

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
