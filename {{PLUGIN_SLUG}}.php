<?php
/**
 * Plugin Name:       {{PLUGIN_NAME}}
 * Plugin URI:        {{PLUGIN_URI}}
 * Description:       {{PLUGIN_DESCRIPTION}}
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            {{AUTHOR_NAME}}
 * Author URI:        {{AUTHOR_URI}}
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       {{TEXT_DOMAIN}}
 * Domain Path:       /languages
 *
 * @package {{NAMESPACE}}
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

// Plugin constants.
define( '{{NAMESPACE}}_VERSION', '0.1.0' );
define( '{{NAMESPACE}}_PLUGIN_FILE', __FILE__ );
define( '{{NAMESPACE}}_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( '{{NAMESPACE}}_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load the plugin text domain for translation.
 */
function {{PLUGIN_SLUG}}_load_textdomain() {
load_plugin_textdomain(
'{{TEXT_DOMAIN}}',
false,
dirname( plugin_basename( __FILE__ ) ) . '/languages'
);
}
add_action( 'init', '{{PLUGIN_SLUG}}_load_textdomain' );

/**
 * Load optional plugin includes.
 * Add your include files in inc/ and require them here when ready.
 */
function {{PLUGIN_SLUG}}_init() {
// Example: require_once {{NAMESPACE}}_PLUGIN_DIR . 'inc/class-{{PLUGIN_SLUG}}-example.php';
}
add_action( 'plugins_loaded', '{{PLUGIN_SLUG}}_init' );
