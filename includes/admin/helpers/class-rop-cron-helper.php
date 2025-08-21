<?php
/**
 * Used to manage WordPress Cron for Rop.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0rc
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Rop_Cron_Helper Class
 *
 * @since      8.0.0rc
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop_Cron_Helper {
	/**
	 * Cron action name.
	 */
	const CRON_NAMESPACE = 'rop_cron_job';

	/**
	 * Cron action name.
	 */
	const CRON_NAMESPACE_ONCE = 'rop_cron_job_once';

	/**
	 * Cron action name for sharing specific post(s).
	 */
	const CRON_NAMESPACE_PUBLISH_NOW = 'rop_cron_job_publish_now';

	/**
	 * Defines new schedules for cron use.
	 *
	 * @param array $schedules The schedules array.
	 *
	 * @return mixed
	 * @since   8.0.0
	 * @access  public
	 */
	public static function rop_cron_schedules( $schedules ) {
		$schedules['5min'] = array(
			'interval' => 5 * 60,
			'display'  => Rop_I18n::get_labels( 'general.cron_interval' ),
		);

		return $schedules;
	}

	/**
	 * Utility method to manage cron.
	 *
	 * @return  array Current status.
	 * @since   8.0.0rc
	 * @access  public
	 */
	public function manage_cron( $request ) {
		if ( isset( $request['action'] ) && 'start' === $request['action'] ) {
			$this->create_cron( true );
			do_action( 'rop_process_start_share' );
		} elseif ( isset( $request['action'] ) && 'stop' === $request['action'] ) {
			$this->remove_cron( $request );
			do_action( 'rop_process_stop_share' );
		} elseif ( isset( $request['action'] ) && 'publish-now' === $request['action'] ) {
			$this->publish_now();
		}

		return array(
			'current_status'   => $this->get_status(),
			'next_event_on'    => $this->next_event(),
			'logs_number'      => $this->get_logs_number(),
			'date_format'      => $this->convert_phpformat_to_js( Rop_Scheduler_Model::get_date_format() ),
			'current_php_date' => Rop_Scheduler_Model::get_date(),
			'current_time'     => Rop_Scheduler_Model::get_current_time(),
		);
	}

	/**
	 * Update database to which Cron System to use.
	 *
	 * @param array $request Cron type.
	 *
	 * @return bool
	 * @since 8.6.0
	 * @access public
	 * @category New Cron System
	 */
	public function update_cron_type( $request ) {
		if ( ! empty( $request ) && isset( $request['action'] ) ) {
			$is_remote_cron = $request['action'];
			update_option( 'rop_use_remote_cron', $is_remote_cron );
			$this->cron_status_global_change( false );

			$is_registered = get_option( 'rop_access_token', false );
			/**
			 * We need to make sure we stop the remote CronJob when cron-type is changed.
			 * if the user is registered to the remote Cron System.
			 */
			if ( false === $is_remote_cron && ! empty( $is_registered ) ) {

				// load the library
				if ( class_exists( 'RopCronSystem\Rop_Cron_Core' ) ) {

					new RopCronSystem\Rop_Cron_Core();

					// Request cron stop
					$stop_cron = new RopCronSystem\Rop_Cron_Core();
					$stop_cron->server_stop_share();
				} else {
					$log = new Rop_Logger();
					$log->alert_error( 'Error: Cannot find ROP_Cron_Core Class.' );
					return false;
				}
			}

			return true;
		}

		return false;
	}


	/**
	 * Utility method to create a single event for publishing specific post(s).
	 *
	 * @access  private
	 * @return bool
	 */
	private function publish_now() {
		if ( ! $this->is_scheduled( self::CRON_NAMESPACE_PUBLISH_NOW ) ) {
			$this->schedule_single_event( time() + 10, self::CRON_NAMESPACE_PUBLISH_NOW );
		}

		return true;
	}


	/**
	 * Utility method to start a cron.
	 *
	 * @param bool $first cron that runs once.
	 *
	 * @return bool
	 * @since   8.0.0rc
	 * @access  public
	 */
	public function create_cron( $first = true ) {
		if ( defined( 'ROP_CRON_ALTERNATIVE' ) && false === ROP_CRON_ALTERNATIVE && ! $this->is_scheduled( self::CRON_NAMESPACE ) ) {
			if ( $first ) {
				$this->fresh_start();
				$settings = new Rop_Global_Settings();
				$settings->update_start_time();
				$this->schedule_single_event( time() + 30, self::CRON_NAMESPACE_ONCE );
			}
			$this->schedule_event( time(), '5min', self::CRON_NAMESPACE );
			/**
			 * Changing this option to true, upon page refresh the WP Cron Jobs will work as normal.
			 * This value must become true anytime the "Start Share" button is clicked.
			 *
			 * @see Rop_Admin::check_cron_status()
			 */
			$this->cron_status_global_change( true );

		} elseif ( defined( 'ROP_CRON_ALTERNATIVE' ) && true === ROP_CRON_ALTERNATIVE ) {

			if ( $first ) {
				$this->fresh_start();
				$settings = new Rop_Global_Settings();
				$settings->update_start_time();
				/**
				 * Changing this option to true, upon page refresh the WP Cron Jobs will work as normal.
				 * This value must become true anytime the "Start Share" button is clicked.
				 *
				 * @see Rop_Admin::check_cron_status()
				 */
				$this->cron_status_global_change( true );
			}
		}

		return true;
	}

	/**
	 * Utility method to stop a cron.
	 *
	 * @param array $request data transmitted via ajax.
	 *
	 * @return bool
	 * @since   8.0.0rc
	 * @access  public
	 */
	public function remove_cron( $request = array() ) {
		global $wpdb;

		/**
		 * Changing this option to false, upon page refresh the WP Cron Jobs will be cleared.
		 * This value must become false anytime the "Stop Share" button is clicked.
		 *
		 * @see Rop_Admin::check_cron_status()
		 */
		$this->cron_status_global_change( false );

		// Clear jobs.
		$this->clear_jobs();

		return false;
	}

	/**
	 * Clear cron jobs.
	 */
	public function clear_jobs() {
		// Check action scheduler is exists or not.
		if ( function_exists( 'as_get_scheduled_actions' ) ) {
			$this->clear_action_scheduler_jobs();
		} else {
			$this->clear_wp_cron_jobs();
		}
	}

	/**
	 * Will return the cron MD5 key used to unschedule cron event
	 *
	 * @param string|array $namespace array for multiple cron data.
	 *
	 * @return array|bool
	 * @since 8.5.0
	 *
	 * @see wp_unschedule_event()
	 * @see _get_cron_array()
	 */
	public static function get_schedule_key( $namespace ) {
		if ( empty( $namespace ) ) {
			return false;
		}

		if ( is_array( $namespace ) ) {
			$namespace = array_map( 'strtolower', $namespace );
		}

		$return_keys = array();
		$cron_list   = _get_cron_array();
		if ( ! empty( $cron_list ) ) {
			foreach ( $cron_list as $cron_time => $cron_data ) {
				$cron_name = key( $cron_data );

				if (
					( is_array( $namespace ) && in_array( strtolower( $cron_name ), $namespace, true ) )
					||
					( is_string( $namespace ) && strtolower( $cron_name ) === strtolower( $namespace ) )
				) {
					$key           = isset( $cron_data[ $cron_name ] ) ? key( $cron_data[ $cron_name ] ) : '';
					$return_keys[] = array(
						'time'      => $cron_time, // next time the cron will run.
						'key'       => $key, // This is the cron signature.
						'namespace' => $cron_name, // cron name space.
					);
				}
			}

			if ( ! empty( $return_keys ) ) {
				return $return_keys;
			}
		}

		return false;
	}

	/**
	 * Change the option that handles the cron status.
	 *
	 * @param bool $action true/false if crons should work or stop.
	 *
	 * @since 8.5.0
	 */
	function cron_status_global_change( $action = false ) {
		$key         = 'rop_is_sharing_cron_active';
		$cron_status = ( true === $action ) ? 'yes' : 'no';

		update_option( $key, $cron_status, 'no' );
	}

	/**
	 * Get cron status.
	 *
	 * @return bool Cron status.
	 */
	public function get_status() {

		if ( defined( 'ROP_CRON_ALTERNATIVE' ) && true === ROP_CRON_ALTERNATIVE ) {
			return filter_var( get_option( 'rop_is_sharing_cron_active', 'no' ), FILTER_VALIDATE_BOOLEAN );
		} else {
			return is_int( $this->is_scheduled( self::CRON_NAMESPACE ) );
		}
	}

	/**
	 * Get next event timestamp.
	 *
	 * @return int Timestamp.
	 */
	public function next_event() {
		if ( $this->get_status() === false ) {
			return 0;
		}

		$scheduler = new Rop_Scheduler_Model();
		$events    = $scheduler->get_all_upcoming_events();
		$min       = PHP_INT_MAX;
		foreach ( $events as $account_events ) {
			foreach ( $account_events as $event_time ) {

				if ( ( $event_time < $min ) && $event_time > Rop_Scheduler_Model::get_current_time() ) {
					$min = $event_time;
				}
			}
		}

		return $min;
	}

	/**
	 * Get number of active logs.
	 *
	 * @return int Timestamp.
	 */
	public function get_logs_number() {
		$logger = new Rop_Logger();
		$logs   = $logger->get_logs();

		return count( $logs );
	}

	/**
	 * Convert PHP Format to JS
	 *
	 * @param string $format Php format.
	 *
	 * @return string
	 */
	private function convert_phpformat_to_js( $format ) {
		$replacements  = array(
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => 'E',
			'S' => 'o',
			'w' => 'e',
			'z' => 'DDD',
			'W' => 'W',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '', // no equivalent
			'L' => '', // no equivalent
			'o' => 'YYYY',
			'Y' => 'YYYY',
			'y' => 'YY',
			'a' => 'a',
			'A' => 'A',
			'B' => '', // no equivalent
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => 'SSS',
			'e' => 'zz', // deprecated since version 1.6.0 of moment.js
			'I' => '', // no equivalent
			'O' => '', // no equivalent
			'P' => '', // no equivalent
			'T' => '', // no equivalent
			'Z' => '', // no equivalent
			'c' => '', // no equivalent
			'r' => '', // no equivalent
			'U' => 'X',
		);
		$moment_format = strtr( $format, $replacements );

		return $moment_format;
	}

	/**
	 * Clear all queue related data.
	 */
	private function fresh_start() {
		/**
		 * Reset start time.
		 */
		$settings = new Rop_Global_Settings();
		$settings->reset_start_time();
		/**
		 * Reset timeline events.
		 */
		$scheduler = new Rop_Scheduler_Model();
		$scheduler->refresh_events();

		/**
		 * Reset queue events.
		 */
		$scheduler = new Rop_Queue_Model();
		$scheduler->clear_queue();
		/**
		 * Clear buffer for all accounts.
		 */
		$selector = new Rop_Posts_Selector_Model();
		$selector->clear_buffer();
		/**
		 * Clear all blocked posts.
		 */
		$selector->clear_blocked_posts();
	}

	/**
	 * Check if an action hook is scheduled.
	 *
	 * @param string $hook The hook to check.
	 * @param array  $args Optional. Arguments to pass to the hook.
	 *
	 * @return bool|int
	 */
	public function is_scheduled( string $hook, array $args = array() ) {
		if ( function_exists( 'as_has_scheduled_action' ) ) {
			return as_has_scheduled_action( $hook, $args ) ? time() : false;
		}

		if ( function_exists( 'as_next_scheduled_action' ) ) {
			// For older versions of AS.
			return as_next_scheduled_action( $hook, $args );
		}

		return wp_next_scheduled( $hook, $args );
	}

	/**
	 * Clear scheduled hook.
	 *
	 * @param string $hook The name of the hook to clear.
	 * @param array  $args Optional. Arguments that were to be passed to the hook's callback function. Default empty array.
	 * @return mixed The scheduled action ID if a scheduled action was found, or null if no matching action found. If WP_Cron is used, on success an integer indicating number of events unscheduled, false or WP_Error if unscheduling one or more events fail.
	 */
	public static function clear_scheduled_hook( $hook, $args = array() ) {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			return as_unschedule_all_actions( $hook, $args );
		}

		return wp_clear_scheduled_hook( $hook, $args );
	}

	/**
	 * Scheduled single event cron.
	 *
	 * @param int    $timestamp The Unix timestamp representing the date you want the action to run.
	 * @param string $hook Name of the action hook.
	 */
	public static function schedule_single_event( $timestamp, $hook ) {
		if ( function_exists( 'as_schedule_single_action' ) ) {
			return as_schedule_single_action( $timestamp, $hook );
		}

		return wp_schedule_single_event( $timestamp, $hook );
	}

	/**
	 * Schedule an event.
	 *
	 * @param int    $time       The first time that the event will occur.
	 * @param string $recurrence How often the event should recur. See wp_get_schedules() for accepted values.
	 * @param string $hook       The name of the hook that will be triggered by the event.
	 * @param array  $args       Optional. Arguments to pass to the hook's callback function. Default empty array.
	 * @return integer|bool|WP_Error The action ID if Action Scheduler is used. True if event successfully scheduled, False or WP_Error on failure if WP Cron is used.
	 */
	public static function schedule_event( $time, $recurrence, $hook, $args = array() ) {
		if ( function_exists( 'as_schedule_recurring_action' ) ) {
			$schedules = wp_get_schedules();
			if ( isset( $schedules[ $recurrence ] ) ) {
				$interval = $schedules[ $recurrence ]['interval'];
				return as_schedule_recurring_action( $time, $interval, $hook, $args );
			}
		}

		return wp_schedule_event( $time, $recurrence, $hook, $args );
	}

	/**
	 * Remove action scheduler event.
	 *
	 * @return void
	 */
	private function clear_action_scheduler_jobs() {
		$rop_cron_hooks = array( self::CRON_NAMESPACE, self::CRON_NAMESPACE_ONCE );
		foreach ( $rop_cron_hooks as $cron_hook ) {
			$scheduled_actions = as_get_scheduled_actions(
				array(
					'hook'   => $cron_hook,
					'status' => ActionScheduler_Store::STATUS_PENDING,
				)
			);

			if ( ! empty( $scheduled_actions ) ) {
				foreach ( $scheduled_actions as $scheduled_action ) {
					as_unschedule_action( $scheduled_action->get_hook(), $scheduled_action->get_args() );
				}
			}
		}

		$this->fresh_start();
	}

	/**
	 * Remove scheduled cron jobs from WP-Cron.
	 *
	 * @return void
	 */
	private function clear_wp_cron_jobs() {
		global $wpdb;

		$rop_cron_hooks    = array( self::CRON_NAMESPACE, self::CRON_NAMESPACE_ONCE );
		$current_cron_list = _get_cron_array();
		$rop_cron_key      = self::get_schedule_key( $rop_cron_hooks );

		if ( ! empty( $rop_cron_key ) ) {
			$wpdb->query( 'START TRANSACTION' );
			foreach ( $rop_cron_key as $rop_active_cron ) {
				$cron_time      = (int) $rop_active_cron['time'];
				$cron_key       = $rop_active_cron['key'];
				$cron_namespace = $rop_active_cron['namespace'];

				unset( $current_cron_list[ $cron_time ][ $cron_namespace ][ $cron_key ] );
				if ( empty( $current_cron_list[ $cron_time ][ $cron_namespace ] ) ) {
					unset( $current_cron_list[ $cron_time ][ $cron_namespace ] );
				}

				if ( empty( $current_cron_list[ $cron_time ] ) ) {
					unset( $current_cron_list[ $cron_time ] );
				}
			}
			uksort( $current_cron_list, 'strnatcasecmp' );
			_set_cron_array( $current_cron_list );

			wp_cache_delete( 'alloptions', 'options' );

			$wpdb->query( 'COMMIT' );
		}

		$this->fresh_start();
	}
}
