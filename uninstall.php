<?php
/**
 * Uninstall LightSpeed Site Plugin.
 *
 * This file runs when the plugin is deleted from the WordPress admin.
 * Add any cleanup logic here — for example, removing options or custom tables.
 *
 * By default this plugin does NOT delete any data on uninstall.
 * Uncomment and extend the sections below only when the plugin stores data
 * and removal is the correct behaviour.
 *
 * @package LS_PLUGIN
 */

// Only run during a real uninstall triggered by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Example: Remove a plugin option.
// delete_option( 'ls_plugin_settings' );

// Example: Remove all plugin options by prefix.
// global $wpdb;
// $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ls_plugin_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

// Example: Remove a custom database table.
// global $wpdb;
// $table_name = $wpdb->prefix . 'ls_plugin_data';
// $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
