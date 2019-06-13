<?php
/**
 * The file that defines the Pinterest Service specifics.
 *
 * A class that is used to interact with Pinterest.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Pinterest_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Buffer_Service extends Rop_Services_Abstract {

	/**
	 * An instance of authenticated Pinterest user.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $user An instance of the current user.
	 */
	public $user;
	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'buffer';
	/**
	 * Defines the service permissions needed.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $permissions The Pinterest required permissions.
	 */

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Buffer';
	}

	/**
	 * Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function expose_endpoints() {
		$this->register_endpoint( 'authorize', 'authorize' );
		$this->register_endpoint( 'authenticate', 'maybe_authenticate' );
	}

	/**
	 * Method for authorizing the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function authorize() {
		header( 'Content-Type: text/html' );
		if ( ! session_id() ) {
			session_start();
		}

		// $this->request_api_token();

		parent::authorize();
	}

	/**
	 * Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @return mixed
	 */
	public function request_api_token() {
		$credentials = $_SESSION['rop_pinterest_credentials'];

		$api = $this->get_api( $credentials['app_id'], $credentials['secret'] );

		if ( isset( $_GET['code'] ) ) {
			$token = $api->auth->getOAuthToken( $_GET['code'] );
			$api->auth->setOAuthToken( $token->access_token );
			$_SESSION['rop_pinterest_token'] = $token->access_token;
		}
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $app_id The Pinterest APP ID. Default empty.
	 * @param   string $secret The Pinterest APP Secret. Default empty.
	 *
	 * @return \Facebook\Facebook
	 */
	public function get_api( $app_id = '', $secret = '' ) {
		if ( $this->api == null ) {
			$this->set_api( $app_id, $secret );
		}

		return $this->api;
	}

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $app_id The Pinterest APP ID. Default empty.
	 * @param   string $secret The Pinterest APP Secret. Default empty.
	 *
	 * @return mixed
	 */
	public function set_api( $app_id = '', $secret = '' ) {
		try {
			if ( empty( $app_id ) || empty( $secret ) ) {
				return false;
			}

			$this->api = new DirkGroenen\Pinterest\Pinterest( $this->strip_whitespace( $app_id ), $this->strip_whitespace( $secret ) );
		} catch ( Exception $exception ) {
			$this->logger->alert_error( 'Can not load Pinterest api. Error: ' . $exception->getMessage() );
		}
	}

	/**
	 * Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function maybe_authenticate() {
		if ( ! session_id() ) {
			session_start();
		}

		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_buffer_credentials',
			)
		) ) {
			return false;
		}

		$token = $_SESSION['rop_buffer_credentials'];
		$credentials['token'] = $token;

        return $this->authenticate( $credentials );

	}

	/**
	 * Method to authenticate an user based on provided credentials.
	 * Used in DB upgrade.
	 *
	 * @param array $args The arguments for facebook service auth.
	 *
	 * @return bool
	 */
	public function authenticate( $args = array() ) {

    $token = $args['token'];

    $url = 'https://api.bufferapp.com/1/user.json?access_token=' . $token;

		$response = wp_remote_get( $url );
		$response = json_decode( wp_remote_retrieve_body( $response ), true );

if( !isset($response['id']) ){
    $this->logger->alert_error( 'Buffer error: ' . $response['error'] );
    return false;
    }

		$this->service = array(
			'id'                 => $response['id'],
			'service'            => $this->service_name,
			'credentials'        => $token,
			'available_accounts' => $this->get_profiles( $token ),
		);

        unset( $_SESSION['rop_buffer_credentials'] );

		return true;

	}

	/**
	 * Method to register credentials for the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $args The credentials array.
	 */
	public function set_credentials( $args ) {

		$this->credentials = $args;
	}

	public function get_profiles( $token = '' ) {

		$url = 'https://api.bufferapp.com/1/profiles.json?access_token=' . $token;

		$response = wp_remote_get( $url );
		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $response['error'] ) ) {
			$this->logger->alert_error( 'Buffer error: ' . $response['error'] );
			return false;
		}

			$buffer_profiles = array();

		foreach ( $response as $response_field ) {
			$buffer_profile          = array();
			$buffer_profile['id']      = $response_field['id'];
			$buffer_profile['account'] = $response_field['formatted_username'];
			$buffer_profile['user']    = $response_field['formatted_service'] . ' - ' . $response_field['formatted_username'];
			;
			$buffer_profile['active']  = false;
			$buffer_profile['service'] = $this->service_name;

			$buffer_profile['img']     = $response_field['avatar_https'];
			$buffer_profile['created'] = date( 'Y-m-d H:i:s', substr( $response_field['created_at'], 0, 10 ) );
			$buffer_profiles[]            = $buffer_profile;
		}

					return $buffer_profiles;
	}

	/**
	 * Returns information for the current service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_service() {
		return $this->service;
	}

	/**
	 * Generate the sign in URL.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $data The data from the user.
	 *
	 * @return mixed
	 */
	public function sign_in_url( $data ) {

		if ( ! session_id() ) {
			session_start();
		}

		$_SESSION['rop_buffer_credentials'] = $data['credentials']['access_token'];

		 $url = get_site_url() . '/wp-admin/admin.php?page=TweetOldPost&state=buffer&network=buffer';

		return $url;
	}

	public function get_buffer_access_token() {
		$code = urldecode( $_GET['code'] );
		if ( empty( $code ) ) {
			return;
		}

		if ( ! class_exists( '\GuzzleHttp\Client' ) ) {
						return;
		}

		$guzzle = new \GuzzleHttp\Client();
		$response = $guzzle->request(
			'POST',
			'https://api.bufferapp.com/1/oauth2/token.json',
			[
				'form_params' => [
					'client_id' => '',
					'client_secret' => '',
					'redirect_uri' => 'https://ecom.uriahsvictor.com/wp-admin/admin.php?page=TweetOldPost',
					'code' => $code,
					'grant_type' => 'authorization_code',
				],
			]
		);

		$json = (string) $response->getBody();

		$json_arr = json_decode( $json, true );
		$access_token = $json_arr['access_token'];

		return $access_token;
	}

	/**
	 * Method for publishing with Facebook service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {
		if ( Rop_Admin::rop_site_is_staging() ) {
			return false;
		}

			$post_id = $post_details['post_id'];

		$url = 'https://api.bufferapp.com/1/updates/create.json';

		$data = array(

			'pretty' => 'true',
			'access_token' => $args['credentials'],
			'profile_ids' => array(
				$args['id'],
			),
			'text' => html_entity_decode( get_the_title( $post_id ) ),
		);

		$response = wp_remote_post(
			$url,
			array(
				'body'    => $data,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);

		// $this->logger->alert_error( 'Buffer Credentials: ' . print_r( $args['credentials'], true ) );

		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response, true );

		if ( $response['success'] === false ) {
			$this->logger->alert_error( 'Buffer error: ' . $response['message'] );
			return false;
		}

		$this->logger->alert_error( print_r( $response, true ) );

		$this->logger->alert_success(
			sprintf(
				'Successfully shared %s to %s on %s ',
				html_entity_decode( get_the_title( $post_id ) ),
				$args['user'],
				$post_details['service']
			)
		);

		return true;
	}

}
