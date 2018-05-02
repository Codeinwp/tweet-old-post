<?php
/**
 * The file that defines this shortner specific functionality.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/shortners
 */

/**
 * Class Rop_Isgd_Shortner
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Isgd_Shortner extends Rop_Url_Shortner_Abstract {

	/**
	 * Method to inject functionality into constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function init() {
		$this->service_name = 'is.gd';
		$this->credentials  = array();
	}

	/**
	 * Method to retrieve the shorten url from the API call.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $url The url to shorten.
	 * @return string
	 */
	public function shorten_url( $url ) {

		$response = $this->callAPI(
			'https://is.gd/api.php',
			array( 'method' => 'get' ),
			array( 'longurl' => $url),
			null
		);
		$shortURL = $url;
		if ( intval( $response['error'] ) == 200 ) {
			$shortURL = $response['response'];
		}
		return $shortURL;
	}
}
