<?php
/**
 * Linkable block controls for supported core container blocks.
 *
 * @package LS_PLUGIN
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns supported block names for linkable block behaviour.
 *
 * @return string[]
 */
function ls_plugin_get_linkable_block_names() {
	return array( 'core/group', 'core/column', 'core/cover', 'core/block' );
}

/**
 * Returns a cache-busting version for a local plugin asset.
 *
 * @param string $relative_path Relative file path inside the plugin.
 * @return string
 */
function ls_plugin_get_asset_version( $relative_path ) {
	$file_path = LS_PLUGIN_PLUGIN_DIR . ltrim( $relative_path, '/' );

	if ( file_exists( $file_path ) ) {
		return (string) filemtime( $file_path );
	}

	return LS_PLUGIN_VERSION;
}

/**
 * Registers linkable block assets.
 */
function ls_plugin_register_linkable_block_assets() {
	wp_register_style(
		'ls-plugin-linkable-blocks',
		LS_PLUGIN_PLUGIN_URL . 'assets/css/linkable-blocks.css',
		array(),
		ls_plugin_get_asset_version( 'assets/css/linkable-blocks.css' )
	);

	wp_register_script(
		'ls-plugin-linkable-blocks-editor',
		LS_PLUGIN_PLUGIN_URL . 'assets/js/linkable-blocks-editor.js',
		array( 'wp-block-editor', 'wp-components', 'wp-data', 'wp-element', 'wp-hooks', 'wp-i18n' ),
		ls_plugin_get_asset_version( 'assets/js/linkable-blocks-editor.js' ),
		true
	);

	wp_set_script_translations(
		'ls-plugin-linkable-blocks-editor',
		'ls-plugin',
		LS_PLUGIN_PLUGIN_DIR . 'languages'
	);

	wp_register_script(
		'ls-plugin-linkable-blocks-frontend',
		LS_PLUGIN_PLUGIN_URL . 'assets/js/linkable-blocks-frontend.js',
		array(),
		ls_plugin_get_asset_version( 'assets/js/linkable-blocks-frontend.js' ),
		true
	);
}
add_action( 'init', 'ls_plugin_register_linkable_block_assets' );

/**
 * Enqueues linkable block assets for the block editor.
 */
function ls_plugin_enqueue_linkable_block_editor_assets() {
	wp_enqueue_style( 'ls-plugin-linkable-blocks' );
	wp_enqueue_script( 'ls-plugin-linkable-blocks-editor' );
}
add_action( 'enqueue_block_editor_assets', 'ls_plugin_enqueue_linkable_block_editor_assets' );

/**
 * Enqueues frontend assets for linkable blocks.
 */
function ls_plugin_enqueue_linkable_block_frontend_assets() {
	if ( is_admin() ) {
		return;
	}

	wp_enqueue_style( 'ls-plugin-linkable-blocks' );
	wp_enqueue_script( 'ls-plugin-linkable-blocks-frontend' );
}
add_action( 'wp_enqueue_scripts', 'ls_plugin_enqueue_linkable_block_frontend_assets' );

/**
 * Determines whether the provided block has supported link attributes.
 *
 * @param string $block_name Block name.
 * @param array  $attrs      Block attributes.
 * @return bool
 */
function ls_plugin_linkable_block_has_link_attributes( $block_name, $attrs ) {
	if ( ! in_array( $block_name, ls_plugin_get_linkable_block_names(), true ) ) {
		return false;
	}

	if ( ! is_array( $attrs ) ) {
		return false;
	}

	if ( ! empty( $attrs['href'] ) ) {
		return true;
	}

	return in_array( $attrs['linkDestination'] ?? '', array( 'post', 'term' ), true );
}

/**
 * Resolves the link target for a linkable block.
 *
 * @param array $attrs   Block attributes.
 * @param array $context Block context.
 * @return string
 */
