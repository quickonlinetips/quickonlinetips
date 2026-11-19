<?php
/**
 * Plugin Name:       QOT All Archives
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       One shortcode builds a complete archives page: every category (or tag) in its own box, with its latest posts and a "View all" link. Columns, colours and post counts are yours to set, and the page is cached so it stays fast.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-all-archives
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_ALL_ARCHIVES_OPTION', 'qot_all_archives' );
define( 'QOT_ALL_ARCHIVES_VERSION', '1.0' );

require_once __DIR__ . '/includes/admin-common.php';

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings, used by every archives shortcode unless the shortcode says otherwise.
 *
 * @return array
 */
function qot_all_archives_defaults() {
	return array(
		'columns'   => 'auto',
		'posts'     => 10,
		'order'     => 'name',
		'top_only'  => false,
		'min_posts' => 1,
		'counts'    => true,
		'dates'     => false,
		/* translators: %d: number of posts in the category or tag. */
		'view_all'  => __( 'View all %d posts', 'qot-all-archives' ),
		'head_bg'   => '#f0f6fc',
		'head_fg'   => '#1d2327',
		'box_bg_on' => false,
		'box_bg'    => '#ffffff',
		'border_on' => false,
		'border'    => '#c3c4c7',
		'cache'     => 12,
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_all_archives_settings() {
	$saved = get_option( QOT_ALL_ARCHIVES_OPTION, array() );
	return array_merge( qot_all_archives_defaults(), is_array( $saved ) ? $saved : array() );
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_all_archives_sanitize( $input ) {
	$d     = qot_all_archives_defaults();
	$input = is_array( $input ) ? $input : array();
	$color = function ( $key ) use ( $input, $d ) {
		$value = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
		return $value ? $value : $d[ $key ];
	};
	$view  = isset( $input['view_all'] ) ? sanitize_text_field( $input['view_all'] ) : '';
	qot_all_archives_flush();
	return array(
		'columns'   => ( isset( $input['columns'] ) && in_array( $input['columns'], array( 'auto', '2', '3', '4' ), true ) ) ? $input['columns'] : $d['columns'],
		'posts'     => isset( $input['posts'] ) ? min( 50, max( 1, absint( $input['posts'] ) ) ) : $d['posts'],
		'order'     => ( isset( $input['order'] ) && in_array( $input['order'], array( 'name', 'count' ), true ) ) ? $input['order'] : $d['order'],
		'top_only'  => ! empty( $input['top_only'] ),
		'min_posts' => isset( $input['min_posts'] ) ? min( 100, max( 1, absint( $input['min_posts'] ) ) ) : $d['min_posts'],
		'counts'    => ! empty( $input['counts'] ),
		'dates'     => ! empty( $input['dates'] ),
		'view_all'  => '' === $view ? $d['view_all'] : $view,
		'head_bg'   => $color( 'head_bg' ),
		'head_fg'   => $color( 'head_fg' ),
		'box_bg_on' => ! empty( $input['box_bg_on'] ),
		'box_bg'    => $color( 'box_bg' ),
		'border_on' => ! empty( $input['border_on'] ),
		'border'    => $color( 'border' ),
		'cache'     => isset( $input['cache'] ) ? min( 168, absint( $input['cache'] ) ) : $d['cache'],
	);
}

/* ------------------------------------------------------------------ *
 * Building the archives
 * ------------------------------------------------------------------ */

/**
 * Forget every cached archives page; they are rebuilt on the next visit.
 * A version number in the cache key does this without a database search.
 */
function qot_all_archives_flush() {
	update_option( 'qot_all_archives_v', (int) get_option( 'qot_all_archives_v', 0 ) + 1, false );
}
add_action( 'save_post_post', 'qot_all_archives_flush' );
add_action( 'deleted_post', 'qot_all_archives_flush' );
add_action( 'edited_term', 'qot_all_archives_flush' );
add_action( 'created_term', 'qot_all_archives_flush' );
add_action( 'delete_term', 'qot_all_archives_flush' );

/**
 * Turn "5, 7, news" into term ids.
 *
 * @param string $list     Ids or slugs, comma separated.
 * @param string $taxonomy Taxonomy.
 * @return int[]
 */
function qot_all_archives_ids( $list, $taxonomy ) {
	$ids = array();
	foreach ( array_filter( array_map( 'trim', explode( ',', (string) $list ) ) ) as $item ) {
		if ( ctype_digit( $item ) ) {
			$ids[] = (int) $item;
		} else {
			$term = get_term_by( 'slug', sanitize_title( $item ), $taxonomy );
			if ( $term ) {
				$ids[] = (int) $term->term_id;
			}
		}
	}
	return $ids;
}

/**
 * The archives HTML for categories or tags.
 *
 * @param string $taxonomy 'category' or 'post_tag'.
 * @param array  $atts     Shortcode attributes.
 * @return string
 */
function qot_all_archives_build( $taxonomy, $atts ) {
	$s    = qot_all_archives_settings();
	$atts = shortcode_atts(
		array(
			'columns'   => $s['columns'],
			'posts'     => $s['posts'],
			'order'     => $s['order'],
			'include'   => '',
			'exclude'   => '',
			'top_only'  => $s['top_only'] ? 'yes' : 'no',
			'min_posts' => $s['min_posts'],
			'counts'    => $s['counts'] ? 'yes' : 'no',
			'dates'     => $s['dates'] ? 'yes' : 'no',
		),
		$atts
	);
	$cols  = in_array( (string) $atts['columns'], array( 'auto', '1', '2', '3', '4' ), true ) ? (string) $atts['columns'] : 'auto';
	$posts = min( 50, max( 1, absint( $atts['posts'] ) ) );
	$yes   = function ( $value ) {
		return in_array( strtolower( (string) $value ), array( 'yes', '1', 'true', 'on' ), true );
	};

	$key   = 'qot_aa_' . md5( wp_json_encode( array( $taxonomy, $atts, $s, get_option( 'qot_all_archives_v', 0 ), get_locale() ) ) );
	$cache = (int) $s['cache'];
	if ( $cache ) {
		$html = get_transient( $key );
		if ( is_string( $html ) ) {
			return $html;
		}
	}

	$args = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'count' === $atts['order'] ? 'count' : 'name',
		'order'      => 'count' === $atts['order'] ? 'DESC' : 'ASC',
	);
	$include = qot_all_archives_ids( $atts['include'], $taxonomy );
	if ( $include ) {
		$args['include'] = $include;
		$args['orderby'] = 'include';
	}
	$exclude = qot_all_archives_ids( $atts['exclude'], $taxonomy );
	if ( $exclude ) {
		$args['exclude'] = $exclude;
	}
	if ( 'category' === $taxonomy && $yes( $atts['top_only'] ) ) {
		$args['parent'] = 0;
	}
	$terms = get_terms( $args );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return '';
	}

	$boxes = '';
	foreach ( $terms as $term ) {
		if ( (int) $term->count < max( 1, absint( $atts['min_posts'] ) ) ) {
			continue;
		}
		$query = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => $posts,
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- cached.
					array(
						'taxonomy'         => $taxonomy,
						'terms'            => $term->term_id,
						'include_children' => false,
					),
				),
			)
		);
		if ( ! $query->posts ) {
			continue;
		}
		$link  = get_term_link( $term );
		$list  = '';
		foreach ( $query->posts as $post ) {
			$list .= '<li><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a>' .
				( $yes( $atts['dates'] ) ? ' <time datetime="' . esc_attr( get_the_date( 'c', $post ) ) . '">' . esc_html( get_the_date( '', $post ) ) . '</time>' : '' ) . '</li>';
		}
		$count  = (int) $term->count;
		$boxes .= '<section class="qaa-box"><h2 class="qaa-title"><a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>' .
			( $yes( $atts['counts'] ) ? ' <span class="qaa-count">' . esc_html( number_format_i18n( $count ) ) . '</span>' : '' ) . '</h2>' .
			'<ul class="qaa-list">' . $list . '</ul>' .
			( $count > count( $query->posts ) ? '<a class="qaa-all" href="' . esc_url( $link ) . '">' . esc_html( str_replace( '%d', number_format_i18n( $count ), $s['view_all'] ) ) . ' &rarr;</a>' : '' ) .
			'</section>';
	}
	$html = '' === $boxes ? '' : '<div class="qaa qaa-cols-' . esc_attr( $cols ) . '">' . $boxes . '</div>';
	if ( $cache ) {
		set_transient( $key, $html, $cache * HOUR_IN_SECONDS );
	}
	return $html;
}

