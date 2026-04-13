<?php
/**
 * Style Switcher.
 *
 * Provides frontend user style switching (light/dark) without page reload.
 * Reads the active theme's dark.json style variation and merges its colour
 * tokens into theme.json when dark mode is active. Preference is persisted
 * in the user's browser via localStorage and mirrored to a cookie so PHP
 * can perform server-side rendering of the correct button label.
 *
 * Strategy:
 * 1. Outputs CSS custom properties from dark.json scoped to html/body.dark-mode.
 * 2. JavaScript applies dark-mode class to <html> immediately (prevents FOUC).
 * 3. On DOMContentLoaded the class is mirrored to <body> and the button is wired.
 * 4. wp_theme_json_data_theme merges dark colour tokens when cookie is set.
 *
 * @package LS_Plugin
 * @since   1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles light/dark style switching for the active theme.
 *
 * @since 1.0.0
 */
class LS_Plugin_Style_Switcher {

	/**
	 * Registers all WordPress hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'wp_head', array( $this, 'output_dark_mode_css' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_filter( 'wp_theme_json_data_theme', array( $this, 'merge_dark_mode_theme_json' ), 200 );
	}

	/**
	 * Registers the style switcher block from the built block assets.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_block() {
		$block_path = LS_PLUGIN_PLUGIN_DIR . 'build/blocks/style-switcher';

		if ( file_exists( $block_path . '/block.json' ) ) {
			$block_type = register_block_type( $block_path );

			if ( $block_type instanceof WP_Block_Type ) {
				$this->localize_block_editor_data( $block_type );
			}
		}
	}

	/**
	 * Localizes style variation options for the block editor UI.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Block_Type $block_type Registered block type object.
	 * @return void
	 */
	private function localize_block_editor_data( $block_type ) {
		if ( empty( $block_type->editor_script_handles ) || ! is_array( $block_type->editor_script_handles ) ) {
			return;
		}

		$data = array(
			'availableStyles'       => $this->get_available_style_variations(),
			'defaultDarkStyleSlug'  => $this->get_default_dark_style_slug(),
		);

		foreach ( $block_type->editor_script_handles as $handle ) {
			wp_localize_script( $handle, 'lsPluginStyleSwitcherBlockData', $data );
		}
	}

	/**
	 * Generates CSS custom properties from the active theme's dark.json.
	 *
	 * Outputs variables scoped to `html.dark-mode, body.dark-mode` so they are
	 * available the moment the JS adds the class to <html>.
	 *
	 * @since 1.0.0
	 *
	 * @return string CSS string, or empty string if dark.json is unavailable.
	 */
	public function get_dark_mode_css() {
		$dark_data = $this->get_dark_json_data();

		$declarations = '';

		if ( isset( $dark_data['settings']['color']['palette'] ) && is_array( $dark_data['settings']['color']['palette'] ) ) {
			foreach ( $dark_data['settings']['color']['palette'] as $color ) {
				if ( ! isset( $color['slug'] ) || ! isset( $color['color'] ) ) {
					continue;
				}

				$declarations .= "\t" . '--wp--preset--color--' . sanitize_key( $color['slug'] ) . ': ' . sanitize_hex_color( $color['color'] ) . ";\n";
			}
		}

		if ( isset( $dark_data['settings']['custom'] ) && is_array( $dark_data['settings']['custom'] ) ) {
			$declarations .= $this->build_custom_css_variables( $dark_data['settings']['custom'] );
		}

		if ( empty( $declarations ) && ! isset( $dark_data['styles']['color'] ) ) {
			return '';
		}

		$css = '';

		if ( ! empty( $declarations ) ) {
			$css .= 'html.dark-mode, body.dark-mode {' . "\n";
			$css .= $declarations;
			$css .= '}' . "\n";
		}

		if ( isset( $dark_data['styles']['color'] ) ) {
			$css .= 'html.dark-mode, body.dark-mode {' . "\n";
			if ( isset( $dark_data['styles']['color']['background'] ) ) {
				$css .= "\t" . 'background-color: ' . $dark_data['styles']['color']['background'] . ";\n";
			}
			if ( isset( $dark_data['styles']['color']['text'] ) ) {
				$css .= "\t" . 'color: ' . $dark_data['styles']['color']['text'] . ";\n";
			}
			$css .= '}' . "\n";
		}

		return $css;
	}

