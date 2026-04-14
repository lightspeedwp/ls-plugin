import { NumberControl, PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { label, positionMode, scrollThreshold } = attributes;
	const blockProps = useBlockProps( {
		className: 'wp-block-button',
		'data-position-mode': positionMode,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Back to Top Settings', 'ls-plugin' ) }>
					<TextControl
						label={ __( 'Button Label', 'ls-plugin' ) }
						value={ label }
						onChange={ ( value ) =>
							setAttributes( { label: value } )
						}
						help={ __( 'Text displayed on the button.', 'ls-plugin' ) }
					/>
					<SelectControl
						label={ __( 'Position Mode', 'ls-plugin' ) }
						value={ positionMode }
						options={ [
							{
								label: __( 'Scroll with page (appears on scroll)', 'ls-plugin' ),
								value: 'scroll',
							},
							{
								label: __( 'Fixed to footer (always visible)', 'ls-plugin' ),
								value: 'fixed',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { positionMode: value } )
						}
						help={ __(
							'Choose how the button should be positioned on the page.',
							'ls-plugin'
						) }
					/>
					{ positionMode === 'scroll' && (
						<NumberControl
							label={ __( 'Scroll Threshold (%)', 'ls-plugin' ) }
							value={ scrollThreshold }
							onChange={ ( value ) =>
								setAttributes( { scrollThreshold: value } )
							}
							min={ 0 }
							max={ 100 }
							step={ 5 }
							help={ __(
								'Show button after scrolling this percentage of viewport height. Default: 50%',
								'ls-plugin'
							) }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<a href="#top" className="wp-block-button__link">
					{ label }
				</a>
			</div>
		</>
	);
}
