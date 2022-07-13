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

		$model       = new Rop_Post_Format_Model;
		$post_format = $model->get_post_format( $post_details['account_id'] );

		$hashtags = $post_details['hashtags'];

		if ( ! empty( $post_format['hashtags_randomize'] ) && $post_format['hashtags_randomize'] ) {
			$hashtags = $this->shuffle_hashtags( $hashtags );
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

				$response = Rop_Pro_Instagram_Service::share( $post_details, $hashtags, $args );

				return $response;

			}
			// ***** //
		}

		// Backwards compatibilty < v8.7.0 we weren't storing 'account_type' for Facebook groups yet.
		if ( strpos( $args['user'], 'Facebook Group:' ) !== false && $global_settings->license_type() < 1 ) {
			$this->logger->alert_error( sprintf( Rop_I18n::get_labels( 'errors.license_not_active' ), $args['user'] ) );
			return false;
		}

		// FB link post
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$sharing_data = $this->fb_article_post( $post_details, $hashtags );
		}

		// FB plain text post
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$sharing_data = $this->fb_text_post( $post_details, $hashtags );
		}

		// FB media post
		if ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {

			if ( strpos( get_post_mime_type( $post_details['post_id'] ), 'video' ) === false ) {
				$sharing_data = $this->fb_image_post( $post_details, $hashtags );
			} else {
				$sharing_data = $this->fb_video_post( $post_details, $hashtags );
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
	private function fb_article_post( $post_details, $hashtags ) {

		$new_post = array();

		$new_post['message'] = $this->strip_excess_blank_lines( $post_details['content'] ) . $hashtags;

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
	private function fb_image_post( $post_details, $hashtags ) {

		$attachment_url = $post_details['post_image'];

		/** If the post has no image but "Share as image post" is checked
		 * Share as an article post */
		if ( empty( $attachment_url ) ) {
			$this->logger->info( 'No image set for post, but "Share as Image Post" is checked. Falling back to article post' );
			return $this->fb_article_post( $post_details, $hashtags );
		}

		$new_post = array();

		$new_post['url']     = $attachment_url;
		$new_post['source']  = $this->get_path_by_url( $attachment_url, $post_details['mimetype'] ); // get image path
		$new_post['caption'] = $post_details['content'] . $this->get_url( $post_details ) . $hashtags;

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
	private function fb_video_post( $post_details, $hashtags ) {

		$new_post = array();

		$image     = $this->get_path_by_url( $post_details['post_image'], $post_details['mimetype'] );
		$new_post['source']      = $image;
		// $new_post['source']      = $api->videoToUpload( $image );
		$new_post['title']       = html_entity_decode( get_the_title( $post_details['post_id'] ), ENT_QUOTES );
		$new_post['description'] = $post_details['content'] . $this->get_url( $post_details ) . $hashtags;

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
	private function fb_text_post( $post_details, $hashtags ) {

		$new_post = array();

		$new_post['message'] = $post_details['content'] . $hashtags;

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

		if ( is_wp_error( $response ) ) {
			$error_string = $response->get_error_message();
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.wordpress_api_error' ) . $error_string );
			return array(
				'response' => '',
				'body'     => '',
			);
		}

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

			$scrape['id']           = $url . '?scrape=true&cacheburst=' . time();
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

			if ( is_wp_error( $scrape_response ) ) {
				$error_string = $scrape_response->get_error_message();
				$this->logger->info( Rop_I18n::get_labels( 'errors.wordpress_api_error' ) . $error_string );
				return false;
			}

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

		$account_id = ( ! empty( $account['id'] ) ) ? $account['id'] : '';
		$account_username = ( ! empty( $account['username'] ) ) ? $account['username'] : '';
		$account_type = ( ! empty( $account['account_type'] ) ) ? $account['account_type'] : '';

		if ( $account_type !== 'instagram_account' ) {
			$account['link'] = sprintf( 'https://facebook.com/%s', $account_id );
		} else {
			$account['link'] = sprintf( 'https://instagram.com/%s', $account_username );
		}
		return $account;
	}

}
