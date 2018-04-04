<?php
/**
 * ROP Test Logger actions for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

/**
 * Test logger related actions. class.
 */
class Test_RopLogger extends WP_UnitTestCase {

	/**
	 * Testing Logger
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @covers Rop_Logger
	 */
	public function test_logger() {
		$log = new Rop_Logger();
		$this->assertEmpty( $log->get_logs(), 'By default logs should be empty' );

		$log->alert_error( 'test alert' );
		$this->assertNotEmpty( $log->get_logs(), 'Alert error is not saved' );
		$this->assertNonEmptyMultidimensionalArray( $log->get_logs() );

		$log->clear_user_logs();
		$this->assertEmpty( $log->get_logs(), 'Clear should clear the logs' );

		$log->alert_success( 'test success' );
		$logs       = $log->get_logs();
		$log_record = reset( $logs );
		$this->assertEquals( 1, count( $logs ), 'Alert should add a single log entry.' );
		$this->assertArrayHasKey( 'type', $log_record, 'Type key is not present in the logs' );
		$this->assertEquals( 'success', $log_record['type'], 'Type key is not present in the logs' );

		$log->clear_user_logs();
		$log->info( 'Test info' );
		$this->assertEmpty( $log->get_logs(), 'Info logs should not be present into production' );

	}

}
