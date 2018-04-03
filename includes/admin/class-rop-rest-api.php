<?php
/**
 * The class that handles the REST main calls for the  plugin.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin
 */

/**
 * Handles the REST main calls for the  plugin.
 *
 * Contains utility methods for the plugin REST API and the API switcher.
 *
 * @package    Rop
 * @subpackage Rop/admin
 * @author     Themeisle <friends@themeisle.com>
 */
class Rop_Rest_Api {

	/**
	 * Stores the default response for the API.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $response The default response.
	 */
	private $response;

	/**
	 * Rop_Rest_Api constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		$this->response = new Rop_Api_Response();
	}

	/**
	 * Registers the API endpoint.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function register() {
		add_action(
			'rest_api_init', function () {
			register_rest_route(
				'tweet-old-post/v8', '/api', array(
					'methods'             => array( 'GET', 'POST' ),
					'callback'            => array( $this, 'api' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				)
			);
		}
		);
	}

	/**
	 * The api switch and entry point.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   WP_REST_Request $request The request object.
	 *
	 * @return array|mixed|null|string
	 */
	public function api( WP_REST_Request $request ) {
		$response         = $this->response;
		$method_requested = $request->get_param( 'req' );
		if ( method_exists( $this, $method_requested ) ) {
			$data     = json_decode( $request->get_body(), true );
			$data     = is_array( $data ) ? $data : array();
			$response = $this->$method_requested( $data );
		}
		$logger = new Rop_Logger();
		$logger->alert_success( 'here we go' );

		return $response;
	}

	/**
	 * API method called to toggle the cron job.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0rc
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function manage_cron( $data ) {
		$cron_helper = new Rop_Cron_Helper();
		$this->response->set_code( '200' )
		               ->set_message( __( 'Cron manager response.', 'tweet-old-post' ) )
		               ->set_data( $cron_helper->manage_cron( $data ) );

		return $this->response->to_array();
	}

	/**
	 * API method called to publish a queue event and return active queue.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 * @Throws  Exception If a service can not be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function publish_queue_event( $data ) {
		$pro_api = new Rop_Pro_Api();

		return $pro_api->publish_queue_event( $data );
	}

	/**
	 * API method called to skip a queue event and return active queue.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function skip_queue_event( $data ) {
		$pro_api = new Rop_Pro_Api();

		return $pro_api->skip_queue_event( $data );
	}

	/**
	 * API method called to block a queue event and return active queue.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function block_queue_event( $data ) {
		$pro_api = new Rop_Pro_Api();

		return $pro_api->block_queue_event( $data );
	}

	/**
	 * API method called to update a queue event and return active queue.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function update_queue_event( $data ) {
		$pro_api = new Rop_Pro_Api();

		return $pro_api->update_queue_event( $data );
	}

	/**
	 * API method called to get the active queue.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_queue( $data ) {
		$queue = new Rop_Queue_Model();
		if ( isset( $data['force'] ) ) {
			$queue->clear_queue();
		}
		$this->response->set_code( '200' )
		               ->set_message( __( 'Queue retrieved successfully.', 'tweet-old-post' ) )
		               ->set_data( $queue->get_ordered_queue() );

		return $this->response->to_array();
	}

	/**
	 * API method called to save a schedule.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function save_schedule( $data ) {
		$pro_api = new Rop_Pro_Api();

		return $pro_api->save_schedule( $data );
	}

	/**
	 * API method called to reset a schedule to defaults.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function reset_schedule( $data ) {
		$schedules = new Rop_Scheduler_Model();
		$schedules->remove_schedule( $data['account_id'] );
		$this->response->set_code( '201' )
		               ->set_message( __( 'Schedule was reset successfully.', 'tweet-old-post' ) )
		               ->set_data( $schedules->get_schedule() );

		return $this->response->is_not_silent()->to_array();
	}

	/**
	 * API method called to retrieve a schedule.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function get_schedule( $data ) {
		$schedules = new Rop_Scheduler_Model();
		$this->response->set_code( '200' )
		               ->set_message( __( 'Schedule was retrieved successfully.', 'tweet-old-post' ) )
		               ->set_data( $schedules->get_schedule() );

		return $this->response->to_array();
	}

	/**
	 * API method called to get shortner service credentials.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 * @Throws  Exception Throws an exception if a short url service can't be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return mixed
	 */
	private function get_shortner_credentials( $data ) {
		if ( empty( $data['short_url_service'] ) ) {
			return $this->response->set_code( '200' )
			                      ->set_message( __( 'Empty shortner.', 'tweet-old-post' ) )
			                      ->set_data( array() )->to_array();
		}
		if ( $data['short_url_service'] === 'wp_short_url' ) {
			return $this->response->set_code( '200' )
			                      ->set_message( __( 'Shortner credentials retrieved successfully.', 'tweet-old-post' ) )
			                      ->set_data( array() )->to_array();
		}

		$sh_factory = new Rop_Shortner_Factory();

		$this->response->set_code( '500' )->set_message( __( 'An error occurred when trying to retrieve the sortner service credentials.', 'tweet-old-post' ) );

		try {
			$shortner = $sh_factory->build( $data['short_url_service'] );
			$this->response->set_code( '200' )
			               ->set_message( __( 'Shortner credentials retrieved successfully.', 'tweet-old-post' ) )
			               ->set_data( $shortner->get_credentials() );
		} catch ( Exception $exception ) {
			// Service not found or can't be built. Maybe log this exception.
			$log           = new Rop_Logger();
			$error_message = sprintf( esc_html__( 'The shortner service %1$s can NOT be built or was not found', 'tweet-old-post' ), $data['short_url_service'] );
			$log->warn( $error_message . $exception->getMessage() );
			$this->response->set_code( '500' )->set_message( $error_message );
		}

		return $this->response->to_array();
	}