	/**
	 * Outputs the inline dark mode CSS in wp_head (priority 1).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_dark_mode_css() {
		$dark_css = $this->get_dark_mode_css();

		if ( empty( $dark_css ) ) {
			return;
		}

		echo '<style id="ls-plugin-dark-mode" type="text/css">' . "\n";
		echo wp_kses_post( $dark_css );
		echo '</style>' . "\n";
	}

	/**
	 * Enqueues the style switcher script in <head> to prevent FOUC.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_script() {
		if ( is_admin() ) {
			return;
		}

		$script_path = 'build/js/style-switcher.js';
		$script_file = LS_PLUGIN_PLUGIN_DIR . $script_path;

		if ( ! file_exists( $script_file ) ) {
			return;
		}

		wp_enqueue_script(
			'ls-plugin-style-switcher',
			LS_PLUGIN_PLUGIN_URL . $script_path,
			array(),
			(string) filemtime( $script_file ),
			array( 'in_footer' => false ) // Must load in <head> to prevent FOUC.
		);

		wp_localize_script(
			'ls-plugin-style-switcher',
			'lsPluginStyleData',
			array(
				'localStorageKey' => 'ls_plugin_style_preference',
				'styleVariationStorageKey' => 'ls_plugin_style_variation',
				'defaultMode'    => 'light',
				'defaultDarkStyleSlug' => $this->get_default_dark_style_slug(),
				'labelLight'     => esc_html__( 'Light Mode', 'ls-plugin' ),
				'labelDark'      => esc_html__( 'Dark Mode', 'ls-plugin' ),
				'switchToText'   => esc_html__( 'Switch to %s', 'ls-plugin' ),
				'switchAriaDark' => esc_html__( 'Switch to dark mode, currently light', 'ls-plugin' ),
				'switchAriaLight'=> esc_html__( 'Switch to light mode, currently dark', 'ls-plugin' ),
			)
		);
	}

	/**
	 * Returns the HTML for the style switcher button.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Optional. Customisation arguments.
	 *
	 *     @type string $label_light Label shown when switching to light mode. Default 'Light Mode'.
	 *     @type string $label_dark  Label shown when switching to dark mode.  Default 'Dark Mode'.
	 *     @type string $class       CSS class on the button element. Default 'ls-plugin-style-switcher'.
	 * }
	 * @return string Button HTML.
	 */
	public function get_switcher_button( $args = array() ) {
		$defaults = array(
			'label_light' => esc_html__( 'Light Mode', 'ls-plugin' ),
			'label_dark'  => esc_html__( 'Dark Mode', 'ls-plugin' ),
			'class'       => 'ls-plugin-style-switcher',
		);

		$args            = wp_parse_args( $args, $defaults );
		$next_mode_label = 'light' === $this->get_style_preference() ? $args['label_dark'] : $args['label_light'];

		return sprintf(
			'<button id="ls-plugin-style-switcher" class="%s" aria-label="%s" title="%s" type="button">
				<span class="ls-plugin-style-label">%s</span>
				<svg class="ls-plugin-style-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path class="ls-plugin-style-icon-light" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" style="display:none;"></path>
					<circle class="ls-plugin-style-icon-dark" cx="12" cy="12" r="5"></circle>
					<line class="ls-plugin-style-icon-dark" x1="12" y1="1" x2="12" y2="3"></line>
					<line class="ls-plugin-style-icon-dark" x1="12" y1="21" x2="12" y2="23"></line>
					<line class="ls-plugin-style-icon-dark" x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
					<line class="ls-plugin-style-icon-dark" x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
					<line class="ls-plugin-style-icon-dark" x1="1" y1="12" x2="3" y2="12"></line>
					<line class="ls-plugin-style-icon-dark" x1="21" y1="12" x2="23" y2="12"></line>
					<line class="ls-plugin-style-icon-dark" x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
					<line class="ls-plugin-style-icon-dark" x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
				</svg>
			</button>',
			esc_attr( $args['class'] ),
			esc_attr( sprintf( esc_html__( 'Switch to %s', 'ls-plugin' ), $next_mode_label ) ),
			esc_attr( sprintf( esc_html__( 'Switch to %s', 'ls-plugin' ), $next_mode_label ) ),
			esc_html( $next_mode_label )
		);
	}

