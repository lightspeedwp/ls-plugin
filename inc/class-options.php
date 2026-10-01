<?php
/**
 * Options Page Registration using Secure Custom Fields.
 *
 * @package LS_Plugin
 * @since   0.3.0
 * @see     https://github.com/WordPress/secure-custom-fields/blob/trunk/docs/tutorials/first-options-page.md
 */

namespace LS_Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the plugin settings page and lets admins choose which
 * plugin post types (detected from scf-json/) are enabled.
 *
 * @since 0.3.0
 */
class Options {

	/**
	 * Options page slug.
	 *
	 * @var string
	 */
	const OPTIONS_PAGE = 'ls_plugin_settings';

	/**
	 * Field group key for the settings page.
	 *
	 * @var string
	 */
	const FIELD_GROUP = 'group_ls_plugin_options';

	/**
	 * Field name storing the enabled post type slugs.
	 *
	 * @var string
	 */
	const ENABLED_POST_TYPES_FIELD = 'ls_plugin_enabled_post_types';

	/**
	 * Cached post types detected from the plugin's JSON files.
	 *
	 * @var array<string, string>|null
	 */
	private $post_types = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'acf/init', array( $this, 'register_options_page' ) );
		add_action( 'acf/init', array( $this, 'register_options_fields' ) );

		// SCF registers JSON post types on acf/init priority 6; remove disabled ones straight after.
		add_action( 'acf/init', array( $this, 'unregister_disabled_post_types' ), 7 );

		add_action( 'acf/save_post', array( $this, 'flush_rewrite_rules_on_save' ), 20 );
	}

	/**
	 * Register the settings page under the WordPress Settings menu.
	 *
	 * @return void
	 */
	public function register_options_page() {
		if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
			return;
		}

		acf_add_options_sub_page(
			array(
				'page_title'      => __( 'LightSpeed Settings', 'ls-plugin' ),
				'menu_title'      => __( 'LightSpeed', 'ls-plugin' ),
				'menu_slug'       => self::OPTIONS_PAGE,
				'parent_slug'     => 'options-general.php',
				'capability'      => 'manage_options',
				'update_button'   => __( 'Save Settings', 'ls-plugin' ),
				'updated_message' => __( 'Settings saved.', 'ls-plugin' ),
				'autoload'        => true,
			)
		);
	}

	/**
	 * Register the settings field group with a Post Types tab.
	 *
	 * @return void
	 */
	public function register_options_fields() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$post_types = $this->get_available_post_types();

		$post_types_field = array(
			'key'           => 'field_' . self::ENABLED_POST_TYPES_FIELD,
			'label'         => __( 'Post Types', 'ls-plugin' ),
			'name'          => self::ENABLED_POST_TYPES_FIELD,
			'type'          => 'checkbox',
			'choices'       => $post_types,
			'default_value' => array(),
			'layout'        => 'vertical',
			'return_format' => 'value',
			'instructions'  => __( 'Choose which plugin post types are enabled on this site.', 'ls-plugin' ),
		);

		if ( empty( $post_types ) ) {
			$post_types_field = array(
				'key'     => 'field_ls_plugin_no_post_types',
				'label'   => __( 'Post Types', 'ls-plugin' ),
				'type'    => 'message',
				'message' => __( 'No post types were found in the plugin\'s scf-json folder.', 'ls-plugin' ),
			);
		}

		acf_add_local_field_group(
			array(
				'key'             => self::FIELD_GROUP,
				'title'           => __( 'Settings', 'ls-plugin' ),
				'fields'          => array(
					// Tab: Post Types.
					array(
						'key'   => 'field_ls_plugin_tab_post_types',
						'label' => __( 'Post Types', 'ls-plugin' ),
						'type'  => 'tab',
					),
					$post_types_field,
				),
				'location'        => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => self::OPTIONS_PAGE,
						),
					),
				),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
			)
		);
	}

	/**
	 * Get post types defined in the plugin's scf-json folder.
	 *
	 * @return array<string, string> Post type slug => label.
	 */
	public function get_available_post_types() {
		if ( null !== $this->post_types ) {
			return $this->post_types;
		}

		$this->post_types = array();

		$files = glob( LS_PLUGIN_PLUGIN_DIR . 'scf-json/*.json' );

		foreach ( $files ? $files : array() as $file ) {
			$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( ! is_array( $data ) || empty( $data['key'] ) || empty( $data['post_type'] ) ) {
				continue;
			}

			if ( 0 !== strpos( $data['key'], 'post_type_' ) ) {
				continue;
			}

			$slug  = sanitize_key( $data['post_type'] );
			$label = ! empty( $data['title'] ) ? $data['title'] : $slug;

			$this->post_types[ $slug ] = $label;
		}

		asort( $this->post_types );

		return $this->post_types;
	}

	/**
	 * Get the post type slugs the admin has enabled.
	 *
	 * Reads the stored option directly so it works before SCF fields are loaded.
	 * Post types are disabled by default until enabled on the settings page.
	 *
	 * @return string[]
	 */
	public static function get_enabled_post_types() {
		$enabled = get_option( 'options_' . self::ENABLED_POST_TYPES_FIELD, array() );

		return is_array( $enabled ) ? array_map( 'sanitize_key', $enabled ) : array();
	}

	/**
	 * Check whether a plugin post type is enabled.
	 *
	 * @param string $post_type Post type slug.
	 * @return bool
	 */
	public static function is_post_type_enabled( $post_type ) {
		return in_array( $post_type, self::get_enabled_post_types(), true );
	}

	/**
	 * Unregister plugin post types that are not enabled in settings.
	 *
	 * @return void
	 */
	public function unregister_disabled_post_types() {
		$enabled = self::get_enabled_post_types();

		foreach ( array_keys( $this->get_available_post_types() ) as $post_type ) {
			if ( in_array( $post_type, $enabled, true ) || ! post_type_exists( $post_type ) ) {
				continue;
			}

			unregister_post_type( $post_type );
		}
	}

	/**
	 * Flush rewrite rules after the settings page is saved so enabled
	 * post type archives and single URLs resolve immediately.
	 *
	 * @param int|string $post_id SCF post ID being saved.
	 * @return void
	 */
	public function flush_rewrite_rules_on_save( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || 'settings_page_' . self::OPTIONS_PAGE !== $screen->id ) {
			return;
		}

		// Rules regenerate on the next request with the updated post type registrations.
		delete_option( 'rewrite_rules' );
	}
}
