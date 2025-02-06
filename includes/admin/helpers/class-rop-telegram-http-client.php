<?php
/**
 * The file that defines the class to manage telegram http client
 *
 * A class that is used manipulate text content.
 *
 * @link       https://themeisle.com/
 * @since      9.1.3
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Class Rop_Content_Helper
 *
 * @since   9.1.3
 * @link    https://themeisle.com/
 */
class Rop_Telegram_Http_Client extends \Telegram\Bot\HttpClients\GuzzleHttpClient {

	/**
	 * Holds promises
	 *
	 * @var PromiseInterface[].
	 */
	private static $promises = array();

	/**
	 * Unwrap Promises.
	 *
	 * @throws Throwable Utils unwrap.
	 */
	public function __destruct() {
		\GuzzleHttp\Promise\Utils::unwrap( self::$promises );
	}
}
