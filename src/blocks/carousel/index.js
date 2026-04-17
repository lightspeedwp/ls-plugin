/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit.js';
import save from './save.js';
import metadata from './block.json';
import './style.scss';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	icon: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			width="24"
			height="24"
			viewBox="-4 3 48 32"
		>
			<path d="M4,34 L8,34 C8,36.206 9.794,38 12,38 L28,38 C30.206,38 32,36.206 32,34 L36,34 C38.206,34 40,32.206 40,30 L40,10 C40,7.794 38.206,6 36,6 L32,6 C32,3.794 30.206,2 28,2 L12,2 C9.794,2 8,3.794 8,6 L4,6 C1.794,6 0,7.794 0,10 L0,30 C0,32.206 1.794,34 4,34 Z M37,9 L37,31 L32,31 L32,9 L37,9 Z M11,5 L28.9977503,5 L29,35 L11,35 L11,5 Z M3,9 L8,9 L8,31 L3,31 L3,9 Z" />
		</svg>
	),
	edit: Edit,
	save,
} );
