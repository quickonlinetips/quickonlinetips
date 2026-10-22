<?php
/**
 * Plugin Name:       Quick Feed Copyright
 * Plugin URI:        https://www.quickonlinetips.com/archives/simple-feed-copyright-wordpress-plugin/
 * Description:       Adds a copyright notice and a link back to the original article at the end of every post in your RSS and Atom feeds, so copied feeds still credit you. Choose the wording, or write your own.
 * Version:           2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-feed-copyright
 */

/*
 * Formerly called Simple Feed Copyright. The folder, this file's name and the
 * text domain keep the old name so existing sites update smoothly.
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SIMPLE_FEED_COPYRIGHT_OPTION', 'simple_feed_copyright' );

/* ------------------------------------------------------------------ *
 * Settings and their defaults
 * ------------------------------------------------------------------ */

/**
 * Choices for the copyright symbol: key => text shown in the feed.
 *
 * @return array<string, string>
 */
function simple_feed_copyright_symbols() {
	return array(
		'c'      => '©',
		'paren'  => '(c)',
		'word'   => __( 'Copyright', 'simple-feed-copyright' ),
		'word_c' => __( 'Copyright', 'simple-feed-copyright' ) . ' ©',
		'none'   => '',
	);
}

/**
 * Ready-made rights statements: key => text shown in the feed.
 *
 * @return array<string, string>
 */
function simple_feed_copyright_rights() {
	return array(
		'all'  => __( 'All Rights Reserved.', 'simple-feed-copyright' ),
		'some' => __( 'Some Rights Reserved.', 'simple-feed-copyright' ),
		'none' => '',
	);
}

/**
 * Settings as they are when nothing has been saved yet.
 * These give exactly the notice that version 1.2 always showed,
 * so updating the plugin changes nothing in anyone's feed.
 *
 * @return array
 */
function simple_feed_copyright_defaults() {
	return array(
		'symbol'         => 'c',
		'year'           => 'current',
		'start_year'     => 0,
		'name'           => 'site',
		'custom_name'    => '',
		'link_name'      => true,
		'rights'         => 'all',
		'custom_rights'  => '',
		'original'       => true,
		'original_label' => __( 'Original article:', 'simple-feed-copyright' ),
		'use_template'   => false,
		'template'       => simple_feed_copyright_default_template(),
		'latest'         => false,
		'latest_count'   => 3,
		'latest_heading' => __( 'More from {site}', 'simple-feed-copyright' ),
		'extra'          => '',
		'excerpts'       => false,
		'channel'        => true,
	);
}

/**
 * The starting text for "Write your own notice".
 *
 * @return string
 */
function simple_feed_copyright_default_template() {
	/* translators: Keep the words in {curly brackets} as they are. */
	return __( '<p>Read the original article, <a href="{link}">{title}</a>, at {site_link}.</p><p>{copyright}</p>', 'simple-feed-copyright' );
}

/**
 * The saved settings, with defaults for anything not saved yet.
 *
 * @return array
 */
function simple_feed_copyright_settings() {
	static $settings = null;

	if ( null === $settings ) {
		$saved    = get_option( SIMPLE_FEED_COPYRIGHT_OPTION, array() );
		$settings = array_merge( simple_feed_copyright_defaults(), is_array( $saved ) ? $saved : array() );

		// The settings page already cleans these, but check again in case the
		// option was changed some other way.
		foreach ( array( 'custom_rights', 'template', 'extra' ) as $key ) {
			$settings[ $key ] = wp_kses_post( (string) $settings[ $key ] );
		}
		foreach ( array( 'symbol' => simple_feed_copyright_symbols(), 'rights' => simple_feed_copyright_rights() + array( 'custom' => '' ) ) as $key => $allowed ) {
			if ( ! isset( $allowed[ $settings[ $key ] ] ) ) {
				$settings[ $key ] = simple_feed_copyright_defaults()[ $key ];
			}
		}
		$settings['latest_count'] = max( 1, min( 10, absint( $settings['latest_count'] ) ) );
	}

	return $settings;
}

/**
 * Check everything sent from the settings page before it is saved.
 *
 * @param mixed $input Values from the settings form.
 * @return array
 */
