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

		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_twitter_request_token',
			)
		) ) {
			return false;
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
			'oauth/authorize',
			[
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

		$post_id = $post_details['post_id'];
		$message = $this->strip_excess_blank_lines( $post_details['content'] );

		if ( ! empty( $post_details['post_image'] ) ) {

			$upload_args  = [
				'media' => $this->get_path_by_url( $post_details['post_image'], $post_details['mimetype'] ),
			];
			$status_check = false;

			if ( strpos( $post_details['mimetype']['type'], 'video' ) !== false ) {
				$upload_args['media_type']     = $post_details['mimetype']['type'];
				$upload_args['media_category'] = 'tweet_video';
				$status_check                  = true;
			}

			if ( strpos( $post_details['mimetype']['type'], 'image/gif' ) !== false ) {
				$upload_args['media_type']     = $post_details['mimetype']['type'];
				$upload_args['media_category'] = 'tweet_gif';
				$status_check                  = true;
			}

			$this->logger->info( 'Before upload to twitter . ' . json_encode( $upload_args ) );
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
					$new_post['media_ids'] = $media_id;
				}
			} else {
				$this->logger->alert_error( sprintf( 'Can not upload media to twitter. Error: %s', json_encode( $media_response ) ) );
			}
		}

		$new_post['status'] = $message . $this->get_url( $post_details ) . $post_details['hashtags'];

		$this->logger->info( sprintf( 'Before twitter share: %s', json_encode( $new_post ) ) );

		$response = $api->post( 'statuses/update', $new_post );
		if ( isset( $response->id ) ) {
			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on %s ',
					html_entity_decode( get_the_title( $post_id ) ),
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

	/**
	 * Get Image file path if exists, return default image_url if not.
	 *
	 * Used where file_get_contents might not work with urls, we provide the file path.
	 *
	 * @param string $image_url Image url.
	 *
	 * @return string Image path.
	 */
	private function get_path_by_url( $image_url, $mimetype ) {

		$dir = wp_upload_dir();

		if ( false === strpos( $image_url, $dir['baseurl'] . '/' ) ) {
			return $image_url;
		}

		$file     = basename( $image_url );
		$query    = array(
			'post_type'      => 'attachment',
			'fields'         => 'ids',
			'posts_per_page' => '20',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'     => '_wp_attached_file',
					'value'   => $file,
					'compare' => 'LIKE',
				),
			),
		);
		$ids      = get_posts( $query );
		$id_found = false;
		if ( strpos( $mimetype['type'], 'video' ) !== false ) {
			if ( empty( $ids ) ) {
				return $image_url;
			}

			return get_attached_file( reset( $ids ) );
		}
		if ( ! empty( $ids ) ) {

			foreach ( $ids as $id ) {
				if ( $image_url === array_shift( wp_get_attachment_image_src( $id, 'full' ) ) ) {
					$id_found = $id;
					break;
				}
			}
		}
		if ( $id_found === false ) {
			$query['meta_query'][0]['key'] = '_wp_attachment_metadata';

			// query attachments again
			$ids = get_posts( $query );

			if ( empty( $ids ) ) {
				return $image_url;
			}

			foreach ( $ids as $id ) {

				$meta = wp_get_attachment_metadata( $id );

				foreach ( $meta['sizes'] as $size => $values ) {

					if ( $values['file'] === $file && $image_url === array_shift( wp_get_attachment_image_src( $id, $size ) ) ) {
						$id_found = $id;
						break;
					}
				}
				if ( $id_found === false ) {
					break;
				}
			}
		}
		if ( $id_found === false ) {
			return $image_url;
		}
		$path = get_attached_file( $id_found );
		if ( empty( $path ) ) {
			return $image_url;
		}

		return $path;
	}

}
