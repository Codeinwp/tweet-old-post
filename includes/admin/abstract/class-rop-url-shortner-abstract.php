<?php
abstract class Rop_Url_Shortner_Abstract {


	/**
	 * Stores the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name in slug format.
	 */
	protected $service_name;

	/**
	 * The array with the credentials for auth-ing the service.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     array $credentials The credentials array used for auth.
	 */
	protected $credentials;

	/**
	 * Holds the Rop_Exception_Handler
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     Rop_Exception_Handler $error The exception handler.
	 */
	protected $error;

	/**
	 * Rop_Services_Abstract constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		$this->error = new Rop_Exception_Handler();
		$this->init();
	}

	/**
	 * Method to inject functionality into constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function init();

	/**
	 * Method to return the needed credentials for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public abstract function get_required_credentials();

	/**
	 * Returns the stored credentials from DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function get_credentials();

	/**
	 * Updates the credentials in DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function set_credentials();

	/**
	 * Method to retrieve the shorten url from the API call.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $url The url to shorten.
	 * @return string
	 */
	public abstract function shorten_url( $url );
}
