<?php
/**
 * Plugin Name:       Quick Code Inserter
 * Plugin URI:        https://www.quickonlinetips.com/archives/quick-code-inserter-wordpress-plugin/
 * Description:       Paste any code (analytics, ads, banners, forms) and pick where it goes: the page head, before or after posts, after a paragraph, the sidebar, the footer and more. A live map shows each spot. Tiny, no PHP to break your site.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       quick-code-inserter
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QUICK_CODE_INSERTER_OPTION', 'quick_code_inserter' );
define( 'QUICK_CODE_INSERTER_MAX_SLOTS', 30 );

/* ------------------------------------------------------------------ *
 * Places, page choices and settings
 * ------------------------------------------------------------------ */

/**
 * Every place code can go, in the order they appear on a page.
 *
 * Each place has a label, the group it is listed under, a one-line note,
 * and which kinds of theme it works with: 'all', 'classic' or 'block'.
 *
 * @return array<string, array{label: string, group: string, note: string, themes: string}>
 */
function quick_code_inserter_locations() {
	$top     = __( 'Top of the page', 'quick-code-inserter' );
	$post    = __( 'In the post', 'quick-code-inserter' );
	$side    = __( 'Comments and sidebar', 'quick-code-inserter' );
	$bottom  = __( 'Bottom of the page', 'quick-code-inserter' );

	return array(
		'head'            => array(
			'label'  => __( 'In the page head (hidden)', 'quick-code-inserter' ),
			'group'  => $top,
			'note'   => __( 'For Google Analytics, site verification tags and anything that asks to go in the <head>. Visitors don\'t see it.', 'quick-code-inserter' ),
			'themes' => 'all',
		),
		'body_open'       => array(
			'label'  => __( 'Right after the <body> tag', 'quick-code-inserter' ),
			'group'  => $top,
			'note'   => __( 'For Google Tag Manager\'s second part and similar codes that must come first in the page.', 'quick-code-inserter' ),
			'themes' => 'all',
		),
		'after_header'    => array(
			'label'  => __( 'Below the site header', 'quick-code-inserter' ),
			'group'  => $top,
			'note'   => __( 'For a notice bar or a banner across the top of every page.', 'quick-code-inserter' ),
			'themes' => 'block',
		),
		'before_content'  => array(
			'label'  => __( 'Before the post', 'quick-code-inserter' ),
			'group'  => $post,
			'note'   => __( 'Shows above the text of each post or page, below its title.', 'quick-code-inserter' ),
			'themes' => 'all',
		),
		'after_paragraph' => array(
			'label'  => __( 'After a paragraph of the post', 'quick-code-inserter' ),
			'group'  => $post,
			'note'   => __( 'For an ad or a sign-up box inside the post, where readers are. Shorter posts get it after their last paragraph.', 'quick-code-inserter' ),
			'themes' => 'all',
		),
		'after_content'   => array(
			'label'  => __( 'After the post', 'quick-code-inserter' ),
			'group'  => $post,
			'note'   => __( 'For share buttons, an author box or a newsletter form at the end of each post.', 'quick-code-inserter' ),
			'themes' => 'all',
		),
		'before_comments' => array(
			'label'  => __( 'Above the comment form', 'quick-code-inserter' ),
			'group'  => $side,
			'note'   => __( 'For comment rules or a short message before people reply.', 'quick-code-inserter' ),
			'themes' => 'all',
		),
		'after_comments'  => array(
			'label'  => __( 'Below the comment form', 'quick-code-inserter' ),
			'group'  => $side,
			'note'   => __( 'For related links or an ad after the comments.', 'quick-code-inserter' ),
			'themes' => 'all',
		),
		'sidebar_top'     => array(
			'label'  => __( 'Top of the sidebar', 'quick-code-inserter' ),
			'group'  => $side,
			'note'   => __( 'Above the widgets in the first widget area on the page, usually the main sidebar.', 'quick-code-inserter' ),
			'themes' => 'classic',
		),
		'sidebar_bottom'  => array(
			'label'  => __( 'Bottom of the sidebar', 'quick-code-inserter' ),
			'group'  => $side,
			'note'   => __( 'Below the widgets in the first widget area on the page, usually the main sidebar.', 'quick-code-inserter' ),
			'themes' => 'classic',
		),
		'before_footer'   => array(
			'label'  => __( 'Above the site footer', 'quick-code-inserter' ),
			'group'  => $bottom,
			'note'   => __( 'For a call to action or a banner at the end of every page.', 'quick-code-inserter' ),
			'themes' => 'all',
		),
		'footer'          => array(
			'label'  => __( 'Very end of the page, before </body> (hidden)', 'quick-code-inserter' ),
			'group'  => $bottom,
			'note'   => __( 'For chat widgets, tracking pixels and scripts that should load last, so the page shows first.', 'quick-code-inserter' ),
			'themes' => 'all',
		),
	);
}

/**
 * Does a place work with the active theme?
 *
 * @param string $key Place key.
 * @return bool
 */
function quick_code_inserter_location_works( $key ) {
	$locations = quick_code_inserter_locations();
	if ( ! isset( $locations[ $key ] ) ) {
		return false;
	}
	$themes = $locations[ $key ]['themes'];
	if ( 'all' === $themes ) {
		return true;
	}
	$block = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	return 'block' === $themes ? $block : ! $block;
}

/**
 * Which pages a code shows on.
 *
 * @return array<string, string>
 */
