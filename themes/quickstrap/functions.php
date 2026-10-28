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

// Icons for social links in the Footer Menu (inline SVG, no icon font needed).
require get_template_directory() . '/inc/social-icons.php';

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

		// Starter content: on a brand-new site, the Customizer offers ready
		// widgets (as blocks), a few pages and a main menu, so the whole design
		// shows. Nothing is saved until the owner clicks Publish, and existing
		// sites never see it.
		$quickstrap_heading = function ( $text ) {
			return '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $text ) . '</h2><!-- /wp:heading -->';
		};
		$quickstrap_search  = '<!-- wp:search ' . wp_json_encode( array(
			'label'      => __( 'Search', 'quickstrap' ),
			'showLabel'  => false,
			'buttonText' => __( 'Search', 'quickstrap' ),
		) ) . ' /-->';
		add_theme_support( 'starter-content', array(
			'widgets'   => array(
				'right-sidebar' => array(
					'search'     => array( 'block', array( 'content' => $quickstrap_search ) ),
					'recent'     => array( 'block', array( 'content' => $quickstrap_heading( __( 'Recent Posts', 'quickstrap' ) ) . '<!-- wp:latest-posts /-->' ) ),
				),
				// The narrow left column, so a new site shows all three columns.
				'left-sidebar'  => array(
					'categories' => array( 'block', array( 'content' => $quickstrap_heading( __( 'Categories', 'quickstrap' ) ) . '<!-- wp:categories {"className":"qs-short"} /-->' ) ),
				),
				// Footer columns, so a new site shows the footer too.
				'footer-1'      => array(
					'about_site' => array( 'block', array( 'content' => $quickstrap_heading( __( 'About This Site', 'quickstrap' ) ) . '<!-- wp:paragraph --><p>' . esc_html__( 'A few words about you and what you write about. Change this in Appearance > Widgets.', 'quickstrap' ) . '</p><!-- /wp:paragraph -->' ) ),
				),
				'footer-2'      => array(
					'comments' => array( 'block', array( 'content' => $quickstrap_heading( __( 'Recent Comments', 'quickstrap' ) ) . '<!-- wp:latest-comments {"displayAvatar":false,"displayDate":false,"displayExcerpt":false} /-->' ) ),
				),
				'footer-3'      => array(
					'pages' => array( 'block', array( 'content' => $quickstrap_heading( __( 'Pages', 'quickstrap' ) ) . '<!-- wp:page-list {"className":"qs-short"} /-->' ) ),
				),
			),
			'posts'     => array( 'about', 'contact' ),
			'nav_menus' => array(
				'header-nav' => array(
					'name'  => __( 'Main Menu', 'quickstrap' ),
					'items' => array( 'link_home', 'page_about', 'page_contact' ),
				),
			),
		) );
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
		'description' => __( 'A narrow column on the left on wide screens (Appearance > Customize > Layout can swap the sides). On tablets and phones it moves below the posts and the right sidebar. Leave it empty for a two-column layout. While both sidebars are empty, a few default widgets fill both.', 'quickstrap' ),
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

	register_sidebar( array_merge( $wrappers, array(
		'name'        => __( 'Between Posts', 'quickstrap' ),
		'id'          => 'between-posts',
		'description' => __( 'Shown in post lists (home page, categories, tags, search) after the third post: a newsletter box, a notice, a sponsor message. Empty means hidden.', 'quickstrap' ),
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

/*
 * Default sidebars, shown only while a sidebar has no widgets.
 * New sites park WordPress's own default widgets in Inactive, so without this
 * a new site would show an empty column. Built from WordPress's own blocks,
 * so it needs no plugin and looks the same as the block widgets people add
 * later in Appearance > Widgets. As soon as a widget is added, it steps aside.
 *
 * Right sidebar (while it is empty): Search, Recent Posts, Recent Comments.
 * Left sidebar (only while BOTH sidebars are empty, so a new site shows the
 * three-column design): Categories, a list that suits the narrow column.
 * Once the owner fills the right sidebar, an empty left sidebar means
 * "two columns, please" and nothing is shown there.
 *
 * A child theme can switch both off: add_filter( 'quickstrap_show_default_sidebar', '__return_false' );
 */
function quickstrap_show_default_sidebar() {
	return (bool) apply_filters( 'quickstrap_show_default_sidebar', true );
}

// Does the left sidebar show its default widgets?
function quickstrap_show_default_left() {
	return quickstrap_show_default_sidebar()
		&& ! is_active_sidebar( 'left-sidebar' )
		&& ! is_active_sidebar( 'right-sidebar' );
}

function quickstrap_default_sidebar( $side = 'right' ) {
	if ( ! quickstrap_show_default_sidebar() ) {
		return;
	}

	if ( 'left' === $side ) {
		$sections = array(
			'widget_categories' => array( __( 'Categories', 'quickstrap' ), '<!-- wp:categories {"className":"qs-short"} /-->' ),
		);
	} else {
		$search = '<!-- wp:search ' . wp_json_encode( array(
			'label'      => __( 'Search', 'quickstrap' ),
			'showLabel'  => false,
			'buttonText' => __( 'Search', 'quickstrap' ),
		) ) . ' /-->';

		$sections = array(
			'widget_search'          => array( '', $search ),
			'widget_recent_entries'  => array( __( 'Recent Posts', 'quickstrap' ), '<!-- wp:latest-posts /-->' ),
			'widget_recent_comments' => array( __( 'Recent Comments', 'quickstrap' ), '<!-- wp:latest-comments {"displayAvatar":false,"displayDate":false,"displayExcerpt":false} /-->' ),
		);
	}

	foreach ( $sections as $class => $section ) {
		echo '<section class="widget ' . esc_attr( $class ) . '">';
		if ( $section[0] ) {
			echo '<h2 class="widget-title">' . esc_html( $section[0] ) . '</h2>';
		}
		echo do_blocks( $section[1] ); // phpcs:ignore WordPress.Security.EscapeOutput -- core blocks escape their own output.
		echo '</section>';
	}
}

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

/*
 * Sub-menus in the main menu, with no JavaScript.
 * Menu items with sub-items get a small arrow, and the sub-items open:
 * - on wide screens, as a dropdown when the mouse is over the item or a link
 *   inside it has keyboard focus (CSS :hover and :focus-within);
 * - on phones and tablets, always shown, indented, in the opened menu.
 * Up to three levels (set by 'depth' in header.php).
 */
function quickstrap_menu_arrow( $title, $item, $args, $depth ) {
	if ( isset( $args->theme_location ) && 'header-nav' === $args->theme_location
		&& in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$title .= '<span class="qs-arrow" aria-hidden="true"></span>';
	}
	return $title;
}
add_filter( 'nav_menu_item_title', 'quickstrap_menu_arrow', 10, 4 );

// Editor style: posts look in the editor much like they do on the site.
function quickstrap_add_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'custom-editor-style.css' );

	// The theme's own colours in the block editor's colour picker,
	// instead of WordPress's general rainbow.
	$colors = quickstrap_colors();
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => __( 'Accent', 'quickstrap' ), 'slug' => 'accent', 'color' => $colors['accent'] ),
		array( 'name' => __( 'Header', 'quickstrap' ), 'slug' => 'brand', 'color' => $colors['brand'] ),
		array( 'name' => __( 'Text', 'quickstrap' ), 'slug' => 'text', 'color' => '#212529' ),
		array( 'name' => __( 'Grey', 'quickstrap' ), 'slug' => 'grey', 'color' => '#6c757d' ),
		array( 'name' => __( 'Light grey', 'quickstrap' ), 'slug' => 'light-grey', 'color' => '#f8f9fa' ),
		array( 'name' => __( 'White', 'quickstrap' ), 'slug' => 'white', 'color' => '#ffffff' ),
	) );

	// Text sizes in the editor, in step with the theme's 15px post text.
	add_theme_support( 'editor-font-sizes', array(
		array( 'name' => __( 'Small', 'quickstrap' ), 'slug' => 'small', 'size' => 13 ),
		array( 'name' => __( 'Normal', 'quickstrap' ), 'slug' => 'normal', 'size' => 15 ),
		array( 'name' => __( 'Large', 'quickstrap' ), 'slug' => 'large', 'size' => 20 ),
		array( 'name' => __( 'Extra large', 'quickstrap' ), 'slug' => 'x-large', 'size' => 26 ),
	) );

	// No gradient presets: they don't suit the theme's flat look.
	add_theme_support( 'editor-gradient-presets', array() );
}
add_action( 'after_setup_theme', 'quickstrap_add_editor_styles' );

