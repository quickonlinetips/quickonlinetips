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

/**
 * Old template tag, kept so themes that already call it keep working.
 * Prints the Powered by WordPress line.
 */
function quickversion() {
	echo 'Powered by <a class="display-version" href="https://wordpress.org/">WordPress</a> ' . esc_html( get_bloginfo( 'version' ) );
}
