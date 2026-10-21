<?php
/**
 * Plugin Name:       Quick Code Styler
 * Plugin URI:        https://github.com/quickonlinetips/quickonlinetips/tree/main/plugins/quick-code-styler
 * Description:       Clean, readable styling for code blocks, inline code and keyboard keys. One small stylesheet, no JavaScript, no settings.
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
 * Load the stylesheet, only on pages that show code.
 */
function quick_code_styler_enqueue() {
	if ( ! quick_code_styler_page_has_code() ) {
		return;
	}

	$file = plugin_dir_path( __FILE__ ) . 'quick-code-styler.css';

	wp_enqueue_style(
		'quick-code-styler',
		plugins_url( 'quick-code-styler.css', __FILE__ ),
		array(),
		(string) filemtime( $file ) // Changes when the file changes, so browsers fetch the new version.
	);
}
add_action( 'wp_enqueue_scripts', 'quick_code_styler_enqueue' );

/**
 * Add the "qcs-on" class to <body> so the styles switch on.
 *
 * @param string[] $classes Classes WordPress is about to add to <body>.
 * @return string[]
 */
function quick_code_styler_body_class( $classes ) {
	if ( quick_code_styler_page_has_code() ) {
		$classes[] = 'qcs-on';
	}

	return $classes;
}
add_filter( 'body_class', 'quick_code_styler_body_class' );