/*
 * Block styles: extra looks in the block editor's Styles panel.
 * The CSS lives in style.css and custom-editor-style.css, so nothing extra loads.
 */
function quickstrap_block_styles() {
	register_block_style( 'core/quote', array(
		'name'  => 'qs-accent',
		'label' => __( 'Accent bar', 'quickstrap' ),
	) );
	register_block_style( 'core/group', array(
		'name'  => 'qs-soft-box',
		'label' => __( 'Soft box', 'quickstrap' ),
	) );

	// Bootstrap's look without typing classes: pick a Group, then a style.
	$group_styles = array(
		'qs-card'          => __( 'Card', 'quickstrap' ),
		'qs-alert-info'    => __( 'Alert: info', 'quickstrap' ),
		'qs-alert-success' => __( 'Alert: success', 'quickstrap' ),
		'qs-alert-warning' => __( 'Alert: warning', 'quickstrap' ),
	);
	foreach ( $group_styles as $name => $label ) {
		register_block_style( 'core/group', array(
			'name'  => $name,
			'label' => $label,
		) );
	}
}
add_action( 'init', 'quickstrap_block_styles' );

/*
 * Block patterns: ready-made boxes in the editor's Patterns tab, under QuickStrap.
 * Handy in a post or in the Below Posts widget area.
 */