/**
 * The archives' CSS, added once on pages that show them.
 */
function qot_all_archives_style() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	$s    = qot_all_archives_settings();
	wp_register_style( 'qot-all-archives', false, array(), QOT_ALL_ARCHIVES_VERSION );
	wp_enqueue_style( 'qot-all-archives' );
	wp_add_inline_style( 'qot-all-archives', qot_all_archives_css( $s['head_bg'], $s['head_fg'], $s['box_bg_on'] ? $s['box_bg'] : '', $s['border_on'] ? $s['border'] : '' ) );
}

/**
 * The CSS, shared with the settings preview.
 *
 * @param string $bg     Heading background.
 * @param string $fg     Heading text.
 * @param string $box    Box background, '' for none (the theme's own).
 * @param string $border Box border, '' for the soft default.
 * @return string
 */
function qot_all_archives_css( $bg, $fg, $box = '', $border = '' ) {
	$box    = '' === $box ? 'transparent' : $box;
	$border = '' === $border ? 'rgba(0,0,0,.14)' : $border;
	return '.qaa{display:grid;gap:20px;align-items:start;margin:1.5em 0;grid-template-columns:repeat(auto-fill,minmax(250px,1fr))}' .
		'.qaa-cols-1{grid-template-columns:1fr}.qaa-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}.qaa-cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}.qaa-cols-4{grid-template-columns:repeat(4,minmax(0,1fr))}' .
		'@media (max-width:900px){.qaa-cols-3,.qaa-cols-4{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:600px){.qaa.qaa{grid-template-columns:1fr}}' .
		'.qaa-box{border:1px solid ' . $border . ';border-radius:6px;overflow:hidden;background:' . $box . ';min-width:0}' .
		'.qaa .qaa-title{display:flex;align-items:center;justify-content:space-between;gap:8px;margin:0;padding:10px 14px;font-size:1.05em;line-height:1.3;background:' . $bg . ';color:' . $fg . '}' .
		'.qaa .qaa-title a{color:inherit;text-decoration:none;overflow-wrap:anywhere;min-width:0}.qaa .qaa-title a:hover{text-decoration:underline}' .
		'.qaa-count{font-size:.75em;font-weight:600;padding:1px 8px;border-radius:10px;background:rgba(0,0,0,.08)}' .
		'.qaa .qaa-list{list-style:none;margin:0;padding:8px 14px}.qaa-list li{margin:0;padding:5px 0;overflow-wrap:anywhere;border-bottom:1px solid rgba(0,0,0,.07);line-height:1.4;font-size:.95em}.qaa-list li:last-child{border-bottom:0}' .
		'.qaa-list time{display:block;font-size:.8em;opacity:.7}.qaa .qaa-all{display:block;margin:0 14px 12px;font-size:.9em;font-weight:600;text-decoration:none}.qaa .qaa-all:hover{text-decoration:underline}';
}

