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
 * Used to test if the server can reach this point.
 *
 * Class Rop_Debug_Ping
 *
 * @package RopCronSystem\Endpoint_Ping_Server
 * @since 8.5.5
 */
class Rop_Debug_Ping {
	/**
	 * Defining endpoint namespace.
	 *
	 * @var string Endpoint namespace.
	 * @since 8.5.5
	 * @access private
	 * @static
	 */
	private static $rop_namespace = 'tweet-old-post-cron/v';

	/**
	 * Defined endpoint version.
	 *
	 * @var string Endpoint version.
	 * @since 8.5.5
	 * @access private
	 * @static
	 */
	private static $rop_version = '1';

	/**
	 * Defined endpoint base.
	 *
	 * @var string Endpoint base tag.
	 * @since 8.5.5
	 * @access private
	 * @static
	 */
	private static $rop_base_tag = 'debug-test';

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
				'callback'            => array( &$this, 'process_the_request' ),
				'args'                => array(
					'secret_temp_key' => array( // This is expected in $_GET parameters and it's required
						'validate_callback' => function ( $parameter, $request, $key ) {
							// If test_parameter does not contain allowed parameter, we validate it as false

							return ! empty( $parameter );
						},
						'required'          => true,
					),
				),
				'permission_callback' => array( &$this, 'catch_authorization_data_permissions' ), // Callback method for this endpoint
			)
		);
	}

	/**
	 * Retrieves end-point data and responds with json element.
	 *
	 * @param WP_REST_Request $request End-point data sent to the endpoint.
	 *
	 * @since 8.5.5
	 * @access public
	 */
	public function process_the_request( WP_REST_Request $request ) {
		$key       = $request->get_param( 'secret_temp_key' );
		$local_key = trim( get_option( 'rop_temp_debug', '' ) );

		$return_data = array(
			'success' => false,
		);

		if ( ! empty( $local_key ) ) {
			if ( $key === $local_key ) {
				$return_data['success'] = true;
			}
		}

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
		// verify if the item token is set or not
		return true;
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

}