	/**
	 * API method called to save a post format.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 * @Throws  Exception Throws an exception if a short url service can't be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function save_post_format( $data ) {
		$post_format = new Rop_Post_Format_Model();
		$sh_factory  = new Rop_Shortner_Factory();
		$this->response->set_code( '500' )->set_message( __( 'An error occurred when trying to save the post format.', 'tweet-old-post' ) );
		try {
			if ( $data['data']['short_url_service'] !== 'wp_short_url' ) {
				$shortner = $sh_factory->build( $data['data']['short_url_service'] );
				$shortner->set_credentials( $data['data']['shortner_credentials'] );
			}
			$this->response->set_code( '201' )
			               ->set_message( __( 'Shortner and credentials set successfully.', 'tweet-old-post' ) );
		} catch ( Exception $exception ) {
			// Service not found or can't be built. Maybe log this exception.
			// Also shorten service not updated at this point.
			$log           = new Rop_Logger();
			$error_message = sprintf( esc_html__( 'The shortner service %1$s can NOT be built or was not found', 'tweet-old-post' ), $data['data']['short_url_service'] );
			$log->info( __( 'Shortner service can NOT be updated.', 'tweet-old-post' ) );
			$log->warn( $error_message . $exception->getMessage() );
			$this->response->set_code( '500' )->set_message( $error_message );
		}
		if ( $post_format->add_update_post_format( $data['account_id'], $data['data'] ) ) {
			$this->response->set_code( '201' )
			               ->set_message( sprintf( esc_html__( 'Post format was saved successfully. For the %1$s service', 'tweet-old-post' ), $data['service'] ) );
		}
		$this->response->set_data( $post_format->get_post_format() )->is_not_silent();

		return $this->response->to_array();
	}

	/**
	 * API method called to reset a post format to defaults.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function reset_post_format( $data ) {
		$post_format = new Rop_Post_Format_Model();
		$post_format->remove_post_format( $data['account_id'] );
		$this->response->set_code( '201' )
		               ->set_message( sprintf( esc_html__( 'Post format was reseted to defaults successfully. For the %1$s service', 'tweet-old-post' ), $data['service'] ) );

		$this->response->set_data( $post_format->get_post_format() );

		return $this->response->is_not_silent()->to_array();
	}

	/**
	 * API method called to retrieve a post format.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function get_post_format( $data ) {
		$post_format = new Rop_Post_Format_Model();
		$this->response->set_code( '200' )
		               ->set_message( sprintf( esc_html__( 'Post format was retrieved successfully. For the %1$s service', 'tweet-old-post' ), $data['service'] ) )
		               ->set_data( $post_format->get_post_format() );

		return $this->response->to_array();
	}

	/**
	 * API method called to select posts for publishing.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return mixed
	 */
	private function select_posts() {
		$posts_selector = new Rop_Posts_Selector_Model();
		$this->response->set_code( '200' )
		               ->set_message( __( 'Selected posts from the database. Here are the results.', 'tweet-old-post' ) )
		               ->set_data( $posts_selector->select() );

		return $this->response->to_array();
	}

