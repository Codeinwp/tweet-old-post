<?php

namespace RopCronSystem;

use RopCronSystem\Curl_Helpers\Rop_Curl_Methods;
use RopCronSystem\Endpoint_Ping_Server\Rop_Debug_Ping;
use RopCronSystem\Endpoint_Ping_Server\Rop_Ping_System;
use RopCronSystem\Endpoint_Ping_Server\Rop_Registration_Check;
use RopCronSystem\Pages\Debug_Page;
use RopCronSystem\ROP_Helpers\Rop_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

// For testing purpose.
#define( 'ROP_CRON_ALTERNATIVE_DEMO_EMAIL', 'mihai@wpriders.com' );
// ROP Cron System Server URL, no "/" slash a t the end.
#define( 'ROP_CRON_DOMAIN', 'https://ropserver.ernomo.re' );
define( 'ROP_CRON_DOMAIN', 'https://ropserver.wpr' );

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

		/**
		 * Register to ROP Cron the share start.
		 */
		add_action( 'rop_process_start_share', array( &$this, 'server_start_share' ) );
		/**
		 * Register to ROP Cron the share stop.
		 */
		add_action( 'rop_process_stop_share', array( &$this, 'server_stop_share' ) );
		/**
		 * Register to ROP Cron the next valid time to share from queue.
		 */
		add_action( 'rop_process_update_share_time', array( &$this, 'server_update_time_to_share' ) );
		/**
		 * Register to ROP Cron a new account.
		 */
		add_action( 'rop_process_do_register', array( &$this, 'server_register_client' ) );
		/**
		 * Register local end-points used by ROP Cron Service.
		 */
		add_action( 'init', array( &$this, 'init_endpoint_items' ) );


	}

	/**
	 * Register local end-points used by ROP Cron Service.
	 *
	 * @access public
	 * @since 8.5.5
	 */
	public function init_endpoint_items() {

		// Share now function.
		$share_now = new Rop_Ping_System();
		$share_now->init_rest_api_route();

		$debug = new Rop_Debug_Ping();
		$debug->init_rest_api_route();

		$registration_check = new Rop_Registration_Check();
		$registration_check->init_rest_api_route();

		new Debug_Page();
	}

	/**
	 * Register to ROP Cron the share start.
	 * @access public
	 * @since 8.5.5
	 */
	public function server_start_share() {

		$time_to_share = current_time( 'timestamp' ) + 30; // phpcs:ignore

		$request_call = new Rop_Curl_Methods();

		$arguments = array(
			'type'          => 'POST',
			'request_path'  => ':activate_account:',
			'time_to_share' => date( 'Y-m-d H:i:s', $time_to_share ),// phpcs:ignore
		);

		$call_response = $request_call->create_call_process( $arguments );
		// TODO add to log.
	}

	/**
	 * Register to ROP Cron the share stop.
	 * @access public
	 * @since 8.5.5
	 */
	public function server_stop_share() {
		error_log( 'Stop was sent' );
		$request_call = new Rop_Curl_Methods();

		$arguments = array(
			'type'         => 'POST',
			'request_path' => ':disable_account:',
		);

		$call_response = $request_call->create_call_process( $arguments );
		// TODO add to log.
	}

	/**
	 * Register to ROP Cron the next valid time to share from queue.
	 * @access public
	 * @since 8.5.5
	 */
	public function server_update_time_to_share() {
		$time_to_share = Rop_Helpers::extract_time_to_share();// This will be in UNIX time from the database queue.

		$request_call = new Rop_Curl_Methods();

		$arguments = array(
			'type'          => 'POST',
			'request_path'  => ':share_time:',
			'time_to_share' => date( 'Y-m-d H:i:s', $time_to_share ),// phpcs:ignore
		);

		$call_response = $request_call->create_call_process( $arguments );

		// TODO add to log.
	}

	/**
	 * Register to ROP Cron a new account.
	 * @access public
	 * @since 8.5.5
	 */
	public function server_register_client() {
		$request_call = new Rop_Curl_Methods();

		$arguments     = array(
			'type'         => 'POST',
			'request_path' => ':register_account:',
		);
		$call_response = $request_call->create_call_process( $arguments );

	}

}
