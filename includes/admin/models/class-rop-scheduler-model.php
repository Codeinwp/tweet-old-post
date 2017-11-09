<?php
/**
 * The model for manipulating schedules of the plugin.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin/models
 */

/**
 * Class Rop_Scheduler_Model
 */
class Rop_Scheduler_Model extends Rop_Model_Abstract {

	private $schedules;

	private $skips;

	private $schedule_defaults;

	public function __construct() {
		parent::__construct( 'rop_schedules_data' );

		$this->schedules = $this->get_schedules();
		$this->skips = $this->get_skips();

		$this->schedule_defaults = array(
			'type' => 'recurring', // another possible value is "fixed"
			// 'interval_r' => '6.5',
			// 'interval_f' => array(
			//      'week_days' => array( 0, 1, 2, 3, 4, 5, 6 ),
			//      'time' => current_time( 'H:i', 0 ),
			// ),
			'timestamp' => current_time( 'timestamp', 0 ),
			'first_share' => null,
			'last_share' => null,
		);
	}

	private function get_schedules() {
		return ( $this->get( 'schedules' ) != null ) ?  $this->get( 'schedules' ) : array();
	}

	private function get_skips() {
		return ( $this->get( 'skips' ) != null ) ?  $this->get( 'skips' ) : array();
	}

	private function check_time_is_float( $time ) {
		if ( ! is_scalar( $time ) ) {
			return false;
		}

		$type = gettype( $time );

		if ( $type === 'float' ) {
			return true;
		} else {
			return preg_match( '/^\\d+\\.\\d+$/', $time ) === 1;
		}
	}

	private function convert_float_to_time( $value, $as_array = true ) {
		$hours = floor( $value );
		$minutes = ( ( $value * 60 ) % 60 );
		if ( ! $as_array ) {
			return $hours . ':' . $minutes;
		}
		return array(
			'hours' => $hours,
			'minutes' => $minutes,
		);
	}

	private function convert_time_to_float( $value ) {
		list( $hour, $minutes ) = explode( ':', $value );
		return $hour + floor( ( $minutes / 60 ) * 100 ) / 100;
	}

	private function add_to_time( $time, $hours, $minutes, $is_timestamp = false, $format = 'Y-m-d H:i' ) {
		if ( $is_timestamp ) {
			$timestamp = strtotime( '+' . $hours . ' hour +' . $minutes . ' minutes', $time );
		} else {
			$timestamp = strtotime( '+' . $hours . ' hour +' . $minutes . ' minutes', strtotime( $time ) );
		}

		return date( $format, $timestamp );
	}

	public function create_schedule( $type = 'recurring', $interval ) {
		$schedule = array();
		if ( in_array( $type, array( 'recurring', 'fixed' ) ) ) {
			$schedule['type'] = $type;
		}

		if ( is_string( $interval ) ) {
			$schedule['interval_r'] = $interval;
		}

		if( is_array( $interval ) ) {
		    $schedule['interval_f'] = $interval;
        }
        $schedule['first_share'] = strtotime( '+15 seconds', current_time( 'timestamp', 0 ) );

		return wp_parse_args( $schedule, $this->schedule_defaults );
	}

	public function get_schedule( $account_id ) {
		$this->schedules = $this->get_schedules();
		if ( isset( $this->schedules[ $account_id ] ) ) {
			return $this->schedules[ $account_id ];
		}
		return $this->schedule_defaults;
	}

	public function add_update_schedule( $account_id, $type, $date, $time, $last_share = null ) {
		$this->schedules = $this->get_schedules();
		$schedule = $this->create_schedule( $type, $date, $time );
		$schedule['last_share'] = $last_share;
		$this->schedules[ $account_id ] = $schedule;
		return $this->set( 'schedules', $this->schedules );
	}

	public function remove_schedule( $account_id ) {
		$this->schedules = $this->get_schedules();
		if ( isset( $this->schedules[ $account_id ] ) ) {
			unset( $this->schedules[ $account_id ] );
		}
		return $this->set( 'schedules', $this->schedules );
	}

	public function add_to_skips( $account_id ) {
		$this->skips = $this->get_skips();
		if ( ! in_array( $account_id, $this->skips ) ) {
			array_push( $this->skips, $account_id );
		}
		return $this->set( 'skips', $this->skips );
	}

	public function remove_skips( $account_id ) {
		$this->skips = $this->get_skips();
		if ( in_array( $account_id, $this->skips ) ) {
			$to_remove = array( $account_id );
			$this->skips = array_diff( $this->skips, $to_remove );
		}
		return $this->set( 'skips', $this->skips );
	}

	public function list_upcomming_schedules( $future_events = 5 ) {
		$this->schedules = $this->get_schedules();
		$this->skips = $this->get_skips();
		$list = array();
		foreach ( $this->schedules as $schedule ) {
			if ( $schedule['type'] == 'recurring' ) {
				if ( $schedule['last_share'] == null ) {
					$time = $this->convert_float_to_time( $schedule['interval_r'] );
					$event['time'] = $this->add_to_time( $schedule['timestamp'], $time['hours'], $time['minutes'], true );
				}
				for ( $i = 1; $i < $future_events; $i++ ) {

				}
			}
		}
	}

}