function ls_plugin_resolve_linkable_block_url( $attrs, $context ) {
	$link_destination = $attrs['linkDestination'] ?? '';

	if ( 'custom' === $link_destination && ! empty( $attrs['href'] ) ) {
		return esc_url_raw( $attrs['href'] );
	}

	if ( 'post' === $link_destination ) {
		$post_id = isset( $context['postId'] ) ? absint( $context['postId'] ) : 0;

		if ( $post_id ) {
			return (string) get_permalink( $post_id );
		}

		$permalink = get_permalink();

		return is_string( $permalink ) ? $permalink : '';
	}

	if ( 'term' === $link_destination ) {
		$term_id  = isset( $context['termId'] ) ? absint( $context['termId'] ) : 0;
		$taxonomy = isset( $context['taxonomy'] ) ? sanitize_key( $context['taxonomy'] ) : '';

		if ( $term_id && $taxonomy ) {
			$term_link = get_term_link( $term_id, $taxonomy );

			if ( ! is_wp_error( $term_link ) ) {
				return (string) $term_link;
			}
		}
	}

	return '';
}

/**
 * Builds link markup for a supported block.
 *
 * @param array $attrs   Block attributes.
 * @param array $context Block context.
 * @return string
 */
function ls_plugin_get_linkable_block_markup( $attrs, $context ) {
	$url = ls_plugin_resolve_linkable_block_url( $attrs, $context );

	if ( '' === $url ) {
		return '';
	}

	$target = '_blank' === ( $attrs['linkTarget'] ?? '' ) ? '_blank' : '_self';
	$rel    = '_blank' === $target ? ' rel="noopener noreferrer"' : '';

	return sprintf(
		'<a class="wp-block__link ls-plugin-linkable-block__anchor" href="%1$s" target="%2$s"%3$s data-expand-click-area tabindex="-1" aria-hidden="true">&nbsp;</a>',
		esc_url( $url ),
		esc_attr( $target ),
		$rel
	);
}

/**
 * Applies link behaviour to supported rendered blocks.
 *
 * @param string   $block_content The rendered block markup.
 * @param array    $block         Parsed block data.
 * @param WP_Block $instance      Block instance.
 * @return string
 */
function ls_plugin_render_linkable_blocks( $block_content, $block, $instance ) {
	$block_name = $block['blockName'] ?? '';
	$attrs      = $block['attrs'] ?? array();

	if ( ! ls_plugin_linkable_block_has_link_attributes( $block_name, $attrs ) ) {
		return $block_content;
	}

	$link_markup = ls_plugin_get_linkable_block_markup(
		$attrs,
		isset( $instance->context ) && is_array( $instance->context ) ? $instance->context : array()
	);

	if ( '' === $link_markup ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag() ) {
		return $block_content;
	}

	$processor->add_class( 'is-linked' );
	$processor->add_class( 'ls-plugin-linkable-block' );
	$tag_name      = strtolower( $processor->get_tag() );
	$block_content = $processor->get_updated_html();
	$closing_tag   = sprintf( '</%s>', $tag_name );
	$closing_pos   = strrpos( $block_content, $closing_tag );

	if ( false === $closing_pos ) {
		return $block_content;
	}

	return substr( $block_content, 0, $closing_pos ) . $link_markup . substr( $block_content, $closing_pos );
}
add_filter( 'render_block', 'ls_plugin_render_linkable_blocks', 10, 3 );

/**
 * Registers block context used by the linkable block feature.
 *
 * @param array  $args       Block registration arguments.
 * @param string $block_type Block type name.
 * @return array
 */
function ls_plugin_register_linkable_block_context( $args, $block_type ) {
	if ( ! in_array( $block_type, ls_plugin_get_linkable_block_names(), true ) ) {
		return $args;
	}

	if ( ! isset( $args['uses_context'] ) || ! is_array( $args['uses_context'] ) ) {
		$args['uses_context'] = array();
	}

	foreach ( array( 'postId', 'termId', 'taxonomy' ) as $context_name ) {
		if ( ! in_array( $context_name, $args['uses_context'], true ) ) {
			$args['uses_context'][] = $context_name;
		}
	}

	return $args;
}
add_filter( 'register_block_type_args', 'ls_plugin_register_linkable_block_context', 10, 2 );