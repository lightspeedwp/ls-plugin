<?php
/**
 * Search Filter block render template.
 *
 * @package LS_Plugin
 */

$is_inherited_query = ! empty( $block->context['query']['inherit'] );
$query_id           = isset( $block->context['queryId'] ) ? absint( $block->context['queryId'] ) : 0;

$meta_key = '';
if ( ! empty( $attributes['searchByCustomField'] ) && ! empty( $attributes['metaKey'] ) ) {
	$meta_key = sanitize_key( $attributes['metaKey'] );
}

if ( ! $is_inherited_query && $query_id ) {
	if ( $meta_key ) {
		$search_key = sprintf( 'query-%d-search-%s', $query_id, $meta_key );
	} else {
		$search_key = sprintf( 'query-%d-search', $query_id );
	}
} elseif ( $meta_key ) {
	$search_key = sprintf( 'query-search-%s', $meta_key );
} else {
	$search_key = 'query-search';
}

$search_value = '';
if ( isset( $_REQUEST[ $search_key ] ) ) {
	$search_value = sanitize_text_field( wp_unslash( $_REQUEST[ $search_key ] ) );
}

$width      = isset( $attributes['width'] ) ? (int) $attributes['width'] : 100;
$width_unit = isset( $attributes['widthUnit'] ) ? sanitize_text_field( $attributes['widthUnit'] ) : '%';

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'style' => sprintf( 'width:%d%s;', $width, esc_attr( $width_unit ) ),
	)
);

$label = ! empty( $attributes['placeholder'] )
	? $attributes['placeholder']
	: __( 'Search', 'ls-plugin' );
?>

<div
	<?php echo wp_kses_data( $wrapper_attributes ); ?>
	data-wp-interactive="lsPluginSearchFilter"
	<?php echo wp_interactivity_data_wp_context( array( 'searchKey' => $search_key ) ); ?>
>
	<input
		class="wp-block-ls-plugin-search-filter__input"
		type="text"
		value="<?php echo esc_attr( $search_value ); ?>"
		placeholder="<?php echo esc_attr( ! empty( $attributes['placeholder'] ) ? $attributes['placeholder'] : __( 'Search…', 'ls-plugin' ) ); ?>"
		aria-label="<?php echo esc_attr( $label ); ?>"
		data-wp-on--input="actions.search"
		data-wp-on--keyup="actions.search"
	/>
</div>