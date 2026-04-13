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

( () => {
	const data = window.lsPluginStyleData || {};
	const storageKey = data.localStorageKey || 'ls_plugin_style_preference';
	const variationStorageKey =
		data.styleVariationStorageKey || 'ls_plugin_style_variation';
	const defaultMode = data.defaultMode || 'light';
	const defaultDarkStyleSlug = data.defaultDarkStyleSlug || 'dark';
	const labelDark = data.labelDark || 'Dark Mode';
	const labelLight = data.labelLight || 'Light Mode';
	const switchToText = data.switchToText || 'Switch to %s';
	const switchAriaDark =
		data.switchAriaDark || 'Switch to dark mode, currently light';
	const switchAriaLight =
		data.switchAriaLight || 'Switch to light mode, currently dark';
	const darkClass = 'dark-mode';
	const cookieName = '_ls_plugin_style';
	const variationCookieName = '_ls_plugin_style_variation';
	const blockInputSelector = '.wp-block-ls-plugin-style-switcher__input';

	/**
	 * @param {string} modeLabel Mode label.
	 * @return {string} Formatted action label.
	 */
	function formatSwitchTo( modeLabel ) {
		return switchToText.replace( '%s', modeLabel );
	}

	/**
	 * @return {string} Current mode.
	 */
	function getPreference() {
		try {
			return window.localStorage.getItem( storageKey ) || defaultMode;
		} catch ( error ) {
			return defaultMode;
		}
	}

	/**
	 * @param {string} mode Mode to persist.
	 * @return {void}
	 */
	function persist( mode ) {
		try {
			window.localStorage.setItem( storageKey, mode );
		} catch ( error ) {
			// Continue with cookie fallback for server-side reading.
		}

		document.cookie =
			cookieName +
			'=' +
			mode +
			'; path=/; SameSite=Lax; max-age=' +
			365 * 24 * 60 * 60;
	}

	/**
	 * @return {string} Stored dark style variation slug.
	 */
	function getStoredVariation() {
		try {
			return (
				window.localStorage.getItem( variationStorageKey ) ||
				defaultDarkStyleSlug
			);
		} catch ( error ) {
			return defaultDarkStyleSlug;
		}
	}

	/**
	 * @param {string} styleSlug Style variation slug.
	 * @return {void}
	 */
	function persistVariation( styleSlug ) {
		const slug = styleSlug || defaultDarkStyleSlug;

		try {
			window.localStorage.setItem( variationStorageKey, slug );
		} catch ( error ) {
			// Continue with cookie fallback for server-side reading.
		}

		document.cookie =
			variationCookieName +
			'=' +
			slug +
			'; path=/; SameSite=Lax; max-age=' +
			365 * 24 * 60 * 60;
	}

	/**
	 * @param {Element|null} element Target element.
	 * @param {string}       mode    Active mode.
	 * @return {void}
	 */
	function applyToElement( element, mode ) {
		if ( ! element ) {
			return;
		}

		element.classList.toggle( darkClass, mode === 'dark' );
	}

	/**
	 * @param {string} currentMode Active mode.
	 * @return {void}
	 */
	function updateButton( currentMode ) {
		const button = document.getElementById( 'ls-plugin-style-switcher' );

		if ( ! button ) {
			return;
		}

		const label = button.querySelector( '.ls-plugin-style-label' );
		const iconLight = button.querySelector( '.ls-plugin-style-icon-light' );
		const iconsDark = button.querySelectorAll(
			'.ls-plugin-style-icon-dark'
		);
		const isDark = currentMode === 'dark';
		const nextLabel = isDark ? labelLight : labelDark;

		if ( label ) {
			label.textContent = nextLabel;
		}

		button.setAttribute( 'aria-label', formatSwitchTo( nextLabel ) );
		button.setAttribute( 'title', formatSwitchTo( nextLabel ) );

		if ( iconLight ) {
			iconLight.style.display = isDark ? '' : 'none';
		}

		iconsDark.forEach( ( icon ) => {
			icon.style.display = isDark ? 'none' : '';
		} );
	}

	/**
	 * @param {string} currentMode Active mode.
	 * @return {void}
	 */
	function updateBlockInputs( currentMode ) {
		const isDark = currentMode === 'dark';
		const blockInputs = document.querySelectorAll( blockInputSelector );

		blockInputs.forEach( ( input ) => {
			input.checked = isDark;
			input.setAttribute( 'aria-checked', isDark ? 'true' : 'false' );
			input.setAttribute(
				'aria-label',
				isDark ? switchAriaLight : switchAriaDark
			);
		} );
	}

	/**
	 * @param {string} mode Active mode.
	 * @return {void}
	 */
	function applyMode( mode ) {
		applyToElement( document.documentElement, mode );
		applyToElement( document.body, mode );
		updateButton( mode );
		updateBlockInputs( mode );

		document.dispatchEvent(
			new CustomEvent( 'ls-plugin-style-change', {
				detail: {
					mode,
					styleVariation: getStoredVariation(),
				},
			} )
		);
	}

	/**
	 * @param {HTMLInputElement} input Block switch input element.
	 * @return {string} Style variation slug.
	 */
	function getVariationFromInput( input ) {
		const fromInput = input.dataset.styleVariation;

		if ( fromInput ) {
			return fromInput;
		}

		const blockElement = input.closest( '.wp-block-ls-plugin-style-switcher' );

		if ( blockElement && blockElement.dataset.styleVariation ) {
			return blockElement.dataset.styleVariation;
		}

		return getStoredVariation();
	}

	/**
	 * @return {void}
	 */
	function toggle() {
		const next = getPreference() === 'dark' ? 'light' : 'dark';

		if ( next === 'dark' ) {
			persistVariation( getStoredVariation() );
		}

		persist( next );
		applyMode( next );
	}

	/**
	 * @param {boolean} isDark Whether dark mode should be enabled.
	 * @return {void}
	 */
	function setModeFromInput( isDark, styleSlug ) {
		const next = isDark ? 'dark' : 'light';

		if ( isDark ) {
			persistVariation( styleSlug );
		}

		persist( next );
		applyMode( next );
	}

	// Apply immediately to <html> before the body is available.
	applyToElement( document.documentElement, getPreference() );

	document.addEventListener( 'DOMContentLoaded', () => {
		const preference = getPreference();

		if ( ! getStoredVariation() ) {
			persistVariation( defaultDarkStyleSlug );
		}

		applyMode( preference );

		const button = document.getElementById( 'ls-plugin-style-switcher' );
		if ( button ) {
			button.addEventListener( 'click', toggle );
		}

		document.querySelectorAll( blockInputSelector ).forEach( ( input ) => {
			input.addEventListener( 'change', ( event ) => {
				const inputElement = event.currentTarget;
				setModeFromInput(
					inputElement.checked,
					getVariationFromInput( inputElement )
				);
			} );
		} );
	} );
} )();
