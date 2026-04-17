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
			viewBox="-12 3 48 30"
		>
			<path d="M3.55271368e-15,32 C3.55271368e-15,34.206 1.794,36 4,36 L20,36 C22.206,36 24,34.206 24,32 L24,4 C24,1.794 22.206,0 20,0 L4,0 C1.794,0 3.55271368e-15,1.794 3.55271368e-15,4 L3.55271368e-15,32 Z M3,3 L20.9977503,3 L21,33 L3,33 L3,3 Z" />
		</svg>
	),
	edit: Edit,
	save,
} );
