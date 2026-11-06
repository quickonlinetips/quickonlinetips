<?php
/**
 * Plugin Name:       QOT Font Changer
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       See which fonts your theme uses and where, then swap any of them for a fast, web-safe font in one click. Or set the font and weight for body text, headings, the site title, menus, buttons and code. Changes fonts only, never your theme files.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-font-changer
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_FONT_CHANGER_OPTION', 'qot_font_changer' );
define( 'QOT_FONT_CHANGER_VERSION', '1.0' );

require_once __DIR__ . '/includes/admin-common.php';

/* ------------------------------------------------------------------ *
 * Fonts, weights and parts of the site
 * ------------------------------------------------------------------ */

/**
 * Web-safe fonts: already on nearly every computer and phone, so nothing
 * is downloaded and pages stay fast. Each has fallbacks for devices without it.
 *
 * @return array<string, array{label: string, stack: string}>
 */
function qot_font_changer_fonts() {
	return array(
		'system'    => array( 'label' => __( 'System font (each device\'s own)', 'qot-font-changer' ), 'stack' => 'system-ui,-apple-system,"Segoe UI",Roboto,Ubuntu,"Helvetica Neue",Arial,sans-serif' ),
		'arial'     => array( 'label' => 'Arial', 'stack' => 'Arial,"Helvetica Neue",Helvetica,sans-serif' ),
		'narrow'    => array( 'label' => 'Arial Narrow', 'stack' => '"Arial Narrow","Roboto Condensed","Helvetica Neue",Arial,sans-serif' ),
		'helvetica' => array( 'label' => 'Helvetica', 'stack' => '"Helvetica Neue",Helvetica,Arial,sans-serif' ),
		'verdana'   => array( 'label' => 'Verdana', 'stack' => 'Verdana,Geneva,"DejaVu Sans",sans-serif' ),
		'tahoma'    => array( 'label' => 'Tahoma', 'stack' => 'Tahoma,Verdana,Segoe,sans-serif' ),
		'trebuchet' => array( 'label' => 'Trebuchet MS', 'stack' => '"Trebuchet MS","Lucida Grande","Lucida Sans Unicode",sans-serif' ),
		'lucida'    => array( 'label' => 'Lucida Sans', 'stack' => '"Lucida Sans Unicode","Lucida Grande","Lucida Sans",sans-serif' ),
		'georgia'   => array( 'label' => 'Georgia', 'stack' => 'Georgia,"Times New Roman",Times,serif' ),
		'times'     => array( 'label' => 'Times New Roman', 'stack' => '"Times New Roman",Times,"Liberation Serif",serif' ),
		'palatino'  => array( 'label' => 'Palatino', 'stack' => '"Palatino Linotype",Palatino,"Book Antiqua",serif' ),
		'garamond'  => array( 'label' => 'Garamond', 'stack' => 'Garamond,"Hoefler Text",Baskerville,"Times New Roman",serif' ),
		'courier'   => array( 'label' => 'Courier New', 'stack' => '"Courier New",Courier,monospace' ),
		'mono'      => array( 'label' => __( 'System monospace (for code)', 'qot-font-changer' ), 'stack' => 'ui-monospace,Menlo,Consolas,"Liberation Mono",monospace' ),
	);
}

/**
 * Font weights. Web-safe fonts have regular and bold built in; other
 * weights show where the device has them.
 *
 * @return array<string, string>
 */
function qot_font_changer_weights() {
	return array(
		''    => __( 'Keep', 'qot-font-changer' ),
		'300' => __( 'Light', 'qot-font-changer' ),
		'400' => __( 'Normal', 'qot-font-changer' ),
		'500' => __( 'Medium', 'qot-font-changer' ),
		'600' => __( 'Semi-bold', 'qot-font-changer' ),
		'700' => __( 'Bold', 'qot-font-changer' ),
		'900' => __( 'Black', 'qot-font-changer' ),
	);
}

/**
 * Parts of a page, with the CSS selectors that cover them in most themes.
 *
 * @return array<string, array{label: string, selector: string}>
 */
