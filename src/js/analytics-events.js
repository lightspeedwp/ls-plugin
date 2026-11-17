/**
 * Analytics Events.
 *
 * Sends the site's conversion and engagement events to Google Analytics 4
 * through the `gtag()` function that Site Kit loads. Settings come from
 * `inc/class-analytics-events.php` as `window.lsPluginAnalytics`.
 *
 * Events: consultation_submit, contact_submit, website_brief_submit,
 * newsletter_subscribe, consultation_cta_click and portfolio_read.
 *
 * No form field values are read or sent. If `gtag()` is not available
 * (logged-in users, consent not given, blocked) the events are skipped.
 */

( () => {
	const config = window.lsPluginAnalytics || {};
	const storagePrefix = 'ls_plugin_analytics_sent_';

	/**
	 * Sends one event to GA4.
	 *
	 * @param {string} name   Event name.
	 * @param {Object} params Event parameters.
	 */
	const send = ( name, params = {} ) => {
		if ( config.debug && window.console ) {
			// eslint-disable-next-line no-console
			window.console.info( '[ls-plugin analytics]', name, params );
		}

		if ( typeof window.gtag !== 'function' ) {
			return;
		}

		window.gtag( 'event', name, params );
	};

	/**
	 * Returns true if this event was already sent inside the time window.
	 *
	 * @param {string} name     Event name.
	 * @param {number} windowMs Window in milliseconds.
	 * @return {boolean} Whether it was sent recently.
	 */
	const sentRecently = ( name, windowMs ) => {
		try {
			const last = Number(
				window.sessionStorage.getItem( storagePrefix + name )
			);
			return last > 0 && Date.now() - last < windowMs;
		} catch ( error ) {
			return false;
		}
	};

	/**
	 * Sends an event unless it was already sent inside the time window.
	 * Stops double counting from a page reload or a double fired hook.
	 *
	 * @param {string} name     Event name.
	 * @param {Object} params   Event parameters.
	 * @param {number} windowMs Window in milliseconds.
	 */
	const sendOnce = ( name, params, windowMs ) => {
		if ( sentRecently( name, windowMs ) ) {
			return;
		}

		try {
			window.sessionStorage.setItem( storagePrefix + name, Date.now() );
		} catch ( error ) {
			// Storage is not available. Send anyway.
		}

		send( name, params );
	};

	/**
	 * Forms that submit with AJAX (Gravity Forms confirmation event).
	 *
	 * @return {boolean} Whether the listener was attached.
	 */
	const bindFormConfirmations = () => {
		const $ = window.jQuery;

		if ( ! $ || ! config.forms ) {
			return false;
		}

		$( document ).on( 'gform_confirmation_loaded', ( event, formId ) => {
			const item = config.forms[ String( formId ) ];

			if ( item ) {
				sendOnce( item.event, { form_name: item.form }, 10 * 1000 );
			}
		} );

		return true;
	};

	/**
	 * Forms that confirm with a message after a normal page reload.
	 *
	 * Gravity Forms only fires its confirmation event for AJAX submits. The
	 * forms on this site submit with a page reload, so look for the
	 * confirmation message in the page instead.
	 */
	const trackPageConfirmations = () => {
		Object.keys( config.forms || {} ).forEach( ( formId ) => {
			const item = config.forms[ formId ];
			const wrapper = document.getElementById(
				'gform_confirmation_wrapper_' + formId
			);

			// A "form saved" notice (Save and Continue) is not a submission.
			if (
				item &&
				wrapper &&
				! wrapper.classList.contains( 'form_saved_message_sent' )
			) {
				sendOnce(
					item.event,
					{ form_name: item.form },
					30 * 60 * 1000
				);
			}
		} );
	};

	/**
	 * Forms that redirect to a thank-you page: send when that page loads.
	 */
	const trackThankYouPage = () => {
		const item = config.thankYou;

		if ( item ) {
			sendOnce( item.event, { form_name: item.form }, 30 * 60 * 1000 );
		}
	};

	/**
	 * Where on the page a CTA sits.
	 *
	 * @param {Element} element The clicked CTA.
	 * @return {string} header, footer, hero, popup or page.
	 */
	const getLocation = ( element ) => {
		if ( element.closest( '.pum' ) ) {
			return 'popup';
		}
		if ( element.closest( 'header' ) ) {
			return 'header';
		}
		if ( element.closest( 'footer' ) ) {
			return 'footer';
		}
		if ( element.closest( '[class*="hero"]' ) ) {
			return 'hero';
		}
		return 'page';
	};

	/**
	 * Returns the consultation CTA for a click target, or null.
	 *
	 * @param {Element} target The clicked element.
	 * @return {Element|null} The CTA element.
	 */
	const findCta = ( target ) => {
		if ( config.ctaSelector ) {
			const trigger = target.closest( config.ctaSelector );
			if ( trigger ) {
				return trigger;
			}
		}

		const link = target.closest( 'a[href]' );
		if ( link && link.origin === window.location.origin ) {
			const path = link.pathname.endsWith( '/' )
				? link.pathname
				: link.pathname + '/';
			if ( ( config.ctaPaths || [] ).includes( path ) ) {
				return link;
			}
		}

		return null;
	};

	/**
	 * Consultation CTA clicks.
	 */
	const bindCtaClicks = () => {
		document.addEventListener( 'click', ( event ) => {
			if ( ! ( event.target instanceof window.Element ) ) {
				return;
			}

			const cta = findCta( event.target );

			if ( ! cta ) {
				return;
			}

			const text = (
				cta.textContent ||
				cta.getAttribute( 'aria-label' ) ||
				''
			)
				.replace( /\s+/g, ' ' )
				.trim()
				.slice( 0, 60 );

			send( 'consultation_cta_click', {
				cta_location: getLocation( cta ),
				cta_text: text,
			} );
		} );
	};

	/**
	 * Case study read: about 30 seconds with the tab visible, or half the
	 * page scrolled, whichever comes first. Sent once per page view.
	 */
	const trackPortfolioRead = () => {
		const settings = config.portfolio;

		if ( ! settings ) {
			return;
		}

		let sent = false;
		let visibleMs = 0;
		let timer = null;

		const fire = ( trigger ) => {
			if ( sent ) {
				return;
			}
			sent = true;
			window.clearInterval( timer );
			window.removeEventListener( 'scroll', onScroll ); // eslint-disable-line no-use-before-define
			send( 'portfolio_read', {
				project: settings.slug,
				read_trigger: trigger,
			} );
		};

		const onScroll = () => {
			const page = document.documentElement;
			const reached =
				( window.scrollY + window.innerHeight ) /
				Math.max( page.scrollHeight, 1 );

			if ( reached >= settings.scroll / 100 ) {
				fire( 'scroll' );
			}
		};

		timer = window.setInterval( () => {
			if ( document.visibilityState !== 'visible' ) {
				return;
			}

			visibleMs += 1000;

			if ( visibleMs >= settings.seconds * 1000 ) {
				fire( 'time' );
			}
		}, 1000 );

		window.addEventListener( 'scroll', onScroll, { passive: true } );
	};

	const init = () => {
		trackPageConfirmations();
		trackThankYouPage();
		bindCtaClicks();
		trackPortfolioRead();
	};

	// Attach the form listener as early as possible, so a confirmation that
	// Gravity Forms triggers while the page loads is not missed.
	if ( ! bindFormConfirmations() ) {
		document.addEventListener( 'DOMContentLoaded', bindFormConfirmations );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
