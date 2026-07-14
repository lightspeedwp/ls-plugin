<?php
/**
 * Breadcrumbs Block
 *
 * Registers the Breadcrumbs dynamic block.
 *
 * @package LS_Plugin
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LS_Plugin_Breadcrumbs
 *
 * Handles registration for the Breadcrumbs block.
 */
class LS_Plugin_Breadcrumbs {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the breadcrumbs block from the built block assets.
	 */
	public function register_block() {
		$block_path = LS_PLUGIN_PLUGIN_DIR . 'build/blocks/breadcrumbs';

		if ( file_exists( $block_path . '/block.json' ) ) {
			register_block_type( $block_path );
		}
	}

	/**
	 * Appends a hierarchical taxonomy term's ancestor chain to a breadcrumb trail.
	 *
	 * Does not append the term itself — only its ancestors, root-first.
	 *
	 * @param array   $trail Breadcrumb trail, passed by reference.
	 * @param WP_Term $term  Term whose ancestors should be added.
	 * @return void
	 */
	public static function add_taxonomy_ancestors( array &$trail, $term ) {
		if ( ! is_taxonomy_hierarchical( $term->taxonomy ) ) {
			return;
		}

		$ancestor_ids = array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) );

		foreach ( $ancestor_ids as $ancestor_id ) {
			$ancestor_term = get_term( $ancestor_id, $term->taxonomy );

			if ( $ancestor_term instanceof WP_Term ) {
				$trail[] = array(
					'label' => $ancestor_term->name,
					'url'   => self::get_term_url( $ancestor_term ),
				);
			}
		}
	}

	/**
	 * Appends a post's ancestor chain to a breadcrumb trail.
	 *
	 * Does not append the post itself — only its ancestors, root-first.
	 *
	 * @param array   $trail Breadcrumb trail, passed by reference.
	 * @param WP_Post $post  Post whose ancestors should be added.
	 * @return void
	 */
	public static function add_post_ancestors( array &$trail, $post ) {
		$ancestor_ids = array_reverse( get_post_ancestors( $post ) );

		foreach ( $ancestor_ids as $ancestor_id ) {
			$trail[] = array(
				'label' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			);
		}
	}

	/**
	 * Safely resolves a term's permalink, guarding against WP_Error.
	 *
	 * @param WP_Term $term Term to link to.
	 * @return string|null Term permalink, or null on failure.
	 */
	public static function get_term_url( $term ) {
		$link = get_term_link( $term );

		return is_wp_error( $link ) ? null : $link;
	}
}
