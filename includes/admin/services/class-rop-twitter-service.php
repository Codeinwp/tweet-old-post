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
	 * Deprecated value, will be used as legacy app as twitter now no longer supports this, due to the restrictions
	 * of the callback URL.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $consumer_key The Twitter APP Consumer Key.
	 */
	private $consumer_key = 'ofaYongByVpa3NDEbXa2g';

	/**
	 * Holds the Twitter APP Consumer Secret.
	 *
	 * Deprecated value, will be used as legacy app as twitter now no longer supports this, due to the restrictions
	 * of the callback URL.
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
	 * @param   string $consumer_key The consumer key. Default empty.
	 * @param   string $consumer_secret The consumer secret. Default empty.
	 */
	public function set_api( $oauth_token = '', $oauth_token_secret = '', $consumer_key = '', $consumer_secret = '' ) {
		if ( empty( $oauth_token ) ) {
			$oauth_token = null;
		}
		if ( empty( $oauth_token_secret ) ) {
			$oauth_token_secret = null;
		}
		if ( empty( $consumer_key ) ) {
			$consumer_key = $this->consumer_key;
		}
		if ( empty( $consumer_secret ) ) {
			$consumer_secret = $this->consumer_secret;
		}

		$this->api = new \Abraham\TwitterOAuth\TwitterOAuth( $consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret );

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
				'rop_twitter_credentials',
			)
		) ) {
			return false;
		}
		$token                    = $_SESSION['rop_twitter_oauth_token'];
		$token['consumer_key']    = $_SESSION['rop_twitter_credentials']['consumer_key'];
		$token['consumer_secret'] = $_SESSION['rop_twitter_credentials']['consumer_secret'];
		unset( $_SESSION['rop_twitter_oauth_token'] );
		unset( $_SESSION['rop_twitter_request_token'] );
		unset( $_SESSION['rop_twitter_credentials'] );

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
				'consumer_key',
				'consumer_secret',
			)
		) ) {
			return false;
		}
		$this->set_api( $args['oauth_token'], $args['oauth_token_secret'], $args['consumer_key'], $args['consumer_secret'] );
		$api                   = $this->get_api();
		$this->consumer_secret = $args['consumer_secret'];
		$this->consumer_key    = $args['consumer_key'];

		$this->set_credentials(
			array_intersect_key(
				$args, array(
					'oauth_token'        => '',
					'oauth_token_secret' => '',
					'consumer_key'       => '',
					'consumer_secret'    => '',
				)
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
			'public_credentials' => array(
				'consumer_key'    => array(
					'name'    => 'Consumer Key',
					'value'   => $this->consumer_key,
					'private' => false,
				),
				'consumer_secret' => array(
					'name'    => 'Consumer Secret',
					'value'   => $this->consumer_secret,
					'private' => true,
				),
			),
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
			$this->set_api( $this->credentials['oauth_token'], $this->credentials['oauth_token_secret'], $this->consumer_key, $this->consumer_secret );
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

		$model                  = new Rop_Services_Model();
		$authenticated_services = $model->get_authenticated_services( $this->service_name );

		if ( ! empty( $authenticated_services ) ) {
			$user['active'] = false;
		}
		$user['id']      = $data->id;
		$user['account'] = $this->normalize_string( $data->name );
		$user['user']    = '@' . $this->normalize_string( $data->screen_name );
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
		$credentials = $data['credentials'];
		if ( ! session_id() ) {
			session_start();
		}
		if ( empty( $credentials ) ) {
			return $this->get_legacy_url();
		}
		if ( ! empty( $credentials['consumer_key'] ) ) {
			$this->consumer_key = trim( $credentials['consumer_key'] );
		}
		if ( ! empty( $credentials['consumer_secret'] ) ) {
			$this->consumer_secret = trim( $credentials['consumer_secret'] );
		}
		$_SESSION['rop_twitter_credentials'] = $credentials;

		$request_token = $this->request_api_token();
		if ( empty( $request_token ) ) {
			return $this->get_legacy_url();
		}
		$this->set_api( $request_token['oauth_token'], $request_token['oauth_token_secret'], $credentials['consumer_key'], $credentials['consumer_secret'] );
		$api = $this->get_api();

		$url = $api->url(
			'oauth/authorize', [
				'oauth_token' => $request_token['oauth_token'],
				'force_login' => false,
			]
		);
		if ( empty( $url ) ) {
			return $this->get_legacy_url();
		}

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

		$api = $this->get_api();
		try {
			$request_token = $api->oauth( 'oauth/request_token', array( 'oauth_callback' => $this->get_legacy_url( 'twitter' ) ) );
		} catch ( Exception $e ) {
			$this->logger->alert_error( 'Error connecting twitter ' . $e->getMessage() );

			return '';
		}
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
		if ( Rop_Admin::rop_site_is_staging() ) {
			return false;
		}

		$this->set_api(
			$this->credentials['oauth_token'],
			$this->credentials['oauth_token_secret'],
			isset( $this->credentials['consumer_key'] ) ? $this->credentials['consumer_key'] : '',
			isset( $this->credentials['consumer_secret'] ) ? $this->credentials['consumer_secret'] : ''
		);
		$api      = $this->get_api();
		$new_post = array();

		$post_type = new Rop_Posts_Selector_Model;

		$post_id = $post_details['post_id'];
		$message = $post_details['content'];

		if ( ! empty( $post_details['post_image'] ) && empty( $post_type->media_post( $post_id ) ) ) {
			$file_path      = get_attached_file( get_post_thumbnail_id( $post_id ) );
			$media_response = $api->upload( 'media/upload', array( 'media' => $file_path ) );
			if ( isset( $media_response->media_id_string ) ) {
				$new_post['media_ids'] = $media_response->media_id_string;
			} else {
				$this->logger->alert_error( sprintf( 'Can not upload photo. Error: %s', json_encode( $media_response ) ) );
			}
		}

		// Photo posts
		if ( ! empty( $post_type->media_post( $post_id ) ) && ! in_array( get_post_mime_type( $post_id ), $post_type->rop_supported_mime_types()['video'] ) ) {
			if ( ! empty( get_attached_file( $post_id ) ) ) {
					$media_response = $api->upload( 'media/upload', array( 'media' => get_attached_file( $post_id ) ) );
				if ( isset( $media_response->media_id_string ) ) {
					$new_post['media_ids'] = $media_response->media_id_string;
				} else {
					$this->logger->alert_error( sprintf( 'Can not upload photo. Error: %s', json_encode( $media_response ) ) );
				}
			} else {
				$this->logger->alert_error( 'Twitter: Not a valid photo media file. ID: ' . $post_id );
			}
		}

		// Video post | Twitter primarily supports MP4 video, so lets only allow that
		if ( ! empty( $post_type->media_post( $post_id ) ) && get_post_mime_type( $post_id ) == 'video/mp4' ) {

			if ( ! empty( get_attached_file( $post_id ) ) ) {

				$media_response = $api->upload( 'media/upload', array( 'media' => get_attached_file( $post_id ), 'media_type' => 'video/mp4', 'media_category' => 'tweet_video' ), true );

				if ( isset( $media_response->media_id_string ) ) {

					$new_post['media_ids'] = $media_response->media_id_string;

					$limit = 0;
					do {
						$upload_status = $api->mediaStatus( $media_response->media_id_string );
						sleep( 6 );
						$limit++;
					} while ( $upload_status->processing_info->state !== 'succeeded' && $limit <= 10 );

				} else {
					$this->logger->alert_error( sprintf( 'Can not upload video. Error: %s', json_encode( $media_response ) ) );
				}
			} else {
						$this->logger->alert_error( 'Twitter: Not a valid video media file. ID: ' . $post_id );
			}
		}

		$new_post['status'] = $message . $this->get_url( $post_details ) . $post_details['hashtags'];
		$this->logger->info( sprintf( 'Before twitter share: %s', json_encode( $new_post ) ) );

		$response = $api->post( 'statuses/update', $new_post );
		if ( isset( $response->id ) ) {
			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on %s ',
					html_entity_decode( get_the_title( $post_details['post_id'] ) ),
					$args['user'],
					$post_details['service']
				)
			);

			return true;
		} else {
			$this->logger->alert_error( sprintf( 'Error posting on twitter. Error: %s', json_encode( $response ) ) );
		}

		return false;
	}

}
