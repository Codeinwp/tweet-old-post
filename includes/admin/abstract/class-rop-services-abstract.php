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
	 * Default account template array.
	 *
	 * @access  protected
	 * @since   8.0.0
	 * @var array Default account values.
	 */
	public $user_default = array(
		'account'    => '',
		'user'       => '',
		'created'    => 0,
		'id'         => 0,
		'active'     => true,
		'is_company' => false,
		'img'        => '',
		'service'    => '',
	);
	/**
	 * Stores the service details.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     array $service The service details.
	 */
	protected $service;
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
	 * @var     mixed $api The API object.
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
	 * Holds the logger
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     Rop_Logger $logger The logger handler.
	 */
	protected $logger;

	/**
	 * Rop_Services_Abstract constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		$this->error                   = new Rop_Exception_Handler();
		$this->logger                  = new Rop_Logger();
		$this->user_default['created'] = date( 'd/m/Y H:i' );
		$this->user_default['service'] = $this->service_name;
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

			$authenticated = $this->maybe_authenticate();

			if ( $authenticated ) {
				$service                    = $this->get_service();
				/**
				 * For LinkedIn, it seems they include '_' char into the service id and
				 * we need to replace with something else in order to not mess with the way we store the indices.
				 */
				$service_id                 = $service['service'] . '_' . $this->strip_underscore( $service['id'] );
				$new_service[ $service_id ] = $service;
			}

			$model = new Rop_Services_Model();
			$model->add_authenticated_service( $new_service );

		} catch ( Exception $exception ) {
			$this->error->throw_exception( 'Error', sprintf( 'The service "' . $this->display_name . '" can not be authorized %s', $exception->getMessage() ) );
		}

		exit( wp_redirect( admin_url( 'admin.php?page=TweetOldPost' ) ) );
	}

	/**
	 * Method for checking authentication the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function maybe_authenticate();

	/**
	 * Returns information for the current service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function get_service();

	/**
	 * Method for authenticate the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public abstract function authenticate( $args );

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
	 * Method to retrieve an service id.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @return array
	 */
	public function get_service_active_accounts() {
		$service_details = $this->service;

		if ( ! isset( $service_details['available_accounts'] ) ) {
			return array();
		}
		if ( empty( $service_details['available_accounts'] ) ) {
			return array();
		}

		$active_accounts = array_filter(
			$service_details['available_accounts'],
			function ( $value ) {
				if ( ! isset( $value['active'] ) ) {
					return false;
				}

				return $value['active'];
			}
		);
		$accounts_ids    = array();
		foreach ( $active_accounts as $account ) {
			$accounts_ids[ $this->get_service_id() . '_' . $account['id'] ] = $account;
		}

		return $accounts_ids;
	}

	/**
	 * Method to retrieve an service id.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @return string
	 */
	public function get_service_id() {
		$service_details = $this->service;
		if ( ! isset( $service_details['id'] ) ) {
			return '';
		}

		return $this->service_name . '_' . $service_details['id'];
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

		$link = ( ! empty( $post_details['post_url'] ) ) ? ' ' . $post_details['post_url'] : '';

		if ( empty( $link ) ) {
			return '';
		}

		if ( ! $post_details['short_url'] ) {
			return $link;
		}
		if ( empty( $post_details['short_url_service'] ) ) {
			return $link;
		}
		if ( $post_details['short_url_service'] === 'wp_short_url' ) {
			return $link;
		}
		$post_format_helper = new Rop_Post_Format_Helper();
		$link               = ' ' . $post_format_helper->get_short_url( $post_details['post_url'], $post_details['short_url_service'], $post_details['shortner_credentials'] );

		return $link;
	}

	/**
	 * Utility method to check array if has certain keys set and not empty.
	 *
	 * @param array $array Array to check.
	 * @param array $list List of keys to check.
	 *
	 * @return bool Valid or not.
	 */
	protected function is_set_not_empty( $array = array(), $list = array() ) {
		if ( empty( $array ) ) {
			return false;
		}
		if ( empty( $list ) ) {
			return false;
		}
		foreach ( $list as $key ) {
			if ( ! isset( $array[ $key ] ) ) {
				$this->error->throw_exception( 'Value not set ', sprintf( 'Value not set : %s in %s ', $key, print_r( $array, true ) ) );

				return false;
			}
			if ( empty( $array[ $key ] ) ) {
				$this->error->throw_exception( 'Value is empty ', sprintf( 'Value is empty : %s in %s ', $key, print_r( $array, true ) ) );

				return false;
			}
		}

		return true;
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

		if ( $callback == false ) {
			return;
		}

		add_action(
			'rest_api_init',
			function () use ( $path, $callback, $method ) {
				register_rest_route(
					'tweet-old-post/v8',
					'/' . $this->service_name . '/' . $path,
					array(
						'methods'  => $method,
						'callback' => array( $this, $callback ),

					)
				);
			}
		);
	}

	/**
	 * Facebook legacy redirect url.
	 *
	 * @return string Old legacy url.
	 */
	protected function get_legacy_url( $network = '' ) {
		$url = get_admin_url( get_current_blog_id(), 'admin.php?page=TweetOldPost' );
		if ( ! empty( $network ) ) {
			$url = add_query_arg( array( 'network' => $network ), $url );
		}

		return str_replace( ':80', '', $url );
	}

	/**
	 * Strip non-ascii chars.
	 *
	 * @param string $string String to check.
	 *
	 * @return string Normalized string.
	 */
	protected function normalize_string( $string ) {
		return preg_replace( '/[[:^print:]]/', '', $string );
	}
	/**
	 * Strip underscore and replace with safe char.
	 *
	 * @param string $name Original name.
	 *
	 * @return mixed Normalized name.
	 */
	protected function strip_underscore( $name ) {
		return str_replace( '_', '---', $name );
	}
	/**
	 * Adds back the underscore.
	 *
	 * @param string $name Safe name.
	 *
	 * @return mixed Unsafe name.
	 */
	protected function unstrip_underscore( $name ) {
		return str_replace( '---', '_', $name );
	}

	/**
	 * Strips white space from credentials
	 *
	 * @param string $data the credential.
	 *
	 * @return string Cleaned credential.
	 */
	protected function strip_whitespace( $data ) {
		$data = rtrim( ltrim( $data ) );
		return $data;
	}

	/**
	 * Strips excess blank lines left by media blocks in Gutenberg editor
	 *
	 * @param string $content the content to clean.
	 *
	 * @return string The cleaned content.
	 */
	protected function strip_excess_blank_lines( $content ) {
		$content = preg_replace( "/([\r\n]{4,}|[\n]{3,}|[\r]{3,})/", "\n\n", $content );
		return $content;
	}


}
