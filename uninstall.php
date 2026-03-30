<?php
/**
 * Uninstall {{PLUGIN_NAME}}.
 *
 * This file runs when the plugin is deleted from the WordPress admin.
 * Add any cleanup logic here — for example, removing options or custom tables.
 *
 * By default this starter does NOT delete any data.
 * Uncomment and extend the sections below only when your plugin stores data
 * and you are confident that removal is the right behaviour.
 *
 * @package {{NAMESPACE}}
 */

// Only run during a real uninstall triggered by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Example: Remove a plugin option.
// delete_option( '{{PLUGIN_SLUG}}_settings' );

// Example: Remove all plugin options by prefix.
// global $wpdb;
// $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '{{PLUGIN_SLUG}}_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

// Example: Remove a custom database table.
// global $wpdb;
// $table_name = $wpdb->prefix . '{{PLUGIN_SLUG}}_data';
// $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
