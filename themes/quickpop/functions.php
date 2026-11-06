<?php
/**
 * QuickPop functions.
 *
 * A block theme: layout, colours and fonts live in theme.json, templates/,
 * parts/ and patterns/. This file only loads the stylesheet, registers the
 * block styles and pattern categories, and switches dark mode off when the
 * "Always light" style is chosen. QuickPop adds no JavaScript.
 *
 * @package QuickPop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme supports. Block themes get most of these from theme.json.
 */
function quickpop_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'quickpop_setup' );

/**
 * Stylesheet version: theme version plus file time, so browsers pick up
 * changes straight away.
 *
 * @return string
 */
function quickpop_version() {
	$file = get_stylesheet_directory() . '/style.css';
	$time = file_exists( $file ) ? filemtime( $file ) : '';
	return wp_get_theme( get_template() )->get( 'Version' ) . ( $time ? '.' . $time : '' );
}

/**
 * Load the one stylesheet.
 */
function quickpop_styles() {
	wp_enqueue_style( 'quickpop-style', get_template_directory_uri() . '/style.css', array(), quickpop_version() );
}
add_action( 'wp_enqueue_scripts', 'quickpop_styles' );

/**
 * Dark mode follows the visitor's device unless the "Always light" style
 * (styles/always-light.json) is chosen in Appearance > Editor > Styles.
 *
 * @return bool
 */
function quickpop_dark_mode() {
	$mode = wp_get_global_settings( array( 'custom', 'darkMode' ) );
	return (bool) apply_filters( 'quickpop_dark_mode', 'off' !== $mode );
}

/**
 * Body class for the "Always light" style.
 *
 * @param array $classes Body classes.
 * @return array
 */
function quickpop_body_class( $classes ) {
	if ( ! quickpop_dark_mode() ) {
		$classes[] = 'qpop-light-only';
	}
	return $classes;
}
add_filter( 'body_class', 'quickpop_body_class' );

/**
 * The "Updated" date on single posts: shown only when a post was changed
 * at least a day after it was published, with a translatable label.
 *
 * @param string $block_content Rendered block.
 * @param array  $block         Block data.
 * @return string
 */
function quickpop_updated_date( $block_content, $block ) {
	if ( empty( $block['attrs']['className'] ) || false === strpos( $block['attrs']['className'], 'qpop-updated' ) ) {
		return $block_content;
	}
	$post_id = get_the_ID();
	if ( ! $post_id || ( get_post_timestamp( $post_id, 'modified' ) - get_post_timestamp( $post_id ) ) < DAY_IN_SECONDS ) {
		return '';
	}
	return preg_replace( '/<time/', '<span class="qpop-updated-label">' . esc_html__( 'Updated', 'quickpop' ) . '</span> <time', $block_content, 1 );
}
add_filter( 'render_block_core/post-date', 'quickpop_updated_date', 10, 2 );

/**
 * Block styles. Their CSS is in style.css, which the editor loads too.
 */
function quickpop_block_styles() {
	register_block_style(
		'core/group',
		array(
			'name'  => 'qpop-card',
			'label' => __( 'Pop card', 'quickpop' ),
		)
	);
	register_block_style(
		'core/image',
		array(
			'name'  => 'qpop-framed',
			'label' => __( 'Framed', 'quickpop' ),
		)
	);
	register_block_style(
		'core/heading',
		array(
			'name'  => 'qpop-poster',
			'label' => __( 'Poster', 'quickpop' ),
		)
	);
	register_block_style(
		'core/button',
		array(
			'name'  => 'qpop-outline',
			'label' => __( 'Outline', 'quickpop' ),
		)
	);
}
add_action( 'init', 'quickpop_block_styles' );

/**
 * Pattern category for the theme's own patterns.
 */
function quickpop_pattern_categories() {
	register_block_pattern_category(
		'quickpop',
		array(
			'label'       => __( 'QuickPop', 'quickpop' ),
			'description' => __( 'Poster headers, colour boxes and calls to action in the QuickPop look.', 'quickpop' ),
		)
	);
}
add_action( 'init', 'quickpop_pattern_categories' );
