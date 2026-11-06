<?php
/**
 * Plugin Name:       QOT CSS Inserter
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       Paste your own CSS in one simple box and it's added to every page. A second box keeps CSS for one theme only, so nothing is lost when you switch themes. Pretty print, copy, download and a live preview of your site.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-css-inserter
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_CSS_INSERTER_OPTION', 'qot_css_inserter' );
define( 'QOT_CSS_INSERTER_VERSION', '1.0' );

require_once __DIR__ . '/includes/admin-common.php';

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings.
 *
 * @return array
 */
function qot_css_inserter_defaults() {
	return array(
		'enabled' => true,
		'global'  => '',
		'themes'  => array(),
		'shrink'  => true,
		'editor'  => false,
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_css_inserter_settings() {
	$saved = get_option( QOT_CSS_INSERTER_OPTION, array() );
	return array_merge( qot_css_inserter_defaults(), is_array( $saved ) ? $saved : array() );
}

/**
 * Make pasted CSS safe to print inside a style tag.
 *
 * @param string $css Raw CSS.
 * @return string
 */
function qot_css_inserter_clean( $css ) {
	$css = str_replace( "\r\n", "\n", (string) $css );
	// A closing style tag is the only way out of the style element, so it can never be saved.
	return trim( preg_replace( '#</?style[^>]*>#i', '', $css ) );
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_css_inserter_sanitize( $input ) {
	$input  = is_array( $input ) ? $input : array();
	$themes = array();
	$known  = array_keys( wp_get_themes() );
	if ( ! empty( $input['themes'] ) && is_array( $input['themes'] ) ) {
		foreach ( $input['themes'] as $slug => $css ) {
			$css = qot_css_inserter_clean( $css );
			if ( '' !== $css && in_array( $slug, $known, true ) ) {
				$themes[ $slug ] = $css;
			}
		}
	}
	// CSS for themes that are not installed right now is kept, not thrown away.
	$old = qot_css_inserter_settings()['themes'];
	foreach ( $old as $slug => $css ) {
		if ( ! in_array( $slug, $known, true ) ) {
			$themes[ $slug ] = $css;
		}
	}
	return array(
		'enabled' => ! empty( $input['enabled'] ),
		'global'  => isset( $input['global'] ) ? qot_css_inserter_clean( $input['global'] ) : '',
		'themes'  => $themes,
		'shrink'  => ! empty( $input['shrink'] ),
		'editor'  => ! empty( $input['editor'] ),
	);
}

/**
 * Remove comments and extra spaces, so the page carries less.
 *
 * @param string $css CSS.
 * @return string
 */
function qot_css_inserter_shrink( $css ) {
	$css = preg_replace( '#/\*.*?\*/#s', '', $css );
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = preg_replace( '/\s*([{};,>])\s*/', '$1', $css );
	$css = preg_replace( '/;}/', '}', $css );
	return trim( $css );
}

/**
 * The CSS for the active theme: the every-theme box, then this theme's box.
 *
 * @return string
 */
function qot_css_inserter_css() {
	$s   = qot_css_inserter_settings();
	$css = trim( $s['global'] . "\n" . ( isset( $s['themes'][ get_stylesheet() ] ) ? $s['themes'][ get_stylesheet() ] : '' ) );
	return ( $s['shrink'] && '' !== $css ) ? qot_css_inserter_shrink( $css ) : $css;
}

/* ------------------------------------------------------------------ *
 * On the site
 * ------------------------------------------------------------------ */

/**
 * Print the CSS last in the head, so it wins over the theme. While the
 * settings page shows its live preview, the saved CSS is left out so the
 * preview can show the CSS being typed instead.
 */
function qot_css_inserter_head() {
	$s = qot_css_inserter_settings();
	if ( ! $s['enabled'] ) {
		return;
	}
	if ( isset( $_GET['qcss-preview'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only skips output for admins.
		return;
	}
	$css = qot_css_inserter_css();
	if ( '' !== $css ) {
		echo '<style id="qot-css-inserter">' . "\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS cleaned on save; it cannot close the style tag.
	}
}
add_action( 'wp_head', 'qot_css_inserter_head', 999 );

/**
 * Optionally show the CSS in the block editor too.
 *
 * @param array $settings Editor settings.
 * @return array
 */
function qot_css_inserter_editor( $settings ) {
	$s = qot_css_inserter_settings();
	if ( $s['enabled'] && $s['editor'] ) {
		$css = qot_css_inserter_css();
		if ( '' !== $css ) {
			$settings['styles'][] = array( 'css' => $css );
		}
	}
	return $settings;
}
add_filter( 'block_editor_settings_all', 'qot_css_inserter_editor' );

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_css_inserter_register_setting() {
	register_setting(
		'qot_css_inserter',
		QOT_CSS_INSERTER_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_css_inserter_sanitize',
			'default'           => qot_css_inserter_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_css_inserter_register_setting' );

/**
 * Add the page under Appearance.
 */
function qot_css_inserter_add_page() {
	$hook = add_theme_page( __( 'QOT CSS Inserter', 'qot-css-inserter' ), __( 'CSS Inserter', 'qot-css-inserter' ), 'manage_options', 'qot-css-inserter', 'qot_css_inserter_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_css_inserter_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_css_inserter_add_page' );

/**
 * Style, code editor and live preview for the settings page.
 */
function qot_css_inserter_admin_assets() {
	// WordPress's own code editor, with CSS colours (unless the user turned them off in their profile).
	$editor = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );

	wp_register_style( 'qot-css-inserter-admin', false, array(), QOT_CSS_INSERTER_VERSION );
	wp_enqueue_style( 'qot-css-inserter-admin' );
	wp_add_inline_style(
		'qot-css-inserter-admin',
		qot_css_inserter_admin_base_css() .
		'.qcss-wrap form{max-width:720px}.qcss-box textarea{width:100%;min-height:220px;font:13px/1.5 Consolas,Monaco,monospace}.qcss-box .CodeMirror{height:260px;border:1px solid #dcdcde}' .
		'.qcss-tools{display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin:8px 0 12px}.qcss-tools .dashicons{vertical-align:text-bottom;margin-right:3px;font-size:17px;width:17px;height:17px}.qcss-done{color:#00a32a;font-weight:600;font-size:12px}' .
		'.qcss-pick{margin:12px 0 8px}.qcss-theme{display:none}.qcss-theme.is-on{display:block}.qcss-count{color:#50575e}' .
		'.qcss-frame{position:relative;height:360px;overflow:hidden;border-radius:4px;background:#fff}.qcss-frame iframe{position:absolute;top:0;left:0;width:1280px;height:1150px;border:0;transform-origin:0 0}' .
		'.qcss-screen{padding:0!important}'
	);
	$themes = array();
	foreach ( wp_get_themes() as $slug => $theme ) {
		$themes[ $slug ] = $theme->get( 'Name' );
	}
	wp_register_script( 'qot-css-inserter-admin', false, array( 'jquery' ), QOT_CSS_INSERTER_VERSION, true );
	wp_enqueue_script( 'qot-css-inserter-admin' );
	wp_add_inline_script(
		'qot-css-inserter-admin',
		'window.qcssData=' . wp_json_encode(
			array(
				'editor' => $editor,
				'active' => get_stylesheet(),
				'copied' => __( 'Copied', 'qot-css-inserter' ),
				'date'   => wp_date( 'Y-m-d' ),
			)
		) . ';' . qot_css_inserter_admin_script()
	);
}

/**
 * The page script: code editors, theme switcher, tools and the live preview.
 *
 * @return string
 */
function qot_css_inserter_admin_script() {
	return <<<'JS'
( function () {
	var d = window.qcssData;
	var form = document.getElementById( 'qcss-form' );
	if ( ! form ) {
		return;
	}
	var editors = {};
	form.querySelectorAll( 'textarea.qcss-code' ).forEach( function ( area ) {
		if ( d.editor && window.wp && wp.codeEditor ) {
			var ed = wp.codeEditor.initialize( area, d.editor );
			editors[ area.id ] = ed.codemirror;
			ed.codemirror.on( 'change', function () {
				ed.codemirror.save();
				preview();
			} );
		}
	} );
	function get( id ) {
		return editors[ id ] ? editors[ id ].getValue() : document.getElementById( id ).value;
	}
	function set( id, text ) {
		if ( editors[ id ] ) {
			editors[ id ].setValue( text );
		} else {
			document.getElementById( id ).value = text;
		}
		preview();
	}
	// A simple CSS tidier: one rule per block, one declaration per line, tab indents.
	function pretty( css ) {
		var out = '', depth = 0, quote = '', i, c;
		css = css.replace( /\r/g, '' ).trim();
		for ( i = 0; i < css.length; i++ ) {
			c = css[ i ];
			if ( quote ) {
				out += c;
				if ( c === quote && css[ i - 1 ] !== '\\' ) {
					quote = '';
				}
				continue;
			}
			if ( '/' === c && '*' === css[ i + 1 ] ) {
				var end = css.indexOf( '*/', i + 2 );
				end = -1 === end ? css.length : end + 2;
				out += css.slice( i, end ) + '\n' + '\t'.repeat( depth );
				i = end - 1;
				continue;
			}
			if ( '"' === c || "'" === c ) {
				quote = c;
				out += c;
			} else if ( '{' === c ) {
				depth++;
				out = out.replace( /\s+$/, '' ) + ' {\n' + '\t'.repeat( depth );
			} else if ( '}' === c ) {
				depth = Math.max( 0, depth - 1 );
				out = out.replace( /\s+$/, '' ) + '\n' + '\t'.repeat( depth ) + '}\n' + ( depth ? '\t'.repeat( depth ) : '\n' );
			} else if ( ';' === c ) {
				out = out.replace( /\s+$/, '' ) + ';\n' + '\t'.repeat( depth );
			} else if ( /\s/.test( c ) ) {
				if ( ! /\s$/.test( out ) ) {
					out += ' ';
				}
			} else if ( ':' === c && depth ) {
				out = out.replace( /\s+$/, '' ) + ': ';
				while ( /\s/.test( css[ i + 1 ] || '' ) ) {
					i++;
				}
			} else {
				out += c;
			}
		}
		return out.replace( /[ \t]+\n/g, '\n' ).replace( /\n{3,}/g, '\n\n' ).trim() + '\n';
	}
	form.addEventListener( 'click', function ( e ) {
		var b = e.target.closest( '[data-qcss]' );
		if ( ! b ) {
			return;
		}
		var id = b.getAttribute( 'data-for' );
		var what = b.getAttribute( 'data-qcss' );
		var done = b.parentNode.querySelector( '.qcss-done' );
		if ( 'pretty' === what ) {
			set( id, pretty( get( id ) ) );
		} else if ( 'copy' === what ) {
			var t = document.createElement( 'textarea' );
			t.value = get( id );
			document.body.appendChild( t );
			t.select();
			try {
				document.execCommand( 'copy' );
				done.textContent = d.copied;
				setTimeout( function () {
					done.textContent = '';
				}, 2000 );
			} catch ( err ) {}
			t.remove();
		} else if ( 'download' === what ) {
			var a = document.createElement( 'a' );
			a.href = URL.createObjectURL( new Blob( [ get( id ) ], { type: 'text/css' } ) );
			a.download = b.getAttribute( 'data-name' ) + '.' + d.date + '.css';
			document.body.appendChild( a );
			a.click();
			setTimeout( function () {
				URL.revokeObjectURL( a.href );
				a.remove();
			}, 500 );
		}
	} );
	var pick = document.getElementById( 'qcss-pick' );
	function showTheme() {
		form.querySelectorAll( '.qcss-theme' ).forEach( function ( box ) {
			var on = box.getAttribute( 'data-theme' ) === pick.value;
			box.classList.toggle( 'is-on', on );
			if ( on ) {
				var cm = editors[ box.querySelector( 'textarea' ).id ];
				if ( cm ) {
					cm.refresh();
				}
			}
		} );
	}
	pick.addEventListener( 'change', showTheme );
	showTheme();
	// Live preview: the home page in a frame, with the CSS being typed instead of the saved CSS.
	var frame = document.getElementById( 'qcss-frame' );
	var holder = frame.parentNode;
	function fit() {
		frame.style.transform = 'scale(' + ( holder.clientWidth / 1280 ) + ')';
	}
	function preview() {
		var doc;
		try {
			doc = frame.contentDocument;
		} catch ( e ) {
			return;
		}
		if ( ! doc || ! doc.head ) {
			return;
		}
		var tag = doc.getElementById( 'qcss-live' );
		if ( ! tag ) {
			tag = doc.createElement( 'style' );
			tag.id = 'qcss-live';
			doc.head.appendChild( tag );
		}
		var on = form.querySelector( '[name="qot_css_inserter[enabled]"]' ).checked;
		var active = document.getElementById( 'qcss-theme-' + d.active );
		tag.textContent = '#wpadminbar{display:none!important}html{margin-top:0!important}\n' + ( on ? get( 'qcss-global' ) + '\n' + ( active ? get( active.id ) : '' ) : '' );
	}
	frame.addEventListener( 'load', preview );
	// The frame may have finished loading before this script ran.
	try {
		if ( frame.contentDocument && 'complete' === frame.contentDocument.readyState ) {
			preview();
		}
	} catch ( e ) {}
	window.addEventListener( 'resize', fit );
	form.addEventListener( 'input', preview );
	form.addEventListener( 'change', preview );
	fit();
}() );
JS;
}

/**
 * The Pretty print, Copy and Download buttons under a box.
 *
 * @param string $id   Textarea id.
 * @param string $name File name for downloads.
 */
function qot_css_inserter_tools( $id, $name ) {
	?>
	<div class="qcss-tools">
		<button type="button" class="button" data-qcss="pretty" data-for="<?php echo esc_attr( $id ); ?>"><span class="dashicons dashicons-editor-alignleft"></span><?php esc_html_e( 'Pretty print', 'qot-css-inserter' ); ?></button>
		<button type="button" class="button" data-qcss="copy" data-for="<?php echo esc_attr( $id ); ?>"><span class="dashicons dashicons-admin-page"></span><?php esc_html_e( 'Copy', 'qot-css-inserter' ); ?></button>
		<button type="button" class="button" data-qcss="download" data-for="<?php echo esc_attr( $id ); ?>" data-name="<?php echo esc_attr( $name ); ?>"><span class="dashicons dashicons-download"></span><?php esc_html_e( 'Download', 'qot-css-inserter' ); ?></button>
		<span class="qcss-done" aria-live="polite"></span>
	</div>
	<?php
}

/**
 * The settings page.
 */
function qot_css_inserter_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s      = qot_css_inserter_settings();
	$n      = QOT_CSS_INSERTER_OPTION;
	$active = get_stylesheet();
	$themes = wp_get_themes();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT CSS Inserter', 'qot-css-inserter' ); ?></h1>
		<p><?php esc_html_e( 'Your own CSS, added to every page of your site. The preview shows your home page with the CSS as you type it.', 'qot-css-inserter' ); ?></p>
		<div class="qcss-wrap">
			<form method="post" action="options.php" id="qcss-form">
				<?php settings_fields( 'qot_css_inserter' ); ?>
				<div class="qcss-card qcss-c1 qcss-box">
					<h2><?php esc_html_e( 'CSS for every theme', 'qot-css-inserter' ); ?></h2>
					<p class="description" style="margin-top:12px"><?php esc_html_e( 'Kept whatever theme you use. Good for things that belong to your site, like hiding an element or your brand colours.', 'qot-css-inserter' ); ?></p>
					<p><textarea id="qcss-global" class="qcss-code" name="<?php echo esc_attr( $n . '[global]' ); ?>" spellcheck="false"><?php echo esc_textarea( $s['global'] ); ?></textarea></p>
					<?php qot_css_inserter_tools( 'qcss-global', 'css-every-theme' ); ?>
				</div>
				<div class="qcss-card qcss-c2 qcss-box">
					<h2><?php esc_html_e( 'CSS for one theme', 'qot-css-inserter' ); ?></h2>
					<p class="qcss-pick">
						<label for="qcss-pick"><?php esc_html_e( 'Theme:', 'qot-css-inserter' ); ?></label>
						<select id="qcss-pick">
							<?php foreach ( $themes as $slug => $theme ) : ?>
								<?php
								$label = $theme->get( 'Name' ) . ( $slug === $active ? ' ' . __( '(active)', 'qot-css-inserter' ) : '' );
								if ( ! empty( $s['themes'][ $slug ] ) ) {
									$label .= ' ' . __( '- has CSS', 'qot-css-inserter' );
								}
								?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $active ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p class="description"><?php esc_html_e( 'Only added while that theme is active. Try a new theme with its own CSS, and switch back without losing anything.', 'qot-css-inserter' ); ?></p>
					<?php foreach ( $themes as $slug => $theme ) : ?>
						<div class="qcss-theme" data-theme="<?php echo esc_attr( $slug ); ?>">
							<p><textarea id="<?php echo esc_attr( 'qcss-theme-' . $slug ); ?>" class="qcss-code" name="<?php echo esc_attr( $n . '[themes][' . $slug . ']' ); ?>" spellcheck="false"><?php echo esc_textarea( isset( $s['themes'][ $slug ] ) ? $s['themes'][ $slug ] : '' ); ?></textarea></p>
							<?php qot_css_inserter_tools( 'qcss-theme-' . $slug, 'css-' . $slug ); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="qcss-card qcss-c3">
					<h2><?php esc_html_e( 'Options', 'qot-css-inserter' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Switch', 'qot-css-inserter' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[enabled]' ); ?>" value="1" <?php checked( $s['enabled'] ); ?> /> <?php esc_html_e( 'Add my CSS to the site', 'qot-css-inserter' ); ?></label>
							<p class="description"><?php esc_html_e( 'Untick to pause all of it at once, for example to check whether your CSS causes a problem.', 'qot-css-inserter' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Smaller pages', 'qot-css-inserter' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[shrink]' ); ?>" value="1" <?php checked( $s['shrink'] ); ?> /> <?php esc_html_e( 'Remove comments and spaces on the site', 'qot-css-inserter' ); ?></label>
							<p class="description"><?php esc_html_e( 'Your boxes here keep your comments and layout; visitors get the shorter version.', 'qot-css-inserter' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Editor', 'qot-css-inserter' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[editor]' ); ?>" value="1" <?php checked( $s['editor'] ); ?> /> <?php esc_html_e( 'Also use it in the block editor', 'qot-css-inserter' ); ?></label>
							<p class="description"><?php esc_html_e( 'So posts look the same while you write. Best for CSS about post content.', 'qot-css-inserter' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_css_inserter_help_box(); ?>
			</form>
			<div class="qcss-side qcss-card qcss-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-css-inserter' ); ?> <span class="qcss-live"><?php esc_html_e( 'Live', 'qot-css-inserter' ); ?></span></h2>
				<div class="qcss-screen"><div class="qcss-frame"><iframe id="qcss-frame" title="<?php esc_attr_e( 'Your home page', 'qot-css-inserter' ); ?>" src="<?php echo esc_url( add_query_arg( 'qcss-preview', '1', home_url( '/' ) ) ); ?>" tabindex="-1"></iframe></div></div>
				<p class="description"><?php esc_html_e( 'Your home page, with the CSS for every theme and the active theme\'s CSS as you type. Nothing changes for visitors until you save.', 'qot-css-inserter' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_css_inserter_plugin_links( admin_url( 'themes.php?page=qot-css-inserter' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function qot_css_inserter_uninstall() {
	delete_option( QOT_CSS_INSERTER_OPTION );
}
register_uninstall_hook( __FILE__, 'qot_css_inserter_uninstall' );
