<?php
/**
 * Taxonomy Filter Block
 *
 * Registers and handles the Taxonomy Filter block with WordPress Interactivity API.
 *
 * @package LS_Plugin
 */

namespace LS_Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Taxonomy_Filter
 *
 * Handles registration and query filtering for the Taxonomy Filter block.
 */
class Taxonomy_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_main_query' ) );
		add_filter( 'query_loop_block_query_vars', array( $this, 'filter_secondary_queries' ), 10, 3 );
		add_filter( 'render_block_data', array( $this, 'ensure_enhanced_pagination' ), 10, 2 );
		add_filter( 'render_block_core/query', array( $this, 'force_client_navigation_for_taxonomy_filter' ), 10, 2 );
	}

	/**
	 * Register the block and its view script module.
	 */
	public function register_block() {
		// Register the view script module manually to avoid auto-injection.
		$view_module_path = LS_PLUGIN_PLUGIN_DIR . 'src/js/taxonomy-filter-view-module.js';

		if ( file_exists( $view_module_path ) ) {
			wp_register_script_module(
				'@ls-plugin/taxonomy-filter-view',
				LS_PLUGIN_PLUGIN_URL . 'src/js/taxonomy-filter-view-module.js',
				array(
					'@wordpress/interactivity',
					'@wordpress/interactivity-router',
				),
				filemtime( $view_module_path )
			);
		}

		// Register the block.
		register_block_type( LS_PLUGIN_PLUGIN_DIR . 'build/blocks/taxonomy-filter' );
	}

	/**
	 * Filter the main query based on taxonomy filter parameters.
	 *
	 * @param \WP_Query $query The WP_Query instance.
	 */
	public function filter_main_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// Check for taxonomy filter parameters.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		foreach ( $_REQUEST as $key => $value ) {
			if ( strpos( $key, 'filter-' ) === 0 && ! empty( $value ) ) {
				// Extract taxonomy name from the key.
				$taxonomy = str_replace( 'filter-', '', $key );

				// Validate taxonomy exists.
				if ( taxonomy_exists( $taxonomy ) ) {
					$tax_query = $this->prepare_tax_query( $taxonomy, $value );

					if ( ! empty( $tax_query ) ) {
						$existing_tax_query = $query->get( 'tax_query' );

						if ( ! empty( $existing_tax_query ) ) {
							$tax_query = array_merge( $existing_tax_query, $tax_query );
						}

						$query->set( 'tax_query', $tax_query );
					}
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Filter secondary queries (Query Loop blocks) based on taxonomy filter parameters.
	 *
	 * @param array     $query  Query vars.
	 * @param \WP_Block $block  Block instance.
	 * @param int      $page   Current page number.
	 * @return array Modified query vars.
	 */
	public function filter_secondary_queries( $query, $block, $page ) {
		// Check for taxonomy filter parameters with queryId.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		foreach ( $_REQUEST as $key => $value ) {
			if ( strpos( $key, 'filter-' ) === 0 && ! empty( $value ) ) {
				// Extract query ID and taxonomy from the key.
				$parts = explode( '-', str_replace( 'filter-', '', $key ) );

				if ( count( $parts ) >= 2 ) {
					$query_id = $parts[0];
					$taxonomy = implode( '-', array_slice( $parts, 1 ) );

					// Check if this filter applies to the current Query Loop.
					$block_query_id = $block->context['queryId'] ?? null;

					if ( $query_id == $block_query_id && taxonomy_exists( $taxonomy ) ) {
						$tax_query = $this->prepare_tax_query( $taxonomy, $value );

						if ( ! empty( $tax_query ) ) {
							if ( ! empty( $query['tax_query'] ) ) {
								$query['tax_query'] = array_merge( $query['tax_query'], $tax_query );
							} else {
								$query['tax_query'] = $tax_query;
							}
						}
					}
				}

				// Handle main query taxonomy filters (no query ID).
				if ( count( $parts ) === 1 ) {
					$taxonomy = $parts[0];

					// Only apply to main query blocks (inherit = true).
					if ( isset( $block->context['query']['inherit'] ) && $block->context['query']['inherit'] && taxonomy_exists( $taxonomy ) ) {
						$tax_query = $this->prepare_tax_query( $taxonomy, $value );

						if ( ! empty( $tax_query ) ) {
							if ( ! empty( $query['tax_query'] ) ) {
								$query['tax_query'] = array_merge( $query['tax_query'], $tax_query );
							} else {
								$query['tax_query'] = $tax_query;
							}
						}
					}
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return $query;
	}

	/**
	 * Prepare taxonomy query array.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @param string $slug     Term slug.
	 * @return array Tax query array.
	 */
	private function prepare_tax_query( $taxonomy, $slug ) {
		// Sanitize the taxonomy and slug.
		$taxonomy = sanitize_key( $taxonomy );
		$slug     = sanitize_text_field( $slug );

		// Validate that the taxonomy exists.
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		// Check if term exists.
		$term = get_term_by( 'slug', $slug, $taxonomy );

		if ( ! $term || is_wp_error( $term ) ) {
			return array();
		}

		return array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $slug,
			),
		);
	}

	/**
	 * Force enhanced pagination for Query blocks that contain taxonomy filter.
	 *
	 * @param array  $parsed_block Parsed block data.
	 * @param array  $source_block Source block data.
	 * @return array Modified block data.
	 */
	public function ensure_enhanced_pagination( $parsed_block, $source_block ) {
		// Only process Query blocks.
		if ( 'core/query' !== $parsed_block['blockName'] ) {
			return $parsed_block;
		}

		// Check if this Query block has a taxonomy filter as a child.
		$has_taxonomy_filter = $this->has_taxonomy_filter_in_block( $parsed_block );

		if ( $has_taxonomy_filter ) {
			// Force enhanced pagination to be enabled.
			if ( ! isset( $parsed_block['attrs']['enhancedPagination'] ) ) {
				$parsed_block['attrs']['enhancedPagination'] = true;
			}
		}

		return $parsed_block;
	}

	/**
	 * Check if a block has a taxonomy filter block as a descendant.
	 *
	 * @param array $block Block data.
	 * @return bool True if taxonomy filter found, false otherwise.
	 */
	private function has_taxonomy_filter_in_block( $block ) {
		if ( 'ls-plugin/taxonomy-filter' === $block['blockName'] ) {
			return true;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			foreach ( $block['innerBlocks'] as $inner_block ) {
				if ( $this->has_taxonomy_filter_in_block( $inner_block ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Force client navigation to stay enabled for Query blocks with taxonomy filter.
	 *
	 * WordPress core sets clientNavigationDisabled:true when non-interactive blocks
	 * are present, which forces full page reloads. This filter overrides that behavior
	 * for Query blocks that contain our taxonomy filter.
	 *
	 * @param string   $block_content Block HTML output.
	 * @param array    $block         Block data.
	 * @return string Modified block content.
	 */
	public function force_client_navigation_for_taxonomy_filter( $block_content, $block ) {
		// Check if this Query block has taxonomy filter.
		$has_taxonomy_filter = $this->has_taxonomy_filter_in_block( $block );

		if ( ! $has_taxonomy_filter ) {
			return $block_content;
		}

		// Override the clientNavigationDisabled setting in the page config.
		// We need to inject a script that fixes the config after WordPress sets it.
		$fix_script = "
		<script type='module'>
			document.addEventListener('DOMContentLoaded', () => {
				if (window?._wpInteractivityConfig?.core?.query) {
					const queryConfig = window._wpInteractivityConfig.core.query;
					if (queryConfig.clientNavigationDisabled === true) {
						queryConfig.clientNavigationDisabled = false;
					}
				}
			});
		</script>
		";

		return $block_content . $fix_script;
	}
}
