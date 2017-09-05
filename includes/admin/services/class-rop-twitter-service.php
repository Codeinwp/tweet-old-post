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
class Rop_Twitter_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'twitter';

	/**
	 * Stores the Twitter token after auth.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $token The Twitter token.
	 */
	private $token;

	/**
	 * Stores the \TwitterOAuth instance.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     \TwitterOAuth $connection Instance.
	 */
	private $connection;

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Twitter';
		$this->credentials = array(
            'oauth_access_token' 		=> "2256465193-KDpAFIYfxpWugX2OU025b1CPs3WB0RJpgA4Gd4h",
            'oauth_access_token_secret' => "abx4Er8qEJ4jI7XDW8a90obzgy8cEtovPXCUNSjmwlpb9",
            'consumer_key' 				=> "ofaYongByVpa3NDEbXa2g",
            'consumer_secret' 			=> "vTzszlMujMZCY3mVtTE6WovUKQxqv3LVgiVku276M"
        );

        //$this->credentials = array_merge( $this->credentials, $this->model->get_option( 'credentials' ) );

		$this->register_endpoint( 'auth', 'auth' );

		//var_dump( 'Twitter INIT!' );
		//var_dump( $this->get_endpoint_url( 'auth' ) );
	}

	/**
	 * Utility method to get the service token.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return string
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Utility method to register the service token.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $value The value to be stored.
	 */
	public function set_token( $value ) {
		$this->token = $value;
		$this->credentials['token'] = $this->token;
		$this->model->set_option( 'credentials', $this->credentials );
	}

	/**
	 * Utility method to auth with Twitter.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function auth() {
		if ( ! session_id() ) {
			session_start();
		}

		$error = new Rop_Exception_Handler();

		if( ! isset( $_GET["oauth_token"] ) ) {
            $this->connection = new \Abraham\TwitterOAuth\TwitterOAuth( $this->credentials['consumer_key'], $this->credentials['consumer_secret'] );
            $connection = $this->connection;

            $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' =>  $this->get_endpoint_url( 'auth' ) ));

            $url = $connection->url("oauth/authorize", ["oauth_token" => $request_token['oauth_token'] ]);

            $_SESSION['rop_twitter']['oauth_token'] = $request_token["oauth_token"];
            $_SESSION['rop_twitter']['oauth_token_secret'] = $request_token["oauth_token_secret"];

            header('Location: ' . $url );
            die();
        } else {
		    $oauth_token = $_SESSION['rop_twitter']['oauth_token'];
            $oauth_token_secret = $_SESSION['rop_twitter']['oauth_token_secret'];

            $this->connection = new \Abraham\TwitterOAuth\TwitterOAuth( $this->credentials['consumer_key'], $this->credentials['consumer_secret'], $oauth_token, $oauth_token_secret );
            $connection = $this->connection;

//            $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_GET["oauth_verifier"] ] );
//            $_SESSION['rop_twitter']['oauth_token'] = $access_token["oauth_token"];
//            $_SESSION['rop_twitter']['oauth_token_secret'] = $access_token["oauth_token_secret"];
//
//            var_dump( $access_token );

            $content = $connection->get("account/verify_credentials");
            var_dump( $content );

        }
	}

	/**
	 * Method to return a Rop_User_Model.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $page A Facebook page array.
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
	 * Method for publishing with Twitter service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $post_details The post details to be published by the service.
	 * @return mixed
	 */
	public function share( $post_details ) {

	}

    public function credentials( $args ) {
        foreach ( $args as $key => $value ) {
            if ( in_array( $key, array( 'token' ) ) ) {
                $this->$key = $value;
                $this->credentials[ $key ] = $this->$key;
            }
        }
        $this->model->set_option( 'credentials', $this->credentials );
    }
}
