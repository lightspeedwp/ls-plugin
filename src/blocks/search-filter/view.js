import { getContext, getElement, store } from '@wordpress/interactivity';

let debounceTimer = null;

store( 'lsPluginSearchFilter', {
	actions: {
		search( event ) {
			event?.preventDefault?.();
			const { ref } = getElement();
			const context = getContext();
			const searchValue = ref?.value ?? '';
			const searchUrl = new URL( window.location );

			clearTimeout( debounceTimer );

			debounceTimer = setTimeout( async () => {
				let routerActions = null;

				try {
					const routerModule = await import( '@wordpress/interactivity-router' );
					routerActions = routerModule?.actions ?? null;
				} catch ( error ) {
					routerActions = null;
				}

				if ( ! routerActions && window?.wp?.interactivityRouter?.actions ) {
					routerActions = window.wp.interactivityRouter.actions;
				}

				if ( searchValue !== '' ) {
					searchUrl.searchParams.set( context.searchKey, searchValue );
				} else {
					searchUrl.searchParams.delete( context.searchKey );
				}

				if ( routerActions?.navigate ) {
					routerActions.navigate( searchUrl.toString() );
					return;
				}

				window.location.assign( searchUrl.toString() );
			}, 1000 );
		},
	},
} );
