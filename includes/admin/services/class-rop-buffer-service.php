<?php
/**
 * The file that defines the Buffer Service specifics.
 *
 * A class that is used to interact with Pinterest.
 * It extends the Rop_Services_Abstract class.
 *
 * @link  https://revive.social/
 * @since 8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Pinterest_Service
 *
 * @since 8.0.0
 * @link  https://revive.social/
 */
class Rop_Buffer_Service extends Rop_Services_Abstract {


	/**
	 * An instance of authenticated Pinterest user.
	 *
	 * @since  8.0.0
	 * @access private
	 * @var    array $user An instance of the current user.
	 */
	public $user;
	/**
	 * Defines the service name in slug format.
	 *
	 * @since  8.0.0
	 * @access protected
	 * @var    string $service_name The service name.
	 */
	protected $service_name = 'buffer';
	/**
	 * Defines the service permissions needed.
	 *
	 * @since  8.0.0
	 * @access private
	 * @var    array $permissions The Pinterest required permissions.
	 */

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since  8.0.0
	 * @access public
	 */
	public function init() {
		$this->display_name = 'Buffer';
	}

	/**
	 * Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since  8.0.0
	 * @access public
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
	 * @since  8.0.0
	 * @access public
	 * @return mixed
	 */
	public function authorize() {
		header( 'Content-Type: text/html' );
		if ( ! session_id() ) {
			session_start();
		}

		parent::authorize();
	}

