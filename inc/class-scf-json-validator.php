<?php
/**
 * SCF JSON Validator.
 *
 * @package LS_Plugin
 * @since   1.0.0
 */

namespace LS_Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates SCF field-group JSON files.
 *
 * @since 1.0.0
 */
class SCF_JSON_Validator {

	/**
	 * Path to JSON schema file.
	 *
	 * @var string
	 */
	private $schema_path;

	/**
	 * Decoded schema cache.
	 *
	 * @var array|null
	 */
	private $schema;

	/**
	 * SCF JSON handler instance.
	 *
	 * @var SCF_JSON
	 */
	private $scf_json;

	/**
	 * Valid SCF field types.
	 *
	 * @var array
	 */
	private $valid_field_types = array(
		'tab', 'accordion', 'message',
		'text', 'textarea', 'email', 'url', 'password', 'number',
		'wysiwyg', 'oembed',
		'image', 'file', 'gallery',
		'select', 'checkbox', 'radio', 'button_group', 'true_false',
		'link', 'post_object', 'page_link', 'relationship', 'taxonomy', 'user',
		'date_picker', 'date_time_picker', 'time_picker', 'color_picker',
		'group', 'repeater', 'flexible_content',
	);

	/**
	 * Valid location rule params.
	 *
	 * @var array
	 */
	private $valid_location_params = array(
		'post_type', 'post_format', 'post_status', 'post_taxonomy',
		'user_role', 'user_form',
		'taxonomy',
		'attachment_type',
		'page_type',
		'page_parent',
		'page_template',
		'option_page',
		'widget',
	);

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->schema_path = LS_PLUGIN_PLUGIN_DIR . '.github/schemas/scf-field-group.schema.json';

		if ( class_exists( __NAMESPACE__ . '\SCF_JSON' ) ) {
			$this->scf_json = new SCF_JSON();
		}

