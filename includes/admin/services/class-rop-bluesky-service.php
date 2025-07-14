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
						'user'    => ! empty( $user-> displayName) ? $user->displayName : $user->handle,
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
