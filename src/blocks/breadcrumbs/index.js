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

registerBlockType( metadata.name, {
	icon: 'admin-links',
	edit: Edit,
	save,
} );
