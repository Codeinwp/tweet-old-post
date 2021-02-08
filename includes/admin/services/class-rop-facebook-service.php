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
	private $permissions = array( 'email', 'pages_manage_posts' );

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
					'default_graph_version' => 'v7.0',
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
			} while ( $pages == $api->next( $pages ) );
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

		$api = $this->get_api( $credentials['app_id'], $credentials['secret'] );
		if ( empty( $api ) || ! method_exists( $api, 'getRedirectLoginHelper' ) ) {
			return '';
		}
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
	 * @param   array $args Optional arguments needed by the method (the account data).
	 *
	 * @return mixed
	 * @throws \Facebook\Exceptions\FacebookSDKException Facebook library exception.
	 */
	public function share( $post_details, $args = array() ) {

		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		$post_id = $post_details['post_id'];
		$post_url = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];
		$global_settings = new Rop_Global_Settings();

		if ( array_key_exists( 'account_type', $args ) ) {

			if ( ( $args['account_type'] === 'instagram_account' || $args['account_type'] === 'facebook_group' ) && $global_settings->license_type() < 1 ) {
				$this->logger->alert_error( sprintf( Rop_I18n::get_labels( 'errors.license_not_active' ), $args['user'] ) );
				return false;
			}

			// **** Instagram Sharing ***** //
			if ( $args['account_type'] === 'instagram_account' && class_exists( 'Rop_Pro_Instagram_Service' ) ) {

				$response = Rop_Pro_Instagram_Service::share( $post_details, $args );

				return $response;

			}
			// ***** //
		}

		// Backwards compatibilty < v8.7.0 we weren't storing 'account_type' for Facebook groups yet.
		if ( strpos( $args['user'], 'Facebook Group:' ) !== false && $global_settings->license_type() < 1 ) {
			$this->logger->alert_error( sprintf( Rop_I18n::get_labels( 'errors.license_not_active' ), $args['user'] ) );
			return false;
		}

		$installed_with_app = get_option( 'rop_facebook_via_rs_app' );

		if ( empty( $installed_with_app ) ) {
			$this->set_api( $this->credentials['app_id'], $this->credentials['secret'] );
		}

		// FB link post
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$sharing_data = $this->fb_article_post( $post_details );
		}

		// FB plain text post
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$sharing_data = $this->fb_text_post( $post_details );
		}

		// FB media post
		if ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {

			if ( strpos( get_post_mime_type( $post_details['post_id'] ), 'video' ) === false ) {
				$sharing_data = $this->fb_image_post( $post_details );
			} else {
				$sharing_data = $this->fb_video_post( $post_details );
			}
		}

		if ( ! isset( $args['id'] ) || ! isset( $args['access_token'] ) ) {
			$this->logger->alert_error( 'Unable to authenticate to facebook, no access_token/id provided. ' );

			return false;
		}

		if ( $this->try_post( $sharing_data['post_data'], $args['id'], $args['access_token'], $post_id, $sharing_data['type'] ) ) {
			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on %s ',
					html_entity_decode( get_the_title( $post_id ), ENT_QUOTES ), // TODO Set ENT_QUOTES for all other entity decode occurences in plugin
					$args['user'],
					$post_details['service']
				)
			);

			return true;

		} else {
			return false;
		}

	}

	/**
	 * Method for preparing article post to share with Facebook service.
	 *
	 * @since   8.6.4
	 * @access  private
	 *
	 * @param   array $post_details The post details to be published by the service.
	 *
	 * @return array
	 */
	private function fb_article_post( $post_details ) {

		$new_post = array();

		$new_post['message'] = $this->strip_excess_blank_lines( $post_details['content'] ) . $post_details['hashtags'];

		$new_post['link'] = $this->get_url( $post_details );

		return array(
			'post_data' => $new_post,
			'type'      => 'post',
		);
	}

	/**
	 * Method for preparing image post to share with Facebook service.
	 *
	 * @since   8.6.4
	 * @access  private
	 *
	 * @param   array $post_details The post details to be published by the service.
	 *
	 * @return array
	 */
	private function fb_image_post( $post_details ) {

		$attachment_url = $post_details['post_image'];

		// if the post has no image but "Share as image post" is checked
		// share as an article post
		if ( empty( $attachment_url ) ) {
			$this->logger->info( 'No image set for post, but "Share as Image Post" is checked. Falling back to article post' );
			return $this->fb_article_post( $post_details );
		}

		$new_post = array();

		$new_post['url']     = $attachment_url;
		$new_post['source']  = $this->get_path_by_url( $attachment_url, $post_details['mimetype'] ); // get image path
		$new_post['caption'] = $post_details['content'] . $this->get_url( $post_details ) . $post_details['hashtags'];

		return array(
			'post_data' => $new_post,
			'type'      => 'photo',
		);

	}

	/**
	 * Method for preparing video post to share with Facebook service.
	 *
	 * @since   8.6.4
	 * @access  private
	 *
	 * @param   array $post_details The post details to be published by the service.
	 *
	 * @return array
	 */
	private function fb_video_post( $post_details ) {

		$new_post = array();

			$image     = $this->get_path_by_url( $post_details['post_image'], $post_details['mimetype'] );
			$new_post['source']      = $image;
			// $new_post['source']      = $api->videoToUpload( $image );
			$new_post['title']       = html_entity_decode( get_the_title( $post_details['post_id'] ), ENT_QUOTES );
			$new_post['description'] = $post_details['content'] . $this->get_url( $post_details ) . $post_details['hashtags'];

			return array(
				'post_data' => $new_post,
				'type'      => 'video',
			);
	}

	/**
	 * Method for preparing plain text post to share with Facebook service.
	 *
	 * @since   8.6.4
	 * @access  private
	 *
	 * @param   array $post_details The post details to be published by the service.
	 *
	 * @return array
	 */
	private function fb_text_post( $post_details ) {

		$new_post = array();

		$new_post['message'] = $post_details['content'] . $post_details['hashtags'];

		return array(
			'post_data' => $new_post,
			'type'      => 'post',
		);

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

		$installed_with_app = get_option( 'rop_facebook_via_rs_app' );

		$path = '/' . $page_id . '/feed';
		switch ( $posting_type ) {
			case 'photo':
				$path = '/' . $page_id . '/photos';
				break;
			case 'video':
				$path = '/' . $page_id . '/videos';
				break;
			default:
				break;
		}

		if ( empty( $installed_with_app ) ) {
			$this->set_api( $this->credentials['app_id'], $this->credentials['secret'] );
		}
		if ( $this->get_api() && empty( $installed_with_app ) ) {
			// Page was added using user application (old method)
			// Try post via Facebook Graph SDK
			$api = $this->get_api();
			try {

				// Scrape post URL before sharing
				if ( isset( $new_post['link'] ) ) {
					$this->rop_fb_scrape_url( $posting_type, $post_id, $token );
				}

				$api->post( $path, $new_post, $token );

				return true;
			} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
				$error_message = $e->getMessage();

				if (
					strpos( $error_message, '(#100)' ) !== false &&
					(
						! empty( $new_post['name'] ) ||
						( ! empty( $new_post['link'] ) && isset( $new_post['message'] ) )
					)
				) {
					// https://developers.facebook.com/docs/graph-api/reference/v3.2/page/feed#custom-image
					// retry without name and with link inside message.
					if ( isset( $new_post['name'] ) ) {
						unset( $new_post['name'] );
					}
					if ( ! empty( $new_post['link'] ) && isset( $new_post['message'] ) ) {
						$new_post['message'] .= $new_post['link'];
						unset( $new_post['link'] );
					}

					try {
						$api->post( $path, $new_post, $token );

						return true;
					} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
						$this->logger->alert_error( 'Unable to share post for facebook. (FacebookResponseException) Error: ' . $e->getMessage() );
						$this->rop_get_error_docs( $e->getMessage() );

						return false;
					} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
						$this->logger->alert_error( 'Unable to share post for facebook.  Error: ' . $e->getMessage() );
						$this->rop_get_error_docs( $e->getMessage() );

						return false;
					}
				} else {
					$this->logger->alert_error( 'Unable to share post for facebook. (FacebookResponseException) Error: ' . $error_message );
					$this->rop_get_error_docs( $e->getMessage() );

					return false;
				}
			} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
				$this->logger->alert_error( 'Unable to share post for facebook.  Error: ' . $e->getMessage() );
				$this->rop_get_error_docs( $e->getMessage() );

				return false;
			}
		} else {
			// Page was added using ROP application (new method)
			$post_data                 = $new_post;
			$post_data['access_token'] = $token;

			if ( 'video' === $posting_type ) {
				$url = 'https://graph-video.facebook.com/v7.0' . $path;
			} else {
				$url = 'https://graph.facebook.com/v7.0' . $path;
			}

			// Scrape post URL before sharing
			if ( isset( $post_data['link'] ) ) {
				$this->rop_fb_scrape_url( $posting_type, $post_id, $token );
			}

			// Hold this value for now
			$attachment_url  = '';
			$attachment_path = '';

			if ( isset( $post_data['url'] ) ) {
				$attachment_url = trim( $post_data['url'] );
				unset( $post_data['url'] ); // Unset from posting parameters
			}

			if ( isset( $post_data['source'] ) ) {
				$attachment_path = $post_data['source'];
				unset( $post_data['source'] ); // Remove image path as it's not needed and it might create an error.
			}

			// If the cURL library is installed and usable
			if ( $this->is_curl_active() && ! empty( $attachment_path ) && false === $this->is_remote_file( $attachment_path ) ) {
				$post_data['source'] = new CurlFile( realpath( $attachment_path ), mime_content_type( $attachment_path ) );

				// Send the request via cURL
				$body     = $this->remote_post_curl( $url, $post_data );
				$response = $body; // Compatible with the code before.

				// If the previous request failed, let's try over HTTP request.
				if ( isset( $body['error'] ) ) {
					if ( ! empty( $attachment_url ) ) {
						$post_data['url'] = $attachment_url; // To use HTTP request, we need image url back.
					}
					if ( isset( $post_data['source'] ) ) {
						unset( $post_data['source'] );
					}

					// Send the request via http request.
					$sent_request = $this->remote_post_http( $url, $post_data );
					$response     = $sent_request['response'];
					$body         = $sent_request['body'];
				}
			} else {

				if ( ! empty( $attachment_url ) ) {
					$post_data['url'] = $attachment_url; // To use HTTP request, we need image url back.
				}
				// Send the request via http request.
				$sent_request = $this->remote_post_http( $url, $post_data );
				$response     = $sent_request['response'];
				$body         = $sent_request['body'];
			}

			if ( ! empty( $body['id'] ) ) {
				return true;
			} elseif ( ! empty( $body['error']['message'] ) ) {
				if (
					strpos( $body['error']['message'], '(#100)' ) !== false &&
					(
						! empty( $post_data['name'] ) ||
						( ! empty( $post_data['link'] ) && isset( $post_data['message'] ) )
					)
				) {
					// https://developers.facebook.com/docs/graph-api/reference/v3.2/page/feed#custom-image
					// retry without name and with link inside message.
					if ( isset( $post_data['name'] ) ) {
						unset( $post_data['name'] );
					}
					if ( ! empty( $post_data['link'] ) && isset( $post_data['message'] ) ) {
						$post_data['message'] .= $post_data['link'];
						unset( $post_data['link'] );
					}

					if ( isset( $post_data['source'] ) ) {
						unset( $post_data['source'] );
					}
					if ( isset( $post_data['url'] ) ) {
						unset( $post_data['url'] );
					}

					// If the cURL library is installed and usable
					if ( $this->is_curl_active() && ! empty( $attachment_path ) && false === $this->is_remote_file( $attachment_path ) ) {
						$post_data['source'] = new CurlFile( realpath( $attachment_path ), mime_content_type( $attachment_path ) );

						// Send the request via cURL
						$body     = $this->remote_post_curl( $url, $post_data );
						$response = $body; // Compatible with the code before.

						// If the previous request failed, let's try over HTTP request.
						if ( isset( $body['error'] ) ) {
							if ( ! empty( $attachment_url ) ) {
								$post_data['url'] = $attachment_url; // To use HTTP request, we need image url back.
							}
							if ( isset( $post_data['source'] ) ) {
								unset( $post_data['source'] );
							}
							// Send the request via http request.
							$sent_request = $this->remote_post_http( $url, $post_data );
							$response     = $sent_request['response'];
							$body         = $sent_request['body'];
						}
					} else {

						if ( ! empty( $attachment_url ) ) {
							$post_data['url'] = $attachment_url; // To use HTTP request, we need image url back.
						}
						// Send the request via http request.
						$sent_request = $this->remote_post_http( $url, $post_data );
						$response     = $sent_request['response'];
						$body         = $sent_request['body'];
					}

					if ( ! empty( $body['id'] ) ) {
						return true;
					} elseif ( ! empty( $body['error']['message'] ) ) {
						$this->logger->alert_error( 'Error Posting to Facebook: ' . $body['error']['message'] );
						$this->rop_get_error_docs( $body['error']['message'] );

						return false;
					} else {
						$this->logger->alert_error( 'Error Posting to Facebook, response: ' . print_r( $response, true ) );

						return false;
					}
				} else {
					$this->logger->alert_error( 'Error Posting to Facebook: ' . $body['error']['message'] );
					$this->rop_get_error_docs( $body['error']['message'] );

					return false;
				}
			} else {
				$this->logger->alert_error( 'Error Posting to Facebook, response: ' . print_r( $response, true ) );

				return false;
			}
		}
	}


	/**
	 * Post to FB using cURL module.
	 *
	 * @param string $url Facebook link path.
	 * @param array  $post_data Data to be posted.
	 *
	 * @since 8.5.0
	 *
	 * @return array|mixed|object
	 */
	public function remote_post_curl( $url = '', $post_data = array() ) {

		$connection = curl_init();
		curl_setopt( $connection, CURLOPT_URL, $url );
		curl_setopt( $connection, CURLOPT_HTTPHEADER, array( 'Content-Type: multipart/form-data' ) );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $connection, CURLOPT_POST, true );
		curl_setopt( $connection, CURLOPT_POSTFIELDS, $post_data );
		$data = curl_exec( $connection );

		return json_decode( $data, true );
	}

	/**
	 * Post to FB using the WordPress function.
	 *
	 * @param string $url Facebook link path.
	 * @param array  $post_data Data to be posted.
	 *
	 * @since 8.5.0
	 *
	 * @return array|mixed|object
	 */
	public function remote_post_http( $url = '', $post_data = array() ) {
		$response = wp_remote_post(
			$url,
			array(

				'body'    => $post_data,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'timeout' => 60,

			)
		);

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return array(
			'response' => $response,
			'body'     => $body,
		);
	}

	/**
	 * Method to add pages.
	 * Used in Rest Api.
	 *
	 * @since   8.3.0
	 * @access  public
	 *
	 * @param   array $account_data Facebook pages data.
	 *
	 * @return  bool
	 */
	public function add_account_with_app( $account_data ) {
		if ( ! $this->is_set_not_empty( $account_data, array( 'id', 'pages' ) ) ) {
			return false;
		}

		$accounts = array();

		$pages_arr = $account_data['pages'];

		for ( $i = 0; $i < sizeof( $pages_arr ); $i ++ ) {

			$page_data = unserialize( base64_decode( $pages_arr[ $i ] ) );
			// assign default values to variable
			$page                 = $this->user_default;
			$page['id']           = $page_data['id'];
			$page['user']         = $this->normalize_string( empty( $page_data['name'] ) ? '' : $page_data['name'] );

			if ( array_key_exists( 'user_name', $page_data ) ) {
				$page['username']     = $page_data['user_name'];
			}

			if ( array_key_exists( 'account_type', $page_data ) ) {
				$page['account_type']     = $page_data['account_type'];
			}

			$page['account']      = $page_data['email'];
			$page['img']          = apply_filters( 'rop_custom_fb_avatar', $page_data['img'] );
			$page['access_token'] = $page_data['access_token'];
			if ( $i == 0 ) {
				$page['active'] = true;
			} else {
				$page['active'] = false;
			}
			$accounts[] = $page;
		}

		$this->service = array(
			'id'                 => unserialize( base64_decode( $account_data['id'] ) ),
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'available_accounts' => $accounts,
		);

		return true;
	}

	/**
	 * Method to scrape post URLs before sharing.
	 *
	 * Facebook crawler caches post details, this method ensures the shared post always reflects the correct info
	 *
	 * @since   8.5.0
	 * @access  public
	 *
	 * @param   array $posting_type The type of post being created.
	 * @param   array $post_id The post id.
	 * @param   array $token The access token.
	 */
	public function rop_fb_scrape_url( $posting_type, $post_id, $token ) {

		if ( get_post_type( $post_id ) === 'revive-network-share' ) {
			$this->logger->info( 'This is a Revive Network share, skipped Facebook scraping.' );
			return;
		}

		// Scrape post URL before sharing
		if ( $posting_type !== 'video' && $posting_type !== 'photo' ) {

			$scrape = array();
			$url = get_permalink( $post_id );

			$scrape['id']           = $url . '?scrape=true';
			$scrape['access_token'] = $token;

			$scrape_response = wp_remote_post(
				'https://graph.facebook.com',
				array(

					'body'    => $scrape,
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
					'timeout' => 60,

				)
			);

			$body = wp_remote_retrieve_body( $scrape_response );

			$this->logger->info( 'Scrape Info: ' . $body );

		}

	}

	/**
	 * Method to populate additional data.
	 *
	 * @since   8.5.13
	 * @access  public
	 * @return mixed
	 */
	public function populate_additional_data( $account ) {
		if ( strpos( $account['user'], 'Instagram Account:' ) === false ) {
			$account['link'] = sprintf( 'https://facebook.com/%s', $account['id'] );
		} else {
			$account['link'] = sprintf( 'https://instagram.com/%s', $account['username'] );
		}
		return $account;
	}

}
