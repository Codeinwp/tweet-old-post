<?php
/**
 * Plugin Name: Revive Old Posts E2E Bootstrap
 * Description: Deterministic fixtures and social API mocks for Playwright.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rop_e2e_host = wp_parse_url( home_url(), PHP_URL_HOST );
if ( 'production' === wp_get_environment_type() && ! in_array( $rop_e2e_host, array( 'localhost', '127.0.0.1' ), true ) ) {
	return;
}

const ROP_E2E_NAMESPACE    = 'rop-e2e/v1';
const ROP_E2E_STATE_OPTION = '_rop_e2e_mock_state';

function rop_e2e_require_admin() {
	return current_user_can( 'manage_options' );
}

function rop_e2e_activate_plugin() {
	if ( class_exists( 'Rop_Twitter_Service' ) ) {
		return true;
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	$result = activate_plugin( 'tweet-old-post/tweet-old-post.php' );

	return is_wp_error( $result ) ? $result : class_exists( 'Rop_Twitter_Service' );
}

function rop_e2e_response( $body ) {
	return array(
		'headers'  => array( 'content-type' => 'application/json' ),
		'body'     => wp_json_encode( $body ),
		'response' => array(
			'code'    => 200,
			'message' => 'OK',
		),
		'cookies'  => array(),
		'filename' => null,
	);
}

function rop_e2e_reset() {
	$loaded = rop_e2e_activate_plugin();
	if ( is_wp_error( $loaded ) ) {
		return $loaded;
	}
	if ( $loaded && class_exists( 'Rop_Services_Model' ) ) {
		( new Rop_Services_Model() )->reset_authenticated_services();
	}

	delete_option( ROP_E2E_STATE_OPTION );
	delete_option( 'rop_twitter_via_rs_app' );
	wp_clear_scheduled_hook( 'rop_cron_job_publish_now' );

	// Clear any publish-now queue left behind by a failed test run, otherwise retries re-share stale posts.
	delete_metadata( 'post', 0, 'rop_publish_now', '', true );
	delete_metadata( 'post', 0, 'rop_publish_now_status', '', true );
	delete_metadata( 'post', 0, 'rop_publish_now_accounts', '', true );

	return rest_ensure_response( array( 'ok' => true ) );
}

function rop_e2e_seed_account() {
	$loaded = rop_e2e_activate_plugin();
	if ( is_wp_error( $loaded ) ) {
		return $loaded;
	}
	if ( ! $loaded ) {
		return new WP_Error( 'rop_e2e_plugin_missing', 'Revive Old Posts is not loaded.', array( 'status' => 500 ) );
	}

	$account = array(
		'id'    => 'rop-e2e-service',
		'pages' => array(
			'id'                      => 'rop-e2e-user',
			'name'                    => 'Test Account',
			'screen_name'             => 'testaccount',
			'profile_image_url_https' => '',
			'default_profile_image'   => true,
			'credentials'             => array( 'rop_auth_token' => 'rop-e2e-token' ),
			'activate_account'        => true,
		),
	);

	$twitter = new Rop_Twitter_Service();
	$twitter->add_account_from_rop_server( $account );

	$services = array( $twitter->get_service_id() => $twitter->get_service() );
	$active   = $twitter->get_service_active_accounts();
	$upgrade  = new Rop_Db_Upgrade();

	( new Rop_Services_Model() )->add_authenticated_service( $services );
	$upgrade->migrate_schedule( $active );
	$upgrade->migrate_post_formats( $active );
	update_option( 'rop_twitter_via_rs_app', 'true', false );

	return rest_ensure_response(
		array(
			'ok'        => true,
			'accountId' => array_key_first( $active ),
		)
	);
}

function rop_e2e_run_publish_now( WP_REST_Request $request ) {
	$post_id = absint( $request->get_param( 'postId' ) );
	if ( ! $post_id || ! get_post( $post_id ) ) {
		return new WP_Error( 'rop_e2e_post_missing', 'A valid postId is required.', array( 'status' => 400 ) );
	}

	// Clear the event scheduled at publish time first so wp-cron cannot fire it a second time.
	wp_clear_scheduled_hook( 'rop_cron_job_publish_now' );
	do_action( 'rop_cron_job_publish_now' );

	return rest_ensure_response( array( 'ok' => true ) );
}

function rop_e2e_get_requests() {
	$state = get_option( ROP_E2E_STATE_OPTION, array() );

	return rest_ensure_response( array( 'requests' => isset( $state['requests'] ) ? $state['requests'] : array() ) );
}

add_filter( 'rop_dont_work_on_staging', '__return_false', PHP_INT_MAX );

add_filter(
	'pre_http_request',
	function ( $preempt, $args, $url ) {
		if ( ! defined( 'ROP_POST_ON_X_API' ) || ! in_array( $url, array( ROP_POST_ON_X_API, ROP_POST_LOGS_API ), true ) ) {
			return $preempt;
		}

		$state               = get_option( ROP_E2E_STATE_OPTION, array( 'requests' => array() ) );
		$state['requests'][] = array(
			'url'    => $url,
			'method' => isset( $args['method'] ) ? $args['method'] : 'POST',
			'body'   => isset( $args['body'] ) ? $args['body'] : array(),
		);
		update_option( ROP_E2E_STATE_OPTION, $state, false );

		if ( ROP_POST_ON_X_API === $url ) {
			return rop_e2e_response(
				array(
					'api_body'    => array( 'data' => array( 'id' => 'rop-e2e-tweet' ) ),
					'api_headers' => array(),
				)
			);
		}

		return rop_e2e_response( array( 'status' => true ) );
	},
	10,
	3
);

add_action(
	'rest_api_init',
	function () {
		$admin = array( 'permission_callback' => 'rop_e2e_require_admin' );

		register_rest_route( ROP_E2E_NAMESPACE, '/reset', $admin + array( 'methods' => 'POST', 'callback' => 'rop_e2e_reset' ) );
		register_rest_route( ROP_E2E_NAMESPACE, '/account', $admin + array( 'methods' => 'POST', 'callback' => 'rop_e2e_seed_account' ) );
		register_rest_route( ROP_E2E_NAMESPACE, '/publish-now', $admin + array( 'methods' => 'POST', 'callback' => 'rop_e2e_run_publish_now' ) );
		register_rest_route( ROP_E2E_NAMESPACE, '/requests', $admin + array( 'methods' => 'POST', 'callback' => 'rop_e2e_get_requests' ) );
	}
);
