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
 * Edit component for the carousel slide block.
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

	const className = [
		attributes.verticalAlign &&
			`are-vertically-aligned-${ attributes.verticalAlign }`,
	]
		.filter( Boolean )
		.join( ' ' );

	// Don't apply slide width styles in editor - the grid handles layout
	const blockProps = useBlockProps( {
		className,
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

