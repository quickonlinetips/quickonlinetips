<?php
/**
 * Plugin Name:       QOT All Images
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       One shortcode builds an image archive: the pictures from your posts as a grid of thumbnails, each linking to its post. A light, fast way for readers to browse your blog by its pictures.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-all-images
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_ALL_IMAGES_OPTION', 'qot_all_images' );
define( 'QOT_ALL_IMAGES_VERSION', '1.0' );
define( 'QOT_ALL_IMAGES_META', '_qot_all_images' );
define( 'QOT_ALL_IMAGES_COUNT', '_qot_all_images_count' );

require_once __DIR__ . '/includes/admin-common.php';

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings.
 *
 * @return array
 */
function qot_all_images_defaults() {
	return array(
		'mode'     => 'one',
		'per_load' => 48,
		'columns'  => 'auto',
		'shape'    => 'square',
		'titles'   => true,
		'new_tab'  => false,
		'featured' => true,
		'more'     => __( 'Load more images', 'qot-all-images' ),
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_all_images_settings() {
	$saved = get_option( QOT_ALL_IMAGES_OPTION, array() );
	return array_merge( qot_all_images_defaults(), is_array( $saved ) ? $saved : array() );
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_all_images_sanitize( $input ) {
	$d     = qot_all_images_defaults();
	$input = is_array( $input ) ? $input : array();
	$pick  = function ( $key, $allowed ) use ( $input, $d ) {
		return ( isset( $input[ $key ] ) && in_array( (string) $input[ $key ], $allowed, true ) ) ? (string) $input[ $key ] : $d[ $key ];
	};
	$more  = isset( $input['more'] ) ? sanitize_text_field( $input['more'] ) : '';
	return array(
		'mode'     => $pick( 'mode', array( 'one', 'all' ) ),
		'per_load' => isset( $input['per_load'] ) ? min( 120, max( 6, absint( $input['per_load'] ) ) ) : $d['per_load'],
		'columns'  => $pick( 'columns', array( 'auto', '3', '4', '5', '6' ) ),
		'shape'    => $pick( 'shape', array( 'square', 'wide', 'natural' ) ),
		'titles'   => ! empty( $input['titles'] ),
		'new_tab'  => ! empty( $input['new_tab'] ),
		'featured' => ! empty( $input['featured'] ),
		'more'     => '' === $more ? $d['more'] : $more,
	);
}

/* ------------------------------------------------------------------ *
 * The image list
 * ------------------------------------------------------------------ */

/**
 * Find the images in one post and remember them. Images come from the
 * post's own content (not "attached to" lists, which miss reused images),
 * plus its featured image. Media library images use their medium size.
 *
 * @param int|WP_Post $post Post.
 * @return int Number of images found.
 */
function qot_all_images_index_post( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'post' !== $post->post_type ) {
		return 0;
	}
	$s      = qot_all_images_settings();
	$images = array();
	$seen   = array();
	$add    = function ( $id, $src, $alt ) use ( &$images, &$seen ) {
		if ( $id ) {
			$thumb = wp_get_attachment_image_src( $id, 'medium' );
			if ( $thumb ) {
				$src = $thumb[0];
			}
			if ( '' === $alt ) {
				$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
			}
		}
		$src = esc_url_raw( $src );
		if ( '' === $src || isset( $seen[ $src ] ) || preg_match( '#(emoji|gravatar\.com|/smilies/|\.svg(\?|$))#i', $src ) ) {
			return;
		}
		$seen[ $src ] = true;
		$images[]     = array(
			'src' => $src,
			'alt' => wp_strip_all_tags( $alt ),
		);
	};
	if ( $s['featured'] && has_post_thumbnail( $post ) ) {
		$add( (int) get_post_thumbnail_id( $post ), '', '' );
	}
	if ( preg_match_all( '/<img\b[^>]*>/i', (string) $post->post_content, $tags ) ) {
		foreach ( $tags[0] as $tag ) {
			$src = preg_match( '/\ssrc=(["\'])(.*?)\1/i', $tag, $m ) ? html_entity_decode( $m[2] ) : '';
			$alt = preg_match( '/\salt=(["\'])(.*?)\1/i', $tag, $m ) ? html_entity_decode( $m[2] ) : '';
			$id  = preg_match( '/wp-image-(\d+)/', $tag, $m ) ? (int) $m[1] : 0;
			if ( 0 === strpos( $src, 'data:' ) ) {
				continue;
			}
			$add( $id, $src, $alt );
			if ( count( $images ) >= 60 ) {
				break;
			}
		}
	}
	update_post_meta( $post->ID, QOT_ALL_IMAGES_META, $images );
	update_post_meta( $post->ID, QOT_ALL_IMAGES_COUNT, count( $images ) );
	return count( $images );
}

/**
 * Keep the list up to date as posts are saved.
 *
 * @param int     $post_id Post id.
 * @param WP_Post $post    Post.
 */
function qot_all_images_on_save( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	qot_all_images_index_post( $post );
}
add_action( 'save_post_post', 'qot_all_images_on_save', 20, 2 );

/**
 * Index a batch of published posts that have no image list yet.
 *
 * @param int $limit Most posts to do now.
 * @return int Posts done.
 */
function qot_all_images_index_missing( $limit ) {
	$ids = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- small batches.
				array(
					'key'     => QOT_ALL_IMAGES_COUNT,
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);
	foreach ( $ids as $id ) {
		qot_all_images_index_post( $id );
	}
	return count( $ids );
}

/**
 * How many published posts still need their images listed.
 *
 * @return int
 */
function qot_all_images_missing_count() {
	$q = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin only.
				array(
					'key'     => QOT_ALL_IMAGES_COUNT,
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);
	return (int) $q->found_posts;
}

/* ------------------------------------------------------------------ *
 * The shortcode
 * ------------------------------------------------------------------ */

/**
 * [qot_all_images]: the grid, one load at a time. "Load more" is a plain
 * link to the next load, so it works without JavaScript and with caching.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function qot_all_images_shortcode( $atts ) {
	$s    = qot_all_images_settings();
	$atts = shortcode_atts(
		array(
			'mode'     => $s['mode'],
			'per_load' => $s['per_load'],
			'columns'  => $s['columns'],
			'shape'    => $s['shape'],
			'category' => '',
		),
		$atts,
		'qot_all_images'
	);
	$mode  = 'all' === $atts['mode'] ? 'all' : 'one';
	$want  = min( 120, max( 6, absint( $atts['per_load'] ) ) );
	$cols  = in_array( (string) $atts['columns'], array( 'auto', '2', '3', '4', '5', '6' ), true ) ? (string) $atts['columns'] : 'auto';
	$shape = in_array( $atts['shape'], array( 'square', 'wide', 'natural' ), true ) ? $atts['shape'] : 'square';
	$from  = isset( $_GET['qai'] ) ? absint( $_GET['qai'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- page number only.

	// New sites and older posts: list a few posts' images now; the settings page can do the rest at once.
	qot_all_images_index_missing( 20 );

	$query = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_query'          => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- needed to skip posts without images.
			array(
				'key'     => QOT_ALL_IMAGES_COUNT,
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
		),
	);
	if ( '' !== $atts['category'] ) {
		$query['category_name'] = sanitize_title( $atts['category'] );
	}

	$items  = array();
	$offset = $from;
	$more   = false;
	while ( count( $items ) < $want ) {
		$posts = get_posts( array_merge( $query, array( 'offset' => $offset, 'posts_per_page' => 'one' === $mode ? $want - count( $items ) : 12 ) ) );
		if ( ! $posts ) {
			break;
		}
		foreach ( $posts as $post ) {
			$images = get_post_meta( $post->ID, QOT_ALL_IMAGES_META, true );
			$images = is_array( $images ) ? $images : array();
			if ( 'all' === $mode && $items && count( $items ) + count( $images ) > $want + 6 ) {
				break 2;
			}
			++$offset;
			foreach ( 'one' === $mode ? array_slice( $images, 0, 1 ) : $images as $image ) {
				$items[] = array( $post, $image );
			}
			if ( count( $items ) >= $want ) {
				break 2;
			}
		}
	}
	if ( $items ) {
		$more = (bool) get_posts( array_merge( $query, array( 'offset' => $offset, 'posts_per_page' => 1, 'fields' => 'ids' ) ) );
	}
	if ( ! $items ) {
		return $from ? '' : '<p class="qai-none">' . esc_html__( 'No images yet.', 'qot-all-images' ) . '</p>';
	}

	$target = $s['new_tab'] ? ' target="_blank" rel="noopener"' : '';
	$html   = '<div class="qai qai-cols-' . esc_attr( $cols ) . ' qai-' . esc_attr( $shape ) . '" id="qai">';
	foreach ( $items as $item ) {
		list( $post, $image ) = $item;
		$title = get_the_title( $post );
		$link  = get_permalink( $post );
		$html .= '<figure class="qai-item"><a href="' . esc_url( $link ) . '"' . $target . '><img src="' . esc_url( $image['src'] ) . '" alt="' . esc_attr( '' !== $image['alt'] ? $image['alt'] : $title ) . '" loading="lazy" decoding="async" /></a>' .
			( $s['titles'] ? '<figcaption><a href="' . esc_url( $link ) . '"' . $target . '>' . esc_html( $title ) . '</a></figcaption>' : '' ) . '</figure>';
	}
	$html .= '</div>';
	if ( $more ) {
		$html .= '<p class="qai-more"><a href="' . esc_url( add_query_arg( 'qai', $offset ) . '#qai' ) . '">' . esc_html( $s['more'] ) . '</a></p>';
	}
	qot_all_images_style();
	return $html;
}
add_shortcode( 'qot_all_images', 'qot_all_images_shortcode' );

/**
 * The grid CSS.
 *
 * @return string
 */
function qot_all_images_css() {
	return '.qai{display:grid;gap:14px;margin:1.5em 0;grid-template-columns:repeat(auto-fill,minmax(150px,1fr))}' .
		'.qai-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}.qai-cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}.qai-cols-4{grid-template-columns:repeat(4,minmax(0,1fr))}.qai-cols-5{grid-template-columns:repeat(5,minmax(0,1fr))}.qai-cols-6{grid-template-columns:repeat(6,minmax(0,1fr))}' .
		'@media (max-width:700px){.qai.qai{grid-template-columns:repeat(2,minmax(0,1fr))}}' .
		'.qai .qai-item{margin:0;min-width:0}.qai-item>a{display:block;overflow:hidden;border-radius:6px;background:rgba(0,0,0,.06)}' .
		'.qai-item img{display:block;width:100%;height:auto;margin:0;transition:transform .25s}.qai-item>a:hover img{transform:scale(1.04)}' .
		'.qai-square .qai-item img{aspect-ratio:1/1;object-fit:cover}.qai-wide .qai-item img{aspect-ratio:4/3;object-fit:cover}' .
		'.qai figcaption{margin:6px 0 0;font-size:.85em;line-height:1.35}.qai figcaption a{text-decoration:none}.qai figcaption a:hover{text-decoration:underline}' .
		'.qai-more{text-align:center;margin:1em 0 2em}.qai-more a{display:inline-block;padding:8px 18px;border:1px solid currentColor;border-radius:999px;text-decoration:none;font-weight:600}';
}

/**
 * Add the grid CSS once on pages that show it.
 */
function qot_all_images_style() {
	if ( wp_style_is( 'qot-all-images', 'enqueued' ) ) {
		return;
	}
	wp_register_style( 'qot-all-images', false, array(), QOT_ALL_IMAGES_VERSION );
	wp_enqueue_style( 'qot-all-images' );
	wp_add_inline_style( 'qot-all-images', qot_all_images_css() );
}

/* ------------------------------------------------------------------ *
 * Building the list from the settings page
 * ------------------------------------------------------------------ */

/**
 * List the images of the next batch of posts (called by the settings page).
 */
function qot_all_images_ajax_index() {
	check_ajax_referer( 'qot_all_images_index' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}
	if ( ! empty( $_POST['again'] ) ) {
		delete_post_meta_by_key( QOT_ALL_IMAGES_COUNT );
		delete_post_meta_by_key( QOT_ALL_IMAGES_META );
	}
	$done = qot_all_images_index_missing( 50 );
	wp_send_json_success(
		array(
			'done' => $done,
			'left' => qot_all_images_missing_count(),
		)
	);
}
add_action( 'wp_ajax_qot_all_images_index', 'qot_all_images_ajax_index' );

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_all_images_register_setting() {
	register_setting(
		'qot_all_images',
		QOT_ALL_IMAGES_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_all_images_sanitize',
			'default'           => qot_all_images_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_all_images_register_setting' );

/**
 * Add the page under Settings.
 */
function qot_all_images_add_page() {
	$hook = add_options_page( __( 'QOT All Images', 'qot-all-images' ), __( 'QOT All Images', 'qot-all-images' ), 'manage_options', 'qot-all-images', 'qot_all_images_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_all_images_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_all_images_add_page' );

/**
 * Some of the site's own images for the preview.
 *
 * @return array
 */
function qot_all_images_sample() {
	$out   = array();
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin only.
				array(
					'key'     => QOT_ALL_IMAGES_COUNT,
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			),
		)
	);
	foreach ( $posts as $post ) {
		$images = get_post_meta( $post->ID, QOT_ALL_IMAGES_META, true );
		if ( is_array( $images ) ) {
			foreach ( array_slice( $images, 0, 3 ) as $i => $image ) {
				$out[] = array(
					'src'   => $image['src'],
					'title' => get_the_title( $post ),
					'first' => 0 === $i,
				);
			}
		}
	}
	return $out;
}

/**
 * Style and live preview for the settings page.
 */
function qot_all_images_admin_assets() {
	wp_register_style( 'qot-all-images-admin', false, array(), QOT_ALL_IMAGES_VERSION );
	wp_enqueue_style( 'qot-all-images-admin' );
	wp_add_inline_style(
		'qot-all-images-admin',
		qot_all_images_admin_base_css() . qot_all_images_css() .
		'#qai-demo{max-height:520px;overflow:auto}#qai-demo .qai{margin:0;gap:10px}#qai-demo .qai-item>a{background:linear-gradient(135deg,#dcdcde,#f0f0f1)}#qai-demo .qai-ph{display:block;aspect-ratio:1/1}' .
		'#qai-demo figcaption{font-size:11px}.qai-stats{margin:12px 0 6px}#qai-progress{margin-left:8px;color:#50575e}.qai-how code{display:inline-block;margin:2px 0}'
	);
	wp_register_script( 'qot-all-images-admin', false, array(), QOT_ALL_IMAGES_VERSION, true );
	wp_enqueue_script( 'qot-all-images-admin' );
	wp_add_inline_script(
		'qot-all-images-admin',
		'window.qaiData=' . wp_json_encode(
			array(
				'sample' => qot_all_images_sample(),
				'ajax'   => admin_url( 'admin-ajax.php' ),
				'nonce'  => wp_create_nonce( 'qot_all_images_index' ),
				/* translators: %d: number of posts left. */
				'left'   => __( 'Working... %d posts to go', 'qot-all-images' ),
				'done'   => __( 'Done. Reloading...', 'qot-all-images' ),
				'failed' => __( 'Something went wrong. Please try again.', 'qot-all-images' ),
			)
		) . ';' . qot_all_images_admin_script()
	);
}

/**
 * Page script: the build button and the live preview.
 *
 * @return string
 */
function qot_all_images_admin_script() {
	return <<<'JS'
( function () {
	var d = window.qaiData;
	var form = document.getElementById( 'qai-form' );
	var demo = document.getElementById( 'qai-demo' );
	if ( ! form || ! demo ) {
		return;
	}
	function esc( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[ c ];
		} );
	}
	function val( name ) {
		var el = form.querySelector( '[name="qot_all_images[' + name + ']"]:checked' ) || form.querySelector( '[name="qot_all_images[' + name + ']"]:not([type=radio]):not([type=checkbox])' );
		return el ? el.value : '';
	}
	function draw() {
		var titles = form.querySelector( '[name="qot_all_images[titles]"]' ).checked;
		var items = d.sample.filter( function ( s ) {
			return 'all' === val( 'mode' ) || s.first;
		} ).slice( 0, 12 );
		var html = '';
		if ( items.length ) {
			items.forEach( function ( s ) {
				html += '<figure class="qai-item"><a href="#"><img src="' + esc( s.src ) + '" alt="" loading="lazy"></a>' + ( titles ? '<figcaption><a href="#">' + esc( s.title ) + '</a></figcaption>' : '' ) + '</figure>';
			} );
		} else {
			for ( var i = 0; i < 9; i++ ) {
				html += '<figure class="qai-item"><a href="#"><span class="qai-ph"></span></a>' + ( titles ? '<figcaption><a href="#">Post title</a></figcaption>' : '' ) + '</figure>';
			}
		}
		var cols = val( 'columns' );
		demo.innerHTML = '<div class="qai qai-cols-' + ( 'auto' === cols ? 'auto' : Math.min( 4, parseInt( cols, 10 ) ) ) + ' qai-' + esc( val( 'shape' ) ) + '">' + html + '</div>' +
			'<p class="qai-more"><a href="#">' + esc( val( 'more' ) ) + '</a></p>';
	}
	demo.addEventListener( 'click', function ( e ) {
		e.preventDefault();
	} );
	form.addEventListener( 'input', draw );
	form.addEventListener( 'change', draw );
	draw();
	document.querySelectorAll( '.qai-build' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var again = btn.getAttribute( 'data-again' ) ? 1 : 0;
			var out = document.getElementById( 'qai-progress' );
			document.querySelectorAll( '.qai-build' ).forEach( function ( b ) {
				b.disabled = true;
			} );
			function step() {
				var body = new FormData();
				body.append( 'action', 'qot_all_images_index' );
				body.append( '_ajax_nonce', d.nonce );
				body.append( 'again', again );
				again = 0;
				fetch( d.ajax, { method: 'POST', body: body, credentials: 'same-origin' } ).then( function ( r ) {
					return r.json();
				} ).then( function ( r ) {
					if ( ! r.success ) {
						throw new Error();
					}
					if ( r.data.left > 0 && r.data.done > 0 ) {
						out.textContent = d.left.replace( '%d', r.data.left );
						step();
					} else {
						out.textContent = d.done;
						window.location.reload();
					}
				} ).catch( function () {
					out.textContent = d.failed;
				} );
			}
			step();
		} );
	} );
}() );
JS;
}

