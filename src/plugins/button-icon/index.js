import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { Button, PanelBody, PanelRow, ToggleControl, __experimentalGrid as Grid } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { BUTTON_ICONS } from './icons.js';

function joinClasses( ...classNames ) {
	return classNames.filter( Boolean ).join( ' ' );
}

function addAttributes( settings ) {
	if ( 'core/button' !== settings.name ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			lsButtonIcon: {
				type: 'string',
			},
			lsButtonIconPositionLeft: {
				type: 'boolean',
				default: false,
			},
		},
	};
}

addFilter(
	'blocks.registerBlockType',
	'ls-plugin/button-icons/add-attributes',
	addAttributes
);

function addInspectorControls( BlockEdit ) {
	return ( props ) => {
		if ( 'core/button' !== props.name ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;
		const { lsButtonIcon, lsButtonIconPositionLeft } = attributes;

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Button Icon', 'ls-plugin' ) }
						className="ls-plugin-button-icon-picker"
						initialOpen={ true }
					>
						<PanelRow>
							<Grid columns="4" gap="8px">
								{ BUTTON_ICONS.map( ( icon ) => (
									<Button
										key={ icon.value }
										label={ icon.label }
										isPressed={ lsButtonIcon === icon.value }
										onClick={ () =>
											setAttributes( {
												lsButtonIcon:
													lsButtonIcon === icon.value
														? undefined
														: icon.value,
											} )
										}
									>
										{ icon.icon }
									</Button>
								) ) }
							</Grid>
						</PanelRow>
						{ lsButtonIcon && (
							<PanelRow>
								<ToggleControl
									label={ __( 'Show icon on left', 'ls-plugin' ) }
									checked={ lsButtonIconPositionLeft }
									onChange={ () =>
										setAttributes( {
											lsButtonIconPositionLeft:
												! lsButtonIconPositionLeft,
										} )
									}
								/>
							</PanelRow>
						) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}

addFilter(
	'editor.BlockEdit',
	'ls-plugin/button-icons/add-inspector-controls',
	addInspectorControls
);

const addClasses = createHigherOrderComponent( ( BlockListBlock ) => {
	return ( props ) => {
		const { name, attributes } = props;

		if ( 'core/button' !== name || ! attributes?.lsButtonIcon ) {
			return <BlockListBlock { ...props } />;
		}

		const classes = joinClasses(
			props.className,
			'has-ls-button-icon',
			attributes.lsButtonIcon
				? `has-ls-button-icon-${ attributes.lsButtonIcon }`
				: '',
			attributes.lsButtonIconPositionLeft
				? 'has-ls-button-icon-position-left'
				: ''
		);

		return <BlockListBlock { ...props } className={ classes } />;
	};
}, 'addClasses' );

addFilter(
	'editor.BlockListBlock',
	'ls-plugin/button-icons/add-classes',
	addClasses
);

function addSaveProps( extraProps, blockType, attributes ) {
	if ( 'core/button' !== blockType.name || ! attributes?.lsButtonIcon ) {
		return extraProps;
	}

	return {
		...extraProps,
		className: joinClasses(
			extraProps.className,
			'has-ls-button-icon',
			attributes.lsButtonIcon
				? `has-ls-button-icon-${ attributes.lsButtonIcon }`
				: '',
			attributes.lsButtonIconPositionLeft
				? 'has-ls-button-icon-position-left'
				: ''
		),
	};
}

addFilter(
	'blocks.getSaveContent.extraProps',
	'ls-plugin/button-icons/add-save-props',
	addSaveProps
);
