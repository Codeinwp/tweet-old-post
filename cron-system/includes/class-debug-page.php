<?php


namespace RopCronSystem\Pages;

use RopCronSystem\Curl_Helpers\Rop_Curl_Methods;
use RopCronSystem\ROP_Helpers\Rop_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * WP-Admin debug page
 *
 * Class Debug_Page
 *
 * @package RopCronSystem\Pages
 */
class Debug_Page {

	/**
	 * Init the debug page for remote Cron system.
	 *
	 * Debug_Page constructor.
	 */
	function __construct() {
		add_action( 'admin_menu', array( &$this, 'debug_page_menu' ) );

		add_action( 'admin_enqueue_scripts', array( &$this, 'load_custom_wp_admin_style' ) );

		// Used to remove local cron auth key.
		add_action( 'wp_ajax_reset_local_auth_key', array( &$this, 'reset_local_client' ) );

		// Delete account from remote Cron server.
		add_action( 'wp_ajax_remove_remote_account', array( &$this, 'cron_system_delete_account' ) );
	}

	/**
	 * Used to delete the remote user account.
	 */
	public function cron_system_delete_account() {
		$response = array();

		$token = get_option( 'rop_access_token', '' );

		if ( empty( $token ) ) {
			$response['success'] = false;
			$response['message'] = __( 'To delete the remove cron account, you need the authentication key.', 'tweet-old-post' );

		} else {
			$request_call = new Rop_Curl_Methods();
			$arguments    = array(
				'type'         => 'POST',
				'request_path' => ':delete_account:',
			);

			$call_response = $request_call->create_call_process( $arguments );

			// Delete local key.
			delete_option( 'rop_access_token' );
			// Reset cron to use local.
			update_option( 'rop_use_remote_cron', 'no' );
			// Reset the agreement checkbox.
			update_option( 'rop_remote_cron_terms_agree', 'no' );

			$response['success'] = true;
			$response['message'] = __( 'Remote account removed and the plugin reverted to using your WordPress CronJob.', 'tweet-old-post' );
		}

		wp_send_json( $response );
	}

	/**
	 * Remove current Cron server authentication key.
	 * Switch the CronType to the local cron.
	 * Uncheck the agreement checkbox in General Settings.
	 *
	 * @access public
	 * @since 0.0.1
	 */
	public function reset_local_client() {
		$response = array();

		// Delete local key.
		delete_option( 'rop_access_token' );
		// Reset cron to use local.
		update_option( 'rop_use_remote_cron', 'no' );
		// Reset the agreement checkbox.
		update_option( 'rop_remote_cron_terms_agree', 'no' );

		$response['success'] = true;
		$response['message'] = __( 'The authentication has been removed.', 'tweet-old-post' );

		wp_send_json( $response );
	}

	/**
	 * Load the CSS/JS required for the debug page.
	 *
	 * @param string $hook WordPress current page hook.
	 *
	 * @access public
	 * @since 0.0.1
	 */
	public function load_custom_wp_admin_style( $hook ) {
		// Load the JS library ony on this page
		if ( 'revive-old-posts_page_rop_service_debug' === $hook ) {
			wp_enqueue_script( 'rop-debug', ROP_LITE_URL . 'cron-system/assets/js/debug-test.js', array( 'jquery' ), '1.0.0', true );
			// Generate a pseudo-random string of bytes.
			$random_key = Rop_Helpers::openssl_random_pseudo_bytes();
			// Auth token creation.
			$created_token = hash( 'sha256', SECURE_AUTH_SALT . $random_key, false );

			update_option( 'rop_temp_debug', $created_token, 'no' );

			$data_tables = array(
				'local_url'      => get_site_url() . '/wp-json/tweet-old-post-cron/v1/debug-test/',
				'nonce'          => $created_token,
				'remote_url'     => ROP_CRON_DOMAIN . '/wp-json/account-status/v1/debug-test/',
				'action_success' => __( 'Request completed', 'tweet-old-post' ),
				'action_fail'    => __( 'Requested failed to complete.', 'tweet-old-post' ),
			);
			wp_localize_script( 'rop-debug', 'rop_debug', $data_tables );
		}
	}

