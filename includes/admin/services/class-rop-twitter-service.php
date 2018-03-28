<?php
/**
 * The file that defines the Twitter Service specifics.
 *
 * A class that is used to interact with Twitter.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Twitter_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Twitter_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'twitter';

	/**
	 * Holds the Twitter APP Consumer Key.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $consumer_key The Twitter APP Consumer Key.
	 */
	private $consumer_key = 'ofaYongByVpa3NDEbXa2g';

	/**
	 * Holds the Twitter APP Consumer Secret.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $consumer_secret The Twitter APP Consumer Secret.
	 */
	private $consumer_secret = 'vTzszlMujMZCY3mVtTE6WovUKQxqv3LVgiVku276M';


	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Twitter';
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
	 */
	public function authorize() {
		header( 'Content-Type: text/html' );
		if ( ! session_id() ) {
			session_start();
		}
		$request_token = $_SESSION['rop_twitter_request_token'];
		$api           = $this->get_api( $request_token['oauth_token'], $request_token['oauth_token_secret'] );

		$access_token = $api->oauth( 'oauth/access_token', [ 'oauth_verifier' => $_GET['oauth_verifier'] ] );

		$_SESSION['rop_twitter_oauth_token'] = $access_token;

		parent::authorize();
		// echo '<script>window.setTimeout("window.close()", 500);</script>';
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $oauth_token The OAuth Token. Default empty.
	 * @param   string $oauth_token_secret The OAuth Token Secret. Default empty.
	 *
	 * @return mixed
	 */
	public function get_api( $oauth_token = '', $oauth_token_secret = '' ) {
		if ( $this->api == null ) {
			$this->set_api( $oauth_token, $oauth_token_secret );
		}

		return $this->api;
	}

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $oauth_token The OAuth Token. Default empty.
	 * @param   string $oauth_token_secret The OAuth Token Secret. Default empty.
	 */
	public function set_api( $oauth_token = '', $oauth_token_secret = '' ) {
		if ( $oauth_token != '' && $oauth_token_secret != '' ) {
			$this->api = new \Abraham\TwitterOAuth\TwitterOAuth( $this->consumer_key, $this->consumer_secret, $oauth_token, $oauth_token_secret );
		} else {
			$this->api = new \Abraham\TwitterOAuth\TwitterOAuth( $this->consumer_key, $this->consumer_secret );
		}
	}

	/**
	 * Check if we need to authenticate the user.
	 *
	 * @return bool
	 */
	public function maybe_authenticate() {
		if ( ! session_id() ) {
			session_start();
		}
		if ( ! $this->is_set_not_empty(
			$_SESSION, array(
				'rop_twitter_oauth_token',
			)
		) ) {
			return false;
		}
		$token = $_SESSION['rop_twitter_oauth_token'];
		unset( $_SESSION['rop_twitter_oauth_token'] );
		unset( $_SESSION['rop_twitter_request_token'] );
		return $this->authenticate( $token );
	}

	/**
	 * Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return bool
	 */
	public function authenticate( $args = array() ) {

		if ( ! $this->is_set_not_empty(
			$args, array(
				'oauth_token',
				'oauth_token_secret',
			)
		) ) {
			return false;
		}
		$this->set_api( $args['oauth_token'], $args['oauth_token_secret'] );
		$api = $this->get_api();

		$this->set_credentials(
			array(
				'oauth_token'        => $args['oauth_token'],
				'oauth_token_secret' => $args['oauth_token_secret'],
			)
		);

		$response = $api->get( 'account/verify_credentials' );

		if ( ! isset( $response->id ) ) {
			return false;
		}
		$this->service = array(
			'id'                 => $response->id,
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'public_credentials' => false,
			'available_accounts' => $this->get_users( $response ),
		);

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

	/**
	 * Utility method to retrieve users from the Twitter account.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   object $data Response data from Twitter.
	 *
	 * @return array
	 */
	private function get_users( $data = null ) {
		$user = $this->user_default;
		if ( $data == null ) {
			$this->set_api( $this->credentials['oauth_token'], $this->credentials['oauth_token_secret'] );
			$api      = $this->get_api();
			$response = $api->get( 'account/verify_credentials' );
			if ( ! isset( $response->id ) ) {
				return $user;
			}
			$data = $response;
		}

		$img = '';
		if ( ! $data->default_profile_image ) {
			$img = $data->profile_image_url_https;
		}
		$user['id']      = $data->id;
		$user['account']    = $data->name;
		$user['user']    = '@' . $data->screen_name;
		$user['img']     = $img;
		$user['service'] = $this->service_name;

		return array( $this->get_service_id() . '_' . $user['id'] => $user );
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
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $data The data from the user.
	 *
	 * @return mixed
	 */
	public function sign_in_url( $data ) {
		$request_token = $this->request_api_token();
		$this->set_api( $request_token['oauth_token'], $request_token['oauth_token_secret'] );
		$api = $this->get_api();

		$url = $api->url(
			'oauth/authorize', [
				'oauth_token' => $request_token['oauth_token'],
				'force_login' => false,
			]
		);

		// $url = $api->url("oauth/authorize", ["oauth_token" => $request_token['oauth_token'] , 'force_login' => true ]);
		return $url;
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
		if ( ! session_id() ) {
			session_start();
		}

		$api           = $this->get_api();
		$request_token = $api->oauth( 'oauth/request_token', array( 'oauth_callback' => $this->get_endpoint_url( 'authorize' ) ) );

		$_SESSION['rop_twitter_request_token'] = $request_token;

		return $request_token;
	}

	/**
	 * Method for publishing with Twitter service.
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
		$this->set_api( $this->credentials['oauth_token'], $this->credentials['oauth_token_secret'] );
		$api = $this->get_api();

		$new_post = array();
		if ( isset( $post_details['post']['post_img'] ) && $post_details['post']['post_img'] !== '' && $post_details['post']['post_img'] !== false ) {
			$media_response = $api->upload( 'media/upload', array( 'media' => $post_details['post']['post_img'] ) );
			if ( $media_response->media_id_string ) {
				$new_post['media_ids'] = $media_response->media_id_string;
			}
		}

		$message = $post_details['post']['post_content'];
		if ( $post_details['post']['custom_content'] !== '' ) {
			$message = $post_details['post']['custom_content'];
		}

		$link = $this->get_url( $post_details );

		$new_post['status'] = $message . $link;

		$response = $api->post( 'statuses/update', $new_post );

		if ( isset( $response->id ) ) {
			return true;
		}

		return false;
	}
}
