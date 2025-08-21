<?php
/**
 * The file that defines the abstract class inherited by all services
 *
 * A class that is used to define the services class and utility methods.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/abstract
 */

/**
 * Class Rop_Services_Abstract
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
abstract class Rop_Services_Abstract {
	/**
	 * Stores the service display name.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     string $display_name The service pretty name.
	 */
	public $display_name;
	/**
	 * Default account template array.
	 *
	 * @access  protected
	 * @since   8.0.0
	 * @var array Default account values.
	 */
	public $user_default = array(
		'account'    => '',
		'user'       => '',
		'created'    => 0,
		'id'         => 0,
		'active'     => true,
		'is_company' => false,
		'img'        => '',
		'service'    => '',
		'link'       => '',
	);
	/**
	 * Stores the service details.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     array $service The service details.
	 */
	protected $service;
	/**
	 * Stores the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name in slug format.
	 */
	protected $service_name;
	/**
	 * Stores a reference to the API to be used.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     mixed $api The API object.
	 */
	protected $api = null;
	/**
	 * The array with the credentials for auth-ing the service.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     array $credentials The credentials array used for auth.
	 */
	protected $credentials;
	/**
	 * Holds the Rop_Exception_Handler
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     Rop_Exception_Handler $error The exception handler.
	 */
	protected $error;
	/**
	 * Holds the logger
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     Rop_Logger $logger The logger handler.
	 */
	protected $logger;
	/**
	 * Stores a share first comment text.
	 *
	 * @since   9.1.3
	 * @access  protected
	 * @var     string $share_link_text Comment text.
	 */
	protected $share_link_text = '';

	/**
	 * Rop_Services_Abstract constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		$this->error                   = new Rop_Exception_Handler();
		$this->logger                  = new Rop_Logger();
		$this->user_default['created'] = gmdate( 'd/m/Y H:i' );
		$this->user_default['service'] = $this->service_name;
		$this->init();
	}

	/**
	 * Method to inject functionality into constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	abstract public function init();

	/**
	 * Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	abstract public function expose_endpoints();

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	abstract public function get_api();

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	abstract public function set_api();


	/**
	 * Method to populate additional data.
	 *
	 * @since   8.5.13
	 * @access  public
	 * @return mixed
	 * @param array $account The account details. See $user_default in Services Abstract.
	 */
	abstract public function populate_additional_data( $account );

	/**
	 * Method for authorizing the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function authorize() {

		try {

			$authenticated = $this->maybe_authenticate();

			if ( $authenticated ) {
				$service = $this->get_service();

				if ( 'linkedin' === $service['service'] ) {

					/**
					 * For LinkedIn, it seems they include '_' char into the service id and
					 * we need to replace with something else in order to not mess with the way we store the indices.
					 */
					$service_id = $service['service'] . '_' . $this->treat_underscore_exception( $service['id'] );
				} else {
					$service_id = $service['service'] . '_' . $this->strip_underscore( $service['id'] );
				}

				$new_service[ $service_id ] = $service;
			}

			$model = new Rop_Services_Model();
			$model->add_authenticated_service( $new_service );

		} catch ( Exception $exception ) {
			$this->error->throw_exception( 'Error', sprintf( 'The service "' . $this->display_name . '" can not be authorized %s', $exception->getMessage() ) );
		}

		wp_redirect( admin_url( 'admin.php?page=TweetOldPost' ) );
		exit;
	}

	/**
	 * Method for checking authentication the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	abstract public function maybe_authenticate();

	/**
	 * Returns information for the current service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	abstract public function get_service();

	/**
	 * Method for authenticate the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	abstract public function authenticate( $args );

	/**
	 * Method to register credentials for the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $args The credentials array.
	 */
	abstract public function set_credentials( $args );

	/**
	 * Method for publishing with the service.
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 * @since   8.0.0
	 * @access  public
	 */
	abstract public function share( $post_details, $args = array() );

	/**
	 * Method to retrieve an endpoint URL.
	 *
	 * @param string $path The endpoint path.
	 *
	 * @return mixed
	 * @since   8.0.0
	 * @access  public
	 */
	public function get_endpoint_url( $path = '' ) {
		return rest_url( '/tweet-old-post/v8/' . $this->service_name . '/' . $path );
	}

	/**
	 * Method to get currently active accounts for the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @return array
	 */
	public function get_service_active_accounts() {
		$service_details = $this->service;

		if ( ! isset( $service_details['available_accounts'] ) ) {
			return array();
		}
		if ( empty( $service_details['available_accounts'] ) ) {
			return array();
		}

		$active_accounts = array_filter(
			$service_details['available_accounts'],
			function ( $value ) {
				if ( ! isset( $value['active'] ) ) {
					return false;
				}

				return $value['active'];
			}
		);
		$accounts_ids    = array();
		foreach ( $active_accounts as $account ) {
			if ( 'linkedin' === $this->get_service_id() ) {
				$accounts_ids[ $this->get_service_id() . '_' . $this->treat_underscore_exception( $account['id'] ) ] = $account;
			} else {
				$accounts_ids[ $this->get_service_id() . '_' . $account['id'] ] = $account;
			}
		}

		return $accounts_ids;
	}

	/**
	 * Method to retrieve an service id.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @return string
	 */
	public function get_service_id() {
		$service_details = $this->service;
		if ( ! isset( $service_details['id'] ) ) {
			return '';
		}

		if ( 'linkedin' === $this->service_name ) {
			return $this->service_name . '_' . $this->treat_underscore_exception( $service_details['id'] );
		} else {
			return $this->service_name . '_' . $service_details['id'];
		}
	}

	/**
	 * Share the post link in the first comment.
	 *
	 * @access  public
	 *
	 * @param string $url API endpoint.
	 * @param array  $data API data.
	 */
	public function share_as_first_comment( $url, $data = array() ) {}

	/**
	 * Method to request a token from api.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @return mixed
	 */
	abstract protected function request_api_token();

	/**
	 * Method to generate url for service post share.
	 *
	 * Apply short url if available.
	 *
	 * @param array $post_details The post details to be published by the service.
	 *
	 * @return string
	 * @since   8.0.0rc
	 * @access  protected
	 */
	protected function get_url( $post_details ) {

		if ( empty( $post_details['post_url'] ) ) {
			return '';
		}

		if ( empty( $post_details['short_url'] ) || empty( $post_details['short_url_service'] ) ) {
			return ' ' . $post_details['post_url'];
		}

		if ( 'wp_short_url' === $post_details['short_url_service'] ) {
			return ' ' . wp_get_shortlink( $post_details['post_id'] );
		}

		$post_format_helper = new Rop_Post_Format_Helper();

		return ' ' . $post_format_helper->get_short_url( $post_details['post_url'], $post_details['short_url_service'], $post_details['shortner_credentials'] );
	}

	/**
	 * Utility method to check array if has certain keys set and not empty.
	 *
	 * @param array $array Array to check.
	 * @param array $list List of keys to check.
	 *
	 * @return bool Valid or not.
	 */
	protected function is_set_not_empty( $array = array(), $list = array() ) {
		if ( empty( $array ) ) {
			return false;
		}
		if ( empty( $list ) ) {
			return false;
		}
		foreach ( $list as $key ) {
			if ( ! isset( $array[ $key ] ) ) {
				$this->error->throw_exception( 'Value not set ', sprintf( 'Value not set : %s in %s ', $key, print_r( $array, true ) ) );

				return false;
			}
			if ( empty( $array[ $key ] ) ) {
				$this->error->throw_exception( 'Value is empty ', sprintf( 'Value is empty : %s in %s ', $key, print_r( $array, true ) ) );

				return false;
			}

			if ( ! $this->is_valid_serialize_data( $array[ $key ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Utility method to register a REST endpoint via WP.
	 *
	 * @param string $path The path for the endpoint.
	 * @param string $callback The method name from the service class.
	 * @param string $method The request type ( GET, POST, PUT, DELETE etc. ).
	 *
	 * @since   8.0.0
	 * @access  protected
	 */
	protected function register_endpoint( $path, $callback, $method = 'GET' ) {

		if ( $callback == false ) {
			return;
		}

		add_action(
			'rest_api_init',
			function () use ( $path, $callback, $method ) {
				register_rest_route(
					'tweet-old-post/v8',
					'/' . $this->service_name . '/' . $path,
					array(
						'methods'             => $method,
						'callback'            => array( $this, $callback ),
						'permission_callback' => function () {
							return current_user_can( 'manage_options' );
						},
					)
				);
			}
		);
	}

	/**
	 * Facebook legacy redirect url.
	 *
	 * @return string Old legacy url.
	 */
	protected function get_legacy_url( $network = '' ) {
		$url = get_admin_url( get_current_blog_id(), 'admin.php?page=TweetOldPost' );
		if ( ! empty( $network ) ) {
			$url = add_query_arg( array( 'network' => $network ), $url );
		}

		return str_replace( ':80', '', $url );
	}

	/**
	 * Strip non-ascii chars.
	 *
	 * @param string $string String to check.
	 *
	 * @return string Normalized string.
	 */
	protected function normalize_string( $string ) {
		return preg_replace( '/[[:^print:]]/', '', $string );
	}

	/**
	 * Strip underscore and replace with safe char.
	 *
	 * @param string $name Original name.
	 *
	 * @return mixed Normalized name.
	 */
	protected function strip_underscore( $name ) {
		return str_replace( '_', '---', $name );
	}

	/**
	 * Adds back the underscore.
	 *
	 * @param string $name Safe name.
	 *
	 * @return mixed Unsafe name.
	 */
	protected function unstrip_underscore( $name ) {
		return str_replace( '---', '_', $name );
	}

	/**
	 * Strips white space from credentials
	 *
	 * @param string $data the credential.
	 *
	 * @return string Cleaned credential.
	 */
	protected function strip_whitespace( $data ) {
		$data = rtrim( ltrim( $data ) );

		return $data;
	}

	/**
	 * Strips excess blank lines left by media blocks in Gutenberg editor
	 *
	 * @param string $content the content to clean.
	 *
	 * @return string The cleaned content.
	 */
	protected function strip_excess_blank_lines( $content ) {
		$content = preg_replace( "/([\r\n]{4,}|[\n]{3,}|[\r]{3,})/", "\n\n", $content );

		return $content;
	}

	/**
	 * Gets the appropriate documentation for errors in log.
	 *
	 * @since   8.2.3
	 * @access  public
	 *
	 * @param string $response the API error response.
	 *
	 * @return string The document link.
	 */
	protected function rop_get_error_docs( $response ) {
		if ( is_array( $response ) || is_object( $response ) ) {
			// Convert arrays and objects to JSON string to match error messages.
			$response = json_encode( $response );
		}

		$errors_docs = array(
			// Facebook errors
			'Only owners of the URL have the ability'    => array(
				'message' => __( 'You need to verify your website with Facebook before sharing posts as article posts.', 'tweet-old-post' ),
				'link'    => 'https://is.gd/fix_owners_url',
			),
			'manage_pages and publish_pages as an admin' => array(
				'message' => __( 'You need to put your Facebook app through review.', 'tweet-old-post' ),
				'link'    => 'https://is.gd/fix_manage_pages_error',
			),
			'The session has been invalidated because the user changed their password' => array(
				'message' => __( 'You need to reconnect your Facebook account.', 'tweet-old-post' ),
				'link'    => 'https://is.gd/fix_fb_invalid_session',
			),
			'Invalid parameter'                          => array(
				'message' => 'There might be an issue with link creations on your website.',
				'link'    => 'https://is.gd/fix_link_issue',
			),
			'The \'manage_pages\' permission must be granted before impersonating' => array(
				'message' => 'You might need to reconnect your Facebook account.',
				'link'    => 'https://is.gd/fix_pages_group_permissions',
			),
			'If posting to a group, requires app being installed in the group' => array(
				'message' => 'If posting to a page, then you might have to reconnect your Facebook account. If posting to a group then you need to install the Revive Social App on the group.',
				'link'    => 'https://is.gd/fix_pages_group_permissions',
			),

			// Twitter errors
			'Desktop applications only support the oauth_callback value' => array(
				'message' => 'Your Callback URL for your Twitter app might not be correct.',
				'link'    => 'https://is.gd/fix_oauth_callback_value',
			),
			'User is over daily status update limit'     => array(
				'message' => 'You might be over your daily limit for sending tweets or our app has hit a limit.',
				'link'    => 'https://is.gd/fix_over_daily_limit',
			),
			'Invalid media_id: Some'                     => array(
				'message' => 'Our plugin might be having an issue posting tweets with an image to your account.',
				'link'    => 'https://is.gd/fix_invalid_media',
			),
			'Callback URL not approved for this client application' => array(
				'message' => 'Your Callback URL for your Twitter app might not be correct.',
				'link'    => 'https://is.gd/fix_oauth_callback_value',
			),

			// LinkedIn errors
			'&#39;submitted-url&#39; can not be empty'   => array(
				'message' => 'There might be an issue with link creations on your website.',
				'link'    => 'https://is.gd/fix_link_issue',
			),
			'[ unauthorized_scope_error ] Scope "r_organization_social"' => array(
				'message' => 'You might need to reconnect your LinkedIn account.',
				'link'    => 'https://is.gd/linkedin_scope_error',
			),
			'The token used in the request has expired'  => array(
				'message' => 'You need to reconnect your LinkedIn account.',
				'link'    => 'https://is.gd/refresh_linkedin_token',
			),
			'You are using an old method of sharing to LinkedIn' => array(
				'message' => 'You need to reconnect your LinkedIn account.',
				'link'    => 'https://is.gd/switch_linkedin_signon_method',
			),

			// Pinterest errors
			'Pinterest error (code: 429) with message: You have exceeded your rate limit' => array(
				'message' => 'You\'ve hit the Pinterest rate limit.',
				'link'    => 'https://is.gd/pinterest_rate_limit',
			),

			// Add more common errors as necessary
		);

		$message = '';
		$link    = '';

		foreach ( $errors_docs as $error => $data ) {
			if ( strpos( $response, $error ) !== false ) {
				$message = $data['message'];
				$link    = $data['link'];
				break;
			}
		}
		if ( empty( $link ) ) {
			// No link found for error, bail.
			return;
		}

		$known_error  = __( 'This error is a known one: ', 'tweet-old-post' );
		$instructions = __( ' Please copy and paste the following link in your browser to see the solution: ', 'tweet-old-post' );

		return $this->logger->alert_error( $known_error . $message . $instructions . $link );
	}

	/**
	 * Download external images temporarily.
	 *
	 * This method is only needed for Twitter Service since the other Libraries
	 * That we use work fine with external image URLS
	 *
	 * @param int $image_url The image URL.
	 *
	 * @return string Path of downloaded external image.
	 */
	protected function rop_download_external_image( $image_url ) {

		$tmp_images_folder = ROP_TEMP_IMAGES;

		if ( ! is_dir( $tmp_images_folder ) ) {
			wp_mkdir_p( $tmp_images_folder );
		}

		$headers = get_headers( $image_url );

		if ( empty( $headers ) ) {
			return $image_url;
		}

		// Returns response code e.g 200, 404 etc
		$response_code = (int) substr( $headers[0], 9, 3 );

		$image_contents = '';

		if ( $response_code !== 200 ) {
			return $image_url;
		}

		$image_contents = file_get_contents( $image_url );

		$tmp_image_name = 'rop-external-image' . substr( time(), -3 ) . rand( 0, 50 ) . '.jpg';
		$tmp_image_path = ROP_TEMP_IMAGES . $tmp_image_name;

		file_put_contents( $tmp_image_path, $image_contents );

		return $tmp_image_path;
	}

	/**
	 * Get Image file path if exists, return default image_url if not.
	 *
	 * Used where file_get_contents might not work with urls, we provide the file path.
	 *
	 * @param string $image_url Image url.
	 * @param array  $mimetype Used to identify the mime type.
	 *
	 * @return string Image path.
	 */
	protected function get_path_by_url( $image_url, $mimetype = '' ) {

		if ( empty( $image_url ) ) {
			return '';
		}

		// Upload folder.
		$dir    = wp_upload_dir();
		$parsed = parse_url( $dir['baseurl'] );
		$dir    = $parsed['host'] . $parsed['path'];

		if ( false === strpos( $image_url, $dir ) ) {
			return $image_url;
		}
		// Fetch the filename.
		$file = wp_basename( $image_url );

		// Find the media ID in the database using the filename.
		$query = array(
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

		// If the attachment is not an image, return the url.
		if ( strpos( $mimetype['type'], 'video' ) !== false ) {
			if ( empty( $ids ) ) {
				return $image_url;
			}

			$reset_id_list = reset( $ids );

			return get_attached_file( $reset_id_list );
		}

		if ( ! empty( $ids ) ) {
			foreach ( $ids as $id ) {
				$image_get             = wp_get_attachment_image_src( $id, 'full' );
				$attachment_url        = $image_get[0];

				$attachment_url_is_wp_upload = strpos( $attachment_url, '/uploads/' );
				$image_url_is_wp_upload = strpos( $image_url, '/uploads/' );

				if ( ! empty( $attachment_url_is_wp_upload ) && ! empty( $image_url_is_wp_upload ) ) {

					$attachment_image_uploads_path = explode( 'uploads', $attachment_url )[1]; // get uploads path from URL.
					$image_url_uploads_path       = explode( 'uploads', $image_url )[1]; // get uploads path from URL.

					// Remove query string from URL which sometime exists for jetpack images.
					$attachment_image_uploads_path = explode( '?', $attachment_image_uploads_path )[0];
					$image_url_uploads_path = explode( '?', $image_url_uploads_path )[0];

					// Check if the found image is the one we require.
					if ( $image_url_uploads_path === $attachment_image_uploads_path ) {
						$id_found = $id;
						break;
					}
				} else {

					$attachment_image_name = wp_basename( $attachment_url ); // get filename from URL.
					$image_url_name        = wp_basename( $image_url ); // get filename from URL.

					// Check if the found image is the one we require.
					if ( $image_url_name === $attachment_image_name ) {
						$id_found = $id;
						break;
					}
				}
			}
		}

		// If the image is a WP size instead of full.
		if ( false === $id_found ) {
			$query['meta_query'][0]['key'] = '_wp_attachment_metadata';

			// query attachments again
			$ids = get_posts( $query );

			if ( empty( $ids ) ) {
				return $image_url;
			}

			foreach ( $ids as $id ) {

				$meta = wp_get_attachment_metadata( $id );
				// Check which of the size value is the requested one.
				foreach ( $meta['sizes'] as $size => $values ) {
					if ( $values['file'] === $file ) { // compare filenames.
						$id_found = $id;
						break;
					}
				}
				if ( false === $id_found ) {
					break;
				}
			}
		}

		if ( false === $id_found ) {
			return $image_url;
		}

		$path = get_attached_file( $id_found );
		if ( empty( $path ) ) {
			return $image_url;
		}

		return $path;
	}

	/**
	 * Converts image into base_64 code from given local path
	 *
	 * @since 8.5.0
	 *
	 * @param string $image_path - Full local image path to uploads folder.
	 *
	 * @return string
	 */
	protected function convert_image_to_base64( $image_path = '' ) {
		$opened_file = fopen( $image_path, 'r' );
		if ( false === $opened_file ) { // If the file cannot be opened, we need to return the given path instead.
			return $image_path;
		}

		$contents = fread( $opened_file, filesize( $image_path ) );
		fclose( $opened_file );

		return base64_encode( $contents );
	}

	/**
	 * Checks to see if the cURL library is loaded and the function can be found.
	 *
	 * @return bool true/false if function is found.
	 * @since 8.5.0
	 */
	protected function is_curl_active() {
		return function_exists( 'curl_init' );
	}

	/**
	 * Returns true if the string $file_path is an URL.
	 *
	 * @param string $file_path string with filepath or url.
	 *
	 * @since 8.5.0
	 *
	 * @return boolean
	 */
	protected function is_remote_file( $file_path = '' ) {
		return preg_match( '/^(https?|ftp):\/\/.*/', $file_path ) === 1;
	}

	/**
	 * Treat the underscore exception.
	 *
	 * @param string $given_id Social media ID.
	 * @param bool   $reverse replace underscore or put it back.
	 *
	 * @return string|string[]
	 * @since 8.5.3
	 */
	protected function treat_underscore_exception( $given_id, $reverse = false ) {

		if ( false === $reverse ) {
			$given_id = str_replace( '_', '!sp!', $given_id );
		} else {
			$given_id = str_replace( '!sp!', '_', $given_id );
		}

		return $given_id;
	}

	/**
	 * Shuffle hashtags list
	 *
	 * @param string $hashtags hashtags list string.
	 *
	 * @return string|string[] Randomized string of hashtags
	 * @since 9.0.2
	 */
	protected function shuffle_hashtags( $hashtags ) {

		if ( empty( $hashtags ) ) {
			return '';
		}

		$hashtags = trim( $hashtags );
		$hashtags = explode( ' ', $hashtags );
		shuffle( $hashtags );
		$hashtags = ' ' . implode( ' ', $hashtags );

		return $hashtags;
	}

	/**
	 * Check is valid serialize data.
	 *
	 * @param string $data serialize string.
	 * @return bool
	 */
	protected function is_valid_serialize_data( $data ) {
		$valid = true;
		if ( is_array( $data ) ) {
			$data = array_map(
				function ( $d ) {
					$d = base64_decode( $d, true );
					$d = maybe_unserialize( $d, array( 'allowed_classes' => false ) );
					if ( $d instanceof \__PHP_Incomplete_Class ) {
						return false;
					}
					return true;
				},
				$data
			);
			$data  = array_filter( $data );
			$valid = empty( $data ) ? false : true;
		} else {
			$data = base64_decode( $data, true );
			$data = maybe_unserialize( $data, array( 'allowed_classes' => false ) );
			if ( $data instanceof \__PHP_Incomplete_Class ) {
				$valid = false;
			}
		}
		return $valid;
	}

	/**
	 * Capture logs on ROP API.
	 *
	 * @since   9.1.3
	 * @access  public
	 *
	 * @param array $data API payload data.
	 * @return bool
	 */
	public function save_logs_on_rop( $data = array() ) {
		if ( empty( $data ) ) {
			return false;
		}

		$license_key    = apply_filters( 'product_rop_license_key', '' );
		$license_status = apply_filters( 'product_rop_license_status', 'invalid' );

		// Send API request.
		$response = wp_remote_post(
			ROP_POST_LOGS_API,
			apply_filters(
				'rop_post_capture_logs_api_args',
				array(
					'timeout' => 100,
					'body'    => array_merge(
						array(
							'website'        => get_site_url(),
							'license'        => $license_key,
							'license_status' => $license_status,
						),
						$data
					),
				)
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body          = json_decode( wp_remote_retrieve_body( $response ) );
		$response_code = wp_remote_retrieve_response_code( $response );
		return 200 === $response_code ? $body->status : false;
	}
}
