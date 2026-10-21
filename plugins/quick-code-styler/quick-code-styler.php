<?php
/**
 * Plugin Name:       Quick Code Styler
 * Plugin URI:        https://github.com/quickonlinetips/quickonlinetips/tree/main/plugins/quick-code-styler
 * Description:       Code windows with syntax colours and a Copy button, plus clean inline code and keyboard keys. Dark, light or automatic. Tiny, no outside library.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       quick-code-styler
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QUICK_CODE_STYLER_OPTION', 'quick_code_styler' );

/* ------------------------------------------------------------------ *
 * Settings and their defaults
 * ------------------------------------------------------------------ */

/**
 * The two built-in palettes: colour name => colour.
 * They match the variables in quick-code-styler.css.
 *
 * @return array{dark: array<string, string>, light: array<string, string>}
 */
function quick_code_styler_palettes() {
	return array(
		'dark'  => array(
			'bg'      => '#1e2433',
			'text'    => '#e6edf3',
			'comment' => '#8b98ae',
			'string'  => '#a5d6ff',
			'tag'     => '#7ee787',
			'attr'    => '#79c0ff',
			'keyword' => '#ff7b72',
			'number'  => '#f2cc60',
		),
		'light' => array(
			'bg'      => '#f5f7fa',
			'text'    => '#1f2430',
			'comment' => '#5f6b7a',
			'string'  => '#0b4f8a',
			'tag'     => '#17703a',
			'attr'    => '#1d5fbf',
			'keyword' => '#c62a2f',
			'number'  => '#9a4a00',
		),
	);
}

/**
 * Labels for the colours on the settings page, in display order.
 *
 * @return array<string, string>
 */
function quick_code_styler_colour_labels() {
	return array(
		'bg'      => __( 'Background', 'quick-code-styler' ),
		'text'    => __( 'Code text', 'quick-code-styler' ),
		'comment' => __( 'Comments', 'quick-code-styler' ),
		'string'  => __( 'Quoted text', 'quick-code-styler' ),
		'tag'     => __( 'HTML tags', 'quick-code-styler' ),
		'attr'    => __( 'Attributes', 'quick-code-styler' ),
		'keyword' => __( 'Keywords', 'quick-code-styler' ),
		'number'  => __( 'Numbers', 'quick-code-styler' ),
	);
}

/**
 * The saved settings, with defaults for anything not saved yet:
 * dark look, lines wrap, colours and Copy button on.
 *
 * @return array{scheme: string, colours: array<string, string>, wrap: bool, color: bool, copy: bool}
 */
