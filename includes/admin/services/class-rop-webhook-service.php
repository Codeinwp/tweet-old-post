<?php
/**
 * The file that defines the Webhook Service specifics.
 *
 * A class that is used to interact with Twitter.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Webhook_Service
 *
 * @since   9.1.0
 * @link    https://themeisle.com/
 */
class Rop_Webhook_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'webhook';


	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Webhook';
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

		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		return false;
	}

	public function process_registration( $data ) {
		if ( empty( $data['url'] ) ) {
			return false;
		}

		$id           = base64_encode( $data['url'] );
		$display_name = ! empty( $data['display_name'] ) ? $data['display_name'] : 'Webhook'; 

		$this->service = array(
			'id'          => $id,
			'service'     => $this->service_name,
			'credentials' => array(
				'url'          => $data['url'],
				'headers'      => ! empty( $data['headers'] ) && is_array( $data['headers'] ) ? $data['headers'] : array(),
				'display_name' => $display_name,
			),
			'available_accounts' => array(
				array(
					'id'      => $id,
					'user'    => $display_name,
					'service' => $this->service_name,
					'account' => $this->normalize_string( $data['url'] ),
					'created' => date( 'd/m/Y H:i' )
				),
			),
		);

		return true;
	}

	public function expose_endpoints()
	{
		
	}

	public function get_api()
	{
		
	}

	public function set_api()
	{
		
	}

	public function populate_additional_data($account)
	{
		return $account;
	}

	public function maybe_authenticate()
	{
		
	}

	public function authenticate($args)
	{
		
	}
	
	public function request_api_token() {

	}
}
