<?php
/**
 * Portfolio Taxonomy Default Terms.
 *
 * Seeds the default term set for the Portfolio taxonomies so that installing
 * the plugin on a new site reproduces the approved taxonomy structure,
 * instead of relying on terms being recreated by hand in wp-admin.
 *
 * @package LS_Plugin
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LS_Plugin_Portfolio_Terms
 *
 * Idempotently creates default terms for the Portfolio taxonomies.
 *
 * @since 0.2.0
 */
class LS_Plugin_Portfolio_Terms {

	/**
	 * Bump this whenever the default term list below changes, so the
	 * seeder runs again and picks up only the newly added terms.
	 *
	 * @var string
	 */
	const SEED_VERSION = '2';

	/**
	 * Option name used to track which seed version has already run.
	 *
	 * @var string
	 */
	const SEEDED_OPTION = 'ls_plugin_portfolio_terms_seeded_version';

	/**
	 * Default terms, keyed by taxonomy.
	 *
	 * @var array<string, string[]>
	 */
	private $default_terms = array(
		'ls_plugin_portfolio_industry'     => array(
			'eLearning',
			'Tour Operators',
			'WordPress',
			'WooCommerce',
			'Health & Fitness',
		),
		'ls_plugin_portfolio_software'     => array(
			'Google Analytics',
			'Gravity Forms',
			'Yoast SEO',
		),
		'ls_plugin_portfolio_project_type' => array(
			'New Store',
			'New Website',
			'Store Redesign',
			'Tour Operator Website Redesign',
			'Website Redesign',
		),
		'ls_plugin_portfolio_service'      => array(
			'Branding',
			'Content Management',
			'Design',
			'Development',
			'Discovery',
			'Featured',
			'Hosting',
			'Migrations',
			'Support',
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'maybe_seed_terms' ), 20 );
	}

	/**
	 * Seed default terms if this seed version hasn't run yet.
	 */
	public function maybe_seed_terms() {
		if ( get_option( self::SEEDED_OPTION ) === self::SEED_VERSION ) {
			return;
		}

		$all_taxonomies_ready = true;

		foreach ( $this->default_terms as $taxonomy => $terms ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				$all_taxonomies_ready = false;
				continue;
			}

			foreach ( $terms as $term ) {
				if ( term_exists( $term, $taxonomy ) ) {
					continue;
				}

				wp_insert_term( $term, $taxonomy );
			}
		}

		// Only mark this version as seeded once every taxonomy in the list
		// was actually registered and processed — otherwise retry on the
		// next request instead of silently leaving a taxonomy unseeded.
		if ( $all_taxonomies_ready ) {
			update_option( self::SEEDED_OPTION, self::SEED_VERSION );
		}
	}
}