/**
 * The settings page.
 */
function qot_all_images_render_page() {
	global $wpdb;
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s       = qot_all_images_settings();
	$n       = QOT_ALL_IMAGES_OPTION;
	$missing = qot_all_images_missing_count();
	// One quick sum over the list's own counts.
	$images = (int) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = %s", QOT_ALL_IMAGES_COUNT ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$posts  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value > 0", QOT_ALL_IMAGES_COUNT ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT All Images', 'qot-all-images' ); ?></h1>
		<p><?php esc_html_e( 'Show the pictures from your posts as one big grid, each linking to its post. Put the shortcode [qot_all_images] on any page. The preview uses your own images.', 'qot-all-images' ); ?></p>
		<div class="qai-wrap">
			<form method="post" action="options.php" id="qai-form">
				<?php settings_fields( 'qot_all_images' ); ?>
				<div class="qai-card qai-c1">
					<h2><?php esc_html_e( 'Your images', 'qot-all-images' ); ?></h2>
					<p class="qai-stats">
						<?php
						/* translators: 1: number of images, 2: number of posts. */
						echo esc_html( sprintf( __( '%1$s images found in %2$s posts.', 'qot-all-images' ), number_format_i18n( $images ), number_format_i18n( $posts ) ) );
						if ( $missing ) {
							echo ' ' . esc_html( sprintf( /* translators: %s: number of posts. */ _n( '%s post not checked yet.', '%s posts not checked yet.', $missing, 'qot-all-images' ), number_format_i18n( $missing ) ) );
						}
						?>
					</p>
					<p>
						<?php if ( $missing ) : ?>
							<button type="button" class="button button-primary qai-build"><?php esc_html_e( 'Find images in all posts now', 'qot-all-images' ); ?></button>
						<?php else : ?>
							<button type="button" class="button qai-build" data-again="1"><?php esc_html_e( 'Check all posts again', 'qot-all-images' ); ?></button>
						<?php endif; ?>
						<span id="qai-progress" aria-live="polite"></span>
					</p>
					<p class="description" style="margin-bottom:12px"><?php esc_html_e( 'New and edited posts are checked as you save them. Older posts are checked a few at a time as visitors open the images page, or all at once with this button.', 'qot-all-images' ); ?></p>
				</div>
				<div class="qai-card qai-c2">
					<h2><?php esc_html_e( 'The grid', 'qot-all-images' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Images from each post', 'qot-all-images' ); ?></th><td>
							<label style="display:block;margin:0 0 6px"><input type="radio" name="<?php echo esc_attr( $n . '[mode]' ); ?>" value="one" <?php checked( $s['mode'], 'one' ); ?> /> <?php esc_html_e( 'One image per post', 'qot-all-images' ); ?></label>
							<label style="display:block"><input type="radio" name="<?php echo esc_attr( $n . '[mode]' ); ?>" value="all" <?php checked( $s['mode'], 'all' ); ?> /> <?php esc_html_e( 'Every image', 'qot-all-images' ); ?></label>
							<p class="description"><?php esc_html_e( 'One per post suits big blogs: a tidy grid with one picture for each post.', 'qot-all-images' ); ?></p>
						</td></tr>
						<tr><th scope="row"><label for="qai-per"><?php esc_html_e( 'Images per load', 'qot-all-images' ); ?></label></th><td>
							<input type="number" id="qai-per" min="6" max="120" class="small-text" name="<?php echo esc_attr( $n . '[per_load]' ); ?>" value="<?php echo esc_attr( $s['per_load'] ); ?>" />
							<p class="description"><?php esc_html_e( 'A "Load more" link shows the next ones, so the page stays quick however many images you have.', 'qot-all-images' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Columns', 'qot-all-images' ); ?></th><td>
							<?php
							foreach ( array( 'auto' => __( 'Fit the page', 'qot-all-images' ), '3' => '3', '4' => '4', '5' => '5', '6' => '6' ) as $value => $label ) {
								printf( '<label style="margin-right:14px"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label>', esc_attr( $n . '[columns]' ), esc_attr( $value ), checked( $s['columns'], $value, false ), esc_html( $label ) );
							}
							?>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Thumbnail shape', 'qot-all-images' ); ?></th><td>
							<?php
							foreach ( array( 'square' => __( 'Square', 'qot-all-images' ), 'wide' => __( 'Wide (4:3)', 'qot-all-images' ), 'natural' => __( 'As the photo is', 'qot-all-images' ) ) as $value => $label ) {
								printf( '<label style="margin-right:14px"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label>', esc_attr( $n . '[shape]' ), esc_attr( $value ), checked( $s['shape'], $value, false ), esc_html( $label ) );
							}
							?>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Show', 'qot-all-images' ); ?></th><td>
							<label style="display:block;margin:0 0 6px"><input type="checkbox" name="<?php echo esc_attr( $n . '[titles]' ); ?>" value="1" <?php checked( $s['titles'] ); ?> /> <?php esc_html_e( 'Post title under each image', 'qot-all-images' ); ?></label>
							<label style="display:block;margin:0 0 6px"><input type="checkbox" name="<?php echo esc_attr( $n . '[featured]' ); ?>" value="1" <?php checked( $s['featured'] ); ?> /> <?php esc_html_e( 'Include featured images', 'qot-all-images' ); ?></label>
							<label style="display:block"><input type="checkbox" name="<?php echo esc_attr( $n . '[new_tab]' ); ?>" value="1" <?php checked( $s['new_tab'] ); ?> /> <?php esc_html_e( 'Open posts in a new tab', 'qot-all-images' ); ?></label>
						</td></tr>
						<tr><th scope="row"><label for="qai-more"><?php esc_html_e( 'Link words', 'qot-all-images' ); ?></label></th><td>
							<input type="text" id="qai-more" class="regular-text" name="<?php echo esc_attr( $n . '[more]' ); ?>" value="<?php echo esc_attr( $s['more'] ); ?>" />
						</td></tr>
					</table>
				</div>
				<div class="qai-card qai-c3">
					<h2><?php esc_html_e( 'Shortcode', 'qot-all-images' ); ?></h2>
					<table class="qai-how" style="margin:12px 0">
						<tr><td><code>[qot_all_images]</code></td><td><?php esc_html_e( 'with the settings above', 'qot-all-images' ); ?></td></tr>
						<tr><td><code>[qot_all_images category="recipes"]</code></td><td><?php esc_html_e( 'one category only', 'qot-all-images' ); ?></td></tr>
						<tr><td><code>[qot_all_images mode="all" columns="4" per_load="24"]</code></td><td><?php esc_html_e( 'change settings for one page', 'qot-all-images' ); ?></td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_all_images_help_box(); ?>
			</form>
			<div class="qai-side qai-card qai-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-all-images' ); ?> <span class="qai-live"><?php esc_html_e( 'Live', 'qot-all-images' ); ?></span></h2>
				<div class="qai-screen" id="qai-demo"></div>
				<p class="description"><?php esc_html_e( 'Images from your latest posts. Grey boxes stand in until images are found.', 'qot-all-images' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_all_images_plugin_links( admin_url( 'options-general.php?page=qot-all-images' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its settings and image lists.
 */
function qot_all_images_uninstall() {
	delete_option( QOT_ALL_IMAGES_OPTION );
	delete_post_meta_by_key( QOT_ALL_IMAGES_META );
	delete_post_meta_by_key( QOT_ALL_IMAGES_COUNT );
}
register_uninstall_hook( __FILE__, 'qot_all_images_uninstall' );
