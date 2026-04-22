<?php
/**
 * Taxonomy Filter Block Template.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @package LS_Plugin
 */

// Determine filter key based on context.
$page_key       = isset( $block->context['queryId'] ) ? 'query-' . $block->context['queryId'] . '-page' : 'query-page';
$is_main_query  = isset( $block->context['query']['inherit'] ) && $block->context['query']['inherit'];
$has_enhanced_pagination = isset( $block->context['enhancedPagination'] ) && $block->context['enhancedPagination'];

// Get taxonomy object.
$taxonomy = get_taxonomy( $attributes['taxonomy']['slug'] ?? 'category' );

if ( ! $taxonomy ) {
	return '';
}

// Build filter key.
$key = isset( $block->context['queryId'] ) && ! $is_main_query
	? "filter-{$block->context['queryId']}-{$taxonomy->name}"
	: "filter-{$taxonomy->name}";

// Enqueue the view script module.
if ( function_exists( 'wp_enqueue_script_module' ) ) {
	wp_enqueue_script_module( '@ls-plugin/taxonomy-filter-view' );
}

$all_items = ! empty( $attributes['allItemsText'] ) ? $attributes['allItemsText'] : $taxonomy->labels->all_items;
$order_by  = ! empty( $attributes['orderBy'] ) ? $attributes['orderBy'] : 'name';
$order     = 'count' === $order_by ? 'DESC' : 'ASC';

// Get terms.
$args = array(
	'taxonomy'   => $taxonomy->name,
	'hide_empty' => apply_filters( 'ls_plugin_taxonomy_filter_hide_empty', true ),
	'exclude'    => apply_filters( 'ls_plugin_taxonomy_filter_exclude', array() ),
	'orderby'    => $order_by,
	'order'      => $order,
);

$terms = get_terms( $args );

if ( empty( $terms ) || is_wp_error( $terms ) ) {
	return '';
}

$terms = array_values( $terms );

// Build classes and styles.
$classes = array( "taxonomy-filter--{$attributes['filterType']}" );
$styles  = array();

// Button-specific classes and styles.
if ( 'buttons' === $attributes['filterType'] ) {
	$classes[] = 'is-layout-flex';
	$classes[] = 'wp-block-buttons';

	if ( ! empty( $attributes['justification'] ) ) {
		$classes[] = "is-content-justification-{$attributes['justification']}";
	}

	// Color handling.
	if ( ! empty( $attributes['buttonTextColor'] ) ) {
		$classes[] = 'has-button-text-color';
		$styles[]  = "--button-text-color:var(--wp--preset--color--{$attributes['buttonTextColor']});";
	} elseif ( ! empty( $attributes['customButtonTextColor'] ) ) {
		$classes[] = 'has-button-text-color';
		$styles[]  = "--button-text-color:{$attributes['customButtonTextColor']};";
	}

	if ( ! empty( $attributes['buttonBackgroundColor'] ) ) {
		$classes[] = 'has-button-background-color';
		$styles[]  = "--button-background-color:var(--wp--preset--color--{$attributes['buttonBackgroundColor']});";
	} elseif ( ! empty( $attributes['customButtonBackgroundColor'] ) ) {
		$classes[] = 'has-button-background-color';
		$styles[]  = "--button-background-color:{$attributes['customButtonBackgroundColor']};";
	}

	// Hover colors.
	if ( ! empty( $attributes['hoverButtonTextColor'] ) ) {
		$classes[] = 'has-hover-button-text-color';
		$styles[]  = "--hover-button-text-color:var(--wp--preset--color--{$attributes['hoverButtonTextColor']});";
	} elseif ( ! empty( $attributes['customHoverButtonTextColor'] ) ) {
		$classes[] = 'has-hover-button-text-color';
		$styles[]  = "--hover-button-text-color:{$attributes['customHoverButtonTextColor']};";
	}

	if ( ! empty( $attributes['hoverButtonBackgroundColor'] ) ) {
		$classes[] = 'has-hover-button-background-color';
		$styles[]  = "--hover-button-background-color:var(--wp--preset--color--{$attributes['hoverButtonBackgroundColor']});";
	} elseif ( ! empty( $attributes['customHoverButtonBackgroundColor'] ) ) {
		$classes[] = 'has-hover-button-background-color';
		$styles[]  = "--hover-button-background-color:{$attributes['customHoverButtonBackgroundColor']};";
	}

	// Active colors.
	if ( ! empty( $attributes['activeButtonTextColor'] ) ) {
		$classes[] = 'has-active-button-text-color';
		$styles[]  = "--active-button-text-color:var(--wp--preset--color--{$attributes['activeButtonTextColor']});";
	} elseif ( ! empty( $attributes['customActiveButtonTextColor'] ) ) {
		$classes[] = 'has-active-button-text-color';
		$styles[]  = "--active-button-text-color:{$attributes['customActiveButtonTextColor']};";
	}

	if ( ! empty( $attributes['activeButtonBackgroundColor'] ) ) {
		$classes[] = 'has-active-button-background-color';
		$styles[]  = "--active-button-background-color:var(--wp--preset--color--{$attributes['activeButtonBackgroundColor']});";
	} elseif ( ! empty( $attributes['customActiveButtonBackgroundColor'] ) ) {
		$classes[] = 'has-active-button-background-color';
		$styles[]  = "--active-button-background-color:{$attributes['customActiveButtonBackgroundColor']};";
	}

	// Border.
	if ( ! empty( $attributes['buttonBorder']['width'] ) ) {
		$classes[] = 'has-button-border';
		$styles[]  = "--button-border:{$attributes['buttonBorder']['width']} solid var(--wp--custom--color--button--fill--border, currentColor);";
	}

	// Border radius.
	if ( ! empty( $attributes['buttonBorderRadius'] ) ) {
		$classes[] = 'has-button-border-radius';
		$styles[]  = "--button-border-radius:{$attributes['buttonBorderRadius']};";
	}

} else {
	if ( ! empty( $attributes['textAlign'] ) ) {
		$classes[] = "has-text-align-{$attributes['textAlign']}";
	}
}

