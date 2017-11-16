<?php
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
				$shuffler = $this->create_shuffler( 0, sizeof( $post_pool ) - 1, sizeof( $schedules ) );
				$i = 0;
				foreach ( $schedules as $time ) {
					$pos = $shuffler[ $i++ ];
					array_push( $account_queue, array( 'time' => $time, 'post' => $post_pool[ $pos ]->ID ) );
				}
				$queue[ $account_id ] = $account_queue;
			}
		}

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
						array_push( $account_queue, array( 'time' => $time, 'post' => $post_pool[ $pos ]->ID ) );
					}
					$this->queue[ $account_id ] = $account_queue;
				}
			}
		}

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
		return $popped['time'];
	}

	/**
	 * Mark a post_id as blocked for the account.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID.
	 * @param   int    $post_id The post id.
	 */
	public function ban_post( $account_id, $post_id ) {
		$this->selector->mark_as_blocked( $account_id, $post_id );
	}

	public function get_queue() {
		return $this->queue;
	}

}
