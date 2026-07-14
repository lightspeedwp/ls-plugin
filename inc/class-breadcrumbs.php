<?php
/**
 * Breadcrumbs Block
 *
 * Registers the Breadcrumbs dynamic block.
 *
 * @package LS_Plugin
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LS_Plugin_Breadcrumbs
 *
 * Handles registration for the Breadcrumbs block.
 */
class LS_Plugin_Breadcrumbs {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the breadcrumbs block from the built block assets.
	 */
	public function register_block() {
		$block_path = LS_PLUGIN_PLUGIN_DIR . 'build/blocks/breadcrumbs';

		if ( file_exists( $block_path . '/block.json' ) ) {
			register_block_type( $block_path );
		}
	}
}
