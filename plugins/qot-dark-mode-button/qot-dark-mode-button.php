<?php
/**
 * Plugin Name:       QOT Dark Mode Button
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       A small button on your posts that switches the post to light text on a dark background, and back. Only the post changes; the rest of your site stays as it is.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-dark-mode-button
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_DARK_MODE_BUTTON_OPTION', 'qot_dark_mode_button' );
define( 'QOT_DARK_MODE_BUTTON_VERSION', '1.0' );

require_once __DIR__ . '/includes/admin-common.php';

/**
 * The moon and sun icons, drawn with the text colour.
 *
 * @param string $which 'moon' or 'sun'.
 * @return string
 */
function qot_dark_mode_button_icon( $which ) {
	if ( 'sun' === $which ) {
		return '<svg class="qdm-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="4.5" fill="currentColor"/><path d="M12 1.5v3M12 19.5v3M1.5 12h3M19.5 12h3M4.6 4.6l2.1 2.1M17.3 17.3l2.1 2.1M4.6 19.4l2.1-2.1M17.3 6.7l2.1-2.1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
	}
	return '<svg class="qdm-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5a8.5 8.5 0 1 0 10.7 10.7z" fill="currentColor"/></svg>';
}

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings.
 *
 * @return array
 */
function qot_dark_mode_button_defaults() {
	return array(
		'show_on'  => 'posts',
		'place'    => 'top',
		'align'    => 'right',
		'style'    => 'both',
		'text_on'  => __( 'Dark', 'qot-dark-mode-button' ),
		'text_off' => __( 'Light', 'qot-dark-mode-button' ),
		'bg'       => '#1d2327',
		'fg'       => '#ffffff',
		'area'     => 'post',
		'dark_bg'  => '#16181d',
		'dark_fg'  => '#e4e6eb',
		'dark_a'   => '#8ab4f8',
		'remember' => true,
		'device'   => false,
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_dark_mode_button_settings() {
	$saved = get_option( QOT_DARK_MODE_BUTTON_OPTION, array() );
	return array_merge( qot_dark_mode_button_defaults(), is_array( $saved ) ? $saved : array() );
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_dark_mode_button_sanitize( $input ) {
	$d     = qot_dark_mode_button_defaults();
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
		'area'     => $pick( 'area', array( 'post', 'text' ) ),
		'dark_bg'  => $color( 'dark_bg' ),
		'dark_fg'  => $color( 'dark_fg' ),
		'dark_a'   => $color( 'dark_a' ),
		'remember' => ! empty( $input['remember'] ),
		'device'   => ! empty( $input['device'] ),
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
function qot_dark_mode_button_wanted() {
	$s     = qot_dark_mode_button_settings();
	$types = array(
		'posts' => array( 'post' ),
		'pages' => array( 'page' ),
		'both'  => array( 'post', 'page' ),
	);
	$show  = is_singular( $types[ $s['show_on'] ] ) && ! is_feed() && ! is_embed();

	/**
	 * Filters whether the dark mode button appears on the current page.
	 *
	 * @param bool $show True to show the button.
	 */
	return (bool) apply_filters( 'qot_dark_mode_button_show', $show );
}

/**
 * The button's HTML, in its light state (shows "Dark").
 *
 * @return string
 */
function qot_dark_mode_button_html() {
	$s     = qot_dark_mode_button_settings();
	$label = esc_html( $s['text_on'] );
	$icon  = '<span class="qdm-i-moon">' . qot_dark_mode_button_icon( 'moon' ) . '</span><span class="qdm-i-sun">' . qot_dark_mode_button_icon( 'sun' ) . '</span>';
	if ( 'icon' === $s['style'] ) {
		$inner = $icon . '<span class="qdm-sr">' . $label . '</span>';
	} elseif ( 'text' === $s['style'] ) {
		$inner = '<span class="qdm-text">' . $label . '</span>';
	} else {
		$inner = $icon . '<span class="qdm-text">' . $label . '</span>';
	}
	return '<button type="button" class="qdm-btn" aria-pressed="false" title="' . esc_attr( $s['text_on'] ) . '">' . $inner . '</button>';
}

/**
 * Put the button at the top of the post text, inside a marker for the text area.
 *
 * @param string $content Post content.
 * @return string
 */
function qot_dark_mode_button_content( $content ) {
	if ( ! in_the_loop() || ! is_main_query() || get_the_ID() !== get_queried_object_id() || ! qot_dark_mode_button_wanted() ) {
		return $content;
	}
	$s   = qot_dark_mode_button_settings();
	$bar = 'top' === $s['place'] ? '<div class="qdm-bar qdm-' . esc_attr( $s['align'] ) . '">' . qot_dark_mode_button_html() . '</div>' : '';
	return '<div class="qdm-text-area">' . $bar . $content . '</div>';
}

/**
 * Or float the button in a corner.
 */
function qot_dark_mode_button_floating() {
	$s = qot_dark_mode_button_settings();
	echo '<div class="qdm-float qdm-' . esc_attr( $s['align'] ) . '">' . qot_dark_mode_button_html() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in qot_dark_mode_button_html().
}

/**
 * Hook in only on pages that show the button.
 */
function qot_dark_mode_button_setup() {
	if ( is_admin() || ! qot_dark_mode_button_wanted() ) {
		return;
	}
	$s = qot_dark_mode_button_settings();
	add_filter( 'the_content', 'qot_dark_mode_button_content', 1 );
	if ( 'float' === $s['place'] ) {
		add_action( 'wp_footer', 'qot_dark_mode_button_floating', 5 );
	}
	add_action( 'wp_enqueue_scripts', 'qot_dark_mode_button_assets' );
}
add_action( 'template_redirect', 'qot_dark_mode_button_setup' );

/**
 * CSS for the dark post. The dark background is drawn with a wide shadow
 * round the box, so the page doesn't jump when it switches.
 *
 * @param array  $s     Settings.
 * @param string $scope Selector the rules apply to.
 * @return string
 */
function qot_dark_mode_button_dark_css( $s, $scope ) {
	$text = ':is(p,li,h1,h2,h3,h4,h5,h6,blockquote,figcaption,td,th,dt,dd,span,strong,em,b,i,cite,label,small,time,div,header,footer,section,figure,address,aside,nav,ul,ol,dl,table,tr,tbody,thead):not(.has-text-color):not(.has-background):not(.qdm-btn):not(.qdm-btn *)';
	return $scope . '{background-color:' . $s['dark_bg'] . '!important;color:' . $s['dark_fg'] . '!important;box-shadow:0 0 0 20px ' . $s['dark_bg'] . ';border-radius:2px;color-scheme:dark;transition:background-color .2s,box-shadow .2s}' .
		$scope . ' ' . $text . '{color:inherit!important}' .
		$scope . ' a:not(.wp-element-button):not(.wp-block-button__link){color:' . $s['dark_a'] . '!important}' .
		$scope . ' :is(hr,table,td,th,blockquote){border-color:rgba(255,255,255,.22)!important}' .
		$scope . ' img{filter:brightness(.92)}';
}

/**
 * The front-end CSS and script, inline.
 */
function qot_dark_mode_button_assets() {
	$s = qot_dark_mode_button_settings();
	wp_register_style( 'qot-dark-mode-button', false, array(), QOT_DARK_MODE_BUTTON_VERSION );
	wp_enqueue_style( 'qot-dark-mode-button' );
	wp_add_inline_style(
		'qot-dark-mode-button',
		'.qdm-bar{display:flex;margin:0 0 1em}.qdm-bar.qdm-right{justify-content:flex-end}' .
		'.qdm-btn{display:inline-flex;align-items:center;gap:6px;margin:0;padding:6px 14px;border:0;border-radius:999px;background:' . $s['bg'] . ';color:' . $s['fg'] . ';font:600 14px/1.3 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.2)}' .
		'.qdm-btn:hover{filter:brightness(1.15)}.qdm-icon{width:16px;height:16px;display:block}.qdm-i-sun,.qdm-btn[aria-pressed=true] .qdm-i-moon{display:none}.qdm-btn[aria-pressed=true] .qdm-i-sun{display:block}' .
		'.qdm-sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}' .
		'.qdm-float{position:fixed;bottom:20px;z-index:99999}.qdm-float.qdm-right{right:20px}.qdm-float.qdm-left{left:20px}.qdm-float .qdm-btn{padding:10px 16px;box-shadow:0 4px 14px rgba(0,0,0,.25)}' .
		qot_dark_mode_button_dark_css( $s, '.qdm-dark' )
	);
	wp_register_script( 'qot-dark-mode-button', false, array(), QOT_DARK_MODE_BUTTON_VERSION, true );
	wp_enqueue_script( 'qot-dark-mode-button' );
	wp_add_inline_script(
		'qot-dark-mode-button',
		'window.qdmData=' . wp_json_encode(
			array(
				'on'       => $s['text_on'],
				'off'      => $s['text_off'],
				'area'     => $s['area'],
				'remember' => (bool) $s['remember'],
				'device'   => (bool) $s['device'],
			)
		) . ';' . qot_dark_mode_button_front_script()
	);
}

/**
 * The front-end script: find the post box (or just its text) and switch a class on it.
 *
 * @return string
 */
function qot_dark_mode_button_front_script() {
	return <<<'JS'
( function () {
	var data = window.qdmData || {};
	var btn = document.querySelector( '.qdm-btn' );
	var text = document.querySelector( '.qdm-text-area' );
	if ( ! btn || ! text ) {
		return;
	}
	var box = 'text' === data.area ? text : ( text.closest( 'article' ) || text.closest( 'main' ) || text );
	function set( dark ) {
		box.classList.toggle( 'qdm-dark', dark );
		var label = dark ? data.off : data.on;
		var t = btn.querySelector( '.qdm-text, .qdm-sr' );
		if ( t ) {
			t.textContent = label;
		}
		btn.title = label;
		btn.setAttribute( 'aria-pressed', dark ? 'true' : 'false' );
	}
	var saved = null;
	if ( data.remember ) {
		try {
			saved = window.localStorage.getItem( 'qdm' );
		} catch ( e ) {}
	}
	if ( '1' === saved || ( null === saved && data.device && window.matchMedia && window.matchMedia( '(prefers-color-scheme: dark)' ).matches ) ) {
		set( true );
	}
	btn.addEventListener( 'click', function () {
		var dark = ! box.classList.contains( 'qdm-dark' );
		set( dark );
		if ( data.remember ) {
			try {
				window.localStorage.setItem( 'qdm', dark ? '1' : '0' );
			} catch ( e ) {}
		}
	} );
}() );
JS;
}

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_dark_mode_button_register_setting() {
	register_setting(
		'qot_dark_mode_button',
		QOT_DARK_MODE_BUTTON_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_dark_mode_button_sanitize',
			'default'           => qot_dark_mode_button_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_dark_mode_button_register_setting' );

/**
 * Add the page under Settings.
 */
function qot_dark_mode_button_add_page() {
	$hook = add_options_page( __( 'QOT Dark Mode Button', 'qot-dark-mode-button' ), __( 'QOT Dark Mode Button', 'qot-dark-mode-button' ), 'manage_options', 'qot-dark-mode-button', 'qot_dark_mode_button_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_dark_mode_button_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_dark_mode_button_add_page' );

/**
 * Style and live preview for the settings page.
 */
function qot_dark_mode_button_admin_assets() {
	wp_register_style( 'qot-dark-mode-button-admin', false, array(), QOT_DARK_MODE_BUTTON_VERSION );
	wp_enqueue_style( 'qot-dark-mode-button-admin' );
	wp_add_inline_style(
		'qot-dark-mode-button-admin',
		qot_dark_mode_button_admin_base_css() .
		'#qdm-demo{position:relative;font:14px/1.6 Georgia,serif;padding:24px 28px;min-height:250px}#qdm-demo .d-site{font:700 12px/1 -apple-system,sans-serif;color:#50575e;margin:0 0 14px;text-transform:uppercase;letter-spacing:.5px;margin-bottom:26px}#qdm-demo h3{color:inherit}' .
		'#qdm-demo .d-box{padding:4px 0}#qdm-demo h3{font-size:19px;margin:0 0 10px}#qdm-demo p{margin:0 0 10px}#qdm-demo a{color:#2271b1}' .
		'#qdm-demo .d-bar{display:flex;margin:0 0 10px}#qdm-demo .d-bar.right{justify-content:flex-end}#qdm-demo .d-float{position:absolute;bottom:10px}#qdm-demo .d-float.right{right:10px}#qdm-demo .d-float.left{left:10px}' .
		'#qdm-demo .qdm-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:0;border-radius:999px;font:600 13px/1.3 -apple-system,sans-serif;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.2)}#qdm-demo .qdm-icon{width:15px;height:15px;display:block}'
	);
	wp_register_script( 'qot-dark-mode-button-admin', false, array(), QOT_DARK_MODE_BUTTON_VERSION, true );
	wp_enqueue_script( 'qot-dark-mode-button-admin' );
	wp_add_inline_script(
		'qot-dark-mode-button-admin',
		'window.qdmAdmin=' . wp_json_encode(
			array(
				'moon'  => qot_dark_mode_button_icon( 'moon' ),
				'sun'   => qot_dark_mode_button_icon( 'sun' ),
				'site'  => get_bloginfo( 'name' ),
				'title' => __( 'A slow walk by the river', 'qot-dark-mode-button' ),
				'p1'    => __( 'Early in the morning the water is still, and the only sound is a heron lifting off the bank.', 'qot-dark-mode-button' ),
				'p2'    => __( 'Read more about our favourite walks.', 'qot-dark-mode-button' ),
			)
		) . ';' . qot_dark_mode_button_admin_script()
	);
}

/**
 * Preview script: rebuilds the demo post from the form.
 *
 * @return string
 */
function qot_dark_mode_button_admin_script() {
	return <<<'JS'
( function () {
	var form = document.getElementById( 'qdm-form' );
	var demo = document.getElementById( 'qdm-demo' );
	var data = window.qdmAdmin;
	if ( ! form || ! demo ) {
		return;
	}
	var dark = false;
	function esc( t ) {
		return String( t ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[ c ];
		} );
	}
	function val( name ) {
		var el = form.querySelector( '[name="qot_dark_mode_button[' + name + ']"]:checked' ) || form.querySelector( '[name="qot_dark_mode_button[' + name + ']"]:not([type=radio]):not([type=checkbox])' );
		return el ? el.value : '';
	}
	function draw() {
		var style = val( 'style' );
		var label = esc( dark ? val( 'text_off' ) : val( 'text_on' ) );
		var icon = dark ? data.sun : data.moon;
		var inner = 'icon' === style ? icon : ( 'text' === style ? label : icon + label );
		var button = '<button type="button" class="qdm-btn" style="background:' + esc( val( 'bg' ) ) + ';color:' + esc( val( 'fg' ) ) + '" title="' + label + '">' + inner + '</button>';
		var align = val( 'align' );
		var top = 'top' === val( 'place' );
		var post = '<p>' + esc( data.p1 ) + '</p><p><a href="#">' + esc( data.p2 ) + '</a></p>';
		var textBox = '<div class="d-text">' + ( top ? '<div class="d-bar ' + align + '">' + button + '</div>' : '' ) + post + '</div>';
		demo.innerHTML = '<div class="d-site">' + esc( data.site ) + '</div><div class="d-box"><h3>' + esc( data.title ) + '</h3>' + textBox + '</div>' +
			( top ? '' : '<div class="d-float ' + align + '">' + button + '</div>' );
		var target = demo.querySelector( 'text' === val( 'area' ) ? '.d-text' : '.d-box' );
		if ( dark ) {
			target.style.cssText = 'background:' + val( 'dark_bg' ) + ';color:' + val( 'dark_fg' ) + ';box-shadow:0 0 0 14px ' + val( 'dark_bg' );
			target.querySelectorAll( 'a' ).forEach( function ( a ) {
				a.style.color = val( 'dark_a' );
			} );
		}
	}
	demo.addEventListener( 'click', function ( e ) {
		e.preventDefault();
		if ( e.target.closest( '.qdm-btn' ) ) {
			dark = ! dark;
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
function qot_dark_mode_button_radios( $key, $choices, $current ) {
	foreach ( $choices as $value => $label ) {
		printf(
			'<label style="margin-right:14px"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label>',
			esc_attr( QOT_DARK_MODE_BUTTON_OPTION . '[' . $key . ']' ),
			esc_attr( $value ),
			checked( $current, $value, false ),
			esc_html( $label )
		);
	}
}

/**
 * Print one colour picker.
 *
 * @param string $key   Setting key.
 * @param string $label Label.
 * @param array  $s     Settings.
 */
function qot_dark_mode_button_color( $key, $label, $s ) {
	printf(
		'<label style="margin-right:16px"><input type="color" name="%1$s" value="%2$s" /> %3$s</label>',
		esc_attr( QOT_DARK_MODE_BUTTON_OPTION . '[' . $key . ']' ),
		esc_attr( $s[ $key ] ),
		esc_html( $label )
	);
}

/**
 * The settings page.
 */
function qot_dark_mode_button_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = qot_dark_mode_button_settings();
	$n = QOT_DARK_MODE_BUTTON_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT Dark Mode Button', 'qot-dark-mode-button' ); ?></h1>
		<p><?php esc_html_e( 'Readers who prefer light text on a dark background can switch your posts with one click. The preview updates as you go; click its button to try it.', 'qot-dark-mode-button' ); ?></p>
		<div class="qdm-wrap">
			<form method="post" action="options.php" id="qdm-form">
				<?php settings_fields( 'qot_dark_mode_button' ); ?>
				<div class="qdm-card qdm-c1">
					<h2><?php esc_html_e( 'Where the button appears', 'qot-dark-mode-button' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Show it on', 'qot-dark-mode-button' ); ?></th><td>
							<?php qot_dark_mode_button_radios( 'show_on', array( 'posts' => __( 'Posts', 'qot-dark-mode-button' ), 'pages' => __( 'Pages', 'qot-dark-mode-button' ), 'both' => __( 'Posts and pages', 'qot-dark-mode-button' ) ), $s['show_on'] ); ?>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Place', 'qot-dark-mode-button' ); ?></th><td>
							<?php qot_dark_mode_button_radios( 'place', array( 'top' => __( 'Top of the post text', 'qot-dark-mode-button' ), 'float' => __( 'Floating in a corner', 'qot-dark-mode-button' ) ), $s['place'] ); ?>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Side', 'qot-dark-mode-button' ); ?></th><td>
							<?php qot_dark_mode_button_radios( 'align', array( 'left' => __( 'Left', 'qot-dark-mode-button' ), 'right' => __( 'Right', 'qot-dark-mode-button' ) ), $s['align'] ); ?>
						</td></tr>
					</table>
				</div>
				<div class="qdm-card qdm-c2">
					<h2><?php esc_html_e( 'How the button looks', 'qot-dark-mode-button' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Button shows', 'qot-dark-mode-button' ); ?></th><td>
							<?php qot_dark_mode_button_radios( 'style', array( 'both' => __( 'Icon and text', 'qot-dark-mode-button' ), 'text' => __( 'Text only', 'qot-dark-mode-button' ), 'icon' => __( 'Icon only', 'qot-dark-mode-button' ) ), $s['style'] ); ?>
						</td></tr>
						<tr><th scope="row"><label for="qdm-on"><?php esc_html_e( 'Button text', 'qot-dark-mode-button' ); ?></label></th><td>
							<input type="text" id="qdm-on" class="regular-text" name="<?php echo esc_attr( $n . '[text_on]' ); ?>" value="<?php echo esc_attr( $s['text_on'] ); ?>" />
						</td></tr>
						<tr><th scope="row"><label for="qdm-off"><?php esc_html_e( 'Text when dark', 'qot-dark-mode-button' ); ?></label></th><td>
							<input type="text" id="qdm-off" class="regular-text" name="<?php echo esc_attr( $n . '[text_off]' ); ?>" value="<?php echo esc_attr( $s['text_off'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Shown on the same button once the post is dark, to switch it back.', 'qot-dark-mode-button' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Colours', 'qot-dark-mode-button' ); ?></th><td>
							<?php qot_dark_mode_button_color( 'bg', __( 'Button', 'qot-dark-mode-button' ), $s ); ?>
							<?php qot_dark_mode_button_color( 'fg', __( 'Text', 'qot-dark-mode-button' ), $s ); ?>
						</td></tr>
					</table>
				</div>
				<div class="qdm-card qdm-c3">
					<h2><?php esc_html_e( 'The dark post', 'qot-dark-mode-button' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'What turns dark', 'qot-dark-mode-button' ); ?></th><td>
							<?php qot_dark_mode_button_radios( 'area', array( 'post' => __( 'The whole post box', 'qot-dark-mode-button' ), 'text' => __( 'Just the post text', 'qot-dark-mode-button' ) ), $s['area'] ); ?>
							<p class="description"><?php esc_html_e( 'The post box includes the title and, in many themes, the comments.', 'qot-dark-mode-button' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Dark colours', 'qot-dark-mode-button' ); ?></th><td>
							<?php qot_dark_mode_button_color( 'dark_bg', __( 'Background', 'qot-dark-mode-button' ), $s ); ?>
							<?php qot_dark_mode_button_color( 'dark_fg', __( 'Text', 'qot-dark-mode-button' ), $s ); ?>
							<?php qot_dark_mode_button_color( 'dark_a', __( 'Links', 'qot-dark-mode-button' ), $s ); ?>
							<p class="description"><?php esc_html_e( 'Soft greys are easier on the eyes than pure black and white.', 'qot-dark-mode-button' ); ?></p>
						</td></tr>
					</table>
				</div>
				<div class="qdm-card qdm-c4">
					<h2><?php esc_html_e( 'Readers\' choice', 'qot-dark-mode-button' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Remember', 'qot-dark-mode-button' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[remember]' ); ?>" value="1" <?php checked( $s['remember'] ); ?> /> <?php esc_html_e( 'Keep each reader\'s choice on every post', 'qot-dark-mode-button' ); ?></label>
							<p class="description"><?php esc_html_e( 'Saved only in the reader\'s own browser, so someone who likes dark posts gets them every time.', 'qot-dark-mode-button' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Device setting', 'qot-dark-mode-button' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[device]' ); ?>" value="1" <?php checked( $s['device'] ); ?> /> <?php esc_html_e( 'Start dark when the reader\'s phone or computer is in dark mode', 'qot-dark-mode-button' ); ?></label>
							<p class="description"><?php esc_html_e( 'Only until they press the button; their own choice always wins.', 'qot-dark-mode-button' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_dark_mode_button_help_box(); ?>
			</form>
			<div class="qdm-side qdm-card qdm-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-dark-mode-button' ); ?> <span class="qdm-live"><?php esc_html_e( 'Live', 'qot-dark-mode-button' ); ?></span></h2>
				<div class="qdm-screen" style="padding:0"><div id="qdm-demo"></div></div>
				<p class="description"><?php esc_html_e( 'A sample post. Click the button to switch it dark, and again to switch it back.', 'qot-dark-mode-button' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_dark_mode_button_plugin_links( admin_url( 'options-general.php?page=qot-dark-mode-button' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function qot_dark_mode_button_uninstall() {
	delete_option( QOT_DARK_MODE_BUTTON_OPTION );
}
register_uninstall_hook( __FILE__, 'qot_dark_mode_button_uninstall' );
