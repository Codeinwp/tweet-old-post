<?php

namespace RopCronSystem\ROP_Helpers;


use Rop_Exception_Handler;
use Rop_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Class Rop_Helpers
 *
 * @package RopCronSystem\ROP_Helpers
 * @since 8.5.5
 */
class Rop_Helpers {


	/**
	 * Extracts the next time to share from the database.
	 * The next time to share does not matter for which social media it belongs.s
	 *
	 * @return bool|int|mixed Unix timestamp with the next time to share
	 * @access public
	 * @static
	 * @since 8.5.5
	 */
	static public function extract_time_to_share() {
		// dates are stored into variable "rop_schedules_data".
		$cron_datetime      = get_option( 'rop_schedules_data', '' );
		$next_time_to_share = 0;

		if ( empty( $cron_datetime ) ) {
			// TODO add to log that there are no items in queue;
			return false;
		}

		if ( is_array( $cron_datetime ) && ! empty( $cron_datetime ) && isset( $cron_datetime['rop_events_timeline'] ) ) {
			$time_list = array();
			// extract all future date-times from all social media types.
			foreach ( $cron_datetime['rop_events_timeline'] as $social => $unix_timestamps ) {
				foreach ( $unix_timestamps as $future_date ) {
					if ( ! empty( $future_date ) && ! in_array( $future_date, $time_list, true ) ) {
						$time_list[] = $future_date;
					}
				}
			}

			if ( empty( $time_list ) ) {
				return false;
			}

			$current_time = current_time( 'timestamp' ); // phpcs:ignore
			// This function sorts an array. Elements will be arranged from lowest to highest when this function has completed.
			sort( $time_list, SORT_NUMERIC );

			foreach ( $time_list as $future_time ) {
				// we need to make sure the next time to share is a future time.
				if ( (int) $future_time > $current_time ) {
					$next_time_to_share = $future_time;
					break;
				}
			}
		}

		return $next_time_to_share;
	}

	/**
	 * Extracts the date change from general settings.
	 *
	 * @return bool|mixed|void
	 * @access public
	 * @static
	 * @since 8.5.5
	 */
	static public function local_timezone() {

		// WordPress saves timezone in 2 different variables.
		// If it's UTC the option name is "".
		$urc_string = get_option( 'gmt_offset', '' ); // can contain int or float numbers
		// If it's valid timezone string the option name is "".
		$timezone_string = get_option( 'timezone_string', '' ); // string only.

		if ( false !== $urc_string && '' !== trim( $urc_string ) ) {
			return $urc_string;
		} elseif ( false !== $timezone_string && '' !== trim( $timezone_string ) ) {
			return $timezone_string;
		}

		return false;
	}

	/**
	 * Returns array with the request headers.
	 *
	 * @return array|false
	 * @since 8.5.5
	 * @access public
	 * @static
	 */
	static public function apache_request_headers() {
		$headers_output        = array();
		$headers_output_return = array();
		if ( ! function_exists( 'apache_request_headers' ) ) {

			foreach ( $_SERVER as $key => $value ) {
				if ( 'HTTP_' === mb_strtoupper( substr( $key, 0, 5 ) ) ) {
					$key                    = str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $key, 5 ) ) ) ) );
					$headers_output[ $key ] = $value;
				} else {
					$headers_output[ $key ] = $value;
				}
			}
		} else {
			$headers_output = apache_request_headers();
		}

		if ( ! empty( $headers_output ) ) {
			foreach ( $headers_output as $header_key => $heaver_value ) {
				$headers_output_return[ strtolower( $header_key ) ] = $heaver_value;
			}
		}

		return $headers_output_return;
	}

	/**
	 * Create a random string.
	 *
	 * @param int $count Default is set to 40.
	 *
	 * @return false|string
	 */
	static public function openssl_random_pseudo_bytes( $count = 40 ) {
		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return openssl_random_pseudo_bytes( $count );
		} else {
			$random = microtime();

			if ( function_exists( 'getmypid' ) ) {
				$random .= getmypid();
			}

			$bytes = '';
			for ( $i = 0; $i < $count; $i += 16 ) {
				$random = md5( microtime() . $random );
				$bytes  .= md5( $random, true );
			}

			return substr( $bytes, 0, $count );

		}
	}

	/**
	 * Function used to create custom requests to the Cron Server.
	 *
	 * @param string $url Server endpoint.
	 * @param array  $post_arguments Parameters sent over to server end-point.
	 *
	 * @return bool|string
	 */
	static public function custom_curl_post_request( $url = '', $post_arguments = array() ) {

		$logger = new Rop_Logger();

		if ( empty( $url ) ) {
			$logger->alert_error( 'Could not update the Cron Server, the URL is missing.' );

			return false;
		}

		$token = get_option( 'rop_access_token', '' );
		if ( empty( $token ) ) {
			$logger->alert_error( 'Could not update the Cron Server, your access token is missing from the database.' );

			return false;
		}

		$connection = curl_init( $url );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $connection, CURLOPT_POST, true );

		$post_data = '';

		if ( ! empty( $post_arguments ) ) {
			$post_data = build_query( $post_arguments );
			curl_setopt( $connection, CURLOPT_POSTFIELDS, $post_data );
		}

		$auth_token  = array(
			'token' => $token,
		);
		$string_data = http_build_query( $auth_token );
		$header_data = base64_encode( $string_data );

		curl_setopt(
			$connection,
			CURLOPT_HTTPHEADER,
			array(
				'rop-authorization:' . $header_data,
				'Accept: */*',
				'Content-Length: ' . strlen( $post_data ),
			)
		);

		/**
		 * Accept up to 3 maximum redirects before cutting the connection.
		 */
		curl_setopt( $connection, CURLOPT_MAXREDIRS, 3 );
		curl_setopt( $connection, CURLOPT_FOLLOWLOCATION, true );

		$server_response_body = curl_exec( $connection );

		error_log( '$server_response_body: ' . var_export( $server_response_body, true ) );

		$http_code            = curl_getinfo( $connection, CURLINFO_HTTP_CODE );
		curl_close( $connection );

		if ( absint( $http_code ) !== 200 ) {
			$logger->alert_error( 'Cron server connection code : ' . $http_code );
		} else {
			$response_array = json_decode( $server_response_body, true );

			if ( ! empty( $response_array ) && json_last_error() === JSON_ERROR_NONE ) {

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
					// Making sure to cast the value into boolean.
					$success = filter_var( $response_success, FILTER_VALIDATE_BOOLEAN );

					if ( true === $success ) {
						$logger->alert_success( $response_array['message'] );
					} else {
						// An issue was found.
						$error = '';
						if ( isset( $response_array['message'] ) ) {
							$error = $response_array['message'];
						} elseif ( isset( $response_array['error'] ) ) {
							$error = $response_array['error'];
						}

						if ( ! empty( $error ) ) {
							$logger->alert_error( 'Error registering to the Cron Service. Error: ' . $error );
						}
					}
				} else {
					// The success variable was not found.
					$error = '';
					if ( isset( $response_array['message'] ) ) {
						$error = $response_array['message'];
					} elseif ( isset( $response_array['error'] ) ) {
						$error = $response_array['error'];
					}

					if ( ! empty( $error ) ) {
						$logger->alert_error( 'Error registering to the Cron Service. Error: ' . $error );
					}
				}
			} else {
				$logger->alert_error( 'Cron server could not be reached to update the timer.' );
			}
		}

		return $server_response_body;
	}
}


