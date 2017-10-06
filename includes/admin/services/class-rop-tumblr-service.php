<?php
/**
 * The file that defines the Tumblr Service specifics.
 *
 * A class that is used to interact with Tumblr.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Tumblr_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Tumblr_Service extends Rop_Services_Abstract {

    /**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'tumblr';

    /**
     * Holds the temp data for the authenticated service.
     *
     * @since   8.0.0
     * @access  private
     * @var     array $service The temporary data of the authenticated service.
     */
	private $service = array();

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Tumblr';
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
        $this->register_endpoint( 'authenticate', 'authenticate' );
        $this->register_endpoint( 'test', 'test' );
    }

    public function test() {
        if ( ! session_id() ) {
            session_start();
        }

        $this->credentials = $_SESSION['rop_tumblr_credentials'];
        $this->credentials['oauth_token'] = isset( $_SESSION['rop_tumblr_token']['oauth_token'] ) ? $_SESSION['rop_tumblr_token']['oauth_token'] : null;
        $this->credentials['oauth_token_secret'] = isset( $_SESSION['rop_tumblr_token']['oauth_token_secret'] ) ? $_SESSION['rop_tumblr_token']['oauth_token_secret'] : null;

        if( isset( $_SESSION['rop_tumblr_credentials'] ) && isset( $_SESSION['rop_tumblr_token'] ) ) {
            $api = $this->get_api($this->credentials['consumer_key'], $this->credentials['consumer_secret'], $this->credentials['oauth_token'], $this->credentials['oauth_token_secret']);

            $profile = $api->getUserInfo();
            print_r( $profile );
        }

    }

    /**
     * Method to define the api.
     *
     * @since   8.0.0
     * @access  public
     * @param   string $consumer_key The Consumer Key. Default empty.
     * @param   string $consumer_secret The Consumer Secret. Default empty.
     * @param   string $token The Consumer Key. Default NULL.
     * @param   string $token_secret The Consumer Secret. Default NULL.
     * @return mixed
     */
    public function set_api( $consumer_key = '', $consumer_secret = '', $token = null, $token_secret = null ) {
        $this->api = new Tumblr\API\Client( $consumer_key, $consumer_secret, $token, $token_secret );
    }

    /**
     * Method to retrieve the api object.
     *
     * @since   8.0.0
     * @access  public
     * @param   string $consumer_key The Consumer Key. Default empty.
     * @param   string $consumer_secret The Consumer Secret. Default empty.
     * @param   string $token The Consumer Key. Default NULL.
     * @param   string $token_secret The Consumer Secret. Default NULL.
     * @return mixed
     */
    public function get_api( $consumer_key = '', $consumer_secret = '', $token = null, $token_secret = null ) {
        if( $this->api == null ) {
            $this->set_api( $consumer_key, $consumer_secret, $token, $token_secret );
        }
        return $this->api;
    }

    /**
     * Method for authorizing the service.
     *
     * @since   8.0.0
     * @access  public
     * @return mixed
     */
    public function authorize() {
        header('Content-Type: text/html');
        if ( ! session_id() ) {
            session_start();
        }

        $credentials = $_SESSION['rop_tumblr_credentials'];
        $tmp_token = $_SESSION['rop_tumblr_request_token'];

        $api = $this->get_api( $credentials['consumer_key'], $credentials['consumer_secret'], $tmp_token['oauth_token'], $tmp_token['oauth_token_secret'] );
        $requestHandler = $api->getRequestHandler();
        $requestHandler->setBaseUrl('https://www.tumblr.com/');

        if ( ! empty( $_GET['oauth_verifier'] ) ) {
            // exchange the verifier for the keys
            $verifier = trim( $_GET['oauth_verifier'] );
            $resp = $requestHandler->request( 'POST', 'oauth/access_token', array('oauth_verifier' => $verifier ) );
            $out = (string) $resp->body;
            $accessToken = array();
            parse_str($out, $accessToken);
            unset($_SESSION['rop_tumblr_request_token']);
            $_SESSION['rop_tumblr_token'] = $accessToken;
        }

        echo '<script>window.setTimeout("window.close()", 500);</script>';
    }

    /**
     * Method for authenticate the service.
     *
     * @since   8.0.0
     * @access  public
     * @return mixed
     */
    public function authenticate() {
        if ( ! session_id() ) {
            session_start();
        }

        $this->credentials = $_SESSION['rop_tumblr_credentials'];
        $this->credentials['oauth_token'] = isset( $_SESSION['rop_tumblr_token']['oauth_token'] ) ? $_SESSION['rop_tumblr_token']['oauth_token'] : null;
        $this->credentials['oauth_token_secret'] = isset( $_SESSION['rop_tumblr_token']['oauth_token_secret'] ) ? $_SESSION['rop_tumblr_token']['oauth_token_secret'] : null;

        if( isset( $_SESSION['rop_tumblr_credentials'] ) && isset( $_SESSION['rop_tumblr_token'] ) ) {
            $api = $this->get_api( $this->credentials['consumer_key'], $this->credentials['consumer_secret'], $this->credentials['oauth_token'], $this->credentials['oauth_token_secret'] );

            $profile = $api->getUserInfo();
            if( isset( $profile->user->name ) ) {
                $this->service = array(
                    'id' => $profile->user->name,
                    'service' => $this->service_name,
                    'credentials' => $this->credentials,
                    'public_credentials' => array(
                        'app_id' => array(
                            'name' => 'Consumer Key',
                            'value' => $this->credentials['consumer_key'],
                            'private' => false,
                        ),
                        'secret' => array(
                            'name' => 'Consumer Secret',
                            'value' => $this->credentials['consumer_secret'],
                            'private' => true,
                        ),
                    ),
                    'available_accounts' => $this->get_users( $profile->user->blogs )
                );

                unset( $_SESSION['rop_tumblr_credentials'] );
                unset( $_SESSION['rop_tumblr_token'] );
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Method to request a token from api.
     *
     * @since   8.0.0
     * @access  protected
     * @return mixed
     */
    public function request_api_token() {
        if ( ! session_id() ) {
            session_start();
        }

        $api = $this->get_api();
        $requestHandler = $api->getRequestHandler();
        $requestHandler->setBaseUrl('https://www.tumblr.com/');

        $resp = $requestHandler->request('POST', 'oauth/request_token', array(
            'oauth_callback' => $this->get_endpoint_url( 'authorize' )
        ));

        $result = (string) $resp->body;
        parse_str($result, $request_token);

        $_SESSION['rop_tumblr_request_token'] = $request_token;

        return $request_token;
    }

    /**
     * Method to register credentials for the service.
     *
     * @since   8.0.0
     * @access  public
     * @param   array $args The credentials array.
     */
    public function set_credentials( $args ) {
        $this->credentials = $args;
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
     * @param   array $data The data from the user.
     * @return mixed
     */
    public function sign_in_url( $data ) {
        $credentials = $data['credentials'];
        if ( ! session_id() ) {
            session_start();
        }

        $_SESSION['rop_tumblr_credentials'] = $credentials;
        $this->set_api( $credentials['consumer_key'], $credentials['consumer_secret'] );
        $request_token = $this->request_api_token();

        $url = 'https://www.tumblr.com/oauth/authorize?oauth_token=' . $request_token['oauth_token'];

        return $url;
    }

    /**
     * Method to return a Rop_User_Model.
     *
     * @since   8.0.0
     * @access  public
     * @param   array $args TODO
     * @return Rop_User_Model
     */
    public function get_user( $args ) {
        $user = new Rop_User_Model();
        return $user;
    }

    /**
     * Utility method to retrieve users from the Twitter account.
     *
     * @since   8.0.0
     * @access  public
     * @param   object $data Response data from Twitter.
     * @return array
     */
    private function get_users( $data = null ) {
        $users = array();
        if( $data == null ) {
            $this->set_api( $this->credentials['consumer_key'], $this->credentials['consumer_secret'], $this->credentials['oauth_token'], $this->credentials['oauth_token_secret'] );
            $api = $this->get_api();

            $profile = $api->getUserInfo();
            if( ! isset( $profile->user->name ) ) {
                return $users;
            }
            $data = $profile->user->blogs;
        }

        foreach ( $data as $page ) {
            $img = '';
            if( isset( $page->name ) ) {
                $img = 'https://api.tumblr.com/v2/blog/' . $page->name .'.tumblr.com/avatar';
            }

            $users[] = array(
                'id' => $page->name,
                'name' => $page->title,
                'account' => $page->name,
                'img' => $img,
                'active' => true,
            );
        }

        return $users;
    }

	/**
	 * Method for publishing with Twitter service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $post_details The post details to be published by the service.
	 * @return mixed
	 */
	public function share( $post_details ) {

	}
}