/**
 * [qot_category_archives] (also [qot_all_archives]) and [qot_tag_archives].
 *
 * @param array|string $atts Attributes.
 * @param string       $content Unused.
 * @param string       $tag Shortcode name.
 * @return string
 */
function qot_all_archives_shortcode( $atts, $content = '', $tag = '' ) {
	$html = qot_all_archives_build( 'qot_tag_archives' === $tag ? 'post_tag' : 'category', is_array( $atts ) ? $atts : array() );
	if ( '' !== $html ) {
		qot_all_archives_style();
	}
	return $html;
}
add_shortcode( 'qot_category_archives', 'qot_all_archives_shortcode' );
add_shortcode( 'qot_all_archives', 'qot_all_archives_shortcode' );
add_shortcode( 'qot_tag_archives', 'qot_all_archives_shortcode' );

/* ------------------------------------------------------------------ *
 * One-click archives page
 * ------------------------------------------------------------------ */

/**
 * Make a draft page holding the shortcode, and open it in the editor.
 */
function qot_all_archives_make_page() {
	if ( ! current_user_can( 'publish_pages' ) ) {
		wp_die( esc_html__( 'You are not allowed to add pages.', 'qot-all-archives' ) );
	}
	check_admin_referer( 'qot_all_archives_page' );
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- checked above.
	$tags  = ! empty( $_GET['tags'] );
	$title = isset( $_GET['title'] ) ? sanitize_text_field( wp_unslash( $_GET['title'] ) ) : '';
	// phpcs:enable
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'draft',
			'post_title'   => '' !== $title ? $title : qot_all_archives_page_title( $tags ),
			'post_content' => '<!-- wp:paragraph --><p>' . esc_html__( 'Browse every topic on this site, with the latest posts in each.', 'qot-all-archives' ) . '</p><!-- /wp:paragraph -->' . "\n\n" .
				'<!-- wp:shortcode -->' . ( $tags ? '[qot_tag_archives]' : '[qot_category_archives]' ) . '<!-- /wp:shortcode -->',
		)
	);
	if ( is_wp_error( $id ) || ! $id ) {
		wp_die( esc_html__( 'The page could not be made. Please try again.', 'qot-all-archives' ) );
	}
	wp_safe_redirect( admin_url( 'post.php?post=' . (int) $id . '&action=edit' ) );
	exit;
}
add_action( 'admin_post_qot_all_archives_page', 'qot_all_archives_make_page' );

/**
 * The suggested title for a new archives page.
 *
 * @param bool $tags True for a tags page.
 * @return string
 */
function qot_all_archives_page_title( $tags ) {
	return $tags ? __( 'Tags', 'qot-all-archives' ) : __( 'Archives', 'qot-all-archives' );
}

/**
 * Pages that already hold an archives shortcode, so the settings page can list them.
 *
 * @return WP_Post[]
 */
