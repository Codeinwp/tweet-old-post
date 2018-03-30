<?php
/**
 * The model for maneging the posts queued for sharing.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin/models
 */

/**
 * Class Rop_Queue_Model
 */
class Rop_Queue_Model extends Rop_Model_Abstract {


	/**
	 * Holds the queue namespace.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $queue The queue option key.
	 */
	private $queue_namespace = 'queue';

	/**
	 * Holds the active queue.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $queue The active queue array.
	 */
	private $queue;

	/**
	 * An instance of the Posts Selector.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     Rop_Posts_Selector_Model An instance of the Posts Selector.
	 */
	private $selector;

	/**
	 * An instance of the Scheduler Model
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     Rop_Scheduler_Model An instance of the Scheduler Model.
	 */
	private $scheduler;

	/**
	 * Rop_Queue_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct( 'rop_queue' );

		$this->selector  = new Rop_Posts_Selector_Model();
		$this->scheduler = new Rop_Scheduler_Model();
		$this->queue     = $this->get( $this->queue_namespace );
		if ( ! is_array( $this->queue ) ) {
			$this->queue = array();
		}
	}

	/**
	 * Update a queue object with custom data, passed by the user.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $account_id The account ID.
	 * @param   int    $post_id The post ID referenced.
	 * @param   array  $custom_data The custom data.
	 *
	 * @return bool
	 */
	public function update_queue_object( $account_id, $post_id, $custom_data ) {
		$key_to_edit = $this->queue_object_exists( $account_id, $post_id );
		if ( $key_to_edit === false ) {
			return false;
		}

		$edit = $this->queue[ $account_id ][ $key_to_edit ];

		if ( isset( $custom_data['custom_content'] ) ) {
			$edit['post']['custom_content'] = $custom_data['custom_content'];
		}
		if ( isset( $custom_data['custom_img'] ) ) {
			$edit['post']['custom_img'] = $custom_data['custom_img'];
		}
		if ( isset( $custom_data['post_img'] ) ) {
			$edit['post']['post_img'] = $custom_data['post_img'];
		}

		$this->queue[ $account_id ][ $key_to_edit ] = $edit;

		$this->set( $this->queue_namespace, $this->queue );

		return true;
	}

	/**
	 * Check if the object exists in queue and returns the key if found.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $account_id The account ID.
	 * @param   int    $post_id The post ID.
	 *
	 * @return bool|int|null|string
	 */
	private function queue_object_exists( $account_id, $post_id ) {
		if ( empty( $this->queue ) ) {
			return false;
		}
		if ( ! isset( $this->queue[ $account_id ] ) || empty( $this->queue[ $account_id ] ) ) {
			return false;
		}

		$key_to_edit = $this->search_array_by_key( 'post_id', $post_id, $this->queue[ $account_id ] );
		if ( $key_to_edit === null ) {
			return false;
		}

		return $key_to_edit;
	}

	/**
	 * Utility  method to search an associative array
	 * and return the key of the first found element.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $key_name The key to use for lookup.
	 * @param   mixed  $value The value to match against.
	 * @param   array  $array The array where to search.
	 *
	 * @return int|null|string
	 */
	private function search_array_by_key( $key_name, $value, $array ) {
		foreach ( $array as $key => $val ) {
			if ( $val['post'][ $key_name ] == $value ) {
				return $key;
			}
		}

		return null;
	}

	/**
	 * Method to pop items from active queue.
	 * And update account scheduler, as if event was resolved.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $account_id The account ID.
	 * @param   bool   $update_last_share Flag to specify if scheduler for the account should be updated.
	 *
	 * @return mixed
	 */
	public function pop_from_queue( $account_id, $update_last_share = true ) {
		$queue_to_pop               = $this->queue[ $account_id ];
		$popped                     = array_shift( $queue_to_pop );
		$this->queue[ $account_id ] = $queue_to_pop;
		if ( $update_last_share ) {
			$this->scheduler->add_update_schedule( $account_id, false, $popped['time'] );
		}
		$this->selector->update_buffer( $account_id, $popped['post']['post_id'] );

		return $popped;
	}

	/**
	 * Utility method to remove from queue.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $index The base64 uid.
	 * @param   string $account_id The account ID.
	 *
	 * @return mixed
	 */
	public function remove_from_queue( $index, $account_id, $update_last_share = true ) {
		$to_remove_from_queue = $this->queue[ $account_id ][ $index ];
		$this->selector->update_buffer( $account_id, $to_remove_from_queue['post']['post_id'] );
		if ( $update_last_share ) {
			$this->scheduler->add_update_schedule( $account_id, false, $to_remove_from_queue['time'] );
		}
		unset( $this->queue[ $account_id ][ $index ] );
		$this->set( $this->queue_namespace, $this->queue );

		return $to_remove_from_queue;
	}

	/**
	 * Removes an account from queue
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $account_id The account ID.
	 */
	public function remove_account_from_queue( $account_id ) {
		unset( $this->queue[ $account_id ] );
		$this->set( $this->queue_namespace, $this->queue );
	}

	/**
	 * Mark a post_id as blocked for the account.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $index The base64 uid.
	 * @param   string $account_id The account ID.
	 */
	public function ban_post( $index, $account_id ) {
		if ( ! empty( $this->queue ) ) {
			$queue = $this->queue;
			if ( isset( $queue[ $account_id ] [ $index ] ) ) {
				$skip_id = $queue[ $account_id ][ $index ]['post']['post_id'];
				$this->selector->mark_as_blocked( $account_id, $skip_id );
				$this->replace_post_in_queue( $account_id, $skip_id );

				$this->set( $this->queue_namespace, $this->queue );

				return true;
			}

			return false;
		}

		return false;

	}

