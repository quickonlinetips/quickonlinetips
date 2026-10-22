<?php
/**
 * Runs only when the plugin is deleted from the Plugins page.
 * Removes its settings so nothing is left behind.
 *
 * @package display-wordpress-version
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'quick_wp_version' );
delete_option( 'widget_quick_wp_version' );