	/**
	 * Echoes the style switcher button.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Optional. See get_switcher_button().
	 * @return void
	 */
	public function display_switcher_button( $args = array() ) {
		echo $this->get_switcher_button( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Returns the user's current style preference from the request cookie.
	 *
	 * The cookie is written by the JS on each toggle and on page load.
	 *
	 * @since 1.0.0
	 *
	 * @return string 'dark' or 'light'.
	 */
	public function get_style_preference() {
		if ( isset( $_COOKIE['_ls_plugin_style'] ) ) {
			return 'dark' === sanitize_text_field( wp_unslash( $_COOKIE['_ls_plugin_style'] ) ) ? 'dark' : 'light';
		}

		return 'light';
	}

	/**
	 * Merges dark colour tokens from dark.json into theme.json when dark mode is active.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Theme_JSON_Data $theme_json Theme JSON data object.
	 * @return WP_Theme_JSON_Data Modified theme JSON data object.
	 */
	public function merge_dark_mode_theme_json( $theme_json ) {
		if ( 'dark' !== $this->get_style_preference() ) {
			return $theme_json;
		}

		$dark_data = $this->get_dark_json_data();

		if ( ! is_array( $dark_data ) ) {
			return $theme_json;
		}

		$overrides = array();

		if ( isset( $dark_data['settings']['custom'] ) && is_array( $dark_data['settings']['custom'] ) ) {
			$overrides['settings']['custom'] = $dark_data['settings']['custom'];
		}

		if ( isset( $dark_data['settings']['color'] ) && is_array( $dark_data['settings']['color'] ) ) {
			$overrides['settings']['color'] = $dark_data['settings']['color'];
		}

		if ( isset( $dark_data['styles']['color'] ) && is_array( $dark_data['styles']['color'] ) ) {
			$overrides['styles']['color'] = $dark_data['styles']['color'];
		}

		if ( empty( $overrides ) ) {
			return $theme_json;
		}

		$theme_json->update_with( $overrides );

		return $theme_json;
	}

	/**
	 * Reads and decodes the active theme's dark.json style variation.
	 *
	 * @since 1.0.0
	 *
	 * @return array Decoded data, or empty array on failure.
	 */
	private function get_dark_json_data() {
		$selected_slug = $this->get_selected_style_variation_slug();
		$path          = $this->get_style_variation_file_path( $selected_slug );

		if ( ! file_exists( $path ) ) {
			return array();
		}

		$data = json_decode( file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Returns available theme style variations for the block dropdown.
	 *
	 * Excludes the default style variation (`default.json`) so only custom
	 * alternatives are selectable as the dark-style target.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, string>> SelectControl options.
	 */
	private function get_available_style_variations() {
		$styles_dir = trailingslashit( get_stylesheet_directory() ) . 'styles/';
		$files      = glob( $styles_dir . '*.json' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$options    = array();

		if ( empty( $files ) || ! is_array( $files ) ) {
			return $options;
		}

		foreach ( $files as $file ) {
			$slug = sanitize_key( basename( $file, '.json' ) );

			if ( 'default' === $slug || empty( $slug ) ) {
				continue;
			}

			$label = ucwords( str_replace( '-', ' ', $slug ) );
			$data  = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( is_array( $data ) && ! empty( $data['title'] ) && is_string( $data['title'] ) ) {
				$label = $data['title'];
			}

			$options[] = array(
				'label' => $label,
				'value' => $slug,
			);
		}

		usort(
			$options,
			static function ( $left, $right ) {
				return strcasecmp( $left['label'], $right['label'] );
			}
		);

		return $options;
	}

	/**
	 * Returns the configured default dark-style slug.
	 *
	 * Prefers `dark` when available, otherwise falls back to the first
	 * available style variation.
	 *
	 * @since 1.0.0
	 *
	 * @return string Style variation slug.
	 */
	private function get_default_dark_style_slug() {
		$options = $this->get_available_style_variations();

		foreach ( $options as $option ) {
			if ( isset( $option['value'] ) && 'dark' === $option['value'] ) {
				return 'dark';
			}
		}

		if ( ! empty( $options[0]['value'] ) ) {
			return sanitize_key( $options[0]['value'] );
		}

		return 'dark';
	}

	/**
	 * Gets the selected style variation from the request cookie.
	 *
	 * @since 1.0.0
	 *
	 * @return string Selected style variation slug.
	 */
	private function get_selected_style_variation_slug() {
		if ( ! isset( $_COOKIE['_ls_plugin_style_variation'] ) ) {
			return $this->get_default_dark_style_slug();
		}

		$slug = sanitize_key( sanitize_text_field( wp_unslash( $_COOKIE['_ls_plugin_style_variation'] ) ) );

		return ! empty( $slug ) ? $slug : $this->get_default_dark_style_slug();
	}

	/**
	 * Builds the file path for a style variation JSON file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Style variation slug.
	 * @return string Absolute JSON file path.
	 */
	private function get_style_variation_file_path( $slug ) {
		$sanitized_slug = sanitize_key( (string) $slug );

		if ( empty( $sanitized_slug ) ) {
			$sanitized_slug = $this->get_default_dark_style_slug();
		}

		$path = trailingslashit( get_stylesheet_directory() ) . 'styles/' . $sanitized_slug . '.json';

		if ( file_exists( $path ) ) {
			return $path;
		}

		return trailingslashit( get_stylesheet_directory() ) . 'styles/' . $this->get_default_dark_style_slug() . '.json';
	}

	/**
	 * Converts theme.json `settings.custom` values into CSS variable declarations.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $custom_data Custom token data.
	 * @param string $prefix      Variable name prefix.
	 * @return string CSS declarations for custom variables.
	 */
	private function build_custom_css_variables( $custom_data, $prefix = '--wp--custom' ) {
		$declarations = '';

		foreach ( $custom_data as $key => $value ) {
			$sanitized_key = sanitize_key( (string) $key );

			if ( is_array( $value ) ) {
				$declarations .= $this->build_custom_css_variables( $value, $prefix . '--' . $sanitized_key );
				continue;
			}

			if ( is_scalar( $value ) && '' !== (string) $value ) {
				$declarations .= "\t" . $prefix . '--' . $sanitized_key . ': ' . (string) $value . ";\n";
			}
		}

		return $declarations;
	}
}
