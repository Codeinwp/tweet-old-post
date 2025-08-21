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
	 * @var     object $response The default response.
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
			'rest_api_init',
			function () {
				register_rest_route(
					'tweet-old-post/v8',
					'/api',
					array(
						'methods'             => array( 'GET', 'POST' ),
						'callback'            => array( $this, 'api' ),
						'permission_callback' => function () {
							return current_user_can( 'manage_options' );
						},
					)
				);
			}
		);

		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'tweet-old-post/v8',
					'/share/(?P<id>[a-zA-Z0-9_-]+)',
					array(
						'methods'             => array( 'POST' ),
						'callback'            => array( $this, 'share' ),
						'permission_callback' => function () {
							return current_user_can( 'edit_posts' );
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
						->set_data( $cron_helper->manage_cron( $data ) );

		return $this->response->to_array();
	}

	/**
	 * Rest Api called, will update the variable which informs the system
	 * to use local or remote Cron Job System.
	 *
	 * @param array $data Data passed from the AJAX call.
	 *
	 * @return array
	 * @since   8.6.0
	 * @access  private
	 * @category New Cron System
	 */
	private function update_cron_type( $data ) {

		$cron_helper = new Rop_Cron_Helper();
		$this->response->set_code( '200' )
					->set_data( $cron_helper->update_cron_type( $data ) );

		return $this->response->to_array();
	}

	/**
	 * Saves user agreeing with the Terms and Conditions for the remote CronJob system.
	 *
	 * @param array $data Data passed from the AJAX call.
	 *
	 * @return array
	 * @since   8.6.0
	 * @access  private
	 * @category New Cron System
	 */
	private function update_cron_type_agreement( $data ) {
		$response = false;

		if ( ! empty( $data ) && isset( $data['action'] ) ) {
			update_option( 'rop_remote_cron_terms_agree', $data['action'] );
			$response = true;
		}

		$this->response->set_code( '200' )
						->set_data( $response );

		return $this->response->to_array();
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

		if ( isset( $data['force'] ) && true === (bool) $data['force'] ) {
			$queue->clear_queue();
		}
		$this->response->set_code( '200' )
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

		$cron_status = filter_var( get_option( 'rop_is_sharing_cron_active', 'no' ), FILTER_VALIDATE_BOOLEAN );

		if ( true === $cron_status && defined( 'ROP_CRON_ALTERNATIVE' ) && true === ROP_CRON_ALTERNATIVE ) {
			$server_url = ROP_CRON_DOMAIN . '/wp-json/update-cron-ping/v1/update-time-to-share/';
			// inform the cron server to ping this website in the next process.
			$time_to_share = array(
				'next_ping' => current_time( 'mysql' ), // phpcs:ignore
			);

			RopCronSystem\ROP_Helpers\Rop_Helpers::custom_curl_post_request( $server_url, $time_to_share );
		}

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
									->set_data( array() )->to_array();
		}
		if ( $data['short_url_service'] === 'wp_short_url' ) {
			return $this->response->set_code( '200' )
									->set_data( array() )->to_array();
		}

		$sh_factory = new Rop_Shortner_Factory();

		$this->response->set_code( '500' );

		try {
			$shortner = $sh_factory->build( $data['short_url_service'] );
			$this->response->set_code( '200' )
							->set_data( $shortner->get_credentials( true ) );
		} catch ( Exception $exception ) {
			// Service not found or can't be built. Maybe log this exception.
			$log           = new Rop_Logger();
			$error_message = sprintf( 'The shortner service %1$s can NOT be built or was not found', $data['short_url_service'] );
			$log->alert_error( $error_message . $exception->getMessage() );
			$this->response->set_code( '500' );
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
		$post_format = new Rop_Post_Format_Model( $data['service'] );
		$sh_factory  = new Rop_Shortner_Factory();
		$this->response->set_code( '500' );

		if ( $data['service'] === 'twitter' ) {

			$max_char_length = $data['data']['maximum_length'];

			if ( $max_char_length > 280 ) {
				$data['data']['maximum_length'] = 280;
			}
		}

		// New users will require a Pro plan (from version 9.1).
		$global_settings = new Rop_Global_Settings();
		$is_new_user     = (int) get_option( 'rop_is_new_user', 0 );
		if ( $global_settings->license_type() <= 0 && $is_new_user ) {
			if ( 'custom_field' === $data['data']['post_content'] ) {
				$data['data']['post_content'] = 'post_title';
			}
			if ( ! in_array( $data['data']['hashtags'], array( 'no-hashtags', 'common-hashtags' ), true ) ) {
				$data['data']['hashtags'] = 'no-hashtags';
			}
			if ( ! in_array( $data['data']['short_url_service'], array( 'rviv.ly', 'wp_short_url' ), true ) ) {
				$data['data']['short_url_service'] = 'rviv.ly';
			}
		}

		// If the user forget to switch from the upsell value, set it to the default value.
		if ( 'custom_content' === $data['data']['post_content'] && $global_settings->license_type() <= 0 ) {
			$data['data']['post_content'] = 'post_title';
		}

		try {
			if ( $data['data']['short_url_service'] !== 'wp_short_url' ) {
				$shortner = $sh_factory->build( $data['data']['short_url_service'] );
				$shortner->set_credentials( $data['data']['shortner_credentials'] );
			}
			$this->response->set_code( '201' );
		} catch ( Exception $exception ) {
			// Service not found or can't be built. Maybe log this exception.
			// Also shorten service not updated at this point.
			$log           = new Rop_Logger();
			$error_message = sprintf( 'The shortner service %1$s can NOT be built or was not found', $data['data']['short_url_service'] );
			$log->alert_error( $error_message . $exception->getMessage() );
			$this->response->set_code( '500' );
		}
		if ( $post_format->add_update_post_format( $data['account_id'], $data['data'] ) ) {
			$this->response->set_code( '201' );
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
		$this->response->set_code( '201' );

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
		$settings_model = new Rop_Settings_Model();
		$taxonomies     = $settings_model->get_available_taxonomies( $data );
		$this->response->set_code( '400' );
		if ( $taxonomies != false ) {
			$this->response->set_code( '200' )
							->set_data( $taxonomies );
		}

		return $this->response->to_array();
	}

	/**
	 * API method to exclude single post
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.4
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function exclude_post( $data ) {

		$settings_model = new Rop_Settings_Model();
		$flag           = (bool) $data['exclude'];
		if ( ! $flag ) {
			$settings_model->add_excluded_posts( $data['post_id'] );
		} else {
			$settings_model->remove_excluded_posts( $data['post_id'] );
		}

		$this->response->set_code( '200' )
						->set_data( $data );

		return $this->response->to_array( $data );
	}

	/**
	 * Api method to exclude posts based on keywords.
	 *
	 * @since   8.0.4
	 * @access  private
	 *
	 * @param   array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function exclude_post_batch( $data ) {
		$search          = sanitize_text_field( $data['search'] );
		$post_selector   = new Rop_Posts_Selector_Model();
		$available_posts = $post_selector->get_posts( $data['post_types'], $data['taxonomies'], $data['exclude'], $search, false, false );
		$post_ids        = wp_list_pluck( $available_posts, 'value' );

		$settings_model = new Rop_Settings_Model();
		$settings_model->add_excluded_posts( $post_ids );

		$this->response->set_code( '200' )
						->set_data( $data );

		return $this->response->to_array( $data );
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
		$available_posts = $post_selector->get_posts( $data['post_types'], $data['taxonomies'], $data['exclude'], $data['search_query'], $data['show_excluded'], $data['page'] );

		$this->response->set_code( '200' )
					->set_data(
						array(
							'posts' => $available_posts,
							'page'  => $data['page'],
						)
					);

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

		$settings_model = new Rop_Settings_Model();
		// Fetch the already saved settings.
		$saved_data = $settings_model->get_settings();
		$settings_model->save_settings( $data );
		$this->response->set_code( '200' )
						->set_data( $settings_model->get_settings() );

		// Save tracking flag.
		$tracking = filter_var( $data['tracking'], FILTER_VALIDATE_BOOLEAN );
		update_option( 'tweet_old_post_logger_flag', $tracking ? 'yes' : 'no' );

		$cron_status = filter_var( get_option( 'rop_is_sharing_cron_active', 'no' ), FILTER_VALIDATE_BOOLEAN );

		if ( true === $cron_status && defined( 'ROP_CRON_ALTERNATIVE' ) && true === ROP_CRON_ALTERNATIVE ) {
			$new_default_interval = trim( $data['default_interval'] );

			$saved_default_interval = trim( $saved_data['default_interval'] );

			if ( $new_default_interval !== $saved_default_interval ) {

				$server_url = ROP_CRON_DOMAIN . '/wp-json/update-cron-ping/v1/update-time-to-share/';

				// inform the cron server to ping this website in the next process.
				$time_to_share = array(
					'next_ping' => current_time( 'mysql' ), // phpcs:ignore
				);

				RopCronSystem\ROP_Helpers\Rop_Helpers::custom_curl_post_request( $server_url, $time_to_share );
			}
		}

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
		$this->response->set_code( '200' )
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
		$model                 = new Rop_Services_Model();
		$saved_active_accounts = $model->get_active_accounts();
		$available_services    = $model->get_authenticated_services();

		// Return the active accounts that are also available.
		$valid_accounts         = array();
		$available_accounts_ids = array();

		foreach ( $available_services as $_ => $service ) {
			if ( ! isset( $service['available_accounts'] ) ) {
				continue;
			}

			foreach ( $service['available_accounts'] as $account_id => $_ ) {
				$available_accounts_ids[] = $account_id;
			}
		}

		foreach ( $saved_active_accounts as $active_account_id => $active_account ) {
			if ( ! in_array( $active_account_id, $available_accounts_ids, true ) ) {
				continue;
			}

			$valid_accounts[ $active_account_id ] = $active_account;
		}

		$this->response->set_code( '200' )
						->set_data( $valid_accounts );

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
		if ( $data['state'] === 'active' ) {
			$model->add_active_accounts( $data['account_id'] );
		} else {
			$model->delete_active_accounts( $data['account_id'] );
		}
		$this->response->set_code( '200' )
						->set_data( $data );

		return $this->response->to_array();
	}

	/**
	 * Remove account from the available list.
	 *
	 * @param array $data Data from the request.
	 *
	 * @return array Data received.
	 */
	private function remove_account( $data ) {
		$this->response->set_code( '200' )
						->set_data( $data );

		$model = new Rop_Services_Model();
		$model->remove_service_account( $data['account_id'] );

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
			$log->alert_error( 'The service "' . $data['service'] . '" can NOT be built or was not found', $exception );

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
	 * Used to create an authentication url for users who want to use their own app via personal auth keys/tokens.
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
					$service = array_filter(
						$service,
						function ( $value ) {
							return ! empty( $value['public_credentials'] );
						}
					);
					$service = reset( $service );
					if ( ! empty( $service['public_credentials'] ) ) {
						$data['credentials'] = array_combine( array_keys( $service['public_credentials'] ), wp_list_pluck( $service['public_credentials'], 'value' ) );
					}
				}
			}
			if ( ${$data['service'] . '_services'} ) {
				if ( method_exists( ${$data['service'] . '_services'}, 'sign_in_url' ) ) {
					$url = ${$data['service'] . '_services'}->sign_in_url( $data );
				} else {
					$url = '';
				}
			}
		} catch ( Exception $exception ) {
			// Service can't be built. Not found or otherwise. Maybe log this.
			$log = new Rop_Logger();
			$log->alert_error( 'The service "' . $data['service'] . '" can NOT be built or was not found' . $exception->getMessage() );
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
						->set_data( $log->get_logs() );

		return $this->response->to_array();
	}

	/**
	 * This will disable facebook domain check toast message.
	 *
	 * @param mixed $data The data.
	 *
	 * @return array
	 */
	private function fb_exception_toast( $data ) {
		update_option( 'rop_facebook_domain_toast', 'no' );
		$this->response->set_code( '200' )
						->set_message( 'Facebook domain check toast new status is closed' )
						->set_data( array( 'display' => false ) );

		return $this->response->to_array();
	}

	/**
	 * API method called to retrieve the logs for toast.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.4.2
	 * @access  private
	 * @return string
	 */
	private function get_toast( $data ) {
		$log = new Rop_Logger();

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( $log->get_logs() );

		$logs_response = $this->response->to_array();

		$logs_data = $logs_response['data'];

		if ( ! empty( $logs_data ) ) {
			$custom_response = 0;
			// Is it a status alert?
			$is_status_logs_alert = $log->is_status_error_necessary( $logs_response ); // true | false
			// The logs will contain latest entry  as first element first.
			reset( $logs_data ); // reset pointer to first element
			$latest_log_entry      = current( $logs_data ); // fetch the latest log entry
			$logs_response['data'] = array(); // reset data
			// Making sure it contains the important attributes.
			if ( isset( $latest_log_entry['message'] ) && isset( $latest_log_entry['type'] ) ) {
				// fetch log entry data;
				$channel = $latest_log_entry['channel'];
				$type    = $latest_log_entry['type'];
				$message = $latest_log_entry['message'];
				$time    = (int) $latest_log_entry['time'];

				if ( 'error' === $type ) { // Not displaying anything if there's no issue
					$get_last_err_timestamp = (int) get_option( 'rop_toast', 0 ); // get the last error timestamp
					if ( $get_last_err_timestamp !== $time ) { // If the time does not match, then proceed further.
						// Check to see if the error needs to be "translated"
						$latest_log_entry['message'] = $log->translate_messages( $message );
						$logs_response['data'][]     = $latest_log_entry;
						// Add the timestamp of the error into DB to now show this alert multiple times.
						update_option( 'rop_toast', $time, 'no' );
						++$custom_response;
					}
				}
			}

			// We need to inform the user as there are many errors in the log
			// This will change the status to "Error (check logs)"
			if ( true === $is_status_logs_alert ) {
				++$custom_response;
				$logs_response['data'][] = array(
					'type'    => 'status_error',
					'message' => '',
					'channel' => 'rop_logs',
					'time'    => Rop_Scheduler_Model::get_current_time(),
				);
			}

			if ( ! empty( $custom_response ) ) {
				// return the current error
				return $logs_response;
			}
		}

		$logs_response['data'] = array();

		return $logs_response;
	}

	/**
	 * API method called to add Facebook pages via app.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   ...
	 * @access  private
	 *
	 * @param   array $data Facebook page data.
	 *
	 * @return  array
	 */
	private function add_account_fb( $data ) {

		$services         = array();
		$active_accounts  = array();
		$facebook_service = new Rop_Facebook_Service();
		$model            = new Rop_Services_Model();
		$db               = new Rop_Db_Upgrade();

		$facebook_service->add_account_with_app( $data );

		$services[ $facebook_service->get_service_id() ] = $facebook_service->get_service();
		$active_accounts                                 = array_merge( $active_accounts, $facebook_service->get_service_active_accounts() );

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( array() );

		$rop_facebook_via_rs_app_option = 'rop_facebook_via_rs_app';
		if ( ! get_option( $rop_facebook_via_rs_app_option ) ) {
			add_option( $rop_facebook_via_rs_app_option, 'true', ' ', 'no' );
		} else {
			update_option( $rop_facebook_via_rs_app_option, 'true' );
		}

		return $this->response->to_array();
	}


	/**
	 * API method called to add Twitter pages via app.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.4.0
	 * @access  private
	 *
	 * @param   array $data Twitter account data.
	 *
	 * @return  array
	 */
	private function add_account_tw( $data ) {
		$services        = array();
		$active_accounts = array();
		$twitter_service = new Rop_Twitter_Service();
		$model           = new Rop_Services_Model();
		$db              = new Rop_Db_Upgrade();

		if ( ! empty( $data['pages'] ) && ! empty( $data['pages']['credentials']['rop_auth_token'] ) ) {
			$twitter_service->add_account_from_rop_server( $data );
		} else {
			$twitter_service->add_account_with_app( $data );
		}

		$services[ $twitter_service->get_service_id() ] = $twitter_service->get_service();
		$active_accounts                                = array_merge( $active_accounts, $twitter_service->get_service_active_accounts() );

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( array() );

		$rop_twitter_via_rs_app_option = 'rop_twitter_via_rs_app';
		if ( ! get_option( $rop_twitter_via_rs_app_option ) ) {
			add_option( $rop_twitter_via_rs_app_option, 'true', ' ', 'no' );
		} else {
			update_option( $rop_twitter_via_rs_app_option, 'true' );
		}

		return $this->response->to_array();
	}

	/**
	 * API method called to add Linkedin pages via app.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.5.0
	 * @access  private
	 *
	 * @param   array $data LinkedIn accounts data.
	 *
	 * @return  array
	 */
	private function add_account_li( $data ) {

		$services        = array();
		$active_accounts = array();
		$linkedin_service = new Rop_Linkedin_Service();
		$model           = new Rop_Services_Model();
		$db              = new Rop_Db_Upgrade();

		$linkedin_service->add_account_with_app( $data );

		$services[ $linkedin_service->get_service_id() ] = $linkedin_service->get_service();
		$active_accounts                                = array_merge( $active_accounts, $linkedin_service->get_service_active_accounts() );

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( array() );

		$rop_linkedin_via_rs_app_option = 'rop_linkedin_via_rs_app';
		if ( ! get_option( $rop_linkedin_via_rs_app_option ) ) {
			add_option( $rop_linkedin_via_rs_app_option, 'true', ' ', 'no' );
		} else {
			update_option( $rop_linkedin_via_rs_app_option, 'true' );
		}

		return $this->response->to_array();
	}

	/**
	 * API method called to add Tumblr Blogs via app.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.5.7
	 * @access  private
	 *
	 * @param   array $data Tumblr accounts data.
	 *
	 * @return  array
	 */
	private function add_account_tumblr( $data ) {
		$services        = array();
		$active_accounts = array();
		$tumblr_service  = new Rop_Tumblr_Service();
		$model           = new Rop_Services_Model();
		$db              = new Rop_Db_Upgrade();

		$tumblr_service->add_account_with_app( $data );

		$services[ $tumblr_service->get_service_id() ] = $tumblr_service->get_service();
		$active_accounts                                = array_merge( $active_accounts, $tumblr_service->get_service_active_accounts() );

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( array() );

		$rop_tumblr_via_rs_app_option = 'rop_tumblr_via_rs_app';
		if ( ! get_option( $rop_tumblr_via_rs_app_option ) ) {
			add_option( $rop_tumblr_via_rs_app_option, 'true', ' ', 'no' );
		} else {
			update_option( $rop_tumblr_via_rs_app_option, 'true' );
		}

		return $this->response->to_array();
	}

	/**
	 * API method called to add Google My Business locations via app.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.5.9
	 * @access  private
	 *
	 * @param   array $data Google My Business accounts data.
	 *
	 * @return  array
	 */
	private function add_account_gmb( $data ) {
		$services        = array();
		$active_accounts = array();
		$gmb_service     = new Rop_Gmb_Service();
		$model           = new Rop_Services_Model();
		$db              = new Rop_Db_Upgrade();

		$gmb_service->add_account_with_app( $data );

		$services[ $gmb_service->get_service_id() ] = $gmb_service->get_service();
		$active_accounts                                = array_merge( $active_accounts, $gmb_service->get_service_active_accounts() );

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( array() );

		return $this->response->to_array();
	}
	/**
	 * API method called to add VK via app.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.6.1
	 * @access  private
	 *
	 * @param   array $data Vk accounts data.
	 *
	 * @return  array
	 */
	private function add_account_vk( $data ) {
		$services        = array();
		$active_accounts = array();
		$vk_service     = new Rop_Vk_Service();
		$model           = new Rop_Services_Model();
		$db              = new Rop_Db_Upgrade();

		$vk_service->add_account_with_app( $data );

		$services[ $vk_service->get_service_id() ] = $vk_service->get_service();
		$active_accounts                                = array_merge( $active_accounts, $vk_service->get_service_active_accounts() );

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( array() );

		return $this->response->to_array();
	}

	/**
	 * API method to call the license processor.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since 9.1.0
	 *
	 * @param array $data Data passed from the AJAX call.
	 *
	 * @return array
	 */
	private function set_license( $data ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->response
				->set_code( '403' )
				->set_message( 'Forbidden' )
				->set_data(
					array(
						'success' => false,
						'message' => Rop_I18n::get_labels( 'general.no_permission' ),
					) 
				);

			return $this->response->to_array();
		}

		// NOTE: The license processor requires the license key, even if we want to deactivate the license.
		if ( empty( $data['license_key'] ) ) {
			$general_settings = new Rop_Global_Settings();
			$license_data     = $general_settings->get_license_data();
			if ( ! empty( $license_data ) && isset( $license_data->key ) ) {
				$data['license_key'] = $license_data->key;
			}
		}

		$response = apply_filters( 'themeisle_sdk_license_process_rop', $data['license_key'], $data['action'] );

		if ( is_wp_error( $response ) ) {
			return $this->response
				->set_data(
					array(
						'success' => false,
						'message' => 'activate' === $data['action'] ? Rop_I18n::get_labels( 'general.validation_failed' ) : Rop_I18n::get_labels( 'general.could_not_change_license' ),
					) 
				)
				->to_array();
		}

		return $this->response
			->set_code( '200' )
			->set_data( array( 'success' => true ) )
			->to_array();
	}

	/**
	 * API method called to add Webhook account.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   9.1.0
	 * @access  private
	 *
	 * @param  array $data Webhook account data.
	 *
	 * @return array
	 */
	private function add_account_webhook( $data ) {
		$services        = array();
		$webhook_service = new Rop_Webhook_Service();
		$model           = new Rop_Services_Model();
		$db              = new Rop_Db_Upgrade();

		if ( ! $webhook_service->add_webhook( $data ) ) {
			$this->response->set_code( '422' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$services[ $webhook_service->get_service_id() ] = $webhook_service->get_service();
		$active_accounts                                = $webhook_service->get_service_active_accounts();

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( array() );

		return $this->response->to_array();
	}

	/**
	 * API method called to edit Webhook account.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   9.1.0
	 * @access  private
	 *
	 * @param  array $data Webhook account data.
	 *
	 * @return array
	 */
	private function edit_account_webhook( $data ) {
		$webhook_service = new Rop_Webhook_Service();
		$model           = new Rop_Services_Model();

		if ( ! $webhook_service->add_webhook( $data ) ) {
			$this->response->set_code( '422' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$service_id             = ! empty( $data['service_id'] ) ? $data['service_id'] : '';
		$authenticated_services = $model->get_authenticated_services();

		if ( ! isset( $authenticated_services[ $service_id ] ) ) {
			$this->response->set_code( '422' )
							->set_data( array() );

			return $this->response->to_array();
		}

		$authenticated_services[ $service_id ] = array_merge( $authenticated_services[ $service_id ], $webhook_service->get_service() );

		$model->update_authenticated_services( $authenticated_services );

		if ( ! empty( $data['active'] ) && ! empty( $data['full_id'] ) ) {
			$model->add_active_accounts( array( $data['full_id'] ) );
		}

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( array() );

		return $this->response->to_array();
	}

	/**
	 * API method called to add Telegram via app.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   9.1.3
	 * @access  private
	 *
	 * @param   array $data Telegram accounts data.
	 *
	 * @return  array
	 */
	private function add_account_telegram( $data ) {
		$services        = array();
		$webhook_service = new Rop_Telegram_Service();
		$model           = new Rop_Services_Model();
		$db              = new Rop_Db_Upgrade();

		if ( ! $webhook_service->add_account_with_app( $data ) ) {
			$this->response->set_code( '422' )
			->set_data( array() );

			return $this->response->to_array();
		}

		$services[ $webhook_service->get_service_id() ] = $webhook_service->get_service();
		$active_accounts                                = $webhook_service->get_service_active_accounts();

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
			->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
		->set_message( 'OK' )
		->set_data( array() );

		return $this->response->to_array();
	}

	/**
	 * API method called to add mastodon via app.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.6.1
	 * @access  private
	 *
	 * @param   array $data Mastodon accounts data.
	 *
	 * @return  array
	 */
	private function add_account_mastodon( $data ) {
		$services        = array();
		$active_accounts = array();
		$md_service      = new Rop_Mastodon_Service();
		$model           = new Rop_Services_Model();
		$db              = new Rop_Db_Upgrade();

		$md_service->add_account_with_app( $data );

		$services[ $md_service->get_service_id() ] = $md_service->get_service();
		$active_accounts                           = array_merge( $active_accounts, $md_service->get_service_active_accounts() );

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
			->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
		->set_message( 'OK' )
		->set_data( array() );

		return $this->response->to_array();
	}

	/**
	 * API method called to add Bluesky via app.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   9.3.0
	 * @access  private
	 *
	 * @param   array $data Bluesky accounts data.
	 *
	 * @return  array
	 */
	private function add_account_bluesky( $data ) {
		$services        = array();
		$active_accounts = array();
		$bs_service      = new Rop_Bluesky_Service();
		$model           = new Rop_Services_Model();
		$db              = new Rop_Db_Upgrade();

		$bs_service->add_account_with_app( $data );

		$services[ $bs_service->get_service_id() ] = $bs_service->get_service();
		$active_accounts                           = array_merge( $active_accounts, $bs_service->get_service_active_accounts() );

		if ( ! empty( $services ) ) {
			$model->add_authenticated_service( $services );
		}

		if ( ! empty( $active_accounts ) ) {
			$db->migrate_schedule( $active_accounts );
			$db->migrate_post_formats( $active_accounts );
		} else {
			$this->response->set_code( '500' )
			->set_data( array() );

			return $this->response->to_array();
		}

		$this->response->set_code( '200' )
						->set_message( 'OK' )
						->set_data( array() );

		return $this->response->to_array();
	}

	/**
	 * Share API method.
	 *
	 * @access  public
	 *
	 * @param   WP_REST_Request $request The request object.
	 *
	 * @return array|mixed|null|string
	 */
	public function share( WP_REST_Request $request ) {
		$post_id = $request->get_param( 'id' );
		$body    = json_decode( $request->get_body(), true );

		if ( ! isset( $body['rop_publish_now_accounts'] ) || ! is_array( $body['rop_publish_now_accounts'] ) || empty( $body['rop_publish_now_accounts'] ) ) {
			return rest_ensure_response(
				new WP_Error(
					'invalid_request',
					__( 'Invalid request. Missing accounts.', 'tweet-old-post' ),
					array(
						'status' => 400,
					)
				)
			);
		}

		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_accounts', $body['rop_publish_now_accounts'] );

		do_action(
			'rop_publish_now_instant_share',
			$post_id,
			true,
		);

		return rest_ensure_response(
			array(
				'success' => true,
				'history' => get_post_meta( $post_id, 'rop_publish_now_history', true ),
				'message' => __( 'Post shared successfully.', 'tweet-old-post' ),
			)
		);
	}
}
