( function () {
	'use strict';

	var linkedBlockSelector = '.ls-plugin-linkable-block.is-linked';
	var interactiveSelector = [
		'a',
		'button',
		'input',
		'select',
		'textarea',
		'summary',
		'details',
		'label',
		'iframe',
		'[contenteditable="true"]',
		'[role="button"]',
		'[role="checkbox"]',
		'[role="link"]',
		'[role="menuitem"]',
		'[role="option"]',
		'[role="radio"]',
		'[role="switch"]',
		'[tabindex]:not([tabindex="-1"])'
	].join( ', ' );

	function getEventTargetElement( event ) {
		if ( ! event.target ) {
			return null;
		}

		if ( 1 === event.target.nodeType ) {
			return event.target;
		}

		return event.target.parentElement || null;
	}

	function getExpandedLink( linkedBlock ) {
		return linkedBlock.querySelector( '[data-expand-click-area]' );
	}

	function hasActiveTextSelection() {
		var selection = window.getSelection ? window.getSelection() : null;

		return !! ( selection && String( selection ).trim() );
	}

	function getWindowFeatures( expandedLink ) {
		var rel = expandedLink.getAttribute( 'rel' ) || '';
		var features = [];

		if ( rel.indexOf( 'noopener' ) !== -1 ) {
			features.push( 'noopener' );
		}

		if ( rel.indexOf( 'noreferrer' ) !== -1 ) {
			features.push( 'noreferrer' );
		}

		return features.join( ',' );
	}

	function navigateToExpandedLink( expandedLink, event ) {
		var href = expandedLink.href;
		var openInNewTab = '_blank' === expandedLink.getAttribute( 'target' ) || event.metaKey || event.ctrlKey || event.shiftKey;

		if ( ! href ) {
			return;
		}

		if ( openInNewTab ) {
			window.open( href, '_blank', getWindowFeatures( expandedLink ) || undefined );
			return;
		}

		window.location.assign( href );
	}

	function shouldIgnorePointerActivation( event, targetElement ) {
		if ( event.defaultPrevented ) {
			return true;
		}

		if ( targetElement.closest( interactiveSelector ) ) {
			return true;
		}

		if ( hasActiveTextSelection() ) {
			return true;
		}

		return false;
	}

	function handleLinkedBlockClick( event ) {
		var targetElement = getEventTargetElement( event );
		var linkedBlock;
		var expandedLink;

		if ( ! targetElement || 0 !== event.button || event.altKey ) {
			return;
		}

		linkedBlock = targetElement.closest( linkedBlockSelector );

		if ( ! linkedBlock ) {
			return;
		}

		expandedLink = getExpandedLink( linkedBlock );

		if ( ! expandedLink || shouldIgnorePointerActivation( event, targetElement ) ) {
			return;
		}

		navigateToExpandedLink( expandedLink, event );
	}

	function handleLinkedBlockAuxClick( event ) {
		var targetElement = getEventTargetElement( event );
		var linkedBlock;
		var expandedLink;

		if ( ! targetElement || 1 !== event.button ) {
			return;
		}

		linkedBlock = targetElement.closest( linkedBlockSelector );

		if ( ! linkedBlock ) {
			return;
		}

		expandedLink = getExpandedLink( linkedBlock );

		if ( ! expandedLink || shouldIgnorePointerActivation( event, targetElement ) ) {
			return;
		}

		navigateToExpandedLink( expandedLink, event );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			document.addEventListener( 'click', handleLinkedBlockClick );
			document.addEventListener( 'auxclick', handleLinkedBlockAuxClick );
		} );
	} else {
		document.addEventListener( 'click', handleLinkedBlockClick );
		document.addEventListener( 'auxclick', handleLinkedBlockAuxClick );
	}
}() );