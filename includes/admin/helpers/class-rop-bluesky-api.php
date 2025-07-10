<?php
/**
 * The file that defines the class to manage Bluesky api request.
 *
 * A class that is used manipulate text content.
 *
 * @link       https://themeisle.com/
 * @since      9.3.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Class Rop_Bluesky_Api
 *
 * @since   9.3.0
 * @link    https://themeisle.com/
 */
class Rop_Bluesky_Api {

	/**
	 * Holds response
	 *
	 * @var mixed.
	 */
	private static $response = array();

	/**
	 * Holds the API url.
	 *
	 * @var string.
	 */
	private $api_url = 'https://bsky.social/xrpc';

	/**
	 * Holds the logger
	 *
	 * @since   9.3.0
	 * @access  protected
	 * @var     Rop_Logger $logger The logger handler.
	 */
	protected $logger;

	/**
	 * Bluesky indentifier.
	 * 
	 * @var string
	 */
	protected $identifier = '';

	/**
	 * Bluesky app password.
	 * 
	 * @var string
	 */
	protected $password = '';

	/**
	 * Construct.
	 *
	 * @param string $identifier Bluesky identifier.
	 * @param string $password   App password.
	 */
	public function __construct( $identifier = '', $password = '' ) {
		if ( empty( $identifier ) || empty( $password ) ) {
			return;
		}

		$this->logger     = new Rop_Logger();
		$this->identifier = $identifier;
		$this->password   = $password;
	}

