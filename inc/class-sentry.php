<?php
/**
 * Sentry error reporting.
 *
 * This plugin is the single owner of the Sentry PHP SDK for the LightSpeed site, so nothing is
 * reported twice. It also tags events with the active theme's name and version, so the theme needs
 * no Sentry code of its own, and switching the theme off never stops error reporting.
 *
 * Sentry only starts when a SENTRY_DSN constant (for example in wp-config.php) or SENTRY_DSN
 * environment variable is set and the SDK is installed in vendor/. Without both, this class does
 * nothing, so local sites and CI are unaffected. The DSN is never stored in this repository.
 *
 * Same shape as the Sentry module in sd-enhancements-2026, so agency sites report alike: minimal
 * data, environment from WP_ENVIRONMENT_TYPE, release `<package>@<version>`, a `component` tag and
 * `theme` / `theme.version` tags.
 *
 * @package LS_Plugin
 */

namespace LS_Plugin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Starts the Sentry SDK with minimal-data defaults.
 */
class Sentry {

	/**
	 * The `component` tag this plugin's events carry.
	 *
	 * @var string
	 */
	const COMPONENT = 'ls-plugin';

	/**
	 * The package name in the release, `<package>@<version>`.
	 *
	 * @var string
	 */
	const RELEASE_PACKAGE = 'ls-plugin';

	/**
	 * Extra key terms whose values must never be sent to Sentry, on top of the SDK's own deny list.
	 *
	 * The SDK already filters keys containing auth, token, secret, password, pwd, key, csrf, session
	 * and similar. A key is matched when it contains a term, so `api.key` and `email[]` are caught.
	 *
	 * @var string[]
	 */
	const SENSITIVE_TERMS = array( 'nonce', 'email' );

	/**
	 * Starts Sentry if a DSN is configured and the SDK is available.
	 *
	 * Called directly from ls-plugin.php, not on a hook, so the SDK is listening before any other
	 * part of the plugin runs.
	 *
	 * @return bool True when Sentry was started by this call.
	 */
	public static function init() {
		$dsn = self::get_dsn();

		if ( '' === $dsn || ! self::load_sdk() ) {
			return false;
		}

		// Another plugin has already started Sentry: do not start a second client.
		if ( null !== \Sentry\SentrySdk::getCurrentHub()->getClient() ) {
			return false;
		}

		\Sentry\init( self::get_options( $dsn ) );

		\Sentry\configureScope(
			static function ( \Sentry\State\Scope $scope ) {
				$scope->setTag( 'component', self::COMPONENT );
				$scope->setTag( 'plugin.version', LS_PLUGIN_VERSION );
			}
		);

		// The theme is not loaded yet when plugins load.
		add_action( 'after_setup_theme', array( __CLASS__, 'tag_theme' ) );

		return true;
	}

	/**
	 * Tags events with the active theme, so errors can be filtered by theme.
	 *
	 * Runs on `after_setup_theme`, only when this class started Sentry. It never changes the
	 * `component` tag.
	 *
	 * @return void
	 */
	public static function tag_theme() {
		$theme   = wp_get_theme();
		$name    = (string) $theme->get_stylesheet();
		$version = (string) $theme->get( 'Version' );

		\Sentry\configureScope(
			static function ( \Sentry\State\Scope $scope ) use ( $name, $version ) {
				$scope->setTag( 'theme', '' !== $name ? $name : 'unknown' );
				$scope->setTag( 'theme.version', '' !== $version ? $version : 'unknown' );
			}
		);
	}

	/**
	 * Returns the DSN from the SENTRY_DSN constant, or the SENTRY_DSN environment variable.
	 *
	 * @return string Empty string when no DSN is configured.
	 */
	public static function get_dsn() {
		$constant = defined( 'SENTRY_DSN' ) ? constant( 'SENTRY_DSN' ) : '';

		if ( is_string( $constant ) && '' !== $constant ) {
			return $constant;
		}

		$env_dsn = getenv( 'SENTRY_DSN' );

		return ( is_string( $env_dsn ) && '' !== $env_dsn ) ? $env_dsn : '';
	}

	/**
	 * Maps the WordPress environment type to the Sentry environment name.
	 *
	 * @return string One of local, development, staging or production.
	 */
	public static function get_environment() {
		$allowed = array( 'local', 'development', 'staging', 'production' );
		$type    = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';

		return in_array( $type, $allowed, true ) ? $type : 'production';
	}

	/**
	 * Returns the SDK options.
	 *
	 * @param string $dsn The DSN to report to.
	 * @return array<string, mixed>
	 */
	public static function get_options( $dsn ) {
		return array(
			'dsn'                   => $dsn,
			'environment'           => self::get_environment(),
			'release'               => self::RELEASE_PACKAGE . '@' . LS_PLUGIN_VERSION,
			// Strip the server path so stack frames read wp-content/plugins/... on every host.
			'prefixes'              => array( ABSPATH ),
			'send_default_pii'      => false,
			// Forms post personal data: never send request bodies.
			'max_request_body_size' => 'none',
			// Report warnings and worse only.
			'error_types'           => E_ALL & ~E_NOTICE & ~E_USER_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED,
			'data_collection'       => self::get_data_collection(),
		);
	}

	/**
	 * Returns the SDK's data collection settings: the minimum an error report needs.
	 *
	 * Setting this switches the SDK to its newer data collection mode, which filters the request URL,
	 * the query string and the URL inside the Referer header with the same term list. It also
	 * controls stack frame variables, which `send_default_pii` does not.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_data_collection() {
		return array(
			// No IP address or user details.
			'user_info'             => false,
			'cookies'               => array( 'mode' => 'off' ),
			'http_headers'          => array(
				'request'  => array(
					'mode'  => 'denyList',
					'terms' => self::SENSITIVE_TERMS,
				),
				'response' => array( 'mode' => 'off' ),
			),
			// Forms post personal data: never send request or response bodies.
			'http_bodies'           => array(),
			'url_query_params'      => array(
				'mode'  => 'denyList',
				'terms' => self::SENSITIVE_TERMS,
			),
			// Function arguments can hold passwords or tokens.
			'stack_frame_variables' => false,
		);
	}

	/**
	 * Loads the Composer autoloader if the SDK is not already loaded.
	 *
	 * @return bool True when the SDK is available.
	 */
	private static function load_sdk() {
		$autoload = LS_PLUGIN_PLUGIN_DIR . 'vendor/autoload.php';

		if ( ! class_exists( '\Sentry\SentrySdk' ) && is_readable( $autoload ) ) {
			require_once $autoload;
		}

		return class_exists( '\Sentry\SentrySdk' );
	}
}