function qot_all_archives_pages() {
	$found = array();
	$pages = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			's'           => 'qot_',
			'numberposts' => 20,
		)
	);
	foreach ( $pages as $page ) {
		foreach ( array( 'qot_category_archives', 'qot_all_archives', 'qot_tag_archives' ) as $code ) {
			if ( has_shortcode( $page->post_content, $code ) ) {
				$found[] = $page;
				break;
			}
		}
	}
	return $found;
}

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_all_archives_register_setting() {
	register_setting(
		'qot_all_archives',
		QOT_ALL_ARCHIVES_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_all_archives_sanitize',
			'default'           => qot_all_archives_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_all_archives_register_setting' );

/**
 * Add the page under Settings.
 */
function qot_all_archives_add_page() {
	$hook = add_options_page( __( 'QOT All Archives', 'qot-all-archives' ), __( 'QOT All Archives', 'qot-all-archives' ), 'manage_options', 'qot-all-archives', 'qot_all_archives_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_all_archives_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_all_archives_add_page' );

/**
 * A sample of the site's own categories and tags for the preview.
 *
 * @return array
 */
function qot_all_archives_sample() {
	$out = array();
	foreach ( array( 'category', 'post_tag' ) as $taxonomy ) {
		$out[ $taxonomy ] = array();
		$terms            = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => 10,
			)
		);
		if ( is_wp_error( $terms ) ) {
			continue;
		}
		foreach ( $terms as $term ) {
			$posts = get_posts(
				array(
					'numberposts' => 12,
					'post_status' => 'publish',
					'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- settings page only.
						array(
							'taxonomy'         => $taxonomy,
							'terms'            => $term->term_id,
							'include_children' => false,
						),
					),
				)
			);
			$list  = array();
			foreach ( $posts as $post ) {
				$list[] = array(
					't' => get_the_title( $post ),
					'd' => get_the_date( '', $post ),
				);
			}
			$out[ $taxonomy ][] = array(
				'name'   => $term->name,
				'count'  => (int) $term->count,
				'parent' => (int) $term->parent,
				'posts'  => $list,
			);
		}
	}
	return $out;
}

/**
 * Style and live preview for the settings page.
 */
function qot_all_archives_admin_assets() {
	wp_register_style( 'qot-all-archives-admin', false, array(), QOT_ALL_ARCHIVES_VERSION );
	wp_enqueue_style( 'qot-all-archives-admin' );
	wp_add_inline_style(
		'qot-all-archives-admin',
		qot_all_archives_admin_base_css() .
		'.qaa-how code{display:inline-block;margin:2px 0}.qaa-how td{padding:6px 10px 6px 0;vertical-align:top}.qaa-make{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:4px 0 8px}' .
		'.qaa-pages{margin:4px 0 14px;padding:10px 14px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px}.qaa-pages ul{margin:6px 0 0}.qaa-pages li{margin:0 0 4px}.qaa-state{color:#50575e}' .
		'.qaa-color{display:inline-flex;align-items:center;gap:6px;margin:0 18px 6px 0;white-space:nowrap}.qaa-color input[type=color]{width:44px;height:30px;padding:2px;cursor:pointer}' .
		'.qaa-reset.button-link{font-size:12px}.qaa-reset:disabled{color:#a7aaad;cursor:default;text-decoration:none}' .
		'#qaa-demo{max-height:560px;overflow:auto;padding:0}.qaa-page{width:800px;padding:22px 26px;box-sizing:border-box;font-size:15px;line-height:1.5}' .
		'.qaa-kind{display:inline-block;margin:0 0 8px;padding:2px 10px;border-radius:10px;background:#f0f6fc;border:1px solid #c5d9ed;color:#1d2327;font-size:12px;font-weight:600}' .
		'.qaa-kind code{background:none;padding:0;font-size:12px}' .
		'.qaa-page-title{margin:0 0 6px;font-size:30px;line-height:1.2;color:#1d2327}.qaa-page-intro{margin:0 0 4px;color:#50575e}' .
		'#qaa-demo .qaa{margin:18px 0 0}#qaa-demo a{color:#2271b1}#qaa-demo .qaa-title a{color:inherit;pointer-events:none}'
	);
	wp_register_script( 'qot-all-archives-admin', false, array(), QOT_ALL_ARCHIVES_VERSION, true );
	wp_enqueue_script( 'qot-all-archives-admin' );
	wp_add_inline_script(
		'qot-all-archives-admin',
		'window.qaaData=' . wp_json_encode(
			array(
				'sample'   => qot_all_archives_sample(),
				'css'      => qot_all_archives_css( '__BG__', '__FG__', '__BOX__', '__BORDER__' ),
				'noBox'    => 'transparent',
				'noBorder' => 'rgba(0,0,0,.14)',
				'titles'   => array(
					'category' => qot_all_archives_page_title( false ),
					'post_tag' => qot_all_archives_page_title( true ),
				),
				'kinds'    => array(
					'category' => __( 'Category archives', 'qot-all-archives' ),
					'post_tag' => __( 'Tag archives', 'qot-all-archives' ),
				),
				'codes'    => array(
					'category' => '[qot_category_archives]',
					'post_tag' => '[qot_tag_archives]',
				),
				'buttons'  => array(
					'category' => __( 'Make the categories page', 'qot-all-archives' ),
					'post_tag' => __( 'Make the tags page', 'qot-all-archives' ),
				),
				'intro'    => __( 'Browse every topic on this site, with the latest posts in each.', 'qot-all-archives' ),
				'untitled' => __( '(no title)', 'qot-all-archives' ),
				'none'     => array(
					'category' => __( 'Your posts have no categories yet. Once they do, the preview shows them here.', 'qot-all-archives' ),
					'post_tag' => __( 'Your posts have no tags yet. Once they do, the preview shows them here.', 'qot-all-archives' ),
				),
			)
		) . ';' . qot_all_archives_admin_script()
	);
}

/**
 * Preview script: builds the page from the site's own categories or tags, like the shortcode.
 *
 * @return string
 */
