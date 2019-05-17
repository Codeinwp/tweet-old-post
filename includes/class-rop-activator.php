<?php

/**
 * Fired during plugin activation
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      8.0.0
 * @package    Rop
 * @subpackage Rop/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public static function activate() {

		// Add version when ROP was first installed on site to DB.
		$rop_first_install_version = get_option( 'rop_first_install_version' );

		if ( class_exists( 'Rop' ) ) {
			$rop = new Rop();
			$version = $rop->get_version();
		}

		if ( empty( $rop_first_install_version ) && ! empty( $version ) ) {
			add_option( 'rop_first_install_version', $version );
		}

		$logger = new Rop_Logger();

		// Get unique token
		if ( ! class_exists( '\GuzzleHttp\Client' ) ) {
			$logger->alert_error( 'Error: Cannot find Guzzle' );
			return;
		}

		$client = new GuzzleHttp\Client();

		try {
			$app_url = ROP_AUTH_APP_URL . ROP_APP_ACTIVATION_PATH;
			$current_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://' . $_SERVER[ HTTP_HOST ];
			$email = base64_encode( get_option( 'admin_email' ) );

			$response = $client->request( 'GET', $app_url . '?activate=true&url=' . $current_url . '&data=' . $email );
			$token = $response->getBody()->getContents();

			if ( ! get_option( ROP_APP_TOKEN_OPTION ) ) {
				$deprecated = ' ';
				$autoload = 'no';
				add_option( ROP_APP_TOKEN_OPTION, $token, $deprecated, $autoload );
			} else {
				update_option( ROP_APP_TOKEN_OPTION, $token );
			}
		} catch ( GuzzleHttp\Exception\GuzzleException $e ) {
			$logger->alert_error( 'Error ' . $e->getCode() . '. ' . $e->getMessage() . "\n" . $e->getTrace() );

		}
	}

}
