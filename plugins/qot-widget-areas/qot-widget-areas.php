<?php
/**
 * Plugin Name:       QOT Widget Areas
 * Plugin URI:        https://www.quickonlinetips.com/archives/wordpress-plugins/
 * Description:       Adds new widget areas to any theme: below the header, before and after posts, below the comments, at the top and bottom of the sidebar and above the footer. Fill them in Appearance > Widgets; empty areas add nothing.
 * Version:           1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qot-widget-areas
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QOT_WIDGET_AREAS_OPTION', 'qot_widget_areas' );
define( 'QOT_WIDGET_AREAS_VERSION', '1.0' );

require_once __DIR__ . '/includes/admin-common.php';

/**
 * The widget areas, in page order. 'themes' says which kind of theme has
 * the spot: 'all', 'classic' (themes with a sidebar) or 'block'.
 *
 * @return array<string, array{label: string, note: string, themes: string}>
 */
function qot_widget_areas_list() {
	return array(
		'below-header'    => array(
			'label'  => __( 'Below the header', 'qot-widget-areas' ),
			'note'   => __( 'A notice bar or banner across the top of every page. In classic themes it shows at the very top of the page.', 'qot-widget-areas' ),
			'themes' => 'all',
		),
		'before-post'     => array(
			'label'  => __( 'Before the post', 'qot-widget-areas' ),
			'note'   => __( 'Above the text of each post, below its title.', 'qot-widget-areas' ),
			'themes' => 'all',
		),
		'after-post'      => array(
			'label'  => __( 'After the post', 'qot-widget-areas' ),
			'note'   => __( 'A newsletter form, author box or related links at the end of each post.', 'qot-widget-areas' ),
			'themes' => 'all',
		),
		'after-comments'  => array(
			'label'  => __( 'Below the comments', 'qot-widget-areas' ),
			'note'   => __( 'After the comment form, at the end of the post page.', 'qot-widget-areas' ),
			'themes' => 'all',
		),
		'sidebar-top'     => array(
			'label'  => __( 'Top of the sidebar', 'qot-widget-areas' ),
			'note'   => __( 'Above your theme\'s own sidebar widgets.', 'qot-widget-areas' ),
			'themes' => 'classic',
		),
		'sidebar-bottom'  => array(
			'label'  => __( 'Bottom of the sidebar', 'qot-widget-areas' ),
			'note'   => __( 'Below your theme\'s own sidebar widgets.', 'qot-widget-areas' ),
			'themes' => 'classic',
		),
		'above-footer'    => array(
			'label'  => __( 'Above the footer', 'qot-widget-areas' ),
			'note'   => __( 'A wide area just before the site footer on every page.', 'qot-widget-areas' ),
			'themes' => 'all',
		),
	);
}

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * Default settings: every area, post areas on posts.
 *
 * @return array
 */
function qot_widget_areas_defaults() {
	return array(
		'areas'   => array_fill_keys( array_keys( qot_widget_areas_list() ), true ),
		'show_on' => 'posts',
	);
}

/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function qot_widget_areas_settings() {
	$saved = get_option( QOT_WIDGET_AREAS_OPTION, array() );
	$s     = array_merge( qot_widget_areas_defaults(), is_array( $saved ) ? $saved : array() );
	return $s;
}

/**
 * Check every field before it is saved.
 *
 * @param mixed $input Raw form values.
 * @return array
 */
function qot_widget_areas_sanitize( $input ) {
	$input = is_array( $input ) ? $input : array();
	$areas = array();
	foreach ( array_keys( qot_widget_areas_list() ) as $key ) {
		$areas[ $key ] = ! empty( $input['areas'][ $key ] );
	}
	return array(
		'areas'   => $areas,
		'show_on' => ( isset( $input['show_on'] ) && in_array( $input['show_on'], array( 'posts', 'pages', 'both' ), true ) ) ? $input['show_on'] : 'posts',
	);
}