function simple_feed_copyright_sanitize( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = simple_feed_copyright_defaults();

	$pick = function ( $key, $allowed ) use ( $input, $defaults ) {
		return isset( $input[ $key ] ) && in_array( $input[ $key ], $allowed, true ) ? $input[ $key ] : $defaults[ $key ];
	};
	$text = function ( $key ) use ( $input ) {
		return isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : '';
	};
	$html = function ( $key ) use ( $input ) {
		return isset( $input[ $key ] ) ? trim( wp_kses_post( wp_unslash( $input[ $key ] ) ) ) : '';
	};

	$this_year  = (int) gmdate( 'Y' );
	$start_year = isset( $input['start_year'] ) ? absint( $input['start_year'] ) : 0;
	if ( $start_year < 1990 || $start_year > $this_year ) {
		$start_year = 0;
	}

	$count    = isset( $input['latest_count'] ) ? absint( $input['latest_count'] ) : 3;
	$template = $html( 'template' );

	return array(
		'symbol'         => $pick( 'symbol', array_keys( simple_feed_copyright_symbols() ) ),
		'year'           => $pick( 'year', array( 'current', 'post', 'range', 'none' ) ),
		'start_year'     => $start_year,
		'name'           => $pick( 'name', array( 'site', 'custom' ) ),
		'custom_name'    => $text( 'custom_name' ),
		'link_name'      => ! empty( $input['link_name'] ),
		'rights'         => $pick( 'rights', array( 'all', 'some', 'none', 'custom' ) ),
		'custom_rights'  => $html( 'custom_rights' ),
		'original'       => ! empty( $input['original'] ),
		'original_label' => $text( 'original_label' ),
		'use_template'   => ! empty( $input['use_template'] ),
		'template'       => '' !== $template ? $template : $defaults['template'],
		'latest'         => ! empty( $input['latest'] ),
		'latest_count'   => max( 1, min( 10, $count ) ),
		'latest_heading' => $text( 'latest_heading' ),
		'extra'          => $html( 'extra' ),
		'excerpts'       => ! empty( $input['excerpts'] ),
		'channel'        => ! empty( $input['channel'] ),
	);
}

/**
 * The year the site's first post was published, for the "range" choice.
 * Falls back to this year on a new site.
 *
 * @return int
 */
function simple_feed_copyright_first_year() {
	$first = get_posts(
		array(
			'numberposts'      => 1,
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'orderby'          => 'date',
			'order'            => 'ASC',
			'suppress_filters' => false,
		)
	);

	return $first ? (int) get_the_date( 'Y', $first[0] ) : (int) current_time( 'Y' );
}

/* ------------------------------------------------------------------ *
 * Building the notice
 * ------------------------------------------------------------------ */

/**
 * The copyright line, for example: © 2026 Blog Name. All Rights Reserved.
 *
 * @param array        $settings Settings from simple_feed_copyright_settings().
 * @param WP_Post|null $post     The feed item, for the year it was published.
 * @param bool         $links    False for plain text with no link.
 * @return string HTML, already escaped.
 */
function simple_feed_copyright_line( $settings, $post = null, $links = true ) {
	$symbols = simple_feed_copyright_symbols();
	$parts   = array();

	if ( '' !== $symbols[ $settings['symbol'] ] ) {
		$parts[] = esc_html( $symbols[ $settings['symbol'] ] );
	}

	$this_year = (int) current_time( 'Y' );
	switch ( $settings['year'] ) {
		case 'current':
			$parts[] = (string) $this_year;
			break;
		case 'post':
			$parts[] = $post ? get_the_date( 'Y', $post ) : (string) $this_year;
			break;
		case 'range':
			$start   = $settings['start_year'] ? (int) $settings['start_year'] : simple_feed_copyright_first_year();
			$parts[] = $start < $this_year ? $start . '&#8211;' . $this_year : (string) $this_year;
			break;
	}

	$name = 'custom' === $settings['name'] && '' !== $settings['custom_name'] ? $settings['custom_name'] : get_bloginfo( 'name' );
	if ( $links && $settings['link_name'] ) {
		$parts[] = '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $name ) . '</a>';
	} else {
		$parts[] = esc_html( $name );
	}

	$line = implode( ' ', $parts );

	if ( 'custom' === $settings['rights'] ) {
		$rights = $links ? $settings['custom_rights'] : wp_strip_all_tags( $settings['custom_rights'] );
	} else {
		$presets = simple_feed_copyright_rights();
		$rights  = esc_html( $presets[ $settings['rights'] ] );
	}

	return '' !== trim( $rights ) ? $line . '. ' . $rights : $line . '.';
}

/**
 * The words that can go in {curly brackets} in your own notice and the
 * extra text, with their values for one feed item. All values are escaped.
 *
 * @param array   $settings Settings.
 * @param WP_Post $post     The feed item.
 * @return array<string, string>
 */
function simple_feed_copyright_tags( $settings, $post ) {
	$site = get_bloginfo( 'name' );

	return array(
		'{copyright}' => simple_feed_copyright_line( $settings, $post ),
		'{title}'     => esc_html( get_the_title( $post ) ),
		'{link}'      => esc_url( get_permalink( $post ) ),
		'{site}'      => esc_html( $site ),
		'{site_url}'  => esc_url( home_url( '/' ) ),
		'{site_link}' => '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $site ) . '</a>',
		'{author}'    => esc_html( get_the_author_meta( 'display_name', $post->post_author ) ),
		'{date}'      => esc_html( get_the_date( '', $post ) ),
		'{year}'      => esc_html( (string) current_time( 'Y' ) ),
	);
}