function quick_code_inserter_show_choices() {
	return array(
		'all'      => __( 'Every page', 'quick-code-inserter' ),
		'singular' => __( 'Posts and pages only', 'quick-code-inserter' ),
		'posts'    => __( 'Posts only', 'quick-code-inserter' ),
		'pages'    => __( 'Pages only', 'quick-code-inserter' ),
		'home'     => __( 'Homepage only', 'quick-code-inserter' ),
	);
}

/**
 * A new, empty code slot.
 *
 * @return array{on: bool, name: string, location: string, paragraph: int, show: string, guests: bool, code: string}
 */
function quick_code_inserter_blank_slot() {
	return array(
		'on'        => true,
		'name'      => '',
		'location'  => 'after_content',
		'paragraph' => 2,
		'show'      => 'all',
		'guests'    => false,
		'code'      => '',
	);
}

/**
 * Check one code slot, from the saved option or the form.
 * The code itself is checked separately when saving.
 *
 * @param mixed $slot Saved or posted slot.
 * @return array
 */
function quick_code_inserter_clean_slot( $slot ) {
	$slot  = is_array( $slot ) ? $slot : array();
	$blank = quick_code_inserter_blank_slot();

	$location = isset( $slot['location'] ) ? (string) $slot['location'] : $blank['location'];
	$show     = isset( $slot['show'] ) ? (string) $slot['show'] : $blank['show'];

	return array(
		'on'        => ! empty( $slot['on'] ),
		'name'      => isset( $slot['name'] ) ? sanitize_text_field( $slot['name'] ) : '',
		'location'  => array_key_exists( $location, quick_code_inserter_locations() ) ? $location : $blank['location'],
		'paragraph' => isset( $slot['paragraph'] ) ? min( 50, max( 1, absint( $slot['paragraph'] ) ) ) : $blank['paragraph'],
		'show'      => array_key_exists( $show, quick_code_inserter_show_choices() ) ? $show : $blank['show'],
		'guests'    => ! empty( $slot['guests'] ),
		'code'      => isset( $slot['code'] ) ? (string) $slot['code'] : '',
	);
}

/**
 * The saved settings, with defaults for anything not saved yet.
 *
 * @return array{enabled: bool, slots: array<int, array>}
 */
