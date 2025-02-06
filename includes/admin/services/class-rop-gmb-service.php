<?php
/**
 * The file that defines the Google My Business Service specifics.
 *
 * NOTE: Extending abstract class but not making use of some of the methods with new authentication workflow.
 *           Abstract class will be cleaned up once we move all services to one click sign on and drop users connecting own apps.
 *
 * A class that is used to interact with Google My Business.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.5.9
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Gmb_Service
 *
 * @since   8.5.9
 * @link    https://themeisle.com/
 */
class Rop_Gmb_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since  8.5.9
	 * @access protected
	 * @var    string $service_name The service name.
	 */
	protected $service_name = 'gmb';

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since  8.5.9
	 * @access public
	 */
	public function init() {
		$this->display_name = 'Google My Business';
	}

		/**
		 * Returns information for the current service.
		 *
		 * @since  8.5.9
		 * @access public
		 * @return mixed
		 */
	public function get_service() {
		return $this->service;
	}

	/**
	 * Abstract function, not in Use. Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since  8.5.9
	 * @access public
	 */
	public function expose_endpoints() {
		return;
	}

	/**
	 * Abstract function, not in Use. Method to register credentials for the service.
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
	 * Abstract function, not in Use. Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since  8.5.9
	 * @access protected
	 * @return mixed
	 */
	public function request_api_token() {
		return;
	}

	/**
	 * Abstract function, not in Use. Method to retrieve the api object.
	 *
	 * @since  8.5.9
	 * @access public
	 *
	 * @param string $app_id The APP ID. Default empty.
	 * @param string $secret The APP Secret. Default empty.
	 *
	 * @return null abstract method not used for this service specifically.
	 */
	public function get_api( $app_id = '', $secret = '' ) {
		return;
	}

	/**
	 * Abstract function, not in Use. Method to define the api.
	 *
	 * @since  8.5.9
	 * @access public
	 *
	 * @param string $app_id The APP ID. Default empty.
	 * @param string $secret The APP Secret. Default empty.
	 *
	 * @return mixed
	 */
	public function set_api( $app_id = '', $secret = '' ) {
		return;
	}

	/**
	 * Abstract function, not in Use. Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since  8.5.9
	 * @access public
	 * @return mixed
	 */
	public function maybe_authenticate() {
		return;
	}

	/**
	 * Abstract function, not in Use. Method to authenticate an user based on provided credentials.
	 * Used in DB upgrade.
	 *
	 * @param array $args The arguments for service auth.
	 *
	 * @return bool
	 */
	public function authenticate( $args = array() ) {
		return;
	}

	/**
	 * This method will load and prepare the account data for Google My Business user.
	 * Used in Rest Api.
	 *
	 * @since   8.5.9
	 * @access  public
	 *
	 * @param   array $accounts_data Buffer accounts data.
	 *
	 * @return  bool
	 */
	public function add_account_with_app( $accounts_data ) {

		if ( ! $this->is_set_not_empty( $accounts_data, array( 'id' ) ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.gmb_no_valid_accounts' ) );
			return false;
		}

		$the_id       = unserialize( base64_decode( $accounts_data['id'] ) );
		$accounts_array = unserialize( base64_decode( $accounts_data['pages'] ) );

		$accounts = array();

		for ( $i = 0; $i < sizeof( $accounts_array ); $i++ ) {

			$account = $this->user_default;

			$account_data = $accounts_array[ $i ];

			$account['id'] = $account_data['id'];
			$account['img'] = apply_filters( 'rop_custom_gmb_avatar', $account_data['img'] );
			$account['account'] = $account_data['account'];
			$account['user'] = $account_data['user'];

			if ( $i === 0 ) {
				$account['active'] = true;
			} else {
				$account['active'] = false;
			}

			$accounts[] = $account;
		}

		// Prepare the data that will be saved as new account added.
		$this->service = array(
			'id'                 => $the_id,
			'service'            => $this->service_name,
			'credentials'        => array(
				'created'                => $account_data['created'],
				'expires_in'         => $account_data['expires_in'],
				'access_token'   => $account_data['access_token'],
				'refresh_token'  => $account_data['refresh_token'],
			),
			'available_accounts' => $accounts,
		);

		return true;
	}

	/**
	 * Method to refresh access token.
	 *
	 * @since  8.5.9
	 * @access private
	 *
	 * @return array
	 */
	private function gmb_refresh_access_token() {

		$rop_data = get_option( 'rop_data' );
		$rop_services_data  = $rop_data['services'];

		$id = '';
		$access_token = '';
		$created = '';
		$expires_in = '';
		$gmb_service_id = '';
		$refresh_token = '';

		foreach ( $rop_services_data as $service => $service_data ) {
			if ( $service_data['service'] === 'gmb' ) {
				$id = $service_data['id'];
				$access_token = $service_data['credentials']['access_token'];
				$created = $service_data['credentials']['created'];
				$expires_in = $service_data['credentials']['expires_in'];
				$gmb_service_id = $service;
				$refresh_token = $service_data['credentials']['refresh_token'];
				break;
			}
		}

		 // $created = '1593273390';
		// Check if access token will expire in next 30 seconds.
		$expired = ( $created + ( $expires_in - 30 ) ) < time();

		// If it's not expired then return current access token in DB
		if ( ! $expired ) {
			// Add an expires_in value to prevent Google Client PHP notice for undefined expires_in index
			$access_token = array('access_token' => $access_token, 'expires_in' => $expires_in);
			return $access_token;
		}

		$this->logger->info( 'Google My Business access token has expired, fetching new...' );

		$url = ROP_AUTH_APP_URL . '/wp-json/gmb/v1/access-token?refresh-token=' . $refresh_token;

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			$error_string = $response->get_error_message();
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.wordpress_api_error' ) . $error_string );
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $response['code'] !== 200 ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.gmb_failed_access_token_refresh' ) . print_r( $response, true ) );
			return;
		}

		$update_token = array(
			'services' => array(
				$gmb_service_id => array(
					'id' => $id,
					'service' => 'gmb',
					'credentials' => array(
						'created' => $response['token_data']['created'],
						'expires_in' => $response['token_data']['expires_in'],
						'access_token' => $response['token_data']['access_token'],
					),
				),
			),

		);

		$rop_updated_data = array_replace_recursive( $rop_data, $update_token );

		update_option( 'rop_data', $rop_updated_data );

		return $response['token_data'];

	}

	/**
	 * Method for creating image posts on Google My Business.
	 *
	 * @since  8.5.9
	 * @access private
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param array  $args Optional arguments needed by the method.
	 * @param object $new_post Google My Business Local Posts object.
	 *
	 * @return object
	 */
	private function gmb_image_post( $post_details, $args, $new_post ) {

		$image_url = $post_details['post_image'];

		// if image is empty lets create a different type of GMB post
		if ( empty( $image_url ) ) {
			$this->logger->info( 'No image set for post, but "Share as Image Post" is checked. Falling back to article post' );
			return $this->gmb_link_with_no_image_post( $post_details, $args, $new_post );
		}

		$locale = get_locale();

		$new_post->setLanguageCode( $locale );
		$new_post->setTopicType( 'STANDARD' );

		if ( ! empty( $post_details['post_url'] ) ) {
			$action_type = apply_filters( 'rop_gmb_action_type', 'LEARN_MORE' );
			$url = $this->get_url( $post_details );

			$new_post->setSummary( $post_details['content'] );

			$call_to_action = new Google_Service_MyBusiness_CallToAction();

			$call_to_action->setActionType( $action_type );

			$call_to_action->setUrl( $url );

			$new_post->setCallToAction( $call_to_action );
		}

		$media = new Google_Service_MyBusiness_MediaItem();

		$media->setMediaFormat( 'PHOTO' );
		$media->setSourceUrl( $image_url );

		$new_post->setMedia( $media );

		return $new_post;

	}


	/**
	 * Method for text posts to Google My Business.
	 *
	 * @since  8.5.9
	 * @access private
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param array  $args Optional arguments needed by the method.
	 * @param object $new_post Google My Business Local Posts object.
	 *
	 * @return object
	 */
	private function gmb_text_post( $post_details, $args, $new_post ) {

		$locale = get_locale();

		$new_post->setLanguageCode( $locale );
		$new_post->setTopicType( 'STANDARD' );
		$new_post->setSummary( $post_details['content'] );

		return $new_post;

	}

	/**
	 * Method for creating link(article) posts to Google My Business.
	 *
	 * @since  8.5.9
	 * @access private
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param array  $args Optional arguments needed by the method.
	 * @param object $new_post Google My Business Local Posts object.
	 *
	 * @return object
	 */
	private function gmb_article_post( $post_details, $args, $new_post ) {

		$image_url = get_the_post_thumbnail_url( $post_details['post_id'], 'large' );

		// if image is empty lets create a different type of GMB post
		if ( empty( $image_url ) ) {
			$this->logger->info( 'Could not get image. Falling back to text post with link.' );
			return $this->gmb_link_with_no_image_post( $post_details, $args, $new_post );
		}

		$locale = get_locale();
		$action_type = apply_filters( 'rop_gmb_action_type', 'LEARN_MORE' );
		$url = $this->get_url( $post_details );

		$new_post->setLanguageCode( $locale );
		$new_post->setTopicType( 'STANDARD' );
		$new_post->setSummary( $post_details['content'] );

		$call_to_action = new Google_Service_MyBusiness_CallToAction();

		$call_to_action->setActionType( $action_type );

		$call_to_action->setUrl( $url );

		$new_post->setCallToAction( $call_to_action );

		$media = new Google_Service_MyBusiness_MediaItem();

		$media->setMediaFormat( 'PHOTO' );
		$media->setSourceUrl( $image_url );

		$new_post->setMedia( $media );

		return $new_post;

	}


	/**
	 * Method for creating posts with no featured image on Google My Business.
	 *
	 * @since  8.5.9
	 * @access private
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param array  $args Optional arguments needed by the method.
	 * @param object $new_post Google My Business Local Posts object.
	 *
	 * @return object
	 */
	private function gmb_link_with_no_image_post( $post_details, $args, $new_post ) {

		$locale = get_locale();
		$action_type = apply_filters( 'rop_gmb_action_type', 'LEARN_MORE' );
		$url = $this->get_url( $post_details );

		$new_post->setLanguageCode( $locale );
		$new_post->setTopicType( 'STANDARD' );
		$new_post->setSummary( $post_details['content'] );

		$call_to_action = new Google_Service_MyBusiness_CallToAction();

		$call_to_action->setActionType( $action_type );

		$call_to_action->setUrl( $url );

		$new_post->setCallToAction( $call_to_action );

				return $new_post;

	}
	/**
	 * Method for publishing with Google My Business service.
	 *
	 * @since  8.5.9
	 * @access public
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {

		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		if ( ! class_exists( 'Google_Client' ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.gmb_missing_main_class' ) );
			return;
		} else {
			$client = new Google_Client();
		}

		if ( ! class_exists( 'Google_Service_MyBusiness' ) ) {

			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			if ( is_plugin_active( 'tweet-old-post-pro/tweet-old-post-pro.php' ) ) {
				require_once ROP_PRO_PATH . 'lib/gmb/gmb-service-helper.php';
			} else {
				$this->logger->alert_error( Rop_I18n::get_labels( 'errors.gmb_missing_lib_class' ) );
				return;
			}
		}

		$access_token = $this->gmb_refresh_access_token();
		$client->setAccessToken( $access_token );
		$client->setApiFormatV2( true );

		$post_id = $post_details['post_id'];
		$post_url = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];
		$new_post = new Google_Service_MyBusiness_LocalPost();

		$gmb = new Google_Service_MyBusiness( $client );
		$post_creator = $gmb->accounts_locations_localPosts;

		$location = $args['id'];

		// GMB link post
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$new_post = $this->gmb_article_post( $post_details, $args, $new_post );
		}

		// GMB image post
		if ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {
			$new_post = $this->gmb_image_post( $post_details, $args, $new_post );
		}

		// GMB plain text post
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$new_post = $this->gmb_text_post( $post_details, $args, $new_post );
		}

		$response = $post_creator->create( $location, $new_post );

		if ( in_array( $response->state, array( 'LIVE', 'PROCESSING' ), true ) ) {

			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on Google My Business ',
					html_entity_decode( $post_details['title'] ),
					$args['user']
				)
			);

			return true;
		} else {

			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.gmb_failed_share' ) . print_r( $response, true ) );
			return false;
		}

	}

	/**
	 * Method to populate additional data.
	 *
	 * @since   8.5.13
	 * @access  public
	 * @return mixed
	 */
	public function populate_additional_data( $account ) {
		$account['link'] = 'https://business.google.com/';
		return $account;
	}

}
