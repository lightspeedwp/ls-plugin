<?php
/**
 * Sentry error reporting.
 *
 * This plugin is the single owner of the Sentry PHP SDK for the LightSpeed site. The theme only
 * adds context tags to events, so nothing is reported twice.
 *
 * Sentry only starts when a SENTRY_DSN constant (for example in wp-config.php) or SENTRY_DSN
 * environment variable is set and the SDK is installed in vendor/. Without both, this class does
 * nothing, so local sites and CI are unaffected. The DSN is never stored in this repository.
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
	 * Request headers that must never be sent to Sentry (lower case).
	 *
	 * @var string[]
	 */
	const SENSITIVE_HEADERS = array( 'authorization', 'cookie', 'set-cookie', 'x-wp-nonce', 'x-csrf-token' );

	/**
	 * Query string parameters whose values must never be sent to Sentry (lower case).
	 *
	 * @var string[]
	 */
	const SENSITIVE_QUERY_KEYS = array( '_wpnonce', 'nonce', 'password', 'pwd', 'token', 'key', 'api_key', 'secret' );

	/**
	 * Starts Sentry if a DSN is configured and the SDK is available.
	 *
	 * @return bool True when Sentry was started by this call.
	 */
	public static function init() {
		$dsn = self::get_dsn();

		if ( '' === $dsn ) {
			return false;
		}

		$autoload = LS_PLUGIN_PLUGIN_DIR . 'vendor/autoload.php';

		if ( ! class_exists( '\Sentry\SentrySdk' ) && is_readable( $autoload ) ) {
			require_once $autoload;
		}

		if ( ! class_exists( '\Sentry\SentrySdk' ) ) {
			return false;
		}

		// Another plugin has already started Sentry: do not start a second client.
		if ( null !== \Sentry\SentrySdk::getCurrentHub()->getClient() ) {
			return false;
		}

		\Sentry\init(
			array(
				'dsn'                   => $dsn,
				'environment'           => self::get_environment(),
				// Strip the server path so stack frames read wp-content/plugins/... on every host.
				'prefixes'              => array( ABSPATH ),
				'release'               => 'ls-plugin@' . LS_PLUGIN_VERSION,
				'send_default_pii'      => false,
				'max_request_body_size' => 'none',
				'error_types'           => E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED,
				'before_send'           => array( __CLASS__, 'scrub_event' ),
			)
		);

		\Sentry\configureScope(
			static function ( \Sentry\State\Scope $scope ) {
				$scope->setTag( 'component', 'ls-plugin' );
				$scope->setTag( 'plugin.version', LS_PLUGIN_VERSION );
			}
		);

		return true;
	}

	/**
	 * Returns the DSN from the SENTRY_DSN constant, or the SENTRY_DSN environment variable.
	 *
	 * @return string Empty string when no DSN is configured.
	 */
	public static function get_dsn() {
		if ( defined( 'SENTRY_DSN' ) && is_string( SENTRY_DSN ) && '' !== SENTRY_DSN ) {
			return SENTRY_DSN;
		}

		$env_dsn = getenv( 'SENTRY_DSN' );

		return ( is_string( $env_dsn ) && '' !== $env_dsn ) ? $env_dsn : '';
	}

	/**
	 * Maps the WordPress environment type to the Sentry environment name.
	 *
	 * @return string
	 */
	public static function get_environment() {
		$allowed = array( 'local', 'development', 'staging', 'production' );
		$type    = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';

		return in_array( $type, $allowed, true ) ? $type : 'production';
	}

	/**
	 * Removes cookies, auth headers and sensitive query values from an event before it is sent.
	 *
	 * @param \Sentry\Event $event The event about to be sent.
	 * @return \Sentry\Event
	 */
	public static function scrub_event( $event ) {
		$request = $event->getRequest();

		if ( ! empty( $request ) ) {
			unset( $request['cookies'] );

			if ( isset( $request['headers'] ) && is_array( $request['headers'] ) ) {
				foreach ( array_keys( $request['headers'] ) as $header ) {
					if ( in_array( strtolower( (string) $header ), self::SENSITIVE_HEADERS, true ) ) {
						unset( $request['headers'][ $header ] );
					}
				}
			}

			if ( ! empty( $request['query_string'] ) && is_string( $request['query_string'] ) ) {
				$request['query_string'] = self::scrub_query_string( $request['query_string'] );
			}

			$event->setRequest( $request );
		}

		return $event;
	}

	/**
	 * Replaces the values of sensitive query string parameters with [Filtered].
	 *
	 * @param string $query_string Raw query string.
	 * @return string
	 */
	public static function scrub_query_string( $query_string ) {
		$parts = explode( '&', $query_string );

		foreach ( $parts as $index => $part ) {
			$pair = explode( '=', $part, 2 );

			if ( in_array( strtolower( urldecode( $pair[0] ) ), self::SENSITIVE_QUERY_KEYS, true ) ) {
				$parts[ $index ] = $pair[0] . '=[Filtered]';
			}
		}

		return implode( '&', $parts );
	}
}