	/**
	 * Utility method to replace given post ID from queue.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $account_id The account ID.
	 * @param   int    $post_id The post ID.
	 */
	private function replace_post_in_queue( $account_id, $post_id ) {
		$post_pool = $this->selector->select( $account_id );
		$shuffler  = $this->create_shuffler( 0, sizeof( $post_pool ) - 1, sizeof( $this->queue[ $account_id ] ) );
		$iterator  = 0;
		foreach ( $this->queue[ $account_id ] as $index => $event ) {
			if ( $event['post']['post_id'] == $post_id ) {
				$pos                                          = $shuffler[ $iterator ++ ];
				$this->queue[ $account_id ][ $index ]['post'] = $this->prepare_post_object( $post_pool[ $pos ], $account_id );
			}
		}
	}


	/**
	 * Rebuilds the post object using the updated post format
	 * and preserving the old user settings. Or creates a new one.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   integer $post_id A WordPress Post Object.
	 * @param   string  $account_id The account ID.
	 *
	 * @return array
	 */
	private function prepare_post_object( $post_id, $account_id ) {
		$post_format_helper = new Rop_Post_Format_Helper();
		$post_format_helper->set_post_format( $account_id );
		$filtered_post = $post_format_helper->get_formated_object( $post_id );

		return $filtered_post;
	}

	/**
	 * Method to retrieve the queue formatted for display.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_ordered_queue() {
		$cron_helper = new Rop_Cron_Helper();
		/**
		 * Bail if the sharing is not started.
		 */
		if ( $cron_helper->get_status() === false ) {
			return array();
		}
		$queue = $this->build_queue();
		foreach ( $queue as $account_id => $data ) {
			foreach ( $data as $index => $event ) {
				$ordered[ $index ] = array(
					'time'      => $event['time'],
					'post_data' => $event,
					'post_id'   => $event['post_id'],
				);
			}
		}
		uasort(
			$ordered,
			function ( $a, $b ) {
				return ( ( $a['time'] ) - ( $b['time'] ) );
			}
		);

		return $ordered;
	}

	/**
	 * Method to build and update the queue.
	 * It builds, rebuilds and refills the queue as needed.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function build_queue() {
		$settings        = new Rop_Settings_Model();
		$no_of_posts     = $settings->get_number_of_posts();
		$upcoming_events = $this->scheduler->get_all_upcoming_events();
		$current_queue   = $this->get_queue();
		if ( empty( $upcoming_events ) ) {
			return array();
		}
		foreach ( $upcoming_events as $account_id => $events ) {
			$account_queue  = isset ( $current_queue[ $account_id ] ) ? $current_queue[ $account_id ] : array();
			$queue_max_size = count( $events ) * $no_of_posts;
			/**
			 * Bail if we have queue already filled for this account.
			 */
			if ( count( $current_queue[ $account_id ] ) === $queue_max_size ) {
				continue;
			}
			$post_pool = $this->selector->select( $account_id );
			foreach ( $events as $index => $time ) {
				for ( $i = 0; $i < $no_of_posts; $i ++ ) {
					$rand_key = rand( 0, count( $post_pool ) - 1 );
					$post_id  = $post_pool[ $rand_key ];
					$uid      = $this->create_uid( $account_id, $time, $post_id );
					/**
					 * If we have already this post scheduled for sharing at the same, bail.
					 */
					if ( isset( $account_queue[ $uid ] ) ) {
						continue;
					}
					$account_queue[ $uid ] = array(
						'time'       => $time,
						'account_id' => $account_id,
						'date'       => Rop_Scheduler_Model::get_date( $time ),
						'post_id'    => $post_id,
						'content'    => $this->prepare_post_object( $post_id, $account_id ),
					);
					unset( $post_pool[ $rand_key ] );
					$post_pool = array_filter( $post_pool );
				}
			}
			$queue[ $account_id ] = $account_queue;
		}
		$this->set( $this->queue_namespace, $queue );

		return $queue;
	}

	/**
	 * Method to retrieve the queue
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_queue() {
		return $this->queue;
	}

	/**
	 * Method to generate an uid.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string  $account_id The account ID.
	 * @param   integer $time A timestamp of the event.
	 * @param   integer $post_id Post ID.
	 *
	 * @return string
	 */
	private function create_uid( $account_id, $time, $post_id ) {
		return md5( $time . $account_id . $post_id );
	}

	/**
	 * Method to clear the queue
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function clear_queue( $account_id = false ) {
		if ( empty( $account_id ) ) {
			$this->queue = array();
		} else {
			$this->queue[ $account_id ] = array();
		}
		$this->set( 'queue', $this->queue );
	}

	/**
	 * Method to skip post for the given account.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $index The base64 uid.
	 * @param   string $account_id The account ID.
	 *
	 * @return bool
	 */
	public function skip_post( $index, $account_id ) {
		if ( ! empty( $this->queue ) ) {
			$queue = $this->queue;
			if ( isset( $queue[ $account_id ][ $index ] ) ) {
				$skip_id = $queue[ $account_id ][ $index ]['post']['post_id'];
				$this->selector->update_buffer( $account_id, $skip_id );
				$post = $this->selector->select( $account_id );
				if ( isset( $post[0] ) && ! empty( $post[0] ) ) {
					$this->queue[ $account_id ][ $index ]['post'] = $this->prepare_post_object( $post[0], $account_id );
					$this->set( $this->queue_namespace, $this->queue );
					$this->build_queue();

					return true;
				}

				return false;
			}

			return false;
		}

		return false;
	}

}
