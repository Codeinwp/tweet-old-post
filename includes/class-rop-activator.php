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
 * @author     ThemeIsle <friends@revive.social>
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

		self::rop_create_install_token();

	}

	/**
	 * ROP installation tasks
	 *
	 * Creates unique ID for website used during authentication requests
	 *
	 * @since      8.3.0
	 * @package    Rop
	 * @subpackage Rop/includes
	 * @author     ThemeIsle <friends@revive.social>
	 */
	private static function rop_create_install_token() {

		$logger = new Rop_Logger();

		$app_url = ROP_AUTH_APP_URL . ROP_APP_ACTIVATION_PATH;

		if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) {
			$protocol = 'https';
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
			$protocol = 'https';
		} else {
			$protocol = 'http';
		}

			$current_url = $protocol . '://' . $_SERVER['HTTP_HOST'];
			$email = base64_encode( get_option( 'admin_email' ) );

		// Get unique token
		$response = wp_remote_get( $app_url . '?activate=true&url=' . $current_url . '&data=' . $email . '&time=' . time() );
		$token = wp_remote_retrieve_body( $response );
		if ( empty( $token ) ) {
			$logger->alert_error( 'There was an error creating your install token. Please send us a support ticket. Error:' . print_r( $response, true ) );
		}

		if ( strpos( $token, 'Rate Limited' ) !== false ) {
					$logger->alert_error( 'Rate limited, please wait a few minutes then deactivate and reactivate ROP to recieve your install token (Needed to log into Facebook). ' );
		}

		if ( is_wp_error( $response ) ) {
			$logger->alert_error( 'There was an error creating your token. Please send us a support ticket: ' . $response->get_error_message() );
		}

		if ( empty( get_option( ROP_APP_TOKEN_OPTION ) ) ) {
				$deprecated = ' ';
				$autoload = 'no';
				add_option( ROP_APP_TOKEN_OPTION, $token, $deprecated, $autoload );
		} else {
			// delete old token incase plugin was installed/uninstalled dirty
			 wp_remote_get( $app_url . '?deactivate=true&token=' . get_option( ROP_APP_TOKEN_OPTION ) . '&time=' . time() );
			update_option( ROP_APP_TOKEN_OPTION, $token );
		}

	}

}
