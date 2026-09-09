<?php

/**
 * LS Plugin - SCF Permalinks Manager
 *
 * @package   ls_plugin
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      https://lightspeedwp.agency/
 * @copyright 2026 LightSpeed
 */

namespace LS_Plugin;

/**
 * Class Permalinks
 *
 * Manages custom permalink slugs for Secure Custom Fields (SCF) post types and taxonomies.
 * Allows admins to customize the base slug for portfolio post types and taxonomies from the
 * WordPress Permalinks settings page.
 *
 * @package ls_plugin
 */
class Permalinks {

	/**
	 * Holds the default for the permalinks.
	 *
	 * @var array
	 */
	public $defaults = array(
		'portfolio'              => 'portfolio',
		'portfolio-industry'     => 'portfolio-industry',
		'portfolio-service'      => 'portfolio-service',
	);

	/**
	 * Bump this whenever a change to the Portfolio post type/taxonomy
	 * registration (slugs, machine names) requires rewrite rules to be
	 * regenerated, so environments with stale cached rules self-heal
	 * without a manual permalink resave.
	 *
	 * @var string
	 */
	const REWRITE_FLUSH_VERSION = '1';

	/**
	 * Option name used to track which rewrite-flush version has already run.
	 *
	 * @var string
	 */
	const REWRITE_FLUSHED_OPTION = 'ls_plugin_portfolio_rewrite_flushed_version';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_permalink_settings' ) );
		add_action( 'admin_init', array( $this, 'save_custom_permalink_fields' ), 20 );
		add_filter( 'acf/post_type/registration_args', array( $this, 'apply_post_type_slugs' ), 10, 2 );
		add_filter( 'acf/taxonomy/registration_args', array( $this, 'apply_taxonomy_slugs' ), 10, 2 );
		// Priority 30: after SCF/ACF has registered the post type and taxonomies on 'init'.
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 30 );
	}

	/**
	 * Flush rewrite rules once per REWRITE_FLUSH_VERSION.
	 *
	 * Restoring the `project` post type/taxonomy registration (LS-3725) does not
	 * itself refresh rewrite rules that were already persisted under a previous,
	 * reverted registration (e.g. `ls_plugin_portfolio`) — WordPress caches rewrite
	 * rules until something explicitly flushes them. This lets any environment that
	 * cached the reverted registration recover automatically, without requiring a
	 * manual Permalinks resave.
	 */
	public function maybe_flush_rewrite_rules() {
		if ( get_option( self::REWRITE_FLUSHED_OPTION ) === self::REWRITE_FLUSH_VERSION ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( self::REWRITE_FLUSHED_OPTION, self::REWRITE_FLUSH_VERSION );
	}

	/**
	 * Register the setting to save custom fields.
	 */
	public function register_permalink_settings() {
		register_setting(
			'permalink',
			'ls_plugin_scf_slugs',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_permalink_fields' ),
				'default'           => $this->defaults,
			)
		);

		add_settings_section(
			'ls_plugin_scf_permalink_section',
			'',
			array( $this, 'permalink_fields' ),
			'permalink'
		);
	}

	/**
	 * Sanitize the custom permalink fields before saving.
	 *
	 * @param array $input Raw input from the form.
	 * @return array Sanitized input.
	 */
	public function sanitize_permalink_fields( $input ) {
		$sanitized = array();

		foreach ( $this->defaults as $key => $default ) {
			$field_key                       = 'ls_plugin_scf_' . $key;
			$sanitized[ $field_key ] = isset( $input[ $field_key ] ) ? sanitize_title( $input[ $field_key ] ) : '';
		}

		return $sanitized;
	}

	/**
	 * Register new fields to the permalink settings page.
	 */
	public function permalink_fields() {
		// Get existing options or defaults.
		$options = get_option( 'ls_plugin_scf_slugs', $this->defaults );

		$post_type_fields = array(
			'portfolio' => array(
				'label'       => esc_html__( 'Portfolio', 'ls-plugin' ),
				'description' => esc_html__( 'Single Portfolio Archive', 'ls-plugin' ),
			),
		);

		$taxonomy_fields = array(
			'portfolio-industry' => array(
				'label'       => esc_html__( 'Industry', 'ls-plugin' ),
				'description' => esc_html__( 'Portfolio Industry Taxonomy', 'ls-plugin' ),
			),
			'portfolio-service'  => array(
				'label'       => esc_html__( 'Service', 'ls-plugin' ),
				'description' => esc_html__( 'Portfolio Service Taxonomy', 'ls-plugin' ),
			),
		);
		?>
		<h2><?php esc_html_e( 'LS Plugin - SCF Permalinks', 'ls-plugin' ); ?></h2>
		<p><?php esc_html_e( 'Use the following fields to customize the base slug for your portfolio post types and taxonomies.', 'ls-plugin' ); ?></p>

		<h3><?php esc_html_e( 'Post Types', 'ls-plugin' ); ?></h3>
		<table class="form-table">
			<?php foreach ( $post_type_fields as $key => $field ) { ?>
				<?php
				$field_key = 'ls_plugin_scf_' . $key;
				$value     = isset( $options[ $field_key ] ) ? $options[ $field_key ] : $this->defaults[ $key ];
				?>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $field['label'] ); ?>
						</label>
					</th>
					<td>
						<input 
							type="text" 
							id="<?php echo esc_attr( $key ); ?>" 
							name="ls_plugin_scf_slugs[<?php echo esc_attr( $field_key ); ?>]" 
							value="<?php echo esc_attr( $value ); ?>" 
							class="regular-text" 
						/>
						<p class="description">
							<?php
							// translators: %s is the home URL with example slug.
							printf(
								esc_html__( 'Example: %s/%s/', 'ls-plugin' ),
								esc_html( home_url() ),
								esc_html( $value )
							);
							?>
						</p>
					</td>
				</tr>
			<?php } ?>
		</table>

		<h3><?php esc_html_e( 'Taxonomies', 'ls-plugin' ); ?></h3>
		<table class="form-table">
			<?php foreach ( $taxonomy_fields as $key => $field ) { ?>
				<?php
				$field_key = 'ls_plugin_scf_' . $key;
				$value     = isset( $options[ $field_key ] ) ? $options[ $field_key ] : $this->defaults[ $key ];
				?>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $field['label'] ); ?>
						</label>
					</th>
					<td>
						<input 
							type="text" 
							id="<?php echo esc_attr( $key ); ?>" 
							name="ls_plugin_scf_slugs[<?php echo esc_attr( $field_key ); ?>]" 
							value="<?php echo esc_attr( $value ); ?>" 
							class="regular-text" 
						/>
						<p class="description">
							<?php
							// translators: %s is the home URL with example slug.
							printf(
								esc_html__( 'Example: %s/%s/example-term/', 'ls-plugin' ),
								esc_html( home_url() ),
								esc_html( $value )
							);
							?>
						</p>
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php
	}

	/**
	 * Manually save the fields on permalink save.
	 *
	 * @return void
	 */
	public function save_custom_permalink_fields() {
		if (
			isset( $_POST['ls_plugin_scf_slugs'] ) &&
			is_array( $_POST['ls_plugin_scf_slugs'] ) &&
			current_user_can( 'manage_options' )
		) {
			check_admin_referer( 'update-permalink' ); // default nonce for permalink page

			$input     = wp_unslash( $_POST['ls_plugin_scf_slugs'] ); // phpcs:ignore WordPress.Security.ValidatedInput.InputNotSanitized
			$sanitized = $this->sanitize_permalink_fields( $input );
			update_option( 'ls_plugin_scf_slugs', $sanitized );
		}
	}

	/**
	 * Apply custom post type slugs from saved options.
	 *
	 * Filters SCF post type registration arguments to apply custom slugs.
	 * This hook is called by ACF/SCF before register_post_type().
	 *
	 * @param array $args Post type registration arguments.
	 * @param array $post SCF post type configuration.
	 * @return array Modified post type registration arguments.
	 */
	public function apply_post_type_slugs( $args, $post ) {
		$slug_options = get_option( 'ls_plugin_scf_slugs', $this->defaults );

		// Check if this is a post type we manage.
		$post_type_slug = $post['post_type'] ?? '';
		$has_archive    = $args['has_archive'] ?? false;

		if ( ! $has_archive ) {
			return $args;
		}

		if ( 'project' === $post_type_slug ) {
			$field_key   = 'ls_plugin_scf_portfolio';
			$custom_slug = isset( $slug_options[ $field_key ] ) ? $slug_options[ $field_key ] : '';

			if ( '' !== $custom_slug ) {
				$args['rewrite']         = $args['rewrite'] ?? array();
				$args['rewrite']['slug'] = $custom_slug;
				$args['has_archive']     = $custom_slug;
			}
		}

		return $args;
	}

	/**
	 * Apply custom taxonomy slugs from saved options.
	 *
	 * Filters SCF taxonomy registration arguments to apply custom slugs.
	 * This hook is called by ACF/SCF before register_taxonomy().
	 *
	 * @param array $args Taxonomy registration arguments.
	 * @param array $post SCF taxonomy configuration.
	 * @return array Modified taxonomy registration arguments.
	 */
	public function apply_taxonomy_slugs( $args, $post ) {
		$slug_options = get_option( 'ls_plugin_scf_slugs', $this->defaults );

		$taxonomy_slug = $post['taxonomy'] ?? '';

		// Map taxonomy slugs to custom configuration keys.
		$taxonomy_mapping = array(
			'project-group' => 'portfolio-industry',
			'project-tag'   => 'portfolio-service',
		);

		if ( ! isset( $taxonomy_mapping[ $taxonomy_slug ] ) ) {
			return $args;
		}

		$config_key  = $taxonomy_mapping[ $taxonomy_slug ];
		$field_key   = 'ls_plugin_scf_' . $config_key;
		$custom_slug = isset( $slug_options[ $field_key ] ) ? $slug_options[ $field_key ] : '';

		if ( '' !== $custom_slug ) {
			$args['rewrite']         = $args['rewrite'] ?? array();
			$args['rewrite']['slug'] = $custom_slug;
		}

		return $args;
	}
}
