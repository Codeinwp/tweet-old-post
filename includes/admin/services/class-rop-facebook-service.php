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
	 * Stores the App ID.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $app_id The Facebook App ID.
	 */
	private $app_id;

	/**
	 * Stores the App Secret.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $secret The Facebook App Secret.
	 */
	private $secret;

	/**
	 * Stores the Facebook token after auth.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $token The Facebook token.
	 */
	private $token;

	/**
	 * Stores the \Facebook\Facebook instance.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     \Facebook\Facebook $fb Instance.
	 */
	private $fb;

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Facebook';
		$this->credentials = $this->model->get_option( 'credentials' );

		$this->set_defaults( 'app_id' );
		$this->set_defaults( 'secret' );
		$this->set_defaults( 'token' );

		$this->register_endpoint( 'login', 'req_login' );
		$this->register_endpoint( 'auth', 'auth' );
	}

	/**
	 * Utility method to set default values for service.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $key The key to instantiate.
	 */
	private function set_defaults( $key ) {
		$this->$key = '';
		if ( isset( $this->credentials[ $key ] ) && $this->credentials[ $key ] != '' && $this->credentials[ $key ] != null ) {
			$this->$key = $this->credentials[ $key ];
		}
	}

	/**
	 * Utility method to get the service token.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return string
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Utility method to register the service token.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $value The value to be stored.
	 */
	public function set_token( $value ) {
		$this->token = $value;
		$this->credentials['token'] = $this->token;
		$this->model->set_option( 'credentials', $this->credentials );
	}

	/**
	 * Utility method to register credentials for auth.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $args The credentials array.
	 */
	public function credentials( $args ) {
		foreach ( $args as $key => $value ) {
			if ( in_array( $key, array( 'app_id', 'secret' ) ) ) {
				$this->$key = $value;
				$this->credentials[ $key ] = $this->$key;
			}
		}
		$this->model->set_option( 'credentials', $this->credentials );
	}

	/**
	 * Utility method to auth with Facebook.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function auth() {
		if ( ! session_id() ) {
			session_start();
		}

		$error = new Rop_Exception_Handler();

		$this->fb = new \Facebook\Facebook([
			'app_id' => $this->app_id,
			'app_secret' => $this->secret,
			'default_graph_version' => 'v2.10',
		]);

		$fb = $this->fb;

		$helper = $fb->getRedirectLoginHelper();

		if ( isset( $this->token ) && $this->token != '' && $this->token != null ) {
			$longAccessToken = new \Facebook\Authentication\AccessToken( $this->token );
		} else {
			try {
				$accessToken = $helper->getAccessToken();
				if ( ! isset( $accessToken ) ) {
					if ( $helper->getError() ) {
						$error->throw_exception( '401 Unauthorized', $error->get_fb_exeption_message( $helper ) );
					} else {
						$error->throw_exception( '400 Bad Request', 'Bad request' );
					}
				}
				$expires = time() + ( 120 * 24 * 60 * 60 ); // 120 days; 24 hours; 60 minutes; 60 seconds.
				$longAccessToken = new \Facebook\Authentication\AccessToken( $accessToken, $expires );
			} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
				$error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
			} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
				$error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
			}
		}

		$this->set_token( $longAccessToken->getValue() );
		$fb->setDefaultAccessToken( $this->token );

		try {
			// Returns a `Facebook\FacebookResponse` object
			$response = $fb->get( '/me?fields=id,name', $this->token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
		}

		$user = $response->getGraphUser();
		if ( $user->getId() ) {
			$this->is_auth = true;
		}
	}

	/**
	 * Method to return a Rop_User_Model.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $page A Facebook page array.
	 * @return Rop_User_Model
	 */
	public function get_user( $page ) {
		$user = new Rop_User_Model( array(
			'user_id' => $page['id'],
			'user_name' => $page['name'],
			'user_picture' => $page['img'],
			'user_service' => $this->service_name,
			'user_credentials' => array(
				'token' => $page['access_token'],
			),
		) );
		return $user;
	}

	/**
	 * Utility method to retrieve pages from the Facebook account.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_pages() {
		$pages_array = array();
		$fb = $this->fb;
		$pages = $fb->get( '/me/accounts' );
		$pages = $pages->getGraphEdge()->asArray();
		foreach ( $pages as $key ) {

			$img = $fb->sendRequest( 'GET','/' . $key['id'] . '/picture', array( 'redirect' => false ) );
			$img = $img->getGraphObject()->asArray();

			$pages_array[] = array(
			  'id' => $key['id'],
			  'name' => $key['name'],
			  'img' => $img['url'],
			  'access_token' => $key['access_token'],
			);
		}
		return $pages_array;
	}

	/**
	 * Method for publishing with Facebook service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $post_details The post details to be published by the service.
	 * @return mixed
	 */
	public function share( $post_details ) {
		$error = new Rop_Exception_Handler();
		$id = '1168461009964049';
		$page_token = 'EAAGrutRBO0ABAHa5ZCq2OWBsZC3o2y6lZA5TQPBNUzBkLZBZCdg28EymWSvJG8yh4H2a5n2ZCP4YibXd5i5YGiS29sltqStlwNvCnxTUV9tUwPyfd1wZBQ3RZC7hp3YZAuVBjYgXdUgZBY3MeqU5IlvKnZBOPHyo5g4ilO2FZC2q5CpkCBiJ3Nk849ZBNDjAIcZBPmadEZD';
		$fb = $this->fb;
		try {
			$post = $fb->post( '/' . $id . '/feed', array('message' => $post_details['message'], 'link' => 'https://themeisle.com', 'picture' => 'https://cdn.pixabay.com/photo/2016/01/19/18/00/city-1150026_960_720.jpg' ), $page_token );
			$post = $post->getGraphNode()->asArray();
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
		}

		var_dump( $post );
	}

}