function quick_code_styler_settings() {
	$saved = get_option( QUICK_CODE_STYLER_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();

	$scheme   = isset( $saved['scheme'] ) && in_array( $saved['scheme'], array( 'dark', 'light', 'auto' ), true ) ? $saved['scheme'] : 'dark';
	$palettes = quick_code_styler_palettes();
	$colours  = 'light' === $scheme ? $palettes['light'] : $palettes['dark'];

	// Own colours apply to the Dark and Light looks. Auto always uses the two built-in palettes.
	if ( 'auto' !== $scheme && isset( $saved['colours'] ) && is_array( $saved['colours'] ) ) {
		foreach ( $colours as $key => $default ) {
			if ( ! empty( $saved['colours'][ $key ] ) ) {
				$colours[ $key ] = $saved['colours'][ $key ];
			}
		}
	}

	return array(
		'scheme'  => $scheme,
		'colours' => $colours,
		'wrap'    => isset( $saved['wrap'] ) ? (bool) $saved['wrap'] : true,
		'color'   => isset( $saved['color'] ) ? (bool) $saved['color'] : true,
		'copy'    => isset( $saved['copy'] ) ? (bool) $saved['copy'] : true,
	);
}

/**
 * Check everything sent from the settings page before it is saved.
 * Only real colour codes like #1e2433 are kept; anything else falls back
 * to the default colour of the chosen look.
 *
 * @param mixed $input Values from the settings form.
 * @return array
 */
function quick_code_styler_sanitize( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$scheme   = isset( $input['scheme'] ) && in_array( $input['scheme'], array( 'dark', 'light', 'auto' ), true ) ? $input['scheme'] : 'dark';
	$palettes = quick_code_styler_palettes();
	$defaults = 'light' === $scheme ? $palettes['light'] : $palettes['dark'];
	$colours  = array();

	foreach ( $defaults as $key => $default ) {
		$value           = isset( $input['colours'][ $key ] ) ? sanitize_hex_color( $input['colours'][ $key ] ) : '';
		$colours[ $key ] = ( $value && 'auto' !== $scheme ) ? strtolower( $value ) : $default;
	}

	return array(
		'scheme'  => $scheme,
		'colours' => $colours,
		'wrap'    => ! empty( $input['wrap'] ),
		'color'   => ! empty( $input['color'] ),
		'copy'    => ! empty( $input['copy'] ),
	);
}

/**
 * CSS for any colours changed from the defaults of the chosen look,
 * plus the header label when WordPress runs in another language.
 * Returns an empty string when there is nothing to add.
 *
 * @param array $settings Settings from quick_code_styler_settings().
 * @return string
 */
function quick_code_styler_extra_css( $settings ) {
	$css = '';

	if ( 'auto' !== $settings['scheme'] ) {
		$palettes = quick_code_styler_palettes();
		$defaults = $palettes[ $settings['scheme'] ];
		$vars     = '';

		foreach ( $defaults as $key => $default ) {
			if ( $settings['colours'][ $key ] !== $default ) {
				$vars .= '--qcs-' . $key . ':' . $settings['colours'][ $key ] . ';';
			}
		}

		if ( $vars ) {
			$css .= '.qcs-on{' . $vars . '}';
		}
	}

	/* translators: Label on the bar across the top of each code block. */
	$label = __( 'Code', 'quick-code-styler' );
	if ( 'Code' !== $label ) {
		$css .= '.qcs-on pre::before{content:' . wp_json_encode( $label ) . '}';
	}

	return $css;
}

/* ------------------------------------------------------------------ *
 * Front end: only on pages that show code
 * ------------------------------------------------------------------ */

/**
 * Does the current page show any code?
 *
 * Looks for <pre>, <code> or <kbd> in the posts on the page: the one post
 * on a single post or page, or every post listed on the home page and archives.
 * The answer is worked out once and remembered for the rest of the page load.
 *
 * Developers can force the styles on everywhere (useful with page builders)
 * with: add_filter( 'quick_code_styler_load', '__return_true' );
 *
 * @return bool
 */
function quick_code_styler_page_has_code() {
	static $has_code = null;

	if ( null !== $has_code ) {
		return $has_code;
	}

	$has_code = false;

	if ( ! is_admin() ) {
		global $wp_query;
		$posts = is_singular() ? array( get_queried_object() ) : (array) $wp_query->posts;

		foreach ( $posts as $post ) {
			if ( $post instanceof WP_Post && preg_match( '/<(pre|code|kbd)[\s>]/i', $post->post_content ) ) {
				$has_code = true;
				break;
			}
		}
	}

	$has_code = (bool) apply_filters( 'quick_code_styler_load', $has_code );

	return $has_code;
}

/**
 * Load the stylesheet, any extra CSS, and the script if a switch is on.
 */
function quick_code_styler_enqueue() {
	if ( ! quick_code_styler_page_has_code() ) {
		return;
	}

	$settings = quick_code_styler_settings();
	$dir      = plugin_dir_path( __FILE__ );

	// The version number is the file's last-changed time, so browsers fetch new versions.
	wp_enqueue_style(
		'quick-code-styler',
		plugins_url( 'quick-code-styler.css', __FILE__ ),
		array(),
		(string) filemtime( $dir . 'quick-code-styler.css' )
	);

	$extra_css = quick_code_styler_extra_css( $settings );
	if ( $extra_css ) {
		wp_add_inline_style( 'quick-code-styler', $extra_css );
	}

	// Both switches off: no JavaScript at all.
	if ( ! $settings['color'] && ! $settings['copy'] ) {
		return;
	}

	wp_enqueue_script(
		'quick-code-styler',
		plugins_url( 'quick-code-styler.js', __FILE__ ),
		array(),
		(string) filemtime( $dir . 'quick-code-styler.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_add_inline_script(
		'quick-code-styler',
		'window.quickCodeStyler = ' . wp_json_encode(
			array(
				'color'      => $settings['color'],
				'copy'       => $settings['copy'],
				'copyText'   => __( 'Copy', 'quick-code-styler' ),
				'copiedText' => __( 'Copied!', 'quick-code-styler' ),
				'copyLabel'  => __( 'Copy code', 'quick-code-styler' ),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'quick_code_styler_enqueue' );

/**
 * Add the classes to <body> that switch the styles on and pick the look.
 *
 * @param string[] $classes Classes WordPress is about to add to <body>.
 * @return string[]
 */
function quick_code_styler_body_class( $classes ) {
	if ( ! quick_code_styler_page_has_code() ) {
		return $classes;
	}

	$settings  = quick_code_styler_settings();
	$classes[] = 'qcs-on';

	if ( 'dark' !== $settings['scheme'] ) {
		$classes[] = 'qcs-' . $settings['scheme'];
	}
	if ( ! $settings['wrap'] ) {
		$classes[] = 'qcs-nowrap';
	}

	return $classes;
}
add_filter( 'body_class', 'quick_code_styler_body_class' );

/* ------------------------------------------------------------------ *
 * Settings page: Settings > Quick Code Styler
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds all settings.
 */
function quick_code_styler_register_setting() {
	register_setting(
		'quick_code_styler',
		QUICK_CODE_STYLER_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'quick_code_styler_sanitize',
		)
	);
}
add_action( 'admin_init', 'quick_code_styler_register_setting' );

/**
 * Add the page to the Settings menu.
 */
function quick_code_styler_add_page() {
	$hook = add_options_page(
		__( 'Quick Code Styler', 'quick-code-styler' ),
		__( 'Quick Code Styler', 'quick-code-styler' ),
		'manage_options',
		'quick-code-styler',
		'quick_code_styler_render_page'
	);

	add_action( 'admin_enqueue_scripts', function ( $current ) use ( $hook ) {
		if ( $current === $hook ) {
			quick_code_styler_admin_assets();
		}
	} );
}
add_action( 'admin_menu', 'quick_code_styler_add_page' );

/**
 * On the settings page only: the colour picker, and the front-end
 * stylesheet so the preview looks exactly like the site.
 */
function quick_code_styler_admin_assets() {
	$dir = plugin_dir_path( __FILE__ );

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style(
		'quick-code-styler',
		plugins_url( 'quick-code-styler.css', __FILE__ ),
		array(),
		(string) filemtime( $dir . 'quick-code-styler.css' )
	);
	wp_add_inline_style(
		'quick-code-styler',
		'#qcs-preview{max-width:560px}#qcs-preview pre{margin:0}#qcs-preview .qcs-copy{cursor:default}' .
		'#qcs-preview.no-color pre span{color:inherit;font-style:normal}#qcs-preview.no-copy .qcs-copy{display:none}' .
		'.qcs-wrap{display:flex;flex-wrap:wrap;gap:32px;align-items:flex-start}.qcs-wrap .form-table{width:auto}' .
		'.qcs-wrap form{max-width:640px}' .
		// Keep the preview in view while picking: beside the colours on wide screens, above them otherwise.
		'.qcs-side{position:sticky;top:46px}@media (max-width:1279px){.qcs-side{order:-1;position:static}}'
	);

	wp_enqueue_script( 'wp-color-picker' );

	// Live preview: colours, look, wrapping and the two switches.
	$script = <<<'JS'
jQuery( function ( $ ) {
	var preview = document.getElementById( 'qcs-preview' );
	var palettes = window.qcsPalettes;
	var setting = false; // True while the look is resetting the pickers.

	function setColour( input, colour ) {
		preview.style.setProperty( '--qcs-' + $( input ).data( 'key' ), colour || $( input ).attr( 'data-default-color' ) );
	}

	$( '.qcs-colour' ).wpColorPicker( {
		change: function ( event, ui ) {
			setColour( this, ui.color.toString() );
		},
		clear: function () {
			setColour( $( this ).closest( '.wp-picker-container' ).find( '.qcs-colour' ), '' );
		}
	} );

	$( '#qcs-scheme' ).on( 'change', function () {
		var scheme = this.value;

		preview.classList.remove( 'qcs-light', 'qcs-auto' );
		if ( scheme !== 'dark' ) {
			preview.classList.add( 'qcs-' + scheme );
		}

		// Own colours are for Dark and Light. Auto uses the built-in palettes.
		$( '#qcs-colours' ).toggle( scheme !== 'auto' );
		$( '#qcs-auto-note' ).toggle( scheme === 'auto' );

		if ( setting ) {
			var palette = palettes[ scheme === 'light' ? 'light' : 'dark' ];
			$( '.qcs-colour' ).each( function () {
				var colour = palette[ $( this ).data( 'key' ) ];
				$( this ).attr( 'data-default-color', colour );
				$( this ).closest( '.wp-picker-container' ).find( '.wp-picker-default' ).attr( 'data-default-color', colour );
				$( this ).wpColorPicker( 'defaultColor', colour );
				$( this ).wpColorPicker( 'color', colour );
			} );
			if ( scheme === 'auto' ) {
				preview.removeAttribute( 'style' );
			}
		}
	} ).trigger( 'change' );
	setting = true;

	$( '#qcs-wrap-on' ).on( 'change', function () {
		preview.classList.toggle( 'qcs-nowrap', ! this.checked );
	} ).trigger( 'change' );
	$( '#qcs-color-on' ).on( 'change', function () {
		preview.classList.toggle( 'no-color', ! this.checked );
	} ).trigger( 'change' );
	$( '#qcs-copy-on' ).on( 'change', function () {
		preview.classList.toggle( 'no-copy', ! this.checked );
	} ).trigger( 'change' );
} );
JS;

	wp_add_inline_script( 'wp-color-picker', 'window.qcsPalettes = ' . wp_json_encode( quick_code_styler_palettes() ) . ';', 'before' );
	wp_add_inline_script( 'wp-color-picker', $script );
}

/**
 * The settings page itself.
 */
function quick_code_styler_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = quick_code_styler_settings();
	$palettes = quick_code_styler_palettes();
	$defaults = 'light' === $settings['scheme'] ? $palettes['light'] : $palettes['dark'];
	$name     = QUICK_CODE_STYLER_OPTION;

	// Start the preview with the saved colours (Auto uses the stylesheet's own palettes).
	$style = '';
	if ( 'auto' !== $settings['scheme'] ) {
		foreach ( $settings['colours'] as $key => $colour ) {
			$style .= '--qcs-' . $key . ':' . $colour . ';';
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Quick Code Styler', 'quick-code-styler' ); ?></h1>
		<p><?php esc_html_e( 'Choose how code looks on your site. The preview updates as you go.', 'quick-code-styler' ); ?></p>

		<div class="qcs-wrap">
			<form method="post" action="options.php">
				<?php settings_fields( 'quick_code_styler' ); ?>

				<h2><?php esc_html_e( 'Look', 'quick-code-styler' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="qcs-scheme"><?php esc_html_e( 'Code window', 'quick-code-styler' ); ?></label></th>
						<td>
							<select id="qcs-scheme" name="<?php echo esc_attr( $name . '[scheme]' ); ?>">
								<option value="dark" <?php selected( $settings['scheme'], 'dark' ); ?>><?php esc_html_e( 'Dark', 'quick-code-styler' ); ?></option>
								<option value="light" <?php selected( $settings['scheme'], 'light' ); ?>><?php esc_html_e( 'Light', 'quick-code-styler' ); ?></option>
								<option value="auto" <?php selected( $settings['scheme'], 'auto' ); ?>><?php esc_html_e( 'Automatic: follow each visitor\'s light or dark mode', 'quick-code-styler' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Long lines', 'quick-code-styler' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="qcs-wrap-on" name="<?php echo esc_attr( $name . '[wrap]' ); ?>" value="1" <?php checked( $settings['wrap'] ); ?> />
								<?php esc_html_e( 'Wrap long lines (best on phones). Turn off to scroll sideways instead, like a code editor.', 'quick-code-styler' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<div id="qcs-colours">
					<h2><?php esc_html_e( 'Colours', 'quick-code-styler' ); ?></h2>
					<p><?php esc_html_e( 'Click Default under a colour to undo a change. Switching between Dark and Light starts from that look\'s own colours.', 'quick-code-styler' ); ?></p>
					<table class="form-table" role="presentation">
						<?php foreach ( quick_code_styler_colour_labels() as $key => $label ) : ?>
							<tr>
								<th scope="row"><label for="qcs-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
								<td>
									<input type="text" class="qcs-colour" id="qcs-<?php echo esc_attr( $key ); ?>"
										name="<?php echo esc_attr( $name . '[colours][' . $key . ']' ); ?>"
										value="<?php echo esc_attr( $settings['colours'][ $key ] ); ?>"
										data-key="<?php echo esc_attr( $key ); ?>"
										data-default-color="<?php echo esc_attr( $defaults[ $key ] ); ?>" />
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
				<p id="qcs-auto-note" class="description"><?php esc_html_e( 'Automatic uses the built-in light and dark colours. Choose Dark or Light to pick your own.', 'quick-code-styler' ); ?></p>

				<h2><?php esc_html_e( 'Extras', 'quick-code-styler' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Syntax colours', 'quick-code-styler' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="qcs-color-on" name="<?php echo esc_attr( $name . '[color]' ); ?>" value="1" <?php checked( $settings['color'] ); ?> />
								<?php esc_html_e( 'Colour comments, quoted text, tags, keywords and numbers', 'quick-code-styler' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Copy button', 'quick-code-styler' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="qcs-copy-on" name="<?php echo esc_attr( $name . '[copy]' ); ?>" value="1" <?php checked( $settings['copy'] ); ?> />
								<?php esc_html_e( 'Show a Copy button on each code block', 'quick-code-styler' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<p class="description"><?php esc_html_e( 'With both turned off, the plugin loads no JavaScript at all.', 'quick-code-styler' ); ?></p>

				<?php submit_button(); ?>
			</form>

			<div class="qcs-side">
				<h2><?php esc_html_e( 'Preview', 'quick-code-styler' ); ?></h2>
				<div id="qcs-preview" class="qcs-on" style="<?php echo esc_attr( $style ); ?>">
<pre><code><span class="qcs-c">&lt;!-- A link --&gt;</span>
<span class="qcs-t">&lt;a</span> <span class="qcs-a">href</span>=<span class="qcs-s">"https://example.com/a/rather/long/address/to/show/how/long/lines/look"</span><span class="qcs-t">&gt;</span>Visit<span class="qcs-t">&lt;/a</span><span class="qcs-t">&gt;</span>

<span class="qcs-c">// Say hello</span>
<span class="qcs-k">function</span> greet( name ) {
	<span class="qcs-k">return</span> <span class="qcs-s">"Hello "</span> + name + <span class="qcs-n">2026</span>;
}</code><span class="qcs-copy"><?php esc_html_e( 'Copy', 'quick-code-styler' ); ?></span></pre>
				</div>
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
function quick_code_styler_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=quick-code-styler' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'quick-code-styler' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'quick_code_styler_action_links' );

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function quick_code_styler_uninstall() {
	delete_option( QUICK_CODE_STYLER_OPTION );
}
register_uninstall_hook( __FILE__, 'quick_code_styler_uninstall' );
