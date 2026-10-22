<?php
/**
 * Plugin Name:       Quick WordPress Version
 * Plugin URI:        https://www.quickonlinetips.com/archives/display-wordpress-version-plugin/
 * Description:       A simple "Powered by WordPress" line for your site, with or without the version number. Shows your installed and latest WordPress version in Settings.
 * Version:           2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       display-wordpress-version
 */

/*
 * This plugin was called Display WordPress Version up to version 1.4.
 * The folder (display-wordpress-version), this file name (wpversion.php)
 * and the quickversion() template tag all stay the same, so the
 * WordPress.org page keeps its address and sites using the plugin
 * keep working after the update.
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QUICK_WP_VERSION_OPTION', 'quick_wp_version' );

/* ------------------------------------------------------------------ *
 * Settings and their defaults
 * ------------------------------------------------------------------ */

/**
 * The choices for what the line shows.
 *
 * plain  Powered by WordPress
 * major  Powered by WordPress 7
 * full   Powered by WordPress 7.1.3
 * custom The site owner's own text
 *
 * @return string[]
 */
function quick_wp_version_show_choices() {
	return array( 'plain', 'major', 'full', 'custom' );
}

/**
 * The marks that can join the line to the text before and after it.
 * An empty mark means just a space.
 *
 * @return array<string, string>
 */
function quick_wp_version_separators() {
	return array(
		'bullet' => '•',
		'bar'    => '|',
		'dash'   => '–',
		'dot'    => '·',
		'space'  => '',
	);
}

/**
 * Tidy the edge of the text before or after the line, where the joining
 * mark goes: spaces, full stops, commas and separators typed there are
 * removed, so the line never shows "WordPress . Hello" or two bullets.
 *
 * @param string $text Text before or after the line.
 * @param string $edge 'end' for the text before the line, 'start' for the text after it.
 * @return string
 */
function quick_wp_version_trim_join( $text, $edge ) {
	$marks = '(?:[\s.,;:|•·–—\-]|&nbsp;|&bull;|&middot;|&ndash;|&mdash;|&#8226;)+';
	$text  = (string) preg_replace( 'end' === $edge ? '/' . $marks . '$/u' : '/^' . $marks . '/u', '', trim( (string) $text ) );
	return trim( $text );
}

/**
 * The saved settings, with defaults for anything not saved yet.
 *
 * The default is Powered by WordPress with no number: showing the exact
 * version tells everyone, including attackers, what your site runs.
 *
 * The line is added at the bottom of every page automatically,
 * centred, until the site owner turns that off.
 *
 * While an update is waiting, the version number is hidden, so the line
 * never advertises an out-of-date WordPress.
 *
 * @return array{show: string, custom: string, link: bool, before: string, after: string, sep: string, hide_old: bool, footer: bool, align: string}
 */
