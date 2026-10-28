<?php
/**
 * QuickPress Child functions.
 *
 * Loads QuickPress's stylesheet first, then this child theme's style.css,
 * so your CSS always wins. Add your own PHP below.
 *
 * @package QuickPress Child
 */

add_action( 'wp_enqueue_scripts', 'quickpress_child_enqueue_styles', 5 );
function quickpress_child_enqueue_styles() {
	$parent = wp_get_theme( 'quickpress' );
	wp_enqueue_style( 'quickpress', get_template_directory_uri() . '/style.css', array(), $parent->get( 'Version' ) );
	wp_enqueue_style( 'quickpress-child', get_stylesheet_uri(), array( 'quickpress' ), wp_get_theme()->get( 'Version' ) );
}
