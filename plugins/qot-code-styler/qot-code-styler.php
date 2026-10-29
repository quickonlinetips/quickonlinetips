<?php
/**
 * Plugin Name:       QOT Code Styler
 * Plugin URI:        https://www.quickonlinetips.com/archives/quick-code-styler-wordpress-plugin/
 * Description:       Code windows with syntax colours and a Copy button, plus clean inline code and keyboard keys. Dark, light or automatic. Tiny, no outside library.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-code-styler
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_CODE_STYLER_OPTION', 'qot_code_styler' );

/* ------------------------------------------------------------------ *
 * Settings and their defaults
 * ------------------------------------------------------------------ */

/**
 * The two built-in palettes: colour name => colour.
 * They match the variables in qot-code-styler.css.
 *
 * @return array{dark: array<string, string>, light: array<string, string>}
 */
function qot_code_styler_palettes() {
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
function qot_code_styler_colour_labels() {
	return array(
		'bg'      => __( 'Background', 'qot-code-styler' ),
		'text'    => __( 'Code text', 'qot-code-styler' ),
		'comment' => __( 'Comments', 'qot-code-styler' ),
		'string'  => __( 'Quoted text', 'qot-code-styler' ),
		'tag'     => __( 'HTML tags', 'qot-code-styler' ),
		'attr'    => __( 'Attributes', 'qot-code-styler' ),
		'keyword' => __( 'Keywords', 'qot-code-styler' ),
		'number'  => __( 'Numbers', 'qot-code-styler' ),
	);
}

/**
 * The saved settings, with defaults for anything not saved yet:
 * dark look, lines wrap, colours and Copy button on.
 *
 * @return array{scheme: string, colours: array<string, string>, wrap: bool, color: bool, copy: bool}
 */
function qot_code_styler_settings() {
	$saved = get_option( QOT_CODE_STYLER_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();

	$scheme   = isset( $saved['scheme'] ) && in_array( $saved['scheme'], array( 'dark', 'light', 'auto' ), true ) ? $saved['scheme'] : 'dark';
	$palettes = qot_code_styler_palettes();
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
function qot_code_styler_sanitize( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$scheme   = isset( $input['scheme'] ) && in_array( $input['scheme'], array( 'dark', 'light', 'auto' ), true ) ? $input['scheme'] : 'dark';
	$palettes = qot_code_styler_palettes();
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
 * @param array $settings Settings from qot_code_styler_settings().
 * @return string
 */
function qot_code_styler_extra_css( $settings ) {
	$css = '';

	if ( 'auto' !== $settings['scheme'] ) {
		$palettes = qot_code_styler_palettes();
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
	$label = __( 'Code', 'qot-code-styler' );
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
 * with: add_filter( 'qot_code_styler_load', '__return_true' );
 *
 * @return bool
 */
function qot_code_styler_page_has_code() {
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

	$has_code = (bool) apply_filters( 'qot_code_styler_load', $has_code );

	return $has_code;
}

/**
 * Load the stylesheet, any extra CSS, and the script if a switch is on.
 */
function qot_code_styler_enqueue() {
	if ( ! qot_code_styler_page_has_code() ) {
		return;
	}

	$settings = qot_code_styler_settings();
	$dir      = plugin_dir_path( __FILE__ );

	// The version number is the file's last-changed time, so browsers fetch new versions.
	wp_enqueue_style(
		'qot-code-styler',
		plugins_url( 'qot-code-styler.css', __FILE__ ),
		array(),
		(string) filemtime( $dir . 'qot-code-styler.css' )
	);

	$extra_css = qot_code_styler_extra_css( $settings );
	if ( $extra_css ) {
		wp_add_inline_style( 'qot-code-styler', $extra_css );
	}

	// Both switches off: no JavaScript at all.
	if ( ! $settings['color'] && ! $settings['copy'] ) {
		return;
	}

	wp_enqueue_script(
		'qot-code-styler',
		plugins_url( 'qot-code-styler.js', __FILE__ ),
		array(),
		(string) filemtime( $dir . 'qot-code-styler.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_add_inline_script(
		'qot-code-styler',
		'window.qotCodeStyler = ' . wp_json_encode(
			array(
				'color'      => $settings['color'],
				'copy'       => $settings['copy'],
				'copyText'   => __( 'Copy', 'qot-code-styler' ),
				'copiedText' => __( 'Copied!', 'qot-code-styler' ),
				'copyLabel'  => __( 'Copy code', 'qot-code-styler' ),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'qot_code_styler_enqueue' );

/**
 * Add the classes to <body> that switch the styles on and pick the look.
 *
 * @param string[] $classes Classes WordPress is about to add to <body>.
 * @return string[]
 */
function qot_code_styler_body_class( $classes ) {
	if ( ! qot_code_styler_page_has_code() ) {
		return $classes;
	}

	$settings  = qot_code_styler_settings();
	$classes[] = 'qcs-on';

	if ( 'dark' !== $settings['scheme'] ) {
		$classes[] = 'qcs-' . $settings['scheme'];
	}
	if ( ! $settings['wrap'] ) {
		$classes[] = 'qcs-nowrap';
	}

	return $classes;
}
add_filter( 'body_class', 'qot_code_styler_body_class' );

/* ------------------------------------------------------------------ *
 * Post summaries: leave code out
 * ------------------------------------------------------------------ */

/*
 * Many themes show a short summary (excerpt) of each post on the home page
 * and archives. WordPress makes it by stripping all tags from the post, so
 * the code box disappears but the code itself spills into the summary as
 * plain text. While WordPress builds an automatic summary, take code blocks
 * out first. Summaries written by hand are never changed.
 */

/**
 * Before WordPress builds an automatic summary, switch on code removal.
 *
 * @param string $excerpt The hand-written summary, if any.
 * @return string
 */
function qot_code_styler_excerpt_start( $excerpt ) {
	if ( '' === trim( (string) $excerpt ) ) {
		add_filter( 'the_content', 'qot_code_styler_remove_code_blocks', 0 );
	}
	return $excerpt;
}
add_filter( 'get_the_excerpt', 'qot_code_styler_excerpt_start', 9 );

/**
 * After the summary is built, switch code removal off again.
 *
 * @param string $excerpt The finished summary.
 * @return string
 */
function qot_code_styler_excerpt_end( $excerpt ) {
	remove_filter( 'the_content', 'qot_code_styler_remove_code_blocks', 0 );
	return $excerpt;
}
add_filter( 'get_the_excerpt', 'qot_code_styler_excerpt_end', 11 );

/**
 * Take every <pre> code block out of the text.
 *
 * @param string $content Post content.
 * @return string
 */
function qot_code_styler_remove_code_blocks( $content ) {
	return preg_replace( '#<pre[\s>].*?</pre>#is', ' ', $content );
}

/* ------------------------------------------------------------------ *
 * Settings page: Settings > QOT Code Styler
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds all settings.
 */
function qot_code_styler_register_setting() {
	register_setting(
		'qot_code_styler',
		QOT_CODE_STYLER_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_code_styler_sanitize',
		)
	);
}
add_action( 'admin_init', 'qot_code_styler_register_setting' );

/**
 * Add the page to the Settings menu.
 */
function qot_code_styler_add_page() {
	$hook = add_options_page(
		__( 'QOT Code Styler', 'qot-code-styler' ),
		__( 'QOT Code Styler', 'qot-code-styler' ),
		'manage_options',
		'qot-code-styler',
		'qot_code_styler_render_page'
	);

	add_action( 'admin_enqueue_scripts', function ( $current ) use ( $hook ) {
		if ( $current === $hook ) {
			qot_code_styler_admin_assets();
		}
	} );
}
add_action( 'admin_menu', 'qot_code_styler_add_page' );

/**
 * On the settings page only: the colour picker, and the front-end
 * stylesheet so the preview looks exactly like the site.
 */
function qot_code_styler_admin_assets() {
	$dir = plugin_dir_path( __FILE__ );

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style(
		'qot-code-styler',
		plugins_url( 'qot-code-styler.css', __FILE__ ),
		array(),
		(string) filemtime( $dir . 'qot-code-styler.css' )
	);
	wp_add_inline_style(
		'qot-code-styler',
		'#qcs-preview pre{margin:0}#qcs-preview .qcs-copy{cursor:default}' .
		'#qcs-preview.no-color pre span{color:inherit;font-style:normal}#qcs-preview.no-copy .qcs-copy{display:none}' .
		// Wrap in reverse: side by side, the preview sits on the right; when the screen is too narrow
		// for both, it moves above the settings instead of below. (flex-end keeps both at the top.)
		'.qcs-wrap{display:flex;flex-wrap:wrap-reverse;gap:32px;align-items:flex-end}.qcs-wrap .form-table{width:auto}' .
		'.qcs-wrap form{max-width:640px;flex:1 1 480px}' .
		// Each section is a white card with its heading in a bar across the top.
		'.qcs-card{box-sizing:border-box;background:#fff;border:1px solid #c3c4c7;border-radius:4px;box-shadow:0 1px 1px rgba(0,0,0,.04);margin:0 0 20px;padding:0 20px 6px}' .
		'.qcs-card>h2{margin:0 -20px;padding:12px 20px;border-bottom:1px solid #dcdcde;background:#f0f6fc;border-radius:4px 4px 0 0;font-size:15px}' .
		// Heading colours change from card to card in a fixed order: blue, green, yellow, pink, purple,
		// then round again (qcs-c1 to qcs-c5, in page order). The preview card is always teal.
		'.qcs-c1>h2{background:#f0f6fc}.qcs-c2>h2{background:#edfaef}.qcs-c3>h2{background:#fcf9e8}' .
		'.qcs-c4>h2{background:#fcf0f1}.qcs-c5>h2{background:#f5f0fc}.qcs-preview>h2{background:#e8f6f5}' .
		// The preview is a live window, not a settings card: a teal frame with a faint dotted
		// background, an eye icon and a Live badge, with the preview itself sitting on top like a screen.
		'.qcs-preview{background:#1d6b65 radial-gradient(rgba(255,255,255,.12) 1px,transparent 1.5px) 0 0/16px 16px;border-color:#11433f;box-shadow:0 2px 8px rgba(0,0,0,.18)}' .
		'.qcs-preview>h2{display:flex;align-items:center;gap:8px;background:#155550;color:#f0fdfa;border-bottom-color:#2a8a82}' .
		'.qcs-preview>h2:before{content:"\f177";font:normal 20px/1 dashicons;color:#99f6e4}' .
		'.qcs-live{margin-left:auto;position:relative;padding:2px 8px 2px 18px;border:1px solid #99f6e4;border-radius:10px;background:rgba(153,246,228,.14);color:#ccfbf1;font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase}' .
		'.qcs-live:before{content:"";position:absolute;left:7px;top:50%;width:6px;height:6px;margin-top:-3px;border-radius:50%;background:#99f6e4}' .
		'.qcs-preview>.description,.qcs-preview>p{color:#e6f7f5}.qcs-preview>.description a,.qcs-preview>p a{color:#cfe9ff}.qcs-card.qcs-preview>.description{margin:12px 0 0}' .
		'.qcs-card .form-table{margin-top:4px}.qcs-card .form-table th{padding-left:0;width:150px}' .
		'.qcs-card .qcs-intro{margin:14px 0 0;color:#50575e}.qcs-card .description{margin:0 0 12px}' .
		'#qcs-auto-note{margin:14px 0 12px}.qcs-side.qcs-card{padding-bottom:20px;margin-bottom:0;flex:1 1 400px;max-width:640px}.qcs-side #qcs-preview{margin-top:20px}' .
		// Keep the preview in view while picking: beside the colours on wide screens, above them otherwise.
		'.qcs-side{position:sticky;top:46px}@media (max-width:1139px){.qcs-wrap{gap:20px}.qcs-side{position:static}}'
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

	wp_add_inline_script( 'wp-color-picker', 'window.qcsPalettes = ' . wp_json_encode( qot_code_styler_palettes() ) . ';', 'before' );
	wp_add_inline_script( 'wp-color-picker', $script );
}

/**
 * The settings page itself.
 */
function qot_code_styler_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = qot_code_styler_settings();
	$palettes = qot_code_styler_palettes();
	$defaults = 'light' === $settings['scheme'] ? $palettes['light'] : $palettes['dark'];
	$name     = QOT_CODE_STYLER_OPTION;

	// Start the preview with the saved colours (Auto uses the stylesheet's own palettes).
	$style = '';
	if ( 'auto' !== $settings['scheme'] ) {
		foreach ( $settings['colours'] as $key => $colour ) {
			$style .= '--qcs-' . $key . ':' . $colour . ';';
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT Code Styler', 'qot-code-styler' ); ?></h1>
		<p><?php esc_html_e( 'Choose how code looks on your site. The preview updates as you go.', 'qot-code-styler' ); ?></p>

		<div class="qcs-wrap">
			<form method="post" action="options.php">
				<?php settings_fields( 'qot_code_styler' ); ?>

				<div class="qcs-card qcs-c1">
				<h2><?php esc_html_e( 'Look', 'qot-code-styler' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="qcs-scheme"><?php esc_html_e( 'Code window', 'qot-code-styler' ); ?></label></th>
						<td>
							<select id="qcs-scheme" name="<?php echo esc_attr( $name . '[scheme]' ); ?>">
								<option value="dark" <?php selected( $settings['scheme'], 'dark' ); ?>><?php esc_html_e( 'Dark', 'qot-code-styler' ); ?></option>
								<option value="light" <?php selected( $settings['scheme'], 'light' ); ?>><?php esc_html_e( 'Light', 'qot-code-styler' ); ?></option>
								<option value="auto" <?php selected( $settings['scheme'], 'auto' ); ?>><?php esc_html_e( 'Automatic: follow each visitor\'s light or dark mode', 'qot-code-styler' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Long lines', 'qot-code-styler' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="qcs-wrap-on" name="<?php echo esc_attr( $name . '[wrap]' ); ?>" value="1" <?php checked( $settings['wrap'] ); ?> />
								<?php esc_html_e( 'Wrap long lines (best on phones). Turn off to scroll sideways instead, like a code editor.', 'qot-code-styler' ); ?>
							</label>
						</td>
					</tr>
				</table>
				</div>

				<div class="qcs-card qcs-c2">
				<h2><?php esc_html_e( 'Colours', 'qot-code-styler' ); ?></h2>
				<div id="qcs-colours">
					<p class="qcs-intro"><?php esc_html_e( 'Click Default under a colour to undo a change. Switching between Dark and Light starts from that look\'s own colours.', 'qot-code-styler' ); ?></p>
					<table class="form-table" role="presentation">
						<?php foreach ( qot_code_styler_colour_labels() as $key => $label ) : ?>
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
				<p id="qcs-auto-note" class="description"><?php esc_html_e( 'Automatic uses the built-in light and dark colours. Choose Dark or Light to pick your own.', 'qot-code-styler' ); ?></p>
				</div>

				<div class="qcs-card qcs-c3">
				<h2><?php esc_html_e( 'Extras', 'qot-code-styler' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Syntax colours', 'qot-code-styler' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="qcs-color-on" name="<?php echo esc_attr( $name . '[color]' ); ?>" value="1" <?php checked( $settings['color'] ); ?> />
								<?php esc_html_e( 'Colour comments, quoted text, tags, keywords and numbers', 'qot-code-styler' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Copy button', 'qot-code-styler' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="qcs-copy-on" name="<?php echo esc_attr( $name . '[copy]' ); ?>" value="1" <?php checked( $settings['copy'] ); ?> />
								<?php esc_html_e( 'Show a Copy button on each code block', 'qot-code-styler' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<p class="description"><?php esc_html_e( 'With both turned off, the plugin loads no JavaScript at all.', 'qot-code-styler' ); ?></p>
				</div>

				<?php submit_button(); ?>
			</form>

			<div class="qcs-side qcs-card qcs-preview">
				<h2><?php esc_html_e( 'Preview', 'qot-code-styler' ); ?> <span class="qcs-live"><?php esc_html_e( 'Live', 'qot-code-styler' ); ?></span></h2>
				<div id="qcs-preview" class="qcs-on" style="<?php echo esc_attr( $style ); ?>">
<pre><code><span class="qcs-c">&lt;!-- A link --&gt;</span>
<span class="qcs-t">&lt;a</span> <span class="qcs-a">href</span>=<span class="qcs-s">"https://example.com/a/rather/long/address/to/show/how/long/lines/look"</span><span class="qcs-t">&gt;</span>Visit<span class="qcs-t">&lt;/a</span><span class="qcs-t">&gt;</span>

<span class="qcs-c">// Say hello</span>
<span class="qcs-k">function</span> greet( name ) {
	<span class="qcs-k">return</span> <span class="qcs-s">"Hello "</span> + name + <span class="qcs-n">2026</span>;
}</code><span class="qcs-copy"><?php esc_html_e( 'Copy', 'qot-code-styler' ); ?></span></pre>
				</div>
				<p class="description"><?php esc_html_e( 'This is how every code block looks in your posts, with your chosen colours and Copy button.', 'qot-code-styler' ); ?></p>
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
function qot_code_styler_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=qot-code-styler' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'qot-code-styler' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'qot_code_styler_action_links' );

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function qot_code_styler_uninstall() {
	delete_option( QOT_CODE_STYLER_OPTION );
}
register_uninstall_hook( __FILE__, 'qot_code_styler_uninstall' );
