<?php
/**
 * The file that defines the Vkontakte Service specifics.
 *
 * NOTE: Extending abstract class but not making use of some of the methods with new authentication workflow.
 *           Abstract class will be cleaned up once we move all services to one click sign on and drop users connecting own apps.
 *
 * A class that is used to interact with  Vkontakte
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.6.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

use \VK\Client\VKApiClient;
/**
 * Class Rop_Vk_Service
 *
 * @since   8.6.0
 * @link    https://themeisle.com/
 */
class Rop_Vk_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since  8.6.0
	 * @access protected
	 * @var    string $service_name The service name.
	 */
	protected $service_name = 'vk';

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since  8.6.0
	 * @access public
	 */
	public function init() {
		$this->display_name = 'Vk';
	}

		/**
		 * Returns information for the current service.
		 *
		 * @since  8.6.0
		 * @access public
		 * @return mixed
		 */
	public function get_service() {
		return $this->service;
	}

	/**
	 * Abstract function, not in Use. Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since  8.6.0
	 * @access public
	 */
	public function expose_endpoints() {
		return;
	}

	/**
	 * Abstract function, not in Use. Method to register credentials for the service.
	 *
	 * @since  8.0.0
	 * @access public
	 *
	 * @param array $args The credentials array.
	 */
	public function set_credentials( $args ) {
		return;
	}

	/**
	 * Abstract function, not in Use. Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since  8.6.0
	 * @access protected
	 * @return mixed
	 */
	public function request_api_token() {
		return;
	}

	/**
	 * Abstract function, not in Use. Method to retrieve the api object.
	 *
	 * @since  8.6.0
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
	 * @since  8.6.0
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
	 * Abstract function, not in Use. Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since  8.6.0
	 * @access public
	 * @return mixed
	 */
	public function maybe_authenticate() {
		return;
	}

	/**
	 * Abstract function, not in Use. Method to authenticate an user based on provided credentials.
	 * Used in DB upgrade.
	 *
	 * @param array $args The arguments for service auth.
	 *
	 * @return bool
	 */
	public function authenticate( $args = array() ) {
		return;
	}

	/**
	 * This method will load and prepare the account data for Vkontakte user.
	 * Used in Rest Api.
	 *
	 * @since   8.6.0
	 * @access  public
	 *
	 * @param   array $accounts_data Buffer accounts data.
	 *
	 * @return  bool
	 */
	public function add_account_with_app( $accounts_data ) {

		if ( ! $this->is_set_not_empty( $accounts_data, array( 'id' ) ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.vk_no_valid_accounts' ) );
			return false;
		}

		$the_id       = unserialize( base64_decode( $accounts_data['id'] ) );
		$accounts_array = unserialize( base64_decode( $accounts_data['pages'] ) );

		$accounts = array();

		for ( $i = 0; $i < sizeof( $accounts_array ); $i++ ) {

			$account = $this->user_default;

			$account_data = $accounts_array[ $i ];

			$account['id'] = $account_data['id'];
			$account['img'] = apply_filters( 'rop_custom_vk_avatar', $account_data['img'] );
			$account['account'] = $account_data['account'];
			$account['user'] = $account_data['user'];
			$account['is_company'] = $account_data['is_company'];

			if ( $i === 0 ) {
				$account['active'] = true;
			} else {
				$account['active'] = false;
			}

			$accounts[] = $account;
		}

		// Prepare the data that will be saved as new account added.
		$this->service = array(
			'id'                 => $the_id,
			'service'            => $this->service_name,
			'credentials'        => array(
				'access_token'   => $account_data['access_token'],
			),
			'available_accounts' => $accounts,
		);

		return true;
	}

	/**
	 * Method for creating media posts on Vkontakte.
	 *
	 * @since  8.6.0
	 * @access private
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param array  $hashtags Hashtags.
	 * @param array  $args Optional arguments needed by the method.
	 * @param int    $owner_id The owner id.
	 * @param object $client Instance of the client.
	 * @param string $access_token The access token.
	 *
	 * @return array $new_post The post contents
	 */
	private function vk_media_post( $post_details, $hashtags, $args, $owner_id, $client, $access_token ) {

		if ( ! function_exists( 'curl_init' ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'misc.curl_not_detected' ) );
			return false;
		}

		$attachment_url = $post_details['post_image'];

		// If the post has no image but "Share as image post" is checked
		// share as an article post
		if ( empty( $attachment_url ) ) {
			$this->logger->info( 'No image set for post, but "Share as Image Post" is checked. Falling back to article post' );
			return $this->vk_article_post( $post_details, $hashtags, $args, $owner_id );
		}

		$passed_image_url_host = parse_url( $attachment_url )['host'];
		$admin_site_url_host = parse_url( get_site_url() )['host'];

		/** If this image is not local then lets download it locally to get its path  */
		if ( ( $passed_image_url_host === $admin_site_url_host ) && strpos( $post_details['mimetype']['type'], 'video' ) !== true ) {
			$attachment_path = $this->get_path_by_url( $attachment_url, $post_details['mimetype'] );
		} else {
			$attachment_path = $this->rop_download_external_image( $attachment_url );
		}

		// If attachment is video
		if ( strpos( $post_details['mimetype']['type'], 'video' ) !== false ) {
			return $this->vk_video_post( $post_details, $hashtags, $attachment_path, $args, $owner_id, $client, $access_token );
		}

		$param = array();

		if ( $args['is_company'] ) {
			$param['group_id'] = $args['id'];
		}

		$photo_response = $client->photos()->getWallUploadServer(
			$access_token,
			$param
		);

		$upload_url = $photo_response['upload_url'];

		$data = array(
			'photo' => new CURLFile(
				$attachment_path,
				'multipart/form-data',
				'image.jpg'
			),
		);

		$ch = '';
		$ch = curl_init( $upload_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );

		$response = json_decode( curl_exec( $ch ), true );

		curl_close( $ch );

		/** Delete this image if it was an external one downloaded temporarily. */
		if ( strpos( $attachment_path, ROP_TEMP_IMAGES ) !== false ) {
			wp_delete_file( $attachment_path );
		}

		$params = array(
			'photo' => stripslashes( $response['photo'] ),
			'server' => (int) $response['server'],
			'hash' => (string) $response['hash'],
		);

		if ( $args['is_company'] ) {
			$params['group_id'] = (int) $args['id']; // has to be positive
		} else {
			$params['user_id'] = (int) $args['id'];
		}

		$response = $client->photos()->saveWallPhoto(
			$access_token,
			$params
		);

		$attachment = 'photo' . $response[0]['owner_id'] . '_' . $response[0]['id'];

		$new_post = array(
			'owner_id' => $owner_id,
			'message' => $post_details['content'] . $hashtags,
			'attachments' => empty( $this->share_link_text ) ? $attachment . ',' . $this->get_url( $post_details ) : $attachment,
		);
		return $new_post;

	}

	/**
	 * Method for publishing videos to Vkontakte service.
	 *
	 * @since  8.6.0
	 * @access private
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param array  $hashtags Hashtags.
	 * @param string $attachment_path The video attachment URL.
	 * @param array  $args Optional arguments needed by the method.
	 * @param int    $owner_id The owner id.
	 * @param object $client Instance of the client.
	 * @param string $access_token The access token.
	 *
	 * @return array $new_post The post contents
	 */
	private function vk_video_post( $post_details, $hashtags, $attachment_path, $args, $owner_id, $client, $access_token ) {

		$params = array(
			'name' => get_the_title( $post_details['post_id'] ),
			'description' => $post_details['content'],
		);

		if ( $args['is_company'] ) {
			$params['group_id'] = $args['id'];
		}

		$upload_url = $client->video()->save(
			$access_token,
			$params
		)['upload_url'];

		$data = array(
			'video_file' => new CURLFile(
				$attachment_path,
				'multipart/form-data',
				'video.mp4'
			),
		);

		$ch = '';
		$ch = curl_init( $upload_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );

		$response = json_decode( curl_exec( $ch ), true );

		curl_close( $ch );

		$attachment = 'video' . $response['owner_id'] . '_' . $response['video_id'];

		$new_post = array(
			'owner_id' => $response['owner_id'],
			'message' => $post_details['content'] . $hashtags,
			'attachments' => empty( $this->share_link_text ) ? $attachment . ',' . $this->get_url( $post_details ) : $attachment,
		);

		return $new_post;

	}


	/**
	 * Method for text posts to Vkontakte.
	 *
	 * @since  8.6.0
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $hashtags Hashtags.
	 * @param array $args Optional arguments needed by the method.
	 * @param int   $owner_id The owner id.
	 *
	 * @return array $new_post The post contents
	 */
	private function vk_text_post( $post_details, $hashtags, $args, $owner_id ) {

		$new_post = array(
			'owner_id' => $owner_id,
			'friends_only' => 0,
			'message' => $post_details['content'] . $hashtags,
		);

		return $new_post;

	}

	/**
	 * Method for creating link(article) posts to Vkontakte.
	 *
	 * @since  8.6.0
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $hashtags Hashtags.
	 * @param array $args Optional arguments needed by the method.
	 *
	 * @return object
	 */
	private function vk_article_post( $post_details, $hashtags, $args, $owner_id ) {

		$new_post = array(
			'owner_id' => $owner_id,
			'friends_only' => 0,
			'message' => $post_details['content'] . $hashtags,
			'attachments' => $this->get_url( $post_details ),
		);

		if ( ! empty( $this->share_link_text ) ) {
			unset( $new_post['attachments'] );
		}
		return $new_post;

	}

	/**
	 * Method for publishing with Vkontakte service.
	 *
	 * @since  8.6.0
	 * @access public
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {

		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		$post_id = $post_details['post_id'];

		$client = new VKApiClient();
		$access_token = $args['credentials']['access_token'];
		$owner_id = ( $args['is_company'] ) ? '-' . $args['id'] : $args['id'];

		$post_url = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];

		$model       = new Rop_Post_Format_Model;
		$post_format = $model->get_post_format( $post_details['account_id'] );

		if ( ! empty( $post_format['share_link_in_comment'] ) && ! empty( $post_format['share_link_text'] ) ) {
			$this->share_link_text = str_replace( '{link}', self::get_url( $post_details ), $post_format['share_link_text'] );
		}

		$hashtags = $post_details['hashtags'];

		if ( ! empty( $post_format['hashtags_randomize'] ) && $post_format['hashtags_randomize'] ) {
			$hashtags = $this->shuffle_hashtags( $hashtags );
		}

		// VK link post
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$new_post = $this->vk_article_post( $post_details, $hashtags, $args, $owner_id );
		}

		// VK plain text post
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$new_post = $this->vk_text_post( $post_details, $hashtags, $args, $owner_id );
		}

		// VK media post
		if ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {
			$new_post = $this->vk_media_post( $post_details, $hashtags, $args, $owner_id, $client, $access_token );
		}

		if ( empty( $new_post ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'misc.no_post_data' ) );
			return false;
		}

		$response = $client->wall()->post(
			$args['credentials']['access_token'],
			$new_post
		);

		if ( ! empty( $response['post_id'] ) ) {
			// Create the first comment if the share link text is not empty.
			if ( ! empty( $this->share_link_text ) ) {
				$create_comment = $client->wall()->createComment(
					$args['credentials']['access_token'],
					array(
						'post_id' => $response['post_id'],
						'message' => $this->share_link_text,
					)
				);
				$this->logger->info( sprintf( '[VK API] Response: %s', json_encode( $create_comment ) ) );

				if ( $create_comment && ! empty( $create_comment['post_id'] ) ) {
					$this->logger->info(
						sprintf(
							'Successfully shared first comment to %s on %s ',
							html_entity_decode( get_the_title( $post_details['post_id'] ) ),
							$post_details['service']
						)
					);
				}
			}

			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on Vkontakte ',
					html_entity_decode( get_the_title( $post_details['post_id'] ) ),
					$args['user']
				)
			);

			return true;

		} else {

			$this->logger->alert_error( 'Error sharing to Vkontakte' . print_r( $response, true ) );
			return false;
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
		$account['link'] = sprintf( 'https://vk.com/id%s', $account['id'] );
		return $account;
	}

}
