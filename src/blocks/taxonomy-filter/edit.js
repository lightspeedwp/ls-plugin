/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
	useBlockProps,
	BlockControls,
	AlignmentToolbar,
	InspectorControls,
	__experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
	withColors,
} from '@wordpress/block-editor';

import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
	ToolbarDropdownMenu,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';

import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import {
	justifyLeft,
	justifyCenter,
	justifyRight,
	justifySpaceBetween,
} from '@wordpress/icons';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
function Edit( {
	attributes,
	setAttributes,
	buttonTextColor,
	setButtonTextColor,
	buttonBackgroundColor,
	setButtonBackgroundColor,
	hoverButtonTextColor,
	setHoverButtonTextColor,
	hoverButtonBackgroundColor,
	setHoverButtonBackgroundColor,
	activeButtonTextColor,
	setActiveButtonTextColor,
	activeButtonBackgroundColor,
	setActiveButtonBackgroundColor,
	style,
	clientId,
} ) {
	const {
		taxonomy,
		filterType,
		showCount,
		allItemsText,
		orderBy,
		textAlign,
		justification,
		limitVisibleItems,
		visibleItemsNumber,
		expandText,
		collapseText,
		customButtonTextColor,
		customButtonBackgroundColor,
		customHoverButtonTextColor,
		customHoverButtonBackgroundColor,
		customActiveButtonTextColor,
		customActiveButtonBackgroundColor,
		buttonBorder,
		buttonBorderRadius,
	} = attributes;

	const [ terms, setTerms ] = useState( null );
	const [ isLoaded, setIsLoaded ] = useState( false );
	const [ taxonomies, setTaxonomies ] = useState( [] );

	// Fetch available taxonomies
	useEffect( () => {
		apiFetch( { path: '/wp/v2/taxonomies' } ).then(
			( response ) => {
				const taxOptions = [];
				Object.entries( response ).forEach( ( [ slug, tax ] ) => {
					if (
						! [ 'wp_pattern_category', 'nav_menu' ].includes( slug )
					) {
						taxOptions.push( {
							label: tax.name,
							value: slug,
						} );
					}
				} );
				setTaxonomies( taxOptions );
			},
			( error ) => {
				console.error( 'Error loading taxonomies:', error );
			}
		);
	}, [] );

	// Fetch terms for current taxonomy
	useEffect( () => {
		if ( ! taxonomy?.rest_base ) {
			return;
		}

		const order = orderBy === 'count' ? 'desc' : 'asc';
		apiFetch( {
			path: addQueryArgs( `/wp/v2/${ taxonomy.rest_base }`, {
				hide_empty: false,
				orderby: orderBy,
				order,
				per_page: 100,
			} ),
		} ).then(
			( response ) => {
				setIsLoaded( true );
				setTerms( response );
			},
			( error ) => {
				setIsLoaded( true );
				console.error( 'Error loading terms:', error );
			}
		);
	}, [ taxonomy, orderBy ] );

	const sourceStyles = style || {};
	const innerSpacingStyles = Object.entries( sourceStyles ).reduce(
		( acc, [ key, value ] ) => {
			if ( /^(margin|padding)/.test( key ) ) {
				acc[ key ] = value;
			}

			return acc;
		},
		{}
	);

	const blockClasses = [
		`taxonomy-filter--${ filterType }`,
		filterType === 'buttons' && 'is-layout-flex',
		filterType === 'buttons' && 'wp-block-buttons',
		filterType === 'buttons' &&
			justification &&
			`is-content-justification-${ justification }`,
		filterType === 'buttons' &&
			( buttonTextColor.color || customButtonTextColor ) &&
			'has-button-text-color',
		filterType === 'buttons' &&
			( buttonBackgroundColor.color || customButtonBackgroundColor ) &&
			'has-button-background-color',
		filterType === 'buttons' &&
			( hoverButtonTextColor.color || customHoverButtonTextColor ) &&
			'has-hover-button-text-color',
		filterType === 'buttons' &&
			( hoverButtonBackgroundColor.color ||
				customHoverButtonBackgroundColor ) &&
			'has-hover-button-background-color',
		filterType === 'buttons' &&
			( activeButtonTextColor.color || customActiveButtonTextColor ) &&
			'has-active-button-text-color',
		filterType === 'buttons' &&
			( activeButtonBackgroundColor.color ||
				customActiveButtonBackgroundColor ) &&
			'has-active-button-background-color',
		filterType === 'buttons' && buttonBorder?.width && 'has-button-border',
		filterType === 'buttons' &&
			buttonBorderRadius &&
			'has-button-border-radius',
		filterType !== 'buttons' &&
			textAlign &&
			`has-text-align-${ textAlign }`,
	]
		.filter( Boolean )
		.join( ' ' );

	const blockStyles = Object.entries( sourceStyles ).reduce(
		( acc, [ key, value ] ) => {
			if ( ! /^(margin|padding)/.test( key ) ) {
				acc[ key ] = value;
			}

			return acc;
		},
		{}
	);

	// Explicitly remove spacing from wrapper by resetting all margin/padding properties.
	blockStyles.margin = 0;
	blockStyles.padding = 0;
	blockStyles.marginTop = 'initial';
	blockStyles.marginRight = 'initial';
	blockStyles.marginBottom = 'initial';
	blockStyles.marginLeft = 'initial';
	blockStyles.paddingTop = 'initial';
	blockStyles.paddingRight = 'initial';
	blockStyles.paddingBottom = 'initial';
	blockStyles.paddingLeft = 'initial';

	// Apply button styles
	if ( filterType === 'buttons' ) {
		if ( buttonTextColor.color || customButtonTextColor ) {
			blockStyles[ '--button-text-color' ] =
				buttonTextColor.color || customButtonTextColor;
		}
		if ( buttonBackgroundColor.color || customButtonBackgroundColor ) {
			blockStyles[ '--button-background-color' ] =
				buttonBackgroundColor.color || customButtonBackgroundColor;
		}
		if ( hoverButtonTextColor.color || customHoverButtonTextColor ) {
			blockStyles[ '--hover-button-text-color' ] =
				hoverButtonTextColor.color || customHoverButtonTextColor;
		}
		if (
			hoverButtonBackgroundColor.color ||
			customHoverButtonBackgroundColor
		) {
			blockStyles[ '--hover-button-background-color' ] =
				hoverButtonBackgroundColor.color ||
				customHoverButtonBackgroundColor;
		}
		if ( activeButtonTextColor.color || customActiveButtonTextColor ) {
			blockStyles[ '--active-button-text-color' ] =
				activeButtonTextColor.color || customActiveButtonTextColor;
		}
		if (
			activeButtonBackgroundColor.color ||
			customActiveButtonBackgroundColor
		) {
			blockStyles[ '--active-button-background-color' ] =
				activeButtonBackgroundColor.color ||
				customActiveButtonBackgroundColor;
		}
		if ( buttonBorder?.width ) {
			blockStyles[ '--button-border' ] = `${ buttonBorder.width } solid var(--wp--custom--color--button--fill--border, currentColor)`;
		}
		if ( buttonBorderRadius ) {
			blockStyles[ '--button-border-radius' ] = buttonBorderRadius;
		}
	}

	const blockProps = useBlockProps( {
		className: blockClasses,
		style: blockStyles,
	} );

	const justificationIcons = {
		left: justifyLeft,
		center: justifyCenter,
		right: justifyRight,
		'space-between': justifySpaceBetween,
	};

	const handleTaxonomyChange = async ( taxSlug ) => {
		try {
			const response = await apiFetch( {
				path: `/wp/v2/taxonomies/${ taxSlug }`,
			} );
			setAttributes( {
				taxonomy: {
					name: response.name,
					all_items: response.labels?.all_items || 'All Items',
					slug: response.slug,
					rest_base: response.rest_base,
				},
			} );
		} catch ( error ) {
			console.error( 'Error fetching taxonomy:', error );
		}
	};

	return (
		<>
			<BlockControls group="block">
				{ filterType === 'buttons' ? (
					<ToolbarDropdownMenu
						icon={
							justificationIcons[ justification ] ||
							justifyLeft
						}
						label={ __( 'Change items justification' ) }
						controls={ [
							{
								title: __( 'Justify items left' ),
								icon: justifyLeft,
								isActive: justification === 'left',
								onClick: () =>
									setAttributes( {
										justification:
											justification === 'left'
												? null
												: 'left',
									} ),
							},
							{
								title: __( 'Justify items center' ),
								icon: justifyCenter,
								isActive: justification === 'center',
								onClick: () =>
									setAttributes( {
										justification:
											justification === 'center'
												? null
												: 'center',
									} ),
							},
							{
								title: __( 'Justify items right' ),
								icon: justifyRight,
								isActive: justification === 'right',
								onClick: () =>
									setAttributes( {
										justification:
											justification === 'right'
												? null
												: 'right',
									} ),
							},
							{
								title: __( 'Space between items' ),
								icon: justifySpaceBetween,
								isActive: justification === 'space-between',
								onClick: () =>
									setAttributes( {
										justification:
											justification === 'space-between'
												? null
												: 'space-between',
									} ),
							},
						] }
					/>
				) : (
					<AlignmentToolbar
						value={ textAlign }
						onChange={ ( value ) =>
							setAttributes( { textAlign: value } )
						}
					/>
				) }
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
					<ToggleGroupControl
						label={ __( 'Filter type', 'ls-plugin' ) }
						value={ filterType }
						onChange={ ( value ) =>
							setAttributes( { filterType: value } )
						}
						isBlock
					>
						<ToggleGroupControlOption
							value="dropdown"
							label={ __( 'Dropdown', 'ls-plugin' ) }
						/>
						<ToggleGroupControlOption
							value="buttons"
							label={ __( 'Buttons', 'ls-plugin' ) }
						/>
					</ToggleGroupControl>

					<ToggleControl
						label={ __( 'Show post counts' ) }
						checked={ showCount }
						onChange={ () =>
							setAttributes( { showCount: ! showCount } )
						}
					/>

					<SelectControl
						label={ __( 'Taxonomy' ) }
						value={ taxonomy.slug }
						options={ taxonomies }
						onChange={ handleTaxonomyChange }
						__nextHasNoMarginBottom
					/>

					<TextControl
						label={ __( 'All items text', 'ls-plugin' ) }
						value={ allItemsText }
						onChange={ ( value ) =>
							setAttributes( { allItemsText: value } )
						}
					/>

					<SelectControl
						label={ __( 'Order by' ) }
						value={ orderBy }
						options={ [
							{ label: __( 'A → Z' ), value: 'name' },
							{
								label: __( 'Post count', 'ls-plugin' ),
								value: 'count',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { orderBy: value } )
						}
					/>

					{ filterType === 'buttons' && (
						<>
							<ToggleControl
								label={ __(
									'Limit number of visible items'
								) }
								checked={ limitVisibleItems }
								onChange={ () =>
									setAttributes( {
										limitVisibleItems: ! limitVisibleItems,
									} )
								}
							/>

							{ limitVisibleItems && (
								<>
									<NumberControl
										labelPosition="side"
										min={ 1 }
										label={ __(
											'Number of visible items',
											'ls-plugin'
										) }
										value={ visibleItemsNumber }
										onChange={ ( value ) =>
											setAttributes( {
												visibleItemsNumber:
													parseInt( value ),
											} )
										}
									/>

									<TextControl
										label={ __(
											'Expand button text',
											'ls-plugin'
										) }
										value={ expandText }
										onChange={ ( value ) =>
											setAttributes( {
												expandText: value,
											} )
										}
										help={ __(
											'The [number] shortcode will be replaced with the actual number of hidden items.',
											'ls-plugin'
										) }
									/>

									<TextControl
										label={ __(
											'Collapse button text',
											'ls-plugin'
										) }
										value={ collapseText }
										onChange={ ( value ) =>
											setAttributes( {
												collapseText: value,
											} )
										}
									/>
								</>
							) }
						</>
					) }
				</PanelBody>
			</InspectorControls>

			{ filterType === 'buttons' && (
				<InspectorControls group="color">
					<ColorGradientSettingsDropdown
						panelId={ clientId }
						__experimentalIsRenderedInSidebar
						settings={ [
							{
								label: __( 'Button text', 'ls-plugin' ),
								colorValue:
									buttonTextColor.color ||
									customButtonTextColor,
								onColorChange: ( value ) => {
									setButtonTextColor( value );
									setAttributes( {
										customButtonTextColor: value,
									} );
								},
							},
							{
								label: __(
									'Button background',
									'ls-plugin'
								),
								colorValue:
									buttonBackgroundColor.color ||
									customButtonBackgroundColor,
								onColorChange: ( value ) => {
									setButtonBackgroundColor( value );
									setAttributes( {
										customButtonBackgroundColor: value,
									} );
								},
							},
							{
								label: __(
									'Hover button text',
									'ls-plugin'
								),
								colorValue:
									hoverButtonTextColor.color ||
									customHoverButtonTextColor,
								onColorChange: ( value ) => {
									setHoverButtonTextColor( value );
									setAttributes( {
										customHoverButtonTextColor: value,
									} );
								},
							},
							{
								label: __(
									'Hover button background',
									'ls-plugin'
								),
								colorValue:
									hoverButtonBackgroundColor.color ||
									customHoverButtonBackgroundColor,
								onColorChange: ( value ) => {
									setHoverButtonBackgroundColor( value );
									setAttributes( {
										customHoverButtonBackgroundColor:
											value,
									} );
								},
							},
							{
								label: __(
									'Active button text',
									'ls-plugin'
								),
								colorValue:
									activeButtonTextColor.color ||
									customActiveButtonTextColor,
								onColorChange: ( value ) => {
									setActiveButtonTextColor( value );
									setAttributes( {
										customActiveButtonTextColor: value,
									} );
								},
							},
							{
								label: __(
									'Active button background',
									'ls-plugin'
								),
								colorValue:
									activeButtonBackgroundColor.color ||
									customActiveButtonBackgroundColor,
								onColorChange: ( value ) => {
									setActiveButtonBackgroundColor( value );
									setAttributes( {
										customActiveButtonBackgroundColor:
											value,
									} );
								},
							},
						] }
						hasColorsOrGradients={ false }
						disableCustomColors={ false }
					/>
				</InspectorControls>
			) }

			<div { ...blockProps }>
				{ ! isLoaded && <span>{ __( 'Loading...' ) }</span> }

				{ isLoaded && terms && filterType === 'dropdown' && (
					<select style={ innerSpacingStyles }>
						<option>
							{ allItemsText || taxonomy.all_items }
						</option>
						{ terms.map( ( term ) => (
							<option key={ term.id } value={ term.id }>
								{ showCount
									? `${ term.name } (${ term.count })`
									: term.name }
							</option>
						) ) }
					</select>
				) }

				{ isLoaded && terms && filterType === 'buttons' && (
					<>
						<a
							className="wp-element-button taxonomy-filter-current"
							style={ innerSpacingStyles }
						>
							{ allItemsText || taxonomy.all_items }
						</a>
						{ terms
							.slice(
								0,
								limitVisibleItems
									? visibleItemsNumber
									: terms.length
							)
							.map( ( term ) => (
								<a
									key={ term.id }
									className="wp-element-button"
									style={ innerSpacingStyles }
								>
									{ term.name }
									{ showCount && ` (${ term.count })` }
								</a>
							) ) }
						{ limitVisibleItems &&
							terms.length > visibleItemsNumber && (
								<button className="taxonomy-filter-show-more">
									{ expandText.replace(
										'[number]',
										terms.length - visibleItemsNumber
									) }
								</button>
							) }
					</>
				) }
			</div>
		</>
	);
}

export default withColors(
	'buttonTextColor',
	'buttonBackgroundColor',
	'hoverButtonTextColor',
	'hoverButtonBackgroundColor',
	'activeButtonTextColor',
	'activeButtonBackgroundColor'
)( Edit );
