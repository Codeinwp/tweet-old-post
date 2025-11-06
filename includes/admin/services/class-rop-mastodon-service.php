<?php
/**
 * The file that defines the Mastodon Service specifics.
 *
 * A class that is used to interact with Mastodon.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      9.1.3
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Mastodon_Service
 *
 * @since   9.1.3
 * @link    https://themeisle.com/
 */
class Rop_Mastodon_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   9.1.3
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'mastodon';

	/**
	 * Holds the Mastodon Domain.
	 *
	 * @since   9.1.3
	 * @access  private
	 * @var     string $domain The Mastodon domain.
	 */
	private $domain = '';

	/**
	 * Holds the scopes.
	 *
	 * @since   9.1.3
	 * @access  private
	 * @var     string $scopes API scopes.
	 */
	private $scopes = '';

	/**
	 * Holds the Mastodon APP Consumer Key.
	 *
	 * @since   9.1.3
	 * @access  private
	 * @var     string $consumer_key The Mastodon APP Consumer Key.
	 */
	private $consumer_key = '';

	/**
	 * Holds the Mastodon APP Consumer Secret.
	 *
	 * @since   9.1.3
	 * @access  private
	 * @var     string $consumer_secret The Mastodon APP Consumer Secret.
	 */
	private $consumer_secret = '';


	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   9.1.3
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Mastodon';
		$this->scopes       = apply_filters( 'rop_mastodon_api_scopes', 'read write push' );
	}

	/**
	 * Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since   9.1.3
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
	 * @since   9.1.3
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
				'rop_mastodon_credentials',
			)
		) ) {
			return false;
		}

		// Safely read session data and GET param.
		$request_token = isset( $_SESSION['rop_mastodon_credentials'] ) && is_array( $_SESSION['rop_mastodon_credentials'] ) ? $_SESSION['rop_mastodon_credentials'] : array();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$code          = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';

		$md_domain       = isset( $request_token['md_domain'] ) ? $request_token['md_domain'] : '';
		$consumer_key    = isset( $request_token['consumer_key'] ) ? $request_token['consumer_key'] : '';
		$consumer_secret = isset( $request_token['consumer_secret'] ) ? $request_token['consumer_secret'] : '';

		$access_token = $this->request_api_access_token( $code, $md_domain, $consumer_key, $consumer_secret );
		if ( ! empty( $access_token ) ) {
			$_SESSION['rop_mastodon_oauth_token'] = $access_token;
		}

		parent::authorize();
	}

	/**
	 * Abstract function, not in Use. Method to retrieve the api object.
	 *
	 * @since  9.1.3
	 * @access public
	 *
	 * @param string $app_id The APP ID. Default empty.
	 * @param string $secret The APP Secret. Default empty.
	 *
	 * @return null abstract method not used for this service specifically.
	 */
	public function get_api( $app_id = '', $secret = '' ) {
		return;
	}

	/**
	 * Abstract function, not in Use. Method to define the api.
	 *
	 * @since  9.1.3
	 * @access public
	 *
	 * @param string $app_id The APP ID. Default empty.
	 * @param string $secret The APP Secret. Default empty.
	 *
	 * @return mixed
	 */
	public function set_api( $app_id = '', $secret = '' ) {
		return;
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
				'rop_mastodon_oauth_token',
				'rop_mastodon_credentials',
			)
		) ) {
			return false;
		}
		// Safely read session data.
		$token = isset( $_SESSION['rop_mastodon_oauth_token'] ) ? $_SESSION['rop_mastodon_oauth_token'] : '';
		$creds = isset( $_SESSION['rop_mastodon_credentials'] ) && is_array( $_SESSION['rop_mastodon_credentials'] ) ? $_SESSION['rop_mastodon_credentials'] : array();

		$consumer_key    = isset( $creds['consumer_key'] ) ? $creds['consumer_key'] : '';
		$consumer_secret = isset( $creds['consumer_secret'] ) ? $creds['consumer_secret'] : '';
		$domain          = isset( $creds['md_domain'] ) ? $creds['md_domain'] : '';

		unset( $_SESSION['rop_mastodon_oauth_token'] );
		unset( $_SESSION['rop_mastodon_request_token'] );
		unset( $_SESSION['rop_mastodon_credentials'] );
		return $this->authenticate(
			array(
				'oauth_token'     => $token,
				'consumer_key'    => $consumer_key,
				'consumer_secret' => $consumer_secret,
				'domain'          => $domain,
			)
		);
	}

	/**
	 * Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   9.1.3
	 * @access  public
	 * @return bool
	 */
	public function authenticate( $args = array() ) {

		if ( ! $this->is_set_not_empty(
			$args,
			array(
				'oauth_token',
				'domain',
				'consumer_key',
				'consumer_secret',
			)
		) ) {
			return false;
		}
		$this->consumer_secret = $args['consumer_secret'];
		$this->consumer_key    = $args['consumer_key'];
		$this->domain          = $args['domain'];
		$oauth_token           = $args['oauth_token'];

		$this->set_credentials(
			array_intersect_key(
				$args,
				array(
					'oauth_token'     => '',
					'domain'          => '',
					'consumer_key'    => '',
					'consumer_secret' => '',
				)
			)
		);
		$data = $this->get_user_accounts();
		if ( ! isset( $data->id ) ) {
			return false;
		}
		$this->service = array(
			'id'                 => $data->id,
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'public_credentials' => array(
				'mastodon' => array(
					'name'    => 'Mastodon',
					'value'   => $this->domain,
					'private' => false,
				),
			),
			'available_accounts' => $this->get_users( $data ),
		);

		return true;
	}

	/**
	 * Get user account data.
	 *
	 * @return false|object
	 */
	public function get_user_accounts() {
		if ( empty( $this->credentials['oauth_token'] ) || empty( $this->credentials['domain'] ) ) {
			return false;
		}
		$api_url  = $this->get_api_endpoint( $this->credentials['domain'], 'api/v1/accounts/verify_credentials' );
		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'timeout'       => 120,
					'Authorization' => 'Bearer ' . $this->credentials['oauth_token'],
					'Accept'        => 'application/json',
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			$this->logger->alert_error( 'Mastodon accounts verify credentials API Error: ' . $response->get_error_message() );
			return false;
		}
		$response = wp_remote_retrieve_body( $response );
		$this->logger->info( 'Mastodon accounts verify credentials API response: ' . print_r( $response, true ) );
		$response = json_decode( $response );

		if ( ! isset( $response->id ) ) {
			$this->logger->alert_error( 'Mastodon accounts verify credentials API Error: ' . print_r( $response, true ) );
			return false;
		}
		return $response;
	}

	/**
	 * Method to register credentials for the service.
	 *
	 * @since   9.1.3
	 * @access  public
	 *
	 * @param   array $args The credentials array.
	 */
	public function set_credentials( $args ) {
		$this->credentials = $args;
	}

	/**
	 * Utility method to retrieve users from the Mastodon account.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   9.1.3
	 * @access  public
	 *
	 * @param   object $data Response data from Mastodon.
	 *
	 * @return array
	 */
	private function get_users( $data = null ) {
		// assign default values to variable.
		$user = $this->user_default;

		$img = '';
		if ( isset( $data->avatar ) ) {
			$img = $data->avatar;
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
		$user['account'] = $this->normalize_string( $data->display_name );
		$user['user']    = '@' . $this->normalize_string( $data->username );
		$user['img']     = apply_filters( 'rop_custom_mastodon_avatar', $img );
		$user['service'] = $this->service_name;
		$user['link']    = $data->url;

		return array( $this->get_service_id() . '_' . $user['id'] => $user );
	}

	/**
	 * Returns information for the current service.
	 *
	 * @since   9.1.3
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
	 * @since   9.1.3
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
		if ( ! empty( $credentials['domain'] ) ) {
			$this->domain = trim( $credentials['domain'] );
		}
		$_SESSION['rop_mastodon_credentials'] = $credentials;

		$oauth_url = $this->request_api_token();

		if ( empty( $oauth_url ) ) {
			return $this->get_legacy_url();
		}
		return $oauth_url;
	}

	/**
	 * Get API endpoint.
	 *
	 * @param string $domain Domain.
	 * @param string $path Base path.
	 * @return string
	 */
	private function get_api_endpoint( $domain, $path ) {
		if ( ! wp_parse_url( $domain, PHP_URL_HOST ) ) {
			$domain = esc_url( $domain );
		}

		$domain = rtrim( $domain, '/' );
		return "$domain/$path";
	}

	/**
	 * Method to request a auto code from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   9.1.3
	 * @access  protected
	 * @return mixed
	 */
	public function request_api_token() {
		if ( ! session_id() ) {
			session_start();
		}
		if ( ! empty( $_SESSION['rop_mastodon_credentials']['md_domain'] ) ) {
			$api_url  = $this->get_api_endpoint( $_SESSION['rop_mastodon_credentials']['md_domain'], 'api/v1/apps' );
			$response = wp_remote_post(
				$api_url,
				array(
					'body'    => array(
						'client_name'   => 'Revive Social',
						'redirect_uris' => $this->get_legacy_url(),
						'scopes'        => $this->scopes,
						'website'       => site_url(),
					),
					'headers' => array(
						'timeout' => 120,
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				$this->logger->alert_error( 'Error creating APP to Mastodon, response: ' . $response->get_error_message() );
				return false;
			}
			$body = wp_remote_retrieve_body( $response );
			$this->logger->info( 'APP created to Mastodon, response: ' . print_r( $body, true ) );
			$body = json_decode( $body );

			if ( empty( $body->client_id ) || empty( $body->client_secret ) ) {
				return false;
			}

			$_SESSION['rop_mastodon_credentials']['consumer_key']    = $this->str_encrypt( $body->client_id );
			$_SESSION['rop_mastodon_credentials']['consumer_secret'] = $this->str_encrypt( $body->client_secret );

			return $this->get_oauth_url();
		}

		return false;
	}

	/**
	 * Encrypts a given string using AES-256-CBC encryption.
	 *
	 * This function uses `SECURE_AUTH_SALT` as the encryption key and generates
	 * an Initialization Vector (IV) based on a SHA-256 hash of the salt.
	 *
	 * @param string $str The string to encrypt.
	 * @return string The encrypted string in base64 format, or an empty string if input is empty.
	 */
	private function str_encrypt( $str = '' ) {
		if ( empty( $str ) ) {
			return '';
		}
		$iv = substr( hash( 'sha256', SECURE_AUTH_SALT ), 0, 16 );
		return openssl_encrypt( $str, 'aes-256-cbc', SECURE_AUTH_SALT, 0, $iv );
	}

	/**
	 * Decrypts an AES-256-CBC encrypted string.
	 *
	 * This function decrypts a given string that was encrypted using `str_encrypt()`.
	 * It uses `SECURE_AUTH_SALT` as the encryption key and derives an Initialization Vector (IV)
	 * from a SHA-256 hash of the salt.
	 *
	 * @param string $str The encrypted string in base64 format.
	 * @return string The decrypted plain text string, or an empty string if input is empty or decryption fails.
	 */
	private function str_decrypt( $str = '' ) {
		if ( empty( $str ) ) {
			return '';
		}
		$iv = substr( hash( 'sha256', SECURE_AUTH_SALT ), 0, 16 );
		return openssl_decrypt( $str, 'aes-256-cbc', SECURE_AUTH_SALT, 0, $iv );
	}

	/**
	 * Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   9.1.3
	 * @access  protected
	 *
	 * @param string $code Auto code.
	 * @param string $domain Domain.
	 * @param string $consumer_key Consumer key.
	 * @param string $consumer_secret Consumer secret.
	 *
	 * @return mixed
	 */
	public function request_api_access_token( $code = '', $domain = '', $consumer_key = '', $consumer_secret = '' ) {
		if (
			empty( $code ) ||
			empty( $domain ) ||
			empty( $consumer_key ) ||
			empty( $consumer_secret ) ) {
			return '';
		}
		$api_url  = $this->get_api_endpoint( $domain, 'oauth/token' );
		$response = wp_remote_post(
			$api_url,
			array(
				'body'    => array(
					'client_id'     => $this->str_decrypt( $consumer_key ),
					'client_secret' => $this->str_decrypt( $consumer_secret ),
					'grant_type'    => 'authorization_code',
					'redirect_uri'  => $this->get_legacy_url(),
					'scopes'        => $this->scopes,
					'code'          => $code,
				),
				'headers' => array(
					'timeout' => 120,
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			$this->logger->alert_error( 'Mastodon oauth token response Error: ' . $response->get_error_message() );
			return '';
		}
		$body = wp_remote_retrieve_body( $response );
		$this->logger->info( 'Mastodon token created response: ' . print_r( $body, true ) );
		$body = json_decode( $body );
		if ( ! empty( $body->error ) ) {
			$this->logger->alert_error( 'Mastodon oauth token response Error: ' . $body->error_description );
			return '';
		}

		return ! empty( $body->access_token ) ? $body->access_token : '';
	}

	/**
	 * Create oauth url.
	 *
	 * @return string
	 */
	private function get_oauth_url() {
		if ( ! session_id() ) {
			session_start();
		}
		$consumer_key = isset( $_SESSION['rop_mastodon_credentials']['consumer_key'] ) ? $this->str_decrypt( $_SESSION['rop_mastodon_credentials']['consumer_key'] ) : '';
		$domain       = isset( $_SESSION['rop_mastodon_credentials']['md_domain'] ) ? $_SESSION['rop_mastodon_credentials']['md_domain'] : 'mastodon.social';
		if ( empty( $consumer_key ) ) {
			return false;
		}
		$url    = $this->get_legacy_url();
		$scopes = $this->scopes;

		$auth_url = $this->get_api_endpoint( $domain, 'oauth/authorize' );
		return add_query_arg(
			array(
				'client_id'     => $consumer_key,
				'redirect_uri'  => $url,
				'scope'         => $scopes,
				'response_type' => 'code',
				'state'         => 'mastodon',
			),
			$auth_url
		);
	}

	/**
	 * Method for creating link(article) posts to Mastodon.
	 *
	 * @since  8.7.0
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 *
	 * @return array
	 */
	private function mastodon_article_post( $post_details ) {

		$new_post['status'] = $this->strip_excess_blank_lines( $post_details['content'] );
		if ( empty( $this->share_link_text ) ) {
			$new_post['status'] .= $this->get_url( $post_details );
		}
		$new_post['visibility'] = 'public';
		return $new_post;
	}

	/**
	 * Method for creating link(article) posts to Mastodon.
	 *
	 * @since  8.7.0
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 *
	 * @return array
	 */
	private function mastodon_text_post( $post_details ) {

		$new_post['status']     = $this->strip_excess_blank_lines( $post_details['content'] );
		$new_post['visibility'] = 'public';
		return $new_post;
	}

	/**
	 * Method for creating media posts on Telegram.
	 *
	 * @since  9.1.3
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 *
	 * @return array $new_post The post contents
	 */
	private function mastodon_media_post( $post_details ) {

		$attachment_url = $post_details['post_image'];
		// If the post has no image but "Share as image post" is checked
		// share as an article post.
		if ( empty( $attachment_url ) ) {
			$this->logger->info( 'No image set for post, but "Share as Image Post" is checked. Falling back to article post' );
			return $this->mastodon_article_post( $post_details );
		}

		$passed_image_url_host = parse_url( $attachment_url )['host'];
		$admin_site_url_host   = parse_url( get_site_url() )['host'];

		/** If this image is not local then lets download it locally to get its path  */
		if ( ( $passed_image_url_host === $admin_site_url_host ) && strpos( $post_details['mimetype']['type'], 'video' ) !== true ) {
			$attachment_path = $this->get_path_by_url( $attachment_url, $post_details['mimetype'] );
		} else {
			$attachment_path = $this->rop_download_external_image( $attachment_url );
		}

		$new_post = array(
			'status'     => $post_details['content'],
			'visibility' => 'public',
		);

		if ( empty( $this->share_link_text ) ) {
			$new_post['status'] .= $this->get_url( $post_details );
		}

		$media_id = $this->upload_media( $attachment_path );
		if ( $media_id > 0 ) {
			$new_post['media_ids'] = array( $media_id );
		}
		return $new_post;
	}

	/**
	 * Media upload to Mastodon
	 *
	 * @param string $media_path Media path.
	 * @return int
	 */
	private function upload_media( $media_path ) {
		if ( empty( $media_path ) ) {
			return 0;
		}
		if ( empty( $this->credentials['oauth_token'] ) || empty( $this->credentials['domain'] ) ) {
			return 0;
		}

		if ( ! function_exists( 'curl_file_create' ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$base_name = basename( $media_path );
		$file_info = wp_check_filetype( $media_path );
		$type      = isset( $file_info['type'] ) ? $file_info['type'] : '';

		$file_data = $wp_filesystem->get_contents( $media_path );
		$api_url   = $this->get_api_endpoint( $this->credentials['domain'], 'api/v2/media' );

		$boundary = md5( time() );
		$eol      = "\r\n";

		$body = '--' . $boundary . $eol;

		// The actual (binary) image data.
		$body .= 'Content-Disposition: form-data; name="file"; filename="' . basename( $media_path ) . '"' . $eol;
		$body .= 'Content-Type: ' . $this->get_content_type( $media_path ) . $eol . $eol;
		$body .= $file_data . $eol; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$body .= '--' . $boundary . '--'; // Note the extra two hyphens at the end.

		$response = wp_safe_remote_post(
			$api_url,
			array(
				'body'                => $body,
				'headers'             => array(
					'timeout'       => 120,
					'Authorization' => 'Bearer ' . $this->credentials['oauth_token'],
					'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
				),
				'data_format'         => 'body',
				'limit_response_size' => 1048576,
			)
		);
		if ( is_wp_error( $response ) ) {
			$this->logger->alert_error( 'Mastodon media upload API Error: ' . $response->get_error_message() );
			return 0;
		}
		$response = wp_remote_retrieve_body( $response );
		$this->logger->info( 'Mastodon media upload API response: ' . print_r( $response, true ) );
		$response = json_decode( $response );

		if ( ! isset( $response->id ) ) {
			$this->logger->alert_error( 'Mastodon media upload API Error: ' . print_r( $response, true ) );
			return 0;
		}
		return $response->id;
	}

	/**
	 * Returns a MIME content type for a certain file.
	 *
	 * @param  string $file_path File path.
	 * @return string            MIME type.
	 */
	private function get_content_type( $file_path ) {
		if ( function_exists( 'mime_content_type' ) ) {
			$result = mime_content_type( $file_path );

			if ( is_string( $result ) ) {
				return $result;
			}
		}

		if ( function_exists( 'finfo_open' ) && function_exists( 'finfo_file' ) ) {
			$finfo  = finfo_open( FILEINFO_MIME_TYPE );
			$result = finfo_file( $finfo, $file_path );

			if ( is_string( $result ) ) {
				return $result;
			}
		}

		$ext = pathinfo( $file_path, PATHINFO_EXTENSION );
		if ( ! empty( $ext ) ) {
			$mime_types = wp_get_mime_types();
			foreach ( $mime_types as $key => $value ) {
				if ( in_array( $ext, explode( '|', $key ), true ) ) {
					return $value;
				}
			}
		}

		return 'application/octet-stream';
	}

	/**
	 * Method for publishing with Mastodon service.
	 *
	 * @since   9.1.3
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

		$new_post = array();

		$post_id             = $post_details['post_id'];
		$post_url            = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];

		$model       = new Rop_Post_Format_Model();
		$post_format = $model->get_post_format( $post_details['account_id'] );

		if ( ! empty( $post_format['share_link_in_comment'] ) && ! empty( $post_format['share_link_text'] ) ) {
			$this->share_link_text = str_replace( '{link}', self::get_url( $post_details ), $post_format['share_link_text'] );
		}

		// Mastodon link post.
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$new_post = $this->mastodon_article_post( $post_details );
		}

		// Mastodon plain text post.
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$new_post = $this->mastodon_text_post( $post_details );
		}

		// Mastodon media post.
		if ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {
			$new_post = $this->mastodon_media_post( $post_details );
		}

		if ( empty( $new_post ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'misc.no_post_data' ) );
			return false;
		}

		$model       = new Rop_Post_Format_Model();
		$post_format = $model->get_post_format( $post_details['account_id'] );

		$hashtags = $post_details['hashtags'];

		if ( ! empty( $post_format['hashtags_randomize'] ) && $post_format['hashtags_randomize'] ) {
			$hashtags = $this->shuffle_hashtags( $hashtags );
		}

		$new_post['status'] = $new_post['status'] . $hashtags;

		$this->logger->info( sprintf( 'Before Mastodon share: %s', json_encode( $new_post ) ) );

		$response = $this->request_new_post( $new_post );

		if ( isset( $response->id ) ) {
			// Create the first comment if the share link text is not empty.
			if ( ! empty( $this->share_link_text ) ) {
				$create_reply = $this->request_new_post(
					array(
						'status'         => $this->share_link_text,
						'in_reply_to_id' => $response->id,
					)
				);
				$this->logger->info( sprintf( '[Mastodon reply API] Response: %s', json_encode( $create_reply ) ) );

				if ( $create_reply && isset( $create_reply->id ) ) {
					$this->logger->info(
						sprintf(
							'Successfully shared first comment to %s on %s ',
							html_entity_decode( get_the_title( $post_details['post_id'] ) ),
							$post_details['service']
						)
					);
				}
			}

			// Save log.
			$this->save_logs_on_rop(
				array(
					'network' => $post_details['service'],
					'handle'  => $args['user'],
					'content' => $post_details['content'],
					'link'    => $post_details['post_url'],
				)
			);

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

		return false;
	}

	/**
	 * This method will load and prepare the account data for Mastodon user.
	 * Used in Rest Api.
	 *
	 * @since   8.4.0
	 * @access  public
	 *
	 * @param   array $account_data Mastodon pages data.
	 *
	 * @return  bool
	 */
	public function add_account_with_app( $account_data ) {
		if ( ! $this->is_set_not_empty( $account_data, array( 'id' ) ) ) {
			return false;
		}
		$the_id       = $account_data['id'];
		$account_data = $account_data['pages'];

		$args = array(
			'oauth_token'     => $account_data['credentials']['oauth_token'],
			'domain'          => $account_data['credentials']['domain'],
			'consumer_key'    => $account_data['credentials']['consumer_key'],
			'consumer_secret' => $account_data['credentials']['consumer_secret'],
		);

		$this->set_credentials(
			array_intersect_key(
				$args,
				array(
					'oauth_token'     => '',
					'domain'          => '',
					'consumer_key'    => '',
					'consumer_secret' => '',
				)
			)
		);

		$data = $this->get_user_accounts();
		if ( ! isset( $data->id ) ) {
			return false;
		}
		$this->service = array(
			'id'                 => $data->id,
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'public_credentials' => array(
				'mastodon' => array(
					'name'    => 'Mastodon',
					'value'   => $this->domain,
					'private' => false,
				),
			),
			'available_accounts' => $this->get_users( $data ),
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
		return $account;
	}

	/**
	 * Share new post.
	 *
	 * @param array $new_post Post data.
	 *
	 * @return false|object
	 */
	private function request_new_post( $new_post = array() ) {
		if ( empty( $this->credentials['oauth_token'] ) || empty( $this->credentials['domain'] ) ) {
			return false;
		}
		$api_url  = $this->get_api_endpoint( $this->credentials['domain'], 'api/v1/statuses' );
		$response = wp_remote_post(
			$api_url,
			array(
				'body'        => wp_json_encode( $new_post ),
				'headers'     => array(
					'timeout'       => 120,
					'Authorization' => 'Bearer ' . $this->credentials['oauth_token'],
					'Accept'        => 'application/json',
					'Content-Type'  => 'application/json',
				),
				'data_format' => 'body',
			)
		);
		if ( is_wp_error( $response ) ) {
			$this->logger->alert_error( 'Mastodon new post statuses API Error: ' . $response->get_error_message() );
			return false;
		}
		$response = wp_remote_retrieve_body( $response );
		$this->logger->info( 'Mastodon new post statuses API response: ' . print_r( $response, true ) );
		$response = json_decode( $response );

		if ( ! isset( $response->id ) ) {
			$this->logger->alert_error( 'Mastodon new post statuses API Error: ' . print_r( $response, true ) );
			return false;
		}
		return $response;
	}
}
