<?php
/**
 * Shared look of the QOT Font Changer settings page: the QOT house style
 * (white cards with coloured heading bars, a live preview window) and
 * the quiet help box every QOT plugin has.
 *
 * @package qot-font-changer
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The house style for the settings page, as one CSS string.
 *
 * @return string
 */
function qot_font_changer_admin_base_css() {
	return
		// Settings on the left, the preview on the right; on narrow screens the preview goes on top.
		'.qfch-wrap{display:flex;flex-wrap:wrap;gap:32px;align-items:flex-start}' .
		'.qfch-wrap form{max-width:640px;flex:1 1 480px}' .
		// Each section is a white card with its heading in a coloured bar across the top.
		'.qfch-card{box-sizing:border-box;background:#fff;border:1px solid #c3c4c7;border-radius:4px;box-shadow:0 1px 1px rgba(0,0,0,.04);margin:0 0 20px;padding:0 20px 6px}' .
		'.qfch-card>h2{margin:0 -20px;padding:12px 20px;border-bottom:1px solid #dcdcde;background:#f0f6fc;border-radius:4px 4px 0 0;font-size:15px}' .
		// Heading colours in a fixed order: blue, green, yellow, pink, purple (c1 to c5, in page order).
		'.qfch-c1>h2{background:#f0f6fc}.qfch-c2>h2{background:#edfaef}.qfch-c3>h2{background:#fcf9e8}' .
		'.qfch-c4>h2{background:#fcf0f1}.qfch-c5>h2{background:#f5f0fc}' .
		// The preview is a live window, not a settings card: a teal frame with a faint dotted
		// background, an eye icon and a Live badge, with the preview itself sitting on top like a screen.
		'.qfch-preview{background:#1d6b65 radial-gradient(rgba(255,255,255,.12) 1px,transparent 1.5px) 0 0/16px 16px;border-color:#11433f;box-shadow:0 2px 8px rgba(0,0,0,.18)}' .
		'.qfch-preview>h2{display:flex;align-items:center;gap:8px;background:#155550;color:#f0fdfa;border-bottom-color:#2a8a82}' .
		'.qfch-preview>h2:before{content:"\f177";font:normal 20px/1 dashicons;color:#99f6e4}' .
		'.qfch-live{margin-left:auto;position:relative;padding:2px 8px 2px 18px;border:1px solid #99f6e4;border-radius:10px;background:rgba(153,246,228,.14);color:#ccfbf1;font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase}' .
		'.qfch-live:before{content:"";position:absolute;left:7px;top:50%;width:6px;height:6px;margin-top:-3px;border-radius:50%;background:#99f6e4}' .
		'.qfch-card.qfch-preview>.description{color:#e6f7f5;margin:12px 0 0}' .
		'.qfch-screen{margin-top:20px;background:#fff;border:1px solid #dcdcde;border-radius:6px;box-shadow:0 6px 18px rgba(0,0,0,.18);padding:14px;color:#1d2327;overflow:hidden}' .
		'.qfch-card .form-table{margin-top:4px}.qfch-card .form-table th{padding-left:0;width:150px}' .
		'.qfch-card .description{margin:6px 0 0}' .
		'.qfch-side{flex:1 1 400px;max-width:640px;position:sticky;top:46px}.qfch-side.qfch-card{padding-bottom:20px}' .
		'@media (max-width:1279px){.qfch-wrap{gap:20px}.qfch-side{order:-1;position:static}}' .
		// The quiet help box under the settings.
		'.qfch-help{max-width:640px;margin:8px 0 0;padding:10px 14px;border-left:4px solid #72aee6;background:#fff;color:#50575e;font-size:13px;line-height:1.7}' .
		'.qfch-help strong{color:#1d2327}.qfch-help a{white-space:nowrap}';
}

/**
 * The quiet help box: support forum, rating, newsletter and the rest of the
 * QOT plugin family. No banners, no notices elsewhere in the admin.
 */
function qot_font_changer_help_box() {
	$links = array(
		'https://wordpress.org/support/plugin/qot-font-changer/'                => __( 'Ask in the support forum', 'qot-font-changer' ),
		'https://wordpress.org/support/plugin/qot-font-changer/reviews/#new-post' => __( 'Rate it', 'qot-font-changer' ),
		'https://www.quickonlinetips.com/archives/newsletter/'          => __( 'Free weekly newsletter', 'qot-font-changer' ),
		'https://www.quickonlinetips.com/archives/wordpress-plugins/'   => __( 'More QOT plugins', 'qot-font-changer' ),
	);
	$out = array();
	foreach ( $links as $url => $label ) {
		$out[] = '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a>';
	}
	echo '<p class="qfch-help"><strong>' . esc_html__( 'Need help?', 'qot-font-changer' ) . '</strong> ' . implode( ' &middot; ', $out ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts above.
}

/**
 * Add "Settings" and "Support" links under the plugin's name on the Plugins screen.
 *
 * @param string   $settings_url Address of the settings page.
 * @param string[] $links        Existing links.
 * @return string[]
 */
function qot_font_changer_plugin_links( $settings_url, $links ) {
	array_unshift(
		$links,
		'<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'qot-font-changer' ) . '</a>',
		'<a href="' . esc_url( 'https://wordpress.org/support/plugin/qot-font-changer/' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Support', 'qot-font-changer' ) . '</a>'
	);
	return $links;
}
