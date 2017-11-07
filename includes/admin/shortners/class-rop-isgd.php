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
 * Class Rop_Isgd
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Isgd extends Rop_Url_Shortner_Abstract {

	/**
	 * Method to inject functionality into constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function init() {
		$this->service_name = 'is.gd';
		$this->credentials = false;
	}

	/**
	 * Method to return the needed credentials for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_required_credentials() {
		return $this->credentials;
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
			array( 'longurl' => $url) ,
			null
		);
		$shortURL = $url;
		if ( intval( $response['error'] ) == 200 ) {
			$shortURL = $response['response'];
		}
		return $shortURL;
	}
}
