/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	ToggleControl,
	Button,
	TextControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';

/**
 * Calculate the maximum slides to show considering breakpoints.
 *
 * @param {Object} attributes Block attributes.
 * @return {number} Maximum slides to show.
 */
function getMaxSlidesToShow( attributes ) {
	let maxSlides = attributes.slidesToShow;
	const breakpoints = attributes.breakpoints || [];

	breakpoints.forEach( ( breakpoint ) => {
		if ( breakpoint.breakpoint && breakpoint.slidesToShow > maxSlides ) {
			maxSlides = breakpoint.slidesToShow;
		}
	} );

	return maxSlides;
}

/**
 * Edit component for the carousel block.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Function to set attributes.
 * @param {string}   props.clientId      Block client ID.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes, clientId } ) {
	const { hasChildBlocks } = useSelect(
		( select ) => {
			const { getBlockCount } = select( blockEditorStore );
			return {
				hasChildBlocks: getBlockCount( clientId ) > 0,
			};
		},
		[ clientId ]
	);

	const [ breakpoints, setBreakpoints ] = useState(
		attributes.breakpoints || []
	);

	const className = [ 'ls-show-scrollbar' ].filter( Boolean ).join( ' ' );

	const blockProps = useBlockProps( {
		className,
	} );

	const { templateLock } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Carousel Settings', 'ls-plugin' ) }
					initialOpen={ true }
				>
					<RangeControl
						label={ __( 'Slides to show', 'ls-plugin' ) }
						value={ attributes.slidesToShow }
						onChange={ ( value ) =>
							setAttributes( { slidesToShow: value } )
						}
						min={ 1 }
						max={ 5 }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<RangeControl
						label={ __( 'Columns gap', 'ls-plugin' ) }
						value={ attributes.columnGap }
						onChange={ ( value ) =>
							setAttributes( { columnGap: value } )
						}
						min={ 0 }
						max={ 100 }
						step={ 10 }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<ToggleControl
						label={ __( 'Dots navigation', 'ls-plugin' ) }
						onChange={ () =>
							setAttributes( {
								pagination: ! attributes.pagination,
							} )
						}
						checked={ attributes.pagination }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Arrows navigation', 'ls-plugin' ) }
						onChange={ () =>
							setAttributes( {
								navigation: ! attributes.navigation,
							} )
						}
						checked={ attributes.navigation }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Autoplay', 'ls-plugin' ) }
						onChange={ () =>
							setAttributes( { autoplay: ! attributes.autoplay } )
						}
						checked={ attributes.autoplay }
						__nextHasNoMarginBottom
					/>
					{ attributes.autoplay && (
						<>
							<RangeControl
								label={ __( 'Delay', 'ls-plugin' ) }
								value={ attributes.delay }
								onChange={ ( value ) =>
									setAttributes( { delay: value } )
								}
								min={ 500 }
								max={ 9999 }
								step={ 500 }
								help={ __(
									'Delay between transitions (ms)',
									'ls-plugin'
								) }
								__nextHasNoMarginBottom
								__next40pxDefaultSize
							/>
							<ToggleControl
								label={ __( 'Infinite', 'ls-plugin' ) }
								onChange={ () =>
									setAttributes( { loop: ! attributes.loop } )
								}
								checked={ attributes.loop }
								help={ __(
									'Total number of slides must be more or equal to SLIDES TO SHOW × 2',
									'ls-plugin'
								) }
								__nextHasNoMarginBottom
							/>
						</>
					) }
				</PanelBody>
				<PanelBody
					title={ __( 'Responsive', 'ls-plugin' ) }
					initialOpen={ false }
				>
					{ breakpoints.map( ( breakpoint, index ) => (
						<div key={ index } style={ { marginBottom: '30px' } }>
							<strong>
								{ __( 'Breakpoint', 'ls-plugin' ) }{ ' ' }
								{ index + 1 }
							</strong>
							<TextControl
								label={ __(
									'Min screen width (px)',
									'ls-plugin'
								) }
								value={ breakpoints[ index ].breakpoint || '' }
								type="number"
								min={ 100 }
								max={ 2000 }
								onChange={ ( value ) => {
									setBreakpoints( ( prev ) => {
										const updated = prev.map( ( bp, i ) =>
											i === index
												? {
														...bp,
														breakpoint: parseInt(
															value,
															10
														),
												  }
												: bp
										);
										setAttributes( {
											breakpoints: updated,
										} );
										return updated;
									} );
								} }
							/>
							<RangeControl
								label={ __( 'Slides to show', 'ls-plugin' ) }
								value={ breakpoints[ index ].slidesToShow }
								help={ __(
									'Number of slides to show at the minimum given screen width.',
									'ls-plugin'
								) }
								onChange={ ( value ) => {
									setBreakpoints( ( prev ) => {
										const updated = prev.map( ( bp, i ) =>
											i === index
												? { ...bp, slidesToShow: value }
												: bp
										);
										setAttributes( {
											breakpoints: updated,
										} );
										return updated;
									} );
								} }
								min={ 1 }
								max={ 5 }
								__nextHasNoMarginBottom
								__next40pxDefaultSize
							/>
							<Button
								isLink
								isDestructive
								onClick={ () => {
									setBreakpoints( ( prev ) => {
										const updated = prev.filter(
											( bp, i ) => i !== index
										);
										setAttributes( {
											breakpoints: updated,
										} );
										return updated;
									} );
								} }
							>
								{ __( 'Remove breakpoint', 'ls-plugin' ) }{ ' ' }
								{ index + 1 }
							</Button>
						</div>
					) ) }
					{ breakpoints.length < 3 && (
						<Button
							variant="secondary"
							onClick={ () => {
								setBreakpoints( ( prev ) => {
									const updated = [ ...prev, {} ];
									setAttributes( { breakpoints: updated } );
									return updated;
								} );
							} }
						>
							{ __( 'Add breakpoint', 'ls-plugin' ) }
						</Button>
					) }
				</PanelBody>
				<PanelBody
					title={ __( 'Animation', 'ls-plugin' ) }
					initialOpen={ false }
				>
					<RangeControl
						label={ __( 'Speed', 'ls-plugin' ) }
						value={ attributes.speed }
						onChange={ ( value ) =>
							setAttributes( { speed: value } )
						}
						min={ 100 }
						max={ 900 }
						step={ 50 }
						help={ __(
							'Duration of transition between slides (ms)',
							'ls-plugin'
						) }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<InnerBlocks
					orientation="horizontal"
					allowedBlocks={ [ 'ls-plugin/carousel-slide' ] }
					templateLock={ templateLock }
					renderAppender={ InnerBlocks.ButtonBlockAppender }
					placeholder={
						<div className="ls-carousel-placeholder">
							{ __( 'Click + to add slides', 'ls-plugin' ) }
						</div>
					}
				/>
			</div>
		</>
	);
}
