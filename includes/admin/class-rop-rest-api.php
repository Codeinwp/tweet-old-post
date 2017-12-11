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
		add_action( 'rest_api_init', function () {
			register_rest_route( 'tweet-old-post/v8', '/api', array(
				'methods' => array( 'GET', 'POST' ),
				'callback' => array( $this, 'api' ),
			) );
		} );
	}

	/**
	 * The api switch and entry point.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   WP_REST_Request $request The request object.
	 * @return array|mixed|null|string
	 */
	public function api( WP_REST_Request $request ) {
		$response = $this->response;
		$method_requested = $request->get_param( 'req' );
		if ( method_exists( $this, $method_requested ) ) {
			$data = json_decode( $request->get_body(), true );
			if ( ! empty( $data ) ) {
				$response = $this->$method_requested( $data );
			} else {
				$response = $this->$method_requested();
			}
		}

		return $response;
	}

	/**
	 * API method called to publish a queue event and return active queue.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 * @Throws Exception If a service can not be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function publish_queue_event( $data ) {
		$queue = new Rop_Queue_Model();
		$services_model = new Rop_Services_Model();
		$account_data = $services_model->find_account( $data['account_id'] );
		$this->response->set_code( '400' )->set_message( __( 'Bad request!', 'tweet-old-post' ) );
		if ( $account_data ) {
			$service_factory = new Rop_Services_Factory();
			try {
				$service = $service_factory->build( $account_data['service'] );
				$service->set_credentials( $account_data['credentials'] );
				$queue_event = $queue->remove_from_queue( $data['index'], $data['account_id'] );
				// $service->share( $queue_event, $account_data );
				$this->response->set_code( '201' )
				               ->set_message( sprintf( esc_html__( 'The post was shared successfully with the %1$s network', 'tweet-old-post' ), $account_data['service'] ) )
				               ->is_not_silent()
				               ->set_data( $queue->get_ordered_queue() );
			} catch ( Exception $exception ) {
			    // The service can not be built or was not found.
				$log = new Rop_Logger();
				$error_message = sprintf( esc_html__( 'The service %1$s can NOT be built or was not found', 'tweet-old-post' ), $account_data['service'] );
				$log->warn( $error_message, $exception );
				$this->response->set_code( '500' )->set_message( $error_message );
			}
		}

		return $this->response->to_array();
	}

	/**
	 * API method called to skip a queue event and return active queue.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function skip_queue_event( $data ) {
		$queue = new Rop_Queue_Model();
		$this->response->set_code( '500' )->set_message( __( 'An error occurred when trying to skip post.', 'tweet-old-post' ) );
		if ( $queue->skip_post( $data['index'], $data['account_id'] ) ) {
			$this->response->set_code( '201' )
			               ->set_message( __( 'The post was skipped successfully.', 'tweet-old-post' ) )
			               ->is_not_silent()
			               ->set_data( $queue->get_ordered_queue() );
		}
		return $this->response->to_array();
	}

	/**
	 * API method called to block a queue event and return active queue.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function block_queue_event( $data ) {
		$queue = new Rop_Queue_Model();
		$this->response->set_code( '500' )->set_message( __( 'An error occurred when trying to block post.', 'tweet-old-post' ) );
		if ( $queue->ban_post( $data['index'], $data['account_id'] ) ) {
			$this->response->set_code( '201' )
			               ->set_message( __( 'The post was blocked successfully.', 'tweet-old-post' ) )
			               ->is_not_silent()
			               ->set_data( $queue->get_ordered_queue() );
		}
		return $this->response->to_array();
	}

	/**
	 * API method called to update a queue event and return active queue.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function update_queue_event( $data ) {
		$queue = new Rop_Queue_Model();
		$this->response->set_code( '500' )->set_message( __( 'An error occurred when trying to update the post.', 'tweet-old-post' ) );
		if ( $queue->update_queue_object( $data['account_id'], $data['post_id'], $data['custom_data'] ) ) {
			$this->response->set_code( '201' )
			               ->set_message( __( 'The post was updated successfully.', 'tweet-old-post' ) )
			               ->is_not_silent()
			               ->set_data( $queue->get_ordered_queue() );
		}
		return $this->response->to_array();
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
	private function get_queue() {
	    $queue = new Rop_Queue_Model();
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
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function save_schedule( $data ) {
		$schedules = new Rop_Scheduler_Model();
		$this->response->set_code( '500' )->set_message( __( 'An error occurred when trying to update the schedule.', 'tweet-old-post' ) );
		if ( $schedules->add_update_schedule( $data['account_id'], $data['schedule'] ) ) {
			$this->response->set_code( '201' )
			               ->set_message( __( 'Schedule saved successfully.', 'tweet-old-post' ) )
			               ->is_not_silent()
			               ->set_data( $schedules->get_schedule( $data['account_id'] ) );
		}
		return $this->response->to_array();
	}

	/**
	 * API method called to reset a schedule to defaults.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function reset_schedule( $data ) {
		$schedules = new Rop_Scheduler_Model();
		$this->response->set_code( '500' )->set_message( __( 'An error occurred when trying to update the schedule.', 'tweet-old-post' ) );
		if ( $schedules->remove_schedule( $data['account_id'] ) ) {
			$this->response->set_code( '201' )
			               ->set_message( __( 'Schedule was reset successfully.', 'tweet-old-post' ) )
			               ->is_not_silent()
			               ->set_data( $schedules->get_schedule( $data['account_id'] ) );
		}
		return $this->response->to_array();
	}

	/**
	 * API method called to retrieve a schedule.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function get_schedule( $data ) {
		$schedules = new Rop_Scheduler_Model();
		$this->response->set_code( '200' )
			->set_message( __( 'Schedule was retrieved successfully.', 'tweet-old-post' ) )
			->set_data( $schedules->get_schedule( $data['account_id'] ) );
		return $this->response->to_array();
	}

	/**
	 * API method called to get shortner service credentials.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 * @Throws Exception Throws an exception if a short url service can't be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return mixed
	 */
	private function get_shortner_credentials( $data ) {
	    $sh_factory = new Rop_Shortner_Factory();
		$this->response->set_code( '500' )->set_message( __( 'An error occurred when trying to retrieve the sortner service credentials.', 'tweet-old-post' ) );
	    try {
			$shortner = $sh_factory->build( $data['short_url_service'] );
		    $this->response->set_code( '200' )
		                   ->set_message( __( 'Shortner credentials retrieved successfully.', 'tweet-old-post' ) )
		                   ->set_data( $shortner->get_credentials() );
		} catch ( Exception $exception ) {
	        // Service not found or can't be built. Maybe log this exception.
		    $log = new Rop_Logger();
		    $error_message = sprintf( esc_html__( 'The shortner service %1$s can NOT be built or was not found', 'tweet-old-post' ), $data['short_url_service'] );
		    $log->warn( $error_message, $exception );
		    $this->response->set_code( '500' )->set_message( $error_message );
		}
		return $this->response->to_array();
	}

	/**
	 * API method called to save a post format.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 * @Throws Exception Throws an exception if a short url service can't be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function save_post_format( $data ) {
		$post_format = new Rop_Post_Format_Model( $data['service'] );
		$sh_factory = new Rop_Shortner_Factory();
		try {
			$shortner = $sh_factory->build( $data['post_format']['short_url_service'] );
			$shortner->set_credentials( $data['post_format']['shortner_credentials'] );
		} catch ( Exception $exception ) {
			// Service not found or can't be built. Maybe log this exception.
			// Also shorten service not updated at this point.
			$log = new Rop_Logger();
			$log->info( 'Shortner service can NOT be updated.' );
			$log->warn( 'The shortner service "' . $data['post_format']['short_url_service'] . '" can NOT be built or was not found', $exception );
		}
		$post_format->add_update_post_format( $data['account_id'], $data['post_format'] );
		return $post_format->get_post_format( $data['account_id'] );
	}

	/**
	 * API method called to reset a post format to defaults.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function reset_post_format( $data ) {
		$post_format = new Rop_Post_Format_Model( $data['service'] );
		$post_format->remove_post_format( $data['account_id'] );
		return $post_format->get_post_format( $data['account_id'] );
	}

	/**
	 * API method called to retrieve a post format.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function get_post_format( $data ) {
		$post_format = new Rop_Post_Format_Model( $data['service'] );
		return $post_format->get_post_format( $data['account_id'] );
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
	    return $posts_selector->select();
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
		return $settings_model->get_settings();
	}

	/**
	 * API method called to retrieve the taxonomies
	 * for the selected post types.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function get_taxonomies( $data ) {
	    $taxonomies = array();
	    foreach ( $data['post_types'] as $post_type_name ) {
			$post_type_taxonomies = get_object_taxonomies( $post_type_name, 'objects' );
			foreach ( $post_type_taxonomies as $post_type_taxonomy ) {
				$taxonomy = get_taxonomy( $post_type_taxonomy->name );
				$terms = get_terms( $post_type_taxonomy->name );
				if ( ! empty( $terms ) ) {
					array_push( $taxonomies, array( 'name' => $taxonomy->label, 'value' => $taxonomy->name . '_all', 'selected' => false ) );
					foreach ( $terms as $term ) {
						array_push( $taxonomies, array( 'name' => $taxonomy->label . ': ' . $term->name, 'value' => $taxonomy->name . '_' . $term->slug, 'selected' => false, 'parent' => $taxonomy->name . '_all' ) );
					}
				}
			}
		}

		return $taxonomies;
	}

	/**
	 * API method called to retrieve the posts
	 * for the selected post types and taxonomies.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array  $data Data passed from the AJAX call.
	 * @param   string $search_query The search query for posts.
	 * @return array
	 */
	private function get_posts( $data, $search_query = '' ) {
	    if ( isset( $data['search_query'] ) && $data['search_query'] != '' ) {
			$search_query = $data['search_query'];
		}

		$post_types = array();
		$tax_queries = array( 'relation' => 'OR' );
		$operator = ( isset( $data['exclude'] ) && $data['exclude'] == true ) ? 'NOT IN' : 'IN';

		if ( ! empty( $data['post_types'] ) ) {
			foreach ( $data['post_types'] as $post_type ) {
				array_push( $post_types, $post_type['value'] );
			}
		}

		if ( ! empty( $data['taxonomies'] ) ) {
			foreach ( $data['taxonomies'] as $taxonomy ) {
				$tmp_query = array();
				list( $tax, $term ) = explode( '_', $taxonomy['value'] );
				$tmp_query['relation'] = 'OR';
				$tmp_query['taxonomy'] = $tax;
				if ( isset( $term ) && $term != 'all' && $term != '' ) {
					$tmp_query['field'] = 'slug';
					$tmp_query['terms'] = $term;
				} else {
					$all_terms = get_terms( $tax );
					$terms = array();
					foreach ( $all_terms as $custom_term ) {
						array_push( $terms, $custom_term->slug );
					}
					$tmp_query['field'] = 'slug';
					$tmp_query['terms'] = $terms;
				}
				$tmp_query['include_children'] = true;
				$tmp_query['operator'] = $operator;
				array_push( $tax_queries, $tmp_query );
			}
		}

		$posts_array = get_posts(
			array(
				'posts_per_page' => 5,
				'post_type' => $post_types,
				's' => $search_query,
				'tax_query' => $tax_queries,
			)
		);

		$formatted_posts = array();
		foreach ( $posts_array as $post ) {
		    array_push( $formatted_posts, array( 'name' => $post->post_title, 'value' => $post->ID, 'selected' => false ) );
		}

	    return $formatted_posts;
	}

	/**
	 * API method called to save general settings.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data The settings data to save.
	 * @return array
	 */
	private function save_general_settings( $data ) {
		$general_settings = array(
		    'available_taxonomies' => $data['available_taxonomies'],
			'minimum_post_age' => $data['minimum_post_age'],
			'maximum_post_age' => $data['maximum_post_age'],
			'number_of_posts' => $data['number_of_posts'],
			'more_than_once' => $data['more_than_once'],
			'selected_post_types' => $data['post_types'],
			'selected_taxonomies' => $data['taxonomies'],
			'exclude_taxonomies' => $data['exclude_taxonomies'],
			'selected_posts' => $data['posts'],
			'exclude_posts' => $data['exclude_posts'],
		);

		$settings_model = new Rop_Settings_Model();
		$settings_model->save_settings( $general_settings );
		return $settings_model->get_settings();
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
		return $global_settings->get_available_services();
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
		return $model->get_authenticated_services();
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
		return $model->get_active_accounts();
	}

	/**
	 * API method called to update active accounts.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function update_active_accounts( $data ) {
		$new_active = array();
		foreach ( $data['to_be_activated'] as $account ) {
			$id = $data['service'] . '_' . $data['service_id'] . '_' . $account['id'];
			$new_active[ $id ] = array(
				'service' => $data['service'],
				'user' => $account['name'],
				'img' => $account['img'],
				'account' => $account['account'],
				'created' => date( 'd/m/Y H:i' ),
			);
		}
		$model = new Rop_Services_Model();
		return $model->add_active_accounts( $new_active );
	}

	/**
	 * API method called to remove accounts.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function remove_account( $data ) {
		$model = new Rop_Services_Model();
		return $model->delete_active_accounts( $data['account_id'] );
	}

	/**
	 * API method called to try and authenticate a service.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod) As it is called dynamically.
	 * @Throws Exception Throws an exception if a service can't be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return mixed|null
	 */
	private function authenticate_service( $data ) {
		$new_service = array();
		$factory = new Rop_Services_Factory();
		try {
			${$data['service'] . '_services'} = $factory->build( $data['service'] );
			$authenticated = ${$data['service'] . '_services'}->authenticate();
			if ( $authenticated ) {
				$service = ${$data['service'] . '_services'}->get_service();
				$service_id = $service['service'] . '_' . $service['id'];
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
	 * @param   array $data Data passed from the AJAX call.
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
	 * @Throws Exception Throws an exception if the service can't be built.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return string
	 */
	private function get_service_sign_in_url( $data ) {
		$url = '';
		$factory = new Rop_Services_Factory();
		try {
			${$data['service'] . '_services'} = $factory->build( $data['service'] );
			if ( ${$data['service'] . '_services'} ) {
				/* @noinspection PhpUndefinedMethodInspection */
				$url = ${$data['service'] . '_services'}->sign_in_url( $data );
			}
		} catch ( Exception $exception ) {
			// Service can't be built. Not found or otherwise. Maybe log this.
			$log = new Rop_Logger();
			$log->warn( 'The service "' . $data['service'] . '" can NOT be built or was not found', $exception );
			$url = '';
		}

		return json_encode( array( 'url' => $url ) );
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
	private function get_log() {
		$log = new Rop_Logger();
		return $log->read();
	}

}
