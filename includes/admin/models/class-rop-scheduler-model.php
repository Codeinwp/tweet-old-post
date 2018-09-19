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
	 * Number of events to show in the queue per account.
	 */
	const EVENTS_PER_ACCOUNT = 10;
	/**
	 * Holds the logger
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     Rop_Logger $logger The logger handler.
	 */
	protected $logger;
	/**
	 * Stores the current schedules per account.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $schedules The current schedules.
	 */
	private $schedules;
	/**
	 * Events timeline option key.
	 *
	 * @var string Events option key.
	 */
	private $events_namespace = 'rop_events_timeline';
	/**
	 * Get the start time.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     int $current_time The current time.
	 */
	private $start_time;
	/**
	 * License type.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     int $license_type License plan type.
	 */
	private $license_type;

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
		$this->logger    = new Rop_Logger();

		$this->schedule_defaults = $global_settings->get_default_schedule();
		$this->license_type      = $global_settings->license_type();
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
			if ( $this->license_type < 2 ) {
				$valid_schedules[ $account_id ] = $this->create_schedule( $this->schedule_defaults );
				continue;
			}
			$valid_schedules[ $account_id ] = isset( $schedules[ $account_id ] ) ? $schedules[ $account_id ] : $this->create_schedule( $this->schedule_defaults );
		}

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
			$schedule['interval_r'] = round( $schedule_data['interval_r'], 2 );
		}

		if ( isset( $schedule_data['interval_f'] ) ) {
			$schedule['interval_f'] = $schedule_data['interval_f'];
		}

		return wp_parse_args( $schedule, $this->schedule_defaults );
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
	 * Get current timestamp regardless of the blog settings.
	 *
	 * @return int
	 */
	public static function get_current_time() {
		return current_time( 'timestamp' );
	}

	/**
	 * Return date format according to WordPress settings.
	 *
	 * @return string Current date format.
	 */
	public static function get_date_format() {
		return get_option( 'date_format', '' ) . ' ' . get_option( 'time_format', '' );
	}

	/**
	 * Method to add or update a schedule in DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $account_id The account ID.
	 * @param   bool   $schedule_data The schedule data.
	 *
	 * @return mixed
	 */
	public function add_update_schedule( $account_id, $schedule_data = false ) {

		$this->schedules = $this->get_schedules();
		$schedule        = ( isset( $this->schedules[ $account_id ] ) && ! empty( $this->schedules[ $account_id ] ) ) ? $this->schedules[ $account_id ] : $this->schedule_defaults;
		if ( $schedule_data != false && is_array( $schedule_data ) && ! empty( $schedule_data ) ) {
			$schedule = $this->create_schedule( $schedule_data );
		}
		$this->schedules[ $account_id ] = $schedule;

		$this->set( 'schedules', $this->schedules );
		// Refresh events when we change the schedule.
		$this->refresh_events( $account_id );

	}

	/**
	 * Refresh timeline for all accounts or specific account.
	 *
	 * Used when we change a schedule for a particular account.
	 * Used when we toggle an account state.
	 *
	 * @param string $account_id Account id to update.
	 *
	 * @return bool
	 */
	public function refresh_events( $account_id = 0 ) {
		$current_events = $this->get_all_upcoming_events();
		if ( empty( $current_events ) ) {
			return false;
		}
		if ( empty( $account_id ) ) {
			$this->update_timeline( array() );
		}
		if ( isset( $current_events[ $account_id ] ) ) {
			$current_events[ $account_id ] = array();
			$this->update_timeline( $current_events );
		}

		return true;
	}

	/**
	 * Get all upcoming events.
	 *
	 * @return array Events array.
	 */
	public function get_all_upcoming_events() {
		$events    = array();
		$schedules = $this->get_schedules();
		foreach ( $schedules as $account_id => $schedule_data ) {
			$events[ $account_id ] = $this->get_upcoming_events( $account_id );
		}

		return $events;
	}

	/**
	 * Get upcoming events for a certain account.
	 *
	 * If the events are missing or are less than the limit, regenerate them.
	 *
	 * @param string $account_id Account to update.
	 *
	 * @return array List of upcoming events.
	 */
	public function get_upcoming_events( $account_id = 0 ) {
		if ( empty( $account_id ) ) {
			return array();
		}
		$current_events = $this->get( $this->events_namespace );
		if ( ! is_array( $current_events ) ) {
			$current_events = array();
		}
		$account_events = isset( $current_events[ $account_id ] ) ? $current_events[ $account_id ] : array();
		if ( ! is_array( $account_events ) ) {
			$account_events = array();
		}
		if ( count( $account_events ) === self::EVENTS_PER_ACCOUNT ) {
			return $account_events;
		}
		if ( empty( $account_events ) ) {
			$events = $this->generate_upcoming_events( self::get_current_time(), $account_id, self::EVENTS_PER_ACCOUNT );
		} else {
			$last_time  = $account_events [ count( $account_events ) - 1 ];
			$events_new = $this->generate_upcoming_events( $last_time, $account_id, self::EVENTS_PER_ACCOUNT - count( $account_events ) );
			$events     = array_merge( $account_events, $events_new );
		}
		sort( $events );
		$prev                          = null;
		$events                        = array_filter(
			$events,
			function ( $value ) use ( &$prev ) {
				if ( empty( $prev ) ) {
					$prev = $value;

					return true;
				}
				/**
			 * Dont allow consecutive shared events on less than 60s diff.
			 */
				if ( abs( $value - $prev ) < 60 ) {
					return false;
				}
				$prev = $value;

				return true;

			}
		);
		$current_events[ $account_id ] = $events;

		$this->update_timeline( $current_events );

		return $events;
	}

	/**
	 * Method to compute and get upcoming schedules
	 * using a basetime according to an account schedule.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   int    $base       Timestamp to reffer to.
	 * @param   string $account_id Timestamp to reffer to.
	 *
	 * @return array
	 */
	public function generate_upcoming_events( $base = 0, $account_id, $limit = 0 ) {

		$schedule = $this->get_schedule( $account_id );
		if ( empty( $schedule ) ) {
			return array();
		}

		if ( empty( $base ) ) {
			$base = self::get_current_time();
		}
		$base = intval( $base );
		if ( empty( $limit ) ) {
			$limit = self::EVENTS_PER_ACCOUNT;
		}
		$limit = intval( $limit );

		$list = array();
		/**
		 * If we just started the sharing, share the post in the next 30s.
		 * Use time() as base refference here as current_time is affected by gmt.
		 */
		if ( ( time() - $this->start_time ) < 15 ) {
			array_push( $list, self::get_current_time() + 20 );
			$limit --;
		}

		if ( $schedule['type'] === 'recurring' ) {
			/**
			 * Get seconds eq of the recurring interval.
			 */
			$time       = $this->convert_float_to_time( $schedule['interval_r'] );
			$event_time = $base;
			for ( $i = 0; $i < $limit; $i ++ ) {
				$event_time = $this->add_to_time( $event_time, $time['hours'], $time['minutes'] );
				array_push( $list, $event_time );
			}
		} else {
			$week_days = $schedule['interval_f']['week_days'];
			/**
			 * If we  don't have any weekdays/times set, bail.
			 */
			if ( count( $week_days ) === 0 ) {
				$this->logger->alert_error( 'No week days selected in custom schedule for this account' );

				return array();
			}
			$times = $schedule['interval_f']['time'];
			if ( count( $times ) === 0 ) {
				$this->logger->alert_error( 'No times selected in custom schedule for this account' );

				return array();
			}

			sort( $week_days );
			/**
			 * Convert time string repres to no. of seconds in that day.
			 * i.e 17:10 ->  ( 17 * 3600 + 10 * 60 )
			 */
			$times = array_map(
				function ( $time ) {
					return $this->convert_string_to_float( $time );
				},
				$times
			);
			sort( $times );
			/**
			 * Get timestamp for the start of the week.
			 */
			$start_week = $this->get_week_start( $base );

			$i = 0;

			while ( $i < $limit ) {
				/**
				 * Build event time having as base the week start timestamp,
				 * selected weekday number * DAY_IN_SECONDS + selected time in seconds.
				 */
				foreach ( $week_days as $day ) {
					$event_day = $start_week + ( ( intval( $day ) - 1 ) * DAY_IN_SECONDS );

					foreach ( $times as $time ) {
						$event_time = $event_day + $time;

						/**
						 * If event is older than base time, bail.
						 */
						if ( $event_time < $base ) {
							continue;
						}
						if ( $i === $limit ) {
							break;
						}
						$i ++;
						array_push( $list, $event_time );

					}
				}
				/**
				 * If we still need events, increment base start week with 1 week.
				 */
				$start_week = strtotime( '+1 week', $start_week );
			}
		} // End if().
		sort( $list );

		return $list;
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
	 * Utility method to convert a float value fo HH:mm format.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   float $value    The value to be converted.
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
	 * Utility method to add to specified time.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $time    The time to append to.
	 * @param   int    $hours   The hours to be added.
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
	private function get_week_start( $start = 0 ) {
		if ( empty( $start ) ) {
			$start = self::get_current_time();
		}
		$strtotime = date( 'o-\WW', $start );
		$start     = strtotime( $strtotime );

		return intval( $start );
	}

	/**
	 * Update the events timeline.
	 *
	 * @param array $new_events New events timeline.
	 *
	 * @return bool Success or not.
	 */
	public function update_timeline( $new_events, $account_id = '' ) {
		if ( ! is_array( $new_events ) ) {
			return false;
		}
		/**
		 * Keep only valid account events.
		 */
		$valid_events = array();
		$schedules    = $this->get_schedules();
		$old_events   = $this->get( $this->events_namespace );
		foreach ( $schedules as $id => $schedule ) {
			$valid_events[ $id ] = isset( $old_events[ $id ] ) ? $old_events[ $id ] : array();
			$valid_events[ $id ] = empty( $account_id ) ? $new_events[ $id ] : ( $id === $account_id ? $new_events : $valid_events[ $id ] );
		}
		$this->set( $this->events_namespace, $valid_events );

		return true;
	}

	/**
	 * Remove timestamp from timeline.
	 *
	 * @param int    $timestamp Timestamp value.
	 * @param string $account_id Account id.
	 *
	 * @return int Index to remove.
	 */
	public function remove_timestamp( $timestamp, $account_id ) {

		$schedule     = $this->get_upcoming_events( $account_id );
		$key          = array_search( $timestamp, $schedule );
		$new_schedule = array_diff( $schedule, array( $timestamp ) );
		$new_schedule = array_values( $new_schedule );
		$this->update_timeline( $new_schedule, $account_id );

		return $key;
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


}
