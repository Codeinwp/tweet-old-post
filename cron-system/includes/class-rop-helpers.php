<?php

namespace RopCronSystem\ROP_Helpers;


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
		if ( ! function_exists( 'apache_request_headers' ) ) {
			$headers_output = array();

			foreach ( $_SERVER as $key => $value ) {
				if ( 'HTTP_' === mb_strtoupper( substr( $key, 0, 5 ) ) ) {
					$key                    = str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $key, 5 ) ) ) ) );
					$headers_output[ $key ] = $value;
				} else {
					$headers_output[ $key ] = $value;
				}
			}

			return $headers_output;
		} else {
			return apache_request_headers();
		}
	}

	/**
	 * Create a random string.
	 *
	 * @param int $count Default is set to 40
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
}


