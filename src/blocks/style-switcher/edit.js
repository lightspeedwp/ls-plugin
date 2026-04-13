import { Disabled, PanelBody, SelectControl } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import { MoonIcon, SunIcon } from './icons.js';

export default function Edit( { attributes, setAttributes } ) {
	const { size, darkStyleSlug } = attributes;
	const sizeClass = size ? `is-${ size }` : '';
	const inputId = 'ls-plugin-style-switcher-input';
	const blockData = window.lsPluginStyleSwitcherBlockData || {};
	const availableStyles = Array.isArray( blockData.availableStyles )
		? blockData.availableStyles
		: [];
	const defaultDarkStyleSlug = blockData.defaultDarkStyleSlug || 'dark';
	const selectedDarkStyle = darkStyleSlug || defaultDarkStyleSlug;
	const blockProps = useBlockProps( {
		className: sizeClass,
		'data-style-variation': selectedDarkStyle,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Switcher Settings', 'ls-plugin' ) }>
					<SelectControl
						label={ __( 'Dark Style Variation', 'ls-plugin' ) }
						value={ selectedDarkStyle }
						options={ availableStyles }
						onChange={ ( value ) =>
							setAttributes( { darkStyleSlug: value } )
						}
						help={
							availableStyles.length
								? __(
									'Select which registered style variation should be used when dark mode is enabled.',
									'ls-plugin'
							  )
								: __(
									'No style variations found in the active theme styles directory.',
									'ls-plugin'
							  )
						}
						disabled={ ! availableStyles.length }
					/>
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
							data-style-variation={ selectedDarkStyle }
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
