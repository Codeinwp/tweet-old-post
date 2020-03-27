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
 * Class Rop_Bitly_Shortner
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Bitly_Shortner extends Rop_Url_Shortner_Abstract {

	/**
	 * Method to inject functionality into constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function init() {
		$this->service_name = 'bit.ly';
		$this->credentials  = array(
			'generic_access_token'  => '',
		);
	}

	/**
	 * Handles upgrade from old authentication to new oauth2 keys authentication.
	 *
	 * @since   ?
	 * @access  public
	 * @return mixed
	 */
	public function filter_credentials( $credentials ) {
		// if the keys are the same, no sweat.
		// if they are anything but identical, we should assume these need to be refreshed as this could be an upgrade to oauth2 keys.
		$prev   = array_keys( $credentials );
		$now    = array_keys( $this->credentials );
		$diff = array_diff( $prev, $now );

		if ( ! empty( $diff ) ) {
			return $this->credentials;
		}
		return $credentials;
	}

	/**
	 * Method to retrieve the shorten url from the API call.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $url The url to shorten.
	 *
	 * @return string
	 */
	public function shorten_url( $url ) {
		$saved          = $this->get_credentials();

		if ( ! array_key_exists( 'generic_access_token', $saved ) ) {
			$logger          = new Rop_Logger();
			$logger->alert_error('Generic Access Token not found. Please see the following link for instructions: https://is.gd/rop_bitly');

			return $url;
		}

		$response = $this->callAPI(
			'https://api-ssl.bit.ly/v4/shorten',
			array( 'method' 	=> 'json' ),
			array( 'long_url' => $url ),
			array(
				'Authorization' => 'Bearer ' . $saved['generic_access_token'],
				'Content-Type' 	=> 'application/json',
			),
		);

		$shortURL = $url;

		if ( intval( $response['error'] ) === 200 || intval( $response['error'] ) === 201 ) {
			$response = json_decode( $response['response'] );
			$shortURL = $response->link;
		}

		return trim( $shortURL );
	}
}