	/**
	 * API method called to retrieve the general settings.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_general_settings() {
		$settings_model = new Rop_Settings_Model();
		$this->response->set_code( '200' )
		               ->set_message( __( 'Retrieved general settings from the database.', 'tweet-old-post' ) )
		               ->set_data( $settings_model->get_settings( true ) );

		return $this->response->to_array();
	}

	/**
	 * API method called to retrieve the taxonomies
	 * for the selected post types.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function get_taxonomies( $data ) {
		$post_selector = new Rop_Posts_Selector_Model();
		$taxonomies    = $post_selector->get_taxonomies( $data['post_types'] );
		$this->response->set_code( '400' )
		               ->set_message( __( 'Something happened when trying to retrieve taxonomies.', 'tweet-old-post' ) );
		if ( $taxonomies != false ) {
			$this->response->set_code( '200' )
			               ->set_message( __( 'Taxonomies retrieved successfully.', 'tweet-old-post' ) )
			               ->set_data( $taxonomies );
		}

		return $this->response->to_array();
	}

	/**
	 * API method called to retrieve the posts
	 * for the selected post types and taxonomies.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function get_posts( $data ) {
		$post_selector   = new Rop_Posts_Selector_Model();
		$available_posts = $post_selector->get_posts( $data['post_types'], $data['taxonomies'], $data['search_query'], $data['exclude'], $data['selected'] );

		$this->response->set_code( '200' )
		               ->set_message( __( 'Retrieved available posts from the database.', 'tweet-old-post' ) )
		               ->set_data( $available_posts );

		return $this->response->to_array();
	}

	/**
	 * API method called to save general settings.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data The settings data to save.
	 *
	 * @return array
	 */
	private function save_general_settings( $data ) {
		$general_settings = array(
			'default_interval'    => $data['default_interval'],
			'minimum_post_age'    => $data['minimum_post_age'],
			'maximum_post_age'    => $data['maximum_post_age'],
			'number_of_posts'     => $data['number_of_posts'],
			'more_than_once'      => $data['more_than_once'],
			'selected_post_types' => $data['post_types'],
			'selected_taxonomies' => $data['taxonomies'],
			'exclude_taxonomies'  => $data['exclude_taxonomies'],
			'selected_posts'      => $data['posts'],
			'ga_tracking'         => $data['ga_tracking'],
		);
		$settings_model   = new Rop_Settings_Model();
		$settings_model->save_settings( $general_settings );
		$this->response->set_code( '200' )
		               ->set_message( __( 'Updated settings', 'tweet-old-post' ) )
		               ->set_data( $settings_model->get_settings() );

		return $this->response->to_array();
	}

	/**
	 * API method called to save general settings for toggles.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data The settings data to save.
	 *
	 * @return array
	 */
	private function update_settings_toggle( $data ) {
		$settings_model                      = new Rop_Settings_Model();
		$general_settings                    = $settings_model->get_settings();
		$general_settings['beta_user']       = $data['beta_user'];
		$general_settings['remote_check']    = $data['remote_check'];
		$general_settings['custom_messages'] = $data['custom_messages'];
		$settings_model->save_settings( $general_settings );
		$this->response->set_code( '200' )
		               ->set_message( __( 'Updated settings', 'tweet-old-post' ) )
		               ->set_data( $settings_model->get_settings() );

		return $this->response->to_array();
	}

