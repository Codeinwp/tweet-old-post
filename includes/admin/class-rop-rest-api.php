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
	 * Rop_Rest_Api constructor.
	 * Registers the API endpoint.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
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
		switch ( $request->get_param( 'req' ) ) {
			case 'publish_queue_event':
				$data = json_decode( $request->get_body(), true );
				$response = $this->publish_queue_event( $data );
				break;
			case 'skip_queue_event':
				$data = json_decode( $request->get_body(), true );
				$response = $this->skip_queue_event( $data );
				break;
			case 'block_queue_event':
				$data = json_decode( $request->get_body(), true );
				$response = $this->block_queue_event( $data );
				break;
			case 'update_queue_event':
				$data = json_decode( $request->get_body(), true );
				$response = $this->update_queue_event( $data );
				break;
			case 'get_queue':
				$response = $this->get_queue();
				break;
			case 'save_schedule':
				$data = json_decode( $request->get_body(), true );
				$response = $this->save_schedule( $data );
				break;
			case 'reset_schedule':
				$data = json_decode( $request->get_body(), true );
				$response = $this->reset_schedule( $data );
				break;
			case 'get_schedule':
				$data = json_decode( $request->get_body(), true );
				$response = $this->get_schedule( $data );
				break;
			case 'shortner_credentials':
				$data = json_decode( $request->get_body(), true );
				$response = $this->get_shortner_credentials( $data );
				break;
			case 'save_post_format':
				$data = json_decode( $request->get_body(), true );
				$response = $this->save_post_format( $data );
				break;
			case 'reset_post_format':
				$data = json_decode( $request->get_body(), true );
				$response = $this->reset_post_format( $data );
				break;
			case 'get_post_format':
				$data = json_decode( $request->get_body(), true );
				$response = $this->get_post_format( $data );
				break;
			case 'select_posts':
				$response = $this->select_posts();
				break;
			case 'get_general_settings':
				$response = $this->get_general_settings();
				break;
			case 'get_taxonomies':
				$data = json_decode( $request->get_body(), true );
				$response = $this->get_taxonomies( $data );
				break;
			case 'get_posts':
				$data = json_decode( $request->get_body(), true );
				$response = $this->get_posts( $data );
				break;
			case 'save_general_settings':
				$data = json_decode( $request->get_body(), true );
				$response = $this->save_general_settings( $data );
				break;
			case 'available_services':
				$response = $this->get_available_services();
				break;
			case 'service_sign_in_url':
				$data = json_decode( $request->get_body(), true );
				$response = $this->get_service_sign_in_url( $data );
				break;
			case 'authenticated_services':
				$response = $this->get_authenticated_services();
				break;
			case 'active_accounts':
				$response = $this->get_active_accounts();
				break;
			case 'update_accounts':
				$data = json_decode( $request->get_body(), true );
				$response = $this->update_active_accounts( $data );
				break;
			case 'remove_account':
				$data = json_decode( $request->get_body(), true );
				$response = $this->remove_account( $data );
				break;
			case 'authenticate_service':
				$data = json_decode( $request->get_body(), true );
				$response = $this->authenticate_service( $data );
				break;
			case 'remove_service':
				$data = json_decode( $request->get_body(), true );
				$response = $this->remove_service( $data );
				break;
			default:
				$response = array( 'status' => '200', 'data' => array( 'list', 'of', 'stuff', 'from', 'api' ) );
		}// End switch().
		// array_push( $response, array( 'current_user' => current_user_can( 'manage_options' ) ) );
		return $response;
	}

	/**
	 * API method called to publish a queue event and return active queue.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function publish_queue_event( $data ) {
		$queue = new Rop_Queue_Model();
		return $queue->get_ordered_queue();
	}

	/**
	 * API method called to skip a queue event and return active queue.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function skip_queue_event( $data ) {
		$queue = new Rop_Queue_Model();
		return $queue->get_ordered_queue();
	}

	/**
	 * API method called to block a queue event and return active queue.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function block_queue_event( $data ) {
		$queue = new Rop_Queue_Model();
		return $queue->get_ordered_queue();
	}

	/**
	 * API method called to update a queue event and return active queue.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function update_queue_event( $data ) {
		$queue = new Rop_Queue_Model();
		$queue->update_queue_object( $data['account_id'], $data['post_id'], $data['custom_data'] );
		return $queue->get_ordered_queue();
	}

	/**
	 * API method called to get the active queue.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_queue() {
	    $queue = new Rop_Queue_Model();
	    return $queue->get_ordered_queue();
	}

	/**
	 * API method called to save a post format.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function save_schedule( $data ) {
		$schedules = new Rop_Scheduler_Model();
		$schedules->add_update_schedule( $data['account_id'], $data['schedule'] );
		return $schedules->get_schedule( $data['account_id'] );
	}

	/**
	 * API method called to reset a post format to defaults.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function reset_schedule( $data ) {
		$schedules = new Rop_Scheduler_Model();
		$schedules->remove_schedule( $data['account_id'] );
		return $schedules->get_schedule( $data['account_id'] );
	}

	/**
	 * API method called to retrieve a schedule.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function get_schedule( $data ) {
		$schedules = new Rop_Scheduler_Model();
		return $schedules->get_schedule( $data['account_id'] );
	}

	/**
	 * API method called to get shortner service credentials.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return mixed
	 */
	private function get_shortner_credentials( $data ) {
	    $sh_factory = new Rop_Shortner_Factory();
	    $shortner = $sh_factory->build( $data['short_url_service'] );
	    return $shortner->get_credentials();
	}

	/**
	 * API method called to save a post format.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return array
	 */
	private function save_post_format( $data ) {
		$post_format = new Rop_Post_Format_Model( $data['service'] );
		$sh_factory = new Rop_Shortner_Factory();
		$shortner = $sh_factory->build( $data['post_format']['short_url_service'] );
		$shortner->set_credentials( $data['post_format']['shortner_credentials'] );
		$post_format->add_update_post_format( $data['account_id'], $data['post_format'] );
		return $post_format->get_post_format( $data['account_id'] );
	}

	/**
	 * API method called to reset a post format to defaults.
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
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_general_settings() {
		$general_settings_model = new Rop_Settings_Model();
		return $general_settings_model->get_settings();
	}

	/**
	 * API method called to retrieve the taxonomies
	 * for the selected post types.
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
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
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

		$general_settings_model = new Rop_Settings_Model();
		$general_settings_model->save_settings( $general_settings );
		return $general_settings_model->get_settings();
	}

	/**
	 * API method called to retrieve available services.
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
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return mixed|null
	 */
	private function authenticate_service( $data ) {
		$new_service = array();
		$factory = new Rop_Services_Factory();
		${$data['service'] . '_services'} = $factory->build( $data['service'] );
		$authenticated = ${$data['service'] . '_services'}->authenticate();
		if ( $authenticated ) {
			$service = ${$data['service'] . '_services'}->get_service();
			$service_id = $service['service'] . '_' . $service['id'];
			$new_service[ $service_id ] = $service;
		}

		$model = new Rop_Services_Model();
		return $model->add_authenticated_service( $new_service );
	}

	/**
	 * API method called to try and remove a service.
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
	 * @since   8.0.0
	 * @access  private
	 * @param   array $data Data passed from the AJAX call.
	 * @return string
	 */
	private function get_service_sign_in_url( $data ) {
		$url = '';
		$factory = new Rop_Services_Factory();
		${$data['service'] . '_services'} = $factory->build( $data['service'] );
		if ( ${$data['service'] . '_services'} ) {
			$url = ${$data['service'] . '_services'}->sign_in_url( $data );
		}
		return json_encode( array( 'url' => $url ) );
	}

}