	/**
	 * Add the item as submenu.
	 *
	 * @access public
	 * @since 0.0.1
	 */
	public function debug_page_menu() {
		add_submenu_page(
			'TweetOldPost',
			__( 'Debug Remote Cron', 'tweet-old-post' ),
			__( 'Debug Remote Cron', 'tweet-old-post' ),
			'manage_options',
			'rop_service_debug',
			array(
				$this,
				'rop_service_debug',
			)
		);
	}


	/**
	 * Display the HTML page for Debug ROP.
	 *
	 * @since 0.0.1
	 * @access public
	 */
	public function rop_service_debug() {
		$version = phpversion();

		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
		}

		?>
		<div class="wrap" id="rop-debug-table">
		<h1><?php _e( 'Debug Info: ', 'tweet-old-post' ); ?></h1>
		<br/>

		<table>
			<tr>
			<td valign="top"><?php _e( 'PHP Version: ', 'tweet-old-post' ); ?></td>
			<td>
				<?php
				echo $version;

				if ( version_compare( $version, '7.0.0', '<' ) ) {
					echo ' <strong style="color:darkred">' . __( 'PHP 7 is recommended', 'tweet-old-post' ) . '</strong>';
				}

				?>
				<br/>
			</td>
			</tr>
			<tr>
			<td valign="top"><?php _e( 'cURL Info: ', 'tweet-old-post' ); ?></td>
			<td>
				<?php
				if ( ! empty( $curl_version ) ) {
					echo 'version: ' . $curl_version['version'] . ' (' . $curl_version['version_number'] . ') ' . '<br/>';
					echo 'libz version: ' . $curl_version['libz_version'] . '<br/>';
					echo 'OpenSSL: ' . $curl_version['ssl_version'] . '<br/>';
					echo '<strong>Protocols</strong>: <br/>'; // . implode( ',', $curl_version['protocols'] ) . '<br/>';

					echo 'HTTP: ' . ( ( in_array( 'http', $curl_version['protocols'] ) ) ? '<span style="color:darkgreen">&#10004;</span>' : '<span style="color:darkred">&#10006;</span>' ) . '<br/>';
					echo 'HTTPS: ' . ( ( in_array( 'https', $curl_version['protocols'] ) ) ? '<span style="color:darkgreen">&#10004;</span>' : '<span style="color:darkred">&#10006;</span>' ) . '<br/>';

				} else {
					echo '<strong style="color:darkred">' . __( 'No version of CURL detected.', 'tweet-old-post' ) . '</strong>';
				}
				?>
				<br/>
			</td>
			</tr>
			<tr>
			<td valign="top"><?php _e( 'Check connection with<br/>Revive Social Cron SyStem: ', 'tweet-old-post' ); ?></td>
			<td>
				<?php _e( 'WordPress -> Server:', 'tweet-old-post' ); ?>
				<span id="server_responded">N/A</span>
				<br/>
				<?php _e( 'Server -> WordPress:', 'tweet-old-post' ); ?>
				<span id="website_responded">N/A</span>
				<br/>
				<br/>
				<input type="button" value="<?php _e( 'Check connection', 'tweet-old-post' ); ?>" id="rop_conection_check"/>
			</td>
			</tr>
		</table>

		<br/>
		<hr/>
		<br/>

		<table>
			<tr>
			<td>
				<input type="button" value="<?php _e( 'Delete Remote Cron Service Data', 'tweet-old-post' ); ?>" id="rop_remove_account"/>
				<span id="ajax_rop_remove_account">

				</span>

				<p>
				<em>
					<?php
					$labels = new \Rop_I18n();
					echo $labels::get_labels( 'cron_system.delete_cron_service_account_info' );
					?>
				</em>
				</p>
			</td>
			</tr>
		</table>

		<table>
			<tr>
			<td>
				<input type="button" value="<?php _e( 'Clear Local Cron Data', 'tweet-old-post' ); ?>" id="rop_clear_local"/>
				<span id="ajax_rop_clear_local">

				</span>

				<p>
				<em>
					<?php
					echo $labels::get_labels( 'cron_system.clear_local_cron_info' );
					?>
				</em>
				</p>
			</td>
			</tr>
		</table>


		</div>
		<?php
	}
}
