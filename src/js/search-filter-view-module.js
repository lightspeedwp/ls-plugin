import { getContext, getElement, store } from '@wordpress/interactivity';

const debounceTimers = new WeakMap();

store( 'lsPluginSearchFilter', {
	actions: {
		search() {
			const { ref } = getElement();
			const context = getContext();
			const searchValue = ref?.value ?? '';
			const searchUrl = new URL( window.location );

			if ( ! ref ) {
				return;
			}

			clearTimeout( debounceTimers.get( ref ) );

			const timerId = setTimeout( async () => {
				const { actions } = await import( '@wordpress/interactivity-router' );

				if ( searchValue !== '' ) {
					searchUrl.searchParams.set( context.searchKey, searchValue );
				} else {
					searchUrl.searchParams.delete( context.searchKey );
				}

				actions.navigate( searchUrl.toString() );
			}, 400 );

			debounceTimers.set( ref, timerId );
		},
	},
} );