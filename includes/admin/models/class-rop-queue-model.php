<?php
class Rop_Queue_Model extends Rop_Model_Abstract {

	private $queue;

	private $selector;

	private $scheduler;

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

	public function create_shuffler( $start, $end, $quantity ) {
		$numbers = range( $start, $end );
		shuffle( $numbers );
		$result = $numbers;
		while ( sizeof( $result ) < $quantity ) {
			shuffle( $numbers );
			$result = array_merge_recursive( $result, $numbers );
		}
		return array_slice( $result, 0, $quantity );
	}

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

	public function pop_from_queue( $account_id, $update_last_share = true ) {
		$queue_to_pop = $this->queue[ $account_id ];
		$popped = array_shift( $queue_to_pop );
		$this->queue[ $account_id ] = $queue_to_pop;
		if ( $update_last_share ) {
			$this->scheduler->add_update_schedule( $account_id, false, $popped['time'] );
		}
		return $popped['time'];
	}

	public function ban_post( $account_id, $post_id ) {
		$this->selector->update_buffer( $account_id, $post_id );
	}

	public function get_queue() {
		return $this->queue;
	}

}
