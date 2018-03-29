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
	 * Get the start time.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     int $current_time The current time.
	 */
	private $start_time;

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
		$this->start_time        = $global_settings->get_start_time();
		$this->schedules         = $this->get_schedules();
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
		$services        = new Rop_Services_Model();
		$active_accounts = $services->get_active_accounts();

		$schedules       = ( $this->get( 'schedules' ) != null ) ? $this->get( 'schedules' ) : array();
		$valid_schedules = array();
		foreach ( $active_accounts as $account_id => $data ) {

			$valid_schedules[ $account_id ] = isset( $schedules[ $account_id ] ) ? $schedules[ $account_id ] : $this->create_schedule( $this->schedule_defaults );
		}
		$valid_schedules = array_filter( $valid_schedules );

		return $valid_schedules;
	}

	/**
	 * Method to create a schedule array.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $schedule_data The schedule data.
	 *
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
	 *
	 * @param   string $account_id The account ID.
	 *
	 * @return array|mixed
	 */
	public function get_schedule( $account_id = null ) {

		$this->schedules = $this->get_schedules();

		if ( empty( $account_id ) ) {
			return $this->schedules;
		}
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
	 *
	 * @param   string $account_id The account ID.
	 * @param   bool   $schedule_data The schedule data.
	 * @param   bool   $last_share A last share timestamp if needed.
	 *
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
	 *
	 * @param   string $account_id The account ID.
	 *
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
	 *
	 * @param   int $future_events No. of future events to compute.
	 *
	 * @return array
	 */
	public function list_upcomming_schedules( $future_events = 10 ) {
		$this->schedules = $this->get_schedules();
		$list            = array();
		foreach ( $this->schedules as $account_id => $schedule ) {
			$list[ $account_id ] = array();
			/**
			 * If we just started the sharing, share the post in the next 30s.
			 */
			if ( ( time() - $this->start_time ) < 15 ) {
				array_push( $list[ $account_id ], time() + 20 );
			}

			if ( $schedule['type'] === 'recurring' ) {
				$time       = $this->convert_float_to_time( $schedule['interval_r'] );
				$event_time = self::get_current_time();

				for ( $i = 0; $i < $future_events; $i ++ ) {
					$event_time = $this->add_to_time( $event_time, $time['hours'], $time['minutes'] );
					array_push( $list[ $account_id ], $event_time );
				}
			} else {
				$week_days = $schedule['interval_f']['week_days'];
				/**
				 * If we  don't have any weekdays/times set, bail.
				 * TODO Log the error.
				 */
				if ( count( $week_days ) === 0 ) {
					continue;
				}
				$times = $schedule['interval_f']['time'];
				if ( count( $times ) === 0 ) {
					continue;
				}

				sort( $week_days );
				$times = array_map( function ( $time ) {
					return $this->convert_string_to_float( $time );
				}, $times );
				sort( $times );
				/**
				 * Get first available week days.
				 */
				$start_week = $this->get_week_start();

				$i = 0;

				while ( $i < $future_events ) {
					/**
					 * Get the first available day comparing with the previous event.
					 */
					foreach ( $week_days as $day ) {
						$event_day = $start_week + ( ( intval( $day ) - 1 ) * DAY_IN_SECONDS );

						foreach ( $times as $time ) {
							$event_time = $event_day + $time;

							/**
							 * If event is older than today, bail.
							 */
							if ( $event_time < self::get_current_time() ) {
								continue;
							}
							$i ++;
							array_push( $list[ $account_id ], $event_time );

						}
					}

					$start_week = strtotime( '+1 week', $start_week );
				}
				$to_sort = $list[ $account_id ];
				sort( $to_sort );
				$list[ $account_id ] = array_slice( $to_sort, 0, $future_events );

			} // End if().
			$list[ $account_id ] = array_map( function ( $value ) {
				return self::get_date( $value );
			}, $list[ $account_id ] );
		} // End foreach().

		return $list;
	}

	/**
	 * Utility method to convert a float value fo HH:mm format.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   float $value The value to be converted.
	 * @param   bool  $as_array Flag to change return type to array.
	 *
	 * @return array|string
	 */
	private function convert_float_to_time( $value, $as_array = true ) {
		$value   = floatval( $value );
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
	 * Get current timestamp regardless of the blog settings.
	 *
	 * @return int
	 */
	public static function get_current_time() {
		return current_time( 'timestamp' );
	}

	/**
	 * Utility method to add to specified time.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $time The time to append to.
	 * @param   int    $hours The hours to be added.
	 * @param   int    $minutes The minutes to be added.
	 *
	 * @return false|string
	 */
	private function add_to_time( $time, $hours = 0, $minutes = 0 ) {
		$timestamp = strtotime( '+' . $hours . ' hour +' . $minutes . ' minutes', $time );

		return $timestamp;
	}

	/**
	 * Utility method to convert a time string to int seconds.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $value The value to be converted.
	 *
	 * @return integer
	 */
	private function convert_string_to_float( $value ) {
		$parts = explode( ':', $value );
		if ( count( $parts ) !== 2 ) {
			return 0;
		}

		return intval( $parts[0] ) * 3600 + intval( $parts[1] ) * 60;
	}

	/**
	 *
	 * Return current timestamp for the current week.
	 *
	 * @return false|string
	 */
	private function get_week_start() {
		$strtotime = date( 'o-\WW', self::get_current_time() );
		$start     = strtotime( $strtotime );

		return intval( $start );
	}

	/**
	 * Get date according to WordPress settings.
	 *
	 * @param int $timestamp Timestamp to format.
	 *
	 * @return int
	 */
	public static function get_date( $timestamp = 0 ) {
		if ( empty( $timestamp ) ) {
			$timestamp = self::get_current_time();
		}

		return date( self::get_date_format(), $timestamp );
	}

	/**
	 * Return date format according to WordPress settings.
	 *
	 * @return string Current date format.
	 */
	public static function get_date_format() {
		return get_option( 'date_format', '' ) . ' ' . get_option( 'time_format', '' );
	}

}