function quickstrap_block_patterns() {
	register_block_pattern_category( 'quickstrap', array( 'label' => __( 'QuickStrap', 'quickstrap' ) ) );

	register_block_pattern( 'quickstrap/newsletter-box', array(
		'title'       => __( 'Newsletter sign-up box', 'quickstrap' ),
		'description' => __( 'A soft box asking readers to subscribe, with a button.', 'quickstrap' ),
		'categories'  => array( 'quickstrap' ),
		'content'     => '<!-- wp:group {"className":"is-style-qs-soft-box"} --><div class="wp-block-group is-style-qs-soft-box">'
			. '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Enjoyed this post?', 'quickstrap' ) . '</h3><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>' . esc_html__( 'Get new posts by email. No spam, unsubscribe any time.', 'quickstrap' ) . '</p><!-- /wp:paragraph -->'
			. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Subscribe', 'quickstrap' ) . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
			. '</div><!-- /wp:group -->',
	) );

	register_block_pattern( 'quickstrap/about-author', array(
		'title'       => __( 'About the author', 'quickstrap' ),
		'description' => __( 'A soft box with a short note about the writer.', 'quickstrap' ),
		'categories'  => array( 'quickstrap' ),
		'content'     => '<!-- wp:group {"className":"is-style-qs-soft-box"} --><div class="wp-block-group is-style-qs-soft-box">'
			. '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'About the author', 'quickstrap' ) . '</h3><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>' . esc_html__( 'A few words about you: what you write about, and why readers should come back.', 'quickstrap' ) . '</p><!-- /wp:paragraph -->'
			. '</div><!-- /wp:group -->',
	) );

	// Bootstrap-style patterns, built from core blocks and the styles above.
	$card = function ( $title, $text ) {
		return '<!-- wp:column --><div class="wp-block-column">'
			. '<!-- wp:group {"className":"is-style-qs-card"} --><div class="wp-block-group is-style-qs-card">'
			. '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html( $title ) . '</h3><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>' . esc_html( $text ) . '</p><!-- /wp:paragraph -->'
			. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Learn more', 'quickstrap' ) . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
			. '</div><!-- /wp:group -->'
			. '</div><!-- /wp:column -->';
	};

	register_block_pattern( 'quickstrap/three-cards', array(
		'title'       => __( 'Three cards', 'quickstrap' ),
		'description' => __( 'Three Bootstrap-style cards side by side, each with a heading, text and an outline button.', 'quickstrap' ),
		'categories'  => array( 'quickstrap' ),
		'content'     => '<!-- wp:columns --><div class="wp-block-columns">'
			. $card( __( 'Fast', 'quickstrap' ), __( 'A sentence about the first thing you offer or write about.', 'quickstrap' ) )
			. $card( __( 'Simple', 'quickstrap' ), __( 'A sentence about the second thing, short and to the point.', 'quickstrap' ) )
			. $card( __( 'Free', 'quickstrap' ), __( 'A sentence about the third thing, and why readers will like it.', 'quickstrap' ) )
			. '</div><!-- /wp:columns -->',
	) );

	register_block_pattern( 'quickstrap/hero', array(
		'title'       => __( 'Hero box', 'quickstrap' ),
		'description' => __( 'A big centred welcome with a headline, a short lead paragraph and two buttons.', 'quickstrap' ),
		'categories'  => array( 'quickstrap' ),
		'content'     => '<!-- wp:group {"className":"qs-hero"} --><div class="wp-block-group qs-hero">'
			. '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Welcome to my site', 'quickstrap' ) . '</h2><!-- /wp:heading -->'
			. '<!-- wp:paragraph {"className":"lead"} --><p class="lead">' . esc_html__( 'One or two sentences on what readers will find here and why it helps them.', 'quickstrap' ) . '</p><!-- /wp:paragraph -->'
			. '<!-- wp:buttons --><div class="wp-block-buttons">'
			. '<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Start here', 'quickstrap' ) . '</a></div><!-- /wp:button -->'
			. '<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'About me', 'quickstrap' ) . '</a></div><!-- /wp:button -->'
			. '</div><!-- /wp:buttons -->'
			. '</div><!-- /wp:group -->',
	) );

	register_block_pattern( 'quickstrap/notice', array(
		'title'       => __( 'Notice', 'quickstrap' ),
		'description' => __( 'A Bootstrap-style info alert for a note, tip or update.', 'quickstrap' ),
		'categories'  => array( 'quickstrap' ),
		'content'     => '<!-- wp:group {"className":"is-style-qs-alert-info"} --><div class="wp-block-group is-style-qs-alert-info">'
			. '<!-- wp:paragraph --><p><strong>' . esc_html__( 'Note:', 'quickstrap' ) . '</strong> ' . esc_html__( 'A short tip or update for your readers. Pick another Alert style in the Styles panel for success or warning.', 'quickstrap' ) . '</p><!-- /wp:paragraph -->'
			. '</div><!-- /wp:group -->',
	) );
}
add_action( 'init', 'quickstrap_block_patterns' );

