<?php
/**
 * The file responsible for various rest api workflows by Google My Business service.
 *
 * A class that creates and does various rest api actions and workflows relating to the Google My Business service in ROP
 *
 * These methods will most likely later go into one file for all services as we drop creation of own apps and bring all services up to speed with the easy sign in option.
 *
 * @link       https://themeisle.com/
 * @since      8.5.9
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services/rest-api
 */

/**
 * Class ROP_Gmb_Rest_Api
 *
 * @since   8.5.9
 * @link    https://themeisle.com/
 */
class Rop_Gmb_Rest_Api {


	/**
	 * Registers the API endpoint for authenticating site sending the request.
	 *
	 * @since   8.5.9
	 * @access  public
	 */
	public function gmb_authenticate_request_sender_endpoint() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'tweet-old-post/v8',
					'/api/authenticate/request-sender',
					array(
						'methods'             => array( 'GET' ),
						'callback'            => array( $this, 'gmb_authenticate_request_sender' ),
					)
				);
			}
		);
	}

	/**
	 * Registers the API endpoint for verifying website requesting for access token refresh.
	 *
	 * @since   8.5.9
	 * @param  array $params The parameters sent with the request from our auth server.
	 * @access  public
	 */
	public function gmb_authenticate_request_sender( $params ) {
		$received_install_token = $params['install-token'];
		$received_hash = $params['hash'];

		if ( empty( $received_install_token ) || empty( $received_hash ) ) {
			return array(
				'code' => 400,
				'message' => 'Bad Request: Could not authenticate request sender. Received install token or hash is parameter is empty.',
			);
		}

		$current_install_token = get_option( ROP_INSTALL_TOKEN_OPTION );
		$current_request_hash = get_option( 'rop_gmb_refresh_access_token_hash' );

		if ( ( $received_install_token === $current_install_token ) && ( $received_hash === $current_request_hash ) ) {
			$response = array(
				'code' => 200,
			);
		} else {
			$response = array(
				'code' => 401,
				'message' => 'Unauthorized: Could not authenticate request sender. Received install token or hash does not match.',
			);
		}

		return $response;
	}
}
