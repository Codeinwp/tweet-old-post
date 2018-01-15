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
		$upgrade_helper = new Rop_Db_Upgrade();
		if ( $upgrade_helper->is_upgrade_required() ) {
			$upgrade_helper->do_upgrade();
		}

		add_filter( 'cron_schedules','rop_cron_schedules' );

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
	public function rop_cron_schedules( $schedules ) {
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