	/**
	 * API method called to retrieve available services.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_available_services() {
		$global_settings = new Rop_Global_Settings();
		$this->response->set_code( '200' )
		               ->set_message( __( 'Retrieved available services.', 'tweet-old-post' ) )
		               ->set_data( $global_settings->get_available_services() );

		return $this->response->to_array();
	}

	/**
	 * API method called to retrieve authenticated services.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_authenticated_services() {
		$model = new Rop_Services_Model();
		// $model->reset_authenticated_services();
		$this->response->set_code( '200' )
		               ->set_message( __( 'Retrieved authenticated services.', 'tweet-old-post' ) )
		               ->set_data( $model->get_authenticated_services() );

		return $this->response->to_array();
	}

	/**
	 * API method called to retrieve active accounts.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_active_accounts() {
		$model = new Rop_Services_Model();
		// $model->reset_authenticated_services();
		$this->response->set_code( '200' )
		               ->set_message( __( 'Retrieved active accounts.', 'tweet-old-post' ) )
		               ->set_data( $model->get_active_accounts() );

		return $this->response->to_array();
	}

	/**
	 * API method called to reset services.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function reset_accounts() {
		$model = new Rop_Services_Model();
		$model->reset_authenticated_services();
		$this->response->set_code( '200' )
		               ->set_message( __( 'Reseted.', 'tweet-old-post' ) )
		               ->set_data( array() );

		return $this->response->to_array();
	}

	/**
	 * API method called to update active accounts.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function update_active_accounts( $data ) {
		$new_active = array();
		foreach ( $data['to_be_activated'] as $account ) {
			$id           = $data['service'] . '_' . $data['service_id'] . '_' . $account['id'];
			$new_active[] = $id;
		}
		$model = new Rop_Services_Model();
		$this->response->set_code( '200' )
		               ->set_message( __( 'Active accounts updated.', 'tweet-old-post' ) )
		               ->set_data( $model->add_active_accounts( $new_active ) );

		return $this->response->to_array();
	}

	/**
	 * API method called to toggle account state.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function toggle_account( $data ) {
		$model = new Rop_Services_Model();
		// $post_format = new Rop_Post_Format_Model();
		// $schedules   = new Rop_Scheduler_Model();
		// $queue       = new Rop_Queue_Model();
		// $post_format->remove_post_format( $data['account_id'] );
		// $schedules->remove_schedule( $data['account_id'] );
		// $queue->remove_account_from_queue( $data['account_id'] );
		if ( $data['state'] === 'active' ) {
			$model->add_active_accounts( $data['account_id'] );
		} else {
			$model->delete_active_accounts( $data['account_id'] );
		}
		$this->response->set_code( '200' )
		               ->set_message( __( 'Account updated.', 'tweet-old-post' ) )
		               ->set_data( $data );

		return $this->response->to_array();
	}

	/**
	 * API method called to try and authenticate a service.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 * @Throws  Exception Throws an exception if a service can't be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return mixed|null
	 */
	private function authenticate_service( $data ) {
		$new_service = array();
		$factory     = new Rop_Services_Factory();
		try {
			${$data['service'] . '_services'} = $factory->build( $data['service'] );
			$authenticated                    = ${$data['service'] . '_services'}->authenticate();
			if ( $authenticated ) {
				$service                    = ${$data['service'] . '_services'}->get_service();
				$service_id                 = $service['service'] . '_' . $service['id'];
				$new_service[ $service_id ] = $service;
			}

			$model = new Rop_Services_Model();

			return $model->add_authenticated_service( $new_service );
		} catch ( Exception $exception ) {
			// Service can't be built. Not found or otherwise. Maybe log this.
			$log = new Rop_Logger();
			$log->warn( 'The service "' . $data['service'] . '" can NOT be built or was not found', $exception );

			return null;
		}
	}

	/**
	 * API method called to try and remove a service.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return mixed|null
	 */
	private function remove_service( $data ) {
		$model = new Rop_Services_Model();

		return $model->delete_authenticated_service( $data['id'], $data['service'] );
	}

	/**
	 * API method called to retrieve a service sign in url.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 * @Throws  Exception Throws an exception if the service can't be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function get_service_sign_in_url( $data ) {
		$url     = '';
		$factory = new Rop_Services_Factory();
		try {

			${$data['service'] . '_services'} = $factory->build( $data['service'] );
			if ( empty( $data['credentials'] ) ) {
				$authenticated_services = new Rop_Services_Model();
				$service                = $authenticated_services->get_authenticated_services( $data['service'] );
				if ( ! empty( $service ) ) {
					$service = reset( $service );
					if ( ! empty( $service['public_credentials'] ) ) {
						$data['credentials'] = array_combine( array_keys( $service['public_credentials'] ), wp_list_pluck( $service['public_credentials'], 'value' ) );
					}
				}
			}
			if ( ${$data['service'] . '_services'} ) {
				/* @noinspection PhpUndefinedMethodInspection */
				$url = ${$data['service'] . '_services'}->sign_in_url( $data );
			}
		} catch ( Exception $exception ) {
			// Service can't be built. Not found or otherwise. Maybe log this.
			$log = new Rop_Logger();
			$log->warn( 'The service "' . $data['service'] . '" can NOT be built or was not found' . $exception->getMessage() );
			$url = '';
		}

		return array( 'url' => $url );
	}

	/**
	 * API method called to retrieve the logs.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return string
	 */
	private function get_log( $data ) {
		$log = new Rop_Logger();
		if ( isset( $data['force'] ) ) {
			$log->clear_user_logs();
		}
		$this->response->set_code( '200' )
		               ->set_message( __( 'Logs retrieved successfully.', 'tweet-old-post' ) )
		               ->set_data( $log->get_logs() );

		return $this->response->to_array();
	}

}
