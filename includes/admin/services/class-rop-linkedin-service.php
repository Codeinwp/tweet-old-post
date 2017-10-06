<?php
/**
 * The file that defines the Linkedin Service specifics.
 *
 * A class that is used to interact with Linkedin.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Linkedin_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Linkedin_Service extends Rop_Services_Abstract {

    /**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'linkedin';

    /**
     * Permissions required by the app.
     *
     * @since   8.0.0
     * @access  protected
     * @var     array $scopes The scopes to authorize with LinkedIn.
     */
    protected $scopes = array( 'r_basicprofile', 'r_emailaddress', 'rw_company_admin', 'w_share' );

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
		$this->display_name = 'LinkedIn';
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
    }

    /**
     * Method to define the api.
     *
     * @since   8.0.0
     * @access  public
     * @param   string $client_id The Client ID. Default empty.
     * @param   string $client_secret The Client Secret. Default empty.
     * @return mixed
     */
    public function set_api( $client_id = '', $client_secret = '' ) {
        $this->api = new \LinkedIn\Client( $client_id, $client_secret );
        $this->api->setRedirectUrl( $this->get_endpoint_url( 'authorize' ) );
    }

    /**
     * Method to retrieve the api object.
     *
     * @since   8.0.0
     * @access  public
     * @param   string $client_id The Client ID. Default empty.
     * @param   string $client_secret The Client Secret. Default empty.
     * @return mixed
     */
    public function get_api( $client_id = '', $client_secret = '' ) {
        if( $this->api == null ) {
            $this->set_api( $client_id, $client_secret );
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

        $credentials = $_SESSION['rop_linkedin_credentials'];

        $api = $this->get_api( $credentials['client_id'], $credentials['secret'] );
        $accessToken = $api->getAccessToken( $_GET['code'] );

        $_SESSION['rop_linkedin_token'] = $accessToken;

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

        $this->credentials = $_SESSION['rop_linkedin_credentials'];

        if( isset( $_SESSION['rop_linkedin_credentials'] ) && isset( $_SESSION['rop_linkedin_token'] ) ) {
            $api = $this->get_api( $this->credentials['client_id'], $this->credentials['secret'] );
            $token = $_SESSION['rop_linkedin_token'];
            $this->credentials['token'] = $token->getToken();
            $api->setAccessToken( new LinkedIn\AccessToken( $this->credentials['token'] ) );

            $profile = $api->get(
                'people/~:(id,email-address,first-name,last-name,formatted-name,picture-url)'
            );
            if( isset( $profile['id'] ) ) {
                $this->service = array(
                    'id' => $profile['id'],
                    'service' => $this->service_name,
                    'credentials' => $this->credentials,
                    'public_credentials' => array(
                        'app_id' => array(
                            'name' => 'Client ID',
                            'value' => $this->credentials['client_id'],
                            'private' => false,
                        ),
                        'secret' => array(
                            'name' => 'Client Secret',
                            'value' => $this->credentials['secret'],
                            'private' => true,
                        ),
                    ),
                    'available_accounts' => $this->get_users( $profile )
                );

                unset( $_SESSION['rop_linkedin_credentials'] );
                unset( $_SESSION['rop_linkedin_token'] );
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
        $request_token = $api->oauth('oauth/request_token', array('oauth_callback' =>  $this->get_endpoint_url( 'authorize' ) ));

        $_SESSION['rop_twitter_request_token'] = $request_token;

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

        $_SESSION['rop_linkedin_credentials'] = $credentials;
        $this->set_api( $credentials['client_id'], $credentials['secret'] );
        $api = $this->get_api();
        $url = $api->getLoginUrl( $this->scopes );

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
            $this->set_api( $this->credentials['client_id'], $this->credentials['secret'] );
            $api = $this->get_api();
            $api->setAccessToken( $this->credentials['token'] );

            $profile = $api->get(
                'people/~:(id,email-address,first-name,last-name,formatted-name,picture-url)'
            );
            if( ! isset( $profile['id'] ) ) {
                return $users;
            }
            $data = $profile;
        }

        $img = '';
        if( isset( $data['pictureUrl'] ) && $data['pictureUrl'] ) {
            $img = $data['pictureUrl'];
        }

        $users = array(
            'id' => $data['id'],
            'name' => $data['formattedName'],
            'account' => $data['emailAddress'],
            'img' => $img,
            'active' => true,
        );
        return array( $users );
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
