<?php
/**
 * LS Plugin - AI Engine
 *
 * @package   ls_plugin
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      https://lightspeedwp.agency/
 * @copyright 2026 LightSpeed
 */

namespace LS_Plugin;

/**
 * Interacts with the AI Engine plugin to provide AI-powered features for the LS Plugin.
 *
 * @package ls_plugin
 */
class AI_Engine {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'mwai_functions_list', [ $this, 'register_functions' ], 10, 1 );
		add_filter( 'mwai_ai_query', [ $this, 'inject_functions_into_query' ], 10, 1 );
		add_filter( 'mwai_ai_feedback', [ $this, 'handle_ai_feedback' ], 10, 2 );
		//add_filter( 'mwai_ai_reply', [ $this, 'override_ai_reply' ], 10, 2 );
	}

	/**
	 * Defines the getCurrentUserInfo function for AI Engine.
	 *
	 * @return \Meow_MWAI_Query_Function
	 */
	private function define_user_info() {
		return \Meow_MWAI_Query_Function::fromJson( [
			'id'   => 'userInfo',
			'type' => 'manual',
			'name' => 'getCurrentUserInfo',
			'desc' => 'Get the current user information.',
		] );
	}

	/**
	 * Returns current user information as JSON.
	 *
	 * @return string|null JSON-encoded user data, or null if not logged in.
	 */
	private function call_user_info() {
		$current_user = wp_get_current_user();
		if ( $current_user->exists() ) {
			return wp_json_encode( [
				'user_url'     => $current_user->user_url,
				'user_login'   => $current_user->user_login,
				'user_email'   => $current_user->user_email,
				'display_name' => $current_user->display_name,
			] );
		}
		return null;
	}

	/**
	 * Defines the sendEmail function for AI Engine.
	 *
	 * @return \Meow_MWAI_Query_Function
	 */
	private function define_send_email() {
		return \Meow_MWAI_Query_Function::fromJson( [
			'id'   => 'sendEmail',
			'type' => 'manual',
			'name' => 'sendEmail',
			'desc' => 'Send an email to the admin.',
			'args' => [
				[
					'name'     => 'subject',
					'desc'     => 'The subject of the email.',
					'type'     => 'string',
					'required' => true,
				],
				[
					'name'     => 'message',
					'desc'     => 'The message of the email.',
					'type'     => 'string',
					'required' => true,
				],
			],
		] );
	}

	/**
	 * Defines the searchPosts function for AI Engine.
	 *
	 * @return \Meow_MWAI_Query_Function
	 */
	private function define_search_posts() {
		return \Meow_MWAI_Query_Function::fromJson( [
			'id'   => 'searchPosts',
			'type' => 'manual',
			'name' => 'searchPosts',
			'desc' => 'Search the site posts and return the 10 latest matching results.',
			'args' => [
				[
					'name'     => 'search',
					'desc'     => 'The search term to filter posts by.',
					'type'     => 'string',
					'required' => true,
				],
			],
		] );
	}

	/**
	 * Searches posts matching the given term and returns the 10 latest as JSON.
	 *
	 * @param string $search Search term.
	 * @return string JSON-encoded array of matching posts.
	 */
	private function call_search_posts( $search ) {
		$query = new \WP_Query( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			's'              => sanitize_text_field( $search ),
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );

		$posts = [];
		foreach ( $query->posts as $post ) {
			$posts[] = [
				'id'    => $post->ID,
				'title' => $post->post_title,
				'date'  => $post->post_date,
				'url'   => get_permalink( $post->ID ),
				'excerpt' => get_the_excerpt( $post ),
			];
		}

		return wp_json_encode( $posts );
	}

	/**
	 * Sends an email to the site admin.
	 *
	 * @param string $subject Email subject.
	 * @param string $message Email message body.
	 * @return string Result message.
	 */
	private function call_send_email( $subject, $message ) {
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) ) {
			return 'The admin email is not set.';
		}
		$headers = 'From: ' . get_bloginfo( 'name' ) . ' <' . $admin_email . '>';
		$result  = wp_mail( $admin_email, $subject, $message, $headers );
		return $result ? 'The email has been sent.' : 'The email could not be sent.';
	}

	/**
	 * Registers custom functions with the AI Engine functions list.
	 *
	 * @param array $functions Existing registered functions.
	 * @return array Modified functions list.
	 */
	public function register_functions( $functions ) {
		$functions[] = $this->define_user_info();
		$functions[] = $this->define_send_email();
		$functions[] = $this->define_search_posts();
		return $functions;
	}

	/**
	 * Force-injects custom functions into every AI query.
	 *
	 * Note: Does not work with OpenAI Assistants.
	 *
	 * @param object $query The AI query object.
	 * @return object Modified query.
	 */
	public function inject_functions_into_query( $query ) {
		$query->add_function( $this->define_user_info() );
		$query->add_function( $this->define_send_email() );
		$query->add_function( $this->define_search_posts() );
		return $query;
	}

	/**
	 * Handles AI feedback callbacks, returning values for the AI model to process further.
	 *
	 * @param mixed $value        Current feedback value.
	 * @param array $need_feedback Feedback request data including function and arguments.
	 * @return mixed Feedback value.
	 */
	public function handle_ai_feedback( $value, $need_feedback ) {
		$function = $need_feedback['function'];
		if ( $function->id === 'userInfo' ) {
			return $this->call_user_info();
		}
		if ( $function->id === 'sendEmail' ) {
			$subject = $need_feedback['arguments']['subject'];
			$message = $need_feedback['arguments']['message'];
			return $this->call_send_email( $subject, $message );
		}
		if ( $function->id === 'searchPosts' ) {
			$search = $need_feedback['arguments']['search'];
			return $this->call_search_posts( $search );
		}
		return $value;
	}

	/**
	 * Overrides the AI reply directly, bypassing AI model processing of the feedback.
	 *
	 * @param object $reply The AI reply object.
	 * @param object $query The AI query object.
	 * @return object Modified reply.
	 */
	public function override_ai_reply( $reply, $query ) {
		foreach ( $reply->needFeedbacks as $index => $need_feedback ) {
			$function = $need_feedback['function'];
			if ( $function->id === 'userInfo' ) {
				$value = $this->call_user_info();
				if ( ! empty( $value ) ) {
					$reply->result = 'Here is your data: ' . $value;
					unset( $reply->needFeedbacks[ $index ] );
					return $reply;
				}
			}
		}
		return $reply;
	}
}

return new AI_Engine();