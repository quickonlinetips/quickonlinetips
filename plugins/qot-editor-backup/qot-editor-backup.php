<?php
/**
 * Plugin Name:       QOT Editor Backup
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       Adds Copy and Download buttons above the Theme File Editor and Plugin File Editor, so you can save a backup of any file before you change it. Downloads can carry the date and time in their name.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-editor-backup
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_EDITOR_BACKUP_OPTION', 'qot_editor_backup' );
define( 'QOT_EDITOR_BACKUP_VERSION', '1.0' );

require_once __DIR__ . '/includes/admin-common.php';

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings.
 *
 * @return array
 */
function qot_editor_backup_defaults() {
	return array(
		'buttons'  => 'both',
		'look'     => 'both',
		'copy'     => __( 'Copy', 'qot-editor-backup' ),
		'download' => __( 'Download', 'qot-editor-backup' ),
		'color'    => 'wp',
		'bg'       => '#2271b1',
		'fg'       => '#ffffff',
		'stamp'    => 'datetime',
		'prefix'   => true,
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_editor_backup_settings() {
	$saved = get_option( QOT_EDITOR_BACKUP_OPTION, array() );
	return array_merge( qot_editor_backup_defaults(), is_array( $saved ) ? $saved : array() );
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_editor_backup_sanitize( $input ) {
	$d     = qot_editor_backup_defaults();
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
		'buttons'  => $pick( 'buttons', array( 'both', 'copy', 'download' ) ),
		'look'     => $pick( 'look', array( 'both', 'text', 'icon' ) ),
		'copy'     => $text( 'copy' ),
		'download' => $text( 'download' ),
		'color'    => $pick( 'color', array( 'wp', 'plain', 'own' ) ),
		'bg'       => $color( 'bg' ),
		'fg'       => $color( 'fg' ),
		'stamp'    => $pick( 'stamp', array( 'none', 'date', 'datetime' ) ),
		'prefix'   => ! empty( $input['prefix'] ),
	);
}

/**
 * The CSS for the buttons, shared by the editors and the preview.
 *
 * @param array $s Settings.
 * @return string
 */
function qot_editor_backup_button_css( $s ) {
	$css = '.qeb-bar{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin:0 0 8px}.qeb-bar .button .dashicons{vertical-align:text-bottom;margin-right:3px;font-size:17px;width:17px;height:17px}' .
		'.qeb-bar .qeb-icon .dashicons{margin:0}.qeb-done{color:#00a32a;font-weight:600;font-size:12px;opacity:0;transition:opacity .2s}.qeb-done.is-on{opacity:1}.qeb-name{color:#646970;font-size:12px}';
	if ( 'own' === $s['color'] ) {
		$css .= '.qeb-bar .button{background:' . $s['bg'] . ';border-color:' . $s['bg'] . ';color:' . $s['fg'] . '}.qeb-bar .button:hover,.qeb-bar .button:focus{background:' . $s['bg'] . ';color:' . $s['fg'] . ';filter:brightness(1.1)}';
	} elseif ( 'plain' === $s['color'] ) {
		$css .= '.qeb-bar .button{background:transparent;border-color:#c3c4c7;color:#2c3338}';
	}
	return $css;
}

/* ------------------------------------------------------------------ *
 * The editors
 * ------------------------------------------------------------------ */

/**
 * Add the buttons to the Theme and Plugin File Editors.
 *
 * @param string $hook Current admin screen.
 */
function qot_editor_backup_editor_assets( $hook ) {
	if ( 'theme-editor.php' !== $hook && 'plugin-editor.php' !== $hook ) {
		return;
	}
	$s = qot_editor_backup_settings();
	wp_register_style( 'qot-editor-backup', false, array(), QOT_EDITOR_BACKUP_VERSION );
	wp_enqueue_style( 'qot-editor-backup' );
	wp_add_inline_style( 'qot-editor-backup', qot_editor_backup_button_css( $s ) );
	wp_register_script( 'qot-editor-backup', false, array(), QOT_EDITOR_BACKUP_VERSION, true );
	wp_enqueue_script( 'qot-editor-backup' );
	wp_add_inline_script( 'qot-editor-backup', 'window.qebData=' . wp_json_encode( qot_editor_backup_script_data( $s ) ) . ';' . qot_editor_backup_script() );
}
add_action( 'admin_enqueue_scripts', 'qot_editor_backup_editor_assets' );

/**
 * Settings and words the script needs.
 *
 * @param array $s Settings.
 * @return array
 */
function qot_editor_backup_script_data( $s ) {
	return array(
		'buttons'  => $s['buttons'],
		'look'     => $s['look'],
		'copy'     => $s['copy'],
		'download' => $s['download'],
		'stamp'    => $s['stamp'],
		'prefix'   => (bool) $s['prefix'],
		'copied'   => __( 'Copied', 'qot-editor-backup' ),
		'failed'   => __( 'Press Ctrl+C to copy', 'qot-editor-backup' ),
		/* translators: %s: file name. */
		'saveAs'   => __( 'Saves as %s', 'qot-editor-backup' ),
	);
}

/**
 * The editor script. It reads the file from the code editor (or the plain
 * box when colouring is off), so even unsaved changes are copied.
 *
 * @return string
 */
function qot_editor_backup_script() {
	return <<<'JS'
( function () {
	var d = window.qebData;
	var box = document.getElementById( 'template' );
	var area = document.getElementById( 'newcontent' );
	if ( ! box || ! area ) {
		return;
	}
	function field( name ) {
		var el = document.querySelector( '#template input[name="' + name + '"], form input[name="' + name + '"]' );
		return el ? el.value : '';
	}
	function content() {
		var cm = box.querySelector( '.CodeMirror' );
		return cm && cm.CodeMirror ? cm.CodeMirror.getValue() : area.value;
	}
	function pad( n ) {
		return ( n < 10 ? '0' : '' ) + n;
	}
	function fileName() {
		var path = field( 'file' ) || 'file.txt';
		var base = path.split( '/' ).pop();
		var dot = base.lastIndexOf( '.' );
		var stem = dot > 0 ? base.slice( 0, dot ) : base;
		var ext = dot > 0 ? base.slice( dot ) : '';
		var owner = field( 'theme' ) || ( field( 'plugin' ).split( '/' )[ 0 ] || '' ).replace( /\.php$/, '' );
		var now = new Date();
		var stamp = '';
		if ( 'none' !== d.stamp ) {
			stamp = '.' + now.getFullYear() + '-' + pad( now.getMonth() + 1 ) + '-' + pad( now.getDate() );
			if ( 'datetime' === d.stamp ) {
				stamp += '-' + pad( now.getHours() ) + pad( now.getMinutes() );
			}
		}
		return ( d.prefix && owner ? owner + '-' : '' ) + stem + stamp + ext;
	}
	function button( kind, icon, label ) {
		var b = document.createElement( 'button' );
		b.type = 'button';
		b.className = 'button qeb-' + kind + ( 'icon' === d.look ? ' qeb-icon' : '' );
		b.title = label;
		if ( 'text' !== d.look ) {
			var i = document.createElement( 'span' );
			i.className = 'dashicons dashicons-' + icon;
			i.setAttribute( 'aria-hidden', 'true' );
			b.appendChild( i );
		}
		if ( 'icon' === d.look ) {
			b.setAttribute( 'aria-label', label );
		} else {
			b.appendChild( document.createTextNode( label ) );
		}
		bar.appendChild( b );
		return b;
	}
	var bar = document.createElement( 'div' );
	bar.className = 'qeb-bar';
	var done = document.createElement( 'span' );
	done.className = 'qeb-done';
	done.setAttribute( 'aria-live', 'polite' );
	function say( text ) {
		done.textContent = text;
		done.classList.add( 'is-on' );
		setTimeout( function () {
			done.classList.remove( 'is-on' );
		}, 2000 );
	}
	if ( 'download' !== d.buttons ) {
		button( 'copy', 'admin-page', d.copy ).addEventListener( 'click', function () {
			var text = content();
			if ( navigator.clipboard && window.isSecureContext ) {
				navigator.clipboard.writeText( text ).then( function () {
					say( d.copied );
				}, function () {
					say( d.failed );
				} );
				return;
			}
			var t = document.createElement( 'textarea' );
			t.value = text;
			t.style.position = 'fixed';
			t.style.opacity = '0';
			document.body.appendChild( t );
			t.select();
			var ok = false;
			try {
				ok = document.execCommand( 'copy' );
			} catch ( e ) {}
			document.body.removeChild( t );
			say( ok ? d.copied : d.failed );
		} );
	}
	if ( 'copy' !== d.buttons ) {
		var name = document.createElement( 'span' );
		name.className = 'qeb-name';
		button( 'download', 'download', d.download ).addEventListener( 'click', function () {
			var blob = new Blob( [ content() ], { type: 'text/plain;charset=utf-8' } );
			var a = document.createElement( 'a' );
			a.href = URL.createObjectURL( blob );
			a.download = fileName();
			name.textContent = d.saveAs.replace( '%s', a.download );
			document.body.appendChild( a );
			a.click();
			setTimeout( function () {
				URL.revokeObjectURL( a.href );
				a.remove();
			}, 500 );
		} );
		bar.appendChild( name );
		name.textContent = d.saveAs.replace( '%s', fileName() );
	}
	bar.appendChild( done );
	box.parentNode.insertBefore( bar, box );
}() );
JS;
}

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_editor_backup_register_setting() {
	register_setting(
		'qot_editor_backup',
		QOT_EDITOR_BACKUP_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_editor_backup_sanitize',
			'default'           => qot_editor_backup_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_editor_backup_register_setting' );

/**
 * Add the page under Settings.
 */
function qot_editor_backup_add_page() {
	$hook = add_options_page( __( 'QOT Editor Backup', 'qot-editor-backup' ), __( 'QOT Editor Backup', 'qot-editor-backup' ), 'manage_options', 'qot-editor-backup', 'qot_editor_backup_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_editor_backup_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_editor_backup_add_page' );

/**
 * Style and live preview for the settings page.
 */
function qot_editor_backup_admin_assets() {
	wp_register_style( 'qot-editor-backup-admin', false, array(), QOT_EDITOR_BACKUP_VERSION );
	wp_enqueue_style( 'qot-editor-backup-admin' );
	wp_add_inline_style(
		'qot-editor-backup-admin',
		qot_editor_backup_admin_base_css() .
		'#qeb-demo h3{margin:0 0 4px;font-size:14px}#qeb-demo .d-file{color:#50575e;font-size:12px;margin:0 0 10px}' .
		'#qeb-demo .d-code{background:#f6f7f7;border:1px solid #dcdcde;border-radius:3px;padding:10px 12px;font:12px/1.6 Consolas,Monaco,monospace;color:#1d2327;white-space:pre}'
	);
	wp_register_script( 'qot-editor-backup-admin', false, array(), QOT_EDITOR_BACKUP_VERSION, true );
	wp_enqueue_script( 'qot-editor-backup-admin' );
	wp_add_inline_script( 'qot-editor-backup-admin', 'window.qebData=' . wp_json_encode( qot_editor_backup_script_data( qot_editor_backup_settings() ) ) . ';' . qot_editor_backup_admin_script() );
}

/**
 * Preview script: draws the bar with the same markup the editors get.
 *
 * @return string
 */
function qot_editor_backup_admin_script() {
	return <<<'JS'
( function () {
	var form = document.getElementById( 'qeb-form' );
	var slot = document.getElementById( 'qeb-demo-bar' );
	var style = document.createElement( 'style' );
	document.head.appendChild( style );
	if ( ! form || ! slot ) {
		return;
	}
	function val( name ) {
		var el = form.querySelector( '[name="qot_editor_backup[' + name + ']"]:checked' ) || form.querySelector( '[name="qot_editor_backup[' + name + ']"]:not([type=radio]):not([type=checkbox])' );
		return el ? el.value : '';
	}
	function esc( t ) {
		return String( t ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[ c ];
		} );
	}
	function pad( n ) {
		return ( n < 10 ? '0' : '' ) + n;
	}
	function btn( icon, label, look ) {
		return '<button type="button" class="button' + ( 'icon' === look ? ' qeb-icon' : '' ) + '" title="' + esc( label ) + '">' +
			( 'text' !== look ? '<span class="dashicons dashicons-' + icon + '"></span>' : '' ) + ( 'icon' !== look ? esc( label ) : '' ) + '</button>';
	}
	function draw() {
		var look = val( 'look' );
		var which = val( 'buttons' );
		var stamp = val( 'stamp' );
		var prefix = form.querySelector( '[name="qot_editor_backup[prefix]"]' ).checked;
		var now = new Date();
		var s = '';
		if ( 'none' !== stamp ) {
			s = '.' + now.getFullYear() + '-' + pad( now.getMonth() + 1 ) + '-' + pad( now.getDate() ) + ( 'datetime' === stamp ? '-' + pad( now.getHours() ) + pad( now.getMinutes() ) : '' );
		}
		var name = ( prefix ? 'my-theme-' : '' ) + 'style' + s + '.css';
		var html = '';
		if ( 'download' !== which ) {
			html += btn( 'admin-page', val( 'copy' ), look );
		}
		if ( 'copy' !== which ) {
			html += btn( 'download', val( 'download' ), look ) + '<span class="qeb-name">' + esc( window.qebData.saveAs.replace( '%s', name ) ) + '</span>';
		}
		slot.innerHTML = html;
		var color = val( 'color' );
		var bg = val( 'bg' );
		var fg = val( 'fg' );
		style.textContent = 'own' === color ? '#qeb-demo .qeb-bar .button{background:' + bg + ';border-color:' + bg + ';color:' + fg + '}' :
			( 'plain' === color ? '#qeb-demo .qeb-bar .button{background:transparent;border-color:#c3c4c7;color:#2c3338}' : '' );
		document.getElementById( 'qeb-colors' ).style.display = 'own' === color ? '' : 'none';
	}
	document.getElementById( 'qeb-demo' ).addEventListener( 'click', function ( e ) {
		e.preventDefault();
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
function qot_editor_backup_radios( $key, $choices, $current ) {
	foreach ( $choices as $value => $label ) {
		printf(
			'<label style="margin-right:14px;display:inline-block"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label>',
			esc_attr( QOT_EDITOR_BACKUP_OPTION . '[' . $key . ']' ),
			esc_attr( $value ),
			checked( $current, $value, false ),
			esc_html( $label )
		);
	}
}

/**
 * The settings page.
 */
function qot_editor_backup_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = qot_editor_backup_settings();
	$n = QOT_EDITOR_BACKUP_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT Editor Backup', 'qot-editor-backup' ); ?></h1>
		<p><?php esc_html_e( 'Copy or download any file from the Theme and Plugin File Editors before you change it. The preview updates as you go.', 'qot-editor-backup' ); ?></p>
		<?php if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'File editing is switched off on this site (DISALLOW_FILE_EDIT), so the file editors and these buttons don\'t appear.', 'qot-editor-backup' ); ?></p></div>
		<?php endif; ?>
		<div class="qeb-wrap">
			<form method="post" action="options.php" id="qeb-form">
				<?php settings_fields( 'qot_editor_backup' ); ?>
				<div class="qeb-card qeb-c1">
					<h2><?php esc_html_e( 'Buttons', 'qot-editor-backup' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Show', 'qot-editor-backup' ); ?></th><td>
							<?php qot_editor_backup_radios( 'buttons', array( 'both' => __( 'Copy and Download', 'qot-editor-backup' ), 'download' => __( 'Download only', 'qot-editor-backup' ), 'copy' => __( 'Copy only', 'qot-editor-backup' ) ), $s['buttons'] ); ?>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Buttons show', 'qot-editor-backup' ); ?></th><td>
							<?php qot_editor_backup_radios( 'look', array( 'both' => __( 'Icon and text', 'qot-editor-backup' ), 'text' => __( 'Text only', 'qot-editor-backup' ), 'icon' => __( 'Icon only', 'qot-editor-backup' ) ), $s['look'] ); ?>
						</td></tr>
						<tr><th scope="row"><label for="qeb-copy"><?php esc_html_e( 'Button words', 'qot-editor-backup' ); ?></label></th><td>
							<input type="text" id="qeb-copy" class="regular-text" style="width:10em" name="<?php echo esc_attr( $n . '[copy]' ); ?>" value="<?php echo esc_attr( $s['copy'] ); ?>" aria-label="<?php esc_attr_e( 'Copy button', 'qot-editor-backup' ); ?>" />
							<input type="text" class="regular-text" style="width:10em" name="<?php echo esc_attr( $n . '[download]' ); ?>" value="<?php echo esc_attr( $s['download'] ); ?>" aria-label="<?php esc_attr_e( 'Download button', 'qot-editor-backup' ); ?>" />
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Colours', 'qot-editor-backup' ); ?></th><td>
							<?php qot_editor_backup_radios( 'color', array( 'wp' => __( 'WordPress buttons', 'qot-editor-backup' ), 'plain' => __( 'Empty box', 'qot-editor-backup' ), 'own' => __( 'My colours', 'qot-editor-backup' ) ), $s['color'] ); ?>
							<p id="qeb-colors" style="margin-top:8px">
								<label><input type="color" name="<?php echo esc_attr( $n . '[bg]' ); ?>" value="<?php echo esc_attr( $s['bg'] ); ?>" /> <?php esc_html_e( 'Button', 'qot-editor-backup' ); ?></label>
								<label style="margin-left:16px"><input type="color" name="<?php echo esc_attr( $n . '[fg]' ); ?>" value="<?php echo esc_attr( $s['fg'] ); ?>" /> <?php esc_html_e( 'Text', 'qot-editor-backup' ); ?></label>
							</p>
						</td></tr>
					</table>
				</div>
				<div class="qeb-card qeb-c2">
					<h2><?php esc_html_e( 'Downloaded file name', 'qot-editor-backup' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Add to the name', 'qot-editor-backup' ); ?></th><td>
							<?php qot_editor_backup_radios( 'stamp', array( 'datetime' => __( 'Date and time', 'qot-editor-backup' ), 'date' => __( 'Date', 'qot-editor-backup' ), 'none' => __( 'Nothing', 'qot-editor-backup' ) ), $s['stamp'] ); ?>
							<p class="description"><?php esc_html_e( 'With the date and time, each backup gets its own name, so a new download never replaces an older one.', 'qot-editor-backup' ); ?></p>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Theme or plugin', 'qot-editor-backup' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[prefix]' ); ?>" value="1" <?php checked( $s['prefix'] ); ?> /> <?php esc_html_e( 'Start the name with the theme or plugin it came from', 'qot-editor-backup' ); ?></label>
							<p class="description"><?php esc_html_e( 'So two style.css files from different themes are easy to tell apart in your Downloads folder.', 'qot-editor-backup' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_editor_backup_help_box(); ?>
			</form>
			<div class="qeb-side qeb-card qeb-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-editor-backup' ); ?> <span class="qeb-live"><?php esc_html_e( 'Live', 'qot-editor-backup' ); ?></span></h2>
				<div class="qeb-screen" id="qeb-demo">
					<style><?php echo qot_editor_backup_button_css( array( 'color' => 'wp' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed CSS. ?></style>
					<h3><?php esc_html_e( 'Editing My Theme', 'qot-editor-backup' ); ?></h3>
					<p class="d-file"><?php esc_html_e( 'File: style.css', 'qot-editor-backup' ); ?></p>
					<div class="qeb-bar" id="qeb-demo-bar"></div>
					<div class="d-code">/*
Theme Name: My Theme
*/
body { font-size: 16px; }</div>
				</div>
				<p class="description"><?php esc_html_e( 'The buttons as they appear above the file editor, with the name a download gets today.', 'qot-editor-backup' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_editor_backup_plugin_links( admin_url( 'options-general.php?page=qot-editor-backup' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function qot_editor_backup_uninstall() {
	delete_option( QOT_EDITOR_BACKUP_OPTION );
}
register_uninstall_hook( __FILE__, 'qot_editor_backup_uninstall' );
