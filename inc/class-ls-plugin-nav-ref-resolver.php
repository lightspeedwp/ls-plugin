<?php
/**
 * Navigation Ref Auto-Resolver.
 *
 * If a `core/navigation` block has no `ref` attribute set, resolves it at
 * render time to the `wp_navigation` post matching a documented slug
 * convention, so themes never need to hardcode a `ref` (which is a database
 * row ID and not portable across environments or sites).
 *
 * This does not create, seed, or assume any specific menu content — menu
 * content stays editorial, set up per site in the Site Editor as normal.
 *
 * @package LS_Plugin
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LS_Plugin_Nav_Ref_Resolver
 *
 * Scoped to the header navigation location only for Phase 1 — there is
 * currently only one active `core/navigation` block in the theme. Additional
 * locations (e.g. a footer nav) would need their own conventional slug and
 * a way to distinguish which block instance they apply to; that's future work.
 *
 * @since 0.2.0
 */
class LS_Plugin_Nav_Ref_Resolver {

	/**
	 * Default conventional slug for the header `wp_navigation` post.
	 *
	 * Sites adopting this plugin can override this via the
	 * `ls_plugin_nav_ref_slug` filter if they use a different slug for
	 * their header navigation content.
	 *
	 * @var string
	 */
	const DEFAULT_HEADER_NAV_SLUG = 'header-navigation';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'render_block_data', array( $this, 'maybe_resolve_ref' ) );
	}

	/**
	 * Injects a resolved `ref` into `core/navigation` blocks that don't
	 * already have one.
	 *
	 * @param array $parsed_block The parsed block data.
	 * @return array The (possibly modified) parsed block data.
	 */
	public function maybe_resolve_ref( $parsed_block ) {
		if ( ! isset( $parsed_block['blockName'] ) || 'core/navigation' !== $parsed_block['blockName'] ) {
			return $parsed_block;
		}

		if ( ! empty( $parsed_block['attrs']['ref'] ) ) {
			return $parsed_block;
		}

		$nav_id = $this->get_header_nav_id();

		if ( $nav_id ) {
			$parsed_block['attrs']['ref'] = $nav_id;
		}

		return $parsed_block;
	}

	/**
	 * Looks up the header `wp_navigation` post ID by its conventional slug.
	 *
	 * Fails gracefully (returns 0) if no matching post exists yet — no
	 * fatal, no placeholder content is created.
	 *
	 * @return int Post ID, or 0 if not found.
	 */
	private function get_header_nav_id() {
		static $resolved_id = null;

		if ( null !== $resolved_id ) {
			return $resolved_id;
		}

		/**
		 * Filters the conventional slug used to look up the header
		 * navigation's `wp_navigation` post.
		 *
		 * @param string $slug The slug to look up. Default 'header-navigation'.
		 */
		$slug = apply_filters( 'ls_plugin_nav_ref_slug', self::DEFAULT_HEADER_NAV_SLUG );

		$posts = get_posts(
			array(
				'name'             => $slug,
				'post_type'        => 'wp_navigation',
				'post_status'      => 'publish',
				'numberposts'      => 1,
				'no_found_rows'    => true,
				'fields'           => 'ids',
				// Allow multilingual plugins (WPML, Polylang) to filter this
				// query and resolve the translated nav post for the current language.
				'suppress_filters' => false,
			)
		);

		$resolved_id = ! empty( $posts ) ? (int) $posts[0] : 0;

		return $resolved_id;
	}
}
