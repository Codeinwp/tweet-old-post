<?php

namespace RopCronSystem\Endpoint_Ping_Server;

use Rop_Admin;
use RopCronSystem\Endpoint_Cron_Base\Rop_System_Base;
use RopCronSystem\ROP_Helpers\Rop_Helpers;
use WP_REST_Request;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}


/**
 * Handles the pings received from ROP Server.
 *
 * Class Rop_Ping_System
 *
 * @package RopCronSystem\Endpoint_Ping_Server
 * @since 8.5.5
 */
class Rop_Ping_System extends Rop_System_Base {
	/**
	 * @var string Endpoint namespace.
	 * @since 8.5.5
	 * @access private
	 * @static
	 */
	private static $rop_namespace = 'share-now/v';

	/**
	 * @var string Endpoint version.
	 * @since 8.5.5
	 * @access private
	 * @static
	 */
	private static $rop_version = '1';

	/**
	 * @var string Endpoint base tag.
	 * @since 8.5.5
	 * @access private
	 * @static
	 */
	private static $rop_base_tag = 'do-share';

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see  WP_REST_Controller::register_routes();
	 *
	 * @since 8.5.5
	 * @access public
	 */
	public function register_routes() {

		$namespace = self::$rop_namespace . self::$rop_version;
		$base_tag  = self::$rop_base_tag;

		register_rest_route(
			$namespace,
			$base_tag,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( &$this, 'catch_authorization_data' ),
				'permission_callback' => array( &$this, 'catch_authorization_data_permissions' ), // Callback method for this endpoint
			)
		);
	}

	/**
	 * Retrieves end-point data and responds with json element.
	 *
	 * @param WP_REST_Request $request End-point data sent to the endpoint.
	 *
	 * @return bool
	 * @since 8.5.5
	 * @access public
	 */
	public function catch_authorization_data( WP_REST_Request $request ) {
		error_log( '### ' . __FUNCTION__ );
		// Get the headers the client is sending.
		$headers = Rop_Helpers::apache_request_headers();

		if ( empty( $headers ) || ! isset( $headers['Rop-Authorization'] ) ) {
			error_log( '### ' . __FUNCTION__ . ' header issue' );

			return false;
		}

		// Fetch the client identity from headers.
		$fetch_token = $this->fetch_token_from_headers( $headers['Rop-Authorization'] );

		if ( false === $fetch_token ) {
			error_log( '### ' . __FUNCTION__ . ' token issue' );

			return false;
		}

		$fetch_related_data = $this->fetch_next_time_to_share();

		if ( false !== $fetch_related_data ) {
			// This info goes to the ROP server.
			$return_data = array(
				'success'            => true,
				'next-time-to-share' => $fetch_related_data, // test line
				'timezone'           => Rop_Helpers::local_timezone(),
			);
			error_log( '### ' . __FUNCTION__ . ' next time to share : ' . $fetch_related_data );
			$admin = new Rop_Admin();
			$admin->rop_cron_job();
		} else {
			error_log( '### ' . __FUNCTION__ . ' next time to share ISSUE : ' . $fetch_related_data );
			// Could not fetch the next time to share.
			$return_data = array(
				'success'            => false,
				'next-time-to-share' => false, // test line
			);
		}
		error_log( '### ' . __FUNCTION__ . ' reached end of function : ' . $fetch_related_data );


		wp_send_json( $return_data );
	}

	/**
	 * Handles the endpoint restriction.
	 * Here we will validate the client token.
	 *
	 * @return bool
	 * @since 8.5.5
	 * @access public
	 */
	public function catch_authorization_data_permissions() {
		// Get the headers the client is sending.
		#error_log(var_export(function_exists('apache_request_headers'),true));
		$headers = Rop_Helpers::apache_request_headers();

		if ( empty( $headers ) || ! isset( $headers['Rop-Authorization'] ) ) {
			return false;
		}

		return $this->is_valid_token( $headers['Rop-Authorization'] );
	}


	/**
	 * Register the endpoint using WP Hook.
	 *
	 * @since 8.5.5
	 * @access public
	 */
	public function init_rest_api_route() {
		add_action( 'rest_api_init', array( &$this, 'register_routes' ) );
	}


	/**
	 * Reads from the database the next time to share.
	 *
	 * @return int Unix Timestamp with next time to share.
	 * @since 8.5.5
	 * @access private
	 */
	private function fetch_next_time_to_share() {
		$time_to_share = Rop_Helpers::extract_time_to_share();// This will be in UNIX time from the database queue.

		if ( empty( $time_to_share ) ) {
			$this->logger->alert_error( 'Could not fetch future share timer.' );

			return false;
		}

		return $time_to_share;
	}
}
