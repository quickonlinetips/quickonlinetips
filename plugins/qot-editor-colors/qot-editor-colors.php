<?php
/**
 * Plugin Name:       QOT Editor Colors
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       Pick a colour scheme, text size and line spacing for the code editor in Appearance > Theme File Editor, Tools > Plugin File Editor and Additional CSS. A button above the editor turns the colours on and off.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-editor-colors
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_EDITOR_COLORS_OPTION', 'qot_editor_colors' );
define( 'QOT_EDITOR_COLORS_VERSION', '1.0' );

require_once __DIR__ . '/includes/admin-common.php';

/**
 * The colour schemes. Each colour is used for one kind of code.
 *
 * @return array<string, array>
 */
function qot_editor_colors_schemes() {
	return array(
		'night'    => array(
			'label' => __( 'Night (dark grey)', 'qot-editor-colors' ),
			'bg'    => '#1e2433', 'text' => '#e6edf3', 'gutter' => '#171c28', 'num' => '#6b7690', 'line' => '#252c3d', 'select' => '#34405a', 'cursor' => '#ffffff',
			'keyword' => '#ff7b72', 'string' => '#a5d6ff', 'number' => '#79c0ff', 'comment' => '#8b949e', 'tag' => '#7ee787', 'attr' => '#d2a8ff', 'prop' => '#79c0ff', 'def' => '#ffa657', 'var' => '#e6edf3', 'meta' => '#ffa657',
		),
		'dusk'     => array(
			'label' => __( 'Dusk (deep blue)', 'qot-editor-colors' ),
			'bg'    => '#14213d', 'text' => '#e5ecf6', 'gutter' => '#0f1a31', 'num' => '#5f7398', 'line' => '#1b2b4d', 'select' => '#2d4373', 'cursor' => '#fca311',
			'keyword' => '#fca311', 'string' => '#8fd694', 'number' => '#f4a6c6', 'comment' => '#7d8fb3', 'tag' => '#5fd3f3', 'attr' => '#fcd581', 'prop' => '#9ad1ff', 'def' => '#ffcf70', 'var' => '#e5ecf6', 'meta' => '#f4a6c6',
		),
		'soft'     => array(
			'label' => __( 'Soft light', 'qot-editor-colors' ),
			'bg'    => '#fbfbfd', 'text' => '#24292f', 'gutter' => '#f2f3f7', 'num' => '#9aa0ae', 'line' => '#f0f3fa', 'select' => '#dbe7fb', 'cursor' => '#24292f',
			'keyword' => '#cf222e', 'string' => '#0a3069', 'number' => '#0550ae', 'comment' => '#6e7781', 'tag' => '#116329', 'attr' => '#8250df', 'prop' => '#0550ae', 'def' => '#953800', 'var' => '#24292f', 'meta' => '#953800',
		),
		'paper'    => array(
			'label' => __( 'Paper (warm sepia)', 'qot-editor-colors' ),
			'bg'    => '#f6f0e3', 'text' => '#433422', 'gutter' => '#ede4d0', 'num' => '#a8987a', 'line' => '#efe6d2', 'select' => '#e3d5b4', 'cursor' => '#433422',
			'keyword' => '#a3402b', 'string' => '#3f6b2a', 'number' => '#8a4f9e', 'comment' => '#9a8a6c', 'tag' => '#1f6a7a', 'attr' => '#9b5d12', 'prop' => '#1f6a7a', 'def' => '#a3402b', 'var' => '#433422', 'meta' => '#8a4f9e',
		),
		'contrast' => array(
			'label' => __( 'High contrast', 'qot-editor-colors' ),
			'bg'    => '#000000', 'text' => '#ffffff', 'gutter' => '#000000', 'num' => '#bbbbbb', 'line' => '#1a1a1a', 'select' => '#264f78', 'cursor' => '#ffff00',
			'keyword' => '#ffd700', 'string' => '#7cfc00', 'number' => '#00ffff', 'comment' => '#c0c0c0', 'tag' => '#00bfff', 'attr' => '#ffa500', 'prop' => '#87cefa', 'def' => '#ff69b4', 'var' => '#ffffff', 'meta' => '#ffa500',
		),
	);
}

