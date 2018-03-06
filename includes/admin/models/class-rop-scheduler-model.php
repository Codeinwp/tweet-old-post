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

	/**
	 * Stores the current schedules per account.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $schedules The current schedules.
	 */
	private $schedules;

	/**
	 * The defaults to be returned for a non existing schedule.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $schedule_defaults %he default from global settings.
	 */
	private $schedule_defaults;

	/**
	 * Rop_Scheduler_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct( 'rop_schedules_data' );

		$global_settings = new Rop_Global_Settings();

		$this->schedule_defaults = $global_settings->get_default_schedule();

		$this->schedules = $this->get_schedules();
	}

	/**
	 * Method to retrieve all the schedules from the DB.
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_schedules() {
		$services          = new Rop_Services_Model();
		$active_accounts   = $services->get_active_accounts();
		$schedules         = ( $this->get( 'schedules' ) != null ) ? $this->get( 'schedules' ) : array();
		$default_schedules = array();
		foreach ( $active_accounts as $account_id => $data ) {
			$default_schedules[ $account_id ] = $this->create_schedule( $this->schedule_defaults );
		}

		$filtered_schedules = wp_parse_args( $schedules, $default_schedules );
		unset( $filtered_schedules[''] );
		return $filtered_schedules;
	}

	/**
	 * Utility method to convert a float value fo HH:mm format.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   float $value The value to be converted.
	 * @param   bool  $as_array Flag to change return type to array.
	 * @return array|string
	 */
	private function convert_float_to_time( $value, $as_array = true ) {
		$hours   = floor( $value );
		$minutes = ( ( $value * 60 ) % 60 );
		if ( ! $as_array ) {
			return $hours . ':' . $minutes;
		}
		return array(
			'hours'   => $hours,
			'minutes' => $minutes,
		);
	}

	/**
	 * Utility method to add to specified time.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $time The time to append to.
	 * @param   int    $hours The hours to be added.
	 * @param   int    $minutes The minutes to be added.
	 * @param   bool   $is_timestamp Flag to specify if time is a timestamp.
	 * @param   string $format The date/time format of the return.
	 * @return false|string
	 */
	private function add_to_time( $time, $hours, $minutes, $is_timestamp = false, $format = 'Y-m-d H:i' ) {
		if ( $is_timestamp ) {
			$timestamp = strtotime( '+' . $hours . ' hour +' . $minutes . ' minutes', $time );
		} else {
			$timestamp = strtotime( '+' . $hours . ' hour +' . $minutes . ' minutes', strtotime( $time ) );
		}

		return date( $format, $timestamp );
	}

	/**
	 * Method to compute next day specified day of the week from given date.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string         $from_date Start date.
	 * @param   string|integer $day_of_the_week The number of the day of the week.
	 * @param   bool           $is_timestamp Flag to specify if given date is timestamp.
	 * @param   string         $format The return format for the date.
	 * @return false|string
	 */
	private function next_day_of_week( $from_date, $day_of_the_week, $is_timestamp = false, $format = 'Y-m-d' ) {
		$days = array(
			'1' => 'monday',
			'2' => 'tuesday',
			'3' => 'wednesday',
			'4' => 'thursday',
			'5' => 'friday',
			'6' => 'saturday',
			'7' => 'sunday',
		);
		if ( $is_timestamp ) {
			$timestamp = strtotime( 'next ' . $days[ $day_of_the_week ], $from_date );
		} else {
			$timestamp = strtotime( 'next ' . $days[ $day_of_the_week ], strtotime( $from_date ) );
		}

		return date( $format, $timestamp );
	}

	/**
	 * Method to create a schedule array.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $schedule_data The schedule data.
	 * @return mixed
	 */
	public function create_schedule( $schedule_data = array() ) {
		$schedule = $this->schedule_defaults;
		if ( in_array( $schedule_data['type'], array( 'recurring', 'fixed' ) ) ) {
			$schedule['type'] = $schedule_data['type'];
		}

		if ( isset( $schedule_data['interval_r'] ) ) {
			$schedule['interval_r'] = $schedule_data['interval_r'];
		}

		if ( isset( $schedule_data['interval_f'] ) ) {
			$schedule['interval_f'] = $schedule_data['interval_f'];
		}

		$schedule['first_share'] = strtotime( '+15 seconds', current_time( 'timestamp', 0 ) );
		if ( isset( $schedule_data['first_share'] ) && $schedule_data['first_share'] != null ) {
			$schedule['first_share'] = $schedule_data['first_share'];
		}

		if ( isset( $schedule_data['last_share'] ) && $schedule_data['last_share'] != null ) {
			$schedule['last_share'] = $schedule_data['last_share'];
		}

		return wp_parse_args( $schedule, $this->schedule_defaults );
	}

	/**
	 * Method to retrieve a schedule for the account id from the DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID.
	 * @return array|mixed
	 */
	public function get_schedule( $account_id ) {
		$this->schedules = $this->get_schedules();
		if ( isset( $this->schedules[ $account_id ] ) ) {
			return $this->schedules[ $account_id ];
		}
		return $this->schedule_defaults;
	}

	/**
	 * Method to add or update a schedule in DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID.
	 * @param   bool   $schedule_data The schedule data.
	 * @param   bool   $last_share A last share timestamp if needed.
	 * @return mixed
	 */
	public function add_update_schedule( $account_id, $schedule_data = false, $last_share = false ) {
		$this->schedules = $this->get_schedules();
		$schedule        = ( isset( $this->schedules[ $account_id ] ) && ! empty( $this->schedules[ $account_id ] ) ) ? $this->schedules[ $account_id ] : $this->schedule_defaults;
		if ( $schedule_data != false && is_array( $schedule_data ) && ! empty( $schedule_data ) ) {
			$schedule = $this->create_schedule( $schedule_data );
		}
		if ( $last_share ) {
			$schedule['last_share'] = $last_share;
		}
		$this->schedules[ $account_id ] = $schedule;
		return $this->set( 'schedules', $this->schedules );
	}

	/**
	 * Method to remove a schedule from DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID.
	 * @return mixed
	 */
	public function remove_schedule( $account_id ) {
		$this->schedules = $this->get_schedules();
		if ( isset( $this->schedules[ $account_id ] ) ) {
			unset( $this->schedules[ $account_id ] );
		}
		return $this->set( 'schedules', $this->schedules );
	}

	/**
	 * Method to compute and list upcoming schedules.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   int $future_events No. of future events to compute.
	 * @return array
	 */
	public function list_upcomming_schedules( $future_events = 10 ) {
		$this->schedules = $this->get_schedules();
		$list            = array();
		foreach ( $this->schedules as $account_id => $schedule ) {
			$list[ $account_id ] = array();
			if ( $schedule['type'] == 'recurring' ) {
				$i    = 0;
				$time = $this->convert_float_to_time( $schedule['interval_r'] );
				if ( $schedule['last_share'] == null ) {
					$event_time             = $this->add_to_time( $schedule['first_share'], $time['hours'], $time['minutes'], true );
					$schedule['last_share'] = $event_time;
					array_push( $list[ $account_id ], $event_time );
					$i++;
				}
				for ( $i; $i < $future_events; $i++ ) {
					$event_time             = $this->add_to_time( $schedule['last_share'], $time['hours'], $time['minutes'], false );
					$schedule['last_share'] = $event_time;
					array_push( $list[ $account_id ], $event_time );
				}
			} else {
				$week_days = $schedule['interval_f']['week_days'];
				$times     = $schedule['interval_f']['time'];
				$next_pos  = $this->get_days_start_pos( $week_days );
				$size      = sizeof( $week_days );
				$i         = 0;
				if ( $schedule['last_share'] == null ) {
					$event_date = $this->next_day_of_week( $schedule['first_share'], $week_days[ $next_pos ], true );
					foreach ( $times as $time ) {
						$event_time             = $event_date . ' ' . $time;
						$schedule['last_share'] = $event_time;
						array_push( $list[ $account_id ], $event_time );
					}
					$next_pos = $this->next_pos_in_size( $next_pos, $size );
					$i++;
				}
				for ( $i; $i < $future_events; $i++ ) {
					$event_date = $this->next_day_of_week( $schedule['last_share'], $week_days[ $next_pos ], false );
					foreach ( $times as $time ) {
						$event_time             = $event_date . ' ' . $time;
						$schedule['last_share'] = $event_time;
						array_push( $list[ $account_id ], $event_time );
					}
					$next_pos = $this->next_pos_in_size( $next_pos, $size );
				}
				$to_sort = $list[ $account_id ];
				uasort(
					$to_sort, function( $a, $b ) {
						return strtotime( $a ) - strtotime( $b );
					}
				);
				$list[ $account_id ] = array_slice( $to_sort, 0, $future_events );
			} // End if().
		} // End foreach().
		return $list;
	}

	/**
	 * Utility method to give a position bounded by two dimensions.
	 *
	 * Used to loop through the days of the week array and the times array as many times as needed.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   int $current_pos The current position.
	 * @param   int $size The upper limit.
	 * @param   int $first_pos The lower limit.
	 * @return int
	 */
	private function next_pos_in_size( $current_pos, $size = 1, $first_pos = 0 ) {
		$next_pos = $current_pos + 1;
		if ( $next_pos < $size ) {
			return $next_pos;
		}
		return $first_pos;
	}

	/**
	 * Computes the next available day of the week from current time
	 * with respect to the days of the week array passed from the schedules.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $days_of_week The days of the week array.
	 * @return int
	 */
	private function get_days_start_pos( $days_of_week ) {
		for ( $i = 0; $i < sizeof( $days_of_week ); $i++ ) {
			if ( $days_of_week[ $i ] >= date( 'N', current_time( 'timestamp', 0 ) ) ) {
				return $i;
			}
		}
		return 0;
	}

}
