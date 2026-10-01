<?php
/**
 * ROP Test Facebook Graph API version for PHPUnit.
 *
 * Covers GitHub issue Codeinwp/tweet-old-post#1140: every Facebook publish
 * request must target the supported Graph API version, on both the
 * user-owned app (SDK) path and the Revive-hosted app (direct HTTP) path.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test Facebook Graph API version. class.
 */
class Test_RopFacebookGraphVersion extends WP_UnitTestCase {

	/**
	 * Expected Graph API version; bump with the plugin's.
	 */
	const GRAPH_API_VERSION = 'v26.0';

	/**
	 * URLs of the outbound requests captured during a share.
	 *
	 * @var list<string>
	 */
	private $captured_urls = array();

	/**
	 * Capture every outbound request and answer it with a successful response.
	 *
	 * @param false|array|WP_Error $preempt Whether to preempt the request.
	 * @param array                $args    Request arguments.
	 * @param string               $url     Request URL.
	 * @return array
	 */
	public function intercept_request( $preempt, array $args, string $url ): array {
		$this->captured_urls[] = $url;

		// Share log endpoint vs Graph API.
		$body = ROP_POST_LOGS_API === $url ? array( 'status' => 'success' ) : array( 'id' => '123_456' );

		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( $body ),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	/**
	 * Route shares through the Revive-hosted app with HTTP intercepted.
	 */
	public function set_up(): void {
		parent::set_up();

		$this->captured_urls = array();
		update_option( 'rop_facebook_via_rs_app', 1 );
		add_filter( 'rop_dont_work_on_staging', '__return_false' );
		add_filter( 'pre_http_request', array( $this, 'intercept_request' ), 10, 3 );
	}

	/**
	 * Undo the option and filters added in set_up().
	 */
	public function tear_down(): void {
		remove_filter( 'pre_http_request', array( $this, 'intercept_request' ), 10 );
		remove_filter( 'rop_dont_work_on_staging', '__return_false' );
		delete_option( 'rop_facebook_via_rs_app' );

		parent::tear_down();
	}

	/**
	 * Share a post to a Facebook page and return the captured request URLs.
	 *
	 * @param int                  $post_id      The post (or attachment) ID.
	 * @param array<string, mixed> $post_details Overrides for the post details.
	 * @return list<string>
	 */
	private function share( int $post_id, array $post_details = array() ): array {
		$service = new Rop_Facebook_Service();
		$shared  = $service->share(
			array_merge(
				array(
					'post_id'         => $post_id,
					'account_id'      => 'facebook_123_456',
					'service'         => 'facebook',
					'title'           => 'Graph version test',
					'content'         => 'Graph version test',
					'hashtags'        => '',
					'post_url'        => '',
					'post_with_image' => '',
				),
				$post_details
			),
			array(
				'id'           => '123',
				'access_token' => 'rop-test-token',
				'user'         => 'Test Page',
			)
		);

		$this->assertTrue( $shared, 'The Facebook share should succeed against the mocked Graph API.' );

		return $this->captured_urls;
	}

	/**
	 * A text share through the hosted app posts to the supported version's feed.
	 *
	 * @covers Rop_Facebook_Service::share
	 */
	public function test_hosted_app_text_share_targets_supported_graph_version(): void {
		$urls = $this->share( self::factory()->post->create() );

		$this->assertContains( 'https://graph.facebook.com/' . self::GRAPH_API_VERSION . '/123/feed', $urls );
	}

	/**
	 * A video share through the hosted app uploads to the supported version.
	 *
	 * @covers Rop_Facebook_Service::share
	 */
	public function test_hosted_app_video_share_targets_supported_graph_version(): void {
		$video_id = self::factory()->attachment->create( array( 'post_mime_type' => 'video/mp4' ) );

		$urls = $this->share(
			$video_id,
			array(
				'post_with_image' => true,
				'post_image'      => 'https://cdn.example.com/clip.mp4',
				'mimetype'        => array( 'type' => 'video/mp4' ),
			)
		);

		$this->assertContains( 'https://graph-video.facebook.com/' . self::GRAPH_API_VERSION . '/123/videos', $urls );
	}

	/**
	 * The SDK used by user-owned apps defaults to the supported version.
	 *
	 * @covers Rop_Facebook_Service::set_api
	 */
	public function test_user_app_sdk_targets_supported_graph_version(): void {
		$service = new Rop_Facebook_Service();
		$service->set_api( '123456', 'rop-test-secret' );

		$this->assertSame( self::GRAPH_API_VERSION, $service->get_api()->getDefaultGraphVersion() );
	}
}
