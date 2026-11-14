<?php
/**
 * Plugin Name:       QOT Featured Image Column
 * Plugin URI:        https://www.quickonlinetips.com/
 * Description:       Shows each post's featured image in the Posts, Pages and other admin lists, flags missing, small, broken or alt-less images, and adds a "No Featured Image" view. Turn the column on or off from Screen Options.
 * Version:           1.1.0
 * Requires at least: 5.5
 * Requires PHP:      7.0
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-featured-image-column
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class QOT_Featured_Image_Column {

	const COLUMN    = 'qot_featured_image';
	const SLUG      = 'qot-featured-image-column';
	const QUERY_VAR = 'qot_fic';
	const MIN_WIDTH = 1200; // Google Discover and most social cards want 1200px+.

	private static $types = null;

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_columns' ) );
		add_filter( 'default_hidden_columns', array( __CLASS__, 'default_hidden' ), 10, 2 );
		add_action( 'admin_head-edit.php', array( __CLASS__, 'print_css' ) );
		add_action( 'admin_footer-edit.php', array( __CLASS__, 'print_js' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_missing' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );

		// Keep the "No Featured Image" count fresh.
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'meta_changed' ), 10, 3 );
		}
		add_action( 'transition_post_status', array( __CLASS__, 'status_changed' ), 10, 3 );
		add_action( 'deleted_post', array( __CLASS__, 'post_deleted' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Setup                                                              */
	/* ------------------------------------------------------------------ */

	/**
	 * Admin-listed post types that support featured images.
	 * WooCommerce products are left out: WooCommerce already shows its own image column.
	 */
	public static function post_types() {
		if ( null !== self::$types ) {
			return self::$types;
		}
		$types = array();
		foreach ( get_post_types( array( 'show_ui' => true ), 'names' ) as $type ) {
			if ( 'attachment' === $type || ! post_type_supports( $type, 'thumbnail' ) ) {
				continue;
			}
			if ( 'product' === $type && class_exists( 'WooCommerce' ) ) {
				continue;
			}
			$types[] = $type;
		}
		/** Filter: change which post types get the column. */
		self::$types = array_values( (array) apply_filters( 'qot_featured_image_column_post_types', $types ) );
		return self::$types;
	}

	public static function register_columns() {
		foreach ( self::post_types() as $type ) {
			add_filter( "manage_{$type}_posts_columns", array( __CLASS__, 'add_column' ) );
			add_action( "manage_{$type}_posts_custom_column", array( __CLASS__, 'render' ), 10, 2 );
			add_filter( "views_edit-{$type}", array( __CLASS__, 'add_view' ) );
		}
	}

	private static function on_list_screen() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && 'edit' === $screen->base && in_array( $screen->post_type, self::post_types(), true );
	}

	/* ------------------------------------------------------------------ */
	/* Column                                                             */
	/* ------------------------------------------------------------------ */

	/** Column goes right after the checkbox. WordPress adds its Screen Options checkbox automatically. */
	public static function add_column( $columns ) {
		$label = __( 'Featured Image', 'qot-featured-image-column' );
		$new   = array();
		$added = false;
		foreach ( $columns as $key => $title ) {
			$new[ $key ] = $title;
			if ( 'cb' === $key ) {
				$new[ self::COLUMN ] = $label;
				$added               = true;
			}
		}
		return $added ? $new : array( self::COLUMN => $label ) + $new;
	}

	/** Hidden until ticked in Screen Options (for users who haven't saved Screen Options on that list yet). */
	public static function default_hidden( $hidden, $screen ) {
		if ( $screen && 'edit' === $screen->base && in_array( $screen->post_type, self::post_types(), true ) ) {
			$hidden[] = self::COLUMN;
		}
		return $hidden;
	}

	public static function render( $column, $post_id ) {
		if ( self::COLUMN !== $column ) {
			return;
		}

		$thumb_id = (int) get_post_meta( $post_id, '_thumbnail_id', true );

		// No featured image.
		if ( ! $thumb_id ) {
			echo '<span class="qot-fic-none" aria-hidden="true">&mdash;</span>';
			echo '<span class="screen-reader-text">' . esc_html__( 'No featured image', 'qot-featured-image-column' ) . '</span>';
			return;
		}

		// Featured image points to a deleted media item.
		$attachment = get_post( $thumb_id );
		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			echo '<span class="qot-fic-broken"><span class="dashicons dashicons-format-image" aria-hidden="true"></span></span>';
			self::print_badges(
				array(
					array( 'error', __( 'Broken', 'qot-featured-image-column' ), __( 'The featured image was deleted from the Media Library.', 'qot-featured-image-column' ) ),
				)
			);
			return;
		}

		// 'thumbnail' (150px) shown at 80px stays sharp on high-DPI screens.
		// loading=lazy: nothing is downloaded while the column is hidden.
		$img = wp_get_attachment_image(
			$thumb_id,
			'thumbnail',
			false,
			array(
				'class'    => 'qot-fic-img',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'alt'      => '',
			)
		);

		$meta    = wp_get_attachment_metadata( $thumb_id );
		$width   = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
		$height  = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
		$issues  = self::issues( $thumb_id, $width );
		$preview = wp_get_attachment_image_url( $thumb_id, 'medium_large' );
		if ( ! $preview ) {
			$preview = wp_get_attachment_image_url( $thumb_id, 'full' );
		}

		$info = $width && $height ? sprintf( '%d × %d px', $width, $height ) : '';
		foreach ( $issues as $issue ) {
			$info .= ( $info ? ' · ' : '' ) . $issue[1];
		}

		$name = sprintf(
			/* translators: %s: post title */
			__( 'Featured image of “%s”', 'qot-featured-image-column' ),
			wp_strip_all_tags( get_the_title( $post_id ) )
		);
		$edit = current_user_can( 'edit_post', $post_id ) ? get_edit_post_link( $post_id ) : '';
		$tag  = $edit ? 'a' : 'span';

		printf(
			'<%1$s%2$s class="qot-fic-link" tabindex="0" aria-label="%3$s" data-qot-preview="%4$s" data-qot-info="%5$s">%6$s</%1$s>',
			$tag, // 'a' or 'span'.
			$edit ? ' href="' . esc_url( $edit ) . '"' : '',
			esc_attr( $name ),
			esc_url( (string) $preview ),
			esc_attr( $info ),
			$img ? $img : '<span class="qot-fic-broken"><span class="dashicons dashicons-format-image" aria-hidden="true"></span></span>' // Built by wp_get_attachment_image().
		);

		self::print_badges( $issues );
	}

	/**
	 * Problems worth fixing: file gone, too small, no alt text.
	 * Each issue: array( level, short label, explanation ).
	 */
	private static function issues( $thumb_id, $width ) {
		$issues = array();

		// File missing on disk (skipped for media offloaded to S3/CDN, where no local copy is expected).
		$file = get_attached_file( $thumb_id );
		if ( $file && self::is_local( $thumb_id ) && apply_filters( 'qot_featured_image_column_check_files', true, $thumb_id ) && ! file_exists( $file ) ) {
			$issues[] = array( 'error', __( 'File missing', 'qot-featured-image-column' ), __( 'The image file is no longer on the server.', 'qot-featured-image-column' ) );
		}

		/** Filter: minimum recommended width in pixels (0 turns the check off). */
		$min = (int) apply_filters( 'qot_featured_image_column_min_width', self::MIN_WIDTH );
		if ( $min && $width && $width < $min ) {
			$issues[] = array(
				'warn',
				__( 'Small', 'qot-featured-image-column' ),
				/* translators: 1: image width, 2: recommended width */
				sprintf( __( 'Only %1$d px wide. Google Discover and social shares look best at %2$d px or more.', 'qot-featured-image-column' ), $width, $min )
			);
		}

		if ( '' === trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) ) ) {
			$issues[] = array( 'info', __( 'No alt', 'qot-featured-image-column' ), __( 'The image has no alt text, which helps image search and screen readers.', 'qot-featured-image-column' ) );
		}

		return (array) apply_filters( 'qot_featured_image_column_issues', $issues, $thumb_id );
	}

	private static function is_local( $thumb_id ) {
		static $base = null;
		if ( null === $base ) {
			$uploads = wp_get_upload_dir();
			$base    = set_url_scheme( $uploads['baseurl'], 'https' );
		}
		$url = wp_get_attachment_url( $thumb_id );
		return $url && 0 === strpos( set_url_scheme( $url, 'https' ), $base );
	}

	private static function print_badges( $issues ) {
		if ( ! $issues ) {
			return;
		}
		echo '<span class="qot-fic-badges">';
		foreach ( $issues as $issue ) {
			printf(
				'<span class="qot-fic-badge qot-fic-badge--%1$s" title="%3$s">%2$s<span class="screen-reader-text">: %3$s</span></span>',
				esc_attr( $issue[0] ),
				esc_html( $issue[1] ),
				esc_attr( $issue[2] )
			);
		}
		echo '</span>';
	}

	/* ------------------------------------------------------------------ */
	/* "No Featured Image" view                                           */
	/* ------------------------------------------------------------------ */

	private static function missing_meta_query() {
		return array(
			'relation' => 'OR',
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_thumbnail_id',
				'value'   => array( '', '0' ),
				'compare' => 'IN',
			),
		);
	}

	private static function is_missing_view() {
		return isset( $_GET[ self::QUERY_VAR ] ) && 'missing' === sanitize_key( wp_unslash( $_GET[ self::QUERY_VAR ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	public static function missing_count( $type ) {
		$key   = 'qot_fic_missing_' . $type;
		$count = get_transient( $key );
		if ( false === $count ) {
			$query = new WP_Query(
				array(
					'post_type'              => $type,
					'post_status'            => 'any',
					'posts_per_page'         => 1,
					'fields'                 => 'ids',
					'orderby'                => 'none',
					'meta_query'             => self::missing_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);
			$count = (int) $query->found_posts;
			set_transient( $key, $count, 12 * HOUR_IN_SECONDS );
		}
		return (int) $count;
	}

	public static function add_view( $views ) {
		$screen = get_current_screen();
		$type   = $screen ? $screen->post_type : 'post';
		$count  = self::missing_count( $type );
		$active = self::is_missing_view();

		if ( ! $count && ! $active ) {
			return $views;
		}

		$url = add_query_arg(
			array(
				'post_type'     => $type,
				self::QUERY_VAR => 'missing',
			),
			admin_url( 'edit.php' )
		);

		$views['qot_fic_missing'] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
			esc_url( $url ),
			$active ? ' class="current" aria-current="page"' : '',
			esc_html__( 'No Featured Image', 'qot-featured-image-column' ),
			esc_html( number_format_i18n( $count ) )
		);
		return $views;
	}

	public static function filter_missing( $query ) {
		global $pagenow;
		if ( ! is_admin() || ! $query->is_main_query() || 'edit.php' !== $pagenow || ! self::is_missing_view() ) {
			return;
		}
		$type = $query->get( 'post_type' );
		$type = $type ? $type : 'post';
		if ( ! is_string( $type ) || ! in_array( $type, self::post_types(), true ) ) {
			return;
		}
		$existing = $query->get( 'meta_query' );
		$ours     = self::missing_meta_query();
		$query->set( 'meta_query', $existing ? array( 'relation' => 'AND', $existing, $ours ) : $ours );
	}

	/* Count cache. */

	private static function flush_count( $post_id ) {
		$type = get_post_type( $post_id );
		if ( $type ) {
			delete_transient( 'qot_fic_missing_' . $type );
		}
	}

	public static function meta_changed( $meta_id, $post_id, $meta_key ) {
		if ( '_thumbnail_id' === $meta_key ) {
			self::flush_count( $post_id );
		}
	}

	public static function status_changed( $new_status, $old_status, $post ) {
		if ( $new_status !== $old_status ) {
			delete_transient( 'qot_fic_missing_' . $post->post_type );
		}
	}

	public static function post_deleted( $post_id ) {
		self::flush_count( $post_id );
	}

	/* ------------------------------------------------------------------ */
	/* Styles, hover preview, plugin links                                */
	/* ------------------------------------------------------------------ */

	public static function print_css() {
		if ( ! self::on_list_screen() ) {
			return;
		}
		?>
		<style id="qot-featured-image-column">
			.fixed .column-qot_featured_image { width: 96px; }
			.column-qot_featured_image .qot-fic-link { display: block; width: 80px; border-radius: 4px; }
			.column-qot_featured_image .qot-fic-img {
				display: block; width: 80px; height: 80px; object-fit: cover;
				border-radius: 4px; background: #f0f0f1; box-shadow: 0 0 0 1px rgba(0, 0, 0, .08);
			}
			.column-qot_featured_image .qot-fic-link:focus { outline: none; box-shadow: 0 0 0 2px #2271b1; }
			.column-qot_featured_image .qot-fic-none { color: #a7aaad; }
			.column-qot_featured_image .qot-fic-broken {
				display: flex; align-items: center; justify-content: center; width: 80px; height: 80px;
				border: 1px dashed #d63638; border-radius: 4px; color: #d63638; box-sizing: border-box;
			}
			.qot-fic-badges { display: flex; flex-wrap: wrap; gap: 3px; margin-top: 5px; max-width: 84px; }
			.qot-fic-badge { font-size: 11px; line-height: 18px; padding: 0 6px; border-radius: 9px; font-weight: 600; cursor: help; white-space: nowrap; }
			.qot-fic-badge--error { background: #fcf0f1; color: #b32d2e; }
			.qot-fic-badge--warn  { background: #fcf9e8; color: #8a6100; }
			.qot-fic-badge--info  { background: #f0f6fc; color: #135e96; }
			#qot-fic-preview {
				position: fixed; z-index: 100000; display: none; pointer-events: none;
				background: #fff; padding: 6px; border-radius: 6px; box-shadow: 0 8px 28px rgba(0, 0, 0, .28);
			}
			#qot-fic-preview img { display: block; max-width: 340px; max-height: 340px; width: auto; height: auto; border-radius: 3px; }
			#qot-fic-preview .qot-fic-meta { font-size: 12px; color: #50575e; margin: 5px 2px 0; }
			#qot-fic-preview .qot-fic-meta:empty { display: none; }
			@media (hover: none) { #qot-fic-preview { display: none !important; } }
		</style>
		<?php
	}

	/** Bigger preview on hover or keyboard focus. Loads the larger image only when asked for. */
	public static function print_js() {
		if ( ! self::on_list_screen() ) {
			return;
		}
		?>
		<script id="qot-featured-image-column-js">
		( function () {
			var box = document.createElement( 'div' ), img = document.createElement( 'img' ), meta = document.createElement( 'div' );
			var current = null, lastX = 0, lastY = 0;
			box.id = 'qot-fic-preview';
			box.setAttribute( 'aria-hidden', 'true' );
			img.alt = '';
			meta.className = 'qot-fic-meta';
			box.appendChild( img );
			box.appendChild( meta );
			document.body.appendChild( box );

			function place( x, y ) {
				var r = box.getBoundingClientRect(), pad = 18, left = x + pad, top = y + pad;
				if ( left + r.width > window.innerWidth - 8 ) { left = Math.max( 8, x - r.width - pad ); }
				if ( top + r.height > window.innerHeight - 8 ) { top = Math.max( 8, window.innerHeight - r.height - 8 ); }
				box.style.left = left + 'px';
				box.style.top = top + 'px';
			}
			function show( link, x, y ) {
				var src = link.getAttribute( 'data-qot-preview' );
				if ( ! src ) { return; }
				current = link; lastX = x; lastY = y;
				img.src = src;
				meta.textContent = link.getAttribute( 'data-qot-info' ) || '';
				box.style.display = 'block';
				place( x, y );
			}
			function hide() {
				current = null;
				box.style.display = 'none';
				img.removeAttribute( 'src' );
			}
			function linkFrom( el ) {
				return el && el.closest ? el.closest( '.qot-fic-link' ) : null;
			}
			img.addEventListener( 'load', function () { if ( current ) { place( lastX, lastY ); } } );
			document.addEventListener( 'mouseover', function ( e ) {
				var link = linkFrom( e.target );
				if ( link && link !== current ) { show( link, e.clientX, e.clientY ); }
			} );
			document.addEventListener( 'mousemove', function ( e ) {
				if ( current ) { lastX = e.clientX; lastY = e.clientY; place( lastX, lastY ); }
			} );
			document.addEventListener( 'mouseout', function ( e ) {
				if ( current && ( ! e.relatedTarget || ! current.contains( e.relatedTarget ) ) ) { hide(); }
			} );
			document.addEventListener( 'focusin', function ( e ) {
				var link = linkFrom( e.target ), r;
				if ( link ) { r = link.getBoundingClientRect(); show( link, r.right - 10, r.top - 10 ); }
			} );
			document.addEventListener( 'focusout', function ( e ) { if ( linkFrom( e.target ) ) { hide(); } } );
			window.addEventListener( 'scroll', hide, { passive: true } );
		} )();
		</script>
		<?php
	}

	public static function row_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$links[] = '<a href="https://wordpress.org/support/plugin/' . self::SLUG . '/" target="_blank" rel="noopener">' . esc_html__( 'Support', 'qot-featured-image-column' ) . '</a>';
		}
		return $links;
	}
}

QOT_Featured_Image_Column::init();
