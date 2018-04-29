<?php
/**
 * ROP Test scheduler actions for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test scheduler related actions. class.
 */
class Test_RopScheduler extends WP_UnitTestCase {
	/**
	 * Init test accounts.
	 */
	public static function setUpBeforeClass() {
		Rop_InitAccounts::init();
	}

	/**
	 * Testing the scheduler model.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @covers  Rop_Scheduler_Model
	 */
	public function test_scheduler() {
		$account_id        = Rop_InitAccounts::get_account_id();
		$global_settings   = new Rop_Global_Settings();
		$scheduler         = new Rop_Scheduler_Model();
		$schedule_defaults = $global_settings->get_default_schedule();

		$this->assertEquals( $scheduler->get_schedule( $account_id ), $schedule_defaults );
		$this->assertArrayHasKey( 'type', $schedule_defaults );
		$this->assertEquals( 'recurring', $schedule_defaults['type'] );

		$new_data         = $schedule_defaults;
		$new_data['type'] = 'fixed';
		$scheduler->add_update_schedule( $account_id, $new_data );
		$this->assertNotEquals( $scheduler->get_schedule( $account_id ), $new_data );
		$schedule_fixed = $scheduler->get_schedule( $account_id );
		$this->assertNotEmpty( $schedule_fixed['interval_f']['week_days'] );
		$this->assertEquals( 7, count( $schedule_fixed['interval_f']['week_days'] ) );
		$this->assertNotEmpty( $schedule_fixed['interval_f']['time'] );

		/**
		 * Check if the events limit per account is used.
		 */
		$events = $scheduler->get_upcoming_events( $account_id );
		$this->assertEquals( Rop_Scheduler_Model::EVENTS_PER_ACCOUNT, count( $events ) );
		$events_clear = reset( $events );
		/**
		 * Check after we process an event, we still have the timeline full.
		 */
		$scheduler->remove_timestamp( $events_clear, $account_id );
		$events = $scheduler->get_upcoming_events( $account_id );
		$this->assertEquals( Rop_Scheduler_Model::EVENTS_PER_ACCOUNT, count( $events ), 'We should always have the required events limit for every account.' );
		$scheduler->remove_schedule( $account_id );
		$this->assertEquals( $scheduler->get_schedule( $account_id ), $schedule_defaults );
	}

}
