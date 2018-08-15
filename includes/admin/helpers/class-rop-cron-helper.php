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
	 * Cron action name for sharing specific post(s).
	 */
	const CRON_NAMESPACE_PUBLISH_NOW = 'rop_cron_job_publish_now';

	/**
	 * Defines new schedules for cron use.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $schedules The schedules array.
	 *
	 * @return mixed
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
	 * @since   8.0.0rc
	 * @access  public
	 * @return  array Current status.
	 */
	public function manage_cron( $request ) {
		if ( $request['action'] == 'start' ) {
			$this->create_cron( true );
		} elseif ( $request['action'] == 'stop' ) {
			$this->remove_cron();
		} elseif ( $request['action'] == 'publish-now' ) {
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
			wp_schedule_single_event( time() + 15, self::CRON_NAMESPACE_PUBLISH_NOW );
		}
		return true;
	}


	/**
	 * Utility method to start a cron.
	 *
	 * @since   8.0.0rc
	 * @access  public
	 * @return bool
	 */
	public function create_cron( $first = true ) {
		if ( ! wp_next_scheduled( self::CRON_NAMESPACE ) ) {

			if ( $first ) {
				$this->fresh_start();
				$settings = new Rop_Global_Settings();
				$settings->update_start_time();
				wp_schedule_single_event( time() + 30, self::CRON_NAMESPACE );
			}
			wp_schedule_event( time(), '5min', self::CRON_NAMESPACE );
		}

		return true;
	}

	/**
	 * Utility method to stop a cron.
	 *
	 * @since   8.0.0rc
	 * @access  public
	 * @return bool
	 */
	public function remove_cron() {
		$timestamp = wp_next_scheduled( self::CRON_NAMESPACE );
		if ( is_int( $timestamp ) ) {
			wp_clear_scheduled_hook( self::CRON_NAMESPACE );
		}
		$this->fresh_start();
		return false;
	}

	/**
	 * Get cron status.
	 *
	 * @return bool Cron status.
	 */
	public function get_status() {

		return is_int( wp_next_scheduled( self::CRON_NAMESPACE ) );
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
		$replacements = [
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
		];
		$momentFormat = strtr( $format, $replacements );

		return $momentFormat;
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