/**
 * The CSS for one scheme, limited to editors inside $scope.
 *
 * @param string $key   Scheme key.
 * @param string $scope Selector the editor sits in.
 * @param int    $size  Text size in pixels.
 * @param float  $lh    Line spacing.
 * @return string
 */
function qot_editor_colors_css( $key, $scope, $size, $lh ) {
	$schemes = qot_editor_colors_schemes();
	if ( ! isset( $schemes[ $key ] ) ) {
		return '';
	}
	$c   = $schemes[ $key ];
	$cm  = $scope . ' .CodeMirror';
	$css = $cm . '{background:' . $c['bg'] . ';color:' . $c['text'] . ';font-size:' . (int) $size . 'px;line-height:' . $lh . '}' .
		$cm . ' .CodeMirror-gutters{background:' . $c['gutter'] . ';border-right-color:' . $c['line'] . '}' .
		$cm . ' .CodeMirror-linenumber{color:' . $c['num'] . '}' .
		$cm . ' .CodeMirror-cursor{border-left-color:' . $c['cursor'] . '}' .
		$cm . ' .CodeMirror-selected,' . $cm . '-focused .CodeMirror-selected{background:' . $c['select'] . '}' .
		$cm . ' .CodeMirror-activeline-background{background:' . $c['line'] . '}' .
		$cm . ' .CodeMirror-matchingbracket{color:inherit!important;outline:1px solid ' . $c['num'] . '}';
	$map = array(
		'keyword' => '.cm-keyword',
		'string'  => '.cm-string,.cm-string-2',
		'number'  => '.cm-number,.cm-atom',
		'comment' => '.cm-comment',
		'tag'     => '.cm-tag,.cm-builtin',
		'attr'    => '.cm-attribute,.cm-qualifier',
		'prop'    => '.cm-property',
		'def'     => '.cm-def,.cm-variable-3,.cm-type',
		'var'     => '.cm-variable,.cm-variable-2,.cm-operator,.cm-bracket',
		'meta'    => '.cm-meta,.cm-header',
	);
	foreach ( $map as $color => $classes ) {
		$parts = array();
		foreach ( explode( ',', $classes ) as $class ) {
			$parts[] = $cm . ' ' . $class;
		}
		$css .= implode( ',', $parts ) . '{color:' . $c[ $color ] . '}';
	}
	return $css . $cm . ' .cm-comment{font-style:italic}';
}

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings.
 *
 * @return array
 */