$is_limit_visible_items = isset( $attributes['limitVisibleItems'] ) && $attributes['limitVisibleItems'];
$visible_items_number   = ! empty( $attributes['visibleItemsNumber'] ) ? (int) $attributes['visibleItemsNumber'] : 5;
$expand_text            = ! empty( $attributes['expandText'] ) ? $attributes['expandText'] : __( '+ Show [number] more', 'ls-plugin' );
$expand_text            = str_replace( '[number]', ( count( $terms ) - $visible_items_number ), $expand_text );
$collapse_text          = ! empty( $attributes['collapseText'] ) ? $attributes['collapseText'] : __( '- Show less', 'ls-plugin' );

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $classes ),
		'style' => implode( '', $styles ),
	)
);
?>

<div
	<?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="lsPluginTaxonomyFilter"
	<?php echo wp_interactivity_data_wp_context( array( 'isExpanded' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
	<?php if ( 'dropdown' === $attributes['filterType'] ) : ?>
		<select
			data-wp-on--change="actions.navigate"
			data-wp-bind--value="context.filterValue"
			aria-label="<?php echo esc_attr( $taxonomy->label ); ?>"
		>
			<option value="<?php echo esc_url( remove_query_arg( $key ) ); ?>">
				<?php echo esc_html( $all_items ); ?>
			</option>
			<?php foreach ( $terms as $term ) : ?>
				<?php
				$base_url = $is_main_query ? get_pagenum_link( 1, false ) : add_query_arg( array( $page_key => 1 ) );
				$term_url = add_query_arg( array( $key => $term->slug ), $base_url );
				$selected = isset( $_REQUEST[ $key ] ) && $term->slug === $_REQUEST[ $key ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<option
					value="<?php echo esc_url( $term_url ); ?>"
					<?php selected( $selected ); ?>
				>
					<?php
					echo esc_html( $term->name );
					if ( $attributes['showCount'] ) {
						echo ' (' . esc_html( $term->count ) . ')';
					}
					?>
				</option>
			<?php endforeach; ?>
		</select>

	<?php elseif ( 'buttons' === $attributes['filterType'] ) : ?>
		<a
			href="<?php echo esc_url( remove_query_arg( $key ) ); ?>"
			class="wp-element-button <?php echo ! isset( $_REQUEST[ $key ] ) ? 'taxonomy-filter-current' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>"
			data-wp-on--click="core/query::actions.navigate"
		>
			<?php echo esc_html( $all_items ); ?>
		</a>

		<?php for ( $i = 0; $i < count( $terms ); $i++ ) : ?>
			<?php
			$term     = $terms[ $i ];
			$base_url = $is_main_query ? get_pagenum_link( 1, false ) : add_query_arg( array( $page_key => 1 ) );
			$term_url = add_query_arg( array( $key => $term->slug ), $base_url );
			$is_current = isset( $_REQUEST[ $key ] ) && $term->slug === $_REQUEST[ $key ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$hidden     = '';

			if ( $is_limit_visible_items && $i >= $visible_items_number ) {
				$hidden = ' data-wp-bind--hidden="!context.isExpanded"';
			}
			?>
			<a<?php echo $hidden; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				href="<?php echo esc_url( $term_url ); ?>"
				class="wp-element-button<?php echo $is_current ? ' taxonomy-filter-current' : ''; ?>"
				data-wp-on--click="core/query::actions.navigate"
			>
				<?php
				echo esc_html( $term->name );
				if ( $attributes['showCount'] ) {
					echo ' (' . esc_html( $term->count ) . ')';
				}
				?>
			</a>
		<?php endfor; ?>

		<?php if ( $is_limit_visible_items && count( $terms ) > $visible_items_number ) : ?>
			<button
				class="taxonomy-filter-show-more"
				data-wp-on--click="actions.showMore"
				data-wp-text="context.showMoreText"
				data-expand-text="<?php echo esc_attr( $expand_text ); ?>"
				data-collapse-text="<?php echo esc_attr( $collapse_text ); ?>"
			>
				<?php echo esc_html( $expand_text ); ?>
			</button>
		<?php endif; ?>
	<?php endif; ?>
</div>
