/**
 * Style Switcher.
 *
 * Reads the saved style preference and applies it immediately to <html>
 * to prevent a flash of unstyled content, then mirrors the class to
 * <body> once the DOM is ready and binds the toggle button.
 *
 * Preference is stored in localStorage and mirrored to a cookie so
 * PHP can read it server-side for SSR button-label rendering.
 */
( function () {
	var data = window.lsPluginStyleData || {};
	var storageKey = data.localStorageKey || 'ls_plugin_style_preference';
	var defaultMode = data.defaultMode || 'light';
	var darkClass = 'dark-mode';
	var cookieName = '_ls_plugin_style';

	/**
	 * Gets the current style preference from localStorage.
	 *
	 * @returns {string} 'dark' or 'light'
	 */
	function getPreference() {
		try {
			return localStorage.getItem( storageKey ) || defaultMode;
		} catch ( e ) {
			return defaultMode;
		}
	}

	/**
	 * Persists the style preference to localStorage and a cookie.
	 *
	 * @param {string} mode
	 */
	function persist( mode ) {
		try {
			localStorage.setItem( storageKey, mode );
		} catch ( e ) {}

		// Mirror to cookie for PHP server-side detection.
		document.cookie =
			cookieName +
			'=' +
			mode +
			'; path=/; SameSite=Lax; max-age=' +
			365 * 24 * 60 * 60;
	}

	/**
	 * Adds or removes the dark class from a DOM element.
	 *
	 * @param {Element} element
	 * @param {string}  mode
	 */
	function applyToElement( element, mode ) {
		if ( ! element ) {
			return;
		}

		if ( 'dark' === mode ) {
			element.classList.add( darkClass );
		} else {
			element.classList.remove( darkClass );
		}
	}

	/**
	 * Updates the switcher button label and icon to reflect the next toggle target.
	 *
	 * @param {string} currentMode
	 */
	function updateButton( currentMode ) {
		var button = document.getElementById( 'ls-plugin-style-switcher' );

		if ( ! button ) {
			return;
		}

		var label = button.querySelector( '.ls-plugin-style-label' );
		var iconLight = button.querySelector( '.ls-plugin-style-icon-light' );
		var iconsDark = button.querySelectorAll( '.ls-plugin-style-icon-dark' );
		var isDark = 'dark' === currentMode;
		var nextLabel = isDark ? 'Light Mode' : 'Dark Mode';

		if ( label ) {
			label.textContent = nextLabel;
		}

		button.setAttribute( 'aria-label', 'Switch to ' + nextLabel );
		button.setAttribute( 'title', 'Switch to ' + nextLabel );

		if ( iconLight ) {
			iconLight.style.display = isDark ? '' : 'none';
		}

		iconsDark.forEach( function ( icon ) {
			icon.style.display = isDark ? 'none' : '';
		} );
	}

	/**
	 * Toggles the style, persists it, and updates the UI.
	 */
	function toggle() {
		var next = 'dark' === getPreference() ? 'light' : 'dark';

		persist( next );
		applyToElement( document.documentElement, next );
		applyToElement( document.body, next );
		updateButton( next );
	}

	// Apply immediately to <html> — document.body is not available yet in <head>.
	applyToElement( document.documentElement, getPreference() );

	document.addEventListener( 'DOMContentLoaded', function () {
		var preference = getPreference();

		// Mirror from <html> to <body> now that the DOM is ready.
		applyToElement( document.body, preference );

		var button = document.getElementById( 'ls-plugin-style-switcher' );

		if ( button ) {
			button.addEventListener( 'click', toggle );
		}

		updateButton( preference );
	} );
}() );
