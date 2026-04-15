import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

const WIDTH_UNIT_OPTIONS = [
	{ label: '%', value: '%' },
	{ label: 'px', value: 'px' },
];

function Edit( { attributes, setAttributes } ) {
	const {
		metaKey,
		placeholder,
		searchByCustomField,
		width,
		widthUnit,
	} = attributes;

	const maxWidth = '%' === widthUnit ? 100 : 1200;
	const blockProps = useBlockProps( {
		style: {
			width: `${ width }${ widthUnit }`,
		},
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Search Settings', 'ls-plugin' ) }
					initialOpen={ true }
				>
					<TextControl
						label={ __( 'Placeholder', 'ls-plugin' ) }
						value={ placeholder }
						onChange={ ( value ) => setAttributes( { placeholder: value } ) }
					/>
					<SelectControl
						label={ __( 'Width unit', 'ls-plugin' ) }
						value={ widthUnit }
						options={ WIDTH_UNIT_OPTIONS }
						onChange={ ( value ) => setAttributes( { widthUnit: value } ) }
					/>
					<RangeControl
						label={ __( 'Width', 'ls-plugin' ) }
						value={ width }
						min={ '%' === widthUnit ? 25 : 160 }
						max={ maxWidth }
						onChange={ ( value ) => setAttributes( { width: value } ) }
					/>
					<ToggleControl
						label={ __( 'Search by custom field', 'ls-plugin' ) }
						checked={ searchByCustomField }
						onChange={ () =>
							setAttributes( { searchByCustomField: ! searchByCustomField } )
						}
					/>
					{ searchByCustomField && (
						<TextControl
							label={ __( 'Meta key', 'ls-plugin' ) }
							value={ metaKey }
							onChange={ ( value ) => setAttributes( { metaKey: value } ) }
							help={ __(
								'Filter against a post meta value instead of the post title/content search.',
								'ls-plugin'
							) }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<input
					className="wp-block-ls-plugin-search-filter__input"
					type="search"
					value={ placeholder }
					placeholder={ __( 'Search…', 'ls-plugin' ) }
					onChange={ ( event ) =>
						setAttributes( { placeholder: event.target.value } )
					}
				/>
			</div>
		</>
	);
}

export default Edit;