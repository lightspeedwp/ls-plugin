import { getContext, getElement, store } from '@wordpress/interactivity';

let debounceTimer = null;

store( 'lsPluginSearchFilter', {
	actions: {
		async search() {
			const { ref } = getElement();
			const context = getContext();
			const searchValue = ref?.value ?? '';
			const searchUrl = new URL( window.location.href );

			clearTimeout( debounceTimer );

			debounceTimer = window.setTimeout( async () => {
				const { actions } = await import( '@wordpress/interactivity-router' );

				if ( searchValue ) {
					searchUrl.searchParams.set( context.searchKey, searchValue );
				} else {
					searchUrl.searchParams.delete( context.searchKey );
				}

				actions.navigate( searchUrl.toString() );
			}, 300 );
		},
	},
} );