/**
 * The latest published posts, looked up once per feed.
 *
 * @param int $count How many to show.
 * @return WP_Post[]
 */
function simple_feed_copyright_latest_posts( $count ) {
	static $posts = null;

	if ( null === $posts ) {
		// One more than needed, in case the feed item itself is among them.
		$posts = get_posts(
			array(
				'numberposts'      => $count + 1,
				'post_type'        => 'post',
				'post_status'      => 'publish',
				'has_password'     => false,
				'suppress_filters' => false,
			)
		);
	}

	return $posts;
}

/**
 * Everything added to the end of one feed item.
 *
 * @param WP_Post $post The feed item.
 * @return string HTML.
 */
function simple_feed_copyright_footer( $post ) {
	$settings = simple_feed_copyright_settings();
	$tags     = simple_feed_copyright_tags( $settings, $post );
	$html     = '';

	if ( $settings['use_template'] ) {
		$html .= strtr( $settings['template'], $tags );
	} else {
		if ( $settings['original'] ) {
			$label = '' !== $settings['original_label'] ? esc_html( $settings['original_label'] ) . ' ' : '';
			$html .= '<p>' . $label . '<a href="' . $tags['{link}'] . '" rel="bookmark">' . $tags['{title}'] . '</a></p>';
		}
		$html .= '<p>' . $tags['{copyright}'] . '</p>';
	}

	if ( $settings['latest'] ) {
		$items = '';
		$shown = 0;
		foreach ( simple_feed_copyright_latest_posts( $settings['latest_count'] ) as $latest ) {
			if ( $latest->ID === $post->ID || $shown >= $settings['latest_count'] ) {
				continue;
			}
			$items .= '<li><a href="' . esc_url( get_permalink( $latest ) ) . '">' . esc_html( get_the_title( $latest ) ) . '</a></li>';
			++$shown;
		}
		if ( $items ) {
			$heading = '' !== $settings['latest_heading'] ? '<p><strong>' . strtr( esc_html( $settings['latest_heading'] ), $tags ) . '</strong></p>' : '';
			$html   .= $heading . '<ul>' . $items . '</ul>';
		}
	}

	if ( '' !== $settings['extra'] ) {
		$html .= wpautop( strtr( $settings['extra'], $tags ) );
	}

	/**
	 * Change what is added to the end of a feed item.
	 *
	 * @param string  $html The notice and extras.
	 * @param WP_Post $post The feed item.
	 */
	return (string) apply_filters( 'simple_feed_copyright_footer', $html, $post );
}

/* ------------------------------------------------------------------ *
 * Adding it to the feeds
 * ------------------------------------------------------------------ */

/**
 * Add the notice to the end of each post in full-text feeds.
 * The function keeps its name from version 1.2, so any site that removed it with
 * remove_filter( 'the_content_feed', 'simplefeedcopyright' ) still can.
 *
 * @param string $content The post as it goes into the feed.
 * @return string
 */
function simplefeedcopyright( $content ) {
	$post = get_post();
	if ( ! $post ) {
		return $content;
	}
	return $content . simple_feed_copyright_footer( $post );
}
add_filter( 'the_content_feed', 'simplefeedcopyright' );

/**
 * Add the notice to summaries too, when the site sends summaries instead of
 * full text (Settings > Reading) and that choice is switched on.
 *
 * @param string $excerpt The summary as it goes into the feed.
 * @return string
 */
function simple_feed_copyright_excerpt( $excerpt ) {
	$settings = simple_feed_copyright_settings();
	$post     = get_post();

	if ( ! $settings['excerpts'] || ! get_option( 'rss_use_excerpt' ) || ! $post ) {
		return $excerpt;
	}
	return $excerpt . simple_feed_copyright_footer( $post );
}
add_filter( 'the_excerpt_rss', 'simple_feed_copyright_excerpt' );

/**
 * The plain-text copyright line for the feed as a whole, which feed readers
 * and directories read: <copyright> in RSS and <rights> in Atom.
 *
 * @return string
 */
function simple_feed_copyright_channel_text() {
	$settings = simple_feed_copyright_settings();
	$text     = wp_strip_all_tags( html_entity_decode( simple_feed_copyright_line( $settings, null, false ), ENT_QUOTES, 'UTF-8' ) );
	return esc_html( $text );
}

/**
 * <copyright> in RSS 2.0 feeds.
 */
