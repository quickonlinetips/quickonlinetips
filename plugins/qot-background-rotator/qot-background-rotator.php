<?php
/**
 * Plugin Name:       QOT Background Rotator
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       Your site's background changes colour on every page, from a set of colours you choose. Pick a ready-made set (pastels, dark pastels, earth, sea, greys) or make your own.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-background-rotator
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_BACKGROUND_ROTATOR_OPTION', 'qot_background_rotator' );
define( 'QOT_BACKGROUND_ROTATOR_VERSION', '1.0' );
define( 'QOT_BACKGROUND_ROTATOR_MAX', 10 );

require_once __DIR__ . '/includes/admin-common.php';

/**
 * Ready-made colour sets.
 *
 * @return array<string, array{label: string, colors: string[]}>
 */
function qot_background_rotator_sets() {
	return array(
		'pastels'      => array(
			'label'  => __( 'Pastels', 'qot-background-rotator' ),
			'colors' => array( '#fde2e4', '#fad2e1', '#e2ece9', '#bee1e6', '#f0efeb', '#dfe7fd', '#cddafd', '#fff1c1' ),
		),
		'dark_pastels' => array(
			'label'  => __( 'Dark pastels', 'qot-background-rotator' ),
			'colors' => array( '#6d597a', '#b56576', '#355070', '#4a6670', '#5f7a61', '#7d6b91', '#8a6a5c', '#50616e' ),
		),
		'earth'        => array(
			'label'  => __( 'Earth', 'qot-background-rotator' ),
			'colors' => array( '#e9dcc9', '#d6c3a5', '#c8b6a6', '#e3d5ca', '#d5bdaf', '#ccd5ae', '#e9edc9', '#fefae0' ),
		),
		'sea'          => array(
			'label'  => __( 'Sea', 'qot-background-rotator' ),
			'colors' => array( '#d8f3f2', '#c4e8f0', '#b8dedf', '#cde9f6', '#e0f2f1', '#d0ebe8', '#bde0fe', '#caf0f8' ),
		),
		'greys'        => array(
			'label'  => __( 'Soft greys', 'qot-background-rotator' ),
			'colors' => array( '#f8f9fa', '#f1f3f5', '#e9ecef', '#dee2e6', '#eceff1', '#f5f5f4', '#e7e5e4', '#f4f4f5' ),
		),
	);
}

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings.
 *
 * @return array
 */
