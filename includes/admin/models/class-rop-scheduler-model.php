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
	 * Stores the schedules to be skipped.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $skips The schedules to be skipped.
	 */
	private $skips;

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

		$this->schedules = $this->get_schedules();
		$this->skips = $this->get_skips();

		$global_settings = new Rop_Global_Settings();

		$this->schedule_defaults = $global_settings->get_default_schedule();
	}

	/**
	 * Method to retrieve all the schedules from the DB.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_schedules() {
		return ( $this->get( 'schedules' ) != null ) ?  $this->get( 'schedules' ) : array();
	}

	/**
	 * Method to retrieve all the skips from the DB.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @return array
	 */
	private function get_skips() {
		return ( $this->get( 'skips' ) != null ) ?  $this->get( 'skips' ) : array();
	}

	/**
	 * Utility method to check if a time value is of float type.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $time The time value to check.
	 * @return bool
	 */
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

	/**
	 * Utility method to convert a string of HH:mm format to float,
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $value The value to be converted.
	 * @return float|int
	 */
	private function convert_time_to_float( $value ) {
		list( $hour, $minutes ) = explode( ':', $value );
		return $hour + floor( ( $minutes / 60 ) * 100 ) / 100;
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
		$schedule = ( isset( $this->schedules[ $account_id ] ) && ! empty( $this->schedules[ $account_id ] ) ) ? $this->schedules[ $account_id ] : $this->schedule_defaults ;
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
	 * Method to add to skips array.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account iD to be skipped.
	 * @param   string $time A date time format string.
	 * @return mixed
	 */
	public function add_to_skips( $account_id, $time, $is_timestamp = false ) {
		$this->skips = $this->get_skips();
		if ( $is_timestamp ) {
		    $time = date( 'Y-m-d H:i', $time );
		}
		if ( ! isset( $this->skips[ $account_id ] ) ) {
			$this->skips[ $account_id ] = array();
		}
		array_push( $this->skips[ $account_id ], $time );
		return $this->set( 'skips', $this->skips );
	}

	/**
	 * Method to remove account from skipped,
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string      $account_id The account ID.
	 * @param   bool|string $time Flag to specify if all skips should be removed or just a specific time.
	 * @return mixed
	 */
	public function remove_skips( $account_id, $time = false ) {
		$this->skips = $this->get_skips();
		if ( isset( $this->skips[ $account_id ] ) ) {
			if ( $time ) {
				$to_remove = array( $time );
				$this->skips[ $account_id ] = array_diff( $this->skips[ $account_id ], $to_remove );
			} else {
				unset( $this->skips[ $account_id ] );
			}
		}
		return $this->set( 'skips', $this->skips );
	}

	/**
	 * Return the skips for a given account ID.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID.
	 * @return array|mixed
	 */
	public function get_account_skips( $account_id ) {
		if ( isset( $this->skips[ $account_id ] ) ) {
			return $this->skips[ $account_id ];
		}
		return array();
	}

	/**
	 * Utility method to determine if a given date is in skips for a given account ID.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $account_id The account ID.
	 * @param   string $time The date time to look for.
	 * @param   bool   $is_timestamp Flag to specify if the given time is timestamp.
	 * @return bool
	 */
	private function is_in_skips( $account_id, $time, $is_timestamp = false ) {
	    $account_skips = $this->get_account_skips( $account_id );
	    if ( $is_timestamp ) {
	        return in_array( date( 'Y-m-d H:i', $time ), $account_skips );
		} else {
			return in_array( $time, $account_skips );
		}
	}

	/**
	 * Method to compute and list upcoming schedules.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   int $future_events No. of future events to compute.
	 * @return array
	 */
	public function list_upcomming_schedules( $future_events = 5 ) {
		$this->schedules = $this->get_schedules();
		$this->skips = $this->get_skips();
		$list = array();
		foreach ( $this->schedules as $account_id => $schedule ) {
			$list[ $account_id ] = array();
			if ( $schedule['type'] == 'recurring' ) {
			    $i = 0;
				if ( $schedule['last_share'] == null ) {
					$time = $this->convert_float_to_time( $schedule['interval_r'] );
					$event_time = $this->add_to_time( $schedule['first_share'], $time['hours'], $time['minutes'], true );
					while ( $this->is_in_skips( $account_id, $event_time ) ) {
					    $last_time = $event_time;
						$event_time = $this->add_to_time( $last_time, $time['hours'], $time['minutes'], false );
					}
					$schedule['last_share'] = $event_time;
					array_push( $list[ $account_id ], $event_time );
					$i++;
				}
				for ( $i; $i < $future_events; $i++ ) {
					$event_time = $this->add_to_time( $schedule['last_share'], $time['hours'], $time['minutes'], false );
					while ( $this->is_in_skips( $account_id, $event_time ) ) {
						$last_time = $event_time;
						$event_time = $this->add_to_time( $last_time, $time['hours'], $time['minutes'], false );
					}
					$schedule['last_share'] = $event_time;
					array_push( $list[ $account_id ], $event_time );
				}
			} else {
				$i = 0;
				$week_days = $schedule['interval_f']['week_days'];
				$time = $schedule['interval_f']['time'];
				$next_pos = $this->get_days_start_pos( $week_days );
				$size = sizeof( $week_days );
				if ( $schedule['last_share'] == null ) {
					$event_time = $this->next_day_of_week( $schedule['first_share'], $week_days[ $next_pos ], true ) . ' ' . $time;
					while ( $this->is_in_skips( $account_id, $event_time ) ) {
						$last_time = $event_time;
						$event_time = $this->next_day_of_week( $last_time, $week_days[ $next_pos ], false ) . ' ' . $time;
					}
					$schedule['last_share'] = $event_time;
					$next_pos = $this->next_pos_in_size( $next_pos, $size );
					array_push( $list[ $account_id ], $event_time );
					$i++;
				}
				for ( $i; $i < $future_events; $i++ ) {
					$event = array( 'account_id' => $account_id );
					$event_time = $this->next_day_of_week( $schedule['last_share'], $week_days[ $next_pos ], false ) . ' ' . $time;
					while ( $this->is_in_skips( $account_id, $event_time ) ) {
						$last_time = $event_time;
						$event_time = $this->next_day_of_week( $last_time, $week_days[ $next_pos ], false ) . ' ' . $time;
					}
					$schedule['last_share'] = $event_time;
					array_push( $list[ $account_id ], $event_time );
					$next_pos = $this->next_pos_in_size( $next_pos, $size );
				}
			}// End if().
		}// End foreach().
		return $list;
	}

	/**
	 * Utility method to give a position bounded by two dimensions.
	 *
	 * Used to loop through the days of the week array as many times as needed.
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
