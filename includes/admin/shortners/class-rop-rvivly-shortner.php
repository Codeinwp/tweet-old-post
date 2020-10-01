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
		return apply_filters( 'rop_shorten_url', $url, 'rviv.ly', $this->website, $this->credentials );
	}
}
