<?php
/**
 * Portfolio Taxonomy Slug Migration.
 *
 * Realigns the ls_plugin_portfolio CPT and its taxonomies with the machine
 * names already used on the live site, so existing posts and term
 * assignments created under the old names aren't orphaned by the rename.
 *
 * @package LS_Plugin
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LS_Plugin_Portfolio_Taxonomy_Migration
 *
 * Idempotently repoints existing wp_posts/wp_term_taxonomy rows from the old
 * ls_plugin_portfolio_* machine names onto the live-matching ones.
 *
 * @since 0.2.0
 */
class LS_Plugin_Portfolio_Taxonomy_Migration {

	/**
	 * Bump this whenever the mapping below changes, so the migration runs
	 * again and picks up the new mapping.
	 *
	 * @var string
	 */
	const MIGRATION_VERSION = '1';

	/**
	 * Option name used to track which migration version has already run.
	 *
	 * @var string
	 */
	const MIGRATED_OPTION = 'ls_plugin_portfolio_taxonomy_migrated_version';

	/**
	 * Old taxonomy name => new (live-matching) taxonomy name.
	 *
	 * Both 'ls_plugin_portfolio_industry' and 'ls_plugin_portfolio_software'
	 * collapse into the single merged 'project-group' taxonomy.
	 *
	 * @var array<string, string>
	 */
	private $taxonomy_map = array(
		'ls_plugin_portfolio_project_type' => 'project-type',
		'ls_plugin_portfolio_service'      => 'project-tag',
		'ls_plugin_portfolio_industry'     => 'project-group',
		'ls_plugin_portfolio_software'     => 'project-group',
	);

	/**
	 * Old post type name => new (live-matching) post type name.
	 *
	 * @var string
	 */
	private $old_post_type = 'ls_plugin_portfolio';

	/**
	 * New (live-matching) post type name.
	 *
	 * @var string
	 */
	private $new_post_type = 'project';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'maybe_migrate' ), 15 );
	}

	/**
	 * Run the migration once per bumped MIGRATION_VERSION.
	 */
	public function maybe_migrate() {
		if ( get_option( self::MIGRATED_OPTION ) === self::MIGRATION_VERSION ) {
			return;
		}

		global $wpdb;

		// Move existing Portfolio posts onto the live-matching post type.
		$wpdb->update(
			$wpdb->posts,
			array( 'post_type' => $this->new_post_type ),
			array( 'post_type' => $this->old_post_type )
		);

		// Move existing term assignments onto the live-matching taxonomy
		// names (this preserves wp_term_relationships untouched, since those
		// reference term_taxonomy_id, not the taxonomy name string).
		foreach ( $this->taxonomy_map as $old_taxonomy => $new_taxonomy ) {
			$wpdb->update(
				$wpdb->term_taxonomy,
				array( 'taxonomy' => $new_taxonomy ),
				array( 'taxonomy' => $old_taxonomy )
			);
		}

		foreach ( array_unique( array_values( $this->taxonomy_map ) ) as $new_taxonomy ) {
			clean_taxonomy_cache( $new_taxonomy );
		}

		update_option( self::MIGRATED_OPTION, self::MIGRATION_VERSION );
	}
}