// The owner's colours in the editor too.
function quickstrap_editor_colors( $settings ) {
	$colors = quickstrap_colors_css();
	if ( $colors && isset( $settings['styles'] ) && is_array( $settings['styles'] ) ) {
		$settings['styles'][] = array( 'css' => str_replace( ':root', 'body', $colors ) );
	}
	return $settings;
}
add_filter( 'block_editor_settings_all', 'quickstrap_editor_colors' );

/*
 * Long lists in the theme's own starter widgets (Categories in the sidebar,
 * Pages in the footer): show the first 10 items, then "12 more" that opens
 * the rest in place. Plain HTML <details>, so no JavaScript.
 * Only blocks with the CSS class "qs-short" are shortened, so lists an
 * owner adds themselves always show in full. (Owners can opt in by adding
 * qs-short under the block's Advanced > Additional CSS class.)
 * Change the number in a child theme: add_filter( 'quickstrap_list_limit', fn() => 15 );
 */
function quickstrap_shorten_long_lists( $block_content, $block ) {
	$blocks = array( 'core/categories', 'core/page-list', 'core/archives' );
	if ( empty( $block['blockName'] ) || ! in_array( $block['blockName'], $blocks, true ) ) {
		return $block_content;
	}
	$classes = isset( $block['attrs']['className'] ) ? ' ' . $block['attrs']['className'] . ' ' : '';
	// Every QOT theme's name for the same thing (QuickChic qc-short, QuickPress
	// qp-short, QuickPic qpic-short), so lists set up under another QOT theme
	// keep shortening after switching to QuickStrap.
	$marked = (bool) preg_match( '/ (qs|qc|qp|qpic)-short /', $classes );
	if ( is_admin() || ! $marked || ! empty( $block['attrs']['displayAsDropdown'] ) ) {
		return $block_content;
	}

	$limit = (int) apply_filters( 'quickstrap_list_limit', 10 );
	if ( $limit < 1 || substr_count( $block_content, '<li' ) <= $limit || ! class_exists( 'DOMDocument' ) ) {
		return $block_content;
	}

	$doc       = new DOMDocument();
	$old_state = libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="qs-list">' . $block_content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $old_state );

	$xpath = new DOMXPath( $doc );
	$wrap  = $xpath->query( '//div[@id="qs-list"]' )->item( 0 );
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
	$more_item->setAttribute( 'class', 'qs-more' );
	$details = $doc->createElement( 'details' );
	$summary = $doc->createElement( 'summary' );
	/* translators: %d: number of hidden list items */
	$summary->appendChild( $doc->createTextNode( sprintf( _n( '%d more', '%d more', $extra, 'quickstrap' ), $extra ) ) );
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
add_filter( 'render_block', 'quickstrap_shorten_long_lists', 10, 2 );

// The "Read More" link used after excerpts.
function quickstrap_read_more_link() {
	return ' &bull; <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read More', 'quickstrap' ) . ' &raquo;</a>';
}

