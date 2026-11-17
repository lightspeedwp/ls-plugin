<?php
/**
 * Analytics Events.
 *
 * Sends the site's conversion and engagement events to Google Analytics 4
 * through the `gtag()` function that Site Kit already loads. The events are
 * written in code (not Google Tag Manager) so every change is version
 * controlled and reviewed. See `docs/analytics-events.md` for the event list.
 *
 * This class only decides what the front-end script should watch for and
 * passes that to `build/js/analytics-events.js`. The script does the tracking.
 *
 * No form field values are ever read or sent, so no personal data leaves the
 * site. Consent is handled by Site Kit's consent mode: when `gtag()` is not
 * available (for example for logged-in users, who Site Kit does not track) the
 * script does nothing.
 *
 * @package LS_Plugin
 * @since   0.2.0
 */

namespace LS_Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Analytics_Events
 *
 * @since 0.2.0
 */
class Analytics_Events {

	/**
	 * Script handle.
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'ls-plugin-analytics-events';

	/**
	 * Transient that caches the Gravity Forms form ID to event map.
	 *
	 * @var string
	 */
	const FORM_MAP_TRANSIENT = 'ls_plugin_analytics_form_map';

	/**
	 * Post type of the case studies (the Portfolio post type).
	 *
	 * @var string
	 */
	const PORTFOLIO_POST_TYPE = 'project';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );

		// Rebuild the form map when a form is saved, so renamed forms are picked up.
		add_action( 'gform_after_save_form', array( $this, 'clear_form_map_cache' ) );
	}

	/**
	 * Enqueues the front-end tracking script and passes it its settings.
	 *
	 * @return void
	 */
	public function enqueue_script() {
		if ( is_admin() || is_feed() ) {
			return;
		}

		/**
		 * Filters whether the analytics events script is loaded.
		 *
		 * Return `false` to switch all custom event tracking off.
		 *
		 * @since 0.2.0
		 *
		 * @param bool $enabled Whether the script is loaded. Default true.
		 */
		if ( ! apply_filters( 'ls_plugin_analytics_events_enabled', true ) ) {
			return;
		}

		$script_path = 'build/js/analytics-events.js';
		$script_file = LS_PLUGIN_PLUGIN_DIR . $script_path;

		if ( ! file_exists( $script_file ) ) {
			return;
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			LS_PLUGIN_PLUGIN_URL . $script_path,
			array(),
			(string) filemtime( $script_file ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		wp_localize_script( self::SCRIPT_HANDLE, 'lsPluginAnalytics', $this->get_config() );
	}

	/**
	 * Builds the settings object the front-end script reads.
	 *
	 * @return array<string, mixed>
	 */
	private function get_config() {
		$consultation_path = wp_parse_url( home_url( '/free-consultation/' ), PHP_URL_PATH );

		$config = array(
			// Gravity Forms form ID => event. Used for forms that confirm on the same page.
			'forms'       => $this->get_form_event_map(),
			// Event to send when this page is a form's thank-you page (redirect confirmations).
			'thankYou'    => $this->get_thank_you_event(),
			// Elements that open the consultation popup (Popup Maker trigger class).
			'ctaSelector' => '.pum-triggercta',
			// Links to these paths count as consultation CTA clicks too.
			'ctaPaths'    => array( $consultation_path ),
			// Case-study read settings, only set on a single case study.
			'portfolio'   => $this->get_portfolio_config(),
			'debug'       => defined( 'WP_DEBUG' ) && WP_DEBUG,
		);

		/**
		 * Filters the settings passed to the analytics events script.
		 *
		 * @since 0.2.0
		 *
		 * @param array<string, mixed> $config Script settings.
		 */
		return apply_filters( 'ls_plugin_analytics_config', $config );
	}

	/**
	 * Form titles and the event each form sends when it is submitted.
	 *
	 * Forms are matched by title, so the same event works on every environment
	 * even though Gravity Forms form IDs differ between sites.
	 *
	 * @return array<string, array{event: string, form: string}>
	 */
	private function get_form_title_events() {
		$forms = array(
			'Contact Form'             => array(
				'event' => 'contact_submit',
				'form'  => 'contact',
			),
			'Free Consultation'        => array(
				'event' => 'consultation_submit',
				'form'  => 'free_consultation',
			),
			'Website Brief'            => array(
				'event' => 'website_brief_submit',
				'form'  => 'website_brief',
			),
			'Newsletter Subscribe'     => array(
				'event' => 'newsletter_subscribe',
				'form'  => 'newsletter',
			),
			'Newsletter Subscribe (1)' => array(
				'event' => 'newsletter_subscribe',
				'form'  => 'newsletter',
			),
		);

		/**
		 * Filters the form title to event map.
		 *
		 * @since 0.2.0
		 *
		 * @param array<string, array{event: string, form: string}> $forms Form titles and events.
		 */
		return apply_filters( 'ls_plugin_analytics_form_events', $forms );
	}

	/**
	 * Pages that a form redirects to after a successful submission.
	 *
	 * Gravity Forms leaves the page on a redirect confirmation, so the event is
	 * sent when the thank-you page loads instead.
	 *
	 * @return array<string, array{event: string, form: string}> Page path (no slashes at the ends) => event.
	 */
	private function get_thank_you_pages() {
		$pages = array(
			'contact/thank-you'              => array(
				'event' => 'contact_submit',
				'form'  => 'contact',
			),
			'free-consultation/thank-you'    => array(
				'event' => 'consultation_submit',
				'form'  => 'free_consultation',
			),
			'thank-you-newsletter-subscribe' => array(
				'event' => 'newsletter_subscribe',
				'form'  => 'newsletter',
			),
		);

		/**
		 * Filters the thank-you page to event map.
		 *
		 * @since 0.2.0
		 *
		 * @param array<string, array{event: string, form: string}> $pages Page paths and events.
		 */
		return apply_filters( 'ls_plugin_analytics_thank_you_pages', $pages );
	}

	/**
	 * Returns the event for the current page when it is a thank-you page.
	 *
	 * @return array{event: string, form: string}|null
	 */
	private function get_thank_you_event() {
		if ( ! is_page() ) {
			return null;
		}

		$uri   = trim( (string) get_page_uri( get_queried_object_id() ), '/' );
		$pages = $this->get_thank_you_pages();

		return $pages[ $uri ] ?? null;
	}

	/**
	 * Maps Gravity Forms form IDs to events, using the form titles.
	 *
	 * @return array<string, array{event: string, form: string}> Form ID => event.
	 */
	private function get_form_event_map() {
		if ( ! class_exists( '\GFAPI' ) ) {
			return array();
		}

		$map = get_transient( self::FORM_MAP_TRANSIENT );

		if ( is_array( $map ) ) {
			return $map;
		}

		$map    = array();
		$titles = $this->get_form_title_events();

		foreach ( (array) \GFAPI::get_forms() as $form ) {
			$title = isset( $form['title'] ) ? (string) $form['title'] : '';

			if ( isset( $titles[ $title ], $form['id'] ) ) {
				$map[ (string) $form['id'] ] = $titles[ $title ];
			}
		}

		set_transient( self::FORM_MAP_TRANSIENT, $map, 12 * HOUR_IN_SECONDS );

		return $map;
	}

	/**
	 * Clears the cached form map.
	 *
	 * @return void
	 */
	public function clear_form_map_cache() {
		delete_transient( self::FORM_MAP_TRANSIENT );
	}

	/**
	 * Settings for the "case study read" event, or null off case-study pages.
	 *
	 * A read is counted once per page view, after about 30 seconds on the page
	 * (time with the tab visible) or when half the page has been scrolled.
	 *
	 * @return array{slug: string, seconds: int, scroll: int}|null
	 */
	private function get_portfolio_config() {
		if ( ! is_singular( self::PORTFOLIO_POST_TYPE ) ) {
			return null;
		}

		return array(
			'slug'    => (string) get_post_field( 'post_name', get_queried_object_id() ),
			'seconds' => 30,
			'scroll'  => 50,
		);
	}
}
