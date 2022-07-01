<?php
/**
 * ROP Test Accounts actions for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test accounts related logic.
 */
class Test_RopAccounts extends WP_UnitTestCase {
	public static function setUpBeforeClass() {
		Rop_InitAccounts::init();
	}

	/**
	 * Testing services avilability.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function test_services_model() {

		$services_model = new Rop_Services_Model();
		$this->assertNotEmpty( $services_model->get_active_accounts(), 'By default the account should be active.' );
		$this->assertNonEmptyMultidimensionalArray( $services_model->get_active_accounts() );

		$this->assertNotEmpty( $services_model->get_authenticated_services(), 'By default the services should be active.' );
		$this->assertNonEmptyMultidimensionalArray( $services_model->get_authenticated_services() );

		$accounts     = $services_model->get_active_accounts();
		$account_test = reset( $accounts );
		$account_key  = key( $accounts );
		$this->assertArrayHasKey( 'active', $account_test, 'Active key is not present' );
		$this->assertTrue( $account_test['active'], 'Active key is not set active into an active account.' );
		$this->assertArrayHasKey( 'id', $account_test, 'Id key is not set active into an active account.' );
		$this->assertNotEmpty( $account_test['id'], 'Id key is empty.' );

		$services_model->delete_active_accounts( $account_key );
		$this->assertEmpty( $services_model->get_active_accounts(), 'Delete active account not working.' );

		$services_model->add_active_accounts( $account_key );
		$this->assertEquals( 1, count( $services_model->get_active_accounts() ), 'Add active account not working.' );

	}

}
