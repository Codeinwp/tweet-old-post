<?php
/**
 * The file that defines the Tumblr Service specifics.
 *
 * A class that is used to interact with Tumblr.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Tumblr_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Tumblr_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'tumblr';


	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Tumblr';
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
		if ( ! session_id() ) {
			session_start();
		}
		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_tumblr_credentials',
				'rop_tumblr_request_token',
			)
		)
		) {
			return false;
		}

		$credentials = $_SESSION['rop_tumblr_credentials'];
		$tmp_token   = $_SESSION['rop_tumblr_request_token'];

		$api            = $this->get_api( $credentials['consumer_key'], $credentials['consumer_secret'], $tmp_token['oauth_token'], $tmp_token['oauth_token_secret'] );
		$requestHandler = $api->getRequestHandler();
		$requestHandler->setBaseUrl( 'https://www.tumblr.com/' );

		if ( ! empty( $_GET['oauth_verifier'] ) ) {
			// exchange the verifier for the keys
			$verifier = trim( $_GET['oauth_verifier'] );

			$resp = $requestHandler->request( 'POST', 'oauth/access_token', array( 'oauth_verifier' => $verifier ) );

			$out         = (string) $resp->body;
			$accessToken = array();

			parse_str( $out, $accessToken );

			unset( $_SESSION['rop_tumblr_request_token'] );
			$_SESSION['rop_tumblr_token'] = $accessToken;
		}
		parent::authorize();
		// echo '<script>window.setTimeout("window.close()", 500);</script>';
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $consumer_key The Consumer Key. Default empty.
	 * @param   string $consumer_secret The Consumer Secret. Default empty.
	 * @param   string $token The Consumer Key. Default NULL.
	 * @param   string $token_secret The Consumer Secret. Default NULL.
	 *
	 * @return mixed
	 */
	public function get_api( $consumer_key = '', $consumer_secret = '', $token = null, $token_secret = null ) {
		if ( empty( $consumer_key ) ) {
			return $this->api;
		}
		if ( empty( $consumer_secret ) ) {
			return $this->api;
		}
		$this->set_api( $consumer_key, $consumer_secret, $token, $token_secret );

		return $this->api;
	}

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $consumer_key The Consumer Key. Default empty.
	 * @param   string $consumer_secret The Consumer Secret. Default empty.
	 * @param   string $token The Consumer Key. Default NULL.
	 * @param   string $token_secret The Consumer Secret. Default NULL.
	 *
	 * @return mixed
	 */
	public function set_api( $consumer_key = '', $consumer_secret = '', $token = null, $token_secret = null ) {
		if ( ! class_exists( 'Tumblr\API\Client' ) ) {
			return false;
		}
		if ( ! function_exists( 'curl_reset' ) ) {
			return false;
		}
		$this->api = new \Tumblr\API\Client( $this->strip_whitespace( $consumer_key ), $this->strip_whitespace( $consumer_secret ), $this->strip_whitespace( $token ), $this->strip_whitespace( $token_secret ) );

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
				'rop_tumblr_credentials',
				'rop_tumblr_token',
			)
		)
		) {
			return false;
		}
		if ( ! $this->is_set_not_empty(
			$_SESSION['rop_tumblr_token'],
			array(
				'oauth_token',
				'oauth_token_secret',
			)
		)
		) {
			return false;
		}
		$credentials                       = $_SESSION['rop_tumblr_credentials'];
		$credentials['oauth_token']        = $_SESSION['rop_tumblr_token']['oauth_token'];
		$credentials['oauth_token_secret'] = $_SESSION['rop_tumblr_token']['oauth_token_secret'];
		unset( $_SESSION['rop_tumblr_credentials'] );
		unset( $_SESSION['rop_tumblr_token'] );

		return $this->authenticate( $credentials );
	}

	/**
	 * Helper method for requesting user info.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return bool
	 */
	public function authenticate( $args ) {

		if ( ! $this->is_set_not_empty(
			$args,
			array(
				'oauth_token',
				'oauth_token_secret',
				'consumer_key',
				'consumer_secret',
			)
		)
		) {
			return false;
		}
		$api = $this->get_api( $args['consumer_key'], $args['consumer_secret'], $args['oauth_token'], $args['oauth_token_secret'] );

		if ( empty( $api ) ) {
			return false;
		}
		$api->getRequestHandler()->setBaseUrl( 'https://api.tumblr.com/' );
		$profile = $api->getUserInfo();
		if ( ! isset( $profile->user->name ) ) {
			return false;
		}
		$this->service = array(
			'id'                 => $profile->user->name,
			'service'            => $this->service_name,
			'credentials'        => $args,
			'public_credentials' => array(
				'consumer_key'    => array(
					'name'    => 'Consumer Key',
					'value'   => $args['consumer_key'],
					'private' => false,
				),
				'consumer_secret' => array(
					'name'    => 'Consumer Secret',
					'value'   => $args['consumer_secret'],
					'private' => true,
				),
			),
			'available_accounts' => $this->get_users( $profile->user->blogs ),
		);

		return true;
	}

	/**
	 * Utility method to retrieve users from the Tumblr account.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   object $data Response data from Tumblr.
	 *
	 * @return array
	 */
	private function get_users( $data = null ) {
		$users = array();

		foreach ( $data as $page ) {
			$img = '';
			if ( isset( $page->name ) ) {
				$img = 'https://api.tumblr.com/v2/blog/' . $page->name . '.tumblr.com/avatar';
			}
			$user_details = wp_parse_args(
				array(
					'id'      => $page->name,
					'user'    => $this->normalize_string( $page->title ),
					'account' => $this->normalize_string( $page->name ),
					'img'     => $img,
				),
				$this->user_default
			);
			$users[]      = $user_details;
		}

		return $users;
	}


	/**
	 * Utility method to retrieve users from the Tumblr account connected using the RS app.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.5.7
	 * @access  public
	 *
	 * @param   object $data Response data from Tumblr.
	 *
	 * @return array
	 */
	private function get_users_rs_app( $data = null ) {
		$users = array();

		foreach ( $data as $page ) {

			$user_details = wp_parse_args(
				array(
					'id'      => $page['id'],
					'user'    => $this->normalize_string( $page['account'] ),
					'account' => $this->normalize_string( $page['user'] ),
					'img'     => apply_filters( 'rop_custom_tmblr_avatar', $page['img'] ),
				),
				$this->user_default
			);
			$users[]      = $user_details;
		}

		return $users;
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
		// @codeCoverageIgnoreStart
		if ( ! session_id() ) {
			session_start();
		}
		// @codeCoverageIgnoreEnd
		$_SESSION['rop_tumblr_credentials'] = $credentials;
		$this->set_api( $credentials['consumer_key'], $credentials['consumer_secret'] );
		$request_token = $this->request_api_token();

		if ( empty( $request_token ) ) {
			return $this->get_legacy_url();
		}

		$url = 'https://www.tumblr.com/oauth/authorize?oauth_token=' . $request_token['oauth_token'];

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

		$api            = $this->get_api();
		$requestHandler = $api->getRequestHandler();
		$requestHandler->setBaseUrl( 'https://www.tumblr.com/' );

		$resp = $requestHandler->request(
			'POST',
			'oauth/request_token',
			array(
				'oauth_callback' => $this->get_endpoint_url( 'authorize' ),
			)
		);

		$result = (string) $resp->body;
		if ( 401 === absint( $resp->status ) ) {
			$this->logger->alert_error( 'Error connecting Tumblr: The Consumer Key/Consumer Secret is not valid. Please ensure that they\'re correct and try again' );

			return '';
		}

		parse_str( $result, $request_token );

		$_SESSION['rop_tumblr_request_token'] = $request_token;

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

		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		$api = $this->get_api( $args['credentials']['consumer_key'], $args['credentials']['consumer_secret'], $args['credentials']['oauth_token'], $args['credentials']['oauth_token_secret'] );

		$post_type = new Rop_Posts_Selector_Model();
		$post_id   = $post_details['post_id'];

		$model       = new Rop_Post_Format_Model;
		$post_format = $model->get_post_format( $post_details['account_id'] );

		$hashtags = $post_details['hashtags'];

		if ( ! empty( $post_format['hashtags_randomize'] ) && $post_format['hashtags_randomize'] ) {
			$hashtags = $this->shuffle_hashtags( $hashtags );
		}

		// Tumblr creates hashtags differently
		$hashtags = preg_replace( array( '/ /', '/#/' ), array( '', ',' ), $hashtags );
		$hashtags = ltrim( $hashtags, ',' );

		// Link post
		if ( ! empty( $post_details['post_url'] ) && empty( $post_details['post_with_image'] ) ) {

			$thumbnail = get_the_post_thumbnail_url( $post_id, 'large' );

			// If thumbnail parameter is set but empty, tumblr would return an error. So we prevent this here.
			if ( ! empty( $thumbnail ) ) {
				$new_post['thumbnail'] = $thumbnail;
			}

			$new_post['type']        = 'link';
			$new_post['url']         = trim( $this->get_url( $post_details ) );
			$new_post['title']       = html_entity_decode( get_the_title( $post_details['post_id'] ), ENT_QUOTES );
			$new_post['description'] = $this->strip_excess_blank_lines( html_entity_decode( $post_details['content'], ENT_QUOTES ) );
			$new_post['author']      = $this->get_author( $post_id );
			$new_post['tags']        = $hashtags;
		}

		// Text post
		if ( empty( $post_details['post_url'] ) && empty( $post_details['post_with_image'] ) ) {
			$new_post['type'] = 'text';
			$new_post['body'] = $this->strip_excess_blank_lines( html_entity_decode( $post_details['content'], ENT_QUOTES ) );
			$new_post['tags'] = $hashtags;
		}

		// Photo post
		if ( ! empty( $post_details['post_with_image'] ) && strpos( $post_details['mimetype']['type'], 'image' ) !== false ) {
			$new_post['type']       = 'photo';
			$new_post['source_url'] = esc_url( get_site_url() );

			// get image path
			$image_source = $this->get_path_by_url( $post_details['post_image'], $post_details['mimetype'] );
			// If the URL is returned instead of PATH, use the url.
			if ( $this->is_remote_file( $image_source ) ) {
				$new_post['data'] = $post_details['post_image'];
			} else {
				// If the file can't be read, it returns the normal path back.
				$get_base64 = $this->convert_image_to_base64( $image_source );
				// We need to check if it was encoded or not.
				if ( $get_base64 === $image_source ) {
					// This is normal path, but Tumblr API doesn't seem to have support for image path
					// Fallback to image URL.
					$new_post['data'] = $post_details['post_image'];

				} else { // This is base 64
					$new_post['data64'] = $get_base64;
				}
			}

			$new_post['caption'] = $this->strip_excess_blank_lines( html_entity_decode( $post_details['content'], ENT_QUOTES ) ) . ' ' . trim( $this->get_url( $post_details ) );
			$new_post['tags']    = $hashtags;
		}

		// Video post| HTML5 video doesn't support all our initially set video formats
		if ( ! empty( $post_details['post_image'] ) && strpos( $post_details['mimetype']['type'], 'video' ) !== false ) {
			$new_post['type']       = 'video';
			$new_post['source_url'] = esc_url( get_site_url() );
			$new_post['embed']      = '<video width="100%" height="auto" controls>
  																 <source src="' . $post_details['post_image'] . '" type="video/mp4">
																	 Your browser does not support the video tag.
																	 </video>';
			$new_post['caption']    = $this->strip_excess_blank_lines( html_entity_decode( $post_details['content'], ENT_QUOTES ) ) . ' ' . trim( $this->get_url( $post_details ) );
			$new_post['tags']       = $hashtags;
		}

		try {

			$api->createPost( $args['id'] . '.tumblr.com', $new_post );

			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on %s ',
					html_entity_decode( get_the_title( $post_id ) ),
					$args['user'],
					$post_details['service']
				)
			);

			return true;

		} catch ( Exception $exception ) {
			$this->logger->alert_error( 'Posting failed to Tumblr. Error: ' . $exception->getMessage() );
			$this->rop_get_error_docs( $exception->getMessage() );

			return false;
		}

	}

	/**
	 * Method for getting post author.
	 *
	 * @since   8.1.0
	 * @access  private
	 *
	 * @param   int $post_id The post id.
	 *
	 * @return string
	 */
	private function get_author( $post_id ) {
		$author_id = get_post_field( 'post_author', $post_id );
		$author    = get_the_author_meta( 'display_name', $author_id );

		$author = ( $author !== 'admin' ) ? $author : '';

		// allow users to not include author in shared posts
		return apply_filters( 'rop_tumblr_post_author', $author );
	}

	/**
	 * Method used to decide whether or not to show Tumblr button
	 *
	 * @since   8.5.6
	 * @access  public
	 *
	 * @return  bool
	 */
	public function rop_show_tmblr_app_btn() {

		$installed_at_version = get_option( 'rop_first_install_version' );

		if ( empty( $installed_at_version ) ) {
			return false;
		}

		if ( version_compare( $installed_at_version, '8.5.0', '>=' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * This method will load and prepare the account data for Tumblr user.
	 * Used in Rest Api.
	 *
	 * @since   8.5.7
	 * @access  public
	 *
	 * @param   array $account_data Tumblr pages data.
	 *
	 * @return  bool
	 */
	public function add_account_with_app( $account_data ) {
		if ( ! $this->is_set_not_empty( $account_data, array( 'id' ) ) ) {
			return false;
		}

		$the_id         = unserialize( base64_decode( $account_data['id'] ) );
		$accounts_array = unserialize( base64_decode( $account_data['pages'] ) );

		$args = array(
			'oauth_token'        => $accounts_array[0]['credentials']['oauth_token'],
			'oauth_token_secret' => $accounts_array[0]['credentials']['oauth_token_secret'],
			'consumer_key'       => $accounts_array[0]['credentials']['consumer_key'],
			'consumer_secret'    => $accounts_array[0]['credentials']['consumer_secret'],
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

		// Prepare the data that will be saved as new account added.
		$this->service = array(
			'id'                 => $the_id,
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'public_credentials' => array(
				'consumer_key'    => array(
					'name'    => 'API Key',
					'value'   => $accounts_array[0]['credentials']['consumer_key'],
					'private' => false,
				),
				'consumer_secret' => array(
					'name'    => 'API secret key',
					'value'   => $accounts_array[0]['credentials']['consumer_secret'],
					'private' => true,
				),
			),
			'available_accounts' => $this->get_users_rs_app( $accounts_array ),
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
		$account['link'] = sprintf( 'https://tumblr.com/blog/%s', $account['id'] );
		return $account;
	}

}