	/**
	 * Create a session with Bluesky.
	 * 
	 * @return mixed|false
	 */
	public function create_session() {
		try {
			$response = wp_remote_post(
				"{$this->api_url}/com.atproto.server.createSession",
				[
					'headers' => [
						'Content-Type' => 'application/json',
						'timeout'      => 120,
					],
					'body'    => json_encode([
						'identifier' => $this->identifier,
						'password'   => $this->password,
					]),
				]
			);

			if ( is_wp_error( $response ) ) {
				$this->logger->alert_error( 'Bluesky create session API Error: ' . $response->get_error_message() );
				return false;
			}

			$response = wp_remote_retrieve_body( $response );
			$this->logger->info( 'Bluesky create session API response: ' . print_r( $response, true ) );
			$response = json_decode( $response );

			if ( isset( $response->error ) ) {
				$this->logger->alert_error( 'Bluesky create session API Error: ' . print_r( $response, true ) );
				return false;
			}

			if (
				! is_object( $response ) ||
				empty( $response->did ) ||
				empty( $response->accessJwt ) ||
				empty( $response->refreshJwt )
			) {
				$this->logger->alert_error( 'Bluesky create session API Error: Invalid response: ' . print_r( $response, true ) );
				return false;
			}

			return $response;
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Bluesky API Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get user details.
	 * 
	 * @param string $did The DID of the user.
	 * @param string $access_token Optional access token for authenticated requests.
	 * 
	 * @return mixed|false
	 */
	public function get_user_details( $did, $access_token = '' ) {
		if ( empty( $did ) || empty( $access_token ) ) {
			$this->logger->alert_error( 'Bluesky get user details API Error: DID or access token is empty.' );
			return false;
		}

		try {
			$response = wp_remote_get(
				"{$this->api_url}/app.bsky.actor.getProfile?" . http_build_query( [ 'actor' => $did ] ),
				[
					'headers' => [
						'Content-Type' => 'application/json',
						'timeout'      => 120,
						'Authorization' => 'Bearer ' . $access_token,
					],
				]
			);

			if ( is_wp_error( $response ) ) {
				$this->logger->alert_error( 'Bluesky get user details API Error: ' . $response->get_error_message() );
				return false;
			}

			$response = wp_remote_retrieve_body( $response );
			$response = json_decode( $response );
			$this->logger->info( 'Bluesky get user details API response: ' . print_r( $response, true ) );

			if ( isset( $response->error ) ) {
				$this->logger->alert_error( 'Bluesky create session API Error: ' . print_r( $response, true ) );
				return false;
			}

			return $response;
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Bluesky API Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Create a post on Bluesky.
	 * 
	 * @param string $did The DID of the user.
	 * @param array  $post The post.
	 * @param array  $hashtags Optional hashtags for the post.
	 * @param string $post_type Type of the post.
	 * @param string $access_token The access token for authenticated requests.
	 * 
	 * @return mixed|false
	 * @since 9.3.0
	 * @throws Exception If the API request fails or returns an error.
	 */
	public function create_post( $did, $post, $post_type, $hashtags, $access_token = '' ) {
		if ( empty( $did ) || empty( $post ) || empty( $access_token ) ) {
			$this->logger->alert_error( 'Bluesky create post API Error: DID, content or access token is empty.' );
			return false;
		}

		try {
			$url = "{$this->api_url}/com.atproto.repo.createRecord";

			$headers = [
				'Content-Type'  => 'application/json',
				'timeout'       => 120,
				'Authorization' => 'Bearer ' . $access_token,
			];

			$now  = gmdate( 'Y-m-d\TH:i:s\Z' );

			$record = array(
				'$type'     => 'app.bsky.feed.post',
				'text'      => $post['content'] . $hashtags,
				'createdAt' => $now,
			);

			if ( $post_type === 'link' ) {
				$record['embed'] = [
					'$type'   => 'app.bsky.embed.external',
					'external' => [
						'uri'         => isset( $post['url'] ) ? $post['url'] : '',
						'title'       => isset( $post['title'] ) ? $post['title'] : '',
						'description' => isset( $post['description'] ) ? $post['description'] : '',
					],
				];

				if (
					isset( $post['post_image'], $post['mimetype'] ) &&
					! empty( $post['post_image'] ) &&
					! empty( $post['mimetype'] )
				) {
					$image_blob = $this->upload_blob( $access_token, $post['post_image'], $post['mimetype']['type'] );

					if ( false !== $image_blob ) {
						$record['embed']['external']['thumb'] = $image_blob;
					}
				}
			}

			if ( $post_type === 'image' ) {
				$image_blob = $this->upload_blob( $access_token, $post['post_image'], $post['mimetype']['type'] );

				if ( false !== $image_blob ) {
					$record['embed'] = [
						'$type'   => 'app.bsky.embed.images',
						'images' => [
							[
								'alt'   => isset( $post['title'] ) ? $post['title'] : '',
								'image' => $image_blob,
							],
						],
					];
				}
			}

			$response = wp_remote_post(
				$url,
				[
					'headers' => $headers,
					'body'    => json_encode( [
						'repo'       => $did,
						'collection' => 'app.bsky.feed.post',
						'record'     => $record,
					] ),
				]
			);

			if ( is_wp_error( $response ) ) {
				$this->logger->alert_error( 'Bluesky create post API Error: ' . $response->get_error_message() );
				return false;
			}

			$response = wp_remote_retrieve_body( $response );
			$response = json_decode( $response );

			if ( isset( $response->error ) ) {
				$this->logger->alert_error( 'Bluesky create post API Error: ' . print_r( $response, true ) );
				return false;
			}

			return $response;
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Bluesky API Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Fetch Website Card embeds.
	 *
	 * @param string $access_token The access token for authenticated requests.
	 * @param array  $data         The embed data (expects 'uri', 'title', 'description').
	 * @return array|false
	 */
	/**
	 * Upload a blob to Bluesky.
	 *
	 * @param string $access_token The access token for authenticated requests.
	 * @param string $img_body     The image URL.
	 * @param string $mime         The MIME type of the image.
	 * @return array|false
	 */
	public function upload_blob( $access_token, $image, $mime ) {
		if ( empty( $access_token ) || empty( $image ) || empty( $mime ) ) {
			$this->logger->alert_error( 'Bluesky upload blob Error: access token or image body is empty.' );
			return false;
		}

		try {
			$data = $this->get_image_blob_from_url( $image );

			if ( false === $data ) {
				$this->logger->alert_error( 'Bluesky upload blob Error: Failed to fetch image blob from URL.' );
				return false;
			}

			$upload_response = wp_remote_post(
				"{$this->api_url}/com.atproto.repo.uploadBlob",
				[
					'headers' => [
						'Content-Type'  => $mime,
						'Authorization' => 'Bearer ' . $access_token,
					],
					'body'    => $data,
				]
			);

			if ( is_wp_error( $upload_response ) ) {
				$this->logger->alert_error( 'Bluesky upload blob Error: ' . $upload_response->get_error_message() );
				return false;
			}

			$blob_data = json_decode( wp_remote_retrieve_body( $upload_response ), true );

			if ( empty( $blob_data['blob'] ) ) {
				$this->logger->alert_error( 'Bluesky upload blob Error: No blob returned from upload.' );
				return false;
			}

			return $blob_data['blob'];
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Bluesky upload blob Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Fetch image binary data from a URL.
	 *
	 * @param string $image_url The image URL.
	 * 
	 * @return string|false Image binary data or false on failure.
	 */
	public function get_image_blob_from_url( $image_url ) {
		if ( empty( $image_url ) ) {
			$this->logger->alert_error( 'Bluesky get image blob Error: image URL is empty.' );
			return false;
		}

		$response = wp_remote_get( $image_url, [ 'timeout' => 30 ] );

		if ( is_wp_error( $response ) ) {
			$this->logger->alert_error( 'Bluesky get image blob Error: ' . $response->get_error_message() );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			$this->logger->alert_error( 'Bluesky get image blob Error: Empty body.' );
			return false;
		}

		return $body;
	}
}
