<?php
/**
 * Search Noindex Exclude.
 *
 * Keeps posts that are set to "noindex" in Yoast SEO out of the front-end
 * site search results.
 *
 * WordPress core search knows nothing about Yoast's noindex setting, so a page
 * that is hidden from Google can still be found through the site's own search
 * box. This class closes that gap without unpublishing or changing any content.
 *
 * @package LS_Plugin
 * @since   0.2.0
 */

namespace LS_Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Search_Noindex_Exclude
 *
 * @since 0.2.0
 */
class Search_Noindex_Exclude {

	/**
	 * Post meta key Yoast SEO uses to store the per-post noindex setting.
	 *
	 * Stored value `1` means noindex, `2` means index and `0` (or no row)
	 * means "use the default for this content type".
	 *
	 * @var string
	 */
	const NOINDEX_META_KEY = '_yoast_wpseo_meta-robots-noindex';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Priority 1000 runs after the Search Filter block (999), which can set
		// the search term itself from the block's request parameter.
		add_action( 'pre_get_posts', array( $this, 'filter_main_query' ), 1000 );
		add_filter( 'query_loop_block_query_vars', array( $this, 'filter_query_loop' ), 1000 );
	}

	/**
	 * Excludes noindexed posts from the main front-end search query.
	 *
	 * @param \WP_Query $query Query object.
	 * @return void
	 */
	public function filter_main_query( $query ) {
		if ( ! $query instanceof \WP_Query || ! $query->is_main_query() || is_admin() ) {
			return;
		}

		if ( '' === (string) $query->get( 's' ) || ! $this->is_enabled() ) {
			return;
		}

		$query->set( 'meta_query', $this->add_clause( $query->get( 'meta_query' ) ) );
	}

	/**
	 * Excludes noindexed posts from Query Loop blocks that are searching.
	 *
	 * @param array $query Query vars.
	 * @return array
	 */
	public function filter_query_loop( $query ) {
		if ( ! is_array( $query ) || empty( $query['s'] ) || ! $this->is_enabled() ) {
			return $query;
		}

		$query['meta_query'] = $this->add_clause( $query['meta_query'] ?? array() );

		return $query;
	}

	/**
	 * Whether noindexed posts should be excluded from search.
	 *
	 * Returning false from `ls_plugin_search_exclude_noindex` switches the
	 * behaviour off, for example on a staging site.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		return (bool) apply_filters( 'ls_plugin_search_exclude_noindex', true );
	}

	/**
	 * Adds the "not noindexed" clause to an existing meta query.
	 *
	 * A post passes when it has no noindex row at all, or when the row is not
	 * `1`. Posts that were never touched by Yoast therefore stay searchable.
	 *
	 * @param mixed $meta_query Existing meta query, if any.
	 * @return array
	 */
	private function add_clause( $meta_query ) {
		$clause = array(
			'relation' => 'OR',
			array(
				'key'     => self::NOINDEX_META_KEY,
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => self::NOINDEX_META_KEY,
				'value'   => '1',
				'compare' => '!=',
			),
		);

		if ( empty( $meta_query ) || ! is_array( $meta_query ) ) {
			return array( $clause );
		}

		return array(
			'relation' => 'AND',
			$meta_query,
			$clause,
		);
	}
}