// After an automatic excerpt that was cut short.
function quickstrap_excerpt_more( $more ) {
	return quickstrap_read_more_link();
}
add_filter( 'excerpt_more', 'quickstrap_excerpt_more' );

// WordPress leaves out the link in two cases where the post does go on:
// a hand-written excerpt (the Excerpt box in the editor), and a post with a
// More block early on, whose excerpt stops there. Add it in both, so every
// post that continues ends with Read More.
function quickstrap_manual_excerpt_more( $excerpt ) {
	if ( is_admin() || is_feed() || ! in_the_loop() || false !== strpos( $excerpt, 'class="read-more"' ) ) {
		return $excerpt;
	}
	if ( has_excerpt() || false !== strpos( get_post_field( 'post_content', get_the_ID() ), '<!--more' ) ) {
		return $excerpt . quickstrap_read_more_link();
	}
	return $excerpt;
}
add_filter( 'get_the_excerpt', 'quickstrap_manual_excerpt_more', 20 );

/*
 * Customizer: Appearance > Customize > Layout
 * Where the two sidebars go on wide screens (see quickstrap_layout()).
 */
function quickstrap_customize_layout( $wp_customize ) {

	$wp_customize->add_section( 'quickstrap_layout', array(
		'title'       => __( 'Layout', 'quickstrap' ),
		'description' => __( 'How the two sidebars sit beside your posts on wide screens. A sidebar with no widgets isn\'t shown, and the posts widen to fill its space. On tablets and phones the sidebars always move below the posts, so no widget is hidden.', 'quickstrap' ),
		'priority'    => 44,
	) );

	$wp_customize->add_setting( 'quickstrap_layout', array(
		'default'           => 'narrow',
		'sanitize_callback' => 'quickstrap_sanitize_layout',
	) );
	$wp_customize->add_control( 'quickstrap_layout', array(
		'type'    => 'radio',
		'label'   => __( 'Sidebars', 'quickstrap' ),
		'section' => 'quickstrap_layout',
		'choices' => array(
			'narrow' => __( 'Narrow Left Sidebar, wider Right Sidebar', 'quickstrap' ),
			'swap'   => __( 'Swapped: Right Sidebar on the left, Left Sidebar on the right', 'quickstrap' ),
		),
	) );
}
add_action( 'customize_register', 'quickstrap_customize_layout' );

function quickstrap_sanitize_layout( $value ) {
	return in_array( $value, array( 'narrow', 'swap' ), true ) ? $value : 'narrow';
}

/*
 * Customizer: Appearance > Customize > Post Lists
 * - Show excerpts (default) or full posts on the home page and archives.
 * - Excerpt length in words (WordPress's default is 55).
 * Search results always show excerpts.
 */
