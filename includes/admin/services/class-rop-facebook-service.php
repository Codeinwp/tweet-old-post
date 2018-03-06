<?php
/**
 * The file that defines the Facebook Service specifics.
 *
 * A class that is used to interact with Facebook.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Facebook_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Facebook_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'facebook';

	/**
	 * Defines the service permissions needed.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $permissions The Facebook required permissions.
	 */
	private $permissions = array( 'email', 'manage_pages', 'publish_pages' );

	/**
	 * Holds the temp data for the authenticated service.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $service The temporary data of the authenticated service.
	 */
	private $service = array();

	/**
	 * An instance of authenticated Facebook user.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $user An instance of the current user.
	 */
	public $user;

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Facebook';
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
	 * @param   string $app_id The Facebook APP ID. Default empty.
	 * @param   string $secret The Facebook APP Secret. Default empty.
	 * @return mixed
	 */
	public function set_api( $app_id = '', $secret = '' ) {
		$this->api = new \Facebook\Facebook( array( 'app_id' => $app_id, 'app_secret' => $secret, 'default_graph_version' => 'v2.10' ) );
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $app_id The Facebook APP ID. Default empty.
	 * @param   string $secret The Facebook APP Secret. Default empty.
	 * @return mixed
	 */
	public function get_api( $app_id = '', $secret = '' ) {
		if ( $this->api == null ) {
			$this->set_api( $app_id, $secret );
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

		$credentials = $_SESSION['rop_facebook_credentials'];

		$api = $this->get_api( $credentials['app_id'], $credentials['secret'] );

		$helper = $api->getRedirectLoginHelper();

		$longAccessToken = '';
		try {
			$accessToken = $helper->getAccessToken();
			if ( ! isset( $accessToken ) ) {
				if ( $helper->getError() ) {
					$this->error->throw_exception( '401 Unauthorized', $this->error->get_fb_exeption_message( $helper ) );
				} else {
					$this->error->throw_exception( '400 Bad Request', 'Bad request' );
				}
			}
			$expires = time() + ( 120 * 24 * 60 * 60 ); // 120 days; 24 hours; 60 minutes; 60 seconds.
			$longAccessToken = new \Facebook\Authentication\AccessToken( $accessToken, $expires );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
		}

		$token = $longAccessToken->getValue();

		$_SESSION['rop_facebook_token'] = $token->getValue();

		parent::authorize();
		// echo '<script>window.setTimeout("window.close()", 500);</script>';
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

		if ( isset( $_SESSION['rop_facebook_token'] ) && isset( $_SESSION['rop_facebook_credentials'] ) ) {
			$credentials = $_SESSION['rop_facebook_credentials'];
			$token = $_SESSION['rop_facebook_token'];
			$api = $this->get_api( $credentials['app_id'], $credentials['secret'] );

			$this->set_credentials( array(
				'app_id' => $credentials['app_id'],
				'secret' => $credentials['secret'],
				'token' => $token,
			) );

			$api->setDefaultAccessToken( $token );

			try {
				// Returns a `Facebook\FacebookResponse` object
				$response = $api->get( '/me?fields=id,name,email', $token );
			} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
				$this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
			} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
				$this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
			}

			unset( $_SESSION['rop_facebook_credentials'] );
			unset( $_SESSION['rop_facebook_token'] );

			$user = $response->getGraphUser();
			if ( $user->getId() ) {
				$this->service = array(
					'id' => $user->getId(),
					'service' => $this->service_name,
					'credentials' => $this->credentials,
					'public_credentials' => array(
						'app_id' => array(
							'name' => 'APP ID',
							'value' => $this->credentials['app_id'],
							'private' => false,
						),
						'secret' => array(
							'name' => 'APP Secret',
							'value' => $this->credentials['secret'],
							'private' => true,
						),
					),
					'available_accounts' => $this->get_pages( $user ),
				);
				return true;
			}

			return false;
		}// End if().

		return false;
	}

	/**
	 * Method to re authenticate an user based on provided credentials.
	 * Used in DB upgrade.
	 *
	 * @param string $app_id    The app id.
	 * @param string $secret    The app secret.
	 * @param string $token     The token.
	 *
	 * @return bool
	 */
	public function re_authenticate( $app_id, $secret, $token ) {
		$api = $this->get_api( $app_id, $secret );
		$this->set_credentials( array(
			'app_id' => $app_id,
			'secret' => $secret,
			'token' => $token,
		) );
		$api->setDefaultAccessToken( $token );

		try {
			// Returns a `Facebook\FacebookResponse` object
			$response = $api->get( '/me?fields=id,name,email', $token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
		}

		$user = $response->getGraphUser();
		$this->user = $user;
		if ( $user->getId() ) {
			$this->service = array(
				'id' => $user->getId(),
				'service' => $this->service_name,
				'credentials' => $this->credentials,
				'public_credentials' => array(
					'app_id' => array(
						'name' => 'APP ID',
						'value' => $this->credentials['app_id'],
						'private' => false,
					),
					'secret' => array(
						'name' => 'APP Secret',
						'value' => $this->credentials['secret'],
						'private' => true,
					),
				),
				'available_accounts' => $this->get_pages( $user ),
			);

			return true;
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
	 * @param   string $token A Facebook token to use.
	 * @return mixed
	 */
	public function request_api_token( $token = '' ) {
		$api = $this->get_api();

		$helper = $api->getRedirectLoginHelper();

		if ( isset( $token ) && $token != '' && $token != null ) {
			$longAccessToken = new \Facebook\Authentication\AccessToken( $this->token );
			$token = $longAccessToken->getValue();
			return $token->getValue();
		}

		try {
			$accessToken = $helper->getAccessToken();
			if ( ! isset( $accessToken ) ) {
				if ( $helper->getError() ) {
					$this->error->throw_exception( '401 Unauthorized', $this->error->get_fb_exeption_message( $helper ) );
				} else {
					$this->error->throw_exception( '400 Bad Request', 'Bad request' );
				}
			}
			$expires = time() + ( 120 * 24 * 60 * 60 ); // 120 days; 24 hours; 60 minutes; 60 seconds.
			$longAccessToken = new \Facebook\Authentication\AccessToken( $accessToken, $expires );
			$token = $longAccessToken->getValue();
			return $token->getValue();
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
		}
		return false;
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
		$credentials = $data['credentials'];
		if ( ! session_id() ) {
			session_start();
		}

		$_SESSION['rop_facebook_credentials'] = $credentials;

		$api = $this->get_api( $credentials['app_id'], $credentials['secret'] );
		$helper = $api->getRedirectLoginHelper();
		$url = $helper->getLoginUrl( $this->get_endpoint_url( 'authorize' ), $this->permissions );
		return $url;
	}

	/**
	 * Utility method to retrieve pages from the Facebook account.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   object $user The Facebook user.
	 * @return array
	 */
	public function get_pages( $user ) {
		$pages_array = array();
		$api = $this->get_api();
		$pages = $api->get( '/me/accounts' );
		$pages = $pages->getGraphEdge()->asArray();
		foreach ( $pages as $key ) {
			$img = $api->sendRequest( 'GET','/' . $key['id'] . '/picture', array( 'redirect' => false ) );
			$img = $img->getGraphNode()->asArray();

			$pages_array[] = array(
			  'id' => $key['id'],
			  'name' => $key['name'],
			  'account' => $user->getEmail(),
			  'img' => $img['url'],
			  'active' => false,
			  'access_token' => $key['access_token'],
			);
		}
		return $pages_array;
	}

	/**
	 * Method to try and share on facebook.
	 * Moved to a separated method to drive the NPath complexity down.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array  $new_post The Facebook post format array.
	 * @param   int    $page_id The Facebook page ID.
	 * @param   string $token The Facebook page token.
	 * @return bool
	 */
	private function try_post( $new_post, $page_id, $token ) {
		$this->set_api( $this->credentials['app_id'], $this->credentials['secret'] );
		$api = $this->get_api();

		try {
			$api->post( '/' . $page_id . '/feed', $new_post, $token );
			return true;
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return false;
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return false;
		}
	}

	/**
	 * Method for publishing with Facebook service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Optional arguments needed by the method.
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {

		$new_post = array();

		if ( isset( $post_details['post']['post_img'] ) && $post_details['post']['post_img'] !== '' && $post_details['post']['post_img'] !== false ) {
			$new_post['picture'] = $post_details['post']['post_img'];
			$new_post['link'] = $post_details['post']['post_img'];
		}

		$new_post['message'] = $post_details['post']['post_content'];
		if ( $post_details['post']['custom_content'] !== '' ) {
			$new_post['message'] = $post_details['post']['custom_content'];
		}

		if ( isset( $post_details['post']['post_url'] ) && $post_details['post']['post_url'] != '' ) {
			$post_format_helper = new Rop_Post_Format_Helper();
			//$link = ' ' . $post_format_helper->get_short_url( 'www.themeisle.com', $post_details['post']['short_url_service'], $post_details['post']['shortner_credentials'] );
			$link = ' ' . $post_format_helper->get_short_url( $post_details['post']['post_url'], $post_details['post']['short_url_service'], $post_details['post']['shortner_credentials'] );
			$new_post['message'] = $new_post['message'] . $link;
		}

		if ( ! isset( $args['id'] ) || ! isset( $args['access_token'] ) ) {
			return false;
		}

		return $this->try_post( $new_post, $args['id'], $args['access_token'] );
	}

}
