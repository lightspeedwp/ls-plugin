<?php
/**
 * LightSpeed Icon Collection.
 *
 * Registers the "lightspeed" SVG icon collection using the WordPress 7.1
 * icon API (wp_register_icon_collection() / wp_register_icon()), so icons
 * placed in assets/icons/lightspeed/ become available to the block editor
 * and to wp_get_icon() calls without any per-icon registration code.
 *
 * @see https://make.wordpress.org/core/2026/07/24/registering-and-rendering-svg-icons-in-wordpress-7-1/
 *
 * @package LS_Plugin
 * @since   0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LS_Plugin_Icons
 *
 * Registers the LightSpeed icon collection and its icons on `init`.
 *
 * @since 0.3.0
 */
class LS_Plugin_Icons {

	/**
	 * Icon collection slug.
	 *
	 * @var string
	 */
	const COLLECTION = 'lightspeed';

	/**
	 * Directory containing the collection's SVG icon files, one file per icon.
	 *
	 * @var string
	 */
	private $icons_dir;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->icons_dir = LS_PLUGIN_PLUGIN_DIR . 'assets/icons/lightspeed/';

		add_action( 'init', array( $this, 'register_icon_collection' ) );
	}

	/**
	 * Register the LightSpeed icon collection and its icons.
	 *
	 * Bails silently on WordPress versions before 7.1, where the icon API
	 * does not exist yet.
	 */
	public function register_icon_collection() {
		if ( ! function_exists( 'wp_register_icon_collection' ) || ! function_exists( 'wp_register_icon' ) ) {
			return;
		}

		wp_register_icon_collection(
			self::COLLECTION,
			array(
				'label'       => __( 'LightSpeed', 'ls-plugin' ),
				'description' => __( 'Icons provided by the LightSpeed Site Plugin.', 'ls-plugin' ),
			)
		);

		foreach ( $this->get_icon_files() as $name => $file_path ) {
			wp_register_icon(
				self::COLLECTION . '/' . $name,
				array(
					'label'     => $this->get_icon_label( $name ),
					'file_path' => $file_path,
				)
			);
		}
	}

	/**
	 * Find the SVG files to register, keyed by sanitized icon name.
	 *
	 * @return array<string, string> Icon name => absolute file path.
	 */
	private function get_icon_files() {
		if ( ! is_dir( $this->icons_dir ) ) {
			return array();
		}

		$files = glob( $this->icons_dir . '*.svg' );

		if ( empty( $files ) ) {
			return array();
		}

		$icons = array();

		foreach ( $files as $file_path ) {
			$name = sanitize_title( basename( $file_path, '.svg' ) );

			if ( '' !== $name ) {
				$icons[ $name ] = $file_path;
			}
		}

		return $icons;
	}

	/**
	 * Build a human-readable label from an icon's file name.
	 *
	 * @param string $name Sanitized icon name, e.g. "arrow-right".
	 * @return string Label, e.g. "Arrow Right".
	 */
	private function get_icon_label( $name ) {
		return ucwords( str_replace( '-', ' ', $name ) );
	}
}