function quickstrap_customize_post_lists( $wp_customize ) {

	$wp_customize->add_section( 'quickstrap_post_lists', array(
		'title'       => __( 'Post Lists', 'quickstrap' ),
		'description' => __( 'How posts appear on the home page and in category, tag, date and author archives.', 'quickstrap' ),
		'priority'    => 45,
	) );

	$wp_customize->add_setting( 'quickstrap_list_display', array(
		'default'           => 'excerpt',
		'sanitize_callback' => 'quickstrap_sanitize_list_display',
	) );
	$wp_customize->add_control( 'quickstrap_list_display', array(
		'type'        => 'radio',
		'label'       => __( 'Show', 'quickstrap' ),
		'description' => __( 'With full posts, add a More block in a post to choose where it breaks.', 'quickstrap' ),
		'section'     => 'quickstrap_post_lists',
		'choices'     => array(
			'excerpt' => __( 'A short excerpt with Read More', 'quickstrap' ),
			'full'    => __( 'The full post', 'quickstrap' ),
		),
	) );

	$wp_customize->add_setting( 'quickstrap_excerpt_length', array(
		'default'           => 55,
		'sanitize_callback' => 'quickstrap_sanitize_excerpt_length',
	) );
	$wp_customize->add_control( 'quickstrap_excerpt_length', array(
		'type'        => 'number',
		'label'       => __( 'Excerpt length (words)', 'quickstrap' ),
		'description' => __( 'Between 10 and 200. WordPress uses 55.', 'quickstrap' ),
		'section'     => 'quickstrap_post_lists',
		'input_attrs' => array( 'min' => 10, 'max' => 200, 'step' => 5 ),
	) );
}
add_action( 'customize_register', 'quickstrap_customize_post_lists' );

function quickstrap_sanitize_list_display( $value ) {
	return 'full' === $value ? 'full' : 'excerpt';
}

function quickstrap_sanitize_excerpt_length( $value ) {
	return max( 10, min( 200, absint( $value ) ) );
}

// Should this post list show full posts? Search results always use excerpts.
function quickstrap_show_full_posts() {
	return ! is_search() && 'full' === get_theme_mod( 'quickstrap_list_display', 'excerpt' );
}

// The excerpt length chosen in Customize > Post Lists.
function quickstrap_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return quickstrap_sanitize_excerpt_length( get_theme_mod( 'quickstrap_excerpt_length', 55 ) );
}
add_filter( 'excerpt_length', 'quickstrap_excerpt_length' );

// Post date, author, comments and categories line, used in lists and single posts
function quickstrap_post_meta( $show_comments = true ) {
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
			esc_html__( 'Updated', 'quickstrap' ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);
	}

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

/*
 * Between Posts widget area: after the third post in a list, only when it has
 * widgets and more posts follow. Change the position in a child theme:
 * add_filter( 'quickstrap_between_posts_after', fn() => 5 );
 */
function quickstrap_show_between_posts() {
	global $wp_query;
	if ( is_singular() || ! in_the_loop() || ! is_main_query() || ! is_active_sidebar( 'between-posts' ) ) {
		return false;
	}
	$after    = max( 1, (int) apply_filters( 'quickstrap_between_posts_after', 3 ) );
	$position = $wp_query->current_post + 1;
	return $position === $after && $position < $wp_query->post_count;
}

// Is this post or page using the Full width template?
function quickstrap_is_full_width() {
	return is_singular() && is_page_template( 'full-width.php' );
}

// Does this page show the left sidebar? When it has widgets, or while
// both sidebars are empty and it shows its default widgets.
function quickstrap_has_left_sidebar() {
	return ! quickstrap_is_full_width()
		&& ( is_active_sidebar( 'left-sidebar' ) || quickstrap_show_default_left() );
}

/*
 * Sidebar layout, from Appearance > Customize > Layout:
 * - narrow: narrow Left Sidebar, wider Right Sidebar (2 | 6 | 4), the default
 * - swap:   the two sidebars swap sides (4 | 6 | 2): the Right Sidebar's
 *           widgets on the left, the Left Sidebar's on the right. With the
 *           Left Sidebar empty, this is a two-column site with the sidebar on the left.
 * On tablets and phones every layout is the same: posts first, then the Right
 * Sidebar, then the Left Sidebar, so no widget is ever hidden.
 */
