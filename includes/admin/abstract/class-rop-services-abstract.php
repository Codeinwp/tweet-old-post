<?php
/**
 * The file that defines the abstract class inherited by all services
 *
 * A class that is used to define the services class and utility methods.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/abstract
 */

/**
 * Class Rop_Services_Abstract
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
abstract class Rop_Services_Abstract {

	/**
	 * Stores the service display name.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     string $display_name The service pretty name.
	 */
	public $display_name;

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
	 * Stores a Rop_Service_Model instance.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     Rop_Service_Model $model The service model.
	 */
	protected $model;

	/**
	 * A flag to specify if is auth or not.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     bool $is_auth The flag for auth.
	 */
	protected $is_auth = false;

	/**
	 * Rop_Services_Abstract constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		$this->model = new Rop_Service_Model( $this->service_name );
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
	 * Method to register credentials for the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $args The credentials array.
	 */
	public abstract function credentials( $args );

	/**
	 * Method to return a token to be used for further requests.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function get_token();

	/**
	 * Method to retrieve a user model for the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $args Optional. Arguments needed by the implementation.
	 * @return Rop_User_Model
	 */
	public abstract function get_user( $args );

	/**
	 * Method for auth-ing the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function auth();

	/**
	 * Method for publishing with the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $post_details The post details to be published by the service.
	 * @return mixed
	 */
	public abstract function share( $post_details );

	/**
	 * Utility method to retrieve state of auth.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return bool
	 */
	public function is_auth() {
		return $this->is_auth;
	}

	/**
	 * Utility method to register a REST endpoint via WP.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $path The path for the endpoint.
	 * @param   string $callback The method name from the service class.
	 * @param   string $method The request type ( GET, POST, PUT, DELETE etc. ).
	 */
	protected function register_endpoint( $path, $callback, $method = 'GET' ) {
		$loader = new Rop_Loader();
		$loader->add_action( 'rest_api_init', $this, function( $path, $callback, $method ) {
			register_rest_route( 'tweet-old-post/v8', '/' . $this->service_name . '/' . $path, array(
				'methods' => $method,
				'callback' => array( $this, $callback ),
			) );
		} );
	}

}
