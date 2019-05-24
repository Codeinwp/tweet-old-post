<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      8.0.0
 * @package    Rop
 * @subpackage Rop/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    8.0.0
	 */
	public static function deactivate() {
		/**
		 * Stop posting action.
		 */
		$cron_helper = new Rop_Cron_Helper();
		$cron_helper->remove_cron();

		/**
		 * Clear activation data
		 */
		$logger = new Rop_Logger();

		$app_url = ROP_AUTH_APP_URL . ROP_APP_ACTIVATION_PATH;
		$response = wp_remote_get( $app_url . '?deactivate=true&token=' . get_option( ROP_APP_TOKEN_OPTION ) );

		if ( is_wp_error( $response ) ) {
			$logger->alert_error( 'There was an error deleting your token: ' . $response->get_error_message() );
		}

	}

}
