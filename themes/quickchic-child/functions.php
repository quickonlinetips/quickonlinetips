<?php
/**
 * QuickChic Child functions.
 *
 * Loads QuickChic's stylesheet first, then this child theme's style.css,
 * so your CSS always wins. Add your own PHP below.
 *
 * @package QuickChic Child
 */

add_action( 'wp_enqueue_scripts', 'quickchic_child_enqueue_styles', 5 );
function quickchic_child_enqueue_styles() {
	$parent = wp_get_theme( 'quickchic' );
	wp_enqueue_style( 'quickchic', get_template_directory_uri() . '/style.css', array(), $parent->get( 'Version' ) );
	wp_enqueue_style( 'quickchic-child', get_stylesheet_uri(), array( 'quickchic' ), wp_get_theme()->get( 'Version' ) );
}
