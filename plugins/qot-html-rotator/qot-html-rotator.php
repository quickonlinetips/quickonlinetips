<?php
/**
 * Plugin Name:       QOT HTML Rotator
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       Rotate several HTML boxes (ads, banners, offers, notes) in one sidebar widget or shortcode. Give each box a weight, like 1:1 or 5:1, and visitors see them in that ratio. Works with page caching.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-html-rotator
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_HTML_ROTATOR_OPTION', 'qot_html_rotator' );
define( 'QOT_HTML_ROTATOR_VERSION', '1.0' );
define( 'QOT_HTML_ROTATOR_MAX', 30 );

require_once __DIR__ . '/includes/admin-common.php';

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * An empty box.
 *
 * @return array
 */
function qot_html_rotator_blank() {
	return array(
		'title'  => '',
		'html'   => '',
		'weight' => 1,
		'group'  => 'main',
		'on'     => true,
	);
}

/**
 * Default settings: two empty boxes in the "main" rotation.
 *
 * @return array
 */
function qot_html_rotator_defaults() {
	return array(
		'boxes'  => array(),
		'sticky' => false,
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_html_rotator_settings() {
	$saved = get_option( QOT_HTML_ROTATOR_OPTION, array() );
	return array_merge( qot_html_rotator_defaults(), is_array( $saved ) ? $saved : array() );
}

/**
 * A rotation name: lower case letters, numbers and dashes.
 *
 * @param string $group Raw name.
 * @return string
 */
function qot_html_rotator_group_key( $group ) {
	$group = sanitize_title( (string) $group );
	return '' === $group ? 'main' : substr( $group, 0, 40 );
}

/**
 * Check every field before it is saved. Only people allowed to add scripts
 * on this site can save scripts; others keep plain HTML.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_html_rotator_sanitize( $input ) {
	$input = is_array( $input ) ? $input : array();
	$boxes = array();
	if ( ! empty( $input['boxes'] ) && is_array( $input['boxes'] ) ) {
		foreach ( array_values( $input['boxes'] ) as $box ) {
			if ( count( $boxes ) >= QOT_HTML_ROTATOR_MAX || ! is_array( $box ) ) {
				break;
			}
			$html = isset( $box['html'] ) ? trim( (string) $box['html'] ) : '';
			$html = current_user_can( 'unfiltered_html' ) ? $html : wp_kses_post( $html );
			$boxes[] = array(
				'title'  => isset( $box['title'] ) ? sanitize_text_field( $box['title'] ) : '',
				'html'   => $html,
				'weight' => isset( $box['weight'] ) ? min( 100, max( 1, absint( $box['weight'] ) ) ) : 1,
				'group'  => qot_html_rotator_group_key( isset( $box['group'] ) ? $box['group'] : '' ),
				'on'     => ! empty( $box['on'] ),
			);
		}
	}
	return array(
		'boxes'  => $boxes,
		'sticky' => ! empty( $input['sticky'] ),
	);
}

/**
 * The names of all rotations in use.
 *
 * @return string[]
 */
function qot_html_rotator_groups() {
	$groups = array( 'main' );
	foreach ( qot_html_rotator_settings()['boxes'] as $box ) {
		$groups[] = $box['group'];
	}
	return array_values( array_unique( $groups ) );
}

/* ------------------------------------------------------------------ *
 * On the site
 * ------------------------------------------------------------------ */

/**
 * A rotation's HTML. Every live box goes into the page inside a <template>
 * (which the browser doesn't show or run), and a tiny script picks one by
 * weight, so cached pages still rotate.
 *
 * @param string $group Rotation name.
 * @return string
 */
function qot_html_rotator_render( $group = 'main' ) {
	$group = qot_html_rotator_group_key( $group );
	$s     = qot_html_rotator_settings();
	$out   = '';
	$first = '';
	foreach ( $s['boxes'] as $box ) {
		if ( ! $box['on'] || '' === $box['html'] || $box['group'] !== $group ) {
			continue;
		}
		$html = do_shortcode( $box['html'] );
		if ( '' === $first ) {
			$first = $html;
		}
		$out .= '<template data-w="' . (int) $box['weight'] . '">' . $html . '</template>';
	}
	if ( '' === $out ) {
		return '';
	}
	wp_enqueue_script( 'qot-html-rotator' );
	return '<div class="qhr" data-qhr="' . esc_attr( $group ) . '"' . ( $s['sticky'] ? ' data-sticky="1"' : '' ) . '>' . $out . '<noscript>' . $first . '</noscript></div>';
}

/**
 * [qot_html_rotator group="main"]
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function qot_html_rotator_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'group' => 'main' ), $atts, 'qot_html_rotator' );
	return qot_html_rotator_render( $atts['group'] );
}
add_shortcode( 'qot_html_rotator', 'qot_html_rotator_shortcode' );

/**
 * The picking script, registered for pages that show a rotation.
 */
function qot_html_rotator_register_script() {
	wp_register_script( 'qot-html-rotator', false, array(), QOT_HTML_ROTATOR_VERSION, true );
	wp_add_inline_script( 'qot-html-rotator', qot_html_rotator_front_script() );
}
add_action( 'wp_enqueue_scripts', 'qot_html_rotator_register_script' );

/**
 * Picks a box by weight, shows it and runs any scripts inside it (scripts
 * copied out of a template don't run on their own).
 *
 * @return string
 */
function qot_html_rotator_front_script() {
	return <<<'JS'
( function () {
	document.querySelectorAll( '.qhr' ).forEach( function ( box ) {
		var items = box.querySelectorAll( 'template' );
		if ( ! items.length ) {
			return;
		}
		var total = 0;
		items.forEach( function ( t ) {
			total += parseInt( t.getAttribute( 'data-w' ), 10 ) || 1;
		} );
		var pick = -1;
		var key = 'qhr-' + box.getAttribute( 'data-qhr' );
		if ( box.getAttribute( 'data-sticky' ) ) {
			try {
				pick = parseInt( window.sessionStorage.getItem( key ), 10 );
			} catch ( e ) {}
		}
		if ( ! ( pick >= 0 && pick < items.length ) ) {
			var r = Math.random() * total;
			for ( pick = 0; pick < items.length - 1; pick++ ) {
				r -= parseInt( items[ pick ].getAttribute( 'data-w' ), 10 ) || 1;
				if ( r < 0 ) {
					break;
				}
			}
			if ( box.getAttribute( 'data-sticky' ) ) {
				try {
					window.sessionStorage.setItem( key, pick );
				} catch ( e ) {}
			}
		}
		var shown = document.createElement( 'div' );
		shown.className = 'qhr-item';
		shown.appendChild( document.importNode( items[ pick ].content, true ) );
		shown.querySelectorAll( 'script' ).forEach( function ( old ) {
			var s = document.createElement( 'script' );
			Array.prototype.forEach.call( old.attributes, function ( a ) {
				s.setAttribute( a.name, a.value );
			} );
			s.text = old.text;
			old.parentNode.replaceChild( s, old );
		} );
		box.appendChild( shown );
	} );
}() );
JS;
}

/**
 * The sidebar widget.
 */
class QOT_HTML_Rotator_Widget extends WP_Widget {

	/**
	 * Set up the widget.
	 */
	public function __construct() {
		parent::__construct(
			'qot_html_rotator',
			__( 'QOT HTML Rotator', 'qot-html-rotator' ),
			array(
				'description'                 => __( 'Shows one of your HTML boxes, picked by weight.', 'qot-html-rotator' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Front end.
	 *
	 * @param array $args     Widget wrappers.
	 * @param array $instance Saved values.
	 */
	public function widget( $args, $instance ) {
		$html = qot_html_rotator_render( isset( $instance['group'] ) ? $instance['group'] : 'main' );
		if ( '' === $html ) {
			return;
		}
		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme markup.
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme markup.
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the site owner's own HTML, checked on save.
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme markup.
	}

	/**
	 * Widget form.
	 *
	 * @param array $instance Saved values.
	 * @return string
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$group = isset( $instance['group'] ) ? $instance['group'] : 'main';
		?>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional):', 'qot-html-rotator' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'group' ) ); ?>"><?php esc_html_e( 'Rotation:', 'qot-html-rotator' ); ?></label>
		<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'group' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'group' ) ); ?>">
			<?php foreach ( qot_html_rotator_groups() as $g ) : ?>
				<option value="<?php echo esc_attr( $g ); ?>" <?php selected( $group, $g ); ?>><?php echo esc_html( $g ); ?></option>
			<?php endforeach; ?>
		</select></p>
		<p><a href="<?php echo esc_url( admin_url( 'options-general.php?page=qot-html-rotator' ) ); ?>"><?php esc_html_e( 'Edit the boxes', 'qot-html-rotator' ); ?></a></p>
		<?php
		return '';
	}

	/**
	 * Save the widget.
	 *
	 * @param array $new_instance New values.
	 * @param array $old_instance Old values.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : '' ),
			'group' => qot_html_rotator_group_key( isset( $new_instance['group'] ) ? $new_instance['group'] : 'main' ),
		);
	}
}
add_action(
	'widgets_init',
	function () {
		register_widget( 'QOT_HTML_Rotator_Widget' );
	}
);

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_html_rotator_register_setting() {
	register_setting(
		'qot_html_rotator',
		QOT_HTML_ROTATOR_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_html_rotator_sanitize',
			'default'           => qot_html_rotator_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_html_rotator_register_setting' );

/**
 * Add the page under Settings.
 */
function qot_html_rotator_add_page() {
	$hook = add_options_page( __( 'QOT HTML Rotator', 'qot-html-rotator' ), __( 'QOT HTML Rotator', 'qot-html-rotator' ), 'manage_options', 'qot-html-rotator', 'qot_html_rotator_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_html_rotator_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_html_rotator_add_page' );

/**
 * Style and live preview for the settings page.
 */
function qot_html_rotator_admin_assets() {
	wp_register_style( 'qot-html-rotator-admin', false, array(), QOT_HTML_ROTATOR_VERSION );
	wp_enqueue_style( 'qot-html-rotator-admin' );
	wp_add_inline_style(
		'qot-html-rotator-admin',
		qot_html_rotator_admin_base_css() .
		'.qhr-box>h2{display:flex;align-items:center;gap:6px}.qhr-box .qhr-hname{font-weight:400;color:#50575e;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}' .
		'.qhr-box textarea{width:100%;min-height:120px;font:13px/1.5 Consolas,Monaco,monospace}.qhr-row{display:flex;flex-wrap:wrap;gap:10px 18px;align-items:center;margin:10px 0}' .
		'.qhr-row label{display:inline-flex;align-items:center;gap:6px}.qhr-row input[type=number]{width:64px}.qhr-row input.qhr-group{width:120px}' .
		'.qhr-remove{margin-left:auto!important;color:#b32d2e;border:0;background:none;cursor:pointer;padding:0}.qhr-remove:hover{text-decoration:underline}' .
		'.qhr-add{margin:0 0 20px!important}.qhr-add .dashicons{vertical-align:text-bottom}.qhr-notice{margin:14px 0 4px;padding:8px 12px;border-left:4px solid #dba617;background:#fcf9e8}' .
		'#qhr-demo h3{font-size:13px;margin:0 0 6px;display:flex;gap:8px;align-items:baseline}#qhr-demo h3 code{font-size:11px;font-weight:400}#qhr-demo .g{margin:0 0 16px}' .
		'#qhr-demo .bar{display:flex;height:26px;border-radius:4px;overflow:hidden;background:#f0f0f1}#qhr-demo .bar span{display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:600;white-space:nowrap;overflow:hidden;min-width:0}' .
		'#qhr-demo .keys{display:flex;flex-wrap:wrap;gap:4px 12px;margin:6px 0 0;font-size:12px;color:#50575e}#qhr-demo .keys i{display:inline-block;width:10px;height:10px;border-radius:2px;margin-right:4px;vertical-align:-1px}#qhr-demo .none{color:#50575e}'
	);
	wp_register_script( 'qot-html-rotator-admin', false, array(), QOT_HTML_ROTATOR_VERSION, true );
	wp_enqueue_script( 'qot-html-rotator-admin' );
	wp_add_inline_script(
		'qot-html-rotator-admin',
		'window.qhrText=' . wp_json_encode(
			array(
				/* translators: %d: box number. */
				'box'     => __( 'Box %d', 'qot-html-rotator' ),
				'none'    => __( 'Add HTML to a box to see how often it shows.', 'qot-html-rotator' ),
				'confirm' => __( 'Remove this box? It is deleted when you save.', 'qot-html-rotator' ),
				'off'     => __( 'off', 'qot-html-rotator' ),
			)
		) . ';' . qot_html_rotator_admin_script()
	);
}

/**
 * Page script: add and remove boxes, and draw how often each one shows.
 *
 * @return string
 */
function qot_html_rotator_admin_script() {
	return <<<'JS'
( function () {
	var form = document.getElementById( 'qhr-form' );
	var list = document.getElementById( 'qhr-boxes' );
	var tpl = document.getElementById( 'qhr-template' );
	var demo = document.getElementById( 'qhr-demo' );
	var t = window.qhrText;
	if ( ! form || ! list || ! demo ) {
		return;
	}
	var colors = [ '#2271b1', '#00a32a', '#dba617', '#d63638', '#8c5fc1', '#0a7d72', '#c4610c', '#50575e' ];
	var next = list.children.length;
	function esc( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[ c ];
		} );
	}
	function f( box, name ) {
		return box.querySelector( '[data-f="' + name + '"]' );
	}
	function draw() {
		var groups = {};
		Array.prototype.forEach.call( list.children, function ( box, i ) {
			var cards = [ 'qhr-c1', 'qhr-c2', 'qhr-c3', 'qhr-c4', 'qhr-c5' ];
			cards.forEach( function ( c ) {
				box.classList.remove( c );
			} );
			box.classList.add( cards[ i % 5 ] );
			var name = f( box, 'title' ).value || t.box.replace( '%d', i + 1 );
			box.querySelector( '.qhr-hname' ).textContent = name + ( f( box, 'on' ).checked ? '' : ' (' + t.off + ')' );
			box.querySelector( '.qhr-num' ).textContent = t.box.replace( '%d', i + 1 );
			if ( ! f( box, 'on' ).checked || ! f( box, 'html' ).value.trim() ) {
				return;
			}
			var g = ( f( box, 'group' ).value || 'main' ).toLowerCase().replace( /[^a-z0-9-]+/g, '-' ).replace( /^-|-$/g, '' ) || 'main';
			( groups[ g ] = groups[ g ] || [] ).push( { name: name, w: Math.max( 1, parseInt( f( box, 'weight' ).value, 10 ) || 1 ) } );
		} );
		var html = '';
		Object.keys( groups ).forEach( function ( g ) {
			var total = groups[ g ].reduce( function ( a, b ) {
				return a + b.w;
			}, 0 );
			var bar = '', keys = '';
			groups[ g ].forEach( function ( b, i ) {
				var pct = Math.round( b.w / total * 100 );
				var c = colors[ i % colors.length ];
				bar += '<span style="width:' + ( b.w / total * 100 ) + '%;background:' + c + '">' + pct + '%</span>';
				keys += '<span><i style="background:' + c + '"></i>' + esc( b.name ) + ' &middot; ' + pct + '%</span>';
			} );
			html += '<div class="g"><h3>' + esc( g ) + ' <code>[qot_html_rotator group="' + esc( g ) + '"]</code></h3><div class="bar">' + bar + '</div><div class="keys">' + keys + '</div></div>';
		} );
		demo.innerHTML = html || '<p class="none">' + esc( t.none ) + '</p>';
	}
	document.getElementById( 'qhr-add' ).addEventListener( 'click', function () {
		var box = document.createElement( 'div' );
		box.innerHTML = tpl.innerHTML.replace( /__i__/g, next++ );
		box = box.firstElementChild;
		list.appendChild( box );
		f( box, 'html' ).focus();
		draw();
	} );
	list.addEventListener( 'click', function ( e ) {
		var b = e.target.closest( '.qhr-remove' );
		if ( b && window.confirm( t.confirm ) ) {
			b.closest( '.qhr-box' ).remove();
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
 * One box card.
 *
 * @param int|string $i   Field index.
 * @param array      $box Box values.
 */
function qot_html_rotator_render_box( $i, $box ) {
	$n = QOT_HTML_ROTATOR_OPTION . '[boxes][' . $i . ']';
	?>
	<div class="qhr-card qhr-box">
		<h2><span class="qhr-num"></span> <span class="qhr-hname"></span></h2>
		<div class="qhr-row">
			<label><?php esc_html_e( 'Name', 'qot-html-rotator' ); ?> <input type="text" data-f="title" name="<?php echo esc_attr( $n . '[title]' ); ?>" value="<?php echo esc_attr( $box['title'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Book ad', 'qot-html-rotator' ); ?>" /></label>
			<label><input type="checkbox" data-f="on" name="<?php echo esc_attr( $n . '[on]' ); ?>" value="1" <?php checked( $box['on'] ); ?> /> <?php esc_html_e( 'Show this box', 'qot-html-rotator' ); ?></label>
		</div>
		<textarea data-f="html" name="<?php echo esc_attr( $n . '[html]' ); ?>" spellcheck="false" aria-label="<?php esc_attr_e( 'HTML', 'qot-html-rotator' ); ?>" placeholder="<?php esc_attr_e( 'Paste your ad code, banner or any HTML here', 'qot-html-rotator' ); ?>"><?php echo esc_textarea( $box['html'] ); ?></textarea>
		<div class="qhr-row">
			<label><?php esc_html_e( 'Weight', 'qot-html-rotator' ); ?> <input type="number" min="1" max="100" data-f="weight" name="<?php echo esc_attr( $n . '[weight]' ); ?>" value="<?php echo esc_attr( $box['weight'] ); ?>" /></label>
			<label><?php esc_html_e( 'Rotation', 'qot-html-rotator' ); ?> <input type="text" class="qhr-group" list="qhr-groups" data-f="group" name="<?php echo esc_attr( $n . '[group]' ); ?>" value="<?php echo esc_attr( $box['group'] ); ?>" /></label>
			<button type="button" class="qhr-remove"><?php esc_html_e( 'Remove', 'qot-html-rotator' ); ?></button>
		</div>
		<p class="description" style="margin-bottom:10px"><?php esc_html_e( 'A box with weight 5 shows five times as often as a box with weight 1 in the same rotation.', 'qot-html-rotator' ); ?></p>
	</div>
	<?php
}

/**
 * The settings page.
 */
function qot_html_rotator_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s     = qot_html_rotator_settings();
	$boxes = $s['boxes'] ? $s['boxes'] : array( qot_html_rotator_blank(), qot_html_rotator_blank() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT HTML Rotator', 'qot-html-rotator' ); ?></h1>
		<p><?php esc_html_e( 'Put several HTML boxes in one place and show a different one to each visitor. Add a box, then place the "QOT HTML Rotator" widget in a sidebar, or the shortcode in a post.', 'qot-html-rotator' ); ?></p>
		<div class="qhr-wrap">
			<form method="post" action="options.php" id="qhr-form">
				<?php settings_fields( 'qot_html_rotator' ); ?>
				<?php if ( ! current_user_can( 'unfiltered_html' ) ) : ?>
					<p class="qhr-notice"><?php esc_html_e( 'Your account is not allowed to add scripts on this site, so only plain HTML is kept when you save.', 'qot-html-rotator' ); ?></p>
				<?php endif; ?>
				<datalist id="qhr-groups"><?php foreach ( qot_html_rotator_groups() as $g ) : ?><option value="<?php echo esc_attr( $g ); ?>"></option><?php endforeach; ?></datalist>
				<div id="qhr-boxes">
					<?php
					foreach ( $boxes as $i => $box ) {
						qot_html_rotator_render_box( $i, array_merge( qot_html_rotator_blank(), $box ) );
					}
					?>
				</div>
				<button type="button" class="button qhr-add" id="qhr-add"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add another box', 'qot-html-rotator' ); ?></button>
				<div class="qhr-card qhr-c1">
					<h2><?php esc_html_e( 'Visitors', 'qot-html-rotator' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Same box', 'qot-html-rotator' ); ?></th><td>
							<label><input type="checkbox" name="<?php echo esc_attr( QOT_HTML_ROTATOR_OPTION . '[sticky]' ); ?>" value="1" <?php checked( $s['sticky'] ); ?> /> <?php esc_html_e( 'Keep showing a visitor the same box during their visit', 'qot-html-rotator' ); ?></label>
							<p class="description"><?php esc_html_e( 'Untick to pick again on every page. Kept only in the visitor\'s browser until they close the tab.', 'qot-html-rotator' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_html_rotator_help_box(); ?>
			</form>
			<div class="qhr-side qhr-card qhr-preview">
				<h2><?php esc_html_e( 'How often each box shows', 'qot-html-rotator' ); ?> <span class="qhr-live"><?php esc_html_e( 'Live', 'qot-html-rotator' ); ?></span></h2>
				<div class="qhr-screen" id="qhr-demo"></div>
				<p class="description"><?php esc_html_e( 'Each rotation with its shortcode, and the share of visitors who see each box. Boxes that are off or empty are left out.', 'qot-html-rotator' ); ?></p>
			</div>
		</div>
		<template id="qhr-template"><?php qot_html_rotator_render_box( '__i__', qot_html_rotator_blank() ); ?></template>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_html_rotator_plugin_links( admin_url( 'options-general.php?page=qot-html-rotator' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function qot_html_rotator_uninstall() {
	delete_option( QOT_HTML_ROTATOR_OPTION );
	delete_option( 'widget_qot_html_rotator' );
}
register_uninstall_hook( __FILE__, 'qot_html_rotator_uninstall' );