function qot_background_rotator_defaults() {
	$pastels = qot_background_rotator_sets()['pastels']['colors'];
	return array(
		'enabled' => true,
		'set'     => 'pastels',
		'custom'  => array_merge( array_slice( $pastels, 0, 6 ), array( '#ffe5d9', '#d8e2dc', '#ece4db', '#f4acb7' ) ),
		'use'     => array( true, true, true, true, true, true, false, false, false, false ),
		'mode'    => 'random',
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_background_rotator_settings() {
	$saved = get_option( QOT_BACKGROUND_ROTATOR_OPTION, array() );
	return array_merge( qot_background_rotator_defaults(), is_array( $saved ) ? $saved : array() );
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_background_rotator_sanitize( $input ) {
	$d      = qot_background_rotator_defaults();
	$input  = is_array( $input ) ? $input : array();
	$custom = array();
	$use    = array();
	for ( $i = 0; $i < QOT_BACKGROUND_ROTATOR_MAX; $i++ ) {
		$color      = isset( $input['custom'][ $i ] ) ? sanitize_hex_color( $input['custom'][ $i ] ) : '';
		$custom[]   = $color ? $color : $d['custom'][ $i ];
		$use[]      = ! empty( $input['use'][ $i ] );
	}
	return array(
		'enabled' => ! empty( $input['enabled'] ),
		'set'     => ( isset( $input['set'] ) && ( 'custom' === $input['set'] || array_key_exists( $input['set'], qot_background_rotator_sets() ) ) ) ? $input['set'] : $d['set'],
		'custom'  => $custom,
		'use'     => $use,
		'mode'    => ( isset( $input['mode'] ) && in_array( $input['mode'], array( 'random', 'turn', 'day' ), true ) ) ? $input['mode'] : $d['mode'],
	);
}

/**
 * The colours in use right now.
 *
 * @return string[]
 */
function qot_background_rotator_colors() {
	$s = qot_background_rotator_settings();
	if ( 'custom' !== $s['set'] ) {
		$sets = qot_background_rotator_sets();
		return $sets[ $s['set'] ]['colors'];
	}
	$colors = array();
	foreach ( $s['custom'] as $i => $color ) {
		if ( ! empty( $s['use'][ $i ] ) ) {
			$colors[] = $color;
		}
	}
	return $colors;
}

/* ------------------------------------------------------------------ *
 * On the site
 * ------------------------------------------------------------------ */

/**
 * Pick the colour in the browser, before the page is drawn, so it works
 * with page caching and never flashes the old colour.
 */
function qot_background_rotator_head() {
	$s      = qot_background_rotator_settings();
	$colors = qot_background_rotator_colors();
	if ( ! $s['enabled'] || count( $colors ) < 1 || is_admin() || is_feed() ) {
		return;
	}
	$script = '(function(){var c=' . wp_json_encode( array_values( $colors ) ) . ',m=' . wp_json_encode( $s['mode'] ) . ',i=0;' .
		'if("day"===m){i=Math.floor(Date.now()/864e5)%c.length;}' .
		'else if("turn"===m){try{i=(parseInt(localStorage.getItem("qbr"),10)+1)%c.length;if(isNaN(i))i=0;localStorage.setItem("qbr",i);}catch(e){i=0;}}' .
		'else{i=Math.floor(Math.random()*c.length);}' .
		'document.documentElement.style.setProperty("--qbr-bg",c[i]);})();';
	// The script and CSS are built only from sanitized hex colours and fixed text.
	echo "<script id=\"qot-background-rotator\">{$script}</script>\n<style id=\"qot-background-rotator-css\">html body{background-color:var(--qbr-bg)!important}</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'qot_background_rotator_head', 1 );

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_background_rotator_register_setting() {
	register_setting(
		'qot_background_rotator',
		QOT_BACKGROUND_ROTATOR_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_background_rotator_sanitize',
			'default'           => qot_background_rotator_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_background_rotator_register_setting' );

/**
 * Add the page under Appearance, where people look for backgrounds.
 */
function qot_background_rotator_add_page() {
	$hook = add_theme_page( __( 'QOT Background Rotator', 'qot-background-rotator' ), __( 'Background Rotator', 'qot-background-rotator' ), 'manage_options', 'qot-background-rotator', 'qot_background_rotator_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_background_rotator_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_background_rotator_add_page' );

/**
 * Style and live preview for the settings page.
 */
function qot_background_rotator_admin_assets() {
	wp_register_style( 'qot-background-rotator-admin', false, array(), QOT_BACKGROUND_ROTATOR_VERSION );
	wp_enqueue_style( 'qot-background-rotator-admin' );
	wp_add_inline_style(
		'qot-background-rotator-admin',
		qot_background_rotator_admin_base_css() .
		'.qbr-sets label{display:flex;align-items:center;gap:8px;margin:0 0 8px}.qbr-sw{display:inline-flex;gap:3px}.qbr-sw i{width:18px;height:18px;border-radius:3px;border:1px solid rgba(0,0,0,.12)}' .
		'.qbr-custom{display:grid;grid-template-columns:repeat(5,auto);gap:8px 14px;justify-content:start}.qbr-custom label{display:flex;align-items:center;gap:4px}.qbr-custom input[type=color]{width:42px;height:30px;padding:1px}' .
		'#qbr-demo{padding:18px;border-radius:4px;transition:background-color .6s;font:12px/1.4 -apple-system,sans-serif}#qbr-demo .d-page{background:#fff;border-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,.12);max-width:82%;margin:0 auto;padding:12px}' .
		'#qbr-demo .d-head{font-weight:700;margin:0 0 10px;color:#1d2327}#qbr-demo .d-cols{display:flex;gap:10px}#qbr-demo .d-main{flex:1}#qbr-demo .d-side{flex:0 0 30%;background:#f6f7f7;border-radius:3px;padding:6px}' .
		'#qbr-demo i{display:block;height:7px;background:#dcdcde;border-radius:3px;margin:0 0 7px}.qbr-screen{padding:0!important}' .
		'#qbr-strip{display:flex;gap:4px;margin:12px 0 0}#qbr-strip i{flex:1;height:14px;border-radius:3px;border:1px solid rgba(255,255,255,.35)}#qbr-strip i.is-on{outline:2px solid #fff;outline-offset:1px}' .
		'#qbr-mix{display:none;margin:10px 0 0;padding:6px 10px;border-radius:4px;background:#fcf9e8;color:#1d2327}'
	);
	$sets = array();
	foreach ( qot_background_rotator_sets() as $key => $set ) {
		$sets[ $key ] = $set['colors'];
	}
	wp_register_script( 'qot-background-rotator-admin', false, array(), QOT_BACKGROUND_ROTATOR_VERSION, true );
	wp_enqueue_script( 'qot-background-rotator-admin' );
	wp_add_inline_script( 'qot-background-rotator-admin', 'window.qbrSets=' . wp_json_encode( $sets ) . ';' . qot_background_rotator_admin_script() );
}

/**
 * Preview script: plays the chosen colours one after another.
 *
 * @return string
 */
function qot_background_rotator_admin_script() {
	return <<<'JS'
( function () {
	var form = document.getElementById( 'qbr-form' );
	var demo = document.getElementById( 'qbr-demo' );
	var strip = document.getElementById( 'qbr-strip' );
	var mix = document.getElementById( 'qbr-mix' );
	if ( ! form || ! demo ) {
		return;
	}
	var colors = [];
	var step = 0;
	function light( hex ) {
		var n = parseInt( hex.slice( 1 ), 16 );
		var r = ( n >> 16 ) & 255, g = ( n >> 8 ) & 255, b = n & 255;
		return ( 0.299 * r + 0.587 * g + 0.114 * b ) > 140;
	}
	function read() {
		var set = form.querySelector( '[name="qot_background_rotator[set]"]:checked' ).value;
		if ( 'custom' === set ) {
			colors = [];
			for ( var i = 0; i < 10; i++ ) {
				if ( form.querySelector( '[name="qot_background_rotator[use][' + i + ']"]' ).checked ) {
					colors.push( form.querySelector( '[name="qot_background_rotator[custom][' + i + ']"]' ).value );
				}
			}
		} else {
			colors = window.qbrSets[ set ].slice();
		}
		document.getElementById( 'qbr-custom-row' ).style.display = 'custom' === set ? '' : 'none';
		strip.innerHTML = colors.map( function ( c ) {
			return '<i style="background:' + c + '"></i>';
		} ).join( '' );
		var lights = colors.filter( light ).length;
		mix.style.display = lights && lights < colors.length ? 'block' : 'none';
		step = 0;
		tick();
	}
	function tick() {
		if ( ! colors.length ) {
			demo.style.backgroundColor = '#f0f0f1';
			return;
		}
		step = step % colors.length;
		demo.style.backgroundColor = colors[ step ];
		Array.prototype.forEach.call( strip.children, function ( el, i ) {
			el.classList.toggle( 'is-on', i === step );
		} );
		step++;
	}
	setInterval( tick, 1400 );
	form.addEventListener( 'input', read );
	form.addEventListener( 'change', read );
	read();
}() );
JS;
}

/**
 * The settings page.
 */
function qot_background_rotator_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = qot_background_rotator_settings();
	$n = QOT_BACKGROUND_ROTATOR_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT Background Rotator', 'qot-background-rotator' ); ?></h1>
		<p><?php esc_html_e( 'A fun touch: the background behind your site changes colour on every page. Your header, posts and sidebar keep their own colours. The preview plays your colours one after another.', 'qot-background-rotator' ); ?></p>
		<div class="qbr-wrap">
			<form method="post" action="options.php" id="qbr-form">
				<?php settings_fields( 'qot_background_rotator' ); ?>
				<div class="qbr-card qbr-c1">
					<h2><?php esc_html_e( 'Colours', 'qot-background-rotator' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Switch', 'qot-background-rotator' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[enabled]' ); ?>" value="1" <?php checked( $s['enabled'] ); ?> /> <?php esc_html_e( 'Change the background colour on my site', 'qot-background-rotator' ); ?></label>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Colour set', 'qot-background-rotator' ); ?></th><td class="qbr-sets">
							<?php foreach ( qot_background_rotator_sets() as $key => $set ) : ?>
								<label><input type="radio" name="<?php echo esc_attr( $n . '[set]' ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $s['set'], $key ); ?> />
									<span style="min-width:100px"><?php echo esc_html( $set['label'] ); ?></span>
									<span class="qbr-sw"><?php foreach ( $set['colors'] as $color ) : ?><i style="background:<?php echo esc_attr( $color ); ?>"></i><?php endforeach; ?></span>
								</label>
							<?php endforeach; ?>
							<label><input type="radio" name="<?php echo esc_attr( $n . '[set]' ); ?>" value="custom" <?php checked( $s['set'], 'custom' ); ?> /> <?php esc_html_e( 'My own colours', 'qot-background-rotator' ); ?></label>
						</td></tr>
						<tr id="qbr-custom-row"><th scope="row"><?php esc_html_e( 'My own colours', 'qot-background-rotator' ); ?></th><td>
							<div class="qbr-custom">
								<?php for ( $i = 0; $i < QOT_BACKGROUND_ROTATOR_MAX; $i++ ) : ?>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( $n . '[use][' . $i . ']' ); ?>" value="1" <?php checked( ! empty( $s['use'][ $i ] ) ); ?> aria-label="<?php echo esc_attr( sprintf( /* translators: %d: colour number. */ __( 'Use colour %d', 'qot-background-rotator' ), $i + 1 ) ); ?>" />
										<input type="color" name="<?php echo esc_attr( $n . '[custom][' . $i . ']' ); ?>" value="<?php echo esc_attr( $s['custom'][ $i ] ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: colour number. */ __( 'Colour %d', 'qot-background-rotator' ), $i + 1 ) ); ?>" />
									</label>
								<?php endfor; ?>
							</div>
							<p class="description"><?php esc_html_e( 'Tick the colours to use, up to ten. Five to eight gentle colours usually look best.', 'qot-background-rotator' ); ?></p>
						</td></tr>
					</table>
				</div>
				<div class="qbr-card qbr-c2">
					<h2><?php esc_html_e( 'When it changes', 'qot-background-rotator' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Change', 'qot-background-rotator' ); ?></th><td>
							<?php
							$modes = array(
								'random' => __( 'A random colour on every page', 'qot-background-rotator' ),
								'turn'   => __( 'The next colour in turn on every page', 'qot-background-rotator' ),
								'day'    => __( 'One colour for everyone, a new one each day', 'qot-background-rotator' ),
							);
							foreach ( $modes as $value => $label ) {
								printf( '<label style="display:block;margin:0 0 6px"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label>', esc_attr( $n . '[mode]' ), esc_attr( $value ), checked( $s['mode'], $value, false ), esc_html( $label ) );
							}
							?>
							<p class="description"><?php esc_html_e( 'The colour is picked in the visitor\'s browser, so it works even when your pages are cached.', 'qot-background-rotator' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_background_rotator_help_box(); ?>
			</form>
			<div class="qbr-side qbr-card qbr-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-background-rotator' ); ?> <span class="qbr-live"><?php esc_html_e( 'Live', 'qot-background-rotator' ); ?></span></h2>
				<div class="qbr-screen">
					<div id="qbr-demo"><div class="d-page"><div class="d-head"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div><div class="d-cols"><div class="d-main"><i></i><i></i><i style="width:70%"></i><i></i><i></i><i style="width:50%"></i></div><div class="d-side"><i></i><i></i><i></i></div></div></div></div>
				</div>
				<div id="qbr-strip"></div>
				<p id="qbr-mix"><?php esc_html_e( 'This set mixes light and dark colours. Text that sits straight on the background may be hard to read on some of them.', 'qot-background-rotator' ); ?></p>
				<p class="description"><?php esc_html_e( 'Each colour shows for a moment, the way visitors see a new one on each page. The strip shows the whole set.', 'qot-background-rotator' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_background_rotator_plugin_links( admin_url( 'themes.php?page=qot-background-rotator' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function qot_background_rotator_uninstall() {
	delete_option( QOT_BACKGROUND_ROTATOR_OPTION );
}
register_uninstall_hook( __FILE__, 'qot_background_rotator_uninstall' );
