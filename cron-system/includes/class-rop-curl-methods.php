<?php

namespace RopCronSystem\Curl_Helpers;

use Rop_Exception_Handler;
use Rop_Logger;
use RopCronSystem\ROP_Helpers\Rop_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Provides the cURL methods used to communicate with the server.
 *
 * Class Rop_Curl_Methods
 *
 * @package RopCronSystem\Curl_Helpers
 * @since 8.5.5
 */
class Rop_Curl_Methods {

	/**
	 * Rop server API path.
	 *
	 * @since 8.5.5
	 */
	const SERVER_URL = 'https://ropserver.wpr/wp-json/';

	/**
	 * @var resource cURL connection object.
	 * @since 8.5.5
	 */
	private $connection;

	/**
	 * @var array Fixed server paths that can be called.
	 * @since 8.5.5
	 */
	private $server_paths = array(
		':activate_account:' => 'account-status/v1/activate-account',
		':disable_account:'  => 'account-status/v1/disable-account',
		':register_account:' => 'rop-register-data/v1/register-new-user',
		':share_time:'       => 'update-cron-ping/v1/update-time-to-share',
	);

	/**
	 * Holds the Rop_Exception_Handler
	 *
	 * @since   8.5.5
	 * @access  protected
	 * @var     Rop_Exception_Handler $error The exception handler.
	 */
	protected $error;
	/**
	 * Holds the logger
	 *
	 * @since   8.5.5
	 * @access  protected
	 * @var     Rop_Logger $logger The logger handler.
	 */
	protected $logger;

	/**
	 * @var string Server API path concatenated with endpoint path.
	 * @since 8.5.5
	 */
	private $server_url = '';

	function __construct() {
		$this->error  = new Rop_Exception_Handler();
		$this->logger = new Rop_Logger();
	}

	/**
	 * Handles the cURL init action.
	 *
	 * @param array $args cURL request type and which action to call on the server.
	 *
	 * @return bool
	 * @access public
	 * @since 8.5.5
	 */
	public function create_call_process( $args = array() ) {
		$default = array(
			'type'          => 'POST',
			'request_path'  => '',
			'time_to_share' => '',
		);

		$args = wp_parse_args( $args, $default );

		if ( empty( $args['request_path'] ) ) {
			// TODO add to log, important parameter missing. "Please specify the server path __CLASS__ > __FUNCTION__ > request_path"
			return false;
		}

		$token = get_option( 'rop_access_token', '' );

		if ( 'post' === strtolower( $args['type'] ) ) {

			$post_fields = array();

			if ( ':register_account:' === $args['request_path'] ) {

				$this->server_url = self::SERVER_URL . $this->server_paths[ $args['request_path'] ];
				$this->connection = curl_init( $this->server_url );
				$this->register_to_top_server();
			} else {

				if ( empty( $token ) ) {

					$this->server_url = self::SERVER_URL . $this->server_paths[':register_account:'];
					$this->connection = curl_init( $this->server_url );
					$this->register_to_top_server( $args );
				} else {

					$this->server_url = self::SERVER_URL . $this->server_paths[ $args['request_path'] ];

					$this->connection = curl_init( $this->server_url );
					if ( isset( $args['time_to_share'] ) && ! empty( $args['time_to_share'] ) ) {
						$post_fields = array( 'next_ping' => $args['time_to_share'] );

					}

					return $this->request_type_post( $post_fields );
				}
			}
		}

		return true;

	}


	/**
	 * Handles the API calls of type POST.
	 *
	 * @param array $post_arguments
	 *
	 * @return bool|string
	 * @access private
	 * @since 8.5.5
	 */
	private function request_type_post( $post_arguments = array() ) {

		curl_setopt( $this->connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->connection, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $this->connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->connection, CURLOPT_POST, true );

		// Some requests will contain parameters.
		if ( ! empty( $post_arguments ) ) {
			curl_setopt( $this->connection, CURLOPT_POSTFIELDS, build_query( $post_arguments ) );
		}

		$authentication = $this->fetch_attach_auth_token();

		if ( false === $authentication ) {
			unset( $this->connection );
			// TODO add to log, cient token is missing.

			exit;
		}

		$server_response_body = curl_exec( $this->connection );
		$http_code            = curl_getinfo( $this->connection, CURLINFO_HTTP_CODE );
		// TODO check $http_code and add to the Log if it's not the expected 200.
		curl_close( $this->connection );

		return $server_response_body;

	}

