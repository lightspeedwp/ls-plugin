<?php
/**
 * General plugin functions
 *
 * @package LS_PLUGIN
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allows Back to Top buttons to render without text content.
 *
 * WordPress by default prevents empty buttons from rendering on the frontend.
 * This filter ensures Back to Top buttons display even when they have no text,
 * allowing for icon-only buttons.
 *
 * @param string $block_content The block content.
 * @param array  $block         The block data.
 * @return string The modified block content.
 */
function ls_plugin_allow_empty_back_to_top_button( $block_content, $block ) {
	// Only apply to core/button blocks.
	if ( 'core/button' !== $block['blockName'] ) {
		return $block_content;
	}

	// Check if this is a Back to Top button.
	$is_back_to_top = $block['attrs']['isBackToTop'] ?? false;

	// If not a back-to-top button, return original content.
	if ( ! $is_back_to_top ) {
		return $block_content;
	}

	// If the button rendered as empty (WordPress filtered it out), rebuild it.
	if ( empty( trim( $block_content ) ) ) {
		$attributes = $block['attrs'] ?? array();
		
		// Build the classes.
		$classes = array( 'wp-block-button', 'is-back-to-top' );
		if ( ! empty( $attributes['className'] ) ) {
			$classes[] = $attributes['className'];
		}
		
		// Build the button link classes.
		$link_classes = array( 'wp-block-button__link' );
		if ( ! empty( $attributes['className'] ) ) {
			// Add any button-specific classes from the block.
			$link_classes[] = 'wp-element-button';
		}
		
		// Get positioning mode.
		$position_mode = $attributes['backToTopPositionMode'] ?? 'scroll';
		
		// Build data attributes.
		$data_attrs = sprintf( 'data-back-to-top-mode="%s"', esc_attr( $position_mode ) );
		
		// Add threshold for sticky/fixed modes.
		if ( in_array( $position_mode, array( 'sticky', 'fixed' ), true ) ) {
			$threshold = $attributes['backToTopScrollThreshold'] ?? 75;
			$data_attrs .= sprintf( ' data-scroll-threshold="%d"', absint( $threshold ) );
		}
		
		// Rebuild the button markup.
		$block_content = sprintf(
			'<div class="%s" %s><a class="%s" href="#"></a></div>',
			esc_attr( implode( ' ', $classes ) ),
			$data_attrs,
			esc_attr( implode( ' ', $link_classes ) )
		);
	}

	return $block_content;
}
add_filter( 'render_block', 'ls_plugin_allow_empty_back_to_top_button', 10, 2 );
