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
class Rop_Buffer_Service extends Rop_Services_Abstract {

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
	protected $service_name = 'buffer';
	/**
	 * Defines the service permissions needed.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $permissions The Pinterest required permissions.
	 */
	//private $permissions = array( 'read_public', 'write_public' );

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Buffer';
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

		//$this->request_api_token();

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
		/*if ( ! $this->is_set_not_empty(
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
		unset( $_SESSION['rop_pinterest_token'] );*/

		//return $this->authenticate( $credentials );
		return $this->authenticate();

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

	/*	if ( ! $this->is_set_not_empty(
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
*/
		$this->service = array(
			'id'                 => '21782', //add unique account ID
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
			'available_accounts' => $this->get_profiles(),
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

	public function get_profiles($token = ''){

		if ( ! class_exists( '\GuzzleHttp\Client' ) ) {
							return ;
			}

			$buffer_profiles = array();

			$guzzle = new \GuzzleHttp\Client();
			$response = $guzzle->request('GET', 'https://api.bufferapp.com/1/profiles.json', ['query' => ['access_token' => '']]);

			$response_body = (string) $response->getBody();
			$response_body = json_decode($response_body , true);

	        $ids = array();

	        foreach( $profiles_arr as $profile_ids){

	            $ids[] = $profile_ids['id'];

	        }

					foreach ( $response_body as $response_field ) {
						$buffer_profile          = array();
						$buffer_profile['id']      = $response_field['id'];
						$buffer_profile['account'] = $response_field['formatted_username'];
					  $buffer_profile['user']    = $response_field['formatted_service'] . ' - ' . $response_field['formatted_username'];;
						$buffer_profile['active']  = false;
						$buffer_profile['service'] = $this->service_name;

						$buffer_profile['img']     = $response_field['avatar_https'];
						$buffer_profile['created'] = date("Y-m-d H:i:s", substr($response_field['created_at'], 0, 10));
						$buffer_profiles[]            = $buffer_profile;
					}

					return $buffer_profiles;

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

    // TODO might not need this
		$_SESSION['rop_buffer_credentials'] = $credentials;

    $url = 'https://bufferapp.com/oauth2/authorize?client_id='.$credentials['client_id'].'&redirect_uri='.admin_url('/admin.php?page=TweetOldPost').'&response_type=code&state=buffer';
		return $url;
	}

  public function get_buffer_access_token(){
		$code = urldecode($_GET['code']);
		if (empty($code)){
		    return;
		}

			 if ( ! class_exists( '\GuzzleHttp\Client' ) ) {
							 return ;
}

	 $guzzle = new \GuzzleHttp\Client();
	 $response = $guzzle->request('POST', 'https://api.bufferapp.com/1/oauth2/token.json', [
	 'form_params' => [
			 'client_id' => '',
			 'client_secret' => '',
			 'redirect_uri' => 'https://ecom.uriahsvictor.com/wp-admin/admin.php?page=TweetOldPost',
			 'code' => $code,
			 'grant_type' => 'authorization_code',
	 ]
]);

$json = (string) $response->getBody();

$json_arr = json_decode($json, true);
$access_token = $json_arr['access_token'];

return $access_token;
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

		// Don't shorten post link, pinterest might reject post if shortened and it also looks bad on pinterest with a shortlink
		$pin = $api->pins->create(
			array(
				'note'      => $this->strip_excess_blank_lines( $post_details['content'] ) . $post_details['hashtags'],
				'image_url' => $post_details['post_image'],
				'board'     => $args['id'],
				'link'     => $post_details['post_url'],
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
