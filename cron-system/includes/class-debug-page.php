<?php


namespace RopCronSystem\Pages;


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

	function __construct() {
		add_action( 'admin_menu', array( &$this, 'debug_page_menu' ) );

		add_action( 'admin_enqueue_scripts', array( &$this, 'load_custom_wp_admin_style' ) );
	}

	function load_custom_wp_admin_style( $hook ) {
		// Load the JS library ony on this page
		if ( 'revive-old-posts_page_rop_service_debug' === $hook ) {
			wp_enqueue_script( 'rop-debug', ROP_LITE_URL . 'cron-system/assets/js/debug-test.js', array( 'jquery' ), '1.0.0', true );
			// Generate a pseudo-random string of bytes.
			$random_key = Rop_Helpers::openssl_random_pseudo_bytes();
			// Auth token creation.
			$created_token = hash( 'sha256', SECURE_AUTH_SALT . $random_key, false );

			update_option( 'rop_temp_debug', $created_token, 'no' );

			$data_tables = array(
				'local_url'  => get_site_url() . '/wp-json/tweet-old-post-cron/v1/debug-test/',
				'nonce'      => $created_token,
				'remote_url' => ROP_CRON_DOMAIN . '/wp-json/account-status/v1/debug-test/',
			);
			wp_localize_script( 'rop-debug', 'rop_debug', $data_tables );
		}
	}


	function debug_page_menu() {
		add_submenu_page(
			'TweetOldPost',
			__( 'Debug ROP', 'tweet-old-post' ),
			__( 'Debug ROP', 'tweet-old-post' ),
			'manage_options',
			'rop_service_debug',
			array(
				$this,
				'rop_service_debug',
			)
		);
	}


	public function rop_service_debug() {
		$version = phpversion();

		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
		}

		?>
		<div class="wrap" id="rop-debug-table">
			<h1>Debug Info</h1>
			<br/>

			<table>
				<tr>
					<td valign="top"><?php _e( 'PHP Version: ', 'tweet-old-post' ); ?></td>
					<td>
						<?php
						echo $version;

						if ( version_compare( $version, '7.0.0', '<' ) ) {
							echo ' <strong style="color:darkred">PHP 7 is recommended</strong>';
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
							echo '<strong style="color:darkred">No version of CURL detected.</strong>';
						}
						?>
						<br/>
					</td>
				</tr>
				<tr>
					<td valign="top"><?php _e( 'Check connection with<br/>ROP Cron SyStem: ', 'tweet-old-post' ); ?></td>
					<td>
						<?php _e( 'WordPress -> Server:', '' ); ?>
						<span id="server_responded">N/A</span>
						<br/>
						<?php _e( 'Server -> WordPress:', '' ); ?>
						<span id="website_responded">N/A</span>
						<br/>
						<br/>
						<input type="button" value="Check connection" id="rop_conection_check"/>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}
}