/**
 * Is this area switched on?
 *
 * @param string $key Area key.
 * @return bool
 */
function qot_widget_areas_on( $key ) {
	$s = qot_widget_areas_settings();
	return ! empty( $s['areas'][ $key ] );
}

/**
 * Does the current theme have this spot?
 *
 * @param string $key Area key.
 * @return bool
 */
function qot_widget_areas_fits( $key ) {
	$list  = qot_widget_areas_list();
	$block = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	return 'all' === $list[ $key ]['themes'] || ( 'classic' === $list[ $key ]['themes'] && ! $block ) || ( 'block' === $list[ $key ]['themes'] && $block );
}

/* ------------------------------------------------------------------ *
 * Registering the areas
 * ------------------------------------------------------------------ */

/**
 * Add the switched-on areas to Appearance > Widgets.
 */
function qot_widget_areas_register() {
	foreach ( qot_widget_areas_list() as $key => $area ) {
		if ( ! qot_widget_areas_on( $key ) || ! qot_widget_areas_fits( $key ) ) {
			continue;
		}
		register_sidebar(
			array(
				/* translators: %s: area name, like "Below the header". */
				'name'          => sprintf( __( 'QOT: %s', 'qot-widget-areas' ), $area['label'] ),
				'id'            => 'qot-' . $key,
				'description'   => $area['note'],
				'before_widget' => '<section id="%1$s" class="widget qwa-widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title qwa-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'qot_widget_areas_register', 99 );

/* ------------------------------------------------------------------ *
 * Showing the areas
 * ------------------------------------------------------------------ */

/**
 * One area's widgets, or nothing when it is empty.
 *
 * @param string $key Area key.
 * @return string
 */
function qot_widget_areas_html( $key ) {
	$id = 'qot-' . $key;
	if ( ! qot_widget_areas_on( $key ) || ! is_active_sidebar( $id ) ) {
		return '';
	}
	ob_start();
	dynamic_sidebar( $id );
	$widgets = ob_get_clean();
	return '<div class="qwa-area qwa-' . esc_attr( $key ) . '">' . $widgets . '</div>';
}

/**
 * Is this the main post or page, where the post areas belong?
 *
 * @return bool
 */
function qot_widget_areas_post_page() {
	$s     = qot_widget_areas_settings();
	$types = array(
		'posts' => array( 'post' ),
		'pages' => array( 'page' ),
		'both'  => array( 'post', 'page' ),
	);
	return is_singular( $types[ $s['show_on'] ] ) && ! is_feed() && ! is_embed();
}

/**
 * Before and after the post text.
 *
 * @param string $content Post content.
 * @return string
 */
function qot_widget_areas_content( $content ) {
	if ( ! qot_widget_areas_post_page() || get_the_ID() !== get_queried_object_id() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	return qot_widget_areas_html( 'before-post' ) . $content . qot_widget_areas_html( 'after-post' );
}
add_filter( 'the_content', 'qot_widget_areas_content', 30 );

/**
 * Below the comments.
 */
function qot_widget_areas_comments() {
	if ( qot_widget_areas_post_page() ) {
		echo qot_widget_areas_html( 'after-comments' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- widget output.
	}
}
add_action( 'comment_form_after', 'qot_widget_areas_comments' );

/**
 * Top and bottom of the theme's first sidebar (classic themes).
 *
 * @param int|string $index       Sidebar being shown.
 * @param bool       $has_widgets Whether it has widgets.
 * @param string     $where       'top' or 'bottom'.
 */
function qot_widget_areas_sidebar( $index, $has_widgets, $where ) {
	static $first = null;
	if ( is_admin() || 0 === strpos( (string) $index, 'qot-' ) ) {
		return;
	}
	if ( null === $first ) {
		$first = (string) $index;
	}
	if ( (string) $index === $first ) {
		echo qot_widget_areas_html( 'sidebar-' . $where ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- widget output.
	}
}
add_action(
	'dynamic_sidebar_before',
	function ( $index, $has ) {
		qot_widget_areas_sidebar( $index, $has, 'top' );
	},
	10,
	2
);
add_action(
	'dynamic_sidebar_after',
	function ( $index, $has ) {
		qot_widget_areas_sidebar( $index, $has, 'bottom' );
	},
	10,
	2
);

/**
 * Below the header and above the footer in block themes.
 *
 * @param string $html  Block output.
 * @param array  $block Block.
 * @return string
 */
function qot_widget_areas_template_part( $html, $block ) {
	$area = isset( $block['attrs']['tagName'] ) ? $block['attrs']['tagName'] : '';
	$slug = isset( $block['attrs']['slug'] ) ? $block['attrs']['slug'] : '';
	if ( 'header' === $area || 'header' === $slug ) {
		return $html . qot_widget_areas_html( 'below-header' );
	}
	if ( 'footer' === $area || 'footer' === $slug ) {
		return qot_widget_areas_html( 'above-footer' ) . $html;
	}
	return $html;
}

/**
 * Hook into the right places for the current kind of theme.
 */
function qot_widget_areas_setup() {
	if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
		add_filter( 'render_block_core/template-part', 'qot_widget_areas_template_part', 10, 2 );
	} else {
		add_action(
			'wp_body_open',
			function () {
				echo qot_widget_areas_html( 'below-header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- widget output.
			}
		);
		add_action(
			'get_footer',
			function () {
				echo qot_widget_areas_html( 'above-footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- widget output.
			}
		);
	}
}
add_action( 'template_redirect', 'qot_widget_areas_setup' );

/**
 * A little spacing so new areas sit well in any theme. Only added when one has widgets.
 */
function qot_widget_areas_style() {
	foreach ( array_keys( qot_widget_areas_list() ) as $key ) {
		if ( is_active_sidebar( 'qot-' . $key ) ) {
			wp_register_style( 'qot-widget-areas', false, array(), QOT_WIDGET_AREAS_VERSION );
			wp_enqueue_style( 'qot-widget-areas' );
			wp_add_inline_style(
				'qot-widget-areas',
				'.qwa-area{margin:1.5em 0}.qwa-area .qwa-widget{margin:0 0 1em}.qwa-area .qwa-widget:last-child{margin-bottom:0}' .
				'.qwa-below-header,.qwa-above-footer{max-width:var(--wp--style--global--wide-size,1200px);margin:1em auto;padding:0 20px;box-sizing:border-box}'
			);
			return;
		}
	}
}
add_action( 'wp_enqueue_scripts', 'qot_widget_areas_style' );

/* ------------------------------------------------------------------ *
 * Settings page
 * ------------------------------------------------------------------ */

/**
 * Register the one option that holds every setting.
 */
function qot_widget_areas_register_setting() {
	register_setting(
		'qot_widget_areas',
		QOT_WIDGET_AREAS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'qot_widget_areas_sanitize',
			'default'           => qot_widget_areas_defaults(),
		)
	);
}
add_action( 'admin_init', 'qot_widget_areas_register_setting' );

/**
 * Add the page under Appearance, next to Widgets.
 */
function qot_widget_areas_add_page() {
	$hook = add_theme_page( __( 'QOT Widget Areas', 'qot-widget-areas' ), __( 'Widget Areas', 'qot-widget-areas' ), 'manage_options', 'qot-widget-areas', 'qot_widget_areas_render_page' );
	add_action(
		'admin_enqueue_scripts',
		function ( $current ) use ( $hook ) {
			if ( $current === $hook ) {
				qot_widget_areas_admin_assets();
			}
		}
	);
}
add_action( 'admin_menu', 'qot_widget_areas_add_page' );

/**
 * Style and live map for the settings page.
 */
function qot_widget_areas_admin_assets() {
	wp_register_style( 'qot-widget-areas-admin', false, array(), QOT_WIDGET_AREAS_VERSION );
	wp_enqueue_style( 'qot-widget-areas-admin' );
	wp_add_inline_style(
		'qot-widget-areas-admin',
		qot_widget_areas_admin_base_css() .
		'.qwa-list label{display:block;margin:0 0 10px}.qwa-list .description{margin:2px 0 0 24px}.qwa-count{color:#50575e;font-size:12px}.qwa-na{color:#996800;font-size:12px}' .
		'#qwa-map{font-size:12px;line-height:1.4}#qwa-map .m-bar{background:#f0f0f1;border-radius:4px;padding:9px;font-weight:600;color:#50575e;margin:0 0 8px}' .
		'#qwa-map .m-cols{display:flex;gap:10px;margin:0 0 8px}#qwa-map .m-main{flex:1;min-width:0}#qwa-map .m-side{flex:0 0 32%;background:#f6f7f7;border-radius:4px;padding:8px}' .
		'#qwa-map .m-title{height:12px;width:60%;background:#c3c4c7;border-radius:3px;margin:2px 0 10px}#qwa-map i{display:block;height:6px;background:#dcdcde;border-radius:3px;margin:0 0 6px}#qwa-map .m-w{height:28px;background:#e8e9ea;border-radius:3px;margin:0 0 6px}' .
		'#qwa-map .m-form{border:1px solid #dcdcde;border-radius:4px;padding:8px;color:#8c8f94;margin:8px 0}' .
		'#qwa-map .m-a{display:block;margin:0 0 8px;padding:5px 8px;border:1px solid #72aee6;background:#f0f6fc;border-radius:4px;font-weight:600;color:#1d2327}#qwa-map .m-a.is-off{display:none}#qwa-map .m-a.is-na{opacity:.45;border-style:dashed}'
	);
	wp_register_script( 'qot-widget-areas-admin', false, array(), QOT_WIDGET_AREAS_VERSION, true );
	wp_enqueue_script( 'qot-widget-areas-admin' );
	wp_add_inline_script(
		'qot-widget-areas-admin',
		'( function () { var form = document.getElementById( "qwa-form" ); if ( ! form ) { return; } function draw() { document.querySelectorAll( "#qwa-map .m-a" ).forEach( function ( a ) { var box = form.querySelector( "[name=\"qot_widget_areas[areas][" + a.getAttribute( "data-a" ) + "]\"]" ); a.classList.toggle( "is-off", ! box.checked ); } ); } form.addEventListener( "change", draw ); draw(); }() );'
	);
}

/**
 * The settings page.
 */
function qot_widget_areas_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s     = qot_widget_areas_settings();
	$n     = QOT_WIDGET_AREAS_OPTION;
	$list  = qot_widget_areas_list();
	$chip  = function ( $key ) use ( $list ) {
		printf( '<span class="m-a%1$s" data-a="%2$s">%3$s</span>', qot_widget_areas_fits( $key ) ? '' : ' is-na', esc_attr( $key ), esc_html( $list[ $key ]['label'] ) );
	};
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'QOT Widget Areas', 'qot-widget-areas' ); ?></h1>
		<p>
			<?php esc_html_e( 'Choose the new widget areas your theme gets, then fill them in', 'qot-widget-areas' ); ?>
			<a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><?php esc_html_e( 'Appearance > Widgets', 'qot-widget-areas' ); ?></a>.
			<?php esc_html_e( 'An empty area adds nothing to your pages. The map shows where each one goes.', 'qot-widget-areas' ); ?>
		</p>
		<div class="qwa-wrap">
			<form method="post" action="options.php" id="qwa-form">
				<?php settings_fields( 'qot_widget_areas' ); ?>
				<div class="qwa-card qwa-c1">
					<h2><?php esc_html_e( 'Widget areas', 'qot-widget-areas' ); ?></h2>
					<div class="qwa-list" style="padding:14px 0 6px">
						<?php foreach ( $list as $key => $area ) : ?>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $n . '[areas][' . $key . ']' ); ?>" value="1" <?php checked( ! empty( $s['areas'][ $key ] ) ); ?> />
								<strong><?php echo esc_html( $area['label'] ); ?></strong>
								<?php if ( ! qot_widget_areas_fits( $key ) ) : ?>
									<span class="qwa-na"><?php esc_html_e( '(not in your theme)', 'qot-widget-areas' ); ?></span>
								<?php elseif ( is_active_sidebar( 'qot-' . $key ) ) : ?>
									<span class="qwa-count"><?php esc_html_e( 'has widgets', 'qot-widget-areas' ); ?></span>
								<?php endif; ?>
								<span class="description" style="display:block"><?php echo esc_html( $area['note'] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<p class="description" style="margin-bottom:12px"><?php esc_html_e( 'Untick an area to remove it from the Widgets screen. Its widgets are kept and come back if you tick it again.', 'qot-widget-areas' ); ?></p>
				</div>
				<div class="qwa-card qwa-c2">
					<h2><?php esc_html_e( 'Post areas', 'qot-widget-areas' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Show on', 'qot-widget-areas' ); ?></th><td>
							<?php
							foreach ( array( 'posts' => __( 'Posts', 'qot-widget-areas' ), 'pages' => __( 'Pages', 'qot-widget-areas' ), 'both' => __( 'Posts and pages', 'qot-widget-areas' ) ) as $value => $label ) {
								printf( '<label style="margin-right:14px"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label>', esc_attr( $n . '[show_on]' ), esc_attr( $value ), checked( $s['show_on'], $value, false ), esc_html( $label ) );
							}
							?>
							<p class="description"><?php esc_html_e( 'For the areas before and after the post and below the comments. The others show on every page.', 'qot-widget-areas' ); ?></p>
						</td></tr>
					</table>
				</div>
				<?php submit_button(); ?>
				<?php qot_widget_areas_help_box(); ?>
			</form>
			<div class="qwa-side qwa-card qwa-preview">
				<h2><?php esc_html_e( 'Where they go', 'qot-widget-areas' ); ?> <span class="qwa-live"><?php esc_html_e( 'Live', 'qot-widget-areas' ); ?></span></h2>
				<div class="qwa-screen" id="qwa-map">
					<div class="m-bar"><?php esc_html_e( 'Site header', 'qot-widget-areas' ); ?></div>
					<?php $chip( 'below-header' ); ?>
					<div class="m-cols">
						<div class="m-main">
							<div class="m-title"></div>
							<?php $chip( 'before-post' ); ?>
							<i></i><i></i><i style="width:70%"></i><i></i><i style="width:55%"></i>
							<?php $chip( 'after-post' ); ?>
							<div class="m-form"><?php esc_html_e( 'Comments', 'qot-widget-areas' ); ?></div>
							<?php $chip( 'after-comments' ); ?>
						</div>
						<div class="m-side">
							<?php $chip( 'sidebar-top' ); ?>
							<div class="m-w"></div><div class="m-w"></div>
							<?php $chip( 'sidebar-bottom' ); ?>
						</div>
					</div>
					<?php $chip( 'above-footer' ); ?>
					<div class="m-bar"><?php esc_html_e( 'Site footer', 'qot-widget-areas' ); ?></div>
				</div>
				<p class="description"><?php esc_html_e( 'A plan of a typical post page. Faded areas are spots your current theme doesn\'t have.', 'qot-widget-areas' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		return qot_widget_areas_plugin_links( admin_url( 'themes.php?page=qot-widget-areas' ), $links );
	}
);

/**
 * When someone deletes the plugin, remove its saved settings too.
 * Widgets placed in its areas are moved by WordPress to Inactive widgets.
 */
function qot_widget_areas_uninstall() {
	delete_option( QOT_WIDGET_AREAS_OPTION );
}
register_uninstall_hook( __FILE__, 'qot_widget_areas_uninstall' );
