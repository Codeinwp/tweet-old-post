<?php
/**
 * The file that defines the Facebook Service specifics.
 *
 * A class that is used to interact with Facebook.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Facebook_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Facebook_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'facebook';

	/**
	 * Defines the service permissions needed.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $permissions The Facebook required permissions.
	 */
	private $permissions = array( 'email', 'manage_pages', 'publish_pages' );

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
		$this->display_name = 'Facebook';
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
     * @param   string $app_id The Facebook APP ID. Default empty.
     * @param   string $secret The Facebook APP Secret. Default empty.
     * @return mixed
     */
    public function set_api( $app_id = '', $secret = '' ) {
        if( $app_id  != '' && $secret != '' ) {
            $this->api = new \Facebook\Facebook( array( 'app_id' => $app_id, 'app_secret' => $secret, 'default_graph_version' => 'v2.10' ) );
        } else {
            $this->api = new \Facebook\Facebook( array( 'app_id' => $this->app_id, 'app_secret' => $this->secret, 'default_graph_version' => 'v2.10' ) );
        }
    }

    /**
     * Method to retrieve the api object.
     *
     * @since   8.0.0
     * @access  public
     * @param   string $app_id The Facebook APP ID. Default empty.
     * @param   string $secret The Facebook APP Secret. Default empty.
     * @return mixed
     */
    public function get_api( $app_id = '', $secret = '' ) {
        if( $this->api == null ) {
            $this->set_api( $app_id, $secret );
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

        $credentials = $_SESSION['rop_facebook_credentials'];

        $api = $this->get_api( $credentials['app_id'], $credentials['secret'] );

        $helper = $api->getRedirectLoginHelper();

        $longAccessToken = '';
        try {
            $accessToken = $helper->getAccessToken();
            if ( ! isset( $accessToken ) ) {
                if ( $helper->getError() ) {
                    $this->error->throw_exception( '401 Unauthorized', $this->error->get_fb_exeption_message( $helper ) );
                } else {
                    $this->error->throw_exception( '400 Bad Request', 'Bad request' );
                }
            }
            $expires = time() + ( 120 * 24 * 60 * 60 ); // 120 days; 24 hours; 60 minutes; 60 seconds.
            $longAccessToken = new \Facebook\Authentication\AccessToken( $accessToken, $expires );
        } catch ( Facebook\Exceptions\FacebookResponseException $e ) {
            $this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
        } catch ( Facebook\Exceptions\FacebookSDKException $e ) {
            $this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
        }

        $token = $longAccessToken->getValue();

        $_SESSION['rop_facebook_token'] = $token->getValue();

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

        if( isset( $_SESSION['rop_facebook_token'] ) && isset( $_SESSION['rop_facebook_credentials'] ) ) {
            $credentials = $_SESSION['rop_facebook_credentials'];
            $token = $_SESSION['rop_facebook_token'];
            $api = $this->get_api( $credentials['app_id'], $credentials['secret'] );

            $this->set_credentials( array(
                'app_id' => $credentials['app_id'],
                'secret' => $credentials['secret'],
                'token' => $token,
            ) );

            $api->setDefaultAccessToken( $token );

            try {
                // Returns a `Facebook\FacebookResponse` object
                $response = $api->get( '/me?fields=id,name,email', $token );
            } catch ( Facebook\Exceptions\FacebookResponseException $e ) {
                $this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
            } catch ( Facebook\Exceptions\FacebookSDKException $e ) {
                $this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
            }

            unset( $_SESSION['rop_facebook_credentials'] );
            unset( $_SESSION['rop_facebook_token'] );

            $user = $response->getGraphUser();
            if ( $user->getId() ) {
                $this->service = array(
                    'id' => $user->getId(),
                    'service' => $this->service_name,
                    'credentials' => $this->credentials,
                    'public_credentials' => array(
                        'app_id' => array(
                            'name' => 'APP ID',
                            'value' => $this->credentials['app_id'],
                            'private' => false,
                        ),
                        'secret' => array(
                            'name' => 'APP Secret',
                            'value' => $this->credentials['secret'],
                            'private' => true,
                        ),
                    ),
                    'available_accounts' => $this->get_pages( $user )
                );
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
     * @param   string $token A Facebook token to use.
     * @return mixed
     */
    protected function request_api_token( $token = '' ) {
        $api = $this->get_api();

        $helper = $api->getRedirectLoginHelper();

        if ( isset( $token ) && $token != '' && $token != null ) {
            $longAccessToken = new \Facebook\Authentication\AccessToken( $this->token );
            $token = $longAccessToken->getValue();
            return $token->getValue();
        }

        try {
            $accessToken = $helper->getAccessToken();
            if ( ! isset( $accessToken ) ) {
                if ( $helper->getError() ) {
                    $this->error->throw_exception( '401 Unauthorized', $this->error->get_fb_exeption_message( $helper ) );
                } else {
                    $this->error->throw_exception( '400 Bad Request', 'Bad request' );
                }
            }
            $expires = time() + ( 120 * 24 * 60 * 60 ); // 120 days; 24 hours; 60 minutes; 60 seconds.
            $longAccessToken = new \Facebook\Authentication\AccessToken( $accessToken, $expires );
            $token = $longAccessToken->getValue();
            return $token->getValue();
        } catch ( Facebook\Exceptions\FacebookResponseException $e ) {
            $this->error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
        } catch ( Facebook\Exceptions\FacebookSDKException $e ) {
            $this->error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
        }
        return false;
    }

    /**
     * Method to register credentials for the service.
     *
     * @since   8.0.0
     * @access  public
     * @param   array $args The credentials array.
     */
    public function set_credentials($args)  {
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

        $_SESSION['rop_facebook_credentials'] = $credentials;

        $api = $this->get_api( $credentials['app_id'], $credentials['secret'] );
        $helper = $api->getRedirectLoginHelper();
        $url = $helper->getLoginUrl( $this->get_endpoint_url( 'authorize' ), $this->permissions );
        return $url;
    }

	/**
	 * Method to return a Rop_User_Model.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $page A Facebook page array. TODO
	 * @return Rop_User_Model
	 */
	public function get_user( $page ) {
		$user = new Rop_User_Model( array(
			'user_id' => $page['id'],
			'user_name' => $page['name'],
			'user_picture' => $page['img'],
			'user_service' => $this->service_name,
			'user_credentials' => array(
				'token' => $page['access_token'],
			),
		) );
		return $user;
	}

	/**
	 * Utility method to retrieve pages from the Facebook account.
	 *
	 * @since   8.0.0
	 * @access  public
     * @param   object $user The Facebook user.
	 * @return array
	 */
	public function get_pages( $user ) {
		$pages_array = array();
		$api = $this->get_api();
		$pages = $api->get( '/me/accounts' );
		$pages = $pages->getGraphEdge()->asArray();
		foreach ( $pages as $key ) {
			$img = $api->sendRequest( 'GET','/' . $key['id'] . '/picture', array( 'redirect' => false ) );
			$img = $img->getGraphNode()->asArray();

			$pages_array[] = array(
			  'id' => $key['id'],
			  'name' => $key['name'],
			  'account' => $user->getEmail(),
			  'img' => $img['url'],
			  'active' => false,
			  'access_token' => $key['access_token'],
			);
		}
		return $pages_array;
	}

	/**
	 * Method for publishing with Facebook service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $post_details The post details to be published by the service.
	 * @return mixed
	 */
	public function share( $post_details ) {
		$error = new Rop_Exception_Handler();
		$id = '1168461009964049';
		$page_token = 'EAAGrutRBO0ABAHa5ZCq2OWBsZC3o2y6lZA5TQPBNUzBkLZBZCdg28EymWSvJG8yh4H2a5n2ZCP4YibXd5i5YGiS29sltqStlwNvCnxTUV9tUwPyfd1wZBQ3RZC7hp3YZAuVBjYgXdUgZBY3MeqU5IlvKnZBOPHyo5g4ilO2FZC2q5CpkCBiJ3Nk849ZBNDjAIcZBPmadEZD';
		$fb = $this->fb;
		try {
			$post = $fb->post( '/' . $id . '/feed', array('message' => $post_details['message'], 'link' => 'https://themeisle.com', 'picture' => 'https://cdn.pixabay.com/photo/2016/01/19/18/00/city-1150026_960_720.jpg' ), $page_token );
			$post = $post->getGraphNode()->asArray();
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			$error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			$error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
		}

		var_dump( $post );
	}

}
