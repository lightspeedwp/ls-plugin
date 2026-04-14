/**
 * Back to Top Variation for core/button
 * Registers a "Back to Top" variation of the core Button block with smooth scrolling.
 */

import { addFilter } from '@wordpress/hooks';
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Register Back to Top as a core/button variation
 */
registerBlockVariation( 'core/button', {
	name: 'back-to-top',
	title: 'Back to Top',
	icon: 'arrow-up',
	description: 'A button that scrolls to the top of the page with smooth animation.',
	attributes: {
		text: 'Back to Top',
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
 * Add back-to-top inspector controls and editor classes
 * We don't need to override BlockEdit - the attributes filter handles everything
 */

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

		return {
			...extraProps,
			'data-is-back-to-top': 'true',
			'data-back-to-top-mode': attributes.backToTopPositionMode || 'scroll',
			'data-back-to-top-threshold': attributes.backToTopScrollThreshold || 50,
		};
	}
);