function qot_font_changer_parts() {
	return array(
		'body'     => array( 'label' => __( 'Body text', 'qot-font-changer' ), 'selector' => 'body,body :is(p,li,dd,dt,td,th,blockquote,figcaption,label,.entry-content,.wp-block-post-content)' ),
		'headings' => array( 'label' => __( 'Headings', 'qot-font-changer' ), 'selector' => 'h1,h2,h3,h4,h5,h6,.wp-block-heading,.entry-title,.wp-block-post-title,.widget-title' ),
		'title'    => array( 'label' => __( 'Site title', 'qot-font-changer' ), 'selector' => '.site-title,.site-title a,.wp-block-site-title,.wp-block-site-title a' ),
		'menu'     => array( 'label' => __( 'Menus', 'qot-font-changer' ), 'selector' => 'nav,nav a,.main-navigation,.main-navigation a,.wp-block-navigation,.wp-block-navigation-item__content' ),
		'buttons'  => array( 'label' => __( 'Buttons and forms', 'qot-font-changer' ), 'selector' => 'button,input,select,textarea,.wp-element-button,.wp-block-button__link' ),
		'code'     => array( 'label' => __( 'Code', 'qot-font-changer' ), 'selector' => 'code,pre,kbd,samp,pre *' ),
	);
}

/* ------------------------------------------------------------------ *
 * Finding the theme's fonts
 * ------------------------------------------------------------------ */

/**
 * Read the active theme's stylesheets (and its parent's) and its theme.json
 * font presets, and list every font stack with the places that use it.
 * Cached until the theme or its files change.
 *
 * @return array<string, array{stack: string, uses: array<int, array{selector: string, prop: string}>}> Keyed by a short id.
 */