	/**
	 * Handles the registration to ROP server.
	 * If callback exists, it will try to use it.
	 *
	 * @param array $callback_param Original request type parameters..
	 *
	 * @return mixed
	 * @access private
	 * @since 8.5.5
	 */
	private function register_to_top_server( $callback_param = array() ) {

		curl_setopt( $this->connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->connection, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $this->connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->connection, CURLOPT_POST, true );

		$base64_register_data = $this->create_register_data();
		$authentication       = $this->fetch_attach_auth_token( $base64_register_data );
		if ( false === $authentication ) {
			unset( $this->connection );
			// TODO add to log, client token is missing.

			exit;
		}

		$server_response_body = curl_exec( $this->connection );
		$http_code            = curl_getinfo( $this->connection, CURLINFO_HTTP_CODE );
		// TODO check $http_code and add to the Log if it's not the expected 200.
		curl_close( $this->connection );

		$response_array = json_decode( $server_response_body, true );

		if ( isset( $response_array['success'] ) ) {
			$success = filter_var( $response_array['success'], FILTER_VALIDATE_BOOLEAN );

			if ( true === $success && ! empty( $callback_param ) ) {

				if ( is_callable( array( 'Rop_Curl_Methods', 'create_call_process' ) ) ) {
					$request_call = new Rop_Curl_Methods();
					$request_call->create_call_process( $callback_param );
				}
			}


			$this->logger->alert_success( 'Successfully registereed to the Cron Service' );

			return $success;
		} else {
			$error = '{not received}';
			if ( ! empty( $response_array ) ) {
				$error = wp_json_encode( $response_array );
			}
			$this->logger->alert_error( 'Error registering to the Cron Service. Error: ' . $error );

			// Add to error log the message.
			return $response_array['error'];
		}

	}

	/**
	 * Will read and add the user token to the request for authorization.
	 *
	 * @param string $custom_value custom string that will go into ROP-Authorization.
	 *
	 * @return bool
	 * @access private
	 * @since 8.5.5
	 */
	private function fetch_attach_auth_token( $custom_value = '' ) {
		$token = get_option( 'rop_access_token', '' );

		if ( ! empty( $token ) || ! empty( $custom_value ) ) {
			if ( empty( $custom_value ) ) {
				$auth_token  = array(
					'token' => $token,
				);
				$string_data = http_build_query( $auth_token );
				$header_data = base64_encode( $string_data );
			} else {
				$header_data = $custom_value;
			}

			curl_setopt(
				$this->connection,
				CURLOPT_HTTPHEADER,
				array(
					'ROP-Authorization:' . $header_data,
				)
			);

			return true;
		} else {
			return false;
		}

	}


	/**
	 * Handles the account creation on ROP Server.
	 *
	 * @return string
	 */
	private function create_register_data() {
		$local_website_url = get_bloginfo( 'url' );

		// Generate a pseudo-random string of bytes.
		$random_key = openssl_random_pseudo_bytes( 40 );
		// Local WordPress salt
		$local_salt = SECURE_AUTH_SALT . $local_website_url;
		// Auth token creation.
		$created_token = hash( 'sha256', $local_salt . $random_key, false );

		// Compile data that will be sent to the server.
		$account_data = array(
			'email'         => get_bloginfo( 'admin_email' ),
			'website_url'   => $local_website_url,
			'register_hash' => $created_token,
			'date_time'     => current_time( 'mysql' ),
			'timezone'      => Rop_Helpers::local_timezone(),
		);

		// Save the token to the database.
		update_option( 'rop_access_token', $created_token, 'no' );

		$url_encode_data = http_build_query( $account_data );

		return base64_encode( $url_encode_data );

	}

	/**
	 * Destruct magic function, un-sets the cURL connection resource.
	 *
	 * @since 8.5.5
	 */
	function __destruct() {
		unset( $this->connection );
	}
}