function quick_code_inserter_settings() {
	$saved = get_option( QUICK_CODE_INSERTER_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();
	$slots = array();

	if ( isset( $saved['slots'] ) && is_array( $saved['slots'] ) ) {
		foreach ( $saved['slots'] as $slot ) {
			$slots[] = quick_code_inserter_clean_slot( $slot );
		}
	}

	return array(
		'enabled' => isset( $saved['enabled'] ) ? (bool) $saved['enabled'] : true,
		'slots'   => $slots,
	);
}

/**
 * Check everything sent from the settings page before it is saved.
 *
 * Code is kept exactly as typed for people allowed to add scripts
 * (the unfiltered_html permission, which site admins have on a normal site).
 * For everyone else, such as admins on a multisite network, only the HTML
 * WordPress allows in posts is kept. Slots with no code are dropped.
 *
 * @param mixed $input Values from the settings form.
 * @return array
 */
function quick_code_inserter_sanitize( $input ) {
	$input = is_array( $input ) ? $input : array();
	$slots = array();

	if ( isset( $input['slots'] ) && is_array( $input['slots'] ) ) {
		foreach ( $input['slots'] as $raw ) {
			$slot = quick_code_inserter_clean_slot( $raw );

			if ( ! current_user_can( 'unfiltered_html' ) ) {
				$slot['code'] = wp_kses_post( $slot['code'] );
			}
			$slot['code'] = rtrim( str_replace( "\r\n", "\n", $slot['code'] ) );

			if ( '' === trim( $slot['code'] ) ) {
				continue;
			}

			$slots[] = $slot;
			if ( count( $slots ) >= QUICK_CODE_INSERTER_MAX_SLOTS ) {
				break;
			}
		}
	}

	return array(
		'enabled' => ! empty( $input['enabled'] ),
		'slots'   => $slots,
	);
}

/* ------------------------------------------------------------------ *
 * Front end: put each code in its place
 * ------------------------------------------------------------------ */

/**
 * Does a slot's page choice match the page being shown?
 *
 * @param string $show Page choice key.
 * @return bool
 */
function quick_code_inserter_show_matches( $show ) {
	switch ( $show ) {
		case 'singular':
			return is_singular();
		case 'posts':
			return is_singular( 'post' );
		case 'pages':
			return is_page();
		case 'home':
			return is_front_page();
		default:
			return true;
	}
}

/**
 * The codes that go on the current page, grouped by place.
 * Worked out once, after WordPress knows which page it is showing.
 *
 * @return array<string, array<int, array>>
 */
function quick_code_inserter_page_slots() {
	static $by_place = null;

	if ( null !== $by_place ) {
		return $by_place;
	}

	$by_place = array();

	if ( is_admin() || is_feed() || is_embed() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $by_place;
	}

	// Safe mode: an admin adds ?qci-off to any address to see the page without any inserted code.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only switches output off for this one view.
	if ( isset( $_GET['qci-off'] ) && current_user_can( 'manage_options' ) ) {
		return $by_place;
	}

	$settings = quick_code_inserter_settings();
	if ( ! $settings['enabled'] ) {
		return $by_place;
	}

	foreach ( $settings['slots'] as $slot ) {
		if ( ! $slot['on'] || '' === trim( $slot['code'] ) ) {
			continue;
		}
		if ( $slot['guests'] && is_user_logged_in() ) {
			continue;
		}
		if ( ! quick_code_inserter_show_matches( $slot['show'] ) ) {
			continue;
		}
		$by_place[ $slot['location'] ][] = $slot;
	}

	return $by_place;
}

/**
 * All the code for one place, joined together.
 * Shortcodes work in every place except the hidden page head.
 *
 * @param string $place Place key.
 * @return string
 */
function quick_code_inserter_code_for( $place ) {
	$by_place = quick_code_inserter_page_slots();

	if ( empty( $by_place[ $place ] ) ) {
		return '';
	}

	$html = '';
	foreach ( $by_place[ $place ] as $slot ) {
		$html .= "\n" . $slot['code'] . "\n";
	}

	return 'head' === $place ? $html : do_shortcode( $html );
}

/**
 * Print the code for one place.
 *
 * @param string $place Place key.
 */
function quick_code_inserter_print( $place ) {
	// The code is the site owner's own, saved by someone allowed to add it, so it is printed as is.
	echo quick_code_inserter_code_for( $place ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Once WordPress knows which page it is showing, hook in only the places
 * that have code for this page. Pages with no code get nothing extra.
 */
function quick_code_inserter_setup() {
	$by_place = quick_code_inserter_page_slots();

	if ( empty( $by_place ) ) {
		return;
	}

	$simple = array(
		'head'            => array( 'wp_head', 10 ),
		'body_open'       => array( 'wp_body_open', 1 ),
		'before_comments' => array( 'comment_form_before', 10 ),
		'after_comments'  => array( 'comment_form_after', 10 ),
		'footer'          => array( 'wp_footer', 99 ),
	);

	foreach ( $simple as $place => $hook ) {
		if ( ! empty( $by_place[ $place ] ) ) {
			add_action(
				$hook[0],
				function () use ( $place ) {
					quick_code_inserter_print( $place );
				},
				$hook[1]
			);
		}
	}

	if ( ! empty( $by_place['before_content'] ) || ! empty( $by_place['after_paragraph'] ) || ! empty( $by_place['after_content'] ) ) {
		// After WordPress has made paragraphs and run shortcodes (priority 11), so paragraphs can be counted.
		add_filter( 'the_content', 'quick_code_inserter_content', 20 );
	}

	if ( ! empty( $by_place['sidebar_top'] ) || ! empty( $by_place['sidebar_bottom'] ) ) {
		add_action( 'dynamic_sidebar_before', 'quick_code_inserter_sidebar_top', 10, 2 );
		add_action( 'dynamic_sidebar_after', 'quick_code_inserter_sidebar_bottom', 10, 2 );
	}

	if ( ! empty( $by_place['after_header'] ) || ! empty( $by_place['before_footer'] ) ) {
		// Block themes: their header and footer are template parts.
		add_filter( 'render_block_core/template-part', 'quick_code_inserter_template_part', 10, 2 );
	}

	if ( ! empty( $by_place['before_footer'] ) ) {
		// Classic themes: fires just before the theme's footer.php.
		add_action( 'get_footer', 'quick_code_inserter_classic_footer' );
	}
}
add_action( 'template_redirect', 'quick_code_inserter_setup' );

/**
 * Add code before, inside and after the main post text.
 *
 * Only the post or posts the page is about, never widgets, menus or
 * summaries that other plugins build from the text.
 *
 * @param string $content Post content.
 * @return string
 */
function quick_code_inserter_content( $content ) {
	if ( ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$by_place = quick_code_inserter_page_slots();
	$before   = quick_code_inserter_code_for( 'before_content' );
	$after    = quick_code_inserter_code_for( 'after_content' );

	if ( ! empty( $by_place['after_paragraph'] ) ) {
		$parts = explode( '</p>', $content );
		$count = count( $parts ) - 1; // Number of paragraphs.

		if ( 0 === $count ) {
			// No paragraphs at all: put the code at the end of the post instead.
			foreach ( $by_place['after_paragraph'] as $slot ) {
				$after = do_shortcode( "\n" . $slot['code'] . "\n" ) . $after;
			}
		} else {
			$inserts = array();
			foreach ( $by_place['after_paragraph'] as $slot ) {
				$n               = min( $slot['paragraph'], $count );
				$inserts[ $n ]   = ( isset( $inserts[ $n ] ) ? $inserts[ $n ] : '' ) . "\n" . $slot['code'] . "\n";
			}

			$content = '';
			foreach ( $parts as $i => $part ) {
				$content .= $part;
				if ( $i < $count ) {
					$content .= '</p>';
					if ( isset( $inserts[ $i + 1 ] ) ) {
						$content .= do_shortcode( $inserts[ $i + 1 ] );
					}
				}
			}
		}
	}

	return $before . $content . $after;
}

/**
 * The first widget area on the page that has widgets, usually the main sidebar.
 * Sidebar code goes only there, not into every footer widget area too.
 *
 * @param string|int|null $index Widget area to remember, or null to read it.
 * @return string|int|null
 */
function quick_code_inserter_first_sidebar( $index = null ) {
	static $first = null;
	if ( null !== $index && null === $first ) {
		$first = $index;
	}
	return $first;
}

/**
 * Code at the top of the first widget area.
 *
 * @param string|int $index       Widget area ID.
 * @param bool       $has_widgets Whether it has widgets.
 */
function quick_code_inserter_sidebar_top( $index, $has_widgets ) {
	if ( ! $has_widgets || null !== quick_code_inserter_first_sidebar() ) {
		return;
	}
	quick_code_inserter_first_sidebar( $index );
	quick_code_inserter_print( 'sidebar_top' );
}

/**
 * Code at the bottom of the first widget area.
 *
 * @param string|int $index       Widget area ID.
 * @param bool       $has_widgets Whether it has widgets.
 */
function quick_code_inserter_sidebar_bottom( $index, $has_widgets ) {
	static $done = false;
	if ( $done || ! $has_widgets || quick_code_inserter_first_sidebar() !== $index ) {
		return;
	}
	$done = true;
	quick_code_inserter_print( 'sidebar_bottom' );
}

/**
 * Block themes: code below the header template part and above the footer one.
 * Each goes in once, even if a theme shows a header or footer twice.
 *
 * @param string $html  The template part's HTML.
 * @param array  $block The block, with its settings.
 * @return string
 */
function quick_code_inserter_template_part( $html, $block ) {
	static $done = array();

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$slug  = isset( $attrs['slug'] ) ? $attrs['slug'] : '';
	$tag   = isset( $attrs['tagName'] ) ? $attrs['tagName'] : '';

	if ( ( 'header' === $slug || 'header' === $tag ) && empty( $done['header'] ) ) {
		$done['header'] = true;
		$html          .= quick_code_inserter_code_for( 'after_header' );
	} elseif ( ( 'footer' === $slug || 'footer' === $tag ) && empty( $done['footer'] ) ) {
		$done['footer'] = true;
		$html           = quick_code_inserter_code_for( 'before_footer' ) . $html;
	}

	return $html;
}

/**
 * Classic themes: code just before the footer.
 */
function quick_code_inserter_classic_footer() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	quick_code_inserter_print( 'before_footer' );
}

/* ------------------------------------------------------------------ *
 * Settings page: Settings > Quick Code Inserter
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds all settings.
 */
function quick_code_inserter_register_setting() {
	register_setting(
		'quick_code_inserter',
		QUICK_CODE_INSERTER_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'quick_code_inserter_sanitize',
		)
	);
}
add_action( 'admin_init', 'quick_code_inserter_register_setting' );

/**
 * Add the page to the Settings menu.
 */
function quick_code_inserter_add_page() {
	$hook = add_options_page(
		__( 'Quick Code Inserter', 'quick-code-inserter' ),
		__( 'Quick Code Inserter', 'quick-code-inserter' ),
		'manage_options',
		'quick-code-inserter',
		'quick_code_inserter_render_page'
	);

	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				quick_code_inserter_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'quick_code_inserter_add_page' );

/**
 * The page's styles and live preview script, inline, on this page only.
 */
function quick_code_inserter_admin_assets() {
	wp_register_style( 'quick-code-inserter-admin', false, array(), '1.0' );
	wp_enqueue_style( 'quick-code-inserter-admin' );
	wp_add_inline_style(
		'quick-code-inserter-admin',
		// Wrap in reverse: side by side, the preview sits on the right; when the screen is too narrow
		// for both, it moves above the settings instead of below. (flex-end keeps both at the top.)
		'.qci-wrap{display:flex;flex-wrap:wrap-reverse;gap:32px;align-items:flex-end}' .
		'.qci-wrap form{max-width:640px;flex:1 1 480px}' .
		// Each section is a white card with its heading in a bar across the top.
		'.qci-card{box-sizing:border-box;background:#fff;border:1px solid #c3c4c7;border-radius:4px;box-shadow:0 1px 1px rgba(0,0,0,.04);margin:0 0 20px;padding:0 20px 6px}' .
		'.qci-card>h2{margin:0 -20px;padding:12px 20px;border-bottom:1px solid #dcdcde;background:#f0f6fc;border-radius:4px 4px 0 0;font-size:15px}' .
		// Heading colours change from card to card in a fixed order: blue, green, yellow, pink, purple,
		// then round again (qci-c1 to qci-c5, in page order). The preview card is always teal.
		'.qci-c1>h2{background:#f0f6fc}.qci-c2>h2{background:#edfaef}.qci-c3>h2{background:#fcf9e8}' .
		'.qci-c4>h2{background:#fcf0f1}.qci-c5>h2{background:#f5f0fc}.qci-preview>h2{background:#e8f6f5}' .
		// The preview is a live window, not a settings card: a teal frame with a faint dotted
		// background, an eye icon and a Live badge, with the preview itself sitting on top like a screen.
		'.qci-preview{background:#1d6b65 radial-gradient(rgba(255,255,255,.12) 1px,transparent 1.5px) 0 0/16px 16px;border-color:#11433f;box-shadow:0 2px 8px rgba(0,0,0,.18)}' .
		'.qci-preview>h2{display:flex;align-items:center;gap:8px;background:#155550;color:#f0fdfa;border-bottom-color:#2a8a82}' .
		'.qci-preview>h2:before{content:"\f177";font:normal 20px/1 dashicons;color:#99f6e4}' .
		'.qci-live{margin-left:auto;position:relative;padding:2px 8px 2px 18px;border:1px solid #99f6e4;border-radius:10px;background:rgba(153,246,228,.14);color:#ccfbf1;font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase}' .
		'.qci-live:before{content:"";position:absolute;left:7px;top:50%;width:6px;height:6px;margin-top:-3px;border-radius:50%;background:#99f6e4}' .
		'.qci-preview>.description{color:#e6f7f5}.qci-card.qci-preview>.description{margin:12px 0 0}' .
		'.qci-card .form-table{margin-top:4px}.qci-card .form-table th{padding-left:0;width:150px}' .
		'.qci-card .qci-intro{margin:14px 0 0;color:#50575e}.qci-card .description{margin:6px 0 0}' .
		'.qci-side.qci-card{padding-bottom:20px;margin-bottom:0;flex:1 1 400px;max-width:640px}' .
		// Keep the preview in view while editing: beside the settings on wide screens, above them otherwise.
		'.qci-side{position:sticky;top:46px}@media (max-width:1139px){.qci-wrap{gap:20px}.qci-side{position:static}}' .
		// Code slots.
		'.qci-slot>h2{display:flex;align-items:center;gap:6px}.qci-slot .qci-hname{font-weight:400;color:#50575e;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}' .
		'.qci-slot .qci-state{margin-left:auto;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;color:#50575e;background:#fff;border:1px solid #c3c4c7;border-radius:10px;padding:1px 8px}' .
		'.qci-slot.is-off .qci-state{color:#b32d2e;border-color:#f0b8b8}' .
		'.qci-slot textarea{width:100%;min-height:140px;font:13px/1.5 Consolas,Monaco,monospace;tab-size:4}' .
		'.qci-remove{color:#b32d2e;text-decoration:none;border:0;background:none;padding:0;cursor:pointer;font-size:13px}.qci-remove:hover{color:#8a2424;text-decoration:underline}' .
		'.qci-foot{display:flex;justify-content:flex-end;padding:4px 0 12px}' .
		'.qci-add{margin:0 0 20px !important}.qci-add .dashicons{vertical-align:text-bottom;margin-right:2px}' .
		'.qci-notice{margin:14px 0 4px;padding:8px 12px;border-left:4px solid #dba617;background:#fcf9e8}' .
		'.qci-badge{font-size:12px;color:#996800}' .
		// The page map inside the preview.
		'#qci-map{margin-top:20px;background:#fff;border:1px solid #dcdcde;border-radius:6px;box-shadow:0 6px 18px rgba(0,0,0,.18);padding:12px;font-size:12px;line-height:1.4;color:#1d2327}' .
		'#qci-map .m-zone{border:1px dashed #a7aaad;border-radius:4px;padding:6px 8px;color:#50575e;margin:0 0 8px}' .
		'#qci-map .m-zone code{background:none;padding:0;font-size:11px}' .
		'#qci-map .m-bar{background:#f0f0f1;border-radius:4px;padding:10px;font-weight:600;color:#50575e;margin:0 0 8px}' .
		'#qci-map .m-cols{display:flex;gap:10px;margin:0 0 8px}#qci-map .m-main{flex:1 1 auto;min-width:0}#qci-map .m-side{flex:0 0 32%;background:#f6f7f7;border-radius:4px;padding:8px}' .
		'#qci-map .m-title{height:12px;width:60%;background:#c3c4c7;border-radius:3px;margin:2px 0 10px}' .
		'#qci-map .m-p{position:relative;margin:0 0 8px;padding-left:22px}#qci-map .m-p:before{content:attr(data-n);position:absolute;left:0;top:-1px;font-size:10px;color:#8c8f94}' .
		'#qci-map .m-p i{display:block;height:6px;background:#dcdcde;border-radius:3px;margin:0 0 5px}#qci-map .m-p i:last-child{width:70%}' .
		'#qci-map .m-w{height:30px;background:#e8e9ea;border-radius:3px;margin:0 0 6px}' .
		'#qci-map .m-form{border:1px solid #dcdcde;border-radius:4px;padding:8px;color:#8c8f94;margin:8px 0}' .
		'#qci-map .m-label{display:block;font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#8c8f94;margin:0 0 4px}' .
		'#qci-map .m-na{opacity:.5}' .
		'.qci-chip{display:block;margin:0 0 8px;padding:5px 8px;border:1px solid;border-radius:4px;font-weight:600;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}' .
		'.qci-chip small{font-weight:400;color:#50575e}.qci-chip.k1{background:#f0f6fc;border-color:#72aee6}.qci-chip.k2{background:#edfaef;border-color:#68de7c}' .
		'.qci-chip.k3{background:#fcf9e8;border-color:#e0bf3f}.qci-chip.k4{background:#fcf0f1;border-color:#f86368}.qci-chip.k5{background:#f5f0fc;border-color:#b48ae0}' .
		'.qci-chip.is-off{opacity:.45;border-style:dashed}' .
		'@media (max-width:600px){#qci-map .m-cols{flex-direction:column}#qci-map .m-side{flex-basis:auto}}'
	);

	$locations = array();
	foreach ( quick_code_inserter_locations() as $key => $location ) {
		$locations[ $key ] = array(
			'note'  => $location['note'],
			'works' => quick_code_inserter_location_works( $key ),
		);
	}

	$data = array(
		'locations' => $locations,
		'shows'     => quick_code_inserter_show_choices(),
		'text'      => array(
			'code'       => __( 'Code', 'quick-code-inserter' ),
			'on'         => __( 'On', 'quick-code-inserter' ),
			'off'        => __( 'Off', 'quick-code-inserter' ),
			'empty'      => __( 'empty', 'quick-code-inserter' ),
			'loggedOut'  => __( 'not for logged-in users', 'quick-code-inserter' ),
			/* translators: %d: paragraph number. */
			'afterPara'  => __( 'after paragraph %d', 'quick-code-inserter' ),
			'confirm'    => __( 'Remove this code? It is deleted when you save.', 'quick-code-inserter' ),
			'notInTheme' => __( 'not shown in your theme', 'quick-code-inserter' ),
			'header'     => __( 'Site header', 'quick-code-inserter' ),
			'comments'   => __( 'Comment form', 'quick-code-inserter' ),
			'sidebar'    => __( 'Sidebar', 'quick-code-inserter' ),
			'footer'     => __( 'Site footer', 'quick-code-inserter' ),
		),
	);

	wp_register_script( 'quick-code-inserter-admin', false, array(), '1.0', true );
	wp_enqueue_script( 'quick-code-inserter-admin' );
	wp_add_inline_script( 'quick-code-inserter-admin', 'window.qciData = ' . wp_json_encode( $data ) . ';' );
	wp_add_inline_script( 'quick-code-inserter-admin', quick_code_inserter_admin_script() );
}

/**
 * The settings page script: add and remove codes, show the paragraph
 * box only when needed, and draw the page map in the preview.
 *
 * @return string
 */
function quick_code_inserter_admin_script() {
	return <<<'JS'
( function () {
	var data = window.qciData;
	var form = document.getElementById( 'qci-form' );
	var list = document.getElementById( 'qci-slots' );
	var tpl = document.getElementById( 'qci-slot-template' );
	var map = document.getElementById( 'qci-map' );
	if ( ! form || ! list || ! map ) {
		return;
	}
	var next = list.querySelectorAll( '.qci-slot' ).length;

	function esc( text ) {
		return String( text ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	function field( slot, name ) {
		return slot.querySelector( '[data-field="' + name + '"]' );
	}

	// Number the codes and colour their cards in page order. The first card on the
	// page is "All your code", so the codes start at the second colour.
	function renumber() {
		list.querySelectorAll( '.qci-slot' ).forEach( function ( slot, i ) {
			for ( var c = 1; c <= 5; c++ ) {
				slot.classList.remove( 'qci-c' + c );
			}
			slot.classList.add( 'qci-c' + ( ( i + 1 ) % 5 + 1 ) );
			slot.querySelector( '.qci-num' ).textContent = data.text.code + ' ' + ( i + 1 );
		} );
	}

	function updateSlot( slot ) {
		var loc = field( slot, 'location' ).value;
		var on = field( slot, 'on' ).checked;
		var name = field( slot, 'name' ).value.trim();
		slot.querySelector( '.qci-para-row' ).style.display = loc === 'after_paragraph' ? '' : 'none';
		slot.querySelector( '.qci-loc-note' ).textContent = data.locations[ loc ] ? data.locations[ loc ].note : '';
		slot.querySelector( '.qci-hname' ).textContent = name ? '· ' + name : '';
		slot.querySelector( '.qci-state' ).textContent = on ? data.text.on : data.text.off;
		slot.classList.toggle( 'is-off', ! on );
	}

	function chip( s ) {
		var extra = [];
		if ( s.loc === 'after_paragraph' ) {
			extra.push( data.text.afterPara.replace( '%d', s.para ) );
		}
		if ( s.show !== 'all' ) {
			extra.push( data.shows[ s.show ].toLowerCase() );
		}
		if ( s.guests ) {
			extra.push( data.text.loggedOut );
		}
		if ( s.empty ) {
			extra.push( data.text.empty );
		}
		if ( ! data.locations[ s.loc ].works ) {
			extra.push( data.text.notInTheme );
		}
		var off = ! s.on || s.empty || ! masterOn() || ! data.locations[ s.loc ].works;
		return '<span class="qci-chip k' + s.colour + ( off ? ' is-off' : '' ) + '">' +
			esc( data.text.code + ' ' + s.num + ( s.name ? ': ' + s.name : '' ) ) +
			( extra.length ? ' <small>(' + esc( extra.join( ', ' ) ) + ')</small>' : '' ) + '</span>';
	}

	function masterOn() {
		var master = document.getElementById( 'qci-enabled' );
		return ! master || master.checked;
	}

	function drawMap() {
		var at = {};
		list.querySelectorAll( '.qci-slot' ).forEach( function ( slot, i ) {
			var s = {
				num: i + 1,
				colour: ( i + 1 ) % 5 + 1,
				name: field( slot, 'name' ).value.trim(),
				loc: field( slot, 'location' ).value,
				para: parseInt( field( slot, 'paragraph' ).value, 10 ) || 1,
				show: field( slot, 'show' ).value,
				guests: field( slot, 'guests' ).checked,
				on: field( slot, 'on' ).checked,
				empty: field( slot, 'code' ).value.trim() === ''
			};
			var key = s.loc === 'after_paragraph' ? 'p' + Math.min( Math.max( s.para, 1 ), 4 ) : s.loc;
			( at[ key ] = at[ key ] || [] ).push( chip( s ) );
		} );

		function put( key ) {
			return ( at[ key ] || [] ).join( '' );
		}
		function para( n ) {
			return '<div class="m-p" data-n="' + n + '"><i></i><i></i></div>' + put( 'p' + n );
		}
		function na( key ) {
			return data.locations[ key ].works ? '' : ' m-na';
		}

		map.innerHTML =
			'<div class="m-zone"><span class="m-label"><code>&lt;head&gt;</code></span>' + put( 'head' ) + '</div>' +
			put( 'body_open' ) +
			'<div class="m-bar">' + esc( data.text.header ) + '</div>' +
			put( 'after_header' ) +
			'<div class="m-cols"><div class="m-main"><div class="m-title"></div>' +
				put( 'before_content' ) + para( 1 ) + para( 2 ) + para( 3 ) + para( 4 ) + put( 'after_content' ) +
				put( 'before_comments' ) + '<div class="m-form">' + esc( data.text.comments ) + '</div>' + put( 'after_comments' ) +
			'</div><div class="m-side' + na( 'sidebar_top' ) + '"><span class="m-label">' + esc( data.text.sidebar ) + '</span>' +
				put( 'sidebar_top' ) + '<div class="m-w"></div><div class="m-w"></div>' + put( 'sidebar_bottom' ) +
			'</div></div>' +
			put( 'before_footer' ) +
			'<div class="m-bar">' + esc( data.text.footer ) + '</div>' +
			'<div class="m-zone"><span class="m-label"><code>&lt;/body&gt;</code></span>' + put( 'footer' ) + '</div>';
	}

	function updateAll() {
		list.querySelectorAll( '.qci-slot' ).forEach( updateSlot );
		drawMap();
	}

	document.getElementById( 'qci-add' ).addEventListener( 'click', function () {
		var html = tpl.innerHTML.replace( /__i__/g, String( next++ ) );
		list.insertAdjacentHTML( 'beforeend', html );
		renumber();
		updateAll();
		var added = list.lastElementChild;
		field( added, 'code' ).focus();
	} );

	list.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.qci-remove' );
		if ( ! button ) {
			return;
		}
		var slot = button.closest( '.qci-slot' );
		if ( field( slot, 'code' ).value.trim() !== '' && ! window.confirm( data.text.confirm ) ) {
			return;
		}
		slot.remove();
		renumber();
		updateAll();
	} );

	form.addEventListener( 'input', updateAll );
	form.addEventListener( 'change', updateAll );
	map.addEventListener( 'click', function ( event ) {
		event.preventDefault();
	} );

	renumber();
	updateAll();
} )();
JS;
}

/**
 * One code card on the settings page.
 *
 * @param string|int $index    Field index, or __i__ in the template for new cards.
 * @param array      $slot     The slot's settings.
 * @param int        $position Its number on the page, starting at 1.
 */
function quick_code_inserter_render_slot( $index, $slot, $position ) {
	$name      = QUICK_CODE_INSERTER_OPTION . '[slots][' . $index . ']';
	$id        = 'qci-' . $index;
	$locations = quick_code_inserter_locations();
	$colour    = $position % 5 + 1;
	$groups    = array();

	foreach ( $locations as $key => $location ) {
		$groups[ $location['group'] ][ $key ] = $location;
	}
	?>
	<div class="qci-card qci-slot qci-c<?php echo esc_attr( (string) $colour ); ?><?php echo $slot['on'] ? '' : ' is-off'; ?>">
		<h2>
			<span class="qci-num">
				<?php
				/* translators: %d: number of the code on the settings page. */
				echo esc_html( sprintf( __( 'Code %d', 'quick-code-inserter' ), $position ) );
				?>
			</span>
			<span class="qci-hname"><?php echo $slot['name'] ? esc_html( '· ' . $slot['name'] ) : ''; ?></span>
			<span class="qci-state"><?php echo $slot['on'] ? esc_html__( 'On', 'quick-code-inserter' ) : esc_html__( 'Off', 'quick-code-inserter' ); ?></span>
		</h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $id . '-code' ); ?>"><?php esc_html_e( 'Your code', 'quick-code-inserter' ); ?></label></th>
				<td>
					<textarea id="<?php echo esc_attr( $id . '-code' ); ?>" data-field="code" name="<?php echo esc_attr( $name . '[code]' ); ?>" rows="7" spellcheck="false" autocomplete="off" placeholder="<?php esc_attr_e( 'Paste HTML, a <script> or a <style> here', 'quick-code-inserter' ); ?>"><?php echo esc_textarea( $slot['code'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'HTML, JavaScript in <script> tags, CSS in <style> tags, or a shortcode. Not PHP.', 'quick-code-inserter' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $id . '-location' ); ?>"><?php esc_html_e( 'Where it goes', 'quick-code-inserter' ); ?></label></th>
				<td>
					<select id="<?php echo esc_attr( $id . '-location' ); ?>" data-field="location" name="<?php echo esc_attr( $name . '[location]' ); ?>">
						<?php foreach ( $groups as $group => $places ) : ?>
							<optgroup label="<?php echo esc_attr( $group ); ?>">
								<?php foreach ( $places as $key => $location ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $slot['location'], $key ); ?>>
										<?php
										echo esc_html( $location['label'] );
										if ( ! quick_code_inserter_location_works( $key ) ) {
											echo ' ' . esc_html__( '(not in your theme)', 'quick-code-inserter' );
										}
										?>
									</option>
								<?php endforeach; ?>
							</optgroup>
						<?php endforeach; ?>
					</select>
					<p class="description qci-loc-note"><?php echo esc_html( $locations[ $slot['location'] ]['note'] ); ?></p>
				</td>
			</tr>
			<tr class="qci-para-row"<?php echo 'after_paragraph' === $slot['location'] ? '' : ' style="display:none"'; ?>>
				<th scope="row"><label for="<?php echo esc_attr( $id . '-paragraph' ); ?>"><?php esc_html_e( 'After paragraph', 'quick-code-inserter' ); ?></label></th>
				<td>
					<input type="number" class="small-text" min="1" max="50" id="<?php echo esc_attr( $id . '-paragraph' ); ?>" data-field="paragraph" name="<?php echo esc_attr( $name . '[paragraph]' ); ?>" value="<?php echo esc_attr( (string) $slot['paragraph'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Paragraph 2 or 3 is a good spot: readers have started, and haven\'t left yet.', 'quick-code-inserter' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $id . '-show' ); ?>"><?php esc_html_e( 'Show on', 'quick-code-inserter' ); ?></label></th>
				<td>
					<select id="<?php echo esc_attr( $id . '-show' ); ?>" data-field="show" name="<?php echo esc_attr( $name . '[show]' ); ?>">
						<?php foreach ( quick_code_inserter_show_choices() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $slot['show'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Visitors', 'quick-code-inserter' ); ?></th>
				<td>
					<label>
						<input type="checkbox" data-field="guests" name="<?php echo esc_attr( $name . '[guests]' ); ?>" value="1" <?php checked( $slot['guests'] ); ?> />
						<?php esc_html_e( 'Hide from logged-in users', 'quick-code-inserter' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Handy for analytics, so your own visits aren\'t counted.', 'quick-code-inserter' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Switch', 'quick-code-inserter' ); ?></th>
				<td>
					<label>
						<input type="checkbox" data-field="on" name="<?php echo esc_attr( $name . '[on]' ); ?>" value="1" <?php checked( $slot['on'] ); ?> />
						<?php esc_html_e( 'Insert this code', 'quick-code-inserter' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Untick to pause it without losing the code.', 'quick-code-inserter' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $id . '-name' ); ?>"><?php esc_html_e( 'Name', 'quick-code-inserter' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="<?php echo esc_attr( $id . '-name' ); ?>" data-field="name" name="<?php echo esc_attr( $name . '[name]' ); ?>" value="<?php echo esc_attr( $slot['name'] ); ?>" placeholder="<?php esc_attr_e( 'For example: Google Analytics', 'quick-code-inserter' ); ?>" />
					<p class="description"><?php esc_html_e( 'Just for you, to tell your codes apart. Visitors never see it.', 'quick-code-inserter' ); ?></p>
				</td>
			</tr>
		</table>
		<div class="qci-foot"><button type="button" class="qci-remove"><?php esc_html_e( 'Remove this code', 'quick-code-inserter' ); ?></button></div>
	</div>
	<?php
}

/**
 * The settings page itself.
 */
function quick_code_inserter_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = quick_code_inserter_settings();
	$slots    = $settings['slots'] ? $settings['slots'] : array( quick_code_inserter_blank_slot() );
	$safe_url = add_query_arg( 'qci-off', '1', home_url( '/' ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Quick Code Inserter', 'quick-code-inserter' ); ?></h1>
		<p><?php esc_html_e( 'Paste any code and choose where it goes on your site. The map shows each spot as you go.', 'quick-code-inserter' ); ?></p>

		<div class="qci-wrap">
			<form method="post" action="options.php" id="qci-form">
				<?php settings_fields( 'quick_code_inserter' ); ?>

				<div class="qci-card qci-c1">
					<h2><?php esc_html_e( 'All your code', 'quick-code-inserter' ); ?></h2>
					<?php if ( ! current_user_can( 'unfiltered_html' ) ) : ?>
						<p class="qci-notice"><?php esc_html_e( 'Your account is not allowed to add scripts on this site, so only plain HTML is kept when you save. Ask the network admin if you need scripts.', 'quick-code-inserter' ); ?></p>
					<?php endif; ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Main switch', 'quick-code-inserter' ); ?></th>
							<td>
								<label>
									<input type="checkbox" id="qci-enabled" name="<?php echo esc_attr( QUICK_CODE_INSERTER_OPTION . '[enabled]' ); ?>" value="1" <?php checked( $settings['enabled'] ); ?> />
									<?php esc_html_e( 'Insert my code on the site', 'quick-code-inserter' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'Untick to pause every code at once, for example while you look for a problem.', 'quick-code-inserter' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Safe view', 'quick-code-inserter' ); ?></th>
							<td>
								<p class="description" style="margin-top:0">
									<?php esc_html_e( 'If some code breaks how your site looks, add ?qci-off to the end of any address. While you are logged in as an admin, the page shows without any of your code, so you can come back here and fix it.', 'quick-code-inserter' ); ?>
									<a href="<?php echo esc_url( $safe_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Try it on your homepage', 'quick-code-inserter' ); ?></a>
								</p>
							</td>
						</tr>
					</table>
				</div>

				<div id="qci-slots">
					<?php
					foreach ( $slots as $i => $slot ) {
						quick_code_inserter_render_slot( $i, $slot, $i + 1 );
					}
					?>
				</div>

				<button type="button" class="button qci-add" id="qci-add"><span class="dashicons dashicons-plus-alt2"></span><?php esc_html_e( 'Add another code', 'quick-code-inserter' ); ?></button>

				<?php submit_button(); ?>
			</form>

			<div class="qci-side qci-card qci-preview">
				<h2><?php esc_html_e( 'Where your code goes', 'quick-code-inserter' ); ?> <span class="qci-live"><?php esc_html_e( 'Live', 'quick-code-inserter' ); ?></span></h2>
				<div id="qci-map" aria-live="polite"></div>
				<p class="description"><?php esc_html_e( 'A plan of a typical page. Each tag shows where a code goes, in its card\'s colour. Faded tags are switched off, empty, or in a spot your theme doesn\'t have.', 'quick-code-inserter' ); ?></p>
			</div>
		</div>

		<template id="qci-slot-template">
			<?php quick_code_inserter_render_slot( '__i__', quick_code_inserter_blank_slot(), 1 ); ?>
		</template>
	</div>
	<?php
}

/**
 * Add a "Settings" link next to Deactivate on the Plugins screen.
 *
 * @param string[] $links Existing links.
 * @return string[]
 */
function quick_code_inserter_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=quick-code-inserter' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'quick-code-inserter' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'quick_code_inserter_action_links' );

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function quick_code_inserter_uninstall() {
	delete_option( QUICK_CODE_INSERTER_OPTION );
}
register_uninstall_hook( __FILE__, 'quick_code_inserter_uninstall' );
