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
	public function remove_from_queue( $index, $post_id, $account_id ) {
		$to_remove_from_queue = $this->queue[ $account_id ][ $index ];
		$this->selector->update_buffer( $account_id, $post_id );
		unset( $this->queue[ $account_id ][ $index ] );
		$this->update_queue( $this->queue );

		return $to_remove_from_queue;
	}

	/**
	 * Update queue object.
	 *
	 *
	 * @param array $queue New queue to update.
	 */
	public function update_queue( $queue ) {

		$this->set( $this->queue_namespace, $queue );
		$this->queue = $queue;
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
	public function ban_post( $post_id, $account_id ) {
		$queue = $this->get_queue();
		if ( empty( $queue ) ) {
			return false;
		}

		if ( ! isset( $queue[ $account_id ] ) ) {
			return false;
		}
		$this->selector->mark_as_blocked( $account_id, $post_id );
		$queue[ $account_id ] = array_diff( $queue[ $account_id ], array( $post_id ) );
		$queue[ $account_id ] = array_values( $queue[ $account_id ] );
		$this->update_queue( $queue );

		return true;

	}

	/**
	 * Method to retrieve the queue based on the number of timeline events.
	 * It creates the queue order, refill it in case that we don't have enought elements.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_queue() {
		$settings        = new Rop_Settings_Model();
		$no_of_posts     = $settings->get_number_of_posts();
		$upcoming_events = $this->scheduler->get_all_upcoming_events();
		$current_queue   = $this->queue;
		if ( empty( $upcoming_events ) ) {
			return array();
		}
		foreach ( $upcoming_events as $account_id => $events ) {
			$account_queue = isset( $current_queue[ $account_id ] ) ? $current_queue[ $account_id ] : array();

			$queue_max_size     = count( $events ) * $no_of_posts;
			$current_queue_size = count( $account_queue );
			/**
			 * Bail if we have queue already filled for this account.
			 */
			if ( $current_queue_size === $queue_max_size ) {
				continue;
			}
			/**
			 * If we have more posts in queue than necessary, slice them.
			 * This might happen when user changes the no of posts shared.
			 */
			if ( $current_queue_size > $queue_max_size ) {
				$current_queue[ $account_id ] = array_slice( $account_queue, 0, $queue_max_size );
				continue;
			}
			$post_pool = $this->selector->select( $account_id );
			if ( empty( $post_pool ) ) {
				// TODO error no posts to share for this account.
				continue;
			}
			$items_needed = $queue_max_size - $current_queue_size;
			$i            = 0;

			while ( $i <= $items_needed ) {
				if ( empty( $post_pool ) ) {
					break;
				}
				$rand_key        = rand( 0, count( $post_pool ) - 1 );
				$post_id         = $post_pool[ $rand_key ];
				$account_queue[] = $post_id;
				$i ++;
				unset( $post_pool[ $rand_key ] );
				$post_pool = array_values( $post_pool );
			}
			$current_queue[ $account_id ] = array_values( $account_queue );
		}
		$this->set( $this->queue_namespace, $current_queue );
		$this->queue = $current_queue;

		return $current_queue;
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
		$queue   = $this->build_queue();
		$ordered = array();
		foreach ( $queue as $account_id => $data ) {
			foreach ( $data as $index => $post ) {
				if ( empty( $post ) ) {
					continue;
				}
				$ordered[] = array(
					'time'      => $post['time'],
					'post_data' => array(
						'time'       => $post['time'],
						'account_id' => $account_id,
						'date'       => Rop_Scheduler_Model::get_date( $post['time'] ),
						'post_id'    => $post['id'],
						'content'    => $this->prepare_post_object( $post['id'], $account_id ),
					),
					'post_id'   => $post['id'],
				);
			}
		}
		usort(
			$ordered,
			function ( $a, $b ) {
				return ( ( $a['time'] ) - ( $b['time'] ) );
			}
		);

		return $ordered;
	}

	/**
	 * Method to build the queue according to the timeline.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function build_queue() {
		$queue            = $this->get_queue();
		$settings         = new Rop_Settings_Model();
		$no_of_posts      = $settings->get_number_of_posts();
		$upcoming_events  = $this->scheduler->get_all_upcoming_events();
		$normalized_queue = array();
		foreach ( $upcoming_events as $account_id => $events ) {
			$account_queue                   = $queue[ $account_id ];
			$normalized_queue[ $account_id ] = array();
			foreach ( $events as $index => $event ) {
				for ( $i = 0; $i < $no_of_posts; $i ++ ) {
					$post_index = $i + $index;
					if ( ! isset( $account_queue[ $post_index ] ) ) {
						continue;
					}
					$normalized_queue[ $account_id ][] = array(
						'time' => $event,
						'id'   => $account_queue[ $post_index ],
					);
				}

			}
		}

		return $normalized_queue;
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
	public function prepare_post_object( $post_id, $account_id ) {
		$post_format_helper = new Rop_Post_Format_Helper();
		$post_format_helper->set_post_format( $account_id );
		$filtered_post = $post_format_helper->get_formated_object( $post_id );

		return $filtered_post;
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
	public function skip_post( $post_id, $account_id ) {
		$queue = $this->get_queue();
		if ( empty( $queue ) ) {
			return false;
		}

		if ( ! isset( $queue[ $account_id ] ) ) {
			return false;
		}
		$this->selector->update_buffer( $account_id, $post_id );
		$queue[ $account_id ] = array_diff( $queue[ $account_id ], array( $post_id ) );
		$queue[ $account_id ] = array_values( $queue[ $account_id ] );
		$this->update_queue( $queue );

		return true;
	}


}
