<?php
/**
 * Plugin Name:       Quick WordPress Version
 * Plugin URI:        https://www.quickonlinetips.com/archives/display-wordpress-version-plugin/
 * Description:       A simple "Powered by WordPress" line for your site, with or without the version number. Shows your installed and latest WordPress version in Settings.
 * Version:           1.4
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
 * The saved settings, with defaults for anything not saved yet.
 *
 * The default is Powered by WordPress with no number: showing the exact
 * version tells everyone, including attackers, what your site runs.
 *
 * @return array{show: string, custom: string, link: bool, extra: string}
 */
function quick_wp_version_settings() {
	$saved = get_option( QUICK_WP_VERSION_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();

	return array(
		'show'   => isset( $saved['show'] ) && in_array( $saved['show'], quick_wp_version_show_choices(), true ) ? $saved['show'] : 'plain',
		'custom' => isset( $saved['custom'] ) ? (string) $saved['custom'] : '',
		'link'   => isset( $saved['link'] ) ? (bool) $saved['link'] : true,
		'extra'  => isset( $saved['extra'] ) ? (string) $saved['extra'] : '',
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
		'extra'  => isset( $input['extra'] ) ? trim( wp_kses_post( wp_unslash( $input['extra'] ) ) ) : '',
	);
}

/* ------------------------------------------------------------------ *
 * The line itself
 * ------------------------------------------------------------------ */

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

	switch ( $settings['show'] ) {
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

	// Anything else the site owner wants on the same line.
	if ( '' !== $settings['extra'] ) {
		$line .= ' ' . wp_kses_post( $settings['extra'] );
	}

	return (string) apply_filters( 'quick_wp_version_line', $line, $settings );
}

/**
 * Old template tag, kept so themes that already call it keep working.
 * Prints the line, using the choices in Settings > Quick WordPress Version.
 */
function quickversion() {
	echo quick_wp_version_line(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
}
