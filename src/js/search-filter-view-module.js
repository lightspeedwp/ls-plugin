import { getContext, getElement, store } from '@wordpress/interactivity';

let debounceTimer = null;

store( 'lsPluginSearchFilter', {
	actions: {
		search() {
			const { ref } = getElement();
			const context = getContext();
			const searchValue = ref?.value ?? '';
			const searchUrl = new URL( window.location );

			clearTimeout( debounceTimer );

			debounceTimer = window.setTimeout( async () => {
				const { actions } = await import( '@wordpress/interactivity-router' );

				if ( searchValue ) {
					searchUrl.searchParams.set( context.searchKey, searchValue );
				} else {
					searchUrl.searchParams.delete( context.searchKey );
				}

				if ( actions?.navigate ) {
					actions.navigate( searchUrl.toString() );
					return;
				}

				window.location.assign( searchUrl.toString() );
			}, 500 );
		},
	},
} );
