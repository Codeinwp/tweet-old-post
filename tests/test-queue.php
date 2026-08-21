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
	public static function setUpBeforeClass(): void {
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

	/**
	 * Utility method to move a post publish date back in time.
	 *
	 * @param int    $post_id The post to age.
	 * @param string $shift The relative date shift, eg. `-400 days`.
	 */
	private function age_post( $post_id, $shift ) {
		$date = date( 'Y-m-d H:i:s', strtotime( $shift ) );
		wp_update_post(
			array(
				'ID'            => $post_id,
				'post_date'     => $date,
				'post_date_gmt' => get_gmt_from_date( $date ),
				'edit_date'     => true,
			)
		);
		clean_post_cache( $post_id );
	}

	/**
	 * Test the eligibility check used before a queued post is shared.
	 *
	 * @covers Rop_Posts_Selector_Model::is_post_eligible
	 * @covers Rop_Posts_Selector_Model::is_post_within_maximum_age
	 */
	public function test_post_eligibility() {
		$selector = new Rop_Posts_Selector_Model();
		$settings = new Rop_Settings_Model();

		$max_age = $settings->get_maximum_post_age();
		$this->assertNotEmpty( $max_age, 'This test needs a maximum post age to be set.' );

		$post_id = self::factory()->post->create(
			array(
				'post_status' => 'publish',
				'post_date'   => date( 'Y-m-d H:i:s', strtotime( '-' . ( $max_age - 5 ) . ' days' ) ),
			)
		);

		$this->assertTrue( $selector->is_post_within_maximum_age( $post_id ), 'A post younger than the maximum age should satisfy the age check.' );
		$this->assertTrue( $selector->is_post_eligible( $post_id ), 'A published post within the maximum age should be eligible.' );

		$this->age_post( $post_id, '-' . ( $max_age + 5 ) . ' days' );
		$this->assertFalse( $selector->is_post_within_maximum_age( $post_id ), 'A post older than the maximum age should fail the age check.' );
		$this->assertFalse( $selector->is_post_eligible( $post_id ), 'A post older than the maximum age should not be eligible.' );

		$draft_id = self::factory()->post->create(
			array(
				'post_status' => 'draft',
				'post_date'   => date( 'Y-m-d H:i:s', strtotime( '-2 month' ) ),
			)
		);
		$this->assertFalse( $selector->is_post_eligible( $draft_id ), 'An unpublished post should not be eligible.' );

		$deleted_id = self::factory()->post->create( array( 'post_status' => 'publish' ) );
		wp_delete_post( $deleted_id, true );
		$this->assertFalse( $selector->is_post_eligible( $deleted_id ), 'A deleted post should not be eligible.' );
	}

	/**
	 * Test that a post which was eligible when queued is not shared once it
	 * exceeds the maximum post age.
	 *
	 * @covers Rop_Admin::rop_cron_job
	 */
	public function test_queued_post_exceeding_max_age_is_not_shared() {
		$account_id = Rop_InitAccounts::get_account_id();
		$queue      = new Rop_Queue_Model();
		$scheduler  = new Rop_Scheduler_Model();
		$settings   = new Rop_Settings_Model();

		$settings_data                    = $settings->get_settings();
		$original_no_of_posts             = $settings_data['number_of_posts'];
		$settings_data['number_of_posts'] = 1;
		$settings->save_settings( $settings_data );

		$built = $queue->build_queue();
		$this->assertArrayHasKey( $account_id, $built, 'The account should have a queue.' );
		$this->assertNotEmpty( $built[ $account_id ][0]['posts'], 'The first queue event should hold a post.' );

		$queued_post = $built[ $account_id ][0]['posts'][0];

		$selector = new Rop_Posts_Selector_Model();
		$this->assertTrue( $selector->is_post_eligible( $queued_post ), 'The queued post should have been eligible when it was queued.' );

		$this->age_post( $queued_post, '-' . ( $settings->get_maximum_post_age() + 10 ) . ' days' );

		$this->assertContains( $queued_post, $queue->build_queue()[ $account_id ][0]['posts'], 'The queue should still hold the aged post.' );

		$events    = $scheduler->get_upcoming_events( $account_id );
		$events[0] = Rop_Scheduler_Model::get_current_time() - MINUTE_IN_SECONDS;
		$scheduler->update_timeline( $events, $account_id );

		$prepared = array();
		$recorder = function ( $post_id ) use ( &$prepared ) {
			$prepared[] = $post_id;
		};
		add_action( 'rop_before_prepare_post', $recorder );

		$checked = array();
		$spy     = function ( $eligible, $post_id ) use ( &$checked ) {
			$checked[ $post_id ] = $eligible;

			return $eligible;
		};
		add_filter( 'rop_is_post_eligible', $spy, 10, 2 );

		$admin = new Rop_Admin();
		$admin->rop_cron_job();

		remove_action( 'rop_before_prepare_post', $recorder );
		remove_filter( 'rop_is_post_eligible', $spy, 10 );

		$this->assertArrayHasKey( $queued_post, $checked, 'The sharing job should re-validate the queued post before sharing it.' );
		$this->assertFalse( $checked[ $queued_post ], 'The aged post should be reported as not eligible.' );
		$this->assertNotContains( $queued_post, $prepared, 'A post older than the maximum post age must not be shared.' );

		$refreshed     = ( new Rop_Queue_Model() )->build_queue();
		$queued_posts  = array();
		foreach ( $refreshed[ $account_id ] as $event ) {
			$queued_posts = array_merge( $queued_posts, $event['posts'] );
		}
		$this->assertNotContains( $queued_post, $queued_posts, 'The skipped post should be removed from the queue.' );

		$settings_data['number_of_posts'] = $original_no_of_posts;
		$settings->save_settings( $settings_data );
	}

}
