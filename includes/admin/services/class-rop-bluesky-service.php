<?php
/**
 * The file that defines the Twitter Service specifics.
 *
 * A class that is used to interact with Twitter.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      9.3.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Bluesky_Service
 *
 * @since   9.3.0
 * @link    https://themeisle.com/
 */
class Rop_Bluesky_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   9.3.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'bluesky';


	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   9.3.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Bluesky';
	}

	/**
	 * Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since   9.3.0
	 * @access  public
	 */
	public function expose_endpoints() {}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   9.1.3
	 * @access  public
	 *
	 * @param string $identifier The Bluesky identifier.
	 * @param string $password   The Bluesky app password.
	 * @param string $refresh_token The refresh token for the API.
	 *
	 * @return Rop_Bluesky_Api
	 */
	public function get_api( $identifier = '', $password = '', $refresh_token = '' ) {
		if ( null === $this->api ) {
			$this->set_api( $identifier, $password, $refresh_token );
		}

		return $this->api;
	}

	/**
	 * Method to define the api.
	 *
	 * @since   9.1.3
	 * @access  public
	 *
	 * @param string $identifier The Bluesky identifier.
	 * @param string $password   The Bluesky app password.
	 * @param string $refresh_token The refresh token for the API.
	 *
	 * @return mixed
	 */
	public function set_api( $identifier = '', $password = '', $refresh_token = '' ) {
		try {
			if ( empty( $identifier ) || empty( $password ) ) {
				return false;
			}

			$this->api = new Rop_Bluesky_Api( $identifier, $password, $refresh_token );
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Can not load Bluesky api. Error: ' . $e->getMessage() );
		}
	}

	/**
	 * Check if we need to authenticate the user.
	 */
	public function maybe_authenticate() {}

	/**
	 * Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   9.3.0
	 * @access  public
	 */
	public function authenticate( $args = array() ) {}

	/**
	 * Method to register credentials for the service.
	 *
	 * @since   9.3.0
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
	 * @since   9.3.0
	 * @access  public
	 * @return mixed
	 */
	public function get_service() {
		return $this->service;
	}

	/**
	 * Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   9.3.0
	 * @access  protected
	 * @return mixed
	 */
	public function request_api_token() {}

	/**
	 * Method for publishing with Twitter service.
	 *
	 * @since   9.3.0
	 * @access  public
	 *
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 * @throws Exception If there is an error during the sharing process.
	 */
	public function share( $post_details, $args = array() ) {

		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		$post_id             = $post_details['post_id'];
		$identifier          = $args['credentials']['identifier'];
		$password            = $args['credentials']['password'];
		$refresh_token       = isset( $args['credentials']['refreshJwt'] ) ? $args['credentials']['refreshJwt'] : '';
		$post_url            = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];
		$model               = new Rop_Post_Format_Model();
		$post_format         = $model->get_post_format( $post_details['account_id'] );
		$hashtags            = $post_details['hashtags'];

		if ( ! empty( $post_format['hashtags_randomize'] ) && $post_format['hashtags_randomize'] ) {
			$hashtags = $this->shuffle_hashtags( $hashtags );
		}

		$post_type = 'text';

		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$post_type = 'link';
		} elseif ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$post_type = 'text';
		} elseif ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {
			$post_type = 'image';
		}

		try {
			$api = $this->get_api( $identifier, $password, $refresh_token );

			if ( ! $api ) {
				throw new Exception( 'Bluesky API Error: Unable to initialize API with provided credentials.' );
			}

			$response = $api->refresh_session();

			if ( empty( $response ) || ! is_object( $response ) || ! isset( $response->did ) ) {
				throw new Exception( 'Bluesky API Error: ' . wp_json_encode( $response ) );
			}

			$id            = $response->did;
			$access_token  = $response->accessJwt;

			// Use the highest resolution image that still fits Bluesky's blob size limit.
			$best_image = $this->get_blob_safe_image_url( $post_details );
			if ( ! empty( $best_image ) ) {
				$post_details['post_image'] = $best_image;
				$post_details['mimetype']   = wp_check_filetype( $best_image );
			}

			$response = $api->create_post( $id, $post_details, $post_type, $hashtags, $access_token );

			if ( $response && $response->validationStatus === 'valid' ) {
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
						'Successfully shared %s to %s on Bluesky ',
						html_entity_decode( get_the_title( $post_details['post_id'] ) ),
						$args['user']
					)
				);

				return true;
			}
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Error sharing to Bluesky: ' . $e->getMessage() );
			return false;
		}

		return false;
	}

	/**
	 * Pick the highest-resolution local image that fits Bluesky's blob size limit.
	 *
	 * Bluesky rejects image blobs larger than 1,000,000 bytes, which is why the
	 * shared image defaults to the WordPress "large" size and often looks blurry.
	 * This walks the available sizes from largest to smallest and returns the URL
	 * of the first one whose file is within the limit, so the sharpest acceptable
	 * image is uploaded. For external images, an unresolved attachment, or when no
	 * larger size fits, the original URL is returned unchanged (never worse than
	 * the previous behaviour).
	 *
	 * @since   9.3.7
	 * @access  private
	 *
	 * @param   array<string, mixed> $post_details The post details to be published.
	 * @return  string The image URL to upload.
	 */
	private function get_blob_safe_image_url( $post_details ) {
		$image_url = isset( $post_details['post_image'] ) ? $post_details['post_image'] : '';
		if ( empty( $image_url ) ) {
			return $image_url;
		}

		// Only handle locally hosted images; external URLs are left untouched.
		$uploads = wp_get_upload_dir();
		if ( empty( $uploads['baseurl'] ) || empty( $uploads['basedir'] ) || strpos( $image_url, $uploads['baseurl'] ) !== 0 ) {
			return $image_url;
		}

		$post_id = isset( $post_details['post_id'] ) ? $post_details['post_id'] : 0;

		// Resolve the attachment behind the shared image.
		$attachment_id = 0;
		if ( $post_id && get_post_type( $post_id ) === 'attachment' ) {
			$attachment_id = $post_id;
		} else {
			$attachment_id = attachment_url_to_postid( $image_url );
			if ( ! $attachment_id ) {
				$stripped = preg_replace( '/-\d+x\d+(\.[A-Za-z0-9]+)$/', '$1', $image_url );
				if ( $stripped !== $image_url ) {
					$attachment_id = attachment_url_to_postid( $stripped );
				}
			}
			if ( ! $attachment_id && $post_id && has_post_thumbnail( $post_id ) ) {
				$attachment_id = get_post_thumbnail_id( $post_id );
			}
		}

		if ( ! $attachment_id ) {
			return $image_url;
		}

		$full_path = get_attached_file( $attachment_id );
		if ( empty( $full_path ) ) {
			return $image_url;
		}

		$limit    = (int) apply_filters( 'rop_bluesky_max_image_bytes', 1000000 );
		$sizes    = apply_filters( 'rop_bluesky_image_size_priority', array( 'full', '2048x2048', '1536x1536', 'large' ) );
		$base_dir = trailingslashit( dirname( $full_path ) );
		$meta     = wp_get_attachment_metadata( $attachment_id );

		foreach ( $sizes as $size ) {
			if ( 'full' === $size ) {
				$path = $full_path;
			} else {
				if ( empty( $meta['sizes'][ $size ]['file'] ) ) {
					continue;
				}
				$path = $base_dir . $meta['sizes'][ $size ]['file'];
			}

			if ( ! file_exists( $path ) ) {
				continue;
			}

			$bytes = filesize( $path );
			if ( $bytes && $bytes <= $limit ) {
				$candidate_url = str_replace( $uploads['basedir'], $uploads['baseurl'], $path );
				if ( $candidate_url !== $image_url ) {
					$this->logger->info( sprintf( 'Bluesky: using "%s" image size (%d bytes) for higher resolution.', $size, $bytes ) );
				}
				return $candidate_url;
			}
		}

		return $image_url;
	}

	/**
	 * This method will load and prepare the account data for Twitter user.
	 * Used in Rest Api.
	 *
	 * @since   8.4.0
	 * @access  public
	 *
	 * @param   array $data Account data.
	 *
	 * @return  bool
	 * @throws Exception If there is an error during the account creation process.
	 */
	public function add_account_with_app( $data ) {
		if ( empty( $data['identifier'] ) || empty( $data['password'] ) ) {
			return false;
		}

		try {
			$bluesky = $this->get_api( $data['identifier'], $data['password'] );

			if ( ! $bluesky ) {
				throw new Exception( 'Bluesky API Error: Unable to initialize API with provided credentials.' );
			}

			$response = $bluesky->create_session();

			if ( empty( $response ) || ! is_object( $response ) || ! isset( $response->did ) ) {
				throw new Exception( 'Bluesky API Error: ' . wp_json_encode( $response ) );
			}

			$id            = $response->did;
			$access_token  = $response->accessJwt;
			$active        = isset( $data['active'] ) ? $data['active'] : true;
			$user          = $bluesky->get_user_details( $id, $access_token );

			$this->service = array(
				'id'                 => $id,
				'service'            => $this->service_name,
				'credentials'        => array(
					'identifier' => $data['identifier'],
					'password'   => $data['password'],
					'refreshJwt' => $response->refreshJwt,
				),
				'available_accounts' => array(
					$this->service_name . '_' . $id => array(
						'id'      => $id,
						'user'    => ! empty( $user->displayName ) ? $user->displayName : $user->handle,
						'account' => $user->handle,
						'service' => $this->service_name,
						'img'     => $user->avatar ? $user->avatar : '',
						'created' => date( 'd/m/Y H:i' ),
						'active'  => $active,
					),
				),
			);
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Bluesky API Error: ' . $e->getMessage() );
			return false;
		}

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
}
