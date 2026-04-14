/**
 * Smooth scroll utility with back-to-top support and anchor link smooth scrolling.
 * Works with core/button Back to Top variation.
 * Accessible, performant, and supports prefers-reduced-motion.
 */

( function () {
	// Check if the browser prefers reduced motion
	const prefersReducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches;

	// Get the topmost scroll target
	const getScrollTarget = () => {
		const wpSiteBlocks = document.querySelector( '.wp-site-blocks' );
		return wpSiteBlocks || document.documentElement;
	};

	// Calculate offset for sticky headers
	const getStickyHeaderOffset = () => {
		const header = document.querySelector( 'header[sticky="true"], [data-sticky="true"], .is-sticky' );
		if ( ! header ) return 0;

		const rect = header.getBoundingClientRect();
		return Math.max( 0, rect.height + 20 ); // Add 20px padding
	};

	// Smooth scroll to target element or position
	const smoothScrollTo = ( target, duration = 600 ) => {
		if ( prefersReducedMotion ) {
			// For users who prefer reduced motion, use instant scroll
			if ( typeof target === 'number' ) {
				window.scrollTo( 0, target );
			} else if ( target instanceof HTMLElement ) {
				target.scrollIntoView();
			}
			return;
		}

		const startY = window.pageYOffset;
		const endY =
			typeof target === 'number'
				? target
				: target.getBoundingClientRect().top + startY - getStickyHeaderOffset();
		const distance = endY - startY;
		const startTime = performance.now();

		const easeInOutCubic = ( progress ) => {
			return progress < 0.5
				? 4 * progress * progress * progress
				: ( progress - 1 ) * ( 2 * progress - 2 ) * ( 2 * progress - 2 ) + 1;
		};

		requestAnimationFrame( ( currentTime ) => {
			const elapsed = currentTime - startTime;
			const progress = Math.min( elapsed / duration, 1 );
			const currentY = startY + distance * easeInOutCubic( progress );

			window.scrollTo( 0, currentY );

			if ( progress < 1 ) {
				requestAnimationFrame( arguments.callee );
			}
		} );
	};

	// Initialize smooth scrolling for anchor links
	const initAnchorLinks = () => {
		document.addEventListener( 'click', ( e ) => {
			const link = e.target.closest( 'a[href*="#"]' );
			if ( ! link ) return;

			const href = link.getAttribute( 'href' );
			const hash = href.substring( href.indexOf( '#' ) );

			// Skip if it's just a hash or empty
			if ( hash === '#' || hash === '' ) return;

			// Check if the target exists
			const target = document.querySelector( hash );
			if ( ! target ) return;

			e.preventDefault();
			smoothScrollTo( target );

			// Update URL without triggering scroll
			window.history.pushState( null, '', hash );
		} );
	};

	// Initialize back-to-top functionality
	const initBackToTop = () => {
		const buttons = document.querySelectorAll(
			'a[data-is-back-to-top="true"], button[data-is-back-to-top="true"]'
		);

		buttons.forEach( ( button ) => {
			const positionMode = button.getAttribute( 'data-back-to-top-mode' ) || 'scroll';
			const scrollThreshold = parseInt(
				button.getAttribute( 'data-back-to-top-threshold' ),
				10
			) || 50;

			// Handle visibility for scroll mode
			if ( positionMode === 'scroll' ) {
				const updateVisibility = () => {
					const viewportHeight = window.innerHeight;
					const scrollThresholdPixels = ( viewportHeight * scrollThreshold ) / 100;
					const isVisible = window.pageYOffset > scrollThresholdPixels;

					button.setAttribute(
						'aria-hidden',
						( ! isVisible ).toString()
					);
					button.style.visibility = isVisible ? 'visible' : 'hidden';
					button.style.opacity = isVisible ? '1' : '0';
					button.style.pointerEvents = isVisible ? 'auto' : 'none';
				};

				// Set up passive scroll listener
				window.addEventListener( 'scroll', updateVisibility, {
					passive: true,
				} );
				updateVisibility(); // Initial check
			}

			// Handle click events
			button.addEventListener( 'click', ( e ) => {
				// Only prevent default for links
				if ( button.tagName === 'A' ) {
					e.preventDefault();
				}
				const target = getScrollTarget();
				smoothScrollTo( target, prefersReducedMotion ? 0 : 600 );
			} );
		} );
	};

	// DOM ready check
	const ready = ( callback ) => {
		if (
			document.readyState === 'loading' ||
			document.readyState === 'interactive'
		) {
			document.addEventListener( 'DOMContentLoaded', callback );
		} else {
			callback();
		}
	};

	// Initialize on DOM ready
	ready( () => {
		initAnchorLinks();
		initBackToTop();
	} );
} )();
