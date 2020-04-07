<?php

namespace RopCronSystem\Endpoint_Ping_Server;

use RopCronSystem\Endpoint_Cron_Base\Rop_System_Base;
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

		register_rest_route(
			self::$rop_namespace . self::$rop_version,
			self::$rop_base_tag,
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

		// Get the headers the client is sending.
		$headers = apache_request_headers();

		if ( empty( $headers ) || ! isset( $headers['Rop-Authorization'] ) ) {
			return false;
		}

		// Fetch the client identity from headers.
		$fetch_token = $this->fetch_token_from_headers( $headers['Rop-Authorization'] );

		if ( false === $fetch_token ) {
			return false;
		}

		$fetch_related_data = $this->fetch_next_time_to_share();

		if ( false !== $fetch_related_data ) {
			// This info goes to the ROP server.
			$return_data = array(
				'success'            => true,
				'next-time-to-share' => $fetch_related_data, // test line
			);
		} else {
			// Could not fetch the next time to share.
			$return_data = array(
				'success'            => false,
				'next-time-to-share' => false, // test line
			);
		}

		// TODO action to start the sharing system.

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
		$headers = apache_request_headers();

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
		$time_to_share = time();

		// TODO , read form the database the next time to share and return it.
		return $time_to_share;
	}
}
