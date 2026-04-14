<?php
/**
 * Plugin Name:       LightSpeed Site Plugin
 * Plugin URI:        https://lightspeedwp.agency/
 * Description:       LightSpeed Site Plugin provides custom blocks and site-specific functionality for the LightSpeed website, separate from theme responsibilities.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            LightSpeed
 * Author URI:        https://lightspeedwp.agency/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ls-plugin
 * Domain Path:       /languages
 *
 * @package LS_PLUGIN
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'LS_PLUGIN_VERSION', '0.1.0' );
define( 'LS_PLUGIN_PLUGIN_FILE', __FILE__ );
define( 'LS_PLUGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LS_PLUGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load the plugin text domain for translation.
 */
function ls_plugin_load_textdomain() {
	load_plugin_textdomain(
		'ls-plugin',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'ls_plugin_load_textdomain' );

/**
 * Load optional plugin includes.
 * Add include files to inc/ and require them here when ready.
 */
function ls_plugin_init() {
	require_once LS_PLUGIN_PLUGIN_DIR . 'inc/class-style-switcher.php';

	$style_switcher = new LS_Plugin_Style_Switcher();
	$style_switcher->register_hooks();
}
add_action( 'plugins_loaded', 'ls_plugin_init' );

/**
 * Registers the Back to Top block type.
 *
 * @return void
 */
function ls_plugin_register_blocks() {
	register_block_type( LS_PLUGIN_PLUGIN_DIR . 'build/blocks/back-to-top' );
}
add_action( 'init', 'ls_plugin_register_blocks' );

/**
 * Enqueues Button Icon editor assets.
 *
 * @return void
 */
function ls_plugin_enqueue_button_icon_editor_assets() {
	$asset_path = LS_PLUGIN_PLUGIN_DIR . 'build/js/button-icon.asset.php';

	if ( ! file_exists( $asset_path ) ) {
		return;
	}

	$asset = include $asset_path;

	wp_enqueue_script(
		'ls-plugin-button-icon',
		LS_PLUGIN_PLUGIN_URL . 'build/js/button-icon.js',
		$asset['dependencies'] ?? array(),
		$asset['version'] ?? LS_PLUGIN_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'ls_plugin_enqueue_button_icon_editor_assets' );

/**
 * Enqueues Button Icon shared styles for front end and editor.
 *
 * @return void
 */
function ls_plugin_enqueue_button_icon_styles() {
	$asset_path = LS_PLUGIN_PLUGIN_DIR . 'build/css/button-icon.asset.php';

	if ( ! file_exists( $asset_path ) ) {
		return;
	}

	$asset = include $asset_path;

	wp_enqueue_style(
		'ls-plugin-button-icon',
		LS_PLUGIN_PLUGIN_URL . 'build/css/style-button-icon.css',
		$asset['dependencies'] ?? array(),
		$asset['version'] ?? LS_PLUGIN_VERSION
	);
}
add_action( 'enqueue_block_assets', 'ls_plugin_enqueue_button_icon_styles' );
