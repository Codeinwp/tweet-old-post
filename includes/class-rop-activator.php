<?php

/**
 * Fired during plugin activation
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      8.0.0
 * @package    Rop
 * @subpackage Rop/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public static function activate() {
		add_filter( 'cron_schedules', array( 'Rop_Activator', 'rop_cron_schedules' ) );

		if ( ! wp_next_scheduled( 'rop_cron_job' ) ) {
			wp_schedule_event( time(), '5min', 'rop_cron_job' );
		}
	}

	/**
	 * Defines new schedules for cron use.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $schedules The schedules array.
	 * @return mixed
	 */
	public static function rop_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['5min'] ) ) {
			$schedules['5min'] = array(
				'interval' => 5 * 60,
				'display' => __( 'Once every 5 minutes' ),
			);
		}
		if ( ! isset( $schedules['30min'] ) ) {
			$schedules['30min'] = array(
				'interval' => 30 * 60,
				'display' => __( 'Once every 30 minutes' ),
			);
		}
		return $schedules;
	}

}
