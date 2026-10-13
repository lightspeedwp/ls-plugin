/**
 * Post FAQs Variation for yoast/faq-block
 * Registers a "Post FAQs" variation of the Yoast FAQ block. On the front end
 * it is filled from the post's FAQ fields (see inc/class-faq-fields.php), so
 * the editor shows a placeholder instead of Yoast's question editor.
 *
 * This script is a dependency of Yoast's FAQ block script, so the attribute
 * filter below runs before Yoast registers the block.
 */

import { addFilter } from '@wordpress/hooks';
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';

const BLOCK_NAME = 'yoast/faq-block';

/**
 * Add the lsPostFaqs attribute to yoast/faq-block
 */
addFilter(
	'blocks.registerBlockType',
	'ls-plugin/add-post-faqs-attribute',
	( settings, name ) => {
		if ( name !== BLOCK_NAME ) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
				lsPostFaqs: {
					type: 'boolean',
					default: false,
				},
			},
		};
	}
);

/**
 * Register Post FAQs as a yoast/faq-block variation
 */
registerBlockVariation( BLOCK_NAME, {
	name: 'ls-post-faqs',
	title: __( 'Post FAQs', 'ls-plugin' ),
	icon: 'editor-help',
	description: __(
		"Shows the questions from this post's FAQ fields, with Yoast FAQ structured data. Nothing is shown if the post has no FAQs.",
		'ls-plugin'
	),
	keywords: [ __( 'faq', 'ls-plugin' ), __( 'questions', 'ls-plugin' ) ],
	attributes: {
		lsPostFaqs: true,
	},
	scope: [ 'inserter', 'transform' ],
	isActive: ( blockAttributes ) => blockAttributes.lsPostFaqs === true,
} );

/**
 * Placeholder shown in place of Yoast's question editor.
 */
function PostFaqsPlaceholder() {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<Placeholder
				icon="editor-help"
				label={ __( 'Post FAQs', 'ls-plugin' ) }
				instructions={ __(
					"The questions from the post's FAQ fields are shown here on the front end. Edit them in the FAQs panel below the post content. If the post has no FAQs, this block is hidden.",
					'ls-plugin'
				) }
			/>
		</div>
	);
}

/**
 * Replace the Yoast FAQ edit UI for the Post FAQs variation
 */
const withPostFaqsEdit = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( props.name !== BLOCK_NAME || ! props.attributes.lsPostFaqs ) {
			return <BlockEdit { ...props } />;
		}

		return <PostFaqsPlaceholder />;
	};
}, 'withPostFaqsEdit' );

addFilter( 'editor.BlockEdit', 'ls-plugin/post-faqs-edit', withPostFaqsEdit );
