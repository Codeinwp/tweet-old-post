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

		if ( ! class_exists( 'GuzzleHttp\Client' ) ) {
			$logger->alert_error( 'Error: Cannot find Guzzle' );
			return;
		}

		$client = new GuzzleHttp\Client();

		try {
			$response = $client->request( 'GET', ROP_AUTH_APP_URL . ROP_APP_ACTIVATION_PATH . '?deactivate=true&token=' . get_option( ROP_APP_TOKEN_OPTION ) );
		} catch ( GuzzleHttp\Exception\GuzzleException $e ) {
			$logger->alert_error( 'Error ' . $e->getCode() . '. ' . $e->getMessage() . "\n" . $e->getTrace() );
		}
	}

}
