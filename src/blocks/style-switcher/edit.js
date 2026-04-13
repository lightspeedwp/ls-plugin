import { Disabled, PanelBody, SelectControl } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import { MoonIcon, SunIcon } from './icons.js';

export default function Edit( { attributes, setAttributes } ) {
	const { size } = attributes;
	const sizeClass = size ? `is-${ size }` : '';
	const inputId = 'ls-plugin-style-switcher-input';
	const blockProps = useBlockProps( {
		className: sizeClass,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Switcher Settings', 'ls-plugin' ) }>
					<SelectControl
						label={ __( 'Size', 'ls-plugin' ) }
						value={ size }
						options={ [
							{
								label: __( 'Small', 'ls-plugin' ),
								value: 'small',
							},
							{
								label: __( 'Medium', 'ls-plugin' ),
								value: 'medium',
							},
							{
								label: __( 'Large', 'ls-plugin' ),
								value: 'large',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { size: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<Disabled>
					<label
						className="wp-block-ls-plugin-style-switcher__label"
						htmlFor={ inputId }
					>
						<input
							id={ inputId }
							type="checkbox"
							className="wp-block-ls-plugin-style-switcher__input"
							role="switch"
							aria-checked="false"
							aria-label={ __(
								'Switch to dark mode, currently light',
								'ls-plugin'
							) }
						/>
						<span className="wp-block-ls-plugin-style-switcher__track">
							<span className="wp-block-ls-plugin-style-switcher__selector">
								<span
									className="wp-block-ls-plugin-style-switcher__icon wp-block-ls-plugin-style-switcher__icon--light"
									aria-hidden="true"
								>
									<SunIcon />
								</span>
								<span
									className="wp-block-ls-plugin-style-switcher__icon wp-block-ls-plugin-style-switcher__icon--dark"
									aria-hidden="true"
								>
									<MoonIcon />
								</span>
							</span>
						</span>
					</label>
				</Disabled>
			</div>
		</>
	);
}
