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

		$this->queue = ( $this->get( 'queue' ) != null ) ? $this->get( 'queue' ) : array();

		$this->selector = new Rop_Posts_Selector_Model();
		$this->scheduler = new Rop_Scheduler_Model();

		if ( empty( $this->queue ) ) {
			$this->queue = $this->build_queue();
		} else {
			$this->refill_queue();
		}
	}

	/**
	 * Utility method to generate a shuffle list within the specified range.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   int $start The starting value.
	 * @param   int $end The end value.
	 * @param   int $quantity The amount of results to be returned.
	 * @return array
	 */
	private function create_shuffler( $start, $end, $quantity ) {
		$numbers = range( $start, $end );
		shuffle( $numbers );
		$result = $numbers;
		while ( sizeof( $result ) < $quantity ) {
			shuffle( $numbers );
			$result = array_merge_recursive( $result, $numbers );
		}
		return array_slice( $result, 0, $quantity );
	}

	/**
	 * Method to generate an uid.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $account_id The account ID.
	 * @param   string $time A date time string.
	 * @return string
	 */
	private function create_uid( $account_id, $time ) {
		return base64_encode( strtotime( $time ) * strlen( $account_id ) );
	}

	/**
	 * This method will be refactored or moved inside a post format helper.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   WP_Post $post The post object.
	 * @param   string  $account_id The account ID.
	 * @return array
	 */
	private function prepare_post_object( WP_Post $post, $account_id ) {
		$post_format_helper = new Rop_Post_Format_Helper();
		$filtered_post = $post_format_helper->get_formated_object( $account_id, $post );
		return $filtered_post;
	}

	/**
	 * Utility  method to search an associative array
	 * and return the key of the first found element.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $key_name The key to use for lookup.
	 * @param   mixed  $value The value to match against.
	 * @param   array  $array The array where to search.
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
	 * Check if the object exists in queue and returns the key if found.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $account_id The account ID.
	 * @param   int    $post_id The post ID.
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
	 * Update a queue object with custom data, passed by the user.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID.
	 * @param   int    $post_id The post ID referenced.
	 * @param   array  $custom_data The custom data.
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

		$this->set( 'queue', $this->queue );
		return true;
	}

	/**
	 * Rebuilds the post object using the updated post format
	 * and preserving the old user settings.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   WP_Post $post A WordPress Post Object.
	 * @param   string  $account_id The account ID.
	 * @param   array   $old_post_object The old filtered post object data.
	 * @return array
	 */
	private function rebuild_post_object( WP_Post $post, $account_id, $old_post_object ) {
		$post_format_helper = new Rop_Post_Format_Helper();
		$filtered_post = $post_format_helper->get_formated_object( $account_id, $post, $old_post_object );
		return $filtered_post;
	}

	/**
	 * Refreshes the current queue items.
	 *
	 * @since   8.0.0
	 * @access  private
	 */
	private function refresh_queue() {
		if ( ! empty( $this->queue ) ) {
			$updated_queue = array();
			foreach ( $this->queue as $account_id => $account_queue ) {
				$updated_accounts_queue = array();
				foreach ( $account_queue as $queue_event ) {
					$post = get_post( $queue_event['post']['post_id'] );
					$updated_post = $this->rebuild_post_object( $post, $account_id, $queue_event['post'] );
					$uid = $this->create_uid( $account_id, $queue_event['time'] );
					$updated_accounts_queue[ $uid ] = array( 'time' => $queue_event['time'], 'post' => $updated_post );
					// array_push( $updated_accounts_queue, array( 'time' => $queue_event['time'], 'post' => $updated_post ) );
				}
				$updated_queue[ $account_id ] = $updated_accounts_queue;
			}
			$this->queue = $updated_queue;
			$this->set( 'queue', $this->queue );
		}
	}

	/**
	 * Utility method to build the active queue.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function build_queue() {
		$queue = array();
		$upcoming_schedules = $this->scheduler->list_upcomming_schedules();
		if ( $upcoming_schedules && ! empty( $upcoming_schedules ) ) {
			foreach ( $upcoming_schedules as $account_id => $schedules ) {
				$account_queue = array();
				$post_pool = $this->selector->select( $account_id );
				// var_dump( $post_pool );
				$shuffler = $this->create_shuffler( 0, sizeof( $post_pool ) - 1, sizeof( $schedules ) );
				$i = 0;
				// print_r( $schedules );
				foreach ( $schedules as $time ) {
					$pos = $shuffler[ $i++ ];
					$uid = $this->create_uid( $account_id, $time );
					$account_queue[ $uid ] = array( 'time' => $time, 'post' => $this->prepare_post_object( $post_pool[ $pos ], $account_id ) );
					// array_push( $account_queue, array( 'time' => $time, 'post' => $this->prepare_post_object( $post_pool[ $pos ], $account_id ) ) );
				}
				$queue[ $account_id ] = $account_queue;
			}
		}
		$this->set( 'queue', $queue );
		return $queue;
	}

	/**
	 * Utility method to top off queue for empty elements.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function refill_queue() {
		$upcoming_schedules = $this->scheduler->list_upcomming_schedules();
		if ( $this->queue && ! empty( $this->queue ) ) {
			foreach ( $this->queue as $account_id => $account_queue ) {
				if ( empty( $account_queue ) ) {
					$schedules = $upcoming_schedules[ $account_id ];
					$post_pool = $this->selector->select( $account_id );
					$shuffler = $this->create_shuffler( 0, sizeof( $post_pool ) - 1, sizeof( $schedules ) );
					$i = 0;
					foreach ( $schedules as $time ) {
						$pos = $shuffler[ $i++ ];
						$uid = $this->create_uid( $account_id, $time );
						$account_queue[ $uid ] = array( 'time' => $time, 'post' => $this->prepare_post_object( $post_pool[ $pos ], $account_id ) );
						// array_push( $account_queue, array( 'time' => $time, 'post' => $this->prepare_post_object( $post_pool[ $pos ], $account_id ) ) );
					}
					$this->queue[ $account_id ] = $account_queue;
				}
			}
		}
		$this->set( 'queue', $this->queue );
	}

	/**
	 * Method to pop items from active queue.
	 * And update account scheduler, as if event was resolved.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID.
	 * @param   bool   $update_last_share Flag to specify if scheduler for the account should be updated.
	 * @return mixed
	 */
	public function pop_from_queue( $account_id, $update_last_share = true ) {
		$queue_to_pop = $this->queue[ $account_id ];
		$popped = array_shift( $queue_to_pop );
		$this->queue[ $account_id ] = $queue_to_pop;
		if ( $update_last_share ) {
			$this->scheduler->add_update_schedule( $account_id, false, $popped['time'] );
		}
		$this->selector->update_buffer( $account_id, $popped['post']['post_id'] );
		// $this->set( 'queue', $this->queue );
		return $popped;
	}

	/**
	 * Utility method to remove from queue.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $index The base64 uid.
	 * @param   string $account_id The account ID.
	 * @return mixed
	 */
	public function remove_from_queue( $index, $account_id ) {
	    $to_remove_from_queue = $this->queue[ $account_id ][ $index ];
		$this->selector->update_buffer( $account_id, $to_remove_from_queue['post']['post_id'] );
		unset( $this->queue[ $account_id ][ $index ] );
		$this->set( 'queue', $this->queue );
		return $to_remove_from_queue;
	}

	/**
	 * Mark a post_id as blocked for the account.
	 *
	 * @since   8.0.0
	 * @access  public
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
			}
		}
		$this->set( 'queue', $this->queue );
	}

	/**
	 * Utility method to replace given post ID from queue.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $account_id The account ID.
	 * @param   int    $post_id The post ID.
	 */
	private function replace_post_in_queue( $account_id, $post_id ) {
		$post_pool = $this->selector->select( $account_id );
		$shuffler = $this->create_shuffler( 0, sizeof( $post_pool ) - 1, sizeof( $this->queue[ $account_id ] ) );
		$i = 0;
		foreach ( $this->queue[ $account_id ] as $index => $event ) {
			if ( $event['post']['post_id'] == $post_id ) {
				$pos = $shuffler[ $i++ ];
				$this->queue[ $account_id ][ $index ]['post'] = $this->prepare_post_object( $post_pool[ $pos ], $account_id );
			}
		}
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
	 * Method to retrieve the queue formatted for display.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_ordered_queue() {
		$this->refresh_queue();
		$queue = $this->queue;
		$ordered = array();
		foreach ( $queue as $account_id => $data ) {
			foreach ( $data as $index => $event ) {
				$formatted_data = $event;
				$formatted_data['time'] = date( 'd-m-Y H:i', strtotime( $formatted_data['time'] ) );
				$ordered[ $index ] = array( 'time' => $event['time'], 'account_id' => $account_id, 'post' => $formatted_data['post'] );
				// array_push( $ordered, array( 'time' => $event['time'], 'account_id' => $account_id, 'post' => $formatted_data['post'] ) );
			}
		}
		uasort( $ordered, function ( $a, $b ) {
			return strtotime( $a['time'] ) - strtotime( $b['time'] );
		} );
		// $new_indexes = array_map( function( $element ){ return base64_encode( strtotime( $element['time'] ) *  strlen( $element['account_id'] ) ) ; }, $ordered );
		// $ordered = array_combine ( $new_indexes , $ordered );
		return $ordered;
	}

	/**
	 * Method to skip post for the given account.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $index The base64 uid.
	 * @param   string $account_id The account ID.
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
					;
					$this->set( 'queue', $this->queue );
					$this->refresh_queue();
					return true;
				}
			}
		}

		return false;
	}

}
