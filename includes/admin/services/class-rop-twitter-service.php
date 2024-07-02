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
	private $consumer_key = 'OE9PNEEzMFZBTHNvOE02T3pOUmc6MTpjaQ';

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
	private $consumer_secret = 'P9zJ8jKGBM_tdIP_RzAUGkos6fwKPYr-ezVqwa8rXKOCI_ndbT';


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

		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_twitter_request_token',
			)
		) ) {
			return false;
		}

		$request_token = $_SESSION['rop_twitter_request_token'];

		$api = $this->get_api( $request_token['oauth_token'], $request_token['oauth_token_secret'] );

		$access_token = $api->oauth(
			'oauth/access_token',
			array(
				'oauth_verifier' => $_GET['oauth_verifier'],
			)
		);

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
	 * @return \Abraham\TwitterOAuth\TwitterOAuth
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

		$this->api = new \Abraham\TwitterOAuth\TwitterOAuth( $this->strip_whitespace( $consumer_key ), $this->strip_whitespace( $consumer_secret ), $this->strip_whitespace( $oauth_token ), $this->strip_whitespace( $oauth_token_secret ) );

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
			$_SESSION,
			array(
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
			$args,
			array(
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
				$args,
				array(
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
					'name'    => 'API Key',
					'value'   => $this->consumer_key,
					'private' => false,
				),
				'consumer_secret' => array(
					'name'    => 'API secret key',
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
		// assign default values to variable
		$user = $this->user_default;

		// Check credentials if the user is using his own dev account.
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

		if ( isset( $data->activate_account ) && $data->activate_account ) { // Used by E2E tests.
			$user['active'] = true;
		}

		$user['id']      = $data->id;
		$user['account'] = $this->normalize_string( $data->name );
		$user['user']    = '@' . $this->normalize_string( $data->screen_name );
		$user['img']     = apply_filters( 'rop_custom_tw_avatar', $img );
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
			'oauth/authorize',
			array(
				'oauth_token' => $request_token['oauth_token'],
				'force_login' => false,
			)
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
	 * Method for creating link(article) posts to Twitter.
	 *
	 * @since  8.7.0
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 *
	 * @return array
	 */
	private function twitter_article_post( $post_details ) {

		$new_post['text'] = $this->strip_excess_blank_lines( $post_details['content'] ) . $this->get_url( $post_details );

		return $new_post;
	}

	/**
	 * Method for creating link(article) posts to Twitter.
	 *
	 * @since  8.7.0
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 *
	 * @return array
	 */
	private function twitter_text_post( $post_details ) {

		$new_post['text'] = $this->strip_excess_blank_lines( $post_details['content'] );

		return $new_post;
	}

	/**
	 * Method for creating media posts to Twitter.
	 *
	 * @since  8.7.0
	 * @access private
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param object $api Instance of twitter api wrapper.
	 *
	 * @return array
	 */
	private function twitter_media_post( $post_details, $api ) {

		$attachment_url = $post_details['post_image'];

		// if the post has no image but "Share as image post" is checked
		// share as an article post
		if ( empty( $attachment_url ) ) {
			$this->logger->info( 'No image set for post, but "Share as Image Post" is checked. Falling back to article post' );
			return $this->twitter_article_post( $post_details );
		}

		if ( class_exists( 'Jetpack_Photon' ) ) {
			// Disable Jetpack Photon filter.
			$photon_bypass = remove_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ) );
		}

		$media_path = '';

		if ( strpos( $post_details['mimetype']['type'], 'image' ) !== false ) {

			$passed_image_url_host = parse_url( $attachment_url )['host'];
			$admin_site_url_host = parse_url( get_site_url() )['host'];

			/** If this image is not local then lets download it locally to get its path  */
			if ( $passed_image_url_host === $admin_site_url_host ) {
				$media_path = $this->get_path_by_url( $post_details['post_image'], $post_details['mimetype'] );
			} else {
				$media_path = $this->rop_download_external_image( $attachment_url );
			}
		} else {

			$media_path = $this->get_path_by_url( $post_details['post_image'], $post_details['mimetype'] );

		}

		$upload_args = array(
			'media' => $media_path,
			'media_type' => $post_details['mimetype']['type'],
		);

		if ( ! empty( $photon_bypass ) && class_exists( 'Jetpack_Photon' ) ) {
			// Re-enable Jetpack Photon filter.
			add_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ), 10, 3 );
		}

		$status_check = false;

		// Overwrite media_type and category if is Video Post
		if ( strpos( $post_details['mimetype']['type'], 'video' ) !== false ) {
			$upload_args['media_type']     = $post_details['mimetype']['type'];
			$upload_args['media_category'] = 'tweet_video';
			$status_check                  = true;
		}

		// Overwrite media_type and category if is Gif Post
		if ( strpos( $post_details['mimetype']['type'], 'image/gif' ) !== false ) {
			$upload_args['media_type']     = $post_details['mimetype']['type'];
			$upload_args['media_category'] = 'tweet_gif';
			$status_check                  = true;
		}

		$this->logger->info( 'Before upload to twitter . ' . json_encode( $upload_args ) );
		$api->setTimeouts( 10, 60 );
		$api->setApiVersion( '1.1' );
		$media_response = $api->upload( 'media/upload', $upload_args, true );

		if ( isset( $media_response->media_id_string ) ) {

			$media_id = $media_response->media_id_string;

			$limit = 0;
			do {
				if ( ! $status_check ) {
					break;
				}
				$upload_status = $api->mediaStatus( $media_response->media_id_string );
				if ( $upload_status->processing_info->state === 'failed' ) {
					$media_id = '';
					break;
				}
				$media_id = $media_response->media_id_string;
				$this->logger->info( 'State : ' . json_encode( $upload_status ) );
				sleep( 3 );
				$limit ++;
			} while ( $upload_status->processing_info->state !== 'succeeded' && $limit <= 10 );

			if ( ! empty( $media_id ) ) {
				$new_post['media']['media_ids'][] = (string) $media_id;
			}
		} else {
			$this->logger->alert_error( sprintf( 'Can not upload media to twitter. Error: %s', json_encode( $media_response ) ) );
			$this->rop_get_error_docs( $media_response );
		}

		/** Delete this image if it was an external one downloaded temporarily. */
		if ( strpos( $media_path, ROP_TEMP_IMAGES ) !== false ) {
			wp_delete_file( $media_path );
		}

		$new_post['text'] = $this->strip_excess_blank_lines( $post_details['content'] ) . $this->get_url( $post_details );

		return $new_post;
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

		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		$share_via_rop_server = ! empty( $this->credentials['rop_auth_token'] );

		$transient_key = 'rop_twitter_limit_reset_' . wp_hash( $share_via_rop_server ? $this->credentials['rop_auth_token'] : $this->credentials['oauth_token'] );
		$limit_saved_msg = get_transient( $transient_key );

		if ( ! empty( $limit_saved_msg ) ) {
			$this->logger->alert_error( $limit_saved_msg );
			return false;
		}

		$new_post = array();
		$api      = null;
		if ( ! $share_via_rop_server ) {
			$this->set_api(
				$this->credentials['oauth_token'],
				$this->credentials['oauth_token_secret'],
				isset( $this->credentials['consumer_key'] ) ? $this->credentials['consumer_key'] : '',
				isset( $this->credentials['consumer_secret'] ) ? $this->credentials['consumer_secret'] : ''
			);
			$api = $this->get_api();
		}

		$post_id = $post_details['post_id'];
		$post_url = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];

		// Twitter link post
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$new_post = $this->twitter_article_post( $post_details );
		}

		// Twitter plain text post
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$new_post = $this->twitter_text_post( $post_details );
		}

		// Twitter media post
		if ( isset( $api ) && ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {
			$new_post = $this->twitter_media_post( $post_details, $api );
		} elseif ( ! isset( $api ) && ! empty( $share_as_image_post ) ) {
			$this->logger->info( __( 'Post with image is available only the local mode (Use my own API Keys). You can find the option when adding your X account to the plugin Dashboard.', 'tweet-old-post' ) . ' ' . __( ' Read more on:', 'tweet-old-post' ) . 'https://docs.revive.social/article/1908-how-to-solve-453-twitter-error-in-rop' );
		}

		if ( empty( $new_post ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'misc.no_post_data' ) );
			return false;
		}

		$model       = new Rop_Post_Format_Model;
		$post_format = $model->get_post_format( $post_details['account_id'] );

		$hashtags = $post_details['hashtags'];

		if ( ! empty( $post_format['hashtags_randomize'] ) && $post_format['hashtags_randomize'] ) {
			$hashtags = $this->shuffle_hashtags( $hashtags );
		}

		$new_post['text'] = $new_post['text'] . $hashtags;

		$this->logger->info( sprintf( 'Before twitter share: %s', json_encode( $new_post ) ) );

		$response         = array();
		$response_headers = array();
		$server_response  = array();

		if ( ! $share_via_rop_server ) {
			$api->setApiVersion( '2' ); // Note: Make sure to always set the correct API version before making a request.
			$response         = $api->post( 'tweets', $new_post, true );
			$response_headers = $api->getLastXHeaders();

			$this->logger->info( sprintf( '[X API] Response: %s', json_encode( $response_headers ) ) );

			$response = (array) $response;
			if ( ! empty( $response['data'] ) ) {
				$response['data'] = (array) $response['data'];
			}
		} else {
			$response = $this->rop_share_post_via_server( 'tw', $new_post, $this->credentials['rop_auth_token'] );

			$this->logger->info( sprintf( '[Revive Social] Response: %s', json_encode( $response_headers ) ) );

			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( $body, true );

			if ( ! empty( $body ) ) {

				if ( ! empty( $body['server'] ) ) {
					$server_response = $body['server'];

					// If we have a cached response, use it to apply the logic for rate limiting.
					if ( ! empty( $server_response['cached_response'] ) ) {
						$body = $server_response['cached_response'];
					}
				}

				if ( ! empty( $body['api_headers'] ) ) {
					$response_headers = $body['api_headers'];
				}

				if ( ! empty( $body['api_body'] ) ) {
					$response = $body['api_body'];
				}
			}
		}

		$limit_remaining          = isset( $response_headers['x_rate_limit_remaining'] ) ? $response_headers['x_rate_limit_remaining'] : false;
		$user_24h_limit_remaining = isset( $response_headers['x_user_limit_24hour_remaining'] ) ? $response_headers['x_user_limit_24hour_remaining'] : false;
		$app_24h_limit_remaining  = isset( $response_headers['x_app_limit_24hour_remaining'] ) ? $response_headers['x_app_limit_24hour_remaining'] : false;

		$reset_time_msg = '';
		$time_diff      = 0;
		$max_reset      = 0;
		$log_limit_msg  = __( 'X posting limit reached. Sharing on X will be skipped.', 'tweet-old-post' ) . ' (' . __( 'Learn more about X limits at', 'tweet-old-post' ) . ' https://developer.twitter.com/en/docs/twitter-api/rate-limits). ';

		if ( false !== $limit_remaining && $limit_remaining <= 0 ) {
			$reset = isset( $response_headers['x_rate_limit_reset'] ) ? $response_headers['x_rate_limit_reset'] : false; // in UTC epoch seconds

			if ( $reset ) {
				$time_diff = max( $time_diff, $reset - time() );
				$max_reset = max( $max_reset, $reset );

				$reset_time_msg .= '(' . __( '"x-rate-limit-remaining" will reset at:', 'tweet-old-post' ) . ' ' . date( 'Y-m-d H:i:s', $reset ) . ' UTC' . ')';
			}
		}

		if ( false !== $user_24h_limit_remaining && $user_24h_limit_remaining <= 0 ) {
			$reset = isset( $response_headers['x_user_limit_24hour_reset'] ) ? $response_headers['x_user_limit_24hour_reset'] : false;

			if ( $reset ) {
				$time_diff = max( $time_diff, $reset - time() );
				$max_reset = max( $max_reset, $reset );

				$reset_time_msg .= '(' . __( '"x-user-limit-24hour-remaining" will reset at:', 'tweet-old-post' ) . ' ' . date( 'Y-m-d H:i:s', $reset ) . ' UTC' . ')';
			}
		}

		if ( false !== $app_24h_limit_remaining && $app_24h_limit_remaining <= 0 ) {
			$reset = isset( $response_headers['x_app_limit_24hour_reset'] ) ? $response_headers['x_app_limit_24hour_reset'] : false;

			if ( $reset ) {
				$time_diff = max( $time_diff, $reset - time() );
				$max_reset = max( $max_reset, $reset );

				$reset_time_msg .= '(' . __( '"x-app-limit-24hour-remaining" will reset at:', 'tweet-old-post' ) . ' ' . date( 'Y-m-d H:i:s', $reset ) . ' UTC' . ')';
			}
		}

		if ( 0 < $time_diff ) {
			set_transient( $transient_key, $log_limit_msg . __( 'All limits will be fully reset by', 'tweet-old-post' ) . ': ' . date( 'Y-m-d H:i:s', $max_reset ) . ' ' . $reset_time_msg, $time_diff );
		}

		if ( isset( $response['data'] ) && ! empty( $response['data']['id'] ) ) {
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

		$msg   = 'Invalid response from X server.';
		$extra = $response;

		if ( isset( $response['detail'] ) ) {
			$msg = $response['detail'];
		}

		if ( ! empty( $server_response['message'] ) ) {
			$msg = $server_response['message'];

			if ( 'limit_reached' === $server_response['code'] ) {
				$extra = json_encode( $response_headers );
			}

			if ( empty( $extra ) ) {
				$extra = $server_response['code'];
			}
		}

		$this->logger->alert_error( sprintf( 'Error posting on X: %s | Additional info: %s', $msg, json_encode( $extra ) ) );
		$this->rop_get_error_docs( $response );

		return false;
	}

	/**
	 * This method will load and prepare the account data for Twitter user.
	 * Used in Rest Api.
	 *
	 * @since   8.4.0
	 * @access  public
	 *
	 * @param   array $account_data Twitter pages data.
	 *
	 * @return  bool
	 */
	public function add_account_with_app( $account_data ) {
		if ( ! $this->is_set_not_empty( $account_data, array( 'id' ) ) ) {
			return false;
		}
		$the_id       = $account_data['id'];
		$account_data = $account_data['pages'];

		$this->set_api( $account_data['credentials']['oauth_token'], $account_data['credentials']['oauth_token_secret'], $account_data['credentials']['consumer_key'], $account_data['credentials']['consumer_secret'] );
		$api = $this->get_api();

		$args = array(
			'oauth_token'        => $account_data['credentials']['oauth_token'],
			'oauth_token_secret' => $account_data['credentials']['oauth_token_secret'],
			'consumer_key'       => $account_data['credentials']['consumer_key'],
			'consumer_secret'    => $account_data['credentials']['consumer_secret'],
		);

		$this->set_credentials(
			array_intersect_key(
				$args,
				array(
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
		// Prepare the data that will be saved as new account added.
		$this->service = array(
			'id'                 => $response->id,
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'public_credentials' => array(
				'consumer_key'    => array(
					'name'    => 'API Key',
					'value'   => $account_data['credentials']['consumer_key'],
					'private' => false,
				),
				'consumer_secret' => array(
					'name'    => 'API secret key',
					'value'   => $account_data['credentials']['consumer_secret'],
					'private' => true,
				),
			),
			'available_accounts' => $this->get_users( $response ),
		);

		return true;
	}

	/**
	 * This method will load and prepare the account data for Twitter user using the info from the Rop server.
	 *
	 * @since   8.4.0
	 *
	 * @param   array $account_data Twitter pages data.
	 *
	 * @return  bool
	 */
	public function add_account_from_rop_server( $account_data ) {
		if ( ! $this->is_set_not_empty( $account_data, array( 'id' ) ) ) {
			return false;
		}
		$the_id       = $account_data['id'];
		$account_data = $account_data['pages'];

		$this->service = array(
			'id'                 => $the_id,
			'service'            => $this->service_name,
			'credentials'        => $account_data['credentials'],
			'public_credentials' => array(
				'consumer_key'    => array(
					'name'    => 'API Key',
					'value'   => '',
					'private' => false,
				),
				'consumer_secret' => array(
					'name'    => 'API secret key',
					'value'   => '',
					'private' => true,
				),
			),
			'available_accounts' => $this->get_users( (object) $account_data ),
		);

		return true;
	}

	/**
	 * Method to populate additional data.
	 *
	 * @since   8.5.13
	 * @access  public
	 * @return mixed
	 */
	public function populate_additional_data( $account ) {
		$account['link'] = sprintf( 'https://twitter.com/%s', $account['user'] );
		return $account;
	}

	/**
	 * Send the post to RoP server for sharing
	 *
	 * @param string $sharing_type Post sharing type.
	 * @param array  $post_data Post data.
	 * @param string $rop_auth_token RoP auth token.
	 * @return array|WP_Error
	 */
	public static function rop_share_post_via_server( $sharing_type = 'tw', $post_data = array(), $rop_auth_token = '' ) {
		$license_key = 'free';
		$plan_id     = 0;
		if ( 'valid' === apply_filters( 'product_rop_license_status', 'invalid' ) ) {
			$license_key = apply_filters( 'product_rop_license_key', 'free' );
			$plan_id     = apply_filters( 'product_rop_license_plan', 0 );
		}
		// Send API request.
		$response = wp_remote_post(
			ROP_POST_ON_X_API,
			apply_filters(
				'rop_post_sharing_api_args',
				array(
					'timeout' => 100,
					'body'    => array_merge(
						array(
							'sharing_type' => $sharing_type,
							'license'      => $license_key,
							'plan_id'      => $plan_id,
							'site_url'     => get_site_url(),
							'post_data'    => $post_data,
							'rop_auth_token' => $rop_auth_token,
						)
					),
				)
			)
		);

		return $response;
	}
}
