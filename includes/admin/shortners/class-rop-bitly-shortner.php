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
		if ( ! empty( $diff = array_diff( $prev, $now ) ) ) {
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
		$credentials    = array();
		if ( array_key_exists( 'generic_access_token', $saved ) ) {
			$credentials    = array(
				'access_token'   => $saved['generic_access_token'],
			);
		} else {
			$credentials    = array(
				'login'   => $saved['user'],
				'apiKey'  => $saved['key'],
			);
		}

		$response = $this->callAPI(
			'https://api-ssl.bit.ly/v3/shorten',
			array( 'method' => 'get' ),
			array_merge(
				array(
					'longUrl' => $url,
					'format'  => 'txt',
				),
				$credentials
			),
			null
		);
		$short_url = $url;
		if ( intval( $response['error'] ) == 200 ) {
			$short_url = $response['response'];
		}

		return trim( $short_url );
	}
}
