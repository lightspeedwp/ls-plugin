<?php
/**
 * Breadcrumbs block render template.
 *
 * Builds a breadcrumb trail entirely from core WordPress data (pages, posts,
 * categories/tags/taxonomies, search, and 404) — no plugin dependency.
 *
 * @package LS_Plugin
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ls_breadcrumbs_wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'      => 'ls-crumbs',
		'aria-label' => __( 'Breadcrumb', 'ls-plugin' ),
	)
);

$ls_breadcrumbs_before = '<nav ' . $ls_breadcrumbs_wrapper_attributes . '>';
$ls_breadcrumbs_after  = '</nav>';

$ls_breadcrumbs_trail = array();

if ( is_front_page() ) {
	$ls_breadcrumbs_trail[] = array(
		'label' => __( 'Home', 'ls-plugin' ),
		'url'   => null,
	);
} else {
	$ls_breadcrumbs_trail[] = array(
		'label' => __( 'Home', 'ls-plugin' ),
		'url'   => home_url( '/' ),
	);

	if ( is_home() ) {
		$ls_breadcrumbs_page_for_posts = (int) get_option( 'page_for_posts' );
		$ls_breadcrumbs_trail[]        = array(
			'label' => $ls_breadcrumbs_page_for_posts
				? get_the_title( $ls_breadcrumbs_page_for_posts )
				: __( 'Blog', 'ls-plugin' ),
			'url'   => null,
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$ls_breadcrumbs_term = get_queried_object();

		if ( $ls_breadcrumbs_term instanceof WP_Term ) {
			LS_Plugin_Breadcrumbs::add_taxonomy_ancestors( $ls_breadcrumbs_trail, $ls_breadcrumbs_term );

			$ls_breadcrumbs_trail[] = array(
				'label' => $ls_breadcrumbs_term->name,
				'url'   => null,
			);
		}
	} elseif ( is_single() ) {
		$ls_breadcrumbs_post = get_queried_object();

		if ( $ls_breadcrumbs_post instanceof WP_Post ) {
			$ls_breadcrumbs_post_type = get_post_type( $ls_breadcrumbs_post );

			if ( is_post_type_hierarchical( $ls_breadcrumbs_post_type ) ) {
				LS_Plugin_Breadcrumbs::add_post_ancestors( $ls_breadcrumbs_trail, $ls_breadcrumbs_post );
			} elseif ( 'post' === $ls_breadcrumbs_post_type ) {
				$ls_breadcrumbs_categories = get_the_category( $ls_breadcrumbs_post->ID );

				if ( ! empty( $ls_breadcrumbs_categories ) ) {
					$ls_breadcrumbs_primary_term = $ls_breadcrumbs_categories[0];

					LS_Plugin_Breadcrumbs::add_taxonomy_ancestors( $ls_breadcrumbs_trail, $ls_breadcrumbs_primary_term );

					$ls_breadcrumbs_trail[] = array(
						'label' => $ls_breadcrumbs_primary_term->name,
						'url'   => LS_Plugin_Breadcrumbs::get_term_url( $ls_breadcrumbs_primary_term ),
					);
				}
			}

			$ls_breadcrumbs_trail[] = array(
				'label' => get_the_title( $ls_breadcrumbs_post ),
				'url'   => null,
			);
		}
	} elseif ( is_page() ) {
		$ls_breadcrumbs_post = get_queried_object();

		if ( $ls_breadcrumbs_post instanceof WP_Post ) {
			LS_Plugin_Breadcrumbs::add_post_ancestors( $ls_breadcrumbs_trail, $ls_breadcrumbs_post );

			$ls_breadcrumbs_trail[] = array(
				'label' => get_the_title( $ls_breadcrumbs_post ),
				'url'   => null,
			);
		}
	} elseif ( is_search() ) {
		$ls_breadcrumbs_trail[] = array(
			/* translators: %s: search query */
			'label' => sprintf( __( 'Search results for: %s', 'ls-plugin' ), get_search_query() ),
			'url'   => null,
		);
	} elseif ( is_404() ) {
		$ls_breadcrumbs_trail[] = array(
			'label' => __( 'Page not found', 'ls-plugin' ),
			'url'   => null,
		);
	} else {
		$ls_breadcrumbs_trail[] = array(
			'label' => get_the_archive_title(),
			'url'   => null,
		);
	}
}

echo $ls_breadcrumbs_before; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$ls_breadcrumbs_last_index = count( $ls_breadcrumbs_trail ) - 1;

foreach ( $ls_breadcrumbs_trail as $ls_breadcrumbs_index => $ls_breadcrumbs_crumb ) {
	if ( $ls_breadcrumbs_index === $ls_breadcrumbs_last_index || empty( $ls_breadcrumbs_crumb['url'] ) ) {
		printf(
			'<span aria-current="page">%s</span>',
			esc_html( $ls_breadcrumbs_crumb['label'] )
		);
		continue;
	}

	printf(
		'<a href="%1$s">%2$s</a><i>/</i>',
		esc_url( $ls_breadcrumbs_crumb['url'] ),
		esc_html( $ls_breadcrumbs_crumb['label'] )
	);
}

echo $ls_breadcrumbs_after; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
