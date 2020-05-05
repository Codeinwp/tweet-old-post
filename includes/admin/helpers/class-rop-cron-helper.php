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
	 *
	 */
	public static function rop_cron_schedules( $schedules ) {
		$schedules['5min'] = array(
			'interval' => 1 * 60,
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
	 * Utility method to create a single event for publishing specific post(s).
	 *
	 * @access  private
	 * @return bool
	 */
	private function publish_now() {
		if ( ! wp_next_scheduled( self::CRON_NAMESPACE_PUBLISH_NOW ) ) {
			wp_schedule_single_event( current_time( 'timestamp' ) + 15, self::CRON_NAMESPACE_PUBLISH_NOW );
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
	 *
	 */
	public function create_cron( $first = true ) {
		if ( defined( 'ROP_CRON_ALTERNATIVE' ) && false === ROP_CRON_ALTERNATIVE && ! wp_next_scheduled( self::CRON_NAMESPACE ) ) {

			if ( $first ) {
				$this->fresh_start();
				$settings = new Rop_Global_Settings();
				$settings->update_start_time();
				wp_schedule_single_event( time() + 30, self::CRON_NAMESPACE_ONCE );
			}
			wp_schedule_event( time(), '5min', self::CRON_NAMESPACE );

		} elseif ( defined( 'ROP_CRON_ALTERNATIVE' ) && true === ROP_CRON_ALTERNATIVE ) {
			if ( $first ) {
				$this->fresh_start();
				$settings = new Rop_Global_Settings();
				$settings->update_start_time();
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
	 *
	 */
	public function remove_cron( $request = array() ) {
		global $wpdb;
		// Mark sharing system as stopped
		$this->cron_status_global_change( false );

		$current_cron_list = _get_cron_array();
		$rop_cron_key      = self::get_schedule_key( array( self::CRON_NAMESPACE, self::CRON_NAMESPACE_ONCE ) );
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

		return false;
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
	 *
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
	 *
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
			return is_int( wp_next_scheduled( self::CRON_NAMESPACE ) );
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
	}
}