function qot_font_changer_detect() {
	$files = array_unique( array( get_stylesheet_directory() . '/style.css', get_template_directory() . '/style.css' ) );
	$key   = get_stylesheet();
	foreach ( $files as $file ) {
		$key .= is_readable( $file ) ? '|' . filemtime( $file ) : '';
	}
	$cache = get_transient( 'qot_font_changer_detect' );
	if ( is_array( $cache ) && isset( $cache['key'] ) && $cache['key'] === $key ) {
		return $cache['fonts'];
	}

	$fonts = array();
	$add   = function ( $stack, $selector, $prop ) use ( &$fonts ) {
		$stack = trim( preg_replace( '/\s+/', ' ', str_replace( array( '!important', "'" ), array( '', '"' ), $stack ) ) );
		// var(--name, fallback list): the fallback is the real font list.
		if ( preg_match( '/^var\(\s*--[\w-]+\s*,\s*(.+)\)$/s', $stack, $m ) ) {
			$stack = trim( $m[1] );
		}
		if ( '' === $stack || preg_match( '/^(inherit|initial|unset|revert|var\()/i', $stack ) ) {
			return;
		}
		$id = substr( md5( strtolower( $stack ) ), 0, 10 );
		if ( ! isset( $fonts[ $id ] ) ) {
			$fonts[ $id ] = array(
				'stack' => $stack,
				'uses'  => array(),
			);
		}
		if ( count( $fonts[ $id ]['uses'] ) < 60 ) {
			$fonts[ $id ]['uses'][] = array(
				'selector' => trim( preg_replace( '/\s+/', ' ', $selector ) ),
				'prop'     => $prop,
			);
		}
	};

	foreach ( $files as $file ) {
		if ( ! is_readable( $file ) ) {
			continue;
		}
		$css = preg_replace( '#/\*.*?\*/#s', '', (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a local theme file.
		if ( ! preg_match_all( '/([^{}]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER ) ) {
			continue;
		}
		foreach ( $rules as $rule ) {
			// Drop at-rules without a block (@charset, @import) that sit before the selector.
			$selector = trim( preg_replace( '/^.*;/s', '', $rule[1] ) );
			if ( '' === $selector || '@' === $selector[0] ) {
				continue;
			}
			if ( preg_match_all( '/(?:^|;)\s*(font-family|--[\w-]*font[\w-]*)\s*:\s*([^;]+)/i', $rule[2], $decls, PREG_SET_ORDER ) ) {
				foreach ( $decls as $decl ) {
					$prop  = strtolower( $decl[1] );
					$value = trim( $decl[2] );
					// A custom property only counts when it holds a font list, not a size.
					if ( 'font-family' !== $prop && ! preg_match( '/(,|serif|monospace|system-ui|")/i', $value ) ) {
						continue;
					}
					$add( $value, $selector, $prop );
				}
			}
		}
	}

	// Block themes keep their fonts in theme.json; changing the preset changes every place it's used.
	if ( function_exists( 'wp_get_global_settings' ) ) {
		$presets = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );
		foreach ( array( 'theme', 'custom' ) as $origin ) {
			if ( empty( $presets[ $origin ] ) || ! is_array( $presets[ $origin ] ) ) {
				continue;
			}
			foreach ( $presets[ $origin ] as $preset ) {
				if ( ! empty( $preset['slug'] ) && ! empty( $preset['fontFamily'] ) ) {
					$add( $preset['fontFamily'], 'body', '--wp--preset--font-family--' . sanitize_key( $preset['slug'] ) );
				}
			}
		}
	}

	set_transient(
		'qot_font_changer_detect',
		array(
			'key'   => $key,
			'fonts' => $fonts,
		),
		WEEK_IN_SECONDS
	);
	return $fonts;
}

/**
 * The first font name in a stack, for showing to people.
 *
 * @param string $stack Font stack.
 * @return string
 */
function qot_font_changer_first( $stack ) {
	$first = explode( ',', $stack );
	$name  = trim( $first[0], " \"'" );
	if ( in_array( strtolower( $name ), array( '-apple-system', 'system-ui', 'blinkmacsystemfont' ), true ) ) {
		return __( 'System font', 'qot-font-changer' );
	}
	return $name;
}

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings: nothing changed.
 *
 * @return array
 */
function qot_font_changer_defaults() {
	$parts = array();
	foreach ( array_keys( qot_font_changer_parts() ) as $part ) {
		$parts[ $part ] = array(
			'font'   => '',
			'weight' => '',
		);
	}
	return array(
		'enabled' => true,
		'swap'    => array(),
		'parts'   => $parts,
		'editor'  => true,
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_font_changer_settings() {
	$saved = get_option( QOT_FONT_CHANGER_OPTION, array() );
	$s     = array_merge( qot_font_changer_defaults(), is_array( $saved ) ? $saved : array() );
	$s['parts'] = array_merge( qot_font_changer_defaults()['parts'], is_array( $s['parts'] ) ? $s['parts'] : array() );
	return $s;
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_font_changer_sanitize( $input ) {
	$input   = is_array( $input ) ? $input : array();
	$fonts   = qot_font_changer_fonts();
	$weights = qot_font_changer_weights();
	$swap    = array();
	if ( ! empty( $input['swap'] ) && is_array( $input['swap'] ) ) {
		foreach ( $input['swap'] as $id => $font ) {
			if ( preg_match( '/^[a-f0-9]{10}$/', (string) $id ) && isset( $fonts[ $font ] ) ) {
				$swap[ $id ] = $font;
			}
		}
	}
	$parts = qot_font_changer_defaults()['parts'];
	foreach ( $parts as $part => $values ) {
		$font   = isset( $input['parts'][ $part ]['font'] ) ? (string) $input['parts'][ $part ]['font'] : '';
		$weight = isset( $input['parts'][ $part ]['weight'] ) ? (string) $input['parts'][ $part ]['weight'] : '';
		$parts[ $part ] = array(
			'font'   => isset( $fonts[ $font ] ) ? $font : '',
			'weight' => isset( $weights[ $weight ] ) ? $weight : '',
		);
	}
	delete_transient( 'qot_font_changer_detect' );
	return array(
		'enabled' => ! empty( $input['enabled'] ),
		'swap'    => $swap,
		'parts'   => $parts,
		'editor'  => ! empty( $input['editor'] ),
	);
}

/* ------------------------------------------------------------------ *
 * On the site
 * ------------------------------------------------------------------ */

/**
 * The CSS that makes the chosen changes.
 *
 * @return string
 */
function qot_font_changer_css() {
	$s = qot_font_changer_settings();
	if ( ! $s['enabled'] ) {
		return '';
	}
	$fonts = qot_font_changer_fonts();
	$css   = '';
	if ( $s['swap'] ) {
		foreach ( qot_font_changer_detect() as $id => $found ) {
			if ( empty( $s['swap'][ $id ] ) ) {
				continue;
			}
			$stack = $fonts[ $s['swap'][ $id ] ]['stack'];
			foreach ( $found['uses'] as $use ) {
				$selector = wp_strip_all_tags( $use['selector'] );
				$css     .= $selector . '{' . $use['prop'] . ':' . $stack . '!important}';
			}
		}
	}
	foreach ( qot_font_changer_parts() as $part => $info ) {
		$choice = $s['parts'][ $part ];
		$decl   = '';
		if ( $choice['font'] ) {
			$decl .= 'font-family:' . $fonts[ $choice['font'] ]['stack'] . '!important;';
			if ( 'narrow' === $choice['font'] ) {
				$decl .= 'font-stretch:condensed;';
			}
		}
		if ( $choice['weight'] ) {
			$decl .= 'font-weight:' . $choice['weight'] . '!important;';
		}
		if ( $decl ) {
			$css .= $info['selector'] . '{' . $decl . '}';
		}
	}
	return str_replace( array( '<', '>' ), array( '', '>' ), $css );
}

/**
 * Print the CSS late in the head, so it comes after the theme's.
 */
function qot_font_changer_head() {
	$css = qot_font_changer_css();
	if ( '' !== $css ) {
		echo '<style id="qot-font-changer">' . $css . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from known fonts and the theme's own selectors, with tags removed.
	}
}
add_action( 'wp_head', 'qot_font_changer_head', 100 );

/**
 * Show the same fonts while writing in the block editor.
 *
 * @param array $settings Editor settings.
 * @return array
 */
function qot_font_changer_editor( $settings ) {
	$s   = qot_font_changer_settings();
	$css = $s['editor'] ? qot_font_changer_css() : '';
	if ( '' !== $css ) {
		$settings['styles'][] = array( 'css' => $css );
	}
	return $settings;
}
add_filter( 'block_editor_settings_all', 'qot_font_changer_editor' );

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_font_changer_register_setting() {
	register_setting(
		'qot_font_changer',
		QOT_FONT_CHANGER_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_font_changer_sanitize',
			'default'           => qot_font_changer_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_font_changer_register_setting' );

/**
 * Add the page under Appearance, next to the theme.
 */
function qot_font_changer_add_page() {
	$hook = add_theme_page( __( 'QOT Font Changer', 'qot-font-changer' ), __( 'Font Changer', 'qot-font-changer' ), 'manage_options', 'qot-font-changer', 'qot_font_changer_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_font_changer_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_font_changer_add_page' );

/**
 * Style and live preview for the settings page.
 */
function qot_font_changer_admin_assets() {
	wp_register_style( 'qot-font-changer-admin', false, array(), QOT_FONT_CHANGER_VERSION );
	wp_enqueue_style( 'qot-font-changer-admin' );
	wp_add_inline_style(
		'qot-font-changer-admin',
		qot_font_changer_admin_base_css() .
		'.qfch-found{width:100%;border-collapse:collapse;margin:12px 0 8px}.qfch-found th,.qfch-found td{text-align:left;vertical-align:top;padding:8px 6px;border-bottom:1px solid #f0f0f1}.qfch-found th{font-weight:600;padding-left:0}' .
		'.qfch-found .f-name{font-size:15px}.qfch-found .f-where{color:#50575e;font-size:12px;max-width:240px}.qfch-found code{font-size:11px}' .
		'.qfch-parts select{max-width:100%}.qfch-parts td{padding:6px 10px 6px 0!important}.qfch-parts th{padding:6px 10px 6px 0!important;width:auto!important}' .
		'#qfch-demo{color:#1d2327}#qfch-demo .d-title{font-size:20px;font-weight:800;margin:0 0 6px}#qfch-demo .d-menu{display:flex;gap:14px;font-size:13px;margin:0 0 14px;padding:0 0 8px;border-bottom:1px solid #dcdcde}' .
		'#qfch-demo h3{font-size:19px;margin:0 0 8px}#qfch-demo p{font-size:14px;line-height:1.6;margin:0 0 10px}#qfch-demo code{font-size:13px;background:#f0f0f1;padding:1px 4px}' .
		'#qfch-demo .d-btn{display:inline-block;background:#2271b1;color:#fff;border:0;border-radius:3px;padding:6px 12px;font-size:13px}'
	);
	$stacks = array();
	foreach ( qot_font_changer_fonts() as $key => $font ) {
		$stacks[ $key ] = $font['stack'];
	}
	wp_register_script( 'qot-font-changer-admin', false, array(), QOT_FONT_CHANGER_VERSION, true );
	wp_enqueue_script( 'qot-font-changer-admin' );
	wp_add_inline_script( 'qot-font-changer-admin', 'window.qfchFonts=' . wp_json_encode( $stacks ) . ';' . qot_font_changer_admin_script() );
}

/**
 * Preview script: shows each part of the sample in the chosen font and weight.
 *
 * @return string
 */
function qot_font_changer_admin_script() {
	return <<<'JS'
( function () {
	var form = document.getElementById( 'qfch-form' );
	var demo = document.getElementById( 'qfch-demo' );
	if ( ! form || ! demo ) {
		return;
	}
	var map = { body: 'p', headings: 'h3', title: '.d-title', menu: '.d-menu', buttons: '.d-btn', code: 'code' };
	function draw() {
		var on = form.querySelector( '[name="qot_font_changer[enabled]"]' ).checked;
		Object.keys( map ).forEach( function ( part ) {
			var font = form.querySelector( '[name="qot_font_changer[parts][' + part + '][font]"]' ).value;
			var weight = form.querySelector( '[name="qot_font_changer[parts][' + part + '][weight]"]' ).value;
			demo.querySelectorAll( map[ part ] ).forEach( function ( el ) {
				el.style.fontFamily = on && font ? window.qfchFonts[ font ] : '';
				el.style.fontWeight = on && weight ? weight : '';
			} );
		} );
	}
	form.addEventListener( 'input', draw );
	form.addEventListener( 'change', draw );
	draw();
}() );
JS;
}

/**
 * One dropdown of fonts.
 *
 * @param string $name    Field name.
 * @param string $current Saved font key.
 * @param string $keep    Label for "no change".
 */
function qot_font_changer_font_select( $name, $current, $keep ) {
	echo '<select name="' . esc_attr( $name ) . '"><option value="">' . esc_html( $keep ) . '</option>';
	foreach ( qot_font_changer_fonts() as $key => $font ) {
		printf( '<option value="%1$s" style="font-family:%2$s" %3$s>%4$s</option>', esc_attr( $key ), esc_attr( $font['stack'] ), selected( $current, $key, false ), esc_html( $font['label'] ) );
	}
	echo '</select>';
}

/**
 * The settings page.
 */
function qot_font_changer_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s     = qot_font_changer_settings();
	$n     = QOT_FONT_CHANGER_OPTION;
	$found = qot_font_changer_detect();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT Font Changer', 'qot-font-changer' ); ?></h1>
		<p><?php esc_html_e( 'See the fonts your theme uses and swap them for fast, web-safe fonts. Your theme files are never changed: untick the switch and everything is as before. The preview updates as you go.', 'qot-font-changer' ); ?></p>
		<div class="qfch-wrap">
			<form method="post" action="options.php" id="qfch-form">
				<?php settings_fields( 'qot_font_changer' ); ?>
				<div class="qfch-card qfch-c1">
					<h2><?php echo esc_html( sprintf( /* translators: %s: theme name. */ __( 'Fonts in %s', 'qot-font-changer' ), wp_get_theme()->get( 'Name' ) ) ); ?></h2>
					<?php if ( ! $found ) : ?>
						<p><?php esc_html_e( 'Your theme doesn\'t name any fonts in its stylesheet, so it uses the browser\'s default. Set fonts by part below.', 'qot-font-changer' ); ?></p>
					<?php else : ?>
						<table class="qfch-found">
							<thead><tr><th><?php esc_html_e( 'Font', 'qot-font-changer' ); ?></th><th><?php esc_html_e( 'Used for', 'qot-font-changer' ); ?></th><th><?php esc_html_e( 'Change to', 'qot-font-changer' ); ?></th></tr></thead>
							<tbody>
							<?php foreach ( $found as $id => $font ) : ?>
								<?php
								$where = array();
								foreach ( array_slice( $font['uses'], 0, 4 ) as $use ) {
									$where[] = 0 === strpos( $use['prop'], '--' ) ? __( 'theme font setting, used across the site', 'qot-font-changer' ) : wp_html_excerpt( $use['selector'], 40, '&hellip;' );
								}
								$where = array_unique( $where );
								$more = count( $font['uses'] ) - min( 4, count( $font['uses'] ) );
								?>
								<tr>
									<td class="f-name" style="font-family:<?php echo esc_attr( $font['stack'] ); ?>"><?php echo esc_html( qot_font_changer_first( $font['stack'] ) ); ?></td>
									<td class="f-where"><?php echo esc_html( implode( ', ', $where ) ); ?>
										<?php if ( $more > 0 ) : ?>
											<?php echo esc_html( sprintf( /* translators: %d: number of other places. */ _n( 'and %d more place', 'and %d more places', $more, 'qot-font-changer' ), $more ) ); ?>
										<?php endif; ?>
									</td>
									<td><?php qot_font_changer_font_select( $n . '[swap][' . $id . ']', isset( $s['swap'][ $id ] ) ? $s['swap'][ $id ] : '', __( 'Keep', 'qot-font-changer' ) ); ?></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
						<p class="description"><?php esc_html_e( 'Read from your theme\'s stylesheet. Fonts loaded by plugins or page builders may not show here; set those by part below.', 'qot-font-changer' ); ?></p>
					<?php endif; ?>
				</div>
				<div class="qfch-card qfch-c2">
					<h2><?php esc_html_e( 'Fonts by part of the page', 'qot-font-changer' ); ?></h2>
					<table class="form-table qfch-parts" role="presentation">
						<?php foreach ( qot_font_changer_parts() as $part => $info ) : ?>
							<tr><th scope="row"><?php echo esc_html( $info['label'] ); ?></th><td>
								<?php qot_font_changer_font_select( $n . '[parts][' . $part . '][font]', $s['parts'][ $part ]['font'], __( 'Theme\'s font', 'qot-font-changer' ) ); ?>
								<select name="<?php echo esc_attr( $n . '[parts][' . $part . '][weight]' ); ?>" aria-label="<?php esc_attr_e( 'Weight', 'qot-font-changer' ); ?>">
									<?php foreach ( qot_font_changer_weights() as $value => $label ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $s['parts'][ $part ]['weight'], $value ); ?>><?php echo esc_html( '' === $value ? __( 'Theme\'s weight', 'qot-font-changer' ) : $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td></tr>
						<?php endforeach; ?>
					</table>
					<p class="description"><?php esc_html_e( 'These win over the swaps above, so you can fine-tune one part.', 'qot-font-changer' ); ?></p>
				</div>
				<div class="qfch-card qfch-c3">
					<h2><?php esc_html_e( 'Switch', 'qot-font-changer' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'On the site', 'qot-font-changer' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[enabled]' ); ?>" value="1" <?php checked( $s['enabled'] ); ?> /> <?php esc_html_e( 'Use my font changes', 'qot-font-changer' ); ?></label>
							<p class="description"><?php esc_html_e( 'Untick to see your theme\'s own fonts again without losing your choices.', 'qot-font-changer' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Editor', 'qot-font-changer' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[editor]' ); ?>" value="1" <?php checked( $s['editor'] ); ?> /> <?php esc_html_e( 'Show the same fonts while writing posts', 'qot-font-changer' ); ?></label>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_font_changer_help_box(); ?>
			</form>
			<div class="qfch-side qfch-card qfch-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-font-changer' ); ?> <span class="qfch-live"><?php esc_html_e( 'Live', 'qot-font-changer' ); ?></span></h2>
				<div class="qfch-screen" id="qfch-demo">
					<div class="d-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
					<div class="d-menu"><span><?php esc_html_e( 'Home', 'qot-font-changer' ); ?></span><span><?php esc_html_e( 'About', 'qot-font-changer' ); ?></span><span><?php esc_html_e( 'Contact', 'qot-font-changer' ); ?></span></div>
					<h3><?php esc_html_e( 'The quick brown fox jumps over the lazy dog', 'qot-font-changer' ); ?></h3>
					<p><?php esc_html_e( 'Good fonts make reading easy. Web-safe fonts are already on your readers\' devices, so nothing extra downloads and your pages open faster.', 'qot-font-changer' ); ?> <code>add_theme_support()</code></p>
					<span class="d-btn"><?php esc_html_e( 'Subscribe', 'qot-font-changer' ); ?></span>
				</div>
				<p class="description"><?php esc_html_e( 'Shows the parts you set below in their new font and weight. Swaps of your theme\'s own fonts show on the site after you save.', 'qot-font-changer' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_font_changer_plugin_links( admin_url( 'themes.php?page=qot-font-changer' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function qot_font_changer_uninstall() {
	delete_option( QOT_FONT_CHANGER_OPTION );
	delete_transient( 'qot_font_changer_detect' );
}
register_uninstall_hook( __FILE__, 'qot_font_changer_uninstall' );
