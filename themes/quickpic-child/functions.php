<?php
/**
 * QuickPic Child functions.
 *
 * Loads QuickPic's stylesheet first, then this child theme's style.css,
 * so your CSS always wins. Add your own PHP below.
 *
 * @package QuickPic Child
 */

add_action( 'wp_enqueue_scripts', 'quickpic_child_enqueue_styles', 5 );
function quickpic_child_enqueue_styles() {
	$parent = wp_get_theme( 'quickpic' );
	wp_enqueue_style( 'quickpic', get_template_directory_uri() . '/style.css', array(), $parent->get( 'Version' ) );
	wp_enqueue_style( 'quickpic-child', get_stylesheet_uri(), array( 'quickpic' ), wp_get_theme()->get( 'Version' ) );
}