function simple_feed_copyright_rss_tag() {
	if ( simple_feed_copyright_settings()['channel'] && ! is_comment_feed() ) {
		echo "\t<copyright>" . simple_feed_copyright_channel_text() . "</copyright>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}
add_action( 'rss2_head', 'simple_feed_copyright_rss_tag' );

/**
 * <rights> in Atom feeds.
 */
function simple_feed_copyright_atom_tag() {
	if ( simple_feed_copyright_settings()['channel'] && ! is_comment_feed() ) {
		echo "\t<rights type=\"text\">" . simple_feed_copyright_channel_text() . "</rights>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}
add_action( 'atom_head', 'simple_feed_copyright_atom_tag' );

/* ------------------------------------------------------------------ *
 * Settings page: Settings > Quick Feed Copyright
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds all settings.
 */
function simple_feed_copyright_register_setting() {
	register_setting(
		'simple_feed_copyright',
		SIMPLE_FEED_COPYRIGHT_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'simple_feed_copyright_sanitize',
		)
	);
}
add_action( 'init', 'simple_feed_copyright_register_setting' );

/**
 * Add the page to the Settings menu.
 */
function simple_feed_copyright_add_page() {
	$hook = add_options_page(
		__( 'Quick Feed Copyright', 'simple-feed-copyright' ),
		__( 'Quick Feed Copyright', 'simple-feed-copyright' ),
		'manage_options',
		'simple-feed-copyright',
		'simple_feed_copyright_render_page'
	);

	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				simple_feed_copyright_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'simple_feed_copyright_add_page' );

/**
 * Styles and the live preview script, on the settings page only.
 */
function simple_feed_copyright_admin_assets() {
	$css = '.qfc-wrap{display:flex;flex-wrap:wrap;gap:32px;align-items:flex-start}' .
		'.qfc-wrap form{max-width:640px;flex:1 1 480px}' .
		// Each section is a white card with its heading in a bar across the top.
		'.qfc-card{box-sizing:border-box;background:#fff;border:1px solid #c3c4c7;border-radius:4px;box-shadow:0 1px 1px rgba(0,0,0,.04);margin:0 0 20px;padding:0 20px 6px}' .
		'.qfc-card>h2{margin:0 -20px;padding:12px 20px;border-bottom:1px solid #dcdcde;background:#f0f6fc;border-radius:4px 4px 0 0;font-size:15px}' .
		// Heading colours change from card to card in a fixed order: blue, green, yellow, pink, purple,
		// then round again (qfc-c1 to qfc-c5, in page order). The preview card is always teal.
		'.qfc-c1>h2{background:#f0f6fc}.qfc-c2>h2{background:#edfaef}.qfc-c3>h2{background:#fcf9e8}' .
		'.qfc-c4>h2{background:#fcf0f1}.qfc-c5>h2{background:#f5f0fc}.qfc-preview>h2{background:#e8f6f5}' .
		// The preview is a live window, not a settings card: a teal frame with a faint dotted
		// background, an eye icon and a Live badge, with the preview itself sitting on top like a screen.
		'.qfc-preview{background:#1d6b65 radial-gradient(rgba(255,255,255,.12) 1px,transparent 1.5px) 0 0/16px 16px;border-color:#11433f;box-shadow:0 2px 8px rgba(0,0,0,.18)}' .
		'.qfc-preview>h2{display:flex;align-items:center;gap:8px;background:#155550;color:#f0fdfa;border-bottom-color:#2a8a82}' .
		'.qfc-preview>h2:before{content:"\f177";font:normal 20px/1 dashicons;color:#99f6e4}' .
		'.qfc-live{margin-left:auto;position:relative;padding:2px 8px 2px 18px;border:1px solid #99f6e4;border-radius:10px;background:rgba(153,246,228,.14);color:#ccfbf1;font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase}' .
		'.qfc-live:before{content:"";position:absolute;left:7px;top:50%;width:6px;height:6px;margin-top:-3px;border-radius:50%;background:#99f6e4}' .
		'.qfc-preview>.description,.qfc-preview>p{color:#e6f7f5}.qfc-preview>.description a,.qfc-preview>p a{color:#cfe9ff}.qfc-card.qfc-preview>.description{margin:12px 0 0}' .
		'.qfc-card .form-table{margin-top:4px}.qfc-card .form-table th{padding-left:0;width:150px}' .
		'.qfc-card .qfc-intro{margin:14px 0 0;color:#50575e}.qfc-card .description{margin:6px 0 0}.qfc-card>.description{margin:0 0 14px}' .
		'.qfc-card textarea{width:100%;font-family:Consolas,Monaco,monospace;font-size:13px}.qfc-sub{margin-top:8px}' .
		'.qfc-tags code{font-size:12px;padding:1px 4px}' .
		// The preview looks like a post in a feed reader.
		'.qfc-side{flex:1 1 400px;max-width:640px;position:sticky;top:46px;padding-bottom:20px;margin-bottom:0}' .
		'.qfc-reader{margin-top:20px;border:1px solid #dcdcde;border-radius:6px;padding:20px 22px;background:#fcfcfc;font:15px/1.6 Georgia,serif;color:#1d2327}' .
		'.qfc-reader .qfc-from{font:12px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#646970;text-transform:uppercase;letter-spacing:.04em}' .
		'.qfc-reader h3{font-size:20px;margin:6px 0 12px;line-height:1.3}.qfc-reader .qfc-body{color:#50575e}' .
		'.qfc-reader #qfc-footer{border-top:1px dashed #c3c4c7;margin-top:16px;padding-top:4px}.qfc-reader a{color:#2271b1}' .
		'.qfc-reader p{margin:10px 0}.qfc-reader ul{margin:6px 0 10px 20px;list-style:disc}.qfc-reader li{margin:2px 0}' .
		'@media (max-width:1279px){.qfc-wrap{gap:20px}.qfc-side{order:-1;position:static}}';

	wp_register_style( 'simple-feed-copyright-admin', false, array(), '2.0' );
	wp_enqueue_style( 'simple-feed-copyright-admin' );
	wp_add_inline_style( 'simple-feed-copyright-admin', $css );

	// Everything the preview needs to build the notice the same way the feed does.
	$sample = get_posts(
		array(
			'numberposts'      => 11,
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'has_password'     => false,
			'suppress_filters' => false,
		)
	);
	$first  = array_shift( $sample );
	$data   = array(
		'site'      => get_bloginfo( 'name' ),
		'siteUrl'   => home_url( '/' ),
		'thisYear'  => (int) current_time( 'Y' ),
		'symbols'   => simple_feed_copyright_symbols(),
		'rights'    => simple_feed_copyright_rights(),
		'post'      => array(
			'title'  => $first ? get_the_title( $first ) : __( 'How to Write a Great Blog Post', 'simple-feed-copyright' ),
			'link'   => $first ? get_permalink( $first ) : home_url( '/sample-post/' ),
			'author' => $first ? get_the_author_meta( 'display_name', $first->post_author ) : wp_get_current_user()->display_name,
			'date'   => $first ? get_the_date( '', $first ) : date_i18n( get_option( 'date_format' ) ),
			'year'   => $first ? (int) get_the_date( 'Y', $first ) : (int) current_time( 'Y' ),
		),
		'latest'    => array_map(
			function ( $p ) {
				return array(
					'title' => get_the_title( $p ),
					'link'  => get_permalink( $p ),
				);
			},
			$sample
		),
		'noLatest'  => __( '(Your latest posts will appear here once you publish more posts.)', 'simple-feed-copyright' ),
	);

	$script = <<<'JS'
( function () {
	var d = window.qfcData;
	var form = document.getElementById( 'qfc-form' );
	var footer = document.getElementById( 'qfc-footer' );

	function esc( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}
	function field( key ) {
		var el = form.elements[ 'simple_feed_copyright[' + key + ']' ];
		if ( ! el ) {
			return '';
		}
		return el.type === 'checkbox' ? el.checked : el.value;
	}
	function show( id, on ) {
		var el = document.getElementById( id );
		if ( el ) {
			el.style.display = on ? '' : 'none';
		}
	}

	function copyrightLine() {
		var parts = [];
		var symbol = d.symbols[ field( 'symbol' ) ];
		if ( symbol ) {
			parts.push( esc( symbol ) );
		}
		var year = field( 'year' );
		if ( year === 'current' ) {
			parts.push( d.thisYear );
		} else if ( year === 'post' ) {
			parts.push( d.post.year );
		} else if ( year === 'range' ) {
			var start = parseInt( field( 'start_year' ), 10 ) || d.thisYear;
			parts.push( start < d.thisYear ? start + '–' + d.thisYear : d.thisYear );
		}
		var name = field( 'name' ) === 'custom' && field( 'custom_name' ).trim() ? field( 'custom_name' ) : d.site;
		parts.push( field( 'link_name' ) ? '<a href="' + esc( d.siteUrl ) + '">' + esc( name ) + '</a>' : esc( name ) );

		var rights = field( 'rights' ) === 'custom' ? field( 'custom_rights' ) : esc( d.rights[ field( 'rights' ) ] );
		return parts.join( ' ' ) + ( rights.trim() ? '. ' + rights : '.' );
	}

	function fill( text, tags ) {
		Object.keys( tags ).forEach( function ( key ) {
			text = text.split( key ).join( tags[ key ] );
		} );
		return text;
	}

	function update() {
		var tags = {
			'{copyright}': copyrightLine(),
			'{title}': esc( d.post.title ),
			'{link}': esc( d.post.link ),
			'{site}': esc( d.site ),
			'{site_url}': esc( d.siteUrl ),
			'{site_link}': '<a href="' + esc( d.siteUrl ) + '">' + esc( d.site ) + '</a>',
			'{author}': esc( d.post.author ),
			'{date}': esc( d.post.date ),
			'{year}': d.thisYear
		};
		var html = '';
		var own = field( 'use_template' );

		if ( own ) {
			html += fill( field( 'template' ), tags );
		} else {
			if ( field( 'original' ) ) {
				var label = field( 'original_label' ).trim();
				html += '<p>' + ( label ? esc( label ) + ' ' : '' ) + '<a href="' + tags[ '{link}' ] + '">' + tags[ '{title}' ] + '</a></p>';
			}
			html += '<p>' + tags[ '{copyright}' ] + '</p>';
		}

		if ( field( 'latest' ) ) {
			var count = Math.max( 1, Math.min( 10, parseInt( field( 'latest_count' ), 10 ) || 3 ) );
			var items = d.latest.slice( 0, count ).map( function ( p ) {
				return '<li><a href="' + esc( p.link ) + '">' + esc( p.title ) + '</a></li>';
			} ).join( '' );
			var heading = field( 'latest_heading' ).trim();
			html += ( heading ? '<p><strong>' + fill( esc( heading ), tags ) + '</strong></p>' : '' ) +
				( items ? '<ul>' + items + '</ul>' : '<p><em>' + esc( d.noLatest ) + '</em></p>' );
		}

		var extra = field( 'extra' ).trim();
		if ( extra ) {
			html += fill( extra, tags ).split( /\n\s*\n/ ).map( function ( para ) {
				return /^\s*<(p|ul|ol|div|h\d|blockquote)[\s>]/i.test( para ) ? para : '<p>' + para.replace( /\n/g, '<br>' ) + '</p>';
			} ).join( '' );
		}

		footer.innerHTML = html;

		// Show only the settings that matter for the current choices.
		show( 'qfc-row-start', field( 'year' ) === 'range' );
		show( 'qfc-custom-name', field( 'name' ) === 'custom' );
		show( 'qfc-custom-rights', field( 'rights' ) === 'custom' );
		show( 'qfc-row-label', field( 'original' ) );
		show( 'qfc-template-box', own );
		show( 'qfc-original-card', ! own );
		show( 'qfc-latest-more', field( 'latest' ) );
	}

	// Links in the preview are just for show.
	footer.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( 'a' ) ) {
			e.preventDefault();
		}
	} );
	form.addEventListener( 'input', update );
	form.addEventListener( 'change', update );
	update();
} )();
JS;

	wp_register_script( 'simple-feed-copyright-admin', false, array(), '2.0', true );
	wp_enqueue_script( 'simple-feed-copyright-admin' );
	wp_add_inline_script( 'simple-feed-copyright-admin', 'window.qfcData = ' . wp_json_encode( $data ) . ';' . $script );
}

