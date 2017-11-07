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
 * Class Rop_Shortest
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Shortest_Shortner extends Rop_Url_Shortner_Abstract {

	/**
	 * Method to inject functionality into constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function init() {
		$this->service_name = 'shorte.st';
		$this->credentials = array(
			'key' => '',
		);
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
			'https://api.shorte.st/v1/data/url',
			array( 'method' => 'put', 'json' => true ),
			array( 'urlToShorten' => $url ),
			array( 'public-api-token' => $this->credentials['key'] )
		);
		$shortURL = $url;
		if ( intval( $response['error'] ) == 200 && $response['response']['status'] == 'ok' ) {
			$shortURL   = $response['response']['shortenedUrl'];
		}
		return $shortURL;
	}
}
