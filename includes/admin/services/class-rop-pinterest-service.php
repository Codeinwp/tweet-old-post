<?php
/**
 * The file that defines the Pinterest Service specifics.
 *
 * A class that is used to interact with Pinterest.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Pinterest_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Pinterest_Service extends Rop_Services_Abstract {

	/**
	 * An instance of authenticated Pinterest user.
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
	protected $service_name = 'pinterest';
	/**
	 * Defines the service permissions needed.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $permissions The Pinterest required permissions.
	 */
	private $permissions = array( 'read_public', 'write_public' );

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Pinterest';
	}

	/**
	 * Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since   8.0.0
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
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function authorize() {
		header( 'Content-Type: text/html' );
		if ( ! session_id() ) {
			session_start();
		}

		$this->request_api_token();

		parent::authorize();
		// echo '<script>window.setTimeout("window.close()", 500);</script>';
	}

	/**
	 * Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @return mixed
	 */
	public function request_api_token() {
		$credentials = $_SESSION['rop_pinterest_credentials'];

		$api = $this->get_api( $credentials['app_id'], $credentials['secret'] );

		if ( isset( $_GET['code'] ) ) {
			$token = $api->auth->getOAuthToken( $_GET['code'] );
			$api->auth->setOAuthToken( $token->access_token );
			$_SESSION['rop_pinterest_token'] = $token->access_token;
		}
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $app_id The Pinterest APP ID. Default empty.
	 * @param   string $secret The Pinterest APP Secret. Default empty.
	 *
	 * @return \Facebook\Facebook
	 */
	public function get_api( $app_id = '', $secret = '' ) {
		if ( $this->api == null ) {
			$this->set_api( $app_id, $secret );
		}

		return $this->api;
	}

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $app_id The Pinterest APP ID. Default empty.
	 * @param   string $secret The Pinterest APP Secret. Default empty.
	 *
	 * @return mixed
	 */
	public function set_api( $app_id = '', $secret = '' ) {
		try {
			if ( empty( $app_id ) || empty( $secret ) ) {
				return false;
			}

			$this->api = new DirkGroenen\Pinterest\Pinterest( $this->strip_whitespace( $app_id ), $this->strip_whitespace( $secret ) );
		} catch ( Exception $exception ) {
			$this->logger->alert_error( 'Can not load Pinterest api. Error: ' . $exception->getMessage() );
		}
	}

	/**
	 * Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function maybe_authenticate() {
		if ( ! session_id() ) {
			session_start();
		}
		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_pinterest_token',
				'rop_pinterest_credentials',
			)
		) ) {
			return false;
		}

		if ( ! $this->is_set_not_empty(
			$_SESSION['rop_pinterest_credentials'],
			array(
				'app_id',
				'secret',
			)
		) ) {
			return false;
		}
		$credentials = $_SESSION['rop_pinterest_credentials'];
		$token       = $_SESSION['rop_pinterest_token'];

		$credentials['token'] = $token;
		unset( $_SESSION['rop_pinterest_credentials'] );
		unset( $_SESSION['rop_pinterest_token'] );

		return $this->authenticate( $credentials );

	}

	/**
	 * Method to authenticate an user based on provided credentials.
	 * Used in DB upgrade.
	 *
	 * @param array $args The arguments for facebook service auth.
	 *
	 * @return bool
	 */
	public function authenticate( $args = array() ) {
		if ( ! $this->is_set_not_empty(
			$args,
			array(
				'app_id',
				'secret',
				'token',
			)
		) ) {
			return false;
		}

		$app_id = $args['app_id'];
		$secret = $args['secret'];
		$token  = $args['token'];

		$api = $this->get_api( $app_id, $secret );
		$this->set_credentials(
			array(
				'app_id' => $app_id,
				'secret' => $secret,
				'token'  => $token,
			)
		);

		$api->auth->setOAuthToken( $token );
		$user = $api->users->me(
			array(
				'fields' => 'username,first_name,last_name,image[small]',
			)
		);

		$this->service = array(
			'id'                 => $user->id,
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'public_credentials' => array(
				'app_id' => array(
					'name'    => 'APP ID',
					'value'   => $this->credentials['app_id'],
					'private' => false,
				),
				'secret' => array(
					'name'    => 'APP Secret',
					'value'   => $this->credentials['secret'],
					'private' => true,
				),
			),
			'available_accounts' => $this->get_boards( $api, $user ),
		);

		return true;

	}

	/**
	 * Method to register credentials for the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $args The credentials array.
	 */
	public function set_credentials( $args ) {
		$this->credentials = $args;
	}

	/**
	 * Gets the details of all boards.
	 *
	 * @param object $api The api object.
	 * @param object $user The user object.
	 */
	private function get_boards( $api, $user ) {
		$user_boards = array();
		$boards      = $api->users->getMeBoards(
			array(
				'fields' => 'name',
			)
		);

		foreach ( $boards as $board ) {
			$board_details            = array();
			$board_details['id']      = $user->username . '/' . str_replace( ' ', '-', $board->name );
			$board_details['account'] = $this->normalize_string( sprintf( '%s %s', $user->first_name, $user->last_name ) );
			$board_details['user']    = $this->normalize_string( $board->name );
			$board_details['active']  = false;
			$board_details['service'] = $this->service_name;
			$img                      = '';
			if ( is_array( $user->image['small'] ) && ! empty( $user->image['small']['url'] ) ) {
				$img = $user->image['small']['url'];
			}
			$board_details['img']     = $img;
			$board_details['created'] = $this->user_default['created'];
			$user_boards[]            = $board_details;
		}

		return $user_boards;
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
	 * Generate the sign in URL.
	 *
	 * @since   8.0.0
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

		$_SESSION['rop_pinterest_credentials'] = $credentials;

		$api = $this->get_api( $credentials['app_id'], $credentials['secret'] );
		$url = $api->auth->getLoginUrl( $this->get_legacy_url( $this->service_name ), $this->permissions );

		return $url;
	}

	/**
	 * Method for publishing with Facebook service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {
		if ( Rop_Admin::rop_site_is_staging() ) {
			return false;
		}
		$post_id = $post_details['post_id'];
		$this->set_api(
			$this->credentials['app_id'],
			$this->credentials['secret']
		);

		$api = $this->get_api();
		$api->auth->setOAuthToken( $args['credentials']['token'] );

		// Check if image is present.
		if ( empty( $post_details['post_image'] ) ) {
			$this->logger->alert_error( sprintf( 'No image present in %s to pin to %s for %s', html_entity_decode( get_the_title( $post_details['post_id'] ) ), $args['id'], $post_details['service'] ) );

			return false;
		}

		if ( strpos( $post_details['mimetype']['type'], 'image' ) === false ) {

			$this->logger->alert_error( sprintf( 'No valid image present in %s to pin to %s for %s', html_entity_decode( get_the_title( $post_details['post_id'] ) ), $args['id'], $post_details['service'] ) );

			return false;
		}

		$pin = $api->pins->create(
			array(
				'note'      => $this->strip_excess_blank_lines( $post_details['content'] ) . $post_details['hashtags'],
				'image_url' => $post_details['post_image'],
				'board'     => $args['id'],
			)
		);

		if ( empty( $pin ) ) {
			$this->logger->alert_error( sprintf( 'Unable to pin to %s for %s', $args['id'], $post_details['service'] ) );

			return false;
		}

		$this->logger->alert_success(
			sprintf(
				'Successfully pinned %s in %s to %s on %s',
				basename( $post_details['post_image'] ),
				html_entity_decode( get_the_title( $post_id ) ),
				$args['id'],
				$post_details['service']
			)
		);

		return true;
	}

}
