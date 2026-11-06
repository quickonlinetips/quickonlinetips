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
	wp_add_inline_style( 'qot-all-archives', qot_all_archives_css( $s['head_bg'], $s['head_fg'] ) );
}

/**
 * The CSS, shared with the settings preview.
 *
 * @param string $bg Heading background.
 * @param string $fg Heading text.
 * @return string
 */
function qot_all_archives_css( $bg, $fg ) {
	return '.qaa{display:grid;gap:20px;align-items:start;margin:1.5em 0;grid-template-columns:repeat(auto-fill,minmax(250px,1fr))}' .
		'.qaa-cols-1{grid-template-columns:1fr}.qaa-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}.qaa-cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}.qaa-cols-4{grid-template-columns:repeat(4,minmax(0,1fr))}' .
		'@media (max-width:900px){.qaa-cols-3,.qaa-cols-4{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:600px){.qaa.qaa{grid-template-columns:1fr}}' .
		'.qaa-box{border:1px solid rgba(0,0,0,.14);border-radius:6px;overflow:hidden;background:transparent;min-width:0}' .
		'.qaa .qaa-title{display:flex;align-items:center;justify-content:space-between;gap:8px;margin:0;padding:10px 14px;font-size:1.05em;line-height:1.3;background:' . $bg . ';color:' . $fg . '}' .
		'.qaa .qaa-title a{color:inherit;text-decoration:none}.qaa .qaa-title a:hover{text-decoration:underline}' .
		'.qaa-count{font-size:.75em;font-weight:600;padding:1px 8px;border-radius:10px;background:rgba(0,0,0,.08)}' .
		'.qaa .qaa-list{list-style:none;margin:0;padding:8px 14px}.qaa-list li{margin:0;padding:5px 0;border-bottom:1px solid rgba(0,0,0,.07);line-height:1.4;font-size:.95em}.qaa-list li:last-child{border-bottom:0}' .
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
	$tags = ! empty( $_GET['tags'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- checked above.
	$id   = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'draft',
			'post_title'   => $tags ? __( 'Tags', 'qot-all-archives' ) : __( 'Archives', 'qot-all-archives' ),
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
 * A sample of the site's own categories for the preview.
 *
 * @return array
 */
function qot_all_archives_sample() {
	$out   = array();
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 6,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return $out;
	}
	foreach ( $terms as $term ) {
		$posts = get_posts(
			array(
				'numberposts' => 12,
				'cat'         => $term->term_id,
				'post_status' => 'publish',
			)
		);
		$list  = array();
		foreach ( $posts as $post ) {
			$list[] = array(
				't' => get_the_title( $post ),
				'd' => get_the_date( '', $post ),
			);
		}
		$out[] = array(
			'name'  => $term->name,
			'count' => (int) $term->count,
			'posts' => $list,
		);
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
		'.qaa-how code{display:inline-block;margin:2px 0}.qaa-how td{padding:6px 10px 6px 0;vertical-align:top}.qaa-make{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0}' .
		'#qaa-demo{max-height:520px;overflow:auto;font-size:13px}#qaa-demo .qaa{margin:0}#qaa-demo .qaa-title a{pointer-events:none}'
	);
	wp_register_script( 'qot-all-archives-admin', false, array(), QOT_ALL_ARCHIVES_VERSION, true );
	wp_enqueue_script( 'qot-all-archives-admin' );
	wp_add_inline_script(
		'qot-all-archives-admin',
		'window.qaaData=' . wp_json_encode(
			array(
				'sample' => qot_all_archives_sample(),
				'css'    => qot_all_archives_css( '__BG__', '__FG__' ),
				'none'   => __( 'Your posts have no categories yet. Once they do, the preview shows them here.', 'qot-all-archives' ),
			)
		) . ';' . qot_all_archives_admin_script()
	);
}

/**
 * Preview script: builds the boxes from the site's own categories, like the shortcode.
 *
 * @return string
 */
function qot_all_archives_admin_script() {
	return <<<'JS'
( function () {
	var d = window.qaaData;
	var form = document.getElementById( 'qaa-form' );
	var demo = document.getElementById( 'qaa-demo' );
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
	function val( name ) {
		var el = form.querySelector( '[name="qot_all_archives[' + name + ']"]:checked' ) || form.querySelector( '[name="qot_all_archives[' + name + ']"]:not([type=radio]):not([type=checkbox])' );
		return el ? el.value : '';
	}
	function on( name ) {
		return form.querySelector( '[name="qot_all_archives[' + name + ']"]' ).checked;
	}
	function draw() {
		style.textContent = d.css.split( '__BG__' ).join( val( 'head_bg' ) ).split( '__FG__' ).join( val( 'head_fg' ) );
		if ( ! d.sample.length ) {
			demo.innerHTML = '<p>' + esc( d.none ) + '</p>';
			return;
		}
		var n = Math.max( 1, Math.min( 50, parseInt( val( 'posts' ), 10 ) || 10 ) );
		var min = Math.max( 1, parseInt( val( 'min_posts' ), 10 ) || 1 );
		var terms = d.sample.slice();
		if ( 'name' === val( 'order' ) ) {
			terms.sort( function ( a, b ) {
				return a.name.localeCompare( b.name );
			} );
		}
		var html = '';
		terms.forEach( function ( t ) {
			if ( t.count < min ) {
				return;
			}
			var list = t.posts.slice( 0, n ).map( function ( p ) {
				return '<li><a href="#">' + esc( p.t ) + '</a>' + ( on( 'dates' ) ? ' <time>' + esc( p.d ) + '</time>' : '' ) + '</li>';
			} ).join( '' );
			html += '<section class="qaa-box"><h2 class="qaa-title"><a href="#">' + esc( t.name ) + '</a>' + ( on( 'counts' ) ? ' <span class="qaa-count">' + t.count + '</span>' : '' ) + '</h2>' +
				'<ul class="qaa-list">' + list + '</ul>' + ( t.count > Math.min( n, t.posts.length ) ? '<a class="qaa-all" href="#">' + esc( val( 'view_all' ).replace( '%d', t.count ) ) + ' &rarr;</a>' : '' ) + '</section>';
		} );
		var cols = val( 'columns' );
		demo.innerHTML = '<div class="qaa qaa-cols-' + esc( 'auto' === cols ? 'auto' : Math.min( 2, parseInt( cols, 10 ) ) ) + '">' + html + '</div>';
	}
	demo.addEventListener( 'click', function ( e ) {
		e.preventDefault();
	} );
	form.addEventListener( 'input', draw );
	form.addEventListener( 'change', draw );
	draw();
}() );
JS;
}

/**
 * The settings page.
 */
function qot_all_archives_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s    = qot_all_archives_settings();
	$n    = QOT_ALL_ARCHIVES_OPTION;
	$make = function ( $tags ) {
		return wp_nonce_url( admin_url( 'admin-post.php?action=qot_all_archives_page' . ( $tags ? '&tags=1' : '' ) ), 'qot_all_archives_page' );
	};
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT All Archives', 'qot-all-archives' ); ?></h1>
		<p><?php esc_html_e( 'Build an archives page where every category (or tag) has its own box with its latest posts. These settings are the starting point for every archives shortcode; the preview uses your own categories.', 'qot-all-archives' ); ?></p>
		<div class="qaa-wrap">
			<form method="post" action="options.php" id="qaa-form">
				<?php settings_fields( 'qot_all_archives' ); ?>
				<div class="qaa-card qaa-c1">
					<h2><?php esc_html_e( 'Make your archives page', 'qot-all-archives' ); ?></h2>
					<p style="margin-top:14px"><?php esc_html_e( 'One click makes a draft page with the shortcode in it. Look at it, add a few words if you like, and publish.', 'qot-all-archives' ); ?></p>
					<div class="qaa-make">
						<a class="button button-primary" href="<?php echo esc_url( $make( false ) ); ?>"><?php esc_html_e( 'Make a categories page', 'qot-all-archives' ); ?></a>
						<a class="button" href="<?php echo esc_url( $make( true ) ); ?>"><?php esc_html_e( 'Make a tags page', 'qot-all-archives' ); ?></a>
					</div>
					<p><?php esc_html_e( 'Or put a shortcode in any page or post (in a Shortcode block):', 'qot-all-archives' ); ?></p>
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
							<p class="description"><?php esc_html_e( '"Fit the page" puts as many boxes side by side as your content area has room for. Phones always get one column. For a wider page, use your theme\'s full-width template.', 'qot-all-archives' ); ?></p>
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
					<h2><?php esc_html_e( 'Colours and speed', 'qot-all-archives' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Box headings', 'qot-all-archives' ); ?></th><td>
							<label><input type="color" name="<?php echo esc_attr( $n . '[head_bg]' ); ?>" value="<?php echo esc_attr( $s['head_bg'] ); ?>" /> <?php esc_html_e( 'Background', 'qot-all-archives' ); ?></label>
							<label style="margin-left:16px"><input type="color" name="<?php echo esc_attr( $n . '[head_fg]' ); ?>" value="<?php echo esc_attr( $s['head_fg'] ); ?>" /> <?php esc_html_e( 'Text', 'qot-all-archives' ); ?></label>
						</td></tr>
						<tr><th scope="row"><label for="qaa-cache"><?php esc_html_e( 'Keep ready for', 'qot-all-archives' ); ?></label></th><td>
							<input type="number" id="qaa-cache" min="0" max="168" class="small-text" name="<?php echo esc_attr( $n . '[cache]' ); ?>" value="<?php echo esc_attr( $s['cache'] ); ?>" /> <?php esc_html_e( 'hours', 'qot-all-archives' ); ?>
							<p class="description"><?php esc_html_e( 'The archives are built once and kept, so the page stays fast even with hundreds of categories. Publishing or editing a post starts afresh straight away. 0 builds them on every visit.', 'qot-all-archives' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_all_archives_help_box(); ?>
			</form>
			<div class="qaa-side qaa-card qaa-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-all-archives' ); ?> <span class="qaa-live"><?php esc_html_e( 'Live', 'qot-all-archives' ); ?></span></h2>
				<div class="qaa-screen" id="qaa-demo"></div>
				<p class="description"><?php esc_html_e( 'Your six biggest categories as they will look. The preview shows at most two columns; your page shows the columns you choose.', 'qot-all-archives' ); ?></p>
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
