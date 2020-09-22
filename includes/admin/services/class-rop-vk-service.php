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
			$account['is_company'] = $account_data['is_company'];

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
	 * @param int $owner_id The owner id.
	 * @param object $client Instance of the client.
	 * @param string $access_token The access token.
	 *
	 * @return array $new_post The post contents
	 */
	private function vk_image_post( $post_details, $args, $owner_id, $client, $access_token ) {

		$param = array();

		if( $args['is_company'] ){
			$param['group_id'] = $args['id'];
		}

		$photo_response = $client->photos()->getWallUploadServer(
			$access_token,
			$param
			);
			
		$upload_url = $photo_response['upload_url'];
		$this->logger->info( print_r('Upload URL: ' . $upload_url, true) );

		$attachment_url = wp_get_attachment_url( $post_details['post_id'] );
		$this->logger->info( print_r($attachment_url, true) );


		$url = $this->get_path_by_url( $attachment_url, $post_details['mimetype'] );
		$this->logger->info( print_r($url, true) );
		

		$data = array(
			'photo' => new CURLFile(
				$url, 
				'multipart/form-data',
				'image.jpg'
			),
		);

		$this->logger->info( print_r($data, true) );
		
		$ch = curl_init($upload_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		
		$response = json_decode(curl_exec($ch), true);

		$this->logger->info( print_r($response, true) );
		
		$params = array(
			'photo' => (string) $response['photo'],
			'server' => (int) $response['server'],
			'hash' => (string) $response['hash'],
		);

		if( $args['is_company'] ){
			$params['group_id'] = (int) $args['id']; //has to be positive
		}else{
			$params['user_id'] = (int) $args['id'];
		}

		$response = $client->photos()->saveWallPhoto(
			$access_token,
			$params
		);

	$this->logger->info( print_r($response, true) );
	$this->logger->info( print_r($response[0]['id'], true) );

	$attachment = 'photo'. $args['id'] . '_' . $response[0]['id'];
	$this->logger->info( print_r($attachment, true) );

	$new_post = array(
		'owner_id' => $owner_id,
		'friends_only' => 0,
		'message' => $post_details['content'] . $post_details['hashtags'],
		'attachments' => $attachment . ',' . $this->get_url( $post_details ),
	);
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
	 * @param int $owner_id The owner id.
	 *
	 * @return array $new_post The post contents
	 */
	private function vk_text_post( $post_details, $args, $owner_id ) {

		$this->logger->info( 'Plain Text Post' );

		$new_post = array(
			'owner_id' => $owner_id,
			'friends_only' => 0,
			'message' => $post_details['content'] . $post_details['hashtags'],
		);

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
	 *
	 * @return object
	 */
	private function vk_article_post( $post_details, $args, $owner_id ) {

		$new_post = array(
			'owner_id' => $owner_id,
			'friends_only' => 0,
			'message' => $post_details['content'] . $post_details['hashtags'],
			'attachments' => $this->get_url( $post_details ),
		);

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
		
		$post_id = $post_details['post_id'];

		$client = new VKApiClient();
		$access_token = $args['credentials']['access_token'];
		$owner_id = ($args['is_company']) ? '-'.$args['id'] : $args['id'];

		$post_url = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];
	
		// VK link post
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$new_post = $this->vk_article_post( $post_details, $args, $owner_id );
		}

		// VK plain text post
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$new_post = $this->vk_text_post( $post_details, $args, $owner_id );
		}

		// VK image post
		if ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {
			$new_post = $this->vk_image_post( $post_details, $args, $owner_id, $client, $access_token );
		}

		$response = $client->wall()->post(
		$args['credentials']['access_token'],
		$new_post
		);

		if ( !empty($response['post_id']) ) {

			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on Vkontakte ',
					html_entity_decode( get_the_title( $post_details['post_id'] ) ),
					$args['user']
				)
			);

			$this->logger->info( print_r($response, true) );
			return true;

		} else {

			$this->logger->alert_error( 'Error sharing to Vkontakte' . print_r( $response, true ) );
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
		$account['link'] = sprintf( 'https://vk.com/id%s', $account['id'] );
		return $account;
	}

}
