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
	 * Stores a reference to the API to be used.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     object $api The API object.
	 */
	protected $api = null;

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
	 * Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function expose_endpoints();

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function get_api();

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function set_api();

	/**
	 * Method for authorizing the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function authorize() {

		try {

			$authenticated = $this->authenticate();

			if ( $authenticated ) {
				$service                    = $this->get_service();
				$service_id                 = $service['service'] . '_' . $service['id'];
				$new_service[ $service_id ] = $service;
			}

			$model = new Rop_Services_Model();
			$model->add_authenticated_service( $new_service );

		} catch ( Exception $exception ) {
			// Service can't be built. Not found or otherwise. Maybe log this.
			$log = new Rop_Logger();
			$log->warn( 'The service "' . $this->display_name . '" can NOT be built or was not found', $exception->getMessage() );
		}

		exit( wp_redirect( admin_url( 'admin.php?page=TweetOldPost' ) ) );
	}

	/**
	 * Method for authenticate the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function authenticate();

	/**
	 * Returns information for the current service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function get_service();

	/**
	 * Method to register credentials for the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $args The credentials array.
	 */
	public abstract function set_credentials( $args );

	/**
	 * Method for publishing with the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 */
	public abstract function share( $post_details, $args = array() );

	/**
	 * Method to retrieve an endpoint URL.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $path The endpoint path.
	 *
	 * @return mixed
	 */
	public function get_endpoint_url( $path = '' ) {
		return rest_url( '/tweet-old-post/v8/' . $this->service_name . '/' . $path );
	}

	/**
	 * Method to request a token from api.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @return mixed
	 */
	protected abstract function request_api_token();

	/**
	 * Method to generate url for service post share.
	 *
	 * @since   8.0.0rc
	 * @access  protected
	 *
	 * @param   array $post_details The post details to be published by the service.
	 *
	 * @return string
	 */
	protected function get_url( $post_details ) {

		$link = ( isset( $post_details['post']['post_url'] ) ) ? $post_details['post']['post_url'] : '';
		if ( $post_details['post']['short_url_service'] === 'wp_short_url' ) {
			$link = wp_get_shortlink( $post_details['post']['post_id'] );
		} else {
			if ( isset( $post_details['post']['post_url'] ) && $post_details['post']['post_url'] != '' ) {
				$post_format_helper = new Rop_Post_Format_Helper();
				$link               = ' ' . $post_format_helper->get_short_url( 'www.themeisle.com', $post_details['post']['short_url_service'], $post_details['post']['shortner_credentials'] );
				// $link = ' ' . $post_format_helper->get_short_url( $post_details['post']['post_url'], $post_details['post']['short_url_service'], $post_details['post']['shortner_credentials'] );
			}
		}

		return $link;
	}

	/**
	 * Utility method to register a REST endpoint via WP.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $path The path for the endpoint.
	 * @param   string $callback The method name from the service class.
	 * @param   string $method The request type ( GET, POST, PUT, DELETE etc. ).
	 */
	protected function register_endpoint( $path, $callback, $method = 'GET' ) {
		add_action(
			'rest_api_init',
			function () use ( $path, $callback, $method ) {
				register_rest_route(
					'tweet-old-post/v8', '/' . $this->service_name . '/' . $path, array(
						'methods'             => $method,
						'callback'            => array( $this, $callback ),
						'permission_callback' => function () {
							return current_user_can( 'manage_options' );
						},
					)
				);
			}
		);
	}

}
