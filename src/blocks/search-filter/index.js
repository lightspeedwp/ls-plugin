import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import edit from './edit.js';
import save from './save.js';
import './style.scss';

registerBlockType( metadata.name, {
	icon: 'search',
	edit,
	save,
} );