function quickstrap_layout() {
	$layout = get_theme_mod( 'quickstrap_layout', 'narrow' );
	return in_array( $layout, array( 'narrow', 'swap' ), true ) ? $layout : 'narrow';
}

/*
 * Bootstrap column classes for the three columns: 'content', 'right' or 'left'.
 * The order classes put the posts first in small screens and in the middle on wide ones.
 */
function quickstrap_column_class( $column ) {
	$layout = quickstrap_layout();
	$swap   = 'swap' === $layout;

	if ( 'content' === $column ) {
		if ( quickstrap_is_full_width() ) {
			return 'content col-12';
		}
		if ( quickstrap_has_left_sidebar() ) {
			return 'content col-md-8 col-lg-6 order-1' . ( $swap ? ' order-md-2' : ' order-lg-2' );
		}
		// No Left Sidebar: the posts take its space.
		return 'content col-md-8 order-1' . ( $swap ? ' order-md-2' : '' );
	}

	if ( 'right' === $column ) {
		$class = 'sidebar sidebar-right col-md-4 order-2';
		if ( $swap ) {
			return $class . ' order-md-1';
		}
		return $class . ' order-lg-3';
	}

	// The Left Sidebar: full width below the others until wide screens.
	if ( $swap ) {
		return 'sidebar sidebar-left col-12 col-lg-2 order-3';
	}
	return 'sidebar sidebar-left col-12 col-lg-2 order-3 order-lg-1';
}

