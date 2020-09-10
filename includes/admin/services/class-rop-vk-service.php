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
 * Class Rop_Vk_Service
 *
 * @since   8.5.9
 * @link    https://themeisle.com/
 */
use \VK\Client\VKApiClient;
class Rop_Vk_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since  8.5.9
	 * @access protected
	 * @var    string $service_name The service name.
	 */
	protected $service_name = 'vk';

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since  8.5.9
	 * @access public
	 */
	public function init() {
		$this->display_name = 'Vk';
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
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.vk_no_valid_accounts' ) );
			return false;
		}

		$the_id       = unserialize( base64_decode( $accounts_data['id'] ) );
		$accounts_array = unserialize( base64_decode( $accounts_data['pages'] ) );

		$accounts = array();

		for ( $i = 0; $i < sizeof( $accounts_array ); $i++ ) {

			$account = $this->user_default;

			$account_data = $accounts_array[ $i ];

			$account['id'] = $account_data['id'];
			$account['img'] = $account_data['img'];
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
				'access_token'   => $account_data['access_token'],
			),
			'available_accounts' => $accounts,
		);

		return true;
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
	private function vk_image_post( $post_details, $args, $new_post ) {

		$image_url = $post_details['post_image'];

		// if image is empty lets create a different type of GMB post
		if ( empty( $image_url ) ) {
			$this->logger->info( 'Could not get image. Falling back to text post with link.' );
			return $this->vk_link_with_no_image_post( $post_details, $args );
		}

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
	private function vk_text_post( $post_details, $args, $new_post ) {

		$locale = get_locale();

		$new_post->setLanguageCode( $locale );

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
	private function vk_article_post( $post_details, $args, $new_post ) {

		$image_url = get_the_post_thumbnail_url( $post_details['post_id'], 'large' );

		// if image is empty lets create a different type of GMB post
		if ( empty( $image_url ) ) {
			$this->logger->info( 'Could not get image. Falling back to text post with link.' );
			return $this->vk_link_with_no_image_post( $post_details, $args, $new_post );
		}

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
	private function vk_link_with_no_image_post( $post_details, $args, $new_post ) {

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
		
		$client = new VKApiClient();

		$post_id = $post_details['post_id'];
		$post_url = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];


		$response = $client->wall()->post(

		'cad239af8c9653799222786193c2873cb8fc104e3f136581273f839eff13dacec8ca5d65ae4b8867f04a3',
		array(
		'owner_id' => '611178731',
		'friends_only' => 0,
		'message' => $post_details['content'],
		'attachments' => 'https://revive.social',
		)

		);


		if ( !empty($response) ) {

			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on Vkontakte ',
					html_entity_decode( get_the_title( $post_details['post_id'] ) ),
					$args['user']
				)
			);

			return true;

		} else {

			$this->logger->alert_error( 'Error sharing to Vkontakte' . print_r( $response, true ) );
				return false;
		}

		// GMB link post
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$new_post = $this->vk_article_post( $post_details, $args, $new_post );
		}

		// GMB image post
		if ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {
			$new_post = $this->vk_image_post( $post_details, $args, $new_post );
		}

		// GMB plain text post
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$new_post = $this->vk_text_post( $post_details, $args, $new_post );
		}


		if ( $response->state === 'LIVE' ) {

			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on Google My Business ',
					html_entity_decode( get_the_title( $post_details['post_id'] ) ),
					$args['user']
				)
			);

		} else {

			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.vk_failed_share' ) . print_r( $response, true ) );
				return false;
		}

		return true;

	}

	/**
	 * Method to populate additional data.
	 *
	 * @since   8.5.13
	 * @access  public
	 * @return mixed
	 */
	public function populate_additional_data( $account ) {
		$account['link'] = sprintf( 'https://vk.com/id%s', $account['id'] );
		return $account;
	}

}
