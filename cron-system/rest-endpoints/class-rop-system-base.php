<?php

namespace RopCronSystem\Endpoint_Cron_Base;

use WP_REST_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Class Rop_System_Base
 *
 * @package RopCronSystem\Endpoint_Cron_Base
 * @since 8.5.5
 */
class Rop_System_Base extends WP_REST_Controller {

	/**
	 * Retrieves the client token from headers sent with the request with REST API.
	 *
	 * @param string $token_data Encoded string containing the auth information.
	 *
	 * @return bool|string
	 * @since 8.5.5
	 * @access protected
	 */
	protected function fetch_token_from_headers( $token_data = '' ) {
		// We need data to exist.
		if ( empty( $token_data ) ) {
			return false;
		}

		// Decode base64 client data.
		$decode_data = base64_decode( $token_data );

		// Create the array which contains user data.
		parse_str( $decode_data, $token_holder );

		if ( ! is_array( $token_holder ) || ! isset( $token_holder['token'] ) ) {
			return false;
		}

		return sanitize_text_field( trim( $token_holder['token'] ) );
	}

	/**
	 * Checks if the given auth token is registered.
	 *
	 * @param string $token_data Client auth token.
	 *
	 * @return bool
	 * @since 8.5.5
	 * @access protected
	 */
	protected function is_valid_token( $token_data = '' ) {

		// Fetch user token from REST API headers request.
		$token = $this->fetch_token_from_headers( $token_data );

		// If the token is not found.
		if ( false === $token ) {
			return false;
		}

		// TODO Check the database.
	}
}
