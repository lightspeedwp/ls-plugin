<?php
/**
 * Search Filter block registration and query handling.
 *
 * @package LS_Plugin
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Search Filter block registration and Query Loop filtering.
 */
class LS_Plugin_Search_Filter {

	/**
	 * Registers all WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'pre_get_posts', array( $this, 'filter_main_query' ), 999 );
		add_filter( 'query_loop_block_query_vars', array( $this, 'filter_secondary_queries' ), 999, 3 );
	}

	/**
	 * Registers the block from built assets.
	 *
	 * @return void
	 */
	public function register_block() {
		$block_path = LS_PLUGIN_PLUGIN_DIR . 'build/blocks/search-filter';

		if ( file_exists( $block_path . '/block.json' ) ) {
			register_block_type( $block_path );
		}
	}

	/**
	 * Filters the inherited main query using the block request parameter.
	 *
	 * @param WP_Query $query Query object.
	 * @return void
	 */
	public function filter_main_query( $query ) {
		if ( ! $query instanceof WP_Query || ! $query->is_main_query() ) {
			return;
		}

		$meta_key = $this->find_request_meta_key( 'query-search-' );
		$key      = $meta_key ? "query-search-{$meta_key}" : 'query-search';
		$search   = $this->get_request_value( $key );

		if ( null === $search ) {
			return;
		}

		if ( $meta_key ) {
			$query->set( 'meta_query', $this->prepare_meta_query( $query->get( 'meta_query' ), $meta_key, $search ) );
			return;
		}

		$query->set( 's', $search );
	}

	/**
	 * Filters non-inherited Query Loop block queries using the block request parameter.
	 *
	 * @param array    $query Query vars.
	 * @param WP_Block $block Block instance.
	 * @param int      $page  Current page.
	 * @return array
	 */
	public function filter_secondary_queries( $query, $block, $page ) {
		unset( $page );

		$query_id = 0;

		if ( isset( $block->context['queryId'] ) ) {
			$query_id = absint( $block->context['queryId'] );
		}

		$meta_prefix = sprintf( 'query-%d-search-', $query_id );
		$meta_key    = $query_id ? $this->find_request_meta_key( $meta_prefix ) : '';
		$key         = $meta_key ? sprintf( 'query-%d-search-%s', $query_id, $meta_key ) : sprintf( 'query-%d-search', $query_id );
		$search      = $this->get_request_value( $key );

		if ( null === $search ) {
			return $query;
		}

		if ( $meta_key ) {
			$query['meta_query'] = $this->prepare_meta_query( $query['meta_query'] ?? array(), $meta_key, $search );
			return $query;
		}

		$query['s'] = $search;

		return $query;
	}

	/**
	 * Finds a custom-field suffix in request parameter names.
	 *
	 * @param string $prefix Request key prefix.
	 * @return string
	 */
	private function find_request_meta_key( $prefix ) {
		foreach ( array_keys( $_REQUEST ) as $request_key ) {
			$request_key = sanitize_key( (string) $request_key );

			if ( 0 !== strpos( $request_key, $prefix ) ) {
				continue;
			}

			$meta_key = substr( $request_key, strlen( $prefix ) );

			if ( '' !== $meta_key ) {
				return $meta_key;
			}
		}

		return '';
	}

	/**
	 * Returns a sanitised request value if present.
	 *
	 * @param string $key Request key.
	 * @return string|null
	 */
	private function get_request_value( $key ) {
		if ( ! isset( $_REQUEST[ $key ] ) ) {
			return null;
		}

		return sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) );
	}

	/**
	 * Appends the search constraint to an existing meta query.
	 *
	 * @param mixed  $query_meta_query Existing meta query.
	 * @param string $meta_key         Meta key.
	 * @param string $meta_value       Search value.
	 * @return array
	 */
	private function prepare_meta_query( $query_meta_query, $meta_key, $meta_value ) {
		$search_meta_query = array(
			'key'     => $meta_key,
			'value'   => $meta_value,
			'compare' => 'LIKE',
		);

		if ( ! is_array( $query_meta_query ) || empty( $query_meta_query ) ) {
			return array( $search_meta_query );
		}

		if ( ! isset( $query_meta_query['relation'] ) ) {
			$query_meta_query['relation'] = 'AND';
		}

		$query_meta_query[] = $search_meta_query;

		return $query_meta_query;
	}
}