function qot_all_archives_admin_script() {
	return <<<'JS'
( function () {
	var d = window.qaaData;
	var form = document.getElementById( 'qaa-form' );
	var demo = document.getElementById( 'qaa-demo' );
	var title = document.getElementById( 'qaa-title' );
	var make = document.getElementById( 'qaa-make' );
	var style = document.createElement( 'style' );
	document.head.appendChild( style );
	if ( ! form || ! demo ) {
		return;
	}
	function esc( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[ c ];
		} );
	}
	function field( name ) {
		return form.querySelector( '[name="qot_all_archives[' + name + ']"]:checked' ) || form.querySelector( '[name="qot_all_archives[' + name + ']"]:not([type=radio]):not([type=checkbox])' );
	}
	function val( name ) {
		var el = field( name );
		return el ? el.value : '';
	}
	function on( name ) {
		var el = form.querySelector( '[name="qot_all_archives[' + name + ']"]' );
		return !! ( el && el.checked );
	}
	function kind() {
		var el = form.querySelector( 'input[name="qaa_preview_type"]:checked' );
		return el && 'post_tag' === el.value ? 'post_tag' : 'category';
	}

	// Reset links put a colour back to the plugin's own; they grey out when nothing has changed.
	var resets = form.querySelectorAll( '.qaa-reset' );
	function resetStates() {
		resets.forEach( function ( b ) {
			var input = document.getElementById( b.getAttribute( 'data-for' ) );
			var off = b.getAttribute( 'data-off' ) ? document.getElementById( b.getAttribute( 'data-off' ) ) : null;
			b.disabled = input.value.toLowerCase() === b.getAttribute( 'data-default' ).toLowerCase() && ( ! off || ! off.checked );
		} );
	}
	resets.forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			var input = document.getElementById( b.getAttribute( 'data-for' ) );
			input.value = b.getAttribute( 'data-default' );
			if ( b.getAttribute( 'data-off' ) ) {
				document.getElementById( b.getAttribute( 'data-off' ) ).checked = false;
			}
			draw();
			input.focus();
		} );
	} );
	// Picking a box colour switches that colour on.
	form.querySelectorAll( 'input[type=color][data-on]' ).forEach( function ( input ) {
		input.addEventListener( 'input', function () {
			document.getElementById( input.getAttribute( 'data-on' ) ).checked = true;
		} );
	} );

	// The page maker: the button follows the chosen kind and title.
	var lastKind = kind();
	function maker() {
		var k = kind();
		if ( title && k !== lastKind && ( '' === title.value.trim() || title.value === d.titles[ lastKind ] ) ) {
			title.value = d.titles[ k ];
		}
		lastKind = k;
		if ( make ) {
			make.textContent = d.buttons[ k ];
			make.href = make.getAttribute( 'data-href' ) + ( 'post_tag' === k ? '&tags=1' : '' ) + '&title=' + encodeURIComponent( title ? title.value.trim() : '' );
		}
	}

	// Show the page at a typical content width, shrunk to fit the preview window.
	function fit() {
		var page = demo.querySelector( '.qaa-page' );
		if ( page ) {
			page.style.zoom = Math.min( 1, demo.clientWidth / 800 );
		}
	}

	function draw() {
		maker();
		resetStates();
		var k = kind();
		style.textContent = d.css.split( '__BG__' ).join( val( 'head_bg' ) ).split( '__FG__' ).join( val( 'head_fg' ) )
			.split( '__BOX__' ).join( on( 'box_bg_on' ) ? val( 'box_bg' ) : d.noBox )
			.split( '__BORDER__' ).join( on( 'border_on' ) ? val( 'border' ) : d.noBorder );
		var head = '<div class="qaa-kind">' + esc( d.kinds[ k ] ) + ' &middot; <code>' + esc( d.codes[ k ] ) + '</code></div>' +
			'<h1 class="qaa-page-title">' + esc( title && title.value.trim() ? title.value.trim() : d.untitled ) + '</h1>' +
			'<p class="qaa-page-intro">' + esc( d.intro ) + '</p>';
		var n = Math.max( 1, Math.min( 50, parseInt( val( 'posts' ), 10 ) || 10 ) );
		var min = Math.max( 1, parseInt( val( 'min_posts' ), 10 ) || 1 );
		var terms = d.sample[ k ].filter( function ( t ) {
			return t.count >= min && ! ( 'category' === k && on( 'top_only' ) && t.parent );
		} ).slice( 0, 6 );
		if ( 'name' === val( 'order' ) ) {
			terms.sort( function ( a, b ) {
				return a.name.localeCompare( b.name );
			} );
		}
		var html = '';
		terms.forEach( function ( t ) {
			var list = t.posts.slice( 0, n ).map( function ( p ) {
				return '<li><a href="#">' + esc( p.t ) + '</a>' + ( on( 'dates' ) ? ' <time>' + esc( p.d ) + '</time>' : '' ) + '</li>';
			} ).join( '' );
			html += '<section class="qaa-box"><h2 class="qaa-title"><a href="#">' + esc( t.name ) + '</a>' + ( on( 'counts' ) ? ' <span class="qaa-count">' + t.count + '</span>' : '' ) + '</h2>' +
				'<ul class="qaa-list">' + list + '</ul>' + ( t.count > Math.min( n, t.posts.length ) ? '<a class="qaa-all" href="#">' + esc( val( 'view_all' ).replace( '%d', t.count ) ) + ' &rarr;</a>' : '' ) + '</section>';
		} );
		var cols = val( 'columns' );
		var body = html ? '<div class="qaa qaa-cols-' + esc( /^[1-4]$/.test( cols ) ? cols : 'auto' ) + '">' + html + '</div>' : '<p>' + esc( d.none[ k ] ) + '</p>';
		demo.innerHTML = '<div class="qaa-page">' + head + body + '</div>';
		fit();
	}
	demo.addEventListener( 'click', function ( e ) {
		e.preventDefault();
	} );
	form.addEventListener( 'input', draw );
	form.addEventListener( 'change', draw );
	window.addEventListener( 'resize', fit );
	draw();
}() );
JS;
}

