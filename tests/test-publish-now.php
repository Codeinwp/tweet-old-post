<?php
/**
 * ROP publish now (instant sharing) queue tests.
 *
 * Covers the queue defects from issue #1102: stale entries expiring instead of
 * sharing, the drain batch size, orphaned entries, and edits of already
 * published posts re-queueing shares — plus regression guards for the sharing
 * paths that must keep working.
 *
 * @package     ROP
 * @subpackage  Tests
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test publish now queue behavior.
 */
class Test_RopPublishNow extends WP_UnitTestCase {

	/**
	 * Init test accounts.
	 */
	public static function setUpBeforeClass(): void {
		Rop_InitAccounts::init();
	}

	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( 1 );
	}

	public function tearDown(): void {
		unset( $_POST['publish_now'], $_POST['publish_now_accounts'], $_POST['rop_publish_now_nonce'] );
		unset( $GLOBALS['post'] );
		parent::tearDown();
	}

	/**
	 * Create a published post queued for instant sharing.
	 *
	 * @param int  $age_seconds  How long ago the entry was queued.
	 * @param bool $with_history Whether to write the sharing history entry.
	 *
	 * @return int The post ID.
	 */
	private function queue_post( $age_seconds = 0, $with_history = true ) {
		$account_id = Rop_InitAccounts::get_account_id();
		$post_id    = self::factory()->post->create( array( 'post_status' => 'publish' ) );

		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_status', 'queued' );
		update_post_meta( $post_id, 'rop_publish_now_accounts', array( $account_id => '' ) );

		if ( $with_history ) {
			update_post_meta(
				$post_id,
				'rop_publish_now_history',
				array(
					array(
						'account'   => $account_id,
						'service'   => 'twitter',
						'timestamp' => time() - $age_seconds,
						'status'    => 'queued',
					),
				)
			);
		}

		return $post_id;
	}

	/**
	 * Create an already published post and clear the throttling transient, so
	 * the next save runs `maybe_publish_now` for real.
	 *
	 * Publishing sets `rop_maybe_publish_now_<id>` for a minute; a later edit
	 * of an archive post — the scenario in #1102 — never sees it.
	 *
	 * @return int The post ID.
	 */
	private function published_post_ready_for_edit() {
		$post_id = self::factory()->post->create( array( 'post_status' => 'publish' ) );
		delete_transient( 'rop_maybe_publish_now_' . $post_id );

		return $post_id;
	}

	/**
	 * A freshly queued post ends up in the publish now queue.
	 */
	public function test_fresh_entry_is_queued() {
		$account_id = Rop_InitAccounts::get_account_id();
		$post_id    = $this->queue_post();

		$queue = ( new Rop_Queue_Model() )->build_queue_publish_now();

		$this->assertArrayHasKey( $account_id, $queue );
		$event = reset( $queue[ $account_id ] );
		$this->assertEquals( array( $post_id ), $event['post'] );
	}

	/**
	 * Entries older than the expiration threshold are dropped, marked expired
	 * and no longer reported as queued.
	 */
	public function test_stale_entry_expires_instead_of_sharing() {
		$post_id = $this->queue_post( 2 * DAY_IN_SECONDS );

		$queue = ( new Rop_Queue_Model() )->build_queue_publish_now();

		$this->assertEmpty( $queue, 'Stale entries must not be shared.' );
		$this->assertNotEquals( 'queued', get_post_meta( $post_id, 'rop_publish_now_status', true ) );

		$history = get_post_meta( $post_id, 'rop_publish_now_history', true );
		$this->assertEquals( 'expired', $history[0]['status'] );
	}

	/**
	 * The core symptom of #1102: a backlog of stale entries must not hold up
	 * the post that was just published.
	 */
	public function test_backlog_does_not_delay_fresh_post() {
		$account_id = Rop_InitAccounts::get_account_id();

		for ( $i = 0; $i < 3; $i ++ ) {
			$this->queue_post( 60 * DAY_IN_SECONDS );
		}
		$fresh_id = $this->queue_post();

		$queue = ( new Rop_Queue_Model() )->build_queue_publish_now();

		$this->assertCount( 1, $queue[ $account_id ], 'Only the fresh post may be shared.' );
		$event = reset( $queue[ $account_id ] );
		$this->assertEquals( array( $fresh_id ), $event['post'] );
	}

	/**
	 * The expiration threshold is filterable.
	 */
	public function test_expiration_is_filterable() {
		$this->queue_post( HOUR_IN_SECONDS );

		add_filter(
			'rop_publish_now_expiration',
			function () {
				return 10 * MINUTE_IN_SECONDS;
			}
		);

		$queue = ( new Rop_Queue_Model() )->build_queue_publish_now();

		$this->assertEmpty( $queue );
	}

	/**
	 * Legacy entries without a sharing history are stale by definition.
	 */
	public function test_entry_without_history_expires() {
		$post_id = $this->queue_post( 0, false );

		$queue = ( new Rop_Queue_Model() )->build_queue_publish_now();

		$this->assertEmpty( $queue );
		$this->assertNotEquals( 'queued', get_post_meta( $post_id, 'rop_publish_now_status', true ) );
	}

	/**
	 * Entries without accounts are skipped AND their status is cleared, so
	 * they do not linger as queued forever.
	 */
	public function test_orphan_entry_clears_status() {
		$post_id = self::factory()->post->create( array( 'post_status' => 'publish' ) );
		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_status', 'queued' );

		$queue = ( new Rop_Queue_Model() )->build_queue_publish_now();

		$this->assertEmpty( $queue );
		$this->assertNotEquals( 'queued', get_post_meta( $post_id, 'rop_publish_now_status', true ) );
	}

	/**
	 * An orphaned entry must also retire its history rows. The editor treats a
	 * `queued` history row as an active share regardless of the top level
	 * status, so leaving one behind spins the sidebar forever.
	 */
	public function test_orphan_entry_retires_history() {
		$post_id = $this->queue_post();
		delete_post_meta( $post_id, 'rop_publish_now_accounts' );

		( new Rop_Queue_Model() )->build_queue_publish_now();

		$history = get_post_meta( $post_id, 'rop_publish_now_history', true );
		$statuses = wp_list_pluck( is_array( $history ) ? $history : array(), 'status' );
		$this->assertNotContains( 'queued', $statuses );
	}

	/**
	 * A full batch schedules another drain, so a fresh post sitting behind a
	 * backlog bigger than one batch is not stranded.
	 */
	public function test_full_batch_schedules_another_pass() {
		add_filter( 'rop_publish_now_batch_size', function () {
			return 2;
		} );

		for ( $i = 0; $i < 3; $i ++ ) {
			$this->queue_post();
		}

		wp_clear_scheduled_hook( 'rop_cron_job_publish_now' );
		( new Rop_Queue_Model() )->build_queue_publish_now();

		$this->assertNotFalse(
			wp_next_scheduled( 'rop_cron_job_publish_now' ),
			'A full batch must schedule a follow-up drain.'
		);
	}

	/**
	 * A partial batch means the queue is drained, so nothing is rescheduled.
	 */
	public function test_partial_batch_does_not_reschedule() {
		add_filter( 'rop_publish_now_batch_size', function () {
			return 10;
		} );

		$this->queue_post();

		wp_clear_scheduled_hook( 'rop_cron_job_publish_now' );
		( new Rop_Queue_Model() )->build_queue_publish_now();

		$this->assertFalse( wp_next_scheduled( 'rop_cron_job_publish_now' ) );
	}

	/**
	 * The drain batch is not capped by the site posts_per_page option
	 * (WP_Query ignores the numberposts argument the query used to pass).
	 */
	public function test_batch_size_exceeds_posts_per_page_option() {
		$account_id     = Rop_InitAccounts::get_account_id();
		$posts_per_page = (int) get_option( 'posts_per_page' );
		$count          = $posts_per_page + 5;

		for ( $i = 0; $i < $count; $i ++ ) {
			$this->queue_post();
		}

		$queue = ( new Rop_Queue_Model() )->build_queue_publish_now();

		$this->assertCount( $count, $queue[ $account_id ] );
	}

	/**
	 * Editing an already-published post must not queue a share from leftover
	 * meta.
	 */
	public function test_editing_published_post_does_not_requeue() {
		$account_id = Rop_InitAccounts::get_account_id();
		$post_id    = $this->published_post_ready_for_edit();

		// Leftover meta from a share that never drained.
		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_accounts', array( $account_id => '' ) );

		wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => 'Routine edit',
			)
		);

		$this->assertNotEquals( 'queued', get_post_meta( $post_id, 'rop_publish_now_status', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'rop_publish_now_history', true ) );
	}

	/**
	 * Publishing a draft with instant sharing enabled still queues the share.
	 */
	public function test_publishing_draft_queues_share() {
		$account_id = Rop_InitAccounts::get_account_id();
		$post_id    = self::factory()->post->create( array( 'post_status' => 'draft' ) );

		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_accounts', array( $account_id => '' ) );

		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			)
		);

		$this->assertEquals( 'queued', get_post_meta( $post_id, 'rop_publish_now_status', true ) );

		$history = get_post_meta( $post_id, 'rop_publish_now_history', true );
		$this->assertEquals( 'queued', $history[0]['status'] );
		$this->assertEquals( $account_id, $history[0]['account'] );
	}

	/**
	 * A scheduled post going live still queues the share.
	 */
	public function test_scheduled_post_going_live_queues_share() {
		$account_id = Rop_InitAccounts::get_account_id();
		$post_id    = self::factory()->post->create(
			array(
				'post_status' => 'future',
				'post_date'   => gmdate( 'Y-m-d H:i:s', time() + HOUR_IN_SECONDS ),
			)
		);

		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_accounts', array( $account_id => '' ) );

		wp_publish_post( $post_id );

		$this->assertEquals( 'queued', get_post_meta( $post_id, 'rop_publish_now_status', true ) );
	}

	/**
	 * A cron-published scheduled post still queues the share when a racing
	 * request has put the pre-publish copy back in the object cache.
	 */
	public function test_scheduled_post_shares_with_stale_status_cache(): void {
		$account_id = Rop_InitAccounts::get_account_id();
		$post_id    = self::factory()->post->create(
			array(
				'post_status' => 'future',
				'post_date'   => gmdate( 'Y-m-d H:i:s', time() + HOUR_IN_SECONDS ),
			)
		);

		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_accounts', array( $account_id => '' ) );

		$cache_stale_copy = static function ( string $new_status, string $old_status, WP_Post $post ): void {
			$stale              = clone $post;
			$stale->post_status = $old_status;
			wp_cache_set( $post->ID, $stale, 'posts' );
		};

		wp_set_current_user( 0 );
		add_filter( 'wp_doing_cron', '__return_true' );
		add_action( 'transition_post_status', $cache_stale_copy, 5, 3 );

		wp_publish_post( $post_id );

		remove_action( 'transition_post_status', $cache_stale_copy, 5 );
		remove_filter( 'wp_doing_cron', '__return_true' );
		clean_post_cache( $post_id );

		$this->assertSame( 'queued', get_post_meta( $post_id, 'rop_publish_now_status', true ) );
	}

	/**
	 * The Classic Editor metabox is an explicit opt-in, so submitting it on an
	 * already-published post must still share.
	 */
	public function test_explicit_metabox_submit_shares_published_post() {
		$account_id = Rop_InitAccounts::get_account_id();
		$post_id    = $this->published_post_ready_for_edit();

		$_POST['publish_now']          = '1';
		$_POST['publish_now_accounts'] = array( $account_id );

		wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => 'Deliberate re-share',
			)
		);

		$this->assertEquals( 'queued', get_post_meta( $post_id, 'rop_publish_now_status', true ) );
	}

	/**
	 * The Classic metabox stays checked while a share is pending, so ordinary
	 * saves of such a post keep submitting `publish_now`. That must not refresh
	 * the queue timestamp, or a long-stalled entry would never expire.
	 */
	public function test_saving_a_pending_share_does_not_refresh_its_timestamp() {
		$post_id = $this->queue_post( 5 * DAY_IN_SECONDS );
		delete_transient( 'rop_maybe_publish_now_' . $post_id );

		$history  = get_post_meta( $post_id, 'rop_publish_now_history', true );
		$queued_at = $history[0]['timestamp'];

		$_POST['publish_now']          = '1';
		$_POST['publish_now_accounts'] = array( Rop_InitAccounts::get_account_id() );

		wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => 'Routine edit while queued',
			)
		);

		$history = get_post_meta( $post_id, 'rop_publish_now_history', true );
		$this->assertEquals( $queued_at, $history[0]['timestamp'], 'The queue timestamp must not be refreshed.' );

		// And it must therefore still expire rather than be shared.
		$this->assertEmpty( ( new Rop_Queue_Model() )->build_queue_publish_now() );
	}

	/**
	 * The Block Editor re-share button posts to the REST share endpoint, which
	 * fires `rop_publish_now_instant_share`. That path must keep sharing
	 * already-published posts.
	 */
	public function test_reshare_action_shares_published_post() {
		$account_id = Rop_InitAccounts::get_account_id();
		$post_id    = $this->published_post_ready_for_edit();

		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_accounts', array( $account_id => '' ) );

		do_action( 'rop_publish_now_instant_share', $post_id, true );

		$this->assertEquals( 'queued', get_post_meta( $post_id, 'rop_publish_now_status', true ) );
	}

	/**
	 * The REST callback must not let an Editor or Contributor queue a post that
	 * they cannot edit, even though the route itself is available to editors.
	 */
	public function test_reshare_rest_callback_rejects_post_without_edit_access() {
		$owner_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$user_id  = self::factory()->user->create( array( 'role' => 'contributor' ) );
		$post_id  = self::factory()->post->create(
			array(
				'post_author' => $owner_id,
				'post_status' => 'publish',
			)
		);
		wp_set_current_user( $user_id );

		$request = new WP_REST_Request( 'POST', '/tweet-old-post/v8/share/' . $post_id );
		$request->set_param( 'id', $post_id );
		$request->set_body(
			wp_json_encode(
				array(
					'rop_publish_now_accounts' => array( Rop_InitAccounts::get_account_id() => '' ),
				)
			)
		);
		$publish_now_before = get_post_meta( $post_id, 'rop_publish_now', true );
		$accounts_before    = get_post_meta( $post_id, 'rop_publish_now_accounts', true );

		$response = ( new Rop_Rest_Api() )->share( $request );

		$this->assertWPError( $response );
		$this->assertSame( 403, $response->get_error_data()['status'] );
		$this->assertSame( $publish_now_before, get_post_meta( $post_id, 'rop_publish_now', true ) );
		$this->assertSame( $accounts_before, get_post_meta( $post_id, 'rop_publish_now_accounts', true ) );
	}

	/**
	 * The metabox checkbox is not pre-checked when editing a published post
	 * that has no share pending — this is what re-queued the archive content.
	 */
	public function test_metabox_not_prechecked_on_published_post() {
		$GLOBALS['post'] = get_post( self::factory()->post->create( array( 'post_status' => 'publish' ) ) );

		$attributes = ( new Rop_Admin() )->publish_now_attributes( array() );

		$this->assertFalse( $attributes['action'] );
	}

	/**
	 * A published post whose share is still pending keeps the box checked.
	 */
	public function test_metabox_prechecked_while_share_pending() {
		$post_id = self::factory()->post->create( array( 'post_status' => 'publish' ) );
		update_post_meta( $post_id, 'rop_publish_now', 'yes' );

		$GLOBALS['post'] = get_post( $post_id );

		$attributes = ( new Rop_Admin() )->publish_now_attributes( array() );

		$this->assertTrue( $attributes['action'] );
	}

	/**
	 * Drafts keep the "instant share by default" behaviour.
	 */
	public function test_metabox_prechecked_on_draft() {
		$GLOBALS['post'] = get_post( self::factory()->post->create( array( 'post_status' => 'draft' ) ) );

		$attributes = ( new Rop_Admin() )->publish_now_attributes( array() );

		$this->assertTrue( $attributes['action'] );
	}

	/**
	 * Saving a draft from the Classic metabox with the share box unticked keeps the
	 * default account selection for when the box is ticked later (#1134).
	 */
	public function test_draft_saved_without_share_keeps_default_accounts() {
		$account_id = Rop_InitAccounts::get_account_id();
		$admin      = new Rop_Admin();
		$admin->register_meta();
		$post_id = self::factory()->post->create( array( 'post_status' => 'draft' ) );

		$_POST['rop_publish_now_nonce'] = wp_create_nonce( 'rop_publish_now_nonce' );
		$admin->maybe_publish_now( $post_id );

		$GLOBALS['post'] = get_post( $post_id );
		$attributes      = $admin->publish_now_attributes( array() );

		$this->assertArrayHasKey( $account_id, $attributes['page_active_accounts'] );
	}

	/**
	 * A selection saved earlier survives a draft save with the share box unticked.
	 */
	public function test_draft_saved_without_share_keeps_saved_selection() {
		$admin   = new Rop_Admin();
		$post_id = self::factory()->post->create( array( 'post_status' => 'draft' ) );
		update_post_meta( $post_id, 'rop_publish_now_accounts', array( 'facebook_1_1' => 'Custom message' ) );

		$_POST['rop_publish_now_nonce'] = wp_create_nonce( 'rop_publish_now_nonce' );
		$admin->maybe_publish_now( $post_id );

		$GLOBALS['post'] = get_post( $post_id );
		$attributes      = $admin->publish_now_attributes( array() );

		$this->assertSame( array( 'facebook_1_1' => 'Custom message' ), $attributes['page_active_accounts'] );
	}

	/**
	 * Register the plugin meta with exactly one share variation sanitizer.
	 *
	 * The test case unregisters meta keys between tests while restoring hooks,
	 * so a sanitizer can be left over or missing; neither must hide the result.
	 */
	private function register_share_variations_meta() {
		remove_all_filters( 'sanitize_post_meta_rop_custom_messages_group' );
		unregister_meta_key( 'post', 'rop_custom_messages_group' );
		( new Rop_Admin() )->register_meta();
	}

	/**
	 * Store share variations through the registered meta sanitizer.
	 *
	 * @param mixed $messages The raw meta value.
	 *
	 * @return mixed The stored meta value.
	 */
	private function store_share_variations( $messages ) {
		$this->register_share_variations_meta();
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, 'rop_custom_messages_group', $messages );

		return get_post_meta( $post_id, 'rop_custom_messages_group', true );
	}

	/**
	 * Build the shared text for a post whose only share variation is given.
	 *
	 * @param string $description The variation text.
	 *
	 * @return string The shared text.
	 */
	private function share_variation( $description ) {
		$settings      = new Rop_Settings_Model();
		$settings_data = $settings->get_settings();
		$settings_data['custom_messages'] = true;
		$settings->save_settings( $settings_data );
		$this->register_share_variations_meta();
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, 'rop_custom_messages_group', array( array( 'rop_custom_description' => $description ) ) );

		$helper = new Rop_Post_Format_Helper();
		$helper->set_post_format( Rop_InitAccounts::get_account_id() );
		$content = $helper->build_content( $post_id );

		return $content['display_content'];
	}

	/**
	 * Share variations are social-network text and must be normalized as text
	 * when they cross the post-meta boundary.
	 */
	public function test_custom_share_messages_are_stored_as_plain_text() {
		$this->assertSame(
			array(
				array( 'rop_custom_description' => 'A useful update' ),
			),
			$this->store_share_variations(
				array(
					array( 'rop_custom_description' => "A <strong>useful</strong> update\n<script>globalThis.rop = 1;</script>" ),
				)
			)
		);
	}

	/**
	 * A tag split around another tag is still removed.
	 */
	public function test_custom_share_messages_strip_tag_rebuilt_from_fragments() {
		$this->assertSame(
			array(
				array( 'rop_custom_description' => 'Before  after' ),
			),
			$this->store_share_variations(
				array(
					array( 'rop_custom_description' => 'Before <<b>script> after' ),
				)
			)
		);
	}

	/**
	 * Entries that are not variations are dropped and the list is re-indexed.
	 */
	public function test_custom_share_messages_drop_entries_without_text() {
		$this->assertSame(
			array(
				array( 'rop_custom_description' => 'ok' ),
			),
			$this->store_share_variations(
				array(
					'junk',
					array( 'other' => 'x' ),
					array( 'rop_custom_description' => array( 'nested' ) ),
					array( 'rop_custom_description' => 'ok' ),
				)
			)
		);
	}

	/**
	 * A value that is not a list stores an empty list.
	 */
	public function test_custom_share_messages_non_list_is_stored_empty() {
		$this->assertSame( array(), $this->store_share_variations( 'junk' ) );
	}

	/**
	 * Text, ampersands, entities typed as text and percent-encoded sequences are stored as typed.
	 */
	public function test_custom_share_messages_keep_typed_text() {
		$typed = "We <3 our customers & friends\n50%2F off at https://example.com/caf%C3%A9\nWrite &lt;b&gt; here, \"quoted\" 'text'";

		$this->assertSame(
			array( array( 'rop_custom_description' => $typed ) ),
			$this->store_share_variations( array( array( 'rop_custom_description' => $typed ) ) )
		);
	}

	/**
	 * The block editor reads back the variation it saved, as typed.
	 */
	public function test_custom_share_message_round_trips_through_rest_api() {
		$this->register_share_variations_meta();
		$post_id = self::factory()->post->create();
		$typed   = 'We <3 our customers & friends';

		$save = new WP_REST_Request( 'POST', '/wp/v2/posts/' . $post_id );
		$save->set_body_params( array( 'meta' => array( 'rop_custom_messages_group' => array( array( 'rop_custom_description' => $typed ) ) ) ) );
		$this->assertSame( 200, rest_do_request( $save )->get_status() );

		$read = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $post_id );
		$read->set_param( 'context', 'edit' );
		$data = rest_do_request( $read )->get_data();

		$this->assertSame( array( array( 'rop_custom_description' => $typed ) ), $data['meta']['rop_custom_messages_group'] );
	}

	/**
	 * A variation is shared without its tags, and a lone "<" and an ampersand are shared as typed.
	 */
	public function test_custom_share_message_keeps_less_than_sign_when_shared() {
		$this->assertSame( 'We <3 our customers & friends', $this->share_variation( '<em>We</em> <3 our customers & friends' ) );
	}

	/**
	 * A variation that contains an entity typed as text is shared unchanged.
	 */
	public function test_custom_share_message_keeps_typed_entity_when_shared() {
		$this->assertStringContainsString( 'Write &lt;b&gt; here', $this->share_variation( 'Write &lt;b&gt; here' ) );
	}
}
