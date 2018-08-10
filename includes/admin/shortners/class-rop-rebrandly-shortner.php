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
 * Class Rop_Rebrandly_Shortner
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Rebrandly_Shortner extends Rop_Url_Shortner_Abstract {

	/**
	 * Method to inject functionality into constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function init() {
		$this->service_name = 'rebrand.ly';
		$this->credentials  = array(
			'key'  => '',
			'domain'  => '',
		);
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
		$post_id    = url_to_postid( $url );
		$title      = '';
		if ( $post_id ) {
			$title  = get_the_title( $post_id );
		}

		$response = $this->callAPI(
			'https://api.rebrandly.com/v1/links',
			array( 'method' => 'json', 'json' => true ),
			array( 'destination' => $url, 'title' => $title, 'domain' => array( 'id' => $this->credentials['domain'] ) ),
			array( 'apikey' => $this->credentials['key'], 'Content-Type' => 'application/json' )
		);

		$shortURL = $url;
		if ( intval( $response['error'] ) === 200 ) {
			if ( ! array_key_exists( 'httpCode', $response['response'] ) ) {
				$shortURL = $response['response']['shortUrl'];
			} else {
				$this->error->throw_exception( 'Error', "Error from {$this->service_name} " . $response['response']['message'] );
			}
		} else {
			$this->error->throw_exception( 'Error', "Error from {$this->service_name} " . $response['error'] );
		}
		return $shortURL;
	}
}
