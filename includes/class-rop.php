<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      8.0.0
 * @package    Rop
 * @subpackage Rop/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    8.0.0
	 * @access   protected
	 * @var      Rop_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    8.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    8.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    8.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'rop';
		$this->version = '8.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rop_Loader. Orchestrates the hooks of the plugin.
	 * - Rop_i18n. Defines internationalization functionality.
	 * - Rop_Admin. Defines all hooks for the admin area.
	 * - Rop_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		$this->loader = new Rop_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Rop_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Rop_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function get_available_services() {
	    return array(
            'facebook' => array(
                'active' => true,
                'name' => 'Facebook',
                'two_step_sign_in' => true,
                'credentials' => array(
                    'app_id'=> array(
                        'name' => 'APP ID',
                        'description' => 'Please add the APP ID from your Facebook app.'
                    ),
                    'secret' => array(
                        'name' => 'APP SECRET',
                        'description' => 'Please add the APP SECRET from your Facebook app.'
                    )
                ),
                'url' => '#'
            ),
            'twitter' => array(
                'active' => true,
                'name' => 'Twitter',
                'two_step_sign_in' => false,
                'credentials' => array(),
                'url' => '#'
            ),
            'linkedin' => array(
                'active' => false,
                'name' => 'LinkedIn'
            ),
            'thumblr' => array(
                'active' => false,
                'name' => 'Thumblr'
            ),
        );
    }

    private function get_authenticated_services() {
	    return array();
    }

	public function api( WP_REST_Request $request ) {
	    switch( $request->get_param( 'req' ) ) {
            case 'available_services':
                $response = $this->get_available_services();
                break;
            case 'authenticated_services':
                $response = $this->get_authenticated_services();
            default:
                $response = array( 'status' => '200', 'data' => array( 'list', 'of', 'stuff', 'from', 'api' ) );
        }
	    return $response;
    }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Rop_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $this, 'menu_pages' );

        add_action( 'rest_api_init', function () {
			register_rest_route( 'tweet-old-post/v8', '/api', array(
				'methods' => array( 'GET', 'POST' ),
				'callback' => array( $this, 'api' ),
			) );
		} );

		//$fb_service = new Rop_Facebook_Service();
		//$twitter_service = new Rop_Twitter_Service();
		//var_dump( $twitter_service );
		// $fb_service->credentials( array( 'app_id' => '470293890022208', 'secret' => 'bf3ee9335692fee071c1a41fbe52fdf5' ) );
		// $fb_service->set_token( 'EAAGrutRBO0ABAEfThg0IOMaKXWD0QzBlZCeETluvu3ZAah1BWStgvd7Of3OMHZAsgX6gUfjaqgnbXEYyToyzkB1gEgc8hsrZBiHRiKgerSaDxjJHevy8ZB1jLrRemQOrFAfYO8MXsZC6lFkwJr8U9WbHm34gFnxSJVRYp3CEoPQb1dMKf37ZApV' );
		//$fb_service->auth();
		//if ( $fb_service->is_auth() ) {
		    // var_dump( $fb_service->get_pages() );
			// $fb_service->share( array( 'message' => 'A new test message from ROP. Just the image.' ) );
		//}

//		add_action( 'rest_api_init', function () {
//			register_rest_route( 'tweet-old-post/v8', '/facebook', array(
//				'methods' => 'GET',
//				'callback' => array( $this, 'doLogin' ),
//			) );
//		} );
//
//		add_action( 'rest_api_init', function () {
//			register_rest_route( 'tweet-old-post/v8', '/facebook/login', array(
//				'methods' => 'GET',
//				'callback' => array( $this, 'requestLogin' ),
//			) );
//		} );

	}

	public function rop_main_page() {
	    echo '
	    <div id="rop_core" style="margin: 20px 20px 40px 0;">
	        <main-page-panel></main-page-panel>
        </div>';
    }

    /**
     * Add admin menu items for orbit-fox.
     *
     * @since   1.0.0
     * @access  public
     */
    public function menu_pages() {
        add_menu_page(
            __( 'Revive Old Posts', 'rop' ), __( 'Revive Old Posts', 'rop' ), 'manage_options', 'rop_main',
            array(
                $this,
                'rop_main_page',
            )
        );
    }

	/**
	 * Does the login request.
	 *
	 * @param WP_REST_Request $request The WP REST object.
	 */
	public function requestLogin( WP_REST_Request $request ) {
		if ( ! session_id() ) {
			session_start();
		}
		$fb = new \Facebook\Facebook([
			'app_id' => '470293890022208',
			'app_secret' => 'bf3ee9335692fee071c1a41fbe52fdf5',
			'default_graph_version' => 'v2.10',
			// 'default_access_token' => '{access-token}', // optional
		]);

		$helper = $fb->getRedirectLoginHelper();

		$permissions = [ 'email', 'manage_pages', 'publish_pages' ]; // Optional permissions
		$url = site_url( '/wp-json/tweet-old-post/v8/facebook/' );
		$loginUrl = $helper->getLoginUrl( $url, $permissions );

		echo $loginUrl;
	}

	/**
	 * Does the login request.
	 *
	 * @param WP_REST_Request $request The WP REST object.
	 */
	public function doLogin( WP_REST_Request $request ) {
		if ( ! session_id() ) {
			session_start();
		}
		$fb = new \Facebook\Facebook([
			'app_id' => '470293890022208',
			'app_secret' => 'bf3ee9335692fee071c1a41fbe52fdf5',
			'default_graph_version' => 'v2.10',
			// 'default_access_token' => '{access-token}', // optional
		]);

		$helper = $fb->getRedirectLoginHelper();

		if ( isset( $_SESSION['facebook_access_token'] ) ) {
			$longAccessToken = $_SESSION['facebook_access_token'];
		} else {
			try {
				$accessToken = $helper->getAccessToken();
				$expires = time() + ( 120 * 24 * 60 * 60 ); // 120 days; 24 hours; 60 minutes; 60 seconds.
				$longAccessToken = new \Facebook\Authentication\AccessToken( $accessToken, $expires );
			} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
				// When Graph returns an error
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
				// When validation fails or other local issues
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}
		}

		if ( ! isset( $longAccessToken ) ) {
			if ( $helper->getError() ) {
				header( 'HTTP/1.0 401 Unauthorized' );
				echo 'Error: ' . $helper->getError() . "\n";
				echo 'Error Code: ' . $helper->getErrorCode() . "\n";
				echo 'Error Reason: ' . $helper->getErrorReason() . "\n";
				echo 'Error Description: ' . $helper->getErrorDescription() . "\n";
			} else {
				header( 'HTTP/1.0 400 Bad Request' );
				echo 'Bad request';
			}
			exit;
		}

		$_SESSION['facebook_access_token'] = $longAccessToken;
		$token_value = $longAccessToken->getValue();

		try {
			// Returns a `Facebook\FacebookResponse` object
			$response = $fb->get( '/me?fields=id,name', $token_value );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		$user = $response->getGraphUser();

	    echo $token_value;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    8.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     8.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     8.0.0
	 * @return    Rop_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     8.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
