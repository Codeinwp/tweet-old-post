<?php

namespace RopCronSystem;

use RopCronSystem\Curl_Helpers\Rop_Curl_Methods;
use RopCronSystem\ROP_Helpers\Rop_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Handles the load of the new Cron System.
 *
 * Class Rop_Cron_Core
 *
 * @package RopCronSystem
 * @since 8.5.5
 */
class Rop_Cron_Core {

	/**
	 * Rop_Cron_Core constructor.
	 */
	function __construct() {

		add_action( 'rop_process_start_share', array( &$this, 'server_start_share' ) );

		add_action( 'rop_process_stop_share', array( &$this, 'server_stop_share' ) );

		add_action( 'rop_process_update_share_time', array( &$this, 'server_update_time_to_share' ) );

		add_action( 'rop_process_do_register', array( &$this, 'server_register_client' ) );

	}

	public function server_start_share() {

		$time_to_share = current_time( 'timestamp' ) + 30;

		$request_call = new Rop_Curl_Methods();

		$arguments = array(
			'type'          => 'POST',
			'request_path'  => ':activate_account:',
			'time_to_share' => date( 'Y-m-d H:i:s', $time_to_share ),
		);

		$call_response = $request_call->create_call_process( $arguments );
		// TODO add to log.
	}

	public function server_stop_share() {
		$request_call = new Rop_Curl_Methods();

		$arguments = array(
			'type'         => 'POST',
			'request_path' => ':disable_account:',
		);

		$call_response = $request_call->create_call_process( $arguments );
		// TODO add to log.
	}

	public function server_update_time_to_share() {
		$time_to_share = Rop_Helpers::extract_time_to_share();// This will be in UNIX time from the database queue.

		$request_call = new Rop_Curl_Methods();

		$arguments = array(
			'type'          => 'POST',
			'request_path'  => ':share_time:',
			'time_to_share' => date( 'Y-m-d H:i:s', $time_to_share ),
		);

		$call_response = $request_call->create_call_process( $arguments );

		// TODO add to log.
	}

	public function server_register_client() {

	}

}
