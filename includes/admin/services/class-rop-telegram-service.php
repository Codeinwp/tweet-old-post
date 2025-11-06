<?php
/**
 * The file that defines the Telegram Service specifics.
 *
 * NOTE: Extending abstract class but not making use of some of the methods with new authentication workflow.
 *           Abstract class will be cleaned up once we move all services to one click sign on and drop users connecting own apps.
 *
 * A class that is used to interact with  Telegram
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      9.1.3
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

use Telegram\Bot\Api;

/**
 * Class Rop_Telegram_Service
 *
 * @since   9.1.3
 * @link    https://themeisle.com/
 */
class Rop_Telegram_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since  9.1.3
	 * @access protected
	 * @var    string $service_name The service name.
	 */
	protected $service_name = 'telegram';

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since  9.1.3
	 * @access public
	 */
	public function init() {
		$this->display_name = 'Telegram';
	}

	/**
	 * Returns information for the current service.
	 *
	 * @since  9.1.3
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
	 * @since  9.1.3
	 * @access public
	 */
	public function expose_endpoints() {
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
		$this->credentials = $args;
	}

	/**
	 * Abstract function, not in Use. Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since  9.1.3
	 * @access protected
	 * @return mixed
	 */
	public function request_api_token() {
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   9.1.3
	 * @access  public
	 *
	 * @param string $token The Telegram Secret token. Default empty.
	 * @param bool   $async Async request. Default false.
	 *
	 * @return Telegram\Bot\Api
	 */
	public function get_api( $token = '', $async = false ) {
		if ( null === $this->api ) {
			$this->set_api( $token, $async );
		}

		return $this->api;
	}

	/**
	 * Method to define the api.
	 *
	 * @since   9.1.3
	 * @access  public
	 *
	 * @param string $token The Telegram Secret token. Default empty.
	 * @param bool   $async Async request. Default false.
	 *
	 * @return mixed
	 */
	public function set_api( $token = '', $async = false ) {
		try {
			if ( empty( $token ) ) {
				return false;
			}
			$this->api = new Rop_Telegram_Api( $token, $this->logger );
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Can not load Telegram api. Error: ' . $e->getMessage() );
		}
	}

	/**
	 * Abstract function, not in Use. Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since  9.1.3
	 * @access public
	 * @return mixed
	 */
	public function maybe_authenticate() {
	}

	/**
	 * Abstract function, not in Use. Method to authenticate an user based on provided credentials.
	 * Used in DB upgrade.
	 *
	 * @param array $args The arguments for service auth.
	 */
	public function authenticate( $args = array() ) {
	}

	/**
	 * This method will load and prepare the account data for Telegram user.
	 * Used in Rest Api.
	 *
	 * @since   9.1.3
	 * @access  public
	 *
	 * @param   array $data Buffer accounts data.
	 *
	 * @throws \Telegram\Bot\Exceptions\TelegramSDKException Telegram API empry response error.
	 * @return  bool
	 */
	public function add_account_with_app( $data ) {

		if ( empty( $data['chat_id'] ) || empty( $data['access_token'] ) ) {
			return false;
		}
		try {
			$telegram = $this->get_api( $data['access_token'] );
			$response = $telegram->get_user_accounts();
			if ( empty( $response->id ) ) {
				throw new Exception( 'Telegram API Error: ' . wp_json_encode( $response ) );
			}

			$id            = $response->id;
			$display_name  = $response->first_name;
			$account       = $response->username;
			$this->service = array(
				'id'                 => $id,
				'service'            => $this->service_name,
				'credentials'        => array(
					'chat_id'      => $data['chat_id'],
					'access_token' => $data['access_token'],
					'display_name' => $display_name,
				),
				'available_accounts' => array(
					$this->service_name . '_' . $id => array(
						'id'      => $id,
						'user'    => $display_name,
						'account' => $account,
						'service' => $this->service_name,
						'img'     => apply_filters( 'rop_custom_telegram_avatar', $telegram->get_profile_photo( $id ) ),
						'created' => gmdate( 'd/m/Y H:i' ),
						'active'  => isset( $data['active'] ) ? $data['active'] : true,
					),
				),
			);
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Telegram API Error: ' . $e->getMessage() );
			return false;
		}
		return true;
	}

	/**
	 * Method for creating media posts on Telegram.
	 *
	 * @since  9.1.3
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $hashtags Hashtags.
	 * @param array $args Optional arguments needed by the method.
	 * @param int   $chat_id The chat id.
	 *
	 * @return array $new_post The post contents
	 */
	private function tlg_media_post( $post_details, $hashtags, $args, $chat_id ) {

		$attachment_url = $post_details['post_image'];
		// If the post has no image but "Share as image post" is checked
		// share as an article post.
		if ( empty( $attachment_url ) ) {
			$this->logger->info( 'No image set for post, but "Share as Image Post" is checked. Falling back to article post' );
			return $this->tlg_article_post( $post_details, $hashtags, $args, $owner_id );
		}

		$new_post = array(
			'chat_id' => $chat_id,
			'caption' => $post_details['content'] . $this->get_url( $post_details ) . $hashtags,
		);

		// If attachment is video.
		if ( strpos( $post_details['mimetype']['type'], 'video' ) !== false ) {
			$new_post['video'] = $attachment_url;
		} else {
			$new_post['photo'] = $attachment_url;
		}
		return $new_post;
	}

	/**
	 * Method for text posts to Telegram.
	 *
	 * @since  9.1.3
	 * @access private
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param array  $hashtags Hashtags.
	 * @param array  $args Optional arguments needed by the method.
	 * @param string $chat_id arguments needed by the method.
	 *
	 * @return array $new_post The post contents
	 */
	private function tlg_text_post( $post_details, $hashtags, $args, $chat_id ) {

		$new_post = array(
			'chat_id' => $chat_id,
			'text'    => $post_details['content'] . $hashtags,
		);

		return $new_post;
	}

	/**
	 * Method for creating link(article) posts to Telegram.
	 *
	 * @since  9.1.3
	 * @access private
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param array  $hashtags Hashtags.
	 * @param array  $args arguments needed by the method.
	 * @param string $chat_id arguments needed by the method.
	 *
	 * @return object
	 */
	private function tlg_article_post( $post_details, $hashtags, $args, $chat_id ) {

		$content  = sprintf( "%s\r\n%s\r\n%s", $post_details['content'], $this->get_url( $post_details ), $hashtags );
		$new_post = array(
			'chat_id'    => $chat_id,
			'text'       => $content,
			'parse_mode' => 'HTML',
		);

		return $new_post;
	}

	/**
	 * Method for publishing with Telegram service.
	 *
	 * @since  9.1.3
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

		$post_id      = $post_details['post_id'];
		$chat_id      = $args['credentials']['chat_id'];
		$access_token = $args['credentials']['access_token'];

		$post_url            = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];

		$model       = new Rop_Post_Format_Model();
		$post_format = $model->get_post_format( $post_details['account_id'] );

		$hashtags = $post_details['hashtags'];

		if ( ! empty( $post_format['hashtags_randomize'] ) && $post_format['hashtags_randomize'] ) {
			$hashtags = $this->shuffle_hashtags( $hashtags );
		}

		// Telegram link post.
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$new_post = $this->tlg_article_post( $post_details, $hashtags, $args, $chat_id );
		}

		// Telegram plain text post.
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$new_post = $this->tlg_text_post( $post_details, $hashtags, $args, $chat_id );
		}

		// Telegram media post.
		if ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {
			$new_post = $this->tlg_media_post( $post_details, $hashtags, $args, $chat_id );
		}

		if ( empty( $new_post ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'misc.no_post_data' ) );
			return false;
		}

		$api = $this->get_api( $access_token );
		try {
			$response = $api->send_message( $new_post );
			if ( $response && $response->message_id ) {
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
						'Successfully shared %s to %s on Telegram ',
						html_entity_decode( get_the_title( $post_details['post_id'] ) ),
						$args['user']
					)
				);

				return true;
			}
		} catch ( \Telegram\Bot\Exceptions\TelegramSDKException $e ) {
			$this->logger->alert_error( 'Error sharing to Telegram: ' . $e->getMessage() );
			return false;
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Error sharing to Telegram: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Method to populate additional data.
	 *
	 * @since   9.1.3
	 * @access  public
	 * @return mixed
	 */
	public function populate_additional_data( $account ) {
		return $account;
	}
}
