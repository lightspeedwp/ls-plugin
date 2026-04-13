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
