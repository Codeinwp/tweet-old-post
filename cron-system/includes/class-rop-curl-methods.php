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
	const SERVER_URL = ROP_CRON_DOMAIN . '/wp-json/';

	/**
	 * CURL connection resource.
	 *
	 * @var resource cURL connection object.
	 * @since 8.5.5
	 */
	private $connection;

	/**
	 * Possible requests to the cron server.
	 *
	 * @var array Fixed server paths that can be called.
	 * @since 8.5.5
	 */
	private $server_paths = array(
		':activate_account:' => 'account-status/v1/activate-account/',
		':disable_account:'  => 'account-status/v1/disable-account/',
		':register_account:' => 'rop-register-data/v1/register-new-user/',
		':share_time:'       => 'update-cron-ping/v1/update-time-to-share/',
		':delete_account:'   => 'account-status/v1/delete-account/',
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
	 * Full server remote URL.
	 *
	 * @var string Server API path concatenated with endpoint path.
	 * @since 8.5.5
	 */
	private $server_url = '';

	/**
	 * Load ROP plugin Logger class.
	 *
	 * Rop_Curl_Methods constructor.
	 */
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
			'type'            => 'POST',
			'request_path'    => '',
			'time_to_share'   => '',
			'remove_location' => get_bloginfo( 'url' ),
		);

		$args = wp_parse_args( $args, $default );

		if ( empty( $args['request_path'] ) ) {
			// TODO add to log, important parameter missing. "Please specify the server path __CLASS__ > __FUNCTION__ > request_path"
			return false;
		}

		$token = get_option( 'rop_access_token', '' );

		if ( 'post' === strtolower( $args['type'] ) ) {

			if ( ':delete_account:' !== $args['request_path'] ) {
				// unset( $args['remove_location'] );
			}

			$post_fields = array();

			if ( ':register_account:' === $args['request_path'] ) {

				$this->server_url = self::SERVER_URL . $this->server_paths[ $args['request_path'] ];
				$this->connection = curl_init( $this->server_url );
				$this->register_to_top_server();
			} else {

				if ( empty( $token ) && ':delete_account:' !== $args['request_path'] ) {
					$this->server_url = self::SERVER_URL . $this->server_paths[':register_account:'];
					$this->connection = curl_init( $this->server_url );

					// If the request comes with "Stop cron" action, there's no need for it in account registration.
					if ( ':disable_account:' === $args['request_path'] ) {
						$args = array();
					}

					$this->register_to_top_server( $args );
				} else {
					error_log('here');

					$this->server_url = self::SERVER_URL . $this->server_paths[ $args['request_path'] ];

					error_log( 'this->server_url: ' . var_export( $this->server_url, true ) );

					$this->connection = curl_init( $this->server_url );

					if ( isset( $args['time_to_share'] ) && ! empty( $args['time_to_share'] ) ) {
						$post_fields = array( 'next_ping' => $args['time_to_share'] );

					}

					return $this->request_type_post( $post_fields, $args['request_path'] );
				}
			}
		}

		return true;

	}


	/**
	 * Handles the API calls of type POST.
	 *
	 * @param array  $post_arguments Post arguments array.
	 * @param string $path_action Action type.
	 *
	 * @return bool|string
	 * @access private
	 * @since 8.5.5
	 */
	private function request_type_post( $post_arguments = array(), $path_action = '' ) {

		curl_setopt( $this->connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->connection, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $this->connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->connection, CURLOPT_POST, true );
		curl_setopt( $this->connection, CURLOPT_ENCODING, '' );
		curl_setopt( $this->connection, CURLOPT_FAILONERROR, true );
		/**
		 * Accept up to 3 maximum redirects before cutting the connection.
		 */
		curl_setopt( $this->connection, CURLOPT_MAXREDIRS, 3 );
		curl_setopt( $this->connection, CURLOPT_FOLLOWLOCATION, true );

		$params_stringified = '';
		// Some requests will contain parameters.
		if ( ! empty( $post_arguments ) ) {
			$params_stringified = build_query( $post_arguments );
			curl_setopt( $this->connection, CURLOPT_POSTFIELDS, $params_stringified );
		}

		$authentication = $this->fetch_attach_auth_token( '', $params_stringified );

		if ( false === $authentication ) {
			unset( $this->connection );
			// TODO add to log, cient token is missing.

			exit;
		}

		$server_response_body = curl_exec( $this->connection );

		if ( false === $server_response_body || curl_errno( $this->connection ) ) {
			error_log( 'Curl error: ' . curl_error( $this->connection ) );
		}

		$http_code = curl_getinfo( $this->connection, CURLINFO_HTTP_CODE );
		if ( absint( $http_code ) !== 200 ) {
			$this->logger->alert_error( 'Cron server connection code is : ' . $http_code );
		}
		// TODO check $http_code and add to the Log if it's not the expected 200.
		curl_close( $this->connection );

		// Decode JSON string.
		$response_array = json_decode( $server_response_body, true );

		$response_success = null;
		// if custom message is received.
		if ( is_array( $response_array ) && isset( $response_array['success'] ) ) {
			$response_success = $response_array['success'];

			// If customized WP_Error is received.
		} elseif ( is_array( $response_array ) && isset( $response_array['data'] ) && isset( $response_array['data']['success'] ) ) {
			$response_success = $response_array['data']['success'];
		}

		// If the response contains the success variable.
		if ( ! is_null( $response_success ) ) {

			// cast the value to make sure it's not set as string.
			$success = filter_var( $response_success, FILTER_VALIDATE_BOOLEAN );

			// If the response was a success.
			if ( true === $success ) {
				switch ( $path_action ) {
					case ':activate_account:':
						// Inform the user about the action
						$this->logger->alert_success( 'Remote cron: Started.' );
						break;
					case ':disable_account:':
						// Inform the user about the action
						$this->logger->alert_success( 'Remote cron: Stopped.' );
						break;
					case ':share_time:':
						// Inform the user about the action
						$this->logger->alert_success( 'Remote cron: Share time sent' );
						break;
				}
			}

			return $success;
		} else {
			// The success variable was not found.
			$error = '';

			if ( isset( $response_array['message'] ) ) {
				$error = $response_array['message'];
			} elseif ( isset( $response_array['error'] ) ) {
				$error = $response_array['error'];
			} elseif ( ! empty( $response_array ) ) {
				$error = wp_json_encode( $response_array );
			}

			// Let's try our best to inform the user about possible issues found.
			if ( ! empty( $error ) ) {
				$this->logger->alert_error( 'Could not process the request. Error message : ' . $error );
			} else {
				$this->logger->alert_error( "Could not reach the Cron Service, HTTP Code: {$http_code}" );
			}

			// Add to error log the message.
			if ( isset( $response_array['error'] ) ) {
				return $response_array['error'];
			}

			return false;
		}
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
		curl_setopt( $this->connection, CURLOPT_FAILONERROR, true );
		curl_setopt( $this->connection, CURLOPT_ENCODING, '' );

		curl_setopt( $this->connection, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
		/**
		 * Accept up to 3 maximum redirects before cutting the connection.
		 */
		curl_setopt( $this->connection, CURLOPT_MAXREDIRS, 3 );
		curl_setopt( $this->connection, CURLOPT_FOLLOWLOCATION, true );

		$base64_register_data = $this->create_register_data();

		// Get the authentication token.
		$authentication = $this->fetch_attach_auth_token( $base64_register_data, '' );
		if ( false === $authentication ) {
			unset( $this->connection );
			// TODO add to log, client token is missing.

			exit;
		}

		// Execute the cURL call.
		$server_response_body = curl_exec( $this->connection );

		// If the response is an error we try to display usable information.
		if ( false === $server_response_body || curl_errno( $this->connection ) ) {
			error_log( 'Curl error: ' . curl_error( $this->connection ) );
		}

		// Get the request apache code.
		$http_code = curl_getinfo( $this->connection, CURLINFO_HTTP_CODE );

		if ( absint( $http_code ) !== 200 ) {
			$this->logger->alert_error( 'Cron server connection code : ' . $http_code );
		}
		// TODO check $http_code and add to the Log if it's not the expected 200.
		curl_close( $this->connection );

		// Decode JSON string.
		$response_array = json_decode( $server_response_body, true );

		$response_success = null;
		// if custom message is received.
		if ( isset( $response_array['success'] ) ) {
			$response_success = $response_array['success'];

			// If customized WP_Error is received.
		} elseif ( isset( $response_array['data'] ) && isset( $response_array['data']['success'] ) ) {
			$response_success = $response_array['data']['success'];
		}

		// If the response contains the success variable.
		if ( ! is_null( $response_success ) ) {
			// cast the value to make sure it's not set as string.
			$success = filter_var( $response_success, FILTER_VALIDATE_BOOLEAN );

			// If the response was a success.
			if ( true === $success ) {

				if ( ! empty( $callback_param ) ) {
					// Being registered with success, let's do the requested call.
					$request_call = new Rop_Curl_Methods();
					$request_call->create_call_process( $callback_param );
				}
				// Inform the user about the action
				$this->logger->alert_success( 'Successfully registered to the Cron Service' );
			} else {
				$error = '{not received}';
				if ( isset( $response_array['message'] ) ) {
					$error = $response_array['message'];
				} elseif ( isset( $response_array['error'] ) ) {
					$error = $response_array['error'];
				} elseif ( ! empty( $response_array ) ) {
					$error = wp_json_encode( $response_array );
				}

				// Some error was encountered, inform the user about it.
				$this->logger->alert_error( 'Could not register to the Cron Service. Error message: ' . $error );

				delete_option( 'rop_access_token' );
			}

			return $success;

		} else {
			// The success variable was not found.
			$error = '';
			if ( isset( $response_array['message'] ) ) {
				$error = $response_array['message'];
			} elseif ( isset( $response_array['error'] ) ) {
				$error = $response_array['error'];
			} elseif ( ! empty( $response_array ) ) {
				$error = wp_json_encode( $response_array );
			}

			// Let's try our best to inform the user about possible issues found.
			if ( ! empty( $error ) ) {
				$this->logger->alert_error( 'Error registering to the Cron Service. Error: ' . $error );
			} else {
				$this->logger->alert_error( "Could not reach the Cron Service, HTTP Code: {$http_code}" );
			}

			// We need this removed because it will be recreated once a retry is made.
			delete_option( 'rop_access_token' );

			// Add to error log the message.
			if ( isset( $response_array['error'] ) ) {
				return $response_array['error'];
			}

			return false;
		}

	}

	/**
	 * Will read and add the user token to the request for authorization.
	 *
	 * @param string $custom_value custom string that will go into ROP-Authorization.
	 *
	 * @param string $post_data Post data used for content length.
	 *
	 * @return bool
	 * @access private
	 * @since 8.5.5
	 */
	private function fetch_attach_auth_token( $custom_value = '', $post_data = '' ) {
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
					'rop-authorization:' . $header_data,
					'Accept: */*',
					'Content-Length: ' . strlen( $post_data ),
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
		$random_key = Rop_Helpers::openssl_random_pseudo_bytes();
		// Local WordPress salt
		$local_salt = SECURE_AUTH_SALT . $local_website_url;
		// Auth token creation.
		$created_token = hash( 'sha256', $local_salt . $random_key, false );

		$client_email = ( defined( 'ROP_CRON_ALTERNATIVE_DEMO_EMAIL' ) ) ? ROP_CRON_ALTERNATIVE_DEMO_EMAIL : get_bloginfo( 'admin_email' );
		// Compile data that will be sent to the server.
		$account_data = array(
			'email'         => $client_email,
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
