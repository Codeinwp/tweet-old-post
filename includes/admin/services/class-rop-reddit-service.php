<?php
/**
 * The file that defines the Reddit Service specifics.
 *
 * A class that is used to interact with Reddit.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Reddit_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Reddit_Service extends Rop_Services_Abstract {

	/**
	 * An instance of authenticated Reddit user.
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
	protected $service_name = 'reddit';
	/**
	 * Permissions required by the app.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     array $scopes The scopes to authorize with Reddit.
	 */
	protected $scopes = array( 'identity', 'account', 'mysubreddits', 'submit' );


	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Reddit';
	}

	/**
	 * Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
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

		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_reddit_credentials',
			)
		) ) {
			return false;
		}

		$credentials = $_SESSION['rop_reddit_credentials'];

		$api         = $this->get_api( $credentials['client_id'], $credentials['secret'] );
		$access_token = $api->getAccessToken();

		$_SESSION['rop_reddit_token'] = $access_token['token'];
		$_SESSION['rop_reddit_token_type'] = $access_token['type'];

		parent::authorize();
		// echo '<script>window.setTimeout("window.close()", 500);</script>';
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $client_id The Client ID. Default empty.
	 * @param   string $client_secret The Client Secret. Default empty.
	 *
	 * @return \Reddit\Client Client Reddit.
	 */
	public function get_api( $client_id = '', $client_secret = '' ) {
		if ( $this->api == null ) {
			$this->set_api( $client_id, $client_secret );
		}

		return $this->api;
	}

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $client_id The Client ID. Default empty.
	 * @param   string $client_secret The Client Secret. Default empty.
	 *
	 * @return mixed
	 */
	public function set_api( $client_id = '', $client_secret = '' ) {
		require_once( ROP_PRO_DIR_PATH . '/vendor/reddit/reddit.php' );
		$this->api = new reddit( $this->strip_whitespace( $client_id ), $this->strip_whitespace( $client_secret ), $this->get_legacy_url(), implode( ',', $this->scopes ) );
	}

	/**
	 * Method for maybe authenticate the service.
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
				'rop_reddit_credentials',
				'rop_reddit_token',
				'rop_reddit_token_type',
			)
		) ) {
			return false;
		}

		if ( ! $this->is_set_not_empty(
			$_SESSION['rop_reddit_credentials'],
			array(
				'client_id',
				'secret',
			)
		) ) {
			return false;
		}

		$credentials          = $_SESSION['rop_reddit_credentials'];
		$token                = $_SESSION['rop_reddit_token'];
		$type                = $_SESSION['rop_reddit_token_type'];
		$credentials['token'] = $token;
		$credentials['type'] = $type;

		unset( $_SESSION['rop_reddit_credentials'] );
		unset( $_SESSION['rop_reddit_token'] );
		unset( $_SESSION['rop_reddit_token_type'] );

		return $this->authenticate( $credentials );
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
	public function authenticate( $args ) {
		if ( ! $this->is_set_not_empty(
			$args,
			array(
				'client_id',
				'token',
				'type',
				'secret',
			)
		) ) {
			return false;
		}

		$token = $args['token'];
		$type = $args['type'];

		$api = $this->get_api( $args['client_id'], $args['secret'] );

		$this->credentials['token']     = $token;
		$this->credentials['type']     = $type;
		$this->credentials['client_id'] = $args['client_id'];
		$this->credentials['secret']    = $args['secret'];

		$api->setAccessToken( $args['type'], $args['token'] );

		$profile = null;
		try {
			$profile = (array) $api->getUser();
		} catch ( Exception $e ) {
			$this->logger->alert_error( 'Can not get reddit user details. Error ' . $e->getMessage() );
		}
		if ( ! isset( $profile['subreddit'] ) ) {
			return false;
		}

		$profile    = (array) $profile['subreddit'];
		$this->service = array(
			'id'                 => $this->strip_underscore( $profile['display_name'] ),
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'public_credentials' => array(
				'client_id' => array(
					'name'    => 'Client ID',
					'value'   => $this->credentials['client_id'],
					'private' => false,
				),
				'secret'    => array(
					'name'    => 'Client Secret',
					'value'   => $this->credentials['secret'],
					'private' => true,
				),
			),
			'available_accounts' => $this->get_subreddits( $api ),
		);

		return true;

	}

	/**
	 * Utility method to retrieve subreddits.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   3.5.0
	 * @access  public
	 *
	 * @param   object $api The API object.
	 *
	 * @return array
	 */
	private function get_subreddits( $api ) {
		$subscriptions  = array();
		$subreddits = $api->getSubreddits( 'subscriber' );
		if ( isset( $subreddits->data ) ) {
			foreach ( $subreddits->data->children as $child ) {
				$subscriptions[] = array(
					'id'    => $child->data->id,
					'user'  => $this->normalize_string( $child->data->display_name ),
					'account'   => $this->normalize_string( $child->data->display_name ),
					'img'   => $child->data->icon_img,
					'access_token'  => $api->getAccessToken( true ),
					'active'    => false,
					'service'   => $this->service_name,
					'created'   => current_time( 'Y-m-d' ),
				);
			}
		}
		return $subscriptions;
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
	 * Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @return mixed
	 */
	public function request_api_token() {
		if ( ! session_id() ) {
			session_start();
		}

		$api           = $this->get_api();
		$request_token = $api->oauth( 'oauth/request_token', array( 'oauth_callback' => $this->get_legacy_url( 'linkedin' ) ) );

		$_SESSION['rop_twitter_request_token'] = $request_token;

		return $request_token;
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
		// @codeCoverageIgnoreStart
		if ( ! session_id() ) {
			session_start();
		}
		// @codeCoverageIgnoreEnd
		$_SESSION['rop_reddit_credentials'] = $credentials;
		$this->set_api( $credentials['client_id'], $credentials['secret'] );
		$api = $this->get_api();
		$url = $api->getLoginUrl();

		return $url;
	}

	/**
	 * Method for publishing with Twitter service.
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

		$this->set_api( $this->credentials['client_id'], $this->credentials['secret'] );
		$api   = $this->get_api();
		$api->setAccessToken( $this->credentials['type'], $this->credentials['token'] );
		$api->setLogger( $this->logger );
		$response = $api->createStory(
			html_entity_decode( get_the_title( $post_details['post_id'] ) ),
			$url_to_share = $this->get_url( $post_details ),
			$this->unstrip_underscore( $args['id'] )
		);

		try {
			if ( empty( $response ) ) {
				$this->logger->alert_success(
					sprintf(
						'Successfully shared %s to %s on %s ',
						html_entity_decode( get_the_title( $post_details['post_id'] ) ),
						$args['user'],
						$post_details['service']
					)
				);
			} else {
				$this->logger->alert_error( 'Can not share to reddit. Error:  ' . print_r( $response, true ) );
			}
		} catch ( Exception $exception ) {
			$this->logger->alert_error( 'Can not share to reddit. Error:  ' . $exception->getMessage() );

			return false;
		}

		return true;
	}
}
