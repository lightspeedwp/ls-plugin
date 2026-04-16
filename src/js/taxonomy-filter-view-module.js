/**
 * Taxonomy Filter Block - Interactivity API View Module
 *
 * Handles client-side navigation for the taxonomy filter block.
 *
 * @package LS_Plugin
 */

import { store, getContext, getElement } from '@wordpress/interactivity';

// WeakMap for per-instance debounce timers.
const debounceTimers = new WeakMap();

store( 'lsPluginTaxonomyFilter', {
	actions: {
		/**
		 * Navigate to selected term (dropdown).
		 */
		*navigate() {
			const { ref } = getElement();
			const url = ref.value;

			// Dynamically import router only when needed.
			const { actions: routerActions } = yield import(
				'@wordpress/interactivity-router'
			);

			yield routerActions.navigate( url );
		},

		/**
		 * Toggle show more/less.
		 *
		 * @param {Event} event Click event.
		 */
		showMore( event ) {
			event.preventDefault();
			const context = getContext();
			const { ref } = getElement();

			context.isExpanded = ! context.isExpanded;

			// Update button text based on expanded state.
			const expandText = ref.dataset.expandText;
			const collapseText = ref.dataset.collapseText;

			context.showMoreText = context.isExpanded
				? collapseText
				: expandText;
		},
	},

	callbacks: {
		/**
		 * Initialize context on first render.
		 */
		initShowMoreText() {
			const context = getContext();
			const { ref } = getElement();

			if ( ! context.showMoreText && ref.dataset.expandText ) {
				context.showMoreText = ref.dataset.expandText;
			}
		},
	},
} );
