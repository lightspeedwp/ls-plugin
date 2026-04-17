/**
 * WordPress dependencies
 */
import {
	useBlockProps,
	InnerBlocks,
	BlockControls,
	BlockVerticalAlignmentToolbar,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';

/**
 * Calculate slide width based on parent carousel settings.
 *
 * @param {Object} parentAttributes Parent carousel attributes.
 * @return {Object} Style object for the slide.
 */
function getSlideStyle( parentAttributes ) {
	if ( ! parentAttributes ) {
		return {};
	}

	const slidesToShow = parentAttributes.slidesToShow || 1;
	const columnGap = parentAttributes.columnGap || 0;

	// Calculate the percentage width for each slide.
	const widthPercent = ( 100 / slidesToShow ).toFixed( 4 );

	// Calculate the gap adjustment for each slide.
	const gapAdjustment = ( columnGap * ( slidesToShow - 1 ) ) / slidesToShow;

	return {
		flexBasis: `calc( ${ widthPercent }% - ${ gapAdjustment }px )`,
		maxWidth: `calc( ${ widthPercent }% - ${ gapAdjustment }px )`,
		minWidth: `calc( ${ widthPercent }% - ${ gapAdjustment }px )`,
		marginLeft: `${ columnGap }px`,
	};
}

/**
 * Edit component for the carousel slide block.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Function to set attributes.
 * @param {string}   props.clientId      Block client ID.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes, clientId } ) {
	const { hasChildBlocks, parentAttributes } = useSelect(
		( select ) => {
			const {
				getBlockCount,
				getBlockParentsByBlockName,
				getBlockAttributes,
			} = select( blockEditorStore );
			const parentIds = getBlockParentsByBlockName(
				clientId,
				'ls-plugin/carousel'
			);
			return {
				hasChildBlocks: getBlockCount( clientId ) > 0,
				parentAttributes: parentIds.length
					? getBlockAttributes( parentIds[ 0 ] )
					: null,
			};
		},
		[ clientId ]
	);

	const className = [
		attributes.verticalAlign &&
			`are-vertically-aligned-${ attributes.verticalAlign }`,
	]
		.filter( Boolean )
		.join( ' ' );

	const slideStyle = getSlideStyle( parentAttributes );

	const blockProps = useBlockProps( {
		className,
		style: slideStyle,
	} );

	return (
		<div { ...blockProps }>
			<BlockControls>
				<BlockVerticalAlignmentToolbar
					value={ attributes.verticalAlign }
					onChange={ ( value ) => {
						setAttributes( { verticalAlign: value } );
					} }
				/>
			</BlockControls>
			<InnerBlocks
				orientation="vertical"
				allowedBlocks={ [
					'core/paragraph',
					'core/heading',
					'core/image',
					'core/group',
					'core/quote',
					'core/pullquote',
					'core/video',
					'core/buttons',
					'core/button',
				] }
				templateLock={ false }
				renderAppender={
					! hasChildBlocks && InnerBlocks.ButtonBlockAppender
				}
			/>
		</div>
	);
}
