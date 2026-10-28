<?php
/**
 * QuickStrap Child functions.
 *
 * Loads Bootstrap and QuickStrap's stylesheet first, then this child
 * theme's style.css, so your CSS always wins. Add your own PHP below.
 *
 * @package QuickStrap Child
 */

add_action( 'wp_enqueue_scripts', 'quickstrap_child_enqueue_styles', 5 );
function quickstrap_child_enqueue_styles() {
	$parent = wp_get_theme( 'quickstrap' );
	// QuickStrap's style.css, after Bootstrap (QuickStrap adds Bootstrap itself).
	wp_enqueue_style( 'quickstrap', get_template_directory_uri() . '/style.css', array( 'quickstrap-bootstrap' ), $parent->get( 'Version' ) );
	wp_enqueue_style( 'quickstrap-child', get_stylesheet_uri(), array( 'quickstrap' ), wp_get_theme()->get( 'Version' ) );
}