		$this->load_schema();
	}

	/**
	 * Load JSON schema.
	 *
	 * @return bool
	 */
	private function load_schema() {
		if ( ! file_exists( $this->schema_path ) ) {
			return false;
		}

		$schema_json  = file_get_contents( $this->schema_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->schema = json_decode( $schema_json, true );

		return null !== $this->schema;
	}

	/**
	 * Validate one field-group JSON file.
	 *
	 * @param string $file_path Path to JSON file.
	 * @return array{valid: bool, errors: array, warnings: array}
	 */
	public function validate( $file_path ) {
		$result = array(
			'valid'    => true,
			'errors'   => array(),
			'warnings' => array(),
		);

		if ( ! file_exists( $file_path ) ) {
			$result['valid']    = false;
			$result['errors'][] = 'File does not exist: ' . $file_path;
			return $result;
		}

		$json_content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$field_group  = json_decode( $json_content, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			$result['valid']    = false;
			$result['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
			return $result;
		}

		$this->validate_root_structure( $field_group, $result );

		if ( ! empty( $result['errors'] ) ) {
			$result['valid'] = false;
			return $result;
		}

		$this->validate_field_group_properties( $field_group, $result );
		$this->validate_fields( $field_group, $result );
		$this->validate_location_rules( $field_group, $result );
		$this->validate_display_properties( $field_group, $result );

		$result['valid'] = empty( $result['errors'] );

		return $result;
	}

	/**
	 * Validate top-level required structure.
	 *
	 * @param array $field_group Field-group data.
	 * @param array $result      Validation result (by reference).
	 * @return void
	 */
	private function validate_root_structure( $field_group, &$result ) {
		$required = array( 'key', 'title', 'fields', 'location' );

		foreach ( $required as $property ) {
			if ( ! isset( $field_group[ $property ] ) ) {
				$result['errors'][] = sprintf( 'Missing required property: %s', $property );
			}
		}

		if ( isset( $field_group['key'] ) ) {
			if ( ! is_string( $field_group['key'] ) ) {
				$result['errors'][] = 'Field group key must be a string';
			} elseif ( 0 !== strpos( $field_group['key'], 'group_' ) ) {
				$result['errors'][] = 'Field group key must start with "group_"';
			}
		}

		if ( isset( $field_group['title'] ) && ! is_string( $field_group['title'] ) ) {
			$result['errors'][] = 'Field group title must be a string';
		}

		if ( isset( $field_group['fields'] ) && ! is_array( $field_group['fields'] ) ) {
			$result['errors'][] = 'Fields must be an array';
		}

		if ( isset( $field_group['location'] ) && ! is_array( $field_group['location'] ) ) {
			$result['errors'][] = 'Location must be an array';
		}
	}

	/**
	 * Validate field-group display properties.
	 *
	 * @param array $field_group Field-group data.
	 * @param array $result      Validation result (by reference).
	 * @return void
	 */
	private function validate_field_group_properties( $field_group, &$result ) {
		$valid_positions = array( 'acf_after_title', 'normal', 'side' );
		if ( isset( $field_group['position'] ) && ! in_array( $field_group['position'], $valid_positions, true ) ) {
			$result['errors'][] = sprintf(
				'Invalid position "%s". Must be one of: %s',
				$field_group['position'],
				implode( ', ', $valid_positions )
			);
		}

		$valid_styles = array( 'default', 'seamless' );
		if ( isset( $field_group['style'] ) && ! in_array( $field_group['style'], $valid_styles, true ) ) {
			$result['warnings'][] = sprintf(
				'Invalid style "%s". Should be one of: %s',
				$field_group['style'],
				implode( ', ', $valid_styles )
			);
		}

		$valid_label_placements = array( 'top', 'left' );
		if ( isset( $field_group['label_placement'] ) && ! in_array( $field_group['label_placement'], $valid_label_placements, true ) ) {
			$result['warnings'][] = 'Invalid label_placement value';
		}

		$valid_instruction_placements = array( 'label', 'field' );
		if ( isset( $field_group['instruction_placement'] ) && ! in_array( $field_group['instruction_placement'], $valid_instruction_placements, true ) ) {
			$result['warnings'][] = 'Invalid instruction_placement value';
		}
	}

	/**
	 * Validate all fields.
	 *
	 * @param array $field_group Field-group data.
	 * @param array $result      Validation result (by reference).
	 * @return void
	 */
	private function validate_fields( $field_group, &$result ) {
		if ( ! isset( $field_group['fields'] ) || ! is_array( $field_group['fields'] ) ) {
			return;
		}

		if ( empty( $field_group['fields'] ) ) {
			$result['warnings'][] = 'Field group has no fields defined';
			return;
		}

		foreach ( $field_group['fields'] as $index => $field ) {
			$this->validate_field( $field, $index, $result );
		}
	}

	/**
	 * Validate an individual field.
	 *
	 * @param array $field  Field config.
	 * @param int   $index  Field index.
	 * @param array $result Validation result (by reference).
	 * @return void
	 */
	private function validate_field( $field, $index, &$result ) {
		if ( ! isset( $field['key'] ) ) {
			$result['errors'][] = sprintf( 'Field at index %d missing required property: key', $index );
			return;
		}

		if ( ! isset( $field['type'] ) ) {
			$result['errors'][] = sprintf( 'Field "%s" missing required property: type', $field['key'] );
			return;
		}

		if ( ! in_array( $field['type'], $this->valid_field_types, true ) ) {
			$result['errors'][] = sprintf(
				'Invalid field type "%s" in field "%s"',
				$field['type'],
				$field['key']
			);
		}

		$layout_types = array( 'tab', 'accordion', 'message' );
		if ( ! in_array( $field['type'], $layout_types, true ) ) {
			if ( 0 !== strpos( $field['key'], 'field_' ) ) {
				$result['errors'][] = sprintf(
					'Field key "%s" must start with "field_"',
					$field['key']
				);
			}

			if ( ! isset( $field['name'] ) || empty( $field['name'] ) ) {
				$result['warnings'][] = sprintf(
					'Field "%s" should have a name for database storage',
					$field['key']
				);
			}
		}

		$container_types = array( 'group', 'repeater', 'flexible_content' );
		if ( in_array( $field['type'], $container_types, true ) ) {
			if ( ! isset( $field['sub_fields'] ) || ! is_array( $field['sub_fields'] ) ) {
				$result['errors'][] = sprintf(
					'Field "%s" of type "%s" must have sub_fields array',
					$field['key'],
					$field['type']
				);
			}
		}

		if ( 'flexible_content' === $field['type'] ) {
			if ( ! isset( $field['layouts'] ) || ! is_array( $field['layouts'] ) ) {
				$result['errors'][] = sprintf(
					'Flexible content field "%s" must have layouts array',
					$field['key']
				);
			}
		}
	}

	/**
	 * Validate location rule groups.
	 *
	 * @param array $field_group Field-group data.
	 * @param array $result      Validation result (by reference).
	 * @return void
	 */
	private function validate_location_rules( $field_group, &$result ) {
		if ( ! isset( $field_group['location'] ) || ! is_array( $field_group['location'] ) ) {
			return;
		}

		if ( empty( $field_group['location'] ) ) {
			$result['warnings'][] = 'Field group has no location rules defined';
			return;
		}

		foreach ( $field_group['location'] as $group_index => $group ) {
			if ( ! is_array( $group ) ) {
				$result['errors'][] = sprintf( 'Location group %d must be an array', $group_index );
				continue;
			}

			foreach ( $group as $rule_index => $rule ) {
				$this->validate_location_rule( $rule, $group_index, $rule_index, $result );
			}
		}
	}

	/**
	 * Validate one location rule.
	 *
	 * @param array $rule        Rule config.
	 * @param int   $group_index Group index.
	 * @param int   $rule_index  Rule index.
	 * @param array $result      Validation result (by reference).
	 * @return void
	 */
	private function validate_location_rule( $rule, $group_index, $rule_index, &$result ) {
		if ( ! isset( $rule['param'] ) ) {
			$result['errors'][] = sprintf( 'Location rule [%d][%d] missing param', $group_index, $rule_index );
			return;
		}

		if ( ! in_array( $rule['param'], $this->valid_location_params, true ) ) {
			$result['warnings'][] = sprintf(
				'Location param "%s" may not be valid in [%d][%d]',
				$rule['param'],
				$group_index,
				$rule_index
			);
		}

		if ( ! isset( $rule['operator'] ) ) {
			$result['errors'][] = sprintf( 'Location rule [%d][%d] missing operator', $group_index, $rule_index );
		}

		if ( ! isset( $rule['value'] ) ) {
			$result['errors'][] = sprintf( 'Location rule [%d][%d] missing value', $group_index, $rule_index );
		}
	}

	/**
	 * Validate selected display properties.
	 *
	 * @param array $field_group Field-group data.
	 * @param array $result      Validation result (by reference).
	 * @return void
	 */
	private function validate_display_properties( $field_group, &$result ) {
		if ( isset( $field_group['hide_on_screen'] ) && is_array( $field_group['hide_on_screen'] ) ) {
			$valid_hide_values = array(
				'permalink', 'the_content', 'excerpt', 'discussion', 'comments',
				'revisions', 'slug', 'author', 'format', 'page_attributes',
				'featured_image', 'categories', 'tags', 'send-trackbacks',
			);

			foreach ( $field_group['hide_on_screen'] as $hide_value ) {
				if ( ! in_array( $hide_value, $valid_hide_values, true ) ) {
					$result['warnings'][] = sprintf( 'Unknown hide_on_screen value: %s', $hide_value );
				}
			}
		}
	}

	/**
	 * Validate all JSON files from plugin SCF directory.
	 *
	 * @return array
	 */
	public function validate_all_files() {
		$results = array();

		if ( ! $this->scf_json ) {
			return $results;
		}

		$files = $this->scf_json->get_json_files();

		foreach ( $files as $file_path ) {
			$filename               = basename( $file_path );
			$results[ $filename ]   = $this->validate( $file_path );
		}

		return $results;
	}

	/**
	 * Get loaded schema.
	 *
	 * @return array|null
	 */
	public function get_schema() {
		return $this->schema;
	}

	/**
	 * Whether schema file loaded successfully.
	 *
	 * @return bool
	 */
	public function has_schema() {
		return ! empty( $this->schema );
	}
}
