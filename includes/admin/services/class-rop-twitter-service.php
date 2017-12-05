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
	 * Holds the temp data for the authenticated service.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $service The temporary data of the authenticated service.
	 */
	private $service = array();

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
	 * @return mixed
	 */
	public function expose_endpoints() {
		$this->register_endpoint( 'authorize', 'authorize' );
		$this->register_endpoint( 'authenticate', 'authenticate' );
	}

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $oauth_token The OAuth Token. Default empty.
	 * @param   string $oauth_token_secret The OAuth Token Secret. Default empty.
	 * @return mixed
	 */
	public function set_api( $oauth_token = '', $oauth_token_secret = '' ) {
		if ( $oauth_token != '' && $oauth_token_secret != '' ) {
			$this->api = new \Abraham\TwitterOAuth\TwitterOAuth( $this->consumer_key, $this->consumer_secret, $oauth_token, $oauth_token_secret );
		} else {
			$this->api = new \Abraham\TwitterOAuth\TwitterOAuth( $this->consumer_key, $this->consumer_secret );
		}
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $oauth_token The OAuth Token. Default empty.
	 * @param   string $oauth_token_secret The OAuth Token Secret. Default empty.
	 * @return mixed
	 */
	public function get_api( $oauth_token = '', $oauth_token_secret = '' ) {
		if ( $this->api == null ) {
			$this->set_api( $oauth_token, $oauth_token_secret );
		}
		return $this->api;
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
		$request_token = $_SESSION['rop_twitter_request_token'];
		$api = $this->get_api( $request_token['oauth_token'], $request_token['oauth_token_secret'] );

		$access_token = $api->oauth( 'oauth/access_token', ['oauth_verifier' => $_GET['oauth_verifier'] ] );

		$_SESSION['rop_twitter_oauth_token'] = $access_token;

		echo '<script>window.setTimeout("window.close()", 500);</script>';
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
	public function authenticate() {
		if ( ! session_id() ) {
			session_start();
		}

		if ( isset( $_SESSION['rop_twitter_oauth_token'] ) ) {
			$access_token = $_SESSION['rop_twitter_oauth_token'];
			$this->set_api( $access_token['oauth_token'], $access_token['oauth_token_secret'] );
			$api = $this->get_api();

			$this->set_credentials( array(
				'oauth_token' => $access_token['oauth_token'],
				'oauth_token_secret' => $access_token['oauth_token_secret'],
			) );

			$response = $api->get( 'account/verify_credentials' );

			unset( $_SESSION['rop_twitter_oauth_token'] );

			if ( isset( $response->id ) ) {
				$this->service = array(
					'id' => $response->id,
					'service' => $this->service_name,
					'credentials' => $this->credentials,
					'public_credentials' => false,
					'available_accounts' => $this->get_users( $response ),
				);
				return true;
			}

			return false;
		}

		return false;
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

		$api = $this->get_api();
		$request_token = $api->oauth( 'oauth/request_token', array('oauth_callback' => $this->get_endpoint_url( 'authorize' ) ) );

		$_SESSION['rop_twitter_request_token'] = $request_token;

		return $request_token;
	}

	/**
	 * Method to register credentials for the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $args The credentials array.
	 */
	public function set_credentials( $args ) {
		$this->credentials = $args;
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
	 * @param   array $data The data from the user.
	 * @return mixed
	 */
	public function sign_in_url( $data ) {
		$request_token = $this->request_api_token();
		$this->set_api( $request_token['oauth_token'], $request_token['oauth_token_secret'] );
		$api = $this->get_api();

		$url = $api->url( 'oauth/authorize', ['oauth_token' => $request_token['oauth_token'], 'force_login' => false ] );
		// $url = $api->url("oauth/authorize", ["oauth_token" => $request_token['oauth_token'] , 'force_login' => true ]);
		return $url;
	}

	/**
	 * Utility method to retrieve users from the Twitter account.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   object $data Response data from Twitter.
	 * @return array
	 */
	private function get_users( $data = null ) {
		$users = array();
		if ( $data == null ) {
			$this->set_api( $this->credentials['oauth_token'], $this->credentials['oauth_token_secret'] );
			$api = $this->get_api();
			$response = $api->get( 'account/verify_credentials' );
			if ( ! isset( $response->id ) ) {
				return $users;
			}
			$data = $response;
		}

		$img = '';
		if ( ! $data->default_profile_image ) {
			$img = $data->profile_image_url_https;
		}

		$users = array(
			'id' => $data->id,
			'name' => $data->name,
			'account' => '@' . $data->screen_name,
			'img' => $img,
			'active' => true,
		);
		return array( $users );
	}

	/**
	 * Method for publishing with Twitter service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Optional arguments needed by the method.
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {
		$this->set_api( $this->credentials['oauth_token'], $this->credentials['oauth_token_secret'] );
		$api = $this->get_api();

		$new_post = array();

	    $img = false;

	    if ( isset( $post_details['post']['post_img'] ) && $post_details['post']['post_img'] !== '' && $post_details['post']['post_img'] !== false ) {
			$img = $post_details['post']['post_img'];
		}

		if ( $img ) {
			$media_response = $api->upload( 'media/upload', array( 'media' => $post_details['post']['post_img'] ) );
			if ( $media_response->media_id_string ) {
				$new_post['media_ids'] = $media_response->media_id_string;
			}
		}

		$message = $post_details['post']['post_content'];
	    if ( $post_details['post']['custom_content'] !== '' ) {
	        $message = $post_details['post']['custom_content'];
		}

		$link = '';
		if ( isset( $post_details['post']['post_url'] ) && $post_details['post']['post_url'] != '' ) {
			$post_format_helper = new Rop_Post_Format_Helper();
			$link = ' ' . $post_format_helper->get_short_url( 'www.themeisle.com', $post_details['post']['short_url_service'], $post_details['post']['shortner_credentials'] );
		}

		$new_post['status'] = $message . $link;

		$response = $api->post( 'statuses/update', $new_post );

		if ( $response->id ) {
			return true;
		}

		return false;
	}
}
