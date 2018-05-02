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
 * Class Rop_Rvivly_Shortner
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Rvivly_Shortner extends Rop_Url_Shortner_Abstract {

	/**
	 * Holds the website root.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $website The website root.
	 */
	private $website;

	/**
	 * Method to inject functionality into constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function init() {
		$this->service_name = 'rviv.ly';
		$this->credentials  = array();
		$this->set_website();
	}

	/**
	 * Utility method to change default website.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   bool|string $website Optional. Another value for website if required.
	 */
	public function set_website( $website = false ) {
		$this->website = get_bloginfo( 'url' );
		if ( $website ) {
			$this->website = $website;
		}
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
			'http://rviv.ly/yourls-api.php',
			array( 'method' => 'post' ),
			array( 'action' => 'shorturl', 'format' => 'simple', 'signature' => substr( md5( $this->website . md5( 'themeisle' ) ), 0, 10 ), 'url' => $url, 'website' => base64_encode( $this->website ) ),
			null
		);

		$shortURL = $url;
		if ( intval( $response['error'] ) == 200 ) {
			$shortURL = $response['response'];
		}
		return $shortURL;
	}
}
