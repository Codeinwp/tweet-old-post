<?php
/**
 * ROP Test queue actions for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test Queue related actions. class.
 */
class Test_RopQueue extends WP_UnitTestCase {

	/**
	 * Init test accounts.
	 */
	public static function setUpBeforeClass() {
		Rop_InitAccounts::init();
		Rop_InitAccounts::generatePosts( 30, 'post', '-2 month' );
	}

	/**
	 * Test skip action.
	 */
	public function test_skip() {
		$account_id          = Rop_InitAccounts::get_account_id();
		$queue               = new Rop_Queue_Model();
		$starting_queue      = $queue->build_queue();
		$account_build_queue = reset( $starting_queue );
		$rand_index          = rand( 0, ( count( $account_build_queue ) - 1 ) );

		$rand_event = $account_build_queue[ $rand_index ];

		$rand_post = reset( $rand_event['posts'] );
		$queue->skip_post( $rand_post, $account_id );

		$starting_queue      = $queue->build_queue();
		$account_build_queue = reset( $starting_queue );
		$new_post            = reset( $account_build_queue[ $rand_index ]['posts'] );
		$this->assertNotEquals( $rand_post, $new_post );
	}

	/**
	 * Test ban action.
	 */
	public function test_ban() {
		$account_id          = Rop_InitAccounts::get_account_id();
		$queue               = new Rop_Queue_Model();
		$starting_queue      = $queue->build_queue();
		$account_build_queue = reset( $starting_queue );
		$rand_index          = rand( 0, ( count( $account_build_queue ) - 1 ) );

		$rand_event = $account_build_queue[ $rand_index ];

		$rand_post = reset( $rand_event['posts'] );
		$queue->ban_post( $rand_post, $account_id );

		$starting_queue      = $queue->build_queue();
		$account_build_queue = reset( $starting_queue );
		$new_post            = reset( $account_build_queue[ $rand_index ]['posts'] );

		$this->assertNotEquals( $rand_post, $new_post );
	}

	/**
	 * Test ban action.
	 */
	public function test_remove() {
		$account_id          = Rop_InitAccounts::get_account_id();
		$queue               = new Rop_Queue_Model();
		$starting_queue      = $queue->build_queue();
		$account_build_queue = reset( $starting_queue );
		$rand_index          = rand( 0, ( count( $account_build_queue ) - 1 ) );

		$rand_event = $account_build_queue[ $rand_index ];

		$queue->remove_from_queue( $rand_event['time'], $account_id );

		$starting_queue      = $queue->build_queue();
		$account_build_queue = reset( $starting_queue );
		$new_event           = $account_build_queue[ $rand_index ];

		$this->assertNotEquals( $rand_event, $new_event );
	}

	/**
	 * Test change of no_of posts, the queue should change also.
	 */
	public function test_queue_no_posts_edit() {

		$queue               = new Rop_Queue_Model();
		$settings            = new Rop_Settings_Model();
		$starting_queue      = $queue->build_queue();
		$account_build_queue = reset( $starting_queue );
		$rand_index          = rand( 0, ( count( $account_build_queue ) - 1 ) );
		$rand_event          = $account_build_queue[ $rand_index ];

		$this->assertArrayHasKey( 'time', $rand_event, 'Time component of the event is missing' );
		$this->assertArrayHasKey( 'posts', $rand_event, 'Posts component of the event is missing' );
		$this->assertEquals( $settings->get_number_of_posts(), count( $rand_event['posts'] ), 'Posts component does not have the correct size.' );

		$settings_data                    = $settings->get_settings();
		$settings_data['number_of_posts'] = 2;
		$settings->save_settings( $settings_data );

		$starting_queue      = $queue->build_queue();
		$account_build_queue = reset( $starting_queue );
		$this->assertEquals( $settings->get_number_of_posts(), count( $account_build_queue[ $rand_index ]['posts'] ), 'We need to have number of posts to share per timestamp times number of events per timeline for each account.' );
		$this->assertEquals( $rand_event['time'], $account_build_queue[ $rand_index ]['time'], 'Rand time has changed after queue post change..' );
	}

	/**
	 * Testing the queue model.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @covers Rop_Queue_Model
	 */
	public function test_queue_init() {

		$queue    = new Rop_Queue_Model();
		$settings = new Rop_Settings_Model();

		$starting_queue = $queue->get_queue();
		$this->assertTrue( ! empty( $starting_queue ) );
		$account_q = reset( $starting_queue );
		$this->assertEquals( ( $settings->get_number_of_posts() * Rop_Scheduler_Model::EVENTS_PER_ACCOUNT ), count( $account_q ), 'We need to have number of posts to share per timestamp times number of events per timeline for each account.' );

		$builded_queue       = $queue->build_queue();
		$builded_queue2      = $queue->build_queue();
		$account_build_queue = reset( $builded_queue );
		$this->assertEquals( $builded_queue, $builded_queue2, "Queue is not consistent" );
		$this->assertEquals( ( $settings->get_number_of_posts() * Rop_Scheduler_Model::EVENTS_PER_ACCOUNT ), count( $account_build_queue ), "Queue is not consistent regardless of the no of events per queue." );
		$ordered_queue = $queue->get_ordered_queue();
		$this->assertEquals( 0, count( $ordered_queue ), 'Ordered queue should be empty if the start sharing is not active' );
	}

}