	/**
	 * Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since  8.0.0
	 * @access protected
	 * @return mixed
	 */
	public function request_api_token() {
		return;
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since  8.0.0
	 * @access public
	 *
	 * @param string $app_id The Pinterest APP ID. Default empty.
	 * @param string $secret The Pinterest APP Secret. Default empty.
	 *
	 * @return null abstract method not used for this service specifically.
	 */
	public function get_api( $app_id = '', $secret = '' ) {
		return;
	}

	/**
	 * Method to define the api.
	 *
	 * @since  8.0.0
	 * @access public
	 *
	 * @param string $app_id The Pinterest APP ID. Default empty.
	 * @param string $secret The Pinterest APP Secret. Default empty.
	 *
	 * @return mixed
	 */
	public function set_api( $app_id = '', $secret = '' ) {
		return;
	}

	/**
	 * Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since  8.0.0
	 * @access public
	 * @return mixed
	 */
	public function maybe_authenticate() {
		if ( ! session_id() ) {
			session_start();
		}

		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_buffer_credentials',
			)
		)
		) {
			return false;
		}

		$token = $_SESSION['rop_buffer_credentials'];
		$credentials['token'] = $token;

		unset( $_SESSION['rop_buffer_credentials'] );
		return $this->authenticate( $credentials );

	}

	/**
	 * Method to authenticate an user based on provided credentials.
	 * Used in DB upgrade.
	 *
	 * @param array $args The arguments for buffer service auth.
	 *
	 * @return bool
	 */
	public function authenticate( $args = array() ) {

		$token = $args['token'];

		$url = 'https://api.bufferapp.com/1/user.json?access_token=' . $token;

		$response = wp_remote_get( $url );
		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $response['id'] ) ) {
			  $this->logger->alert_error( 'Buffer error: ' . $response['error'] );
			  return false;
		}

		$this->service = array(
			'id'                 => $response['id'],
			'service'            => $this->service_name,
			'credentials'        => $token,
			'available_accounts' => $this->get_profiles( $token ),
		);

		return true;

	}

	/**
	 * Method to register credentials for the service.
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
	 * Method to get buffer profiles.
	 *
	 * @since  8.3.3
	 * @access public
	 *
	 * @param string $token The access token.
	 *
	 * @return array Array of buffer profiles information.
	 */
	public function get_profiles( $token = '' ) {

		$url = 'https://api.bufferapp.com/1/profiles.json?access_token=' . $token;

		$response = wp_remote_get( $url );
		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $response['error'] ) ) {
			$this->logger->alert_error( 'Buffer error: ' . $response['error'] );
			return false;
		}

		$buffer_profiles = array();

		// Only allow these types of profiles to be added using buffer
		$allowed = array( 'Instagram', 'Facebook Group', 'LinkedIn Page' );

		foreach ( $response as $response_field ) {
			if ( ! in_array( $response_field['formatted_service'], $allowed ) ) {
				 continue;
			}
			$buffer_profile          = array();
			$buffer_profile['id']      = $response_field['id'];
			$buffer_profile['account'] = $response_field['formatted_username'];
			$buffer_profile['user']    = $response_field['formatted_service'] . ' - ' . $response_field['formatted_username'];
			$buffer_profile['active']  = false;
			$buffer_profile['service'] = $this->service_name;

			$buffer_profile['img']     = $response_field['avatar_https'];
			$buffer_profile['created'] = date( 'Y-m-d H:i:s', substr( $response_field['created_at'], 0, 10 ) );
			$buffer_profiles[]            = $buffer_profile;
		}

		return $buffer_profiles;
	}

	/**
	 * Returns information for the current service.
	 *
	 * @since  8.0.0
	 * @access public
	 * @return mixed
	 */
	public function get_service() {
		return $this->service;
	}

	/**
	 * Generate the sign in URL.
	 *
	 * @since  8.0.0
	 * @access public
	 *
	 * @param array $data The data from the user.
	 *
	 * @return mixed
	 */
	public function sign_in_url( $data ) {

		if ( ! session_id() ) {
			session_start();
		}

		$_SESSION['rop_buffer_credentials'] = $data['credentials']['access_token'];

		$url = get_site_url() . '/wp-admin/admin.php?page=TweetOldPost&state=buffer&network=buffer';

		return $url;
	}

	/**
	 * Method for publishing with Buffer service.
	 *
	 * @since  8.0.0
	 * @access public
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $args         Optional arguments needed by the method.
	 *
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {
		if ( Rop_Admin::rop_site_is_staging() ) {
			return false;
		}

		$post_id = $post_details['post_id'];

		if ( get_post_type( $post_id ) !== 'attachment' ) {
			// If post image option unchecked, share as article post
			if ( empty( $post_details['post_image'] ) ) {
				$new_post  = $this->buffer_article_post( $post_details, $args );
			} else {
				$new_post  = $this->buffer_media_post( $post_details, $args );
			}
		} elseif ( get_post_type( $post_id ) === 'attachment' ) {

			// work on video post
			if ( strpos( $post_details['mimetype']['type'], 'video' ) !== false ) {
				   $new_post  = $this->buffer_media_post( $post_details, $args );
			}

			$new_post  = $this->buffer_media_post( $post_details, $args );

		}

		$url = 'https://api.bufferapp.com/1/updates/create.json';

		$response = wp_remote_post(
			$url,
			array(
				'body'    => $new_post,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);

		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response, true );

		if ( $response['success'] === false ) {
			$this->logger->alert_error( 'Buffer error: ' . $response['message'] );
			return false;
		}

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

	/**
	 * Method for sending link posts to buffer.
	 *
	 * @since  8.0.0
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $args         Optional arguments needed by the method.
	 *
	 * @return array
	 */
	private function buffer_article_post( $post_details, $args = array() ) {

		if ( strpos( $args['user'], 'Instagram' ) !== false ) {
			return $this->buffer_media_post( $post_details, $args );
		}

		$data = array(
			'pretty' => 'true',
			'now' => 'true',
			'access_token' => $args['credentials'],
			'profile_ids' => array(
				$args['id'],
			),
			'shorten' => 'false',
			'text' => $post_details['content'] . $post_details['hashtags'],
			'media' => array(
				'link' => trim( $this->get_url( $post_details ) ),
			),

		);

		// Schedule some posts via buffer
		 $value = mt_rand( 0, 6 );

		if ( $value >= 3 ) {
			$data['scheduled_at'] = time() + 60;
		}

		return $data;

	}

	/**
	 * Method for sending media posts to buffer.
	 *
	 * @since  8.0.0
	 * @access private
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $args         Optional arguments needed by the method.
	 *
	 * @return array
	 */
	private function buffer_media_post( $post_details, $args = array() ) {

		$format_helper = new Rop_Post_Format_Helper();

		$img = $format_helper->build_image( $post_details['post_id'] );

		$data = array(
			'pretty' => 'true',
			'now' => 'true',
			'access_token' => $args['credentials'],
			'profile_ids' => array(
				$args['id'],
			),
			'shorten' => 'false',
			'text' => $post_details['content'] . $this->get_url( $post_details ) . $post_details['hashtags'],
			'media' => array(
				'photo' => $img,
			),

		);

		// Schedule some posts via buffer
		$value = mt_rand( 0, 6 );

		if ( $value >= 3 ) {
			$data['scheduled_at'] = time() + 60;
		}

		return $data;
	}

}