/**
 * The settings page itself.
 */
function simple_feed_copyright_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$s    = simple_feed_copyright_settings();
	$name = SIMPLE_FEED_COPYRIGHT_OPTION;
	$f    = function ( $key ) use ( $name ) {
		return esc_attr( $name . '[' . $key . ']' );
	};

	$start_year = $s['start_year'] ? (int) $s['start_year'] : simple_feed_copyright_first_year();
	$symbols    = simple_feed_copyright_symbols();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Quick Feed Copyright', 'simple-feed-copyright' ); ?></h1>
		<p><?php esc_html_e( 'Choose the notice added to the end of every post in your RSS and Atom feeds. If someone copies your feed onto their site, the notice and its links still credit you. The preview updates as you go.', 'simple-feed-copyright' ); ?></p>

		<div class="qfc-wrap">
			<form method="post" action="options.php" id="qfc-form">
				<?php settings_fields( 'simple_feed_copyright' ); ?>

				<div class="qfc-card qfc-c1">
					<h2><?php esc_html_e( 'Copyright line', 'simple-feed-copyright' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="qfc-symbol"><?php esc_html_e( 'Symbol', 'simple-feed-copyright' ); ?></label></th>
							<td>
								<select id="qfc-symbol" name="<?php echo $f( 'symbol' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
									<?php foreach ( $symbols as $key => $symbol ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $s['symbol'], $key ); ?>><?php echo '' !== $symbol ? esc_html( $symbol ) : esc_html__( 'None', 'simple-feed-copyright' ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="qfc-year"><?php esc_html_e( 'Year', 'simple-feed-copyright' ); ?></label></th>
							<td>
								<select id="qfc-year" name="<?php echo $f( 'year' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
									<option value="current" <?php selected( $s['year'], 'current' ); ?>><?php esc_html_e( 'This year', 'simple-feed-copyright' ); ?></option>
									<option value="post" <?php selected( $s['year'], 'post' ); ?>><?php esc_html_e( 'Year each post was published', 'simple-feed-copyright' ); ?></option>
									<option value="range" <?php selected( $s['year'], 'range' ); ?>><?php esc_html_e( 'From a start year to this year', 'simple-feed-copyright' ); ?></option>
									<option value="none" <?php selected( $s['year'], 'none' ); ?>><?php esc_html_e( 'No year', 'simple-feed-copyright' ); ?></option>
								</select>
							</td>
						</tr>
						<tr id="qfc-row-start">
							<th scope="row"><label for="qfc-start"><?php esc_html_e( 'Start year', 'simple-feed-copyright' ); ?></label></th>
							<td>
								<input type="number" id="qfc-start" class="small-text" min="1990" max="<?php echo esc_attr( current_time( 'Y' ) ); ?>" name="<?php echo $f( 'start_year' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $start_year ); ?>" />
								<p class="description"><?php esc_html_e( 'Filled in with the year of your first post.', 'simple-feed-copyright' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="qfc-name"><?php esc_html_e( 'Name', 'simple-feed-copyright' ); ?></label></th>
							<td>
								<select id="qfc-name" name="<?php echo $f( 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
									<option value="site" <?php selected( $s['name'], 'site' ); ?>>
										<?php
										/* translators: %s: the site title. */
										printf( esc_html__( 'Site title (%s)', 'simple-feed-copyright' ), esc_html( get_bloginfo( 'name' ) ) );
										?>
									</option>
									<option value="custom" <?php selected( $s['name'], 'custom' ); ?>><?php esc_html_e( 'Another name, such as your company', 'simple-feed-copyright' ); ?></option>
								</select>
								<div id="qfc-custom-name" class="qfc-sub">
									<input type="text" class="regular-text" name="<?php echo $f( 'custom_name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $s['custom_name'] ); ?>" aria-label="<?php esc_attr_e( 'Name', 'simple-feed-copyright' ); ?>" />
								</div>
								<p class="qfc-sub">
									<label>
										<input type="checkbox" name="<?php echo $f( 'link_name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="1" <?php checked( $s['link_name'] ); ?> />
										<?php esc_html_e( 'Link the name to your home page', 'simple-feed-copyright' ); ?>
									</label>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="qfc-rights"><?php esc_html_e( 'Rights', 'simple-feed-copyright' ); ?></label></th>
							<td>
								<select id="qfc-rights" name="<?php echo $f( 'rights' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
									<option value="all" <?php selected( $s['rights'], 'all' ); ?>><?php esc_html_e( 'All Rights Reserved.', 'simple-feed-copyright' ); ?></option>
									<option value="some" <?php selected( $s['rights'], 'some' ); ?>><?php esc_html_e( 'Some Rights Reserved.', 'simple-feed-copyright' ); ?></option>
									<option value="custom" <?php selected( $s['rights'], 'custom' ); ?>><?php esc_html_e( 'My own wording', 'simple-feed-copyright' ); ?></option>
									<option value="none" <?php selected( $s['rights'], 'none' ); ?>><?php esc_html_e( 'Nothing', 'simple-feed-copyright' ); ?></option>
								</select>
								<div id="qfc-custom-rights" class="qfc-sub">
									<input type="text" class="large-text" name="<?php echo $f( 'custom_rights' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $s['custom_rights'] ); ?>" placeholder="<?php esc_attr_e( 'For example: Licensed under CC BY 4.0.', 'simple-feed-copyright' ); ?>" aria-label="<?php esc_attr_e( 'Rights wording', 'simple-feed-copyright' ); ?>" />
									<p class="description"><?php esc_html_e( 'Links are allowed, for example to a Creative Commons licence.', 'simple-feed-copyright' ); ?></p>
								</div>
							</td>
						</tr>
					</table>
				</div>

				<div class="qfc-card qfc-c2" id="qfc-original-card">
					<h2><?php esc_html_e( 'Link to the original article', 'simple-feed-copyright' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Show link', 'simple-feed-copyright' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?php echo $f( 'original' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="1" <?php checked( $s['original'] ); ?> />
									<?php esc_html_e( 'Add the post title, linked to the post, above the copyright line', 'simple-feed-copyright' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'When a site copies your feed, this link tells readers and search engines where the article came from.', 'simple-feed-copyright' ); ?></p>
							</td>
						</tr>
						<tr id="qfc-row-label">
							<th scope="row"><label for="qfc-label"><?php esc_html_e( 'Words before it', 'simple-feed-copyright' ); ?></label></th>
							<td><input type="text" id="qfc-label" class="regular-text" name="<?php echo $f( 'original_label' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $s['original_label'] ); ?>" /></td>
						</tr>
					</table>
				</div>

				<div class="qfc-card qfc-c3">
					<h2><?php esc_html_e( 'Write your own notice', 'simple-feed-copyright' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Own notice', 'simple-feed-copyright' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?php echo $f( 'use_template' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="1" <?php checked( $s['use_template'] ); ?> />
									<?php esc_html_e( 'Use my own wording instead of the link and copyright line above', 'simple-feed-copyright' ); ?>
								</label>
							</td>
						</tr>
					</table>
					<div id="qfc-template-box">
						<textarea name="<?php echo $f( 'template' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" rows="5" aria-label="<?php esc_attr_e( 'Your own notice', 'simple-feed-copyright' ); ?>"><?php echo esc_textarea( $s['template'] ); ?></textarea>
					</div>
					<p class="description qfc-tags">
						<?php esc_html_e( 'You can use HTML and these words in your own notice, the latest posts heading and the extra text:', 'simple-feed-copyright' ); ?>
						<code>{copyright}</code> <?php esc_html_e( 'the copyright line,', 'simple-feed-copyright' ); ?>
						<code>{title}</code> <code>{link}</code> <?php esc_html_e( 'the post title and address,', 'simple-feed-copyright' ); ?>
						<code>{site}</code> <code>{site_url}</code> <code>{site_link}</code> <?php esc_html_e( 'your site,', 'simple-feed-copyright' ); ?>
						<code>{author}</code> <code>{date}</code> <code>{year}</code>.
					</p>
				</div>

				<div class="qfc-card qfc-c4">
					<h2><?php esc_html_e( 'Extras below the notice', 'simple-feed-copyright' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Latest posts', 'simple-feed-copyright' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?php echo $f( 'latest' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="1" <?php checked( $s['latest'] ); ?> />
									<?php esc_html_e( 'List your latest posts, linked', 'simple-feed-copyright' ); ?>
								</label>
								<div id="qfc-latest-more">
									<p class="qfc-sub">
										<label>
											<?php esc_html_e( 'How many:', 'simple-feed-copyright' ); ?>
											<input type="number" class="small-text" min="1" max="10" name="<?php echo $f( 'latest_count' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $s['latest_count'] ); ?>" />
										</label>
									</p>
									<p class="qfc-sub">
										<label>
											<?php esc_html_e( 'Heading:', 'simple-feed-copyright' ); ?>
											<input type="text" class="regular-text" name="<?php echo $f( 'latest_heading' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $s['latest_heading'] ); ?>" />
										</label>
									</p>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="qfc-extra"><?php esc_html_e( 'Extra text', 'simple-feed-copyright' ); ?></label></th>
							<td>
								<textarea id="qfc-extra" name="<?php echo $f( 'extra' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" rows="4"><?php echo esc_textarea( $s['extra'] ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Anything else to add, such as a newsletter link or a short message. Leave empty for none.', 'simple-feed-copyright' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<div class="qfc-card qfc-c5">
					<h2><?php esc_html_e( 'Where it appears', 'simple-feed-copyright' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Summary feeds', 'simple-feed-copyright' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?php echo $f( 'excerpts' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="1" <?php checked( $s['excerpts'] ); ?> />
									<?php esc_html_e( 'Also add it when your feed shows summaries instead of full posts', 'simple-feed-copyright' ); ?>
								</label>
								<p class="description">
									<?php
									printf(
										/* translators: %s: link to the Reading settings page. */
										esc_html__( 'Full-text feeds always get it. Choose full text or summary in %s.', 'simple-feed-copyright' ),
										'<a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '">' . esc_html__( 'Settings > Reading', 'simple-feed-copyright' ) . '</a>'
									);
									?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Feed details', 'simple-feed-copyright' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?php echo $f( 'channel' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="1" <?php checked( $s['channel'] ); ?> />
									<?php esc_html_e( 'Also add the copyright line to the feed\'s own details, which feed readers and directories read', 'simple-feed-copyright' ); ?>
								</label>
							</td>
						</tr>
					</table>
				</div>

				<?php submit_button(); ?>
			</form>

			<div class="qfc-side qfc-card qfc-preview">
				<h2><?php esc_html_e( 'Preview', 'simple-feed-copyright' ); ?> <span class="qfc-live"><?php esc_html_e( 'Live', 'simple-feed-copyright' ); ?></span></h2>
				<div class="qfc-reader">
					<div class="qfc-from"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
					<?php
					$preview_post = get_posts(
						array(
							'numberposts'      => 1,
							'post_type'        => 'post',
							'post_status'      => 'publish',
							'has_password'     => false,
							'suppress_filters' => false,
						)
					);
					?>
					<h3><?php echo esc_html( $preview_post ? get_the_title( $preview_post[0] ) : __( 'How to Write a Great Blog Post', 'simple-feed-copyright' ) ); ?></h3>
					<p class="qfc-body"><?php esc_html_e( 'Your post\'s text appears here, followed by the notice.', 'simple-feed-copyright' ); ?></p>
					<div id="qfc-footer"></div>
				</div>
				<p class="description"><?php esc_html_e( 'This is how the end of each post looks in a feed reader. The notice is added after every post in your feed.', 'simple-feed-copyright' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Add a "Settings" link next to Deactivate on the Plugins screen.
 *
 * @param string[] $links Existing links.
 * @return string[]
 */
function simple_feed_copyright_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=simple-feed-copyright' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'simple-feed-copyright' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'simple_feed_copyright_action_links' );

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function simple_feed_copyright_uninstall() {
	delete_option( SIMPLE_FEED_COPYRIGHT_OPTION );
}
register_uninstall_hook( __FILE__, 'simple_feed_copyright_uninstall' );
