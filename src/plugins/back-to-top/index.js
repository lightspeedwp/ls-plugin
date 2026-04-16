/**
 * Back to Top Variation for core/button
 * Registers a "Back to Top" variation of the core Button block with smooth scrolling.
 */

import { addFilter } from '@wordpress/hooks';
import { registerBlockVariation } from '@wordpress/blocks';
import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Register Back to Top as a core/button variation
 */
registerBlockVariation( 'core/button', {
	name: 'back-to-top',
	title: __( 'Back to Top', 'ls-plugin' ),
	icon: 'arrow-up',
	description: __(
		'A button that scrolls to the top of the page with smooth animation.',
		'ls-plugin'
	),
	attributes: {
		text: __( 'Back to Top', 'ls-plugin' ),
		isBackToTop: true,
		backToTopPositionMode: 'scroll',
		backToTopScrollThreshold: 50,
	},
	isActive: ( blockAttributes ) => blockAttributes.isBackToTop === true,
} );

/**
 * Add back-to-top attributes to core/button
 */
addFilter(
	'blocks.registerBlockType',
	'ls-plugin/add-back-to-top-attributes',
	( settings ) => {
		if ( settings.name !== 'core/button' ) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
				isBackToTop: {
					type: 'boolean',
					default: false,
				},
				backToTopPositionMode: {
					type: 'string',
					default: 'scroll',
				},
				backToTopScrollThreshold: {
					type: 'number',
					default: 50,
				},
			},
		};
	}
);

/**
 * Add back-to-top inspector controls
 */
const withBackToTopControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { attributes, setAttributes, name } = props;

		// Only apply to core/button blocks with isBackToTop enabled
		if ( name !== 'core/button' || ! attributes.isBackToTop ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Back to Top Settings', 'ls-plugin' ) }
						initialOpen={ true }
					>
						<SelectControl
							label={ __( 'Position Mode', 'ls-plugin' ) }
							value={ attributes.backToTopPositionMode || 'scroll' }
							options={ [
								{
									label: __( 'Inline (Scroll)', 'ls-plugin' ),
									value: 'scroll',
								},
								{
									label: __( 'Sticky (Fixed, Center)', 'ls-plugin' ),
									value: 'sticky',
								},
								{
									label: __( 'Fixed (Bottom Right)', 'ls-plugin' ),
									value: 'fixed',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( { backToTopPositionMode: value } )
							}
							help={ __(
								'Choose how the button is positioned. Sticky and Fixed modes support scroll-based visibility.',
								'ls-plugin'
							) }
						/>
						{ ( attributes.backToTopPositionMode === 'sticky' || attributes.backToTopPositionMode === 'fixed' ) && (
							<RangeControl
								label={ __( 'Visibility Threshold (%)', 'ls-plugin' ) }
								value={ attributes.backToTopScrollThreshold ?? 75 }
								onChange={ ( value ) =>
									setAttributes( { backToTopScrollThreshold: value } )
								}
								min={ 0 }
								max={ 100 }
								step={ 5 }
								help={ sprintf(
									/* translators: %d: threshold percentage */
									__(
										'Button appears after scrolling %d%% of the page.',
										'ls-plugin'
									),
									attributes.backToTopScrollThreshold ?? 75
								) }
							/>
						) }
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'withBackToTopControls' );

addFilter(
	'editor.BlockEdit',
	'ls-plugin/with-back-to-top-controls',
	withBackToTopControls
);

/**
 * Add back-to-top data attributes to saved button markup
 */
addFilter(
	'blocks.getSaveContent.extraProps',
	'ls-plugin/back-to-top-save-props',
	( extraProps, blockType, attributes ) => {
		if ( blockType.name !== 'core/button' || ! attributes.isBackToTop ) {
			return extraProps;
		}

		const classes = [ extraProps.className, 'is-back-to-top' ]
			.filter( Boolean )
			.join( ' ' );

		const props = {
			...extraProps,
			className: classes,
			'data-back-to-top-mode': attributes.backToTopPositionMode || 'scroll',
		};

		// Add scroll threshold for sticky and fixed modes
		if ( attributes.backToTopPositionMode === 'sticky' || attributes.backToTopPositionMode === 'fixed' ) {
			props[ 'data-scroll-threshold' ] = attributes.backToTopScrollThreshold ?? 75;
		}

		return props;
	}
);