function qot_editor_colors_defaults() {
	return array(
		'scheme'     => 'night',
		'size'       => 14,
		'lh'         => '1.6',
		'theme_ed'   => true,
		'plugin_ed'  => true,
		'custom_css' => true,
		'toggle'     => true,
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_editor_colors_settings() {
	$saved = get_option( QOT_EDITOR_COLORS_OPTION, array() );
	return array_merge( qot_editor_colors_defaults(), is_array( $saved ) ? $saved : array() );
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_editor_colors_sanitize( $input ) {
	$d     = qot_editor_colors_defaults();
	$input = is_array( $input ) ? $input : array();
	$lh    = isset( $input['lh'] ) ? (string) $input['lh'] : $d['lh'];
	return array(
		'scheme'     => ( isset( $input['scheme'] ) && array_key_exists( $input['scheme'], qot_editor_colors_schemes() ) ) ? $input['scheme'] : $d['scheme'],
		'size'       => isset( $input['size'] ) ? min( 22, max( 11, absint( $input['size'] ) ) ) : $d['size'],
		'lh'         => in_array( $lh, array( '1.3', '1.45', '1.6', '1.8', '2' ), true ) ? $lh : $d['lh'],
		'theme_ed'   => ! empty( $input['theme_ed'] ),
		'plugin_ed'  => ! empty( $input['plugin_ed'] ),
		'custom_css' => ! empty( $input['custom_css'] ),
		'toggle'     => ! empty( $input['toggle'] ),
	);
}

/**
 * Has this user turned off code colouring in their profile? Then WordPress
 * shows a plain box with no colours at all, whatever the scheme.
 *
 * @return bool
 */
function qot_editor_colors_profile_off() {
	return 'false' === get_user_meta( get_current_user_id(), 'syntax_highlighting', true );
}

/* ------------------------------------------------------------------ *
 * The editors
 * ------------------------------------------------------------------ */

/**
 * Add the scheme and the on/off button to the file editors.
 *
 * @param string $hook Current admin screen.
 */
function qot_editor_colors_editor_assets( $hook ) {
	$s     = qot_editor_colors_settings();
	$wants = ( 'theme-editor.php' === $hook && $s['theme_ed'] ) || ( 'plugin-editor.php' === $hook && $s['plugin_ed'] );
	if ( ! $wants ) {
		return;
	}
	if ( qot_editor_colors_profile_off() ) {
		add_action( 'admin_notices', 'qot_editor_colors_profile_notice' );
		return;
	}
	wp_register_style( 'qot-editor-colors', false, array(), QOT_EDITOR_COLORS_VERSION );
	wp_enqueue_style( 'qot-editor-colors' );
	wp_add_inline_style(
		'qot-editor-colors',
		qot_editor_colors_css( $s['scheme'], 'html:not(.qec-off) #template', $s['size'], $s['lh'] ) .
		'.qec-toggle{margin:0 0 8px!important}.qec-toggle .dashicons{vertical-align:text-bottom;margin-right:3px}'
	);
	if ( $s['toggle'] ) {
		wp_register_script( 'qot-editor-colors', false, array(), QOT_EDITOR_COLORS_VERSION, true );
		wp_enqueue_script( 'qot-editor-colors' );
		wp_add_inline_script(
			'qot-editor-colors',
			'window.qecText=' . wp_json_encode(
				array(
					'on'  => __( 'Colours: on', 'qot-editor-colors' ),
					'off' => __( 'Colours: off', 'qot-editor-colors' ),
				)
			) . ';' . qot_editor_colors_toggle_script()
		);
	}
}
add_action( 'admin_enqueue_scripts', 'qot_editor_colors_editor_assets' );

/**
 * The on/off button. The choice is kept in this browser.
 *
 * @return string
 */
function qot_editor_colors_toggle_script() {
	return <<<'JS'
( function () {
	var box = document.getElementById( 'template' );
	if ( ! box ) {
		return;
	}
	var root = document.documentElement;
	var off = false;
	try {
		off = '1' === window.localStorage.getItem( 'qec-off' );
	} catch ( e ) {}
	var btn = document.createElement( 'button' );
	btn.type = 'button';
	btn.className = 'button button-small qec-toggle';
	function show() {
		root.classList.toggle( 'qec-off', off );
		btn.innerHTML = '<span class="dashicons dashicons-art"></span>';
		btn.appendChild( document.createTextNode( off ? window.qecText.off : window.qecText.on ) );
		btn.setAttribute( 'aria-pressed', off ? 'false' : 'true' );
	}
	btn.addEventListener( 'click', function () {
		off = ! off;
		try {
			window.localStorage.setItem( 'qec-off', off ? '1' : '0' );
		} catch ( e ) {}
		show();
	} );
	box.parentNode.insertBefore( btn, box );
	show();
}() );
JS;
}

/**
 * Colours in Appearance > Customize > Additional CSS.
 */
function qot_editor_colors_customizer() {
	$s = qot_editor_colors_settings();
	if ( ! $s['custom_css'] || qot_editor_colors_profile_off() ) {
		return;
	}
	wp_register_style( 'qot-editor-colors-customizer', false, array(), QOT_EDITOR_COLORS_VERSION );
	wp_enqueue_style( 'qot-editor-colors-customizer' );
	wp_add_inline_style( 'qot-editor-colors-customizer', qot_editor_colors_css( $s['scheme'], '#customize-control-custom_css', $s['size'], $s['lh'] ) );
}
add_action( 'customize_controls_enqueue_scripts', 'qot_editor_colors_customizer' );

/**
 * Tell the user why there are no colours when their profile turns them off.
 */
function qot_editor_colors_profile_notice() {
	printf(
		'<div class="notice notice-warning"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
		esc_html__( 'QOT Editor Colors: code colouring is turned off in your profile ("Disable syntax highlighting when editing code"), so the editor shows plain text.', 'qot-editor-colors' ),
		esc_url( admin_url( 'profile.php#syntax_highlighting' ) ),
		esc_html__( 'Turn it back on in your profile', 'qot-editor-colors' )
	);
}

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_editor_colors_register_setting() {
	register_setting(
		'qot_editor_colors',
		QOT_EDITOR_COLORS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_editor_colors_sanitize',
			'default'           => qot_editor_colors_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_editor_colors_register_setting' );

/**
 * Add the page under Settings.
 */
function qot_editor_colors_add_page() {
	$hook = add_options_page( __( 'QOT Editor Colors', 'qot-editor-colors' ), __( 'QOT Editor Colors', 'qot-editor-colors' ), 'manage_options', 'qot-editor-colors', 'qot_editor_colors_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_editor_colors_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_editor_colors_add_page' );

/**
 * Style and live preview for the settings page.
 */
function qot_editor_colors_admin_assets() {
	wp_register_style( 'qot-editor-colors-admin', false, array(), QOT_EDITOR_COLORS_VERSION );
	wp_enqueue_style( 'qot-editor-colors-admin' );
	wp_add_inline_style(
		'qot-editor-colors-admin',
		qot_editor_colors_admin_base_css() .
		'#qec-demo .CodeMirror{display:flex;font-family:Consolas,Monaco,monospace;border-radius:4px;overflow:hidden;white-space:pre}' .
		'#qec-demo .CodeMirror-gutters{padding:8px 8px 8px 12px;text-align:right;border-right:1px solid}#qec-demo .CodeMirror-lines{padding:8px 12px;flex:1;min-width:0;overflow:hidden}' .
		'#qec-demo .CodeMirror-activeline-background{display:block;margin:0 -12px;padding:0 12px}.qec-screen{padding:0!important;background:transparent!important;border:0!important}'
	);
	// Each scheme's CSS with stand-in numbers (9999px, 8888) that the script swaps for the chosen size and spacing.
	$css = array();
	foreach ( array_keys( qot_editor_colors_schemes() ) as $key ) {
		$css[ $key ] = qot_editor_colors_css( $key, '#qec-demo', 9999, 8888 );
	}
	wp_register_script( 'qot-editor-colors-admin', false, array(), QOT_EDITOR_COLORS_VERSION, true );
	wp_enqueue_script( 'qot-editor-colors-admin' );
	wp_add_inline_script( 'qot-editor-colors-admin', 'window.qecCss=' . wp_json_encode( $css ) . ';' . qot_editor_colors_admin_script() );
}

/**
 * Preview script: swaps the sample editor's colours, size and spacing.
 *
 * @return string
 */
function qot_editor_colors_admin_script() {
	return <<<'JS'
( function () {
	var form = document.getElementById( 'qec-form' );
	var style = document.createElement( 'style' );
	document.head.appendChild( style );
	if ( ! form ) {
		return;
	}
	function val( name ) {
		var el = form.querySelector( '[name="qot_editor_colors[' + name + ']"]:checked' ) || form.querySelector( 'select[name="qot_editor_colors[' + name + ']"], input[type=number][name="qot_editor_colors[' + name + ']"]' );
		return el ? el.value : '';
	}
	function draw() {
		var css = window.qecCss[ val( 'scheme' ) ] || '';
		style.textContent = css.split( '9999px' ).join( ( parseInt( val( 'size' ), 10 ) || 14 ) + 'px' ).split( '8888' ).join( val( 'lh' ) || '1.6' );
	}
	form.addEventListener( 'input', draw );
	form.addEventListener( 'change', draw );
	draw();
}() );
JS;
}

/**
 * Sample CSS and PHP, marked up with the same class names WordPress's editor uses.
 */
function qot_editor_colors_sample() {
	$lines = array(
		'<span class="cm-comment">/* Make links stand out */</span>',
		'<span class="cm-tag">a</span><span class="cm-qualifier">.button</span> {',
		'  <span class="cm-property">color</span>: <span class="cm-atom">#fff</span>;',
		'  <span class="cm-property">padding</span>: <span class="cm-number">8px</span> <span class="cm-number">16px</span>;',
		'}',
		'',
		'<span class="cm-meta">&lt;?php</span>',
		'<span class="cm-keyword">function</span> <span class="cm-def">my_theme_setup</span>() {',
		'  <span class="cm-variable-2">$size</span> <span class="cm-operator">=</span> <span class="cm-number">640</span>; <span class="cm-comment">// pixels</span>',
		'  <span class="cm-builtin">add_theme_support</span>( <span class="cm-string">\'title-tag\'</span> );',
		'}',
		'<span class="cm-tag">&lt;p</span> <span class="cm-attribute">class</span>=<span class="cm-string">"note"</span><span class="cm-tag">&gt;</span>Hello<span class="cm-tag">&lt;/p&gt;</span>',
	);
	$nums = '';
	$code = '';
	foreach ( $lines as $i => $line ) {
		$nums .= '<div class="CodeMirror-linenumber">' . ( $i + 1 ) . '</div>';
		$code .= 7 === $i ? '<div class="CodeMirror-activeline-background">' . $line . '</div>' : '<div>' . ( '' === $line ? '&nbsp;' : $line ) . '</div>';
	}
	// Built from fixed strings above only.
	echo '<div id="qec-demo"><div class="CodeMirror"><div class="CodeMirror-gutters">' . $nums . '</div><div class="CodeMirror-lines">' . $code . '</div></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * The settings page.
 */
function qot_editor_colors_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = qot_editor_colors_settings();
	$n = QOT_EDITOR_COLORS_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT Editor Colors', 'qot-editor-colors' ); ?></h1>
		<p><?php esc_html_e( 'Choose how code looks when you edit theme and plugin files or Additional CSS. The preview updates as you go.', 'qot-editor-colors' ); ?></p>
		<?php if ( qot_editor_colors_profile_off() ) : ?>
			<div class="notice notice-warning inline"><p>
				<?php esc_html_e( 'Code colouring is turned off in your profile, so you will see plain text in the editors.', 'qot-editor-colors' ); ?>
				<a href="<?php echo esc_url( admin_url( 'profile.php#syntax_highlighting' ) ); ?>"><?php esc_html_e( 'Untick "Disable syntax highlighting when editing code" in your profile', 'qot-editor-colors' ); ?></a>
			</p></div>
		<?php endif; ?>
		<div class="qec-wrap">
			<form method="post" action="options.php" id="qec-form">
				<?php settings_fields( 'qot_editor_colors' ); ?>
				<div class="qec-card qec-c1">
					<h2><?php esc_html_e( 'Colours and text', 'qot-editor-colors' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Colour scheme', 'qot-editor-colors' ); ?></th><td>
							<?php foreach ( qot_editor_colors_schemes() as $key => $scheme ) : ?>
								<label style="display:block;margin:0 0 6px"><input type="radio" name="<?php echo esc_attr( $n . '[scheme]' ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $s['scheme'], $key ); ?> /> <?php echo esc_html( $scheme['label'] ); ?></label>
							<?php endforeach; ?>
							<p class="description"><?php esc_html_e( 'Dark schemes are kinder to the eyes in long sessions; High contrast helps with low vision.', 'qot-editor-colors' ); ?></p>
						</td></tr>
						<tr><th scope="row"><label for="qec-size"><?php esc_html_e( 'Text size', 'qot-editor-colors' ); ?></label></th><td>
							<input type="number" id="qec-size" min="11" max="22" class="small-text" name="<?php echo esc_attr( $n . '[size]' ); ?>" value="<?php echo esc_attr( $s['size'] ); ?>" /> px
						</td></tr>
						<tr><th scope="row"><label for="qec-lh"><?php esc_html_e( 'Line spacing', 'qot-editor-colors' ); ?></label></th><td>
							<select id="qec-lh" name="<?php echo esc_attr( $n . '[lh]' ); ?>">
								<?php
								$spacing = array(
									'1.3'  => __( 'Tight', 'qot-editor-colors' ),
									'1.45' => __( 'Normal', 'qot-editor-colors' ),
									'1.6'  => __( 'Comfortable', 'qot-editor-colors' ),
									'1.8'  => __( 'Roomy', 'qot-editor-colors' ),
									'2'    => __( 'Double', 'qot-editor-colors' ),
								);
								foreach ( $spacing as $value => $label ) {
									printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $value ), selected( $s['lh'], $value, false ), esc_html( $label ) );
								}
								?>
							</select>
						</td></tr>
					</table>
				</div>
				<div class="qec-card qec-c2">
					<h2><?php esc_html_e( 'Where the colours apply', 'qot-editor-colors' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Editors', 'qot-editor-colors' ); ?></th><td>
							<label style="display:block;margin:0 0 6px"><input type="checkbox" name="<?php echo esc_attr( $n . '[theme_ed]' ); ?>" value="1" <?php checked( $s['theme_ed'] ); ?> /> <?php esc_html_e( 'Theme File Editor', 'qot-editor-colors' ); ?></label>
							<label style="display:block;margin:0 0 6px"><input type="checkbox" name="<?php echo esc_attr( $n . '[plugin_ed]' ); ?>" value="1" <?php checked( $s['plugin_ed'] ); ?> /> <?php esc_html_e( 'Plugin File Editor', 'qot-editor-colors' ); ?></label>
							<label style="display:block"><input type="checkbox" name="<?php echo esc_attr( $n . '[custom_css]' ); ?>" value="1" <?php checked( $s['custom_css'] ); ?> /> <?php esc_html_e( 'Additional CSS in the Customizer', 'qot-editor-colors' ); ?></label>
						</td></tr>
						<tr><th scope="row"><?php esc_html_e( 'On/off button', 'qot-editor-colors' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( $n . '[toggle]' ); ?>" value="1" <?php checked( $s['toggle'] ); ?> /> <?php esc_html_e( 'Show a "Colours: on" button above the file editors', 'qot-editor-colors' ); ?></label>
							<p class="description"><?php esc_html_e( 'Switch back to WordPress\'s own colours for a moment, for example to compare. Each browser remembers its choice.', 'qot-editor-colors' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_editor_colors_help_box(); ?>
			</form>
			<div class="qec-side qec-card qec-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-editor-colors' ); ?> <span class="qec-live"><?php esc_html_e( 'Live', 'qot-editor-colors' ); ?></span></h2>
				<div class="qec-screen"><?php qot_editor_colors_sample(); ?></div>
				<p class="description"><?php esc_html_e( 'Sample CSS, PHP and HTML as it will look in the editor. The highlighted line is where your cursor is.', 'qot-editor-colors' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_editor_colors_plugin_links( admin_url( 'options-general.php?page=qot-editor-colors' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function qot_editor_colors_uninstall() {
	delete_option( QOT_EDITOR_COLORS_OPTION );
}
register_uninstall_hook( __FILE__, 'qot_editor_colors_uninstall' );
