<?php
/**
 * SCF Local JSON Configuration.
 *
 * @package LS_Plugin
 * @since   1.0.0
 */

namespace LS_Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages Local JSON save and load paths for SCF field groups.
 *
 * @since 1.0.0
 */
class SCF_JSON {

	/**
	 * Local JSON directory path.
	 *
	 * @var string
	 */
	private $json_path;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->json_path = LS_PLUGIN_PLUGIN_DIR . 'scf-json';

		add_filter( 'acf/settings/save_json', array( $this, 'set_save_path' ) );
		add_filter( 'acf/settings/load_json', array( $this, 'add_load_path' ) );

		add_filter( 'acf/settings/save_json/type=acf-post-type', array( $this, 'set_save_path' ) );
		add_filter( 'acf/settings/save_json/type=acf-taxonomy', array( $this, 'set_save_path' ) );
		add_filter( 'acf/json/load_paths', array( $this, 'add_post_type_load_paths' ) );
		add_filter( 'acf/json/load_paths', array( $this, 'add_taxonomy_load_paths' ) );

		$this->maybe_create_directory();
	}

	/**
	 * Set the save path for JSON exports.
	 *
	 * @param string $path Default save path.
	 * @return string
	 */
	public function set_save_path( $path ) {
		return $this->json_path;
	}

	/**
	 * Add custom load path for field groups.
	 *
	 * @param array $paths Existing load paths.
	 * @return array
	 */
	public function add_load_path( $paths ) {
		return $this->add_unique_load_path( $paths );
	}

	/**
	 * Add custom load path for post types.
	 *
	 * @param array $paths Existing load paths.
	 * @return array
	 */
	public function add_post_type_load_paths( $paths ) {
		return $this->add_unique_load_path( $paths );
	}

	/**
	 * Add custom load path for taxonomies.
	 *
	 * @param array $paths Existing load paths.
	 * @return array
	 */
	public function add_taxonomy_load_paths( $paths ) {
		return $this->add_unique_load_path( $paths );
	}

	/**
	 * Add the plugin path once to load paths.
	 *
	 * @param array $paths Existing load paths.
	 * @return array
	 */
	private function add_unique_load_path( $paths ) {
		if ( ! is_array( $paths ) ) {
			$paths = array();
		}

		if ( ! in_array( $this->json_path, $paths, true ) ) {
			$paths[] = $this->json_path;
		}

		return $paths;
	}

	/**
	 * Create JSON directory when missing.
	 *
	 * @return bool
	 */
	private function maybe_create_directory() {
		if ( ! is_dir( $this->json_path ) ) {
			return wp_mkdir_p( $this->json_path );
		}

		return true;
	}

	/**
	 * Get JSON directory path.
	 *
	 * @return string
	 */
	public function get_json_path() {
		return $this->json_path;
	}

	/**
	 * Get all JSON files in the directory.
	 *
	 * @return array
	 */
	public function get_json_files() {
		$files = glob( $this->json_path . '/*.json' );

		return $files ? $files : array();
	}

	/**
	 * Basic JSON validation for one file.
	 *
	 * @param string $file_path Path to JSON file.
	 * @return array{valid: bool, errors: array}
	 */
	public function validate_json_file( $file_path ) {
		$result = array(
			'valid'  => true,
			'errors' => array(),
		);

		if ( ! file_exists( $file_path ) ) {
			$result['valid']    = false;
			$result['errors'][] = 'File does not exist.';
			return $result;
		}

		$json_content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data         = json_decode( $json_content, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			$result['valid']    = false;
			$result['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
			return $result;
		}

		$required = array( 'key', 'title', 'fields', 'location' );
		foreach ( $required as $property ) {
			if ( ! isset( $data[ $property ] ) ) {
				$result['valid']    = false;
				$result['errors'][] = sprintf( 'Missing required property: %s', $property );
			}
		}

		if ( isset( $data['key'] ) && 0 !== strpos( $data['key'], 'group_' ) ) {
			$result['valid']    = false;
			$result['errors'][] = 'Field group key must start with "group_".';
		}

		if ( isset( $data['fields'] ) && ! is_array( $data['fields'] ) ) {
			$result['valid']    = false;
			$result['errors'][] = 'Fields must be an array.';
		}

		if ( isset( $data['fields'] ) && is_array( $data['fields'] ) ) {
			foreach ( $data['fields'] as $index => $field ) {
				$field_required = array( 'key', 'type' );
				foreach ( $field_required as $prop ) {
					if ( ! isset( $field[ $prop ] ) ) {
						$result['valid']    = false;
						$result['errors'][] = sprintf(
							'Field at index %d is missing required property: %s',
							$index,
							$prop
						);
					}
				}

				$layout_types = array( 'tab', 'accordion', 'message' );
				if ( isset( $field['key'] ) && ! in_array( $field['type'], $layout_types, true ) ) {
					if ( 0 !== strpos( $field['key'], 'field_' ) ) {
						$result['valid']    = false;
						$result['errors'][] = sprintf(
							'Field "%s" key must start with "field_".',
							isset( $field['label'] ) ? $field['label'] : $field['key']
						);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Validate all JSON files in the directory.
	 *
	 * @return array
	 */
	public function validate_all_json_files() {
		$results = array();
		$files   = $this->get_json_files();

		foreach ( $files as $file ) {
			$filename             = basename( $file );
			$results[ $filename ] = $this->validate_json_file( $file );
		}

		return $results;
	}
}
