<?php
/**
 * The file that defines the abstract class inherited by all shortners
 *
 * A class that is used to define the shortners class and utility methods.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/abstract
 */

/**
 * Class Rop_Url_Shortner_Abstract
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
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
	 * Stores an instance of Rop_Shortners_Model
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     Rop_Shortners_Model $model An instance of the model.
	 */
	protected $model;

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
		$this->model = new Rop_Shortners_Model( $this->service_name, $this->credentials );
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
	 * Returns the stored credentials from DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_credentials() {
		return $this->model->credentials();
	}

	/**
	 * Updates the credentials in DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $credentials An array of credentials to save.
	 *
	 * @return mixed
	 */
	public function set_credentials( $credentials ) {
		$this->model->save( $credentials );
		$this->credentials = $this->get_credentials();

		return $this->credentials;
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
	public abstract function shorten_url( $url );

	/**
	 * Utility method to generate a salt string.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @return string
	 */
	protected function getSalt() {
		$charset       = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/\\][{}\'";:?.>,<!@#$%^&*()-_=+|';
		$randStringLen = 64;

		$randString = '';
		for ( $i = 0; $i < $randStringLen; $i ++ ) {
			$randString .= $charset[ mt_rand( 0, strlen( $charset ) - 1 ) ];
		}

		return $randString;
	}

	/**
	 * Method to call a shortner API.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  protected
	 *
	 * @param   string $url     The URL to shorten.
	 * @param   array  $props   cURL props.
	 * @param   array  $params  Params to be passed to API.
	 * @param   array  $headers Additional headers if needed.
	 *
	 * @return array
	 */
	protected final function callAPI( $url, $props = array(), $params = array(), $headers = array() ) {
		$body  = null;
		$error = null;

		$conn = curl_init( $this->build_url( $url, $props, $params ) );
		curl_setopt( $conn, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $conn, CURLOPT_FRESH_CONNECT, true );
		curl_setopt( $conn, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $conn, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $conn, CURLOPT_HEADER, 0 );
		curl_setopt( $conn, CURLOPT_NOSIGNAL, 1 );

		if ( $headers ) {
			curl_setopt( $conn, CURLOPT_HTTPHEADER, $this->build_headers( $headers ) );
		}
		if ( $props && isset( $props['method'] ) ) {
			if ( in_array( $props['method'], array( 'post', 'put' ) ) ) {
				curl_setopt( $conn, CURLOPT_POSTFIELDS, $params );
			}
			if ( $props['method'] === 'json' ) {
				curl_setopt( $conn, CURLOPT_POSTFIELDS, json_encode( $params ) );
			}
			if ( ! in_array( $props['method'], array( 'get', 'post', 'json' ) ) ) {
				curl_setopt( $conn, CURLOPT_CUSTOMREQUEST, strtoupper( $props['method'] ) );
			}
		}
		try {
			$body  = curl_exec( $conn );
			$error = curl_getinfo( $conn, CURLINFO_HTTP_CODE );
		} catch ( Exception $e ) {
			$this->error->throw_exception( 'Error', 'Exception ' . $e->getMessage() );
		}

		curl_close( $conn );
		if ( $props && isset( $props['json'] ) && $props['json'] ) {
			$body = json_decode( $body, true );
		}
		$array = array(
			'response' => $body,
			'error'    => $error,
		);

		return $array;
	}

	/**
	 * Utility method to build the request url for cURL.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $url    The URL to shorten.
	 * @param   array  $props  cURL props.
	 * @param   array  $params Params to be appended to URL.
	 *
	 * @return string
	 */
	private function build_url( $url, $props, $params ) {
		if ( $props && isset( $props['method'] ) && $props['method'] === 'get' ) {
			$params = array_map(
				function ( $value ) {
						return urlencode( $value );
				},
				$params
			);
			$url    = add_query_arg( $params, $url );
		}

		return $url;
	}

	/**
	 * Utility method to build the headers.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param array $headers The headers to be added to cURL.
	 *
	 * @return array
	 */
	private function build_headers( $headers ) {
		$header = array();
		foreach ( $headers as $key => $val ) {
			$header[] = "$key: $val";
		}

		return $header;
	}
}