/**
 * One colour picker with its Reset link.
 *
 * @param string $key   Setting name.
 * @param string $label Label beside the picker.
 * @param string $off   Id of the checkbox that Reset switches off, if any.
 */
function qot_all_archives_color_field( $key, $label, $off = '' ) {
	$s  = qot_all_archives_settings();
	$d  = qot_all_archives_defaults();
	$id = 'qaa-' . str_replace( '_', '-', $key );
	printf(
		'<span class="qaa-color"><input type="color" id="%1$s" name="%2$s" value="%3$s"%4$s /> <label for="%1$s">%5$s</label> <button type="button" class="button-link qaa-reset" data-for="%1$s" data-default="%6$s"%7$s>%8$s</button></span>',
		esc_attr( $id ),
		esc_attr( QOT_ALL_ARCHIVES_OPTION . '[' . $key . ']' ),
		esc_attr( $s[ $key ] ),
		$off ? ' data-on="' . esc_attr( $off ) . '"' : '',
		esc_html( $label ),
		esc_attr( $d[ $key ] ),
		$off ? ' data-off="' . esc_attr( $off ) . '"' : '',
		esc_html__( 'Reset', 'qot-all-archives' )
	);
}

/**
 * The settings page.
 */
function qot_all_archives_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s     = qot_all_archives_settings();
	$n     = QOT_ALL_ARCHIVES_OPTION;
	$base  = wp_nonce_url( admin_url( 'admin-post.php?action=qot_all_archives_page' ), 'qot_all_archives_page' );
	$pages = qot_all_archives_pages();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT All Archives', 'qot-all-archives' ); ?></h1>
		<p><?php esc_html_e( 'Build an archives page where every category (or tag) has its own box with its latest posts. These settings are the starting point for every archives shortcode. The preview uses your own categories and tags, and updates as you go.', 'qot-all-archives' ); ?></p>
		<div class="qaa-wrap">
			<form method="post" action="options.php" id="qaa-form">
				<?php settings_fields( 'qot_all_archives' ); ?>
				<div class="qaa-card qaa-c1">
					<h2><?php esc_html_e( 'Make your archives page', 'qot-all-archives' ); ?></h2>
					<?php if ( $pages ) : ?>
						<div class="qaa-pages" style="margin-top:14px">
							<strong><?php esc_html_e( 'Your archives pages so far:', 'qot-all-archives' ); ?></strong>
							<ul>
								<?php
								foreach ( $pages as $page ) {
									$state = get_post_status_object( get_post_status( $page ) );
									$kind  = has_shortcode( $page->post_content, 'qot_tag_archives' ) ? __( 'tags', 'qot-all-archives' ) : __( 'categories', 'qot-all-archives' );
									printf(
										'<li><strong>%1$s</strong> <span class="qaa-state">(%2$s, %3$s)</span> &middot; <a href="%4$s">%5$s</a> &middot; <a href="%6$s" target="_blank">%7$s</a></li>',
										esc_html( get_the_title( $page ) ? get_the_title( $page ) : __( '(no title)', 'qot-all-archives' ) ),
										esc_html( $kind ),
										esc_html( $state ? $state->label : get_post_status( $page ) ),
										esc_url( get_edit_post_link( $page ) ),
										esc_html__( 'Edit', 'qot-all-archives' ),
										esc_url( get_permalink( $page ) ),
										'publish' === get_post_status( $page ) ? esc_html__( 'View', 'qot-all-archives' ) : esc_html__( 'Preview', 'qot-all-archives' )
									);
								}
								?>
							</ul>
						</div>
					<?php endif; ?>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'The page shows', 'qot-all-archives' ); ?></th><td>
							<label style="margin-right:14px"><input type="radio" name="qaa_preview_type" value="category" checked /> <?php esc_html_e( 'Categories', 'qot-all-archives' ); ?></label>
							<label><input type="radio" name="qaa_preview_type" value="post_tag" /> <?php esc_html_e( 'Tags', 'qot-all-archives' ); ?></label>
							<p class="description"><?php esc_html_e( 'The preview switches too, so you can see either kind before you make the page.', 'qot-all-archives' ); ?></p>
						</td></tr>
						<tr><th scope="row"><label for="qaa-title"><?php esc_html_e( 'Page title', 'qot-all-archives' ); ?></label></th><td>
							<input type="text" id="qaa-title" class="regular-text" value="<?php echo esc_attr( qot_all_archives_page_title( false ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Shown at the top of the page and used for its address (a page titled Archives lives at yoursite.com/archives/). You can change it later in the editor.', 'qot-all-archives' ); ?></p>
						</td></tr>
					</table>
					<div class="qaa-make">
						<a class="button button-primary" id="qaa-make" href="<?php echo esc_url( $base ); ?>" data-href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'Make the categories page', 'qot-all-archives' ); ?></a>
					</div>
					<p class="description"><?php esc_html_e( 'This makes a draft page with the shortcode in it and opens it in the editor. Nothing shows on your site until you publish it there.', 'qot-all-archives' ); ?></p>
					<p style="margin-top:16px"><?php esc_html_e( 'Or put a shortcode in any page or post (in a Shortcode block):', 'qot-all-archives' ); ?></p>
					<table class="qaa-how">
						<tr><td><code>[qot_category_archives]</code></td><td><?php esc_html_e( 'every category', 'qot-all-archives' ); ?></td></tr>
						<tr><td><code>[qot_tag_archives]</code></td><td><?php esc_html_e( 'every tag', 'qot-all-archives' ); ?></td></tr>
						<tr><td><code>[qot_category_archives include="news,reviews"]</code></td><td><?php esc_html_e( 'only these categories, in this order (names from the address, or ID numbers)', 'qot-all-archives' ); ?></td></tr>
						<tr><td><code>[qot_category_archives exclude="uncategorized"]</code></td><td><?php esc_html_e( 'all but these', 'qot-all-archives' ); ?></td></tr>
						<tr><td><code>[qot_tag_archives columns="3" posts="5" min_posts="10"]</code></td><td><?php esc_html_e( 'change any setting below for one page', 'qot-all-archives' ); ?></td></tr>
					</table>
					<p class="description" style="margin-bottom:12px"><?php esc_html_e( 'Settings you can use in a shortcode: columns (auto, 1-4), posts, order (name or count), include, exclude, top_only, min_posts, counts, dates (yes or no).', 'qot-all-archives' ); ?></p>
				</div>
				<div class="qaa-card qaa-c2">
					<h2><?php esc_html_e( 'Boxes', 'qot-all-archives' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Columns', 'qot-all-archives' ); ?></th><td>
							<?php
							foreach ( array( 'auto' => __( 'Fit the page', 'qot-all-archives' ), '2' => '2', '3' => '3', '4' => '4' ) as $value => $label ) {
								printf( '<label style="margin-right:14px"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label>', esc_attr( $n . '[columns]' ), esc_attr( $value ), checked( $s['columns'], $value, false ), esc_html( $label ) );
							}
							?>
							<p class="description"><?php esc_html_e( '"Fit the page" puts as many boxes side by side as your content area has room for. Phones always get one column, and small tablets at most two. For a wider page, use your theme\'s full-width template.', 'qot-all-archives' ); ?></p>
						</td></tr>
						<tr><th scope="row"><label for="qaa-posts"><?php esc_html_e( 'Posts in each box', 'qot-all-archives' ); ?></label></th><td>
							<input type="number" id="qaa-posts" min="1" max="50" class="small-text" name="<?php echo esc_attr( $n . '[posts]' ); ?>" value="<?php echo esc_attr( $s['posts'] ); ?>" />
							<p class="description"><?php esc_html_e( 'The latest posts. A "View all" link leads to the rest.', 'qot-all-archives' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Order of boxes', 'qot-all-archives' ); ?></th><td>
							<label style="margin-right:14px"><input type="radio" name="<?php echo esc_attr( $n . '[order]' ); ?>" value="name" <?php checked( $s['order'], 'name' ); ?> /> <?php esc_html_e( 'A to Z', 'qot-all-archives' ); ?></label>
							<label><input type="radio" name="<?php echo esc_attr( $n . '[order]' ); ?>" value="count" <?php checked( $s['order'], 'count' ); ?> /> <?php esc_html_e( 'Most posts first', 'qot-all-archives' ); ?></label>
						</td></tr>
						<tr><th scope="row"><label for="qaa-min"><?php esc_html_e( 'Smallest box', 'qot-all-archives' ); ?></label></th><td>
							<input type="number" id="qaa-min" min="1" max="100" class="small-text" name="<?php echo esc_attr( $n . '[min_posts]' ); ?>" value="<?php echo esc_attr( $s['min_posts'] ); ?>" /> <?php esc_html_e( 'posts or more', 'qot-all-archives' ); ?>
							<p class="description"><?php esc_html_e( 'Leave out categories and tags with fewer posts. Handy for sites with hundreds of tags.', 'qot-all-archives' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Show', 'qot-all-archives' ); ?></th><td>
							<label style="display:block;margin:0 0 6px"><input type="checkbox" name="<?php echo esc_attr( $n . '[counts]' ); ?>" value="1" <?php checked( $s['counts'] ); ?> /> <?php esc_html_e( 'Number of posts beside each name', 'qot-all-archives' ); ?></label>
							<label style="display:block;margin:0 0 6px"><input type="checkbox" name="<?php echo esc_attr( $n . '[dates]' ); ?>" value="1" <?php checked( $s['dates'] ); ?> /> <?php esc_html_e( 'Date under each post', 'qot-all-archives' ); ?></label>
							<label style="display:block"><input type="checkbox" name="<?php echo esc_attr( $n . '[top_only]' ); ?>" value="1" <?php checked( $s['top_only'] ); ?> /> <?php esc_html_e( 'Only top-level categories, no sub-categories', 'qot-all-archives' ); ?></label>
						</td></tr>
						<tr><th scope="row"><label for="qaa-view"><?php esc_html_e( 'Link words', 'qot-all-archives' ); ?></label></th><td>
							<input type="text" id="qaa-view" class="regular-text" name="<?php echo esc_attr( $n . '[view_all]' ); ?>" value="<?php echo esc_attr( $s['view_all'] ); ?>" />
							<p class="description"><?php esc_html_e( '%d becomes the number of posts.', 'qot-all-archives' ); ?></p>
						</td></tr>
					</table>
				</div>
				<div class="qaa-card qaa-c3">
					<h2><?php esc_html_e( 'Colours', 'qot-all-archives' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Box headings', 'qot-all-archives' ); ?></th><td>
							<?php
							qot_all_archives_color_field( 'head_bg', __( 'Background', 'qot-all-archives' ) );
							qot_all_archives_color_field( 'head_fg', __( 'Text', 'qot-all-archives' ) );
							?>
							<p class="description"><?php esc_html_e( 'Reset puts a colour back to the plugin\'s own. Save to keep it.', 'qot-all-archives' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Box background', 'qot-all-archives' ); ?></th><td>
							<label style="margin-right:12px"><input type="checkbox" id="qaa-box-bg-on" name="<?php echo esc_attr( $n . '[box_bg_on]' ); ?>" value="1" <?php checked( $s['box_bg_on'] ); ?> /> <?php esc_html_e( 'Use a colour', 'qot-all-archives' ); ?></label>
							<?php qot_all_archives_color_field( 'box_bg', __( 'Colour', 'qot-all-archives' ), 'qaa-box-bg-on' ); ?>
							<p class="description"><?php esc_html_e( 'Off: the boxes show your theme\'s own background. Picking a colour switches this on.', 'qot-all-archives' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Box border', 'qot-all-archives' ); ?></th><td>
							<label style="margin-right:12px"><input type="checkbox" id="qaa-border-on" name="<?php echo esc_attr( $n . '[border_on]' ); ?>" value="1" <?php checked( $s['border_on'] ); ?> /> <?php esc_html_e( 'Use a colour', 'qot-all-archives' ); ?></label>
							<?php qot_all_archives_color_field( 'border', __( 'Colour', 'qot-all-archives' ), 'qaa-border-on' ); ?>
							<p class="description"><?php esc_html_e( 'Off: a soft see-through line that suits light and dark themes.', 'qot-all-archives' ); ?></p>
						</td></tr>
					</table>
				</div>
				<div class="qaa-card qaa-c4">
					<h2><?php esc_html_e( 'Speed', 'qot-all-archives' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><label for="qaa-cache"><?php esc_html_e( 'Refresh every', 'qot-all-archives' ); ?></label></th><td>
							<input type="number" id="qaa-cache" min="0" max="168" class="small-text" name="<?php echo esc_attr( $n . '[cache]' ); ?>" value="<?php echo esc_attr( $s['cache'] ); ?>" /> <?php esc_html_e( 'hours', 'qot-all-archives' ); ?>
							<p class="description"><?php esc_html_e( 'The archives are built once and saved, so the page loads fast even with hundreds of categories. They are built afresh after this many hours, and straight away when you publish or edit a post or save this page. 0 builds them on every visit (slower).', 'qot-all-archives' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_all_archives_help_box(); ?>
			</form>
			<div class="qaa-side qaa-card qaa-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-all-archives' ); ?> <span class="qaa-live"><?php esc_html_e( 'Live', 'qot-all-archives' ); ?></span></h2>
				<div class="qaa-screen" id="qaa-demo"></div>
				<p class="description"><?php esc_html_e( 'Your page with its six biggest categories (or tags), in the columns you chose, at a typical content width of 800 pixels and shrunk to fit. Your theme\'s fonts and link colours will show on the real page.', 'qot-all-archives' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_all_archives_plugin_links( admin_url( 'options-general.php?page=qot-all-archives' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 * Cached archives expire on their own.
 */
function qot_all_archives_uninstall() {
	delete_option( QOT_ALL_ARCHIVES_OPTION );
	delete_option( 'qot_all_archives_v' );
}
register_uninstall_hook( __FILE__, 'qot_all_archives_uninstall' );
