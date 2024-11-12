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

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test logger related actions. class.
 */
class Test_RopSelector extends WP_UnitTestCase {

	/**
	 * Init test accounts.
	 */
	public static function setUpBeforeClass(): void {
		Rop_InitAccounts::init();
		Rop_InitAccounts::generatePosts( 20, 'post', '-2 month' );
	}

	/**
	 * Testing posts selector
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 */
	public function test_posts_selector() {

		$posts_selector = new Rop_Posts_Selector_Model();

		$this->assertGreaterThan( 0, sizeof( $posts_selector->select( Rop_InitAccounts::get_account_id() ) ), 'Empty results from post selector.' );

		$settings                         = new Rop_Settings_Model();
		$new_settings                     = $settings->get_settings();
		$new_settings['minimum_post_age'] = 1;
		$new_settings['maximum_post_age'] = 10;
		$settings->save_settings( $new_settings );
		$posts_selector2 = new Rop_Posts_Selector_Model();
		$this->assertEquals( 0, sizeof( $posts_selector2->select( Rop_InitAccounts::get_account_id() ) ), 'Date selection is not working.' );

	}

}
