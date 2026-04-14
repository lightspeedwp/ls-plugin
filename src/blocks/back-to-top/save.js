import { useBlockProps } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { label, positionMode, scrollThreshold } = attributes;

	return (
		<div
			{ ...useBlockProps.save( {
				className: 'wp-block-button',
				'data-position-mode': positionMode,
				'data-scroll-threshold': scrollThreshold,
			} ) }
		>
			<a href="#wp-site-blocks" className="wp-block-button__link wp-block-ls-plugin-back-to-top__link">
				{ label }
			</a>
		</div>
	);
}
