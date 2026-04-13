( function () {
	'use strict';

	var linkedBlockSelector = '.ls-plugin-linkable-block.is-linked';
	var interactiveSelector = 'a, button, input, select, textarea, summary, [role="button"]';

	function handleLinkedBlockClick( event ) {
		var linkedBlock = event.target.closest( linkedBlockSelector );

		if ( ! linkedBlock ) {
			return;
		}

		var expandedLink = linkedBlock.querySelector( '[data-expand-click-area]' );

		if ( ! expandedLink ) {
			return;
		}

		if ( event.target === expandedLink || event.target.closest( interactiveSelector ) ) {
			return;
		}

		event.preventDefault();
		expandedLink.click();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			document.addEventListener( 'click', handleLinkedBlockClick );
		} );
	} else {
		document.addEventListener( 'click', handleLinkedBlockClick );
	}
}() );