// The posts column's classes (kept for child themes that call it).
function quickstrap_content_class() {
	return quickstrap_column_class( 'content' );
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

// Body classes: dark mode switched off, Full width template.
function quickstrap_body_classes( $classes ) {
	if ( ! get_theme_mod( 'quickstrap_dark_mode', true ) ) {
		$classes[] = 'qs-light-only';
	}
	if ( quickstrap_is_full_width() ) {
		$classes[] = 'qs-full-width';
	}
	return $classes;
}
add_filter( 'body_class', 'quickstrap_body_classes' );

/*
 * Customizer: Appearance > Customize > Colours
 * - Header colour: the top bar behind the site title and menu.
 * - Accent colour: links, buttons and page numbers.
 * - Dark mode: follow the reader's device setting (on by default) or stay light.
 */
function quickstrap_customize_register( $wp_customize ) {

	$wp_customize->add_setting( 'quickstrap_brand_color', array(
		'default'           => '#212529',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'quickstrap_brand_color', array(
		'label'       => __( 'Header colour', 'quickstrap' ),
		'description' => __( 'The top bar behind the site title and menu. Pick a dark colour so the white text stays readable on it.', 'quickstrap' ),
		'section'     => 'colors',
	) ) );

	$wp_customize->add_setting( 'quickstrap_accent_color', array(
		'default'           => '#0d6efd',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'quickstrap_accent_color', array(
		'label'       => __( 'Accent colour', 'quickstrap' ),
		'description' => __( 'Links, buttons and page numbers.', 'quickstrap' ),
		'section'     => 'colors',
	) ) );

	$wp_customize->add_setting( 'quickstrap_dark_mode', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'quickstrap_dark_mode', array(
		'type'        => 'checkbox',
		'label'       => __( 'Dark mode for readers who use it', 'quickstrap' ),
		'description' => __( 'Turns the site dark when the reader\'s phone or computer is set to dark. Untick to always stay light.', 'quickstrap' ),
		'section'     => 'colors',
	) );
}
add_action( 'customize_register', 'quickstrap_customize_register' );

// The theme's two colours: the saved one, or the default.
function quickstrap_colors() {
	$brand  = sanitize_hex_color( get_theme_mod( 'quickstrap_brand_color', '#212529' ) );
	$accent = sanitize_hex_color( get_theme_mod( 'quickstrap_accent_color', '#0d6efd' ) );
	return array(
		'brand'  => $brand ? $brand : '#212529',
		'accent' => $accent ? $accent : '#0d6efd',
	);
}

// The owner's colours as CSS, or '' when both are the defaults.
function quickstrap_colors_css() {
	$colors = quickstrap_colors();
	$css    = '';
	if ( '#212529' !== strtolower( $colors['brand'] ) ) {
		$css .= '--qs-brand:' . $colors['brand'] . ';';
	}
	if ( '#0d6efd' !== strtolower( $colors['accent'] ) ) {
		$css .= '--qs-accent:' . $colors['accent'] . ';';
	}
	return $css ? ':root{' . $css . '}' : '';
}

/*
 * Customizer: Appearance > Customize > Bootstrap Extras
 * The theme itself needs no JavaScript and no icon font. These two switches
 * load Bootstrap's JavaScript and Font Awesome for owners who use them in
 * their own posts and widgets (a dropdown, a modal, <i class="fa-solid fa-star"></i>).
 */
function quickstrap_customize_extras( $wp_customize ) {

	$wp_customize->add_section( 'quickstrap_extras', array(
		'title'       => __( 'Bootstrap Extras', 'quickstrap' ),
		'description' => __( 'QuickStrap needs neither of these. Switch one on only if your own posts or widgets use it; each adds a file for every visitor to download.', 'quickstrap' ),
		'priority'    => 46,
	) );

	$wp_customize->add_setting( 'quickstrap_bootstrap_js', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'quickstrap_bootstrap_js', array(
		'type'        => 'checkbox',
		'label'       => __( 'Load Bootstrap\'s JavaScript', 'quickstrap' ),
		'description' => __( 'For Bootstrap dropdowns, modals, carousels, tooltips and collapses in your posts. About 80 KB.', 'quickstrap' ),
		'section'     => 'quickstrap_extras',
	) );

	$wp_customize->add_setting( 'quickstrap_font_awesome', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'quickstrap_font_awesome', array(
		'type'        => 'checkbox',
		'label'       => __( 'Load Font Awesome icons', 'quickstrap' ),
		'description' => __( 'For Font Awesome icons you add to posts or widgets. About 90 KB, plus the icon fonts.', 'quickstrap' ),
		'section'     => 'quickstrap_extras',
	) );
}
add_action( 'customize_register', 'quickstrap_customize_extras' );

// Load styles and scripts
function quickstrap_scripts() {

	// Theme version plus the file's last change time, so browsers fetch fresh CSS
	// after every update, even when the version number stays the same.
	$version = wp_get_theme( get_template() )->get( 'Version' ) . '.' . filemtime( get_template_directory() . '/style.css' );

	// Bootstrap first, then the theme's own small stylesheet on top of it.
	wp_enqueue_style( 'quickstrap-bootstrap', get_template_directory_uri() . '/vendor/bootstrap/bootstrap.min.css', array(), QUICKSTRAP_BOOTSTRAP_VERSION );
	wp_enqueue_style( 'quickstrap', get_stylesheet_uri(), array( 'quickstrap-bootstrap' ), $version );

	// The owner's colours, if they picked any (a few bytes, no extra file).
	$colors = quickstrap_colors_css();
	if ( $colors ) {
		wp_add_inline_style( 'quickstrap', $colors );
	}

	// Extras for owners who use them in their own posts and widgets,
	// switched on in Customize > Bootstrap Extras. The theme itself needs neither.
	if ( get_theme_mod( 'quickstrap_font_awesome', false ) ) {
		wp_enqueue_style( 'quickstrap-fontawesome', get_template_directory_uri() . '/vendor/font-awesome/css/all.min.css', array(), QUICKSTRAP_FONTAWESOME_VERSION );
	}
	if ( get_theme_mod( 'quickstrap_bootstrap_js', false ) ) {
		wp_enqueue_script( 'quickstrap-bootstrap', get_template_directory_uri() . '/vendor/bootstrap/bootstrap.bundle.min.js', array(), QUICKSTRAP_BOOTSTRAP_VERSION, true );
	}

	// WordPress's own script for threaded comment replies, only where needed.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quickstrap_scripts' );