function quick_wp_version_settings() {
	$saved = get_option( QUICK_WP_VERSION_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();

	return array(
		'show'   => isset( $saved['show'] ) && in_array( $saved['show'], quick_wp_version_show_choices(), true ) ? $saved['show'] : 'plain',
		'custom' => isset( $saved['custom'] ) ? (string) $saved['custom'] : '',
		'link'   => isset( $saved['link'] ) ? (bool) $saved['link'] : true,
		'before' => isset( $saved['before'] ) ? (string) $saved['before'] : '',
		'after'  => isset( $saved['after'] ) ? (string) $saved['after'] : '',
		'sep'    => isset( $saved['sep'] ) && array_key_exists( $saved['sep'], quick_wp_version_separators() ) ? $saved['sep'] : 'bullet',
		'hide_old' => isset( $saved['hide_old'] ) ? (bool) $saved['hide_old'] : true,
		'footer' => isset( $saved['footer'] ) ? (bool) $saved['footer'] : true,
		'align'  => isset( $saved['align'] ) && in_array( $saved['align'], array( 'left', 'center', 'right' ), true ) ? $saved['align'] : 'center',
	);
}

/**
 * Check everything sent from the settings page before it is saved.
 * The two text boxes keep simple HTML such as links and bold text,
 * the same HTML allowed in a post. Scripts are removed.
 *
 * @param mixed $input Values from the settings form.
 * @return array
 */
function quick_wp_version_sanitize( $input ) {
	$input = is_array( $input ) ? $input : array();

	return array(
		'show'   => isset( $input['show'] ) && in_array( $input['show'], quick_wp_version_show_choices(), true ) ? $input['show'] : 'plain',
		'custom' => isset( $input['custom'] ) ? trim( wp_kses_post( wp_unslash( $input['custom'] ) ) ) : '',
		'link'   => ! empty( $input['link'] ),
		'before' => isset( $input['before'] ) ? quick_wp_version_trim_join( wp_kses_post( wp_unslash( $input['before'] ) ), 'end' ) : '',
		'after'  => isset( $input['after'] ) ? quick_wp_version_trim_join( wp_kses_post( wp_unslash( $input['after'] ) ), 'start' ) : '',
		'sep'    => isset( $input['sep'] ) && array_key_exists( $input['sep'], quick_wp_version_separators() ) ? $input['sep'] : 'bullet',
		'hide_old' => ! empty( $input['hide_old'] ),
		'footer' => ! empty( $input['footer'] ),
		'align'  => isset( $input['align'] ) && in_array( $input['align'], array( 'left', 'center', 'right' ), true ) ? $input['align'] : 'center',
	);
}

/* ------------------------------------------------------------------ *
 * The line itself
 * ------------------------------------------------------------------ */

/**
 * Is a newer WordPress waiting to be installed?
 *
 * Reads the answer WordPress saved at its last update check (it checks
 * twice a day by itself). Never contacts WordPress.org, so it costs
 * nothing on the front end.
 *
 * @return string The newer version, like 7.1.3, or empty when up to date or not known.
 */
function quick_wp_version_update_waiting() {
	$check     = get_site_transient( 'update_core' );
	$installed = (string) get_bloginfo( 'version' );
	$newest    = '';

	if ( is_object( $check ) && ! empty( $check->updates ) && is_array( $check->updates ) ) {
		foreach ( $check->updates as $update ) {
			if (
				isset( $update->response, $update->current )
				&& 'upgrade' === $update->response
				&& version_compare( $update->current, $installed, '>' )
				&& ( '' === $newest || version_compare( $update->current, $newest, '>' ) )
			) {
				$newest = $update->current;
			}
		}
	}

	return $newest;
}

/**
 * Will the version number be hidden right now, because an update is waiting?
 * Only matters for lines that show a number.
 *
 * @param array $settings Settings from quick_wp_version_settings().
 * @return bool
 */
function quick_wp_version_number_hidden( $settings ) {
	if ( ! $settings['hide_old'] || '' === quick_wp_version_update_waiting() ) {
		return false;
	}

	if ( 'custom' === $settings['show'] ) {
		return false !== strpos( $settings['custom'], '{version}' ) || false !== strpos( $settings['custom'], '{major}' );
	}

	return in_array( $settings['show'], array( 'major', 'full' ), true );
}

/**
 * Build the line from the settings, ready to print.
 *
 * Every place the line can appear uses this, so it always looks the same.
 * Developers can change it with the quick_wp_version_line filter.
 *
 * @param array|null $settings Settings to use; the saved ones if left out.
 * @return string HTML.
 */
function quick_wp_version_line( $settings = null ) {
	$settings = is_array( $settings ) ? $settings : quick_wp_version_settings();
	$version  = (string) get_bloginfo( 'version' );
	$major    = strtok( $version, '.' );

	$wordpress = $settings['link']
		? '<a class="display-version" href="https://wordpress.org/">WordPress</a>'
		: 'WordPress';

	// An update is waiting and the owner chose to hide the number meanwhile:
	// show the plain line until WordPress is updated.
	$show = quick_wp_version_number_hidden( $settings ) ? 'plain' : $settings['show'];

	switch ( $show ) {
		case 'major':
			/* translators: 1: the word WordPress, 2: main version number, like 7 */
			$line = sprintf( __( 'Powered by %1$s %2$s', 'display-wordpress-version' ), $wordpress, esc_html( $major ) );
			break;

		case 'full':
			/* translators: 1: the word WordPress, 2: full version number, like 7.1.3 */
			$line = sprintf( __( 'Powered by %1$s %2$s', 'display-wordpress-version' ), $wordpress, esc_html( $version ) );
			break;

		case 'custom':
			// Own text, with {version} and {major} swapped for the numbers.
			// Left empty, it falls back to the plain line.
			if ( '' !== $settings['custom'] ) {
				$line = str_replace(
					array( '{version}', '{major}' ),
					array( esc_html( $version ), esc_html( $major ) ),
					wp_kses_post( $settings['custom'] )
				);
				break;
			}
			// Fall through to plain.

		default:
			/* translators: %s: the word WordPress */
			$line = sprintf( __( 'Powered by %s', 'display-wordpress-version' ), $wordpress );
	}

	// Text before and after the line, joined with the chosen mark (a bullet unless changed).
	$marks  = quick_wp_version_separators();
	$mark   = isset( $marks[ $settings['sep'] ] ) ? $marks[ $settings['sep'] ] : '•';
	$glue   = '' === $mark ? ' ' : ' ' . $mark . ' ';
	$before = quick_wp_version_trim_join( wp_kses_post( $settings['before'] ), 'end' );
	$after  = quick_wp_version_trim_join( wp_kses_post( $settings['after'] ), 'start' );

	if ( '' !== $before ) {
		$line = $before . $glue . $line;
	}
	if ( '' !== $after ) {
		$line .= $glue . $after;
	}

	return (string) apply_filters( 'quick_wp_version_line', $line, $settings );
}

/**
 * Remember whether the theme has already printed the line with
 * quickversion() on this page, so the automatic line is not added twice.
 *
 * @param bool $printed Pass true to note that the line was printed.
 * @return bool
 */
function quick_wp_version_printed( $printed = false ) {
	static $done = false;

	if ( $printed ) {
		$done = true;
	}

	return $done;
}

/**
 * Old template tag, kept so themes that already call it keep working.
 * Prints the line, using the choices in Settings > Quick WordPress Version.
 * When a theme uses it, the automatic line at the bottom is skipped.
 */
function quickversion() {
	quick_wp_version_printed( true );
	echo quick_wp_version_line(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
}

/* ------------------------------------------------------------------ *
 * Automatic line at the very bottom of every page
 * ------------------------------------------------------------------ */

/**
 * Add the line as a new line at the very bottom of the page, below the
 * theme's own footer. Works with any theme, classic or block, with no
 * theme files to edit. Small, plain text that takes the theme's colours.
 */
function quick_wp_version_footer() {
	$settings = quick_wp_version_settings();

	if ( ! $settings['footer'] || quick_wp_version_printed() ) {
		return;
	}

	printf(
		'<p class="quick-wp-version" style="margin:0;padding:12px 16px;font-size:13px;text-align:%1$s">%2$s</p>' . "\n",
		esc_attr( $settings['align'] ),
		quick_wp_version_line( $settings ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
	);
}
add_action( 'wp_footer', 'quick_wp_version_footer', 100 );

/* ------------------------------------------------------------------ *
 * Anywhere else: shortcode and widget
 * ------------------------------------------------------------------ */

/**
 * [quick_wp_version] shortcode, for posts, pages, the Shortcode block
 * and block theme footers.
 *
 * @return string
 */
function quick_wp_version_shortcode() {
	return '<span class="quick-wp-version">' . quick_wp_version_line() . '</span>';
}
add_shortcode( 'quick_wp_version', 'quick_wp_version_shortcode' );

/**
 * Widget for themes with widget areas, such as a footer or sidebar.
 */
class Quick_WP_Version_Widget extends WP_Widget {

	/**
	 * Name and description in Appearance > Widgets.
	 */
	public function __construct() {
		parent::__construct(
			'quick_wp_version',
			__( 'Quick WordPress Version', 'display-wordpress-version' ),
			array(
				'description'                 => __( 'Your Powered by WordPress line.', 'display-wordpress-version' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Show the widget on the site.
	 *
	 * @param array $args     Theme's widget wrapper HTML.
	 * @param array $instance This widget's settings.
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) : '';

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- From the theme.
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- From the theme.
		}
		echo '<p class="quick-wp-version">' . quick_wp_version_line() . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- From the theme.
	}

	/**
	 * The widget's one setting: an optional title.
	 *
	 * @param array $instance This widget's settings.
	 * @return string
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional):', 'display-wordpress-version' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p class="description">
			<?php
			printf(
				/* translators: %s: link to the plugin's settings page */
				esc_html__( 'Choose what the line says in %s.', 'display-wordpress-version' ),
				'<a href="' . esc_url( admin_url( 'options-general.php?page=quick-wp-version' ) ) . '">' . esc_html__( 'Settings > Quick WordPress Version', 'display-wordpress-version' ) . '</a>'
			);
			?>
		</p>
		<?php
		return '';
	}

	/**
	 * Save the title.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Previous settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '',
		);
	}
}

/**
 * Make the widget available in Appearance > Widgets.
 */
function quick_wp_version_register_widget() {
	register_widget( 'Quick_WP_Version_Widget' );
}
add_action( 'widgets_init', 'quick_wp_version_register_widget' );

/* ------------------------------------------------------------------ *
 * Installed and latest WordPress version
 * ------------------------------------------------------------------ */

/**
 * The newest WordPress version, as last reported by WordPress.org.
 *
 * WordPress already checks for updates twice a day and remembers the
 * answer, so this normally costs nothing. Only if there is no answer
 * yet does it ask WordPress.org, and only on the settings page.
 *
 * @return string Version like 7.1.3, or empty if WordPress.org could not be reached.
 */
function quick_wp_version_latest() {
	require_once ABSPATH . 'wp-admin/includes/update.php';

	$updates = get_core_updates( array( 'dismissed' => true ) );

	if ( ! is_array( $updates ) && function_exists( 'wp_version_check' ) ) {
		wp_version_check();
		$updates = get_core_updates( array( 'dismissed' => true ) );
	}

	$latest = '';

	foreach ( (array) $updates as $update ) {
		if (
			isset( $update->response, $update->current )
			&& in_array( $update->response, array( 'upgrade', 'latest' ), true )
			&& ( '' === $latest || version_compare( $update->current, $latest, '>' ) )
		) {
			$latest = $update->current;
		}
	}

	return $latest;
}

/* ------------------------------------------------------------------ *
 * Settings page: Settings > Quick WordPress Version
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds all settings.
 */
function quick_wp_version_register_setting() {
	register_setting(
		'quick_wp_version',
		QUICK_WP_VERSION_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'quick_wp_version_sanitize',
		)
	);
}
add_action( 'admin_init', 'quick_wp_version_register_setting' );

/**
 * Add the page to the Settings menu.
 */
function quick_wp_version_add_page() {
	$hook = add_options_page(
		__( 'Quick WordPress Version', 'display-wordpress-version' ),
		__( 'Quick WordPress Version', 'display-wordpress-version' ),
		'manage_options',
		'quick-wp-version',
		'quick_wp_version_render_page'
	);

	add_action( 'admin_enqueue_scripts', function ( $current ) use ( $hook ) {
		if ( $current === $hook ) {
			quick_wp_version_admin_assets();
		}
	} );
}
add_action( 'admin_menu', 'quick_wp_version_add_page' );

/**
 * A Settings link next to Deactivate on the Plugins page.
 *
 * @param string[] $links Links WordPress shows for this plugin.
 * @return string[]
 */
function quick_wp_version_action_links( $links ) {
	array_unshift(
		$links,
		'<a href="' . esc_url( admin_url( 'options-general.php?page=quick-wp-version' ) ) . '">' . esc_html__( 'Settings', 'display-wordpress-version' ) . '</a>'
	);
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'quick_wp_version_action_links' );

/**
 * On the settings page only: a few lines of CSS for the cards,
 * and a small script so the preview follows the choices as you click.
 */
function quick_wp_version_admin_assets() {
	wp_register_style( 'quick-wp-version-admin', false, array(), '2.0' );
	wp_enqueue_style( 'quick-wp-version-admin' );
	wp_add_inline_style(
		'quick-wp-version-admin',
		'.qwv-wrap{display:flex;flex-wrap:wrap;gap:32px;align-items:flex-start}' .
		'.qwv-main{max-width:640px;flex:1 1 480px}' .
		'.qwv-card{box-sizing:border-box;background:#fff;border:1px solid #c3c4c7;border-radius:4px;box-shadow:0 1px 1px rgba(0,0,0,.04);margin:0 0 20px;padding:0 20px 8px}' .
		'.qwv-card>h2{margin:0 -20px;padding:12px 20px;border-bottom:1px solid #dcdcde;background:#f0f6fc;border-radius:4px 4px 0 0;font-size:15px}' .
		// Heading colours change from card to card in a fixed order: blue, green, yellow, pink, purple,
		// then round again (qwv-c1 to qwv-c5, in page order). The preview card is always teal.
		'.qwv-c1>h2{background:#f0f6fc}.qwv-c2>h2{background:#edfaef}.qwv-c3>h2{background:#fcf9e8}' .
		'.qwv-c4>h2{background:#fcf0f1}.qwv-c5>h2{background:#f5f0fc}.qwv-preview>h2{background:#e8f6f5}' .
		'.qwv-card .form-table{margin-top:4px}.qwv-card .form-table th{padding-left:0;width:170px}' .
		'.qwv-versions{display:flex;flex-wrap:wrap;gap:12px;margin:16px 0 12px}' .
		'.qwv-versions div{flex:1 1 150px;border:1px solid #dcdcde;border-radius:4px;padding:10px 14px}' .
		'.qwv-versions span{display:block;color:#50575e;font-size:12px}.qwv-versions strong{font-size:22px;line-height:1.5}' .
		'.qwv-status{display:inline-block;border-radius:12px;padding:2px 10px;font-weight:600;font-size:12px;margin-right:6px}' .
		'.qwv-ok{background:#edfaef;color:#00650d}.qwv-old{background:#fcf0e1;color:#8a4a00}.qwv-unknown{background:#f0f0f1;color:#50575e}' .
		'.qwv-card .form-table td fieldset label{display:block;margin:0 0 10px;line-height:1.6}.qwv-card fieldset code{font-size:12px}' .
		'#qwv-custom-text{margin:4px 0 0 24px;width:calc(100% - 24px)}#qwv-custom-wrap .description{margin-left:24px}' .
		'.qwv-card .qwv-intro{margin:14px 0 0;color:#50575e}' .
		'.qwv-other{margin-top:12px}.qwv-dev{font-weight:400;font-size:12px;color:#646970}' .
		'.qwv-card .form-table td label code{font-size:12px}' .
		// Preview card: beside the form and sticky on wide screens, above it on narrow ones.
		'.qwv-side{flex:1 1 400px;max-width:640px;position:sticky;top:46px;padding-bottom:20px;margin-bottom:0}' .
		// The preview is a small website, with the line highlighted at the very bottom.
		'.qwv-site{margin-top:20px;border:1px solid #c3c4c7;border-radius:8px;overflow:hidden;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.06)}' .
		'.qwv-bar{display:flex;align-items:center;gap:6px;padding:8px 12px;background:#f0f0f1;border-bottom:1px solid #dcdcde}' .
		'.qwv-bar i{width:10px;height:10px;border-radius:50%;background:#c3c4c7}' .
		'.qwv-bar span{flex:1;margin-left:8px;padding:3px 10px;background:#fff;border-radius:12px;font-size:12px;color:#646970;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}' .
		'.qwv-page{padding:18px 20px 22px}.qwv-page strong{display:block;font-size:17px;color:#1d2327;margin-bottom:12px}' .
		'.qwv-page b{display:block;height:9px;border-radius:5px;background:#e6e8ea;margin:0 0 9px}' .
		'.qwv-foot{padding:12px 20px;background:#f6f7f7;border-top:1px solid #dcdcde;font-size:13px;color:#50575e}' .
		'.qwv-line{position:relative;margin:14px 12px 12px;padding:12px 14px;border:2px solid #2271b1;border-radius:6px;background:#f0f6fc;font-size:14px;color:#1d2327;text-align:center}' .
		'.qwv-line a{color:#2271b1}' .
		'.qwv-tag{position:absolute;top:-11px;left:12px;padding:1px 9px;border-radius:10px;background:#2271b1;color:#fff;font-size:11px;font-weight:600;line-height:18px}' .
		'#qwv-where{margin:12px 0 0}' .
		'@media (max-width:1279px){.qwv-wrap{gap:20px}.qwv-side{order:-1;position:static}}'
	);

	// Live preview. It only shows how the line will look; nothing is saved until Save Changes.
	$script = <<<'JS'
document.addEventListener( 'DOMContentLoaded', function () {
	var form = document.getElementById( 'qwv-form' );
	var preview = document.getElementById( 'qwv-preview' );
	if ( ! form || ! preview ) {
		return;
	}
	var data = window.qwvPreview;

	function value( name ) {
		var field = form.querySelector( '[name="quick_wp_version[' + name + ']"]:checked' ) || form.querySelector( '[name="quick_wp_version[' + name + ']"]' );
		return field ? ( field.type === 'checkbox' && ! field.checked ? '' : field.value ) : '';
	}

	function update() {
		var show = value( 'show' );
		var wp = value( 'link' ) ? '<a class="display-version" href="https://wordpress.org/">WordPress</a>' : 'WordPress';
		var custom = value( 'custom' ).trim();
		var line = data.plain.replace( '%s', wp );

		var hasNumber = show === 'major' || show === 'full' || ( show === 'custom' && /\{(version|major)\}/.test( custom ) );
		var hidden = data.waiting && value( 'hide_old' ) && hasNumber;
		if ( hidden ) {
			show = 'plain';
		}

		if ( show === 'major' || show === 'full' ) {
			line = data.number.replace( '%1$s', wp ).replace( '%2$s', show === 'major' ? data.major : data.version );
		} else if ( show === 'custom' && custom ) {
			line = custom.split( '{version}' ).join( data.version ).split( '{major}' ).join( data.major );
		}

		// Same tidying and joining as the PHP.
		var marks = '(?:[\\s.,;:|•·–—\\-]|&nbsp;|&bull;|&middot;|&ndash;|&mdash;|&#8226;)+';
		var before = value( 'before' ).trim().replace( new RegExp( marks + '$' ), '' ).trim();
		var after = value( 'after' ).trim().replace( new RegExp( '^' + marks ), '' ).trim();
		var mark = data.marks[ value( 'sep' ) ];
		var glue = mark ? ' ' + mark + ' ' : ' ';
		preview.innerHTML = ( before ? before + glue : '' ) + line + ( after ? glue + after : '' );
		preview.parentNode.style.textAlign = value( 'align' ) || 'center';
		var note = value( 'footer' ) ? data.whereOn : data.whereOff;
		if ( hidden ) {
			note += ' ' + data.hiddenNote;
		} else if ( data.waiting && hasNumber ) {
			note += ' ' + data.oldNote;
		}
		if ( value( 'link' ) && line.indexOf( wp ) !== -1 ) {
			note += ' ' + data.linkNote;
		}
		document.getElementById( 'qwv-where' ).textContent = note;
		document.getElementById( 'qwv-custom-wrap' ).style.opacity = show === 'custom' ? 1 : 0.6;
	}

	// Links in the preview are only for show.
	document.getElementById( 'qwv-site' ).addEventListener( 'click', function ( event ) {
		if ( event.target.closest( 'a' ) ) {
			event.preventDefault();
		}
	} );

	form.addEventListener( 'input', update );
	form.addEventListener( 'change', update );
	update();
} );
JS;

	$version = (string) get_bloginfo( 'version' );

	wp_register_script( 'quick-wp-version-admin', false, array(), '2.0', true );
	wp_enqueue_script( 'quick-wp-version-admin' );
	wp_add_inline_script(
		'quick-wp-version-admin',
		'window.qwvPreview = ' . wp_json_encode(
			array(
				'version' => $version,
				'major'   => strtok( $version, '.' ),
				/* translators: %s: the word WordPress */
				'plain'   => __( 'Powered by %s', 'display-wordpress-version' ),
				/* translators: 1: the word WordPress, 2: version number */
				'number'  => __( 'Powered by %1$s %2$s', 'display-wordpress-version' ),
				'whereOn' => __( 'This is how the line looks at the very bottom of every page.', 'display-wordpress-version' ),
				'waiting'  => quick_wp_version_update_waiting(),
				'marks'    => quick_wp_version_separators(),
				'linkNote' => __( 'The word WordPress links to https://wordpress.org/.', 'display-wordpress-version' ),
				/* translators: %s: newest WordPress version */
				'hiddenNote' => sprintf( __( 'WordPress %s is out, so the number is hidden until you update.', 'display-wordpress-version' ), quick_wp_version_update_waiting() ),
				/* translators: %s: newest WordPress version */
				'oldNote'  => sprintf( __( 'Careful: WordPress %s is out, and this line tells visitors your site has an older version.', 'display-wordpress-version' ), quick_wp_version_update_waiting() ),
				'whereOff' => __( 'The automatic line is off. The line looks like this wherever you place the shortcode, widget or template tag.', 'display-wordpress-version' ),
			)
		) . ';' . $script
	);
}

/**
 * The settings page itself.
 */
function quick_wp_version_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings  = quick_wp_version_settings();
	$name      = QUICK_WP_VERSION_OPTION;
	$installed = (string) get_bloginfo( 'version' );
	$major     = strtok( $installed, '.' );
	$latest    = quick_wp_version_latest();

	$examples = array(
		/* translators: %s: the word WordPress */
		'plain' => sprintf( __( 'Powered by %s', 'display-wordpress-version' ), 'WordPress' ),
		/* translators: 1: the word WordPress, 2: version number */
		'major' => sprintf( __( 'Powered by %1$s %2$s', 'display-wordpress-version' ), 'WordPress', $major ),
		/* translators: 1: the word WordPress, 2: version number */
		'full'  => sprintf( __( 'Powered by %1$s %2$s', 'display-wordpress-version' ), 'WordPress', $installed ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Quick WordPress Version', 'display-wordpress-version' ); ?></h1>
		<p><?php esc_html_e( 'See which WordPress your site runs, and choose the Powered by WordPress line your visitors see. The preview updates as you go.', 'display-wordpress-version' ); ?></p>

		<div class="qwv-wrap">
		<div class="qwv-main">
		<div class="qwv-card qwv-c1">
			<h2><?php esc_html_e( 'Your WordPress', 'display-wordpress-version' ); ?></h2>
			<div class="qwv-versions">
				<div>
					<span><?php esc_html_e( 'Installed on this site', 'display-wordpress-version' ); ?></span>
					<strong><?php echo esc_html( $installed ); ?></strong>
				</div>
				<div>
					<span><?php esc_html_e( 'Latest from WordPress.org', 'display-wordpress-version' ); ?></span>
					<strong><?php echo esc_html( $latest ? $latest : '—' ); ?></strong>
				</div>
			</div>
			<p>
				<?php if ( '' === $latest ) : ?>
					<span class="qwv-status qwv-unknown"><?php esc_html_e( 'Not checked', 'display-wordpress-version' ); ?></span>
					<?php esc_html_e( 'WordPress.org could not be reached just now.', 'display-wordpress-version' ); ?>
					<?php if ( current_user_can( 'update_core' ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'update-core.php?force-check=1' ) ); ?>"><?php esc_html_e( 'Check again', 'display-wordpress-version' ); ?></a>
					<?php endif; ?>
				<?php elseif ( version_compare( $installed, $latest, '>=' ) ) : ?>
					<span class="qwv-status qwv-ok"><?php esc_html_e( 'Up to date', 'display-wordpress-version' ); ?></span>
					<?php esc_html_e( 'You have the latest WordPress.', 'display-wordpress-version' ); ?>
				<?php else : ?>
					<span class="qwv-status qwv-old"><?php esc_html_e( 'Update available', 'display-wordpress-version' ); ?></span>
					<?php
					/* translators: %s: newest WordPress version */
					echo esc_html( sprintf( __( 'WordPress %s is out. Updating keeps your site safe.', 'display-wordpress-version' ), $latest ) );
					if ( $settings['hide_old'] ) {
						echo ' ' . esc_html__( 'Until you update, your line does not show a version number.', 'display-wordpress-version' );
					}
					?>
					<?php if ( current_user_can( 'update_core' ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>"><?php esc_html_e( 'Update now', 'display-wordpress-version' ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</p>
		</div>

		<form id="qwv-form" method="post" action="options.php">
			<?php settings_fields( 'quick_wp_version' ); ?>

			<div class="qwv-card qwv-c2">
				<h2><?php esc_html_e( 'What to show', 'display-wordpress-version' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Line', 'display-wordpress-version' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'Line', 'display-wordpress-version' ); ?></legend>
								<label>
									<input type="radio" name="<?php echo esc_attr( $name . '[show]' ); ?>" value="plain" <?php checked( $settings['show'], 'plain' ); ?>>
									<?php echo esc_html( $examples['plain'] ); ?>
									<em>&mdash; <?php esc_html_e( 'safest, keeps your version private', 'display-wordpress-version' ); ?></em>
								</label>
								<label>
									<input type="radio" name="<?php echo esc_attr( $name . '[show]' ); ?>" value="major" <?php checked( $settings['show'], 'major' ); ?>>
									<?php echo esc_html( $examples['major'] ); ?>
								</label>
								<label>
									<input type="radio" name="<?php echo esc_attr( $name . '[show]' ); ?>" value="full" <?php checked( $settings['show'], 'full' ); ?>>
									<?php echo esc_html( $examples['full'] ); ?>
								</label>
								<div id="qwv-custom-wrap">
									<label for="qwv-show-custom" style="margin-bottom:0">
										<input type="radio" id="qwv-show-custom" name="<?php echo esc_attr( $name . '[show]' ); ?>" value="custom" <?php checked( $settings['show'], 'custom' ); ?>>
										<?php esc_html_e( 'My own text:', 'display-wordpress-version' ); ?>
									</label>
									<input type="text" id="qwv-custom-text" class="regular-text" name="<?php echo esc_attr( $name . '[custom]' ); ?>" value="<?php echo esc_attr( $settings['custom'] ); ?>" placeholder="<?php esc_attr_e( 'Proudly built with WordPress', 'display-wordpress-version' ); ?>">
									<p class="description">
										<?php
										/* translators: 1: {version} placeholder, 2: installed version, 3: {major} placeholder, 4: main version number */
										printf( esc_html__( 'Use %1$s for %2$s and %3$s for %4$s. Links and simple HTML are fine.', 'display-wordpress-version' ), '<code>{version}</code>', esc_html( $installed ), '<code>{major}</code>', esc_html( $major ) );
										?>
									</p>
								</div>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'When WordPress is out of date', 'display-wordpress-version' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $name . '[hide_old]' ); ?>" value="1" <?php checked( $settings['hide_old'] ); ?>>
								<?php esc_html_e( 'Hide the version number until WordPress is updated', 'display-wordpress-version' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Older versions can have known security holes, so it is best not to show them. While an update is waiting, visitors see just Powered by WordPress. The number comes back by itself after you update.', 'display-wordpress-version' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Link', 'display-wordpress-version' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $name . '[link]' ); ?>" value="1" <?php checked( $settings['link'] ); ?>>
								<?php
								/* translators: %s: web address of WordPress.org */
								printf( esc_html__( 'Link the word WordPress to %s', 'display-wordpress-version' ), '<code>https://wordpress.org/</code>' );
								?>
							</label>
							<p class="description"><?php esc_html_e( 'Visitors who click WordPress in the line go to the official WordPress website. This is for the three ready-made lines; in your own text, add any links you like.', 'display-wordpress-version' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<div class="qwv-card qwv-c3">
				<h2><?php esc_html_e( 'Text around the line (optional)', 'display-wordpress-version' ); ?></h2>
				<p class="qwv-intro"><?php esc_html_e( 'Add your own words before or after the line, such as a copyright or a credit to your host. The mark below joins them up, so there is no need to type one.', 'display-wordpress-version' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="qwv-before"><?php esc_html_e( 'Text before', 'display-wordpress-version' ); ?></label></th>
						<td>
							<input type="text" id="qwv-before" class="large-text" name="<?php echo esc_attr( $name . '[before]' ); ?>" value="<?php echo esc_attr( $settings['before'] ); ?>" placeholder="<?php echo esc_attr( '© ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="qwv-after"><?php esc_html_e( 'Text after', 'display-wordpress-version' ); ?></label></th>
						<td>
							<input type="text" id="qwv-after" class="large-text" name="<?php echo esc_attr( $name . '[after]' ); ?>" value="<?php echo esc_attr( $settings['after'] ); ?>" placeholder="<?php esc_attr_e( 'Hosted by …', 'display-wordpress-version' ); ?>">
							<p class="description"><?php esc_html_e( 'Links and simple HTML are fine in both boxes.', 'display-wordpress-version' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="qwv-sep"><?php esc_html_e( 'Joined with', 'display-wordpress-version' ); ?></label></th>
						<td>
							<select id="qwv-sep" name="<?php echo esc_attr( $name . '[sep]' ); ?>">
								<option value="bullet" <?php selected( $settings['sep'], 'bullet' ); ?>><?php esc_html_e( '• Bullet', 'display-wordpress-version' ); ?></option>
								<option value="bar" <?php selected( $settings['sep'], 'bar' ); ?>><?php esc_html_e( '| Line', 'display-wordpress-version' ); ?></option>
								<option value="dash" <?php selected( $settings['sep'], 'dash' ); ?>><?php esc_html_e( '– Dash', 'display-wordpress-version' ); ?></option>
								<option value="dot" <?php selected( $settings['sep'], 'dot' ); ?>><?php esc_html_e( '· Dot', 'display-wordpress-version' ); ?></option>
								<option value="space" <?php selected( $settings['sep'], 'space' ); ?>><?php esc_html_e( 'Just a space', 'display-wordpress-version' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Added by itself between the line and your text. A full stop or mark you type at the join is left out, so it never shows twice.', 'display-wordpress-version' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<div class="qwv-card qwv-c4">
				<h2><?php esc_html_e( 'Where it appears', 'display-wordpress-version' ); ?></h2>
				<p class="qwv-intro"><?php esc_html_e( 'This is all most sites need. The line appears by itself at the very bottom of every page.', 'display-wordpress-version' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Bottom of every page', 'display-wordpress-version' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $name . '[footer]' ); ?>" value="1" <?php checked( $settings['footer'] ); ?>>
								<?php esc_html_e( 'Add the line automatically, below your theme\'s footer', 'display-wordpress-version' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Works with any theme. No theme files to edit.', 'display-wordpress-version' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="qwv-align"><?php esc_html_e( 'Line position', 'display-wordpress-version' ); ?></label></th>
						<td>
							<select id="qwv-align" name="<?php echo esc_attr( $name . '[align]' ); ?>">
								<option value="center" <?php selected( $settings['align'], 'center' ); ?>><?php esc_html_e( 'Centre', 'display-wordpress-version' ); ?></option>
								<option value="left" <?php selected( $settings['align'], 'left' ); ?>><?php esc_html_e( 'Left', 'display-wordpress-version' ); ?></option>
								<option value="right" <?php selected( $settings['align'], 'right' ); ?>><?php esc_html_e( 'Right', 'display-wordpress-version' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
			</div>

			<?php submit_button(); ?>
		</form>

		<div class="qwv-card qwv-c5 qwv-other">
			<h2><?php esc_html_e( 'Other ways to add the line (optional)', 'display-wordpress-version' ); ?></h2>
			<p class="qwv-intro"><?php esc_html_e( 'You do not need these. Use one only if you want the line in a different place, such as a sidebar. They show the same line you chose above. If you use one, you can untick the automatic line.', 'display-wordpress-version' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Widget', 'display-wordpress-version' ); ?></th>
					<td>
						<?php
						printf(
							/* translators: %s: link to Appearance > Widgets */
							esc_html__( 'If your theme has widget areas, add the Quick WordPress Version widget to a footer or sidebar in %s.', 'display-wordpress-version' ),
							'<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">' . esc_html__( 'Appearance > Widgets', 'display-wordpress-version' ) . '</a>'
						);
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Shortcode', 'display-wordpress-version' ); ?></th>
					<td>
						<code>[quick_wp_version]</code>
						<p class="description"><?php esc_html_e( 'Paste it into a post, a page, or a Shortcode block, and the line shows there.', 'display-wordpress-version' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Theme template', 'display-wordpress-version' ); ?><br><span class="qwv-dev"><?php esc_html_e( 'For theme developers', 'display-wordpress-version' ); ?></span></th>
					<td>
						<code>&lt;?php quickversion(); ?&gt;</code>
						<p class="description"><?php esc_html_e( 'Prints the line exactly where it is placed in a theme file. Pages that use it skip the automatic line.', 'display-wordpress-version' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		</div>

		<div class="qwv-side qwv-card qwv-preview">
			<h2><?php esc_html_e( 'Preview', 'display-wordpress-version' ); ?></h2>
			<div class="qwv-site" id="qwv-site" aria-label="<?php esc_attr_e( 'Preview of your site', 'display-wordpress-version' ); ?>">
				<div class="qwv-bar"><i></i><i></i><i></i><span><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></span></div>
				<div class="qwv-page">
					<strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong>
					<b style="width:92%"></b><b style="width:85%"></b><b style="width:60%"></b>
				</div>
				<div class="qwv-foot">
					<?php
					/* translators: 1: current year, 2: site name */
					echo esc_html( sprintf( __( '© %1$s %2$s · Your theme\'s footer', 'display-wordpress-version' ), gmdate( 'Y' ), get_bloginfo( 'name' ) ) );
					?>
				</div>
				<div class="qwv-line" style="text-align:<?php echo esc_attr( $settings['align'] ); ?>">
					<span class="qwv-tag"><?php esc_html_e( 'Your line', 'display-wordpress-version' ); ?></span>
					<span id="qwv-preview"><?php echo quick_wp_version_line( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts. ?></span>
				</div>
			</div>
			<p class="description" id="qwv-where"></p>
		</div>
		</div>
	</div>
	<?php
}
