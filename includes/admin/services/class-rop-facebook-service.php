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
	 * An instance of authenticated Facebook user.
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

		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_facebook_credentials',
			)
		) ) {
			return false;
		}

		$credentials = $_SESSION['rop_facebook_credentials'];

		try {
			$api = $this->get_api( $credentials['app_id'], $credentials['secret'] );

			$helper          = $api->getRedirectLoginHelper();
			$longAccessToken = '';
			$accessToken     = $helper->getAccessToken( $this->get_legacy_url() );
			if ( ! isset( $accessToken ) ) {
				if ( $helper->getError() ) {
					$this->error->throw_exception( '401 Unauthorized', $this->error->get_fb_exeption_message( $helper ) );
				} else {
					$this->error->throw_exception( '400 Bad Request', 'Bad request' );
				}
			}
			$expires         = time() + ( 120 * 24 * 60 * 60 ); // 120 days; 24 hours; 60 minutes; 60 seconds.
			$longAccessToken = new \Facebook\Authentication\AccessToken( $accessToken, $expires );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
		}

		$token = $longAccessToken->getValue();

		$_SESSION['rop_facebook_token'] = $token;

		parent::authorize();
		// echo '<script>window.setTimeout("window.close()", 500);</script>';
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $app_id The Facebook APP ID. Default empty.
	 * @param   string $secret The Facebook APP Secret. Default empty.
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
	 * @param   string $app_id The Facebook APP ID. Default empty.
	 * @param   string $secret The Facebook APP Secret. Default empty.
	 *
	 * @return mixed
	 */
	public function set_api( $app_id = '', $secret = '' ) {
		try {
			if ( empty( $app_id ) || empty( $secret ) ) {
				return false;
			}
			$this->api = new \Facebook\Facebook(
				array(
					'app_id'                => $this->strip_whitespace( $app_id ),
					'app_secret'            => $this->strip_whitespace( $secret ),
					'default_graph_version' => 'v2.10',
				)
			);
		} catch ( Exception $exception ) {
			$this->logger->alert_error( 'Can not load Facebook api. Error: ' . $exception->getMessage() );
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
				'rop_facebook_token',
				'rop_facebook_credentials',
			)
		) ) {
			return false;
		}

		if ( ! $this->is_set_not_empty(
			$_SESSION['rop_facebook_credentials'],
			array(
				'app_id',
				'secret',
			)
		) ) {
			return false;
		}
		$credentials = $_SESSION['rop_facebook_credentials'];
		$token       = $_SESSION['rop_facebook_token'];

		$credentials['token'] = $token;
		unset( $_SESSION['rop_facebook_credentials'] );
		unset( $_SESSION['rop_facebook_token'] );

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
		if ( ! $this->is_set_not_empty(
			$args,
			array(
				'app_id',
				'secret',
				'token',
			)
		) ) {
			return false;
		}

		$app_id = $args['app_id'];
		$secret = $args['secret'];
		$token  = $args['token'];

		try {
			$api = $this->get_api( $app_id, $secret );
			$this->set_credentials(
				array(
					'app_id' => $app_id,
					'secret' => $secret,
					'token'  => $token,
				)
			);
			$api->setDefaultAccessToken( $token );

			// Returns a `Facebook\FacebookResponse` object
			$response = $api->get( '/me?fields=id,name,email,picture', $token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );

			return false;
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );

			return false;
		}
		try {
			$user = $response->getGraphUser();
		} catch ( Exception $exception ) {
			$this->logger->alert_error( 'Can not load Facebook user. Error: ' . $exception->getMessage() );
		}
		$this->user = $user;
		$user_id    = $user->getId();
		if ( empty( $user_id ) ) {
			return false;
		}
		$user_details                 = $this->user_default;
		$user_details['id']           = $user->getId();
		$user_details['user']         = $this->normalize_string( $user['name'] );
		$email                        = $user->getEmail();
		$user_details['account']      = empty( $email ) ? '' : $email;
		$user_details['img']          = $user->getPicture()->getUrl();
		$user_details['access_token'] = $token;
		$this->service                = array(
			'id'                 => $user->getId(),
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'public_credentials' => array(
				'app_id' => array(
					'name'    => 'APP ID',
					'value'   => $this->credentials['app_id'],
					'private' => false,
				),
				'secret' => array(
					'name'    => 'APP Secret',
					'value'   => $this->credentials['secret'],
					'private' => true,
				),
			),
			'available_accounts' => array_merge(
				array(
					$user_details,
				),
				$this->get_pages( $user )
			),
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
	 * Utility method to retrieve pages from the Facebook account.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   object $user The Facebook user.
	 *
	 * @return array
	 */
	public function get_pages( $user ) {
		$pages_array = array();
		$api         = $this->get_api();
		try {
			$pages = $api->get( '/me/accounts' );
			$pages = $pages->getGraphEdge();
			do {
				foreach ( $pages->asArray() as $key ) {
					$img                          = $api->sendRequest( 'GET', '/' . $key['id'] . '/picture', array( 'redirect' => false ) );
					$img                          = $img->getGraphNode()->asArray();
					$user_details                 = $this->user_default;
					$user_details['id']           = $key['id'];
					$user_details['user']         = $this->normalize_string( empty( $key['name'] ) ? '' : $key['name'] );
					$user_details['account']      = $user->getEmail();
					$user_details['img']          = $img['url'];
					$user_details['access_token'] = $key['access_token'];
					$user_details['active']       = false;
					$pages_array[]                = $user_details;
				}
			} while ( $pages = $api->next( $pages ) );
		} catch ( Exception $e ) {
			$this->logger->alert_error( 'Can not fetch pages for facebook. Error: ' . $e->getMessage() );
		}

		return $pages_array;
	}

	/**
	 * Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  protected
	 *
	 * @param   string $token A Facebook token to use.
	 *
	 * @return mixed
	 */
	public function request_api_token( $token = '' ) {
		$api = $this->get_api();

		$helper = $api->getRedirectLoginHelper();

		if ( isset( $token ) && $token != '' && $token != null ) {
			$longAccessToken = new \Facebook\Authentication\AccessToken( $this->token );
			$token           = $longAccessToken->getValue();

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
			$expires         = time() + ( 120 * 24 * 60 * 60 ); // 120 days; 24 hours; 60 minutes; 60 seconds.
			$longAccessToken = new \Facebook\Authentication\AccessToken( $accessToken, $expires );
			$token           = $longAccessToken->getValue();

			return $token->getValue();
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
		}

		return false;
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
		$credentials = $data['credentials'];
		if ( ! session_id() ) {
			session_start();
		}

		$_SESSION['rop_facebook_credentials'] = $credentials;

		$api    = $this->get_api( $credentials['app_id'], $credentials['secret'] );
		$helper = $api->getRedirectLoginHelper();
		$url    = $helper->getLoginUrl( $this->get_legacy_url(), $this->permissions );

		return $url;
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

		$this->set_api( $this->credentials['app_id'], $this->credentials['secret'] );

		$post_id = $post_details['post_id'];

		$sharing_data = $this->prepare_for_sharing( $post_details );

		if ( ! isset( $args['id'] ) || ! isset( $args['access_token'] ) ) {
			$this->logger->alert_error( 'Unable to authenticate to facebook, no access_token/id provided. ' );

			return false;
		}

		if ( $this->try_post( $sharing_data['post_data'], $args['id'], $args['access_token'], $post_id, $sharing_data['type'] ) ) {
			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on %s ',
					html_entity_decode( get_the_title( $post_id ) ),
					$args['user'],
					$post_details['service']
				)
			);
		} else {
			return false;
		}
	}

	/**
	 * Method for preparing post to share with Facebook service.
	 *
	 * @since   8.1.0
	 * @access  private
	 *
	 * @param   array $post_details The post details to be published by the service.
	 *
	 * @return array
	 */
	private function prepare_for_sharing( $post_details ) {
		$post_id = $post_details['post_id'];

		/**
		 * If is not an attachment and we do have an url share it as regular post.
		 *
		 * TODO Add in the post format tab, for facebook, an posting behaviour option,
		 * where we should allow user to choose how the posting with image will work, as regular post or photo post.
		 */
		if ( get_post_type( $post_id ) !== 'attachment' && ! empty( $post_details['post_url'] ) ) {

			$new_post['message'] = $this->strip_excess_blank_lines( $post_details['content'] ) . $post_details['hashtags'];

			if ( ! empty( $post_details['post_url'] ) ) {
				$new_post['name'] = html_entity_decode( get_the_title( $post_details['post_id'] ) );
				$new_post['link'] = $this->get_url( $post_details );
			}
			if ( ! empty( $post_details['post_image'] ) ) {
				$new_post['picture'] = $post_details['post_image'];
			}

			return [
				'post_data' => $new_post,
				'type'      => 'post',
			];
		}

		// If we don't have an image link share as regular post.
		if ( empty( $post_details['post_image'] ) ) {

			$new_post['message'] = $post_details['content'] . $post_details['hashtags'];

			if ( ! empty( $post_details['post_url'] ) ) {
				$new_post['name'] = html_entity_decode( get_the_title( $post_details['post_id'] ) );
				$new_post['link'] = $this->get_url( $post_details );
			}

			return [
				'post_data' => $new_post,
				'type'      => 'post',
			];
		}

		$api = $this->get_api();

		if ( strpos( $post_details['mimetype']['type'], 'image' ) !== false ) {

			$new_post['source'] = $api->fileToUpload( $post_details['post_image'] );

			$new_post['message'] = $post_details['content'] . $this->get_url( $post_details ) . $post_details['hashtags'];

			return [
				'post_data' => $new_post,
				'type'      => 'photo',
			];
		}
		if ( strpos( $post_details['mimetype']['type'], 'video' ) !== false ) {

			$new_post['source']      = $api->fileToUpload( $post_details['post_image'] );
			$new_post['title']       = html_entity_decode( get_the_title( $post_id ) );
			$new_post['description'] = $post_details['content'] . $this->get_url( $post_details ) . $post_details['hashtags'];

			return [
				'post_data' => $new_post,
				'type'      => 'video',
			];
		}

	}

	/**
	 * Method to try and share on facebook.
	 * Moved to a separated method to drive the NPath complexity down.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array  $new_post The Facebook post format array.
	 * @param   int    $page_id The Facebook page ID.
	 * @param   string $token The Facebook page token.
	 * @param   int    $post_id The post ID.
	 * @param   string $posting_type Type of posting.
	 *
	 * @return bool
	 */
	private function try_post( $new_post, $page_id, $token, $post_id, $posting_type ) {

		$this->set_api( $this->credentials['app_id'], $this->credentials['secret'] );
		$api = $this->get_api();

		try {
			switch ( $posting_type ) {
				case 'photo':
					$api->post( '/' . $page_id . '/photos', $new_post, $token );
					break;
				case 'video':
					$api->post( '/' . $page_id . '/videos', $new_post, $token );
					break;
				default:
					$api->post( '/' . $page_id . '/feed', $new_post, $token );
					break;
			}

			return true;
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$this->logger->alert_error( 'Unable to share post for facebook.  Error: ' . $e->getMessage() );

			return false;
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$this->logger->alert_error( 'Unable to share post for facebook.  Error: ' . $e->getMessage() );

			return false;
		}
	}

}
