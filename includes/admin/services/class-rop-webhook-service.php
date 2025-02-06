<?php
/**
 * The file that defines the Webhook Service specifics.
 *
 * A class that is used to interact with Twitter.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      9.1.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Webhook_Service
 *
 * @since   9.1.0
 * @link    https://themeisle.com/
 */
class Rop_Webhook_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   9.1.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'webhook';


	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   9.1.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Webhook';
	}

	/**
	 * Method to register credentials for the service.
	 *
	 * @since   9.1.0
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
	 * @since   9.1.0
	 * @access  public
	 * @return mixed
	 */
	public function get_service() {
		return $this->service;
	}

	/**
	 * Method for publishing with Twitter service.
	 *
	 * @since   9.1.0
	 * @access  public
	 *
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {

		$log_account_used = '[' . $this->credentials['user'] . '] ';
		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( $log_account_used . Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		$url = isset( $this->credentials['url'] ) ? $this->credentials['url'] : '';

		if ( empty( $url ) ) {
			$this->logger->alert_error( $log_account_used . Rop_I18n::get_labels( 'sharing.webhook_url_not_set' ) );
			return false;
		}

		$headers = array();

		if ( ! empty( $this->credentials['headers'] ) && is_array( $this->credentials['headers'] ) ) {
			$headers = array_map(
				function( $header ) {
					if ( ! is_string( $header ) || false === strpos( $header, ':' ) ) {
						  return false;
					}

					list( $key, $value ) = explode( ':', $header );

					$key   = sanitize_text_field( $key );
					$value = sanitize_text_field( $value );
					return array( $key => $value );
				},
				$this->credentials['headers']
			);

			$headers = array_filter(
				$headers,
				function( $header ) {
					return $header !== false && is_array( $header );
				}
			);
		}

		$args = array(
			'headers' => apply_filters( 'rop_webhook_headers', $headers ),
		);

		$payload = array(
			'postId'        => isset( $post_details['post_id'] ) ? $post_details['post_id'] : '',
			'message'       => isset( $post_details['content'] ) ? $post_details['content'] : '',
			'postUrl'       => isset( $post_details['post_url'] ) ? $post_details['post_url'] : '',
			'featuredImage' => isset( $post_details['post_image'] ) ? $post_details['post_image'] : '',
		);

		if ( ! class_exists( 'ROP_Pro_Webhook_Helper' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			if ( is_plugin_active( 'tweet-old-post-pro/tweet-old-post-pro.php' ) ) {
				require_once ROP_PRO_PATH . 'includes/helpers/class-rop-pro-webhook-helper.php';
			}
		}

		if ( class_exists( 'ROP_Pro_Webhook_Helper' ) ) {
			$response = ROP_Pro_Webhook_Helper::send_webhook_payload( $this->credentials['url'], $payload, $args );
		} else {
			$this->logger->alert_error( $log_account_used . Rop_I18n::get_labels( 'sharing.webhook_extension_not_found' ) );
			return false;
		}

		if ( is_wp_error( $response ) ) {
			$this->logger->alert_error( $log_account_used . Rop_I18n::get_labels( 'errors.webhook_error' ) . $response->get_error_message() );
			return false;
		} elseif ( true === $response ) {
			// Save log.
			$this->save_logs_on_rop(
				array(
					'network' => $this->service_name,
					'handle'  => $this->display_name,
					'content' => $post_details['content'],
					'link'    => $post_details['post_url'],
				)
			);

			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on %s ',
					html_entity_decode( $post_details['title'], ENT_QUOTES ),
					$this->display_name,
					$this->service_name
				)
			);
		}

		return false;
	}

	/**
	 * Add the webhook to the service data.
	 *
	 * @since   9.1.0
	 * @access  public
	 *
	 * @param   array $data The webhook data.
	 *
	 * @return  bool
	 */
	public function add_webhook( $data ) {
		if ( empty( $data['url'] ) ) {
			return false;
		}

		$id           = empty( $data['id'] ) ? hash( 'sha1', $data['url'] . time() ) : $data['id'];
		$display_name = ! empty( $data['display_name'] ) ? $data['display_name'] : 'Webhook';
		$account_id   = hash( 'md5', $data['url'] );

		$this->service = array(
			'id'          => $id,
			'service'     => $this->service_name,
			'credentials' => array(
				'url'          => $data['url'],
				'headers'      => ! empty( $data['headers'] ) && is_array( $data['headers'] ) ? $data['headers'] : array(),
				'display_name' => $display_name,
			),
			'available_accounts' => array(
				$this->service_name . '_' . $id . '_' . $account_id => array(
					'id'      => $account_id,
					'user'    => $display_name,
					'service' => $this->service_name,
					'account' => $this->normalize_string( $data['url'] ),
					'created' => date( 'd/m/Y H:i' ),
					'active'  => isset( $data['active'] ) ? $data['active'] : true,
				),
			),
		);

		return true;
	}


	/**
	 * Expose the endpoints for the webhook service.
	 *
	 * @since 9.1.0
	 * @access public
	 * @return void
	 */
	public function expose_endpoints() {
	}

	/**
	 * Retrieve the API object.
	 *
	 * @since 9.1.0
	 * @access public
	 * @return mixed The API object.
	 */
	public function get_api() {
	}

	/**
	 * Define the API object.
	 *
	 * @since 9.1.0
	 * @access public
	 * @return void
	 */
	public function set_api() {
	}

	/**
	 * Populate additional data for the account.
	 *
	 * @since 9.1.0
	 * @access public
	 * @param mixed $account The account data.
	 * @return mixed The populated account data.
	 */
	public function populate_additional_data( $account ) {
		return $account;
	}

	/**
	 * Check if authentication is needed.
	 *
	 * @since 9.1.0
	 * @access public
	 * @return void
	 */
	public function maybe_authenticate() {
	}

	/**
	 * Authenticate the user with the given arguments.
	 *
	 * @since 9.1.0
	 * @access public
	 * @param mixed $args The arguments for authentication.
	 * @return void
	 */
	public function authenticate( $args ) {
	}

	/**
	 * Request an API token.
	 *
	 * @since 9.1.0
	 * @access public
	 * @return mixed The API token.
	 */
	public function request_api_token() {
	}
}
