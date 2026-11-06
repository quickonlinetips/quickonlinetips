<?php
/**
 * Plugin Name:       QOT Focus Mode
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       Adds a Focus button to your posts. One click hides the header, sidebars, footer and everything else, leaving just the post to read. One more click brings the page back. No setup needed.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-focus-mode
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_FOCUS_MODE_OPTION', 'qot_focus_mode' );
define( 'QOT_FOCUS_MODE_VERSION', '1.0' );

require_once __DIR__ . '/includes/admin-common.php';

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings. The button shows at the top of every post as soon as
 * the plugin is activated.
 *
 * @return array
 */
function qot_focus_mode_defaults() {
	return array(
		'show_on'  => 'posts',
		'place'    => 'top',
		'align'    => 'right',
		'style'    => 'both',
		'text_on'  => __( 'Focus', 'qot-focus-mode' ),
		'text_off' => __( 'Exit focus', 'qot-focus-mode' ),
		'bg'       => '#1d2327',
		'fg'       => '#ffffff',
		'center'   => true,
		'width'    => 760,
		'remember' => false,
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_focus_mode_settings() {
	$saved = get_option( QOT_FOCUS_MODE_OPTION, array() );
	return array_merge( qot_focus_mode_defaults(), is_array( $saved ) ? $saved : array() );
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_focus_mode_sanitize( $input ) {
	$d     = qot_focus_mode_defaults();
	$input = is_array( $input ) ? $input : array();
	$pick  = function ( $key, $allowed ) use ( $input, $d ) {
		return ( isset( $input[ $key ] ) && in_array( $input[ $key ], $allowed, true ) ) ? $input[ $key ] : $d[ $key ];
	};
	$text  = function ( $key ) use ( $input, $d ) {
		$value = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
		return '' === $value ? $d[ $key ] : $value;
	};
	$color = function ( $key ) use ( $input, $d ) {
		$value = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
		return $value ? $value : $d[ $key ];
	};

	return array(
		'show_on'  => $pick( 'show_on', array( 'posts', 'pages', 'both' ) ),
		'place'    => $pick( 'place', array( 'top', 'float' ) ),
		'align'    => $pick( 'align', array( 'left', 'right' ) ),
		'style'    => $pick( 'style', array( 'text', 'icon', 'both' ) ),
		'text_on'  => $text( 'text_on' ),
		'text_off' => $text( 'text_off' ),
		'bg'       => $color( 'bg' ),
		'fg'       => $color( 'fg' ),
		'center'   => ! empty( $input['center'] ),
		'width'    => isset( $input['width'] ) ? min( 1400, max( 480, absint( $input['width'] ) ) ) : $d['width'],
		'remember' => ! empty( $input['remember'] ),
	);
}

/* ------------------------------------------------------------------ *
 * The button on the site
 * ------------------------------------------------------------------ */

/**
 * Is this a post or page that should get the button?
 *
 * @return bool
 */
function qot_focus_mode_wanted() {
	$s     = qot_focus_mode_settings();
	$types = array(
		'posts' => array( 'post' ),
		'pages' => array( 'page' ),
		'both'  => array( 'post', 'page' ),
	);
	$show  = is_singular( $types[ $s['show_on'] ] ) && ! is_feed() && ! is_embed();

	/**
	 * Filters whether the Focus button appears on the current page.
	 *
	 * @param bool $show True to show the button.
	 */
	return (bool) apply_filters( 'qot_focus_mode_show', $show );
}

/**
 * The button's HTML.
 *
 * @return string
 */
function qot_focus_mode_button() {
	$s    = qot_focus_mode_settings();
	$icon = '<svg class="qfm-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 8V3h5M16 3h5v5M21 16v5h-5M8 21H3v-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$text = '<span class="qfm-text">' . esc_html( $s['text_on'] ) . '</span>';
	if ( 'icon' === $s['style'] ) {
		$inner = $icon . '<span class="qfm-sr">' . esc_html( $s['text_on'] ) . '</span>';
	} elseif ( 'text' === $s['style'] ) {
		$inner = $text;
	} else {
		$inner = $icon . $text;
	}
	return '<button type="button" class="qfm-btn" aria-pressed="false" title="' . esc_attr( $s['text_on'] ) . '">' . $inner . '</button>';
}

/**
 * Put the button at the top of the post text.
 *
 * @param string $content Post content.
 * @return string
 */
function qot_focus_mode_content( $content ) {
	if ( ! in_the_loop() || ! is_main_query() || get_the_ID() !== get_queried_object_id() || ! qot_focus_mode_wanted() ) {
		return $content;
	}
	$s = qot_focus_mode_settings();
	return '<div class="qfm-bar qfm-' . esc_attr( $s['align'] ) . '">' . qot_focus_mode_button() . '</div>' . $content;
}

/**
 * Or float it in a corner of the screen.
 */
function qot_focus_mode_floating() {
	if ( ! qot_focus_mode_wanted() ) {
		return;
	}
	$s = qot_focus_mode_settings();
	echo '<div class="qfm-float qfm-' . esc_attr( $s['align'] ) . '">' . qot_focus_mode_button() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in qot_focus_mode_button().
}

/**
 * Hook the button in, and add its small style and script, only on pages that show it.
 */
function qot_focus_mode_setup() {
	if ( is_admin() || ! qot_focus_mode_wanted() ) {
		return;
	}
	$s = qot_focus_mode_settings();
	if ( 'float' === $s['place'] ) {
		add_action( 'wp_footer', 'qot_focus_mode_floating', 5 );
	} else {
		add_filter( 'the_content', 'qot_focus_mode_content', 1 );
	}
	add_action( 'wp_enqueue_scripts', 'qot_focus_mode_assets' );
}
add_action( 'template_redirect', 'qot_focus_mode_setup' );

/**
 * The front-end CSS and script, inline (a few hundred bytes each).
 */
function qot_focus_mode_assets() {
	$s = qot_focus_mode_settings();

	wp_register_style( 'qot-focus-mode', false, array(), QOT_FOCUS_MODE_VERSION );
	wp_enqueue_style( 'qot-focus-mode' );
	$css = ':root{--qfm-bg:' . $s['bg'] . ';--qfm-fg:' . $s['fg'] . ';--qfm-width:' . (int) $s['width'] . 'px}' .
		'.qfm-bar{display:flex;margin:0 0 1em}.qfm-bar.qfm-right{justify-content:flex-end}' .
		'.qfm-btn{display:inline-flex;align-items:center;gap:6px;margin:0;padding:6px 14px;border:0;border-radius:999px;background:var(--qfm-bg);color:var(--qfm-fg);font:600 14px/1.3 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.2)}' .
		'.qfm-btn:hover{filter:brightness(1.15)}.qfm-btn:focus-visible{outline:2px solid var(--qfm-bg);outline-offset:2px}' .
		'.qfm-icon{width:16px;height:16px;flex:none}.qfm-sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}' .
		'.qfm-float{position:fixed;bottom:20px;z-index:99999}.qfm-float.qfm-right{right:20px}.qfm-float.qfm-left{left:20px}.qfm-float .qfm-btn{padding:10px 16px;box-shadow:0 4px 14px rgba(0,0,0,.25)}' .
		'[data-qfm-hide]{display:none!important}html.qfm-on{margin-top:0!important}html.qfm-on body{padding-top:1.5em}';
	if ( $s['center'] ) {
		$css .= 'html.qfm-on [data-qfm-path]{float:none!important;width:auto!important;max-width:none!important;margin-left:auto!important;margin-right:auto!important;grid-column:1/-1!important;flex:1 1 auto!important}' .
			'html.qfm-on [data-qfm-anchor]{float:none!important;width:auto!important;max-width:var(--qfm-width)!important;margin-left:auto!important;margin-right:auto!important;grid-column:1/-1!important;flex:1 1 auto!important}';
	}
	wp_add_inline_style( 'qot-focus-mode', $css );

	wp_register_script( 'qot-focus-mode', false, array(), QOT_FOCUS_MODE_VERSION, true );
	wp_enqueue_script( 'qot-focus-mode' );
	wp_add_inline_script(
		'qot-focus-mode',
		'window.qfmData=' . wp_json_encode(
			array(
				'on'       => $s['text_on'],
				'off'      => $s['text_off'],
				'remember' => (bool) $s['remember'],
			)
		) . ';' . qot_focus_mode_front_script()
	);
}

/**
 * The front-end script. It finds the post on the page, then walks up from it
 * to the page body and hides everything beside that path, so it works with
 * any theme. Pressing the button again, or Escape, shows it all again.
 *
 * @return string
 */
function qot_focus_mode_front_script() {
	return <<<'JS'
( function () {
	var data = window.qfmData || {};
	var btn = document.querySelector( '.qfm-btn' );
	if ( ! btn ) {
		return;
	}
	var root = document.documentElement;
	var on = false;
	var marked = [];
	function findPost() {
		if ( ! btn.closest( '.qfm-float' ) ) {
			return btn.closest( 'article' ) || btn.closest( 'main' ) || btn.parentNode.parentNode;
		}
		return document.querySelector( 'main article, article.post, article.page, article' ) || document.querySelector( 'main' ) || document.querySelector( '.entry-content, .wp-block-post-content' );
	}
	function mark( el, name ) {
		el.setAttribute( name, '' );
		marked.push( [ el, name ] );
	}
	function setText( pressed ) {
		var label = pressed ? data.off : data.on;
		var text = btn.querySelector( '.qfm-text, .qfm-sr' );
		if ( text ) {
			text.textContent = label;
		}
		btn.title = label;
		btn.setAttribute( 'aria-pressed', pressed ? 'true' : 'false' );
	}
	function focusOn() {
		var post = findPost();
		if ( ! post ) {
			return;
		}
		mark( post, 'data-qfm-anchor' );
		var el = post;
		while ( el && el.parentElement && el !== document.body ) {
			var parent = el.parentElement;
			Array.prototype.forEach.call( parent.children, function ( child ) {
				if ( child === el || child.contains( btn ) || /^(SCRIPT|STYLE|LINK|TEMPLATE|NOSCRIPT)$/.test( child.tagName ) ) {
					return;
				}
				mark( child, 'data-qfm-hide' );
			} );
			if ( parent !== document.body ) {
				mark( parent, 'data-qfm-path' );
			}
			el = parent;
		}
		root.classList.add( 'qfm-on' );
		on = true;
		setText( true );
		window.scrollTo( 0, Math.max( 0, post.getBoundingClientRect().top + window.pageYOffset - 20 ) );
	}
	function focusOff() {
		marked.forEach( function ( m ) {
			m[ 0 ].removeAttribute( m[ 1 ] );
		} );
		marked = [];
		root.classList.remove( 'qfm-on' );
		on = false;
		setText( false );
		btn.scrollIntoView( { block: 'center' } );
	}
	function save() {
		if ( ! data.remember ) {
			return;
		}
		try {
			window.localStorage.setItem( 'qfm', on ? '1' : '0' );
		} catch ( e ) {}
	}
	btn.addEventListener( 'click', function () {
		if ( on ) {
			focusOff();
		} else {
			focusOn();
		}
		save();
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( on && 'Escape' === e.key ) {
			focusOff();
			save();
		}
	} );
	if ( data.remember ) {
		try {
			if ( '1' === window.localStorage.getItem( 'qfm' ) ) {
				focusOn();
			}
		} catch ( e ) {}
	}
}() );
JS;
}

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_focus_mode_register_setting() {
	register_setting(
		'qot_focus_mode',
		QOT_FOCUS_MODE_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_focus_mode_sanitize',
			'default'           => qot_focus_mode_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_focus_mode_register_setting' );

/**
 * Add the page under Settings, with its style and script on that page only.
 */
function qot_focus_mode_add_page() {
	$hook = add_options_page( __( 'QOT Focus Mode', 'qot-focus-mode' ), __( 'QOT Focus Mode', 'qot-focus-mode' ), 'manage_options', 'qot-focus-mode', 'qot_focus_mode_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_focus_mode_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_focus_mode_add_page' );

/**
 * Style and live preview script for the settings page.
 */
function qot_focus_mode_admin_assets() {
	wp_register_style( 'qot-focus-mode-admin', false, array(), QOT_FOCUS_MODE_VERSION );
	wp_enqueue_style( 'qot-focus-mode-admin' );
	wp_add_inline_style(
		'qot-focus-mode-admin',
		qot_focus_mode_admin_base_css() .
		'#qfm-demo{font:13px/1.5 -apple-system,"Segoe UI",Roboto,sans-serif}#qfm-demo .d-head{background:#2c3338;color:#fff;font-weight:700;padding:10px 12px;border-radius:4px;margin:0 0 10px}' .
		'#qfm-demo .d-cols{display:flex;gap:12px}#qfm-demo .d-post{flex:1 1 auto;min-width:0}#qfm-demo .d-side{flex:0 0 30%;background:#f6f7f7;border-radius:4px;padding:8px}#qfm-demo .d-side i,#qfm-demo .d-post i{display:block;height:7px;background:#dcdcde;border-radius:3px;margin:0 0 7px}' .
		'#qfm-demo .d-title{font-size:17px;font-weight:700;margin:0 0 8px}#qfm-demo .d-foot{background:#f0f0f1;color:#50575e;padding:10px 12px;border-radius:4px;margin:10px 0 0}' .
		'#qfm-demo .d-bar{display:flex;margin:0 0 10px}#qfm-demo .d-bar.right{justify-content:flex-end}#qfm-demo .d-wrap{position:relative;min-height:240px}' .
		'#qfm-demo .d-float{position:absolute;bottom:4px}#qfm-demo .d-float.right{right:4px}#qfm-demo .d-float.left{left:4px}' .
		'#qfm-demo .qfm-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:0;border-radius:999px;font:600 13px/1.3 inherit;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.2)}#qfm-demo .qfm-icon{width:15px;height:15px}' .
		'#qfm-demo.is-on .d-head,#qfm-demo.is-on .d-side,#qfm-demo.is-on .d-foot{display:none}#qfm-demo.is-on .d-post{max-width:80%;margin:0 auto}'
	);
	wp_register_script( 'qot-focus-mode-admin', false, array(), QOT_FOCUS_MODE_VERSION, true );
	wp_enqueue_script( 'qot-focus-mode-admin' );
	wp_add_inline_script(
		'qot-focus-mode-admin',
		'window.qfmAdmin=' . wp_json_encode(
			array(
				'icon'  => '<svg class="qfm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 8V3h5M16 3h5v5M21 16v5h-5M8 21H3v-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
				'title' => __( 'How to brew a perfect cup of tea', 'qot-focus-mode' ),
				'site'  => get_bloginfo( 'name' ),
				'foot'  => __( 'Site footer', 'qot-focus-mode' ),
			)
		) . ';' . qot_focus_mode_admin_script()
	);
}

/**
 * Builds the preview from the form, the same way the PHP builds the real button.
 *
 * @return string
 */
function qot_focus_mode_admin_script() {
	return <<<'JS'
( function () {
	var form = document.getElementById( 'qfm-form' );
	var demo = document.getElementById( 'qfm-demo' );
	var data = window.qfmAdmin;
	if ( ! form || ! demo ) {
		return;
	}
	var on = false;
	function esc( t ) {
		return String( t ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[ c ];
		} );
	}
	function val( name ) {
		var el = form.querySelector( '[name="qot_focus_mode[' + name + ']"]:checked' ) || form.querySelector( '[name="qot_focus_mode[' + name + ']"]:not([type=radio])' );
		return el ? el.value : '';
	}
	function draw() {
		var style = val( 'style' );
		var label = esc( on ? val( 'text_off' ) : val( 'text_on' ) );
		var inner = 'icon' === style ? data.icon : ( 'text' === style ? label : data.icon + label );
		var button = '<button type="button" class="qfm-btn" style="background:' + esc( val( 'bg' ) ) + ';color:' + esc( val( 'fg' ) ) + '" title="' + label + '">' + inner + '</button>';
		var align = val( 'align' );
		var lines = '<i></i><i></i><i style="width:80%"></i><i></i><i style="width:60%"></i>';
		var top = 'top' === val( 'place' );
		demo.className = on ? 'is-on' : '';
		demo.innerHTML = '<div class="d-wrap"><div class="d-head">' + esc( data.site ) + '</div><div class="d-cols"><div class="d-post"><div class="d-title">' + esc( data.title ) + '</div>' +
			( top ? '<div class="d-bar ' + align + '">' + button + '</div>' : '' ) + lines + lines + '</div><div class="d-side"><i></i><i></i><i></i><i></i></div></div><div class="d-foot">' + esc( data.foot ) + '</div>' +
			( top ? '' : '<div class="d-float ' + align + '">' + button + '</div>' ) + '</div>';
	}
	demo.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '.qfm-btn' ) ) {
			on = ! on;
			draw();
		}
	} );
	form.addEventListener( 'input', draw );
	form.addEventListener( 'change', draw );
	draw();
}() );
JS;
}

/**
 * Print one radio group.
 *
 * @param string $key     Setting key.
 * @param array  $choices Value => label.
 * @param string $current Saved value.
 */
function qot_focus_mode_radios( $key, $choices, $current ) {
	foreach ( $choices as $value => $label ) {
		printf(
			'<label style="margin-right:14px"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label>',
			esc_attr( QOT_FOCUS_MODE_OPTION . '[' . $key . ']' ),
			esc_attr( $value ),
			checked( $current, $value, false ),
			esc_html( $label )
		);
	}
}

/**
 * The settings page.
 */
function qot_focus_mode_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = qot_focus_mode_settings();
	$n = QOT_FOCUS_MODE_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT Focus Mode', 'qot-focus-mode' ); ?></h1>
		<p><?php esc_html_e( 'A Focus button on your posts hides everything except the post, for calm long reads. The preview updates as you go; click its button to try it.', 'qot-focus-mode' ); ?></p>
		<div class="qfm-wrap">
			<form method="post" action="options.php" id="qfm-form">
				<?php settings_fields( 'qot_focus_mode' ); ?>
				<div class="qfm-card qfm-c1">
					<h2><?php esc_html_e( 'Where the button appears', 'qot-focus-mode' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Show it on', 'qot-focus-mode' ); ?></th><td>
							<?php qot_focus_mode_radios( 'show_on', array( 'posts' => __( 'Posts', 'qot-focus-mode' ), 'pages' => __( 'Pages', 'qot-focus-mode' ), 'both' => __( 'Posts and pages', 'qot-focus-mode' ) ), $s['show_on'] ); ?>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Place', 'qot-focus-mode' ); ?></th><td>
							<?php qot_focus_mode_radios( 'place', array( 'top' => __( 'Top of the post text', 'qot-focus-mode' ), 'float' => __( 'Floating in a corner', 'qot-focus-mode' ) ), $s['place'] ); ?>
							<p class="description"><?php esc_html_e( 'The top of the post works with every theme. A floating button stays in view while people scroll.', 'qot-focus-mode' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Side', 'qot-focus-mode' ); ?></th><td>
							<?php qot_focus_mode_radios( 'align', array( 'left' => __( 'Left', 'qot-focus-mode' ), 'right' => __( 'Right', 'qot-focus-mode' ) ), $s['align'] ); ?>
						</td></tr>
					</table>
				</div>
				<div class="qfm-card qfm-c2">
					<h2><?php esc_html_e( 'How the button looks', 'qot-focus-mode' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Button shows', 'qot-focus-mode' ); ?></th><td>
							<?php qot_focus_mode_radios( 'style', array( 'both' => __( 'Icon and text', 'qot-focus-mode' ), 'text' => __( 'Text only', 'qot-focus-mode' ), 'icon' => __( 'Icon only', 'qot-focus-mode' ) ), $s['style'] ); ?>
						</td></tr>
						<tr><th scope="row"><label for="qfm-on"><?php esc_html_e( 'Button text', 'qot-focus-mode' ); ?></label></th><td>
							<input type="text" id="qfm-on" class="regular-text" name="<?php echo esc_attr( $n . '[text_on]' ); ?>" value="<?php echo esc_attr( $s['text_on'] ); ?>" />
						</td></tr>
						<tr><th scope="row"><label for="qfm-off"><?php esc_html_e( 'Text while focused', 'qot-focus-mode' ); ?></label></th><td>
							<input type="text" id="qfm-off" class="regular-text" name="<?php echo esc_attr( $n . '[text_off]' ); ?>" value="<?php echo esc_attr( $s['text_off'] ); ?>" />
							<p class="description"><?php esc_html_e( 'The same button brings the page back, so tell readers how to leave.', 'qot-focus-mode' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Colours', 'qot-focus-mode' ); ?></th><td>
							<label><input type="color" name="<?php echo esc_attr( $n . '[bg]' ); ?>" value="<?php echo esc_attr( $s['bg'] ); ?>" /> <?php esc_html_e( 'Button', 'qot-focus-mode' ); ?></label>
							<label style="margin-left:16px"><input type="color" name="<?php echo esc_attr( $n . '[fg]' ); ?>" value="<?php echo esc_attr( $s['fg'] ); ?>" /> <?php esc_html_e( 'Text', 'qot-focus-mode' ); ?></label>
							<p class="description"><?php esc_html_e( 'Pick colours from your site so the button feels part of it.', 'qot-focus-mode' ); ?></p>
						</td></tr>
					</table>
				</div>
				<div class="qfm-card qfm-c3">
					<h2><?php esc_html_e( 'While focused', 'qot-focus-mode' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Layout', 'qot-focus-mode' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[center]' ); ?>" value="1" <?php checked( $s['center'] ); ?> /> <?php esc_html_e( 'Centre the post on the screen', 'qot-focus-mode' ); ?></label>
							<p class="description"><?php esc_html_e( 'Without the sidebar, the post moves to the middle instead of leaving an empty gap. Untick if your theme looks better as it is.', 'qot-focus-mode' ); ?></p>
						</td></tr>
						<tr><th scope="row"><label for="qfm-width"><?php esc_html_e( 'Reading width', 'qot-focus-mode' ); ?></label></th><td>
							<input type="number" id="qfm-width" min="480" max="1400" step="10" class="small-text" name="<?php echo esc_attr( $n . '[width]' ); ?>" value="<?php echo esc_attr( $s['width'] ); ?>" /> px
							<p class="description"><?php esc_html_e( 'About 700 to 800 pixels is easiest to read.', 'qot-focus-mode' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Remember', 'qot-focus-mode' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[remember]' ); ?>" value="1" <?php checked( $s['remember'] ); ?> /> <?php esc_html_e( 'Keep focus on as readers move to the next post', 'qot-focus-mode' ); ?></label>
							<p class="description"><?php esc_html_e( 'Saved only in the reader\'s own browser. Readers can always press the button or Escape to leave.', 'qot-focus-mode' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_focus_mode_help_box(); ?>
			</form>
			<div class="qfm-side qfm-card qfm-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-focus-mode' ); ?> <span class="qfm-live"><?php esc_html_e( 'Live', 'qot-focus-mode' ); ?></span></h2>
				<div class="qfm-screen"><div id="qfm-demo"></div></div>
				<p class="description"><?php esc_html_e( 'A plan of a post on your site. Click the button to see focus mode; click again to come back.', 'qot-focus-mode' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_focus_mode_plugin_links( admin_url( 'options-general.php?page=qot-focus-mode' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function qot_focus_mode_uninstall() {
	delete_option( QOT_FOCUS_MODE_OPTION );
}
register_uninstall_hook( __FILE__, 'qot_focus_mode_uninstall' );
