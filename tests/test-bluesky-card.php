<?php
/**
 * ROP Test Bluesky link card embed for PHPUnit.
 *
 * Reproduces GitHub issue Codeinwp/tweet-old-post-pro#676: the link card
 * (app.bsky.embed.external) must not repeat the post caption as its
 * description — Bluesky renders cards without a description as title + domain.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test Bluesky link card. class.
 */
class Test_RopBlueskyCard extends WP_UnitTestCase {

	/**
	 * The link card description must be empty, not a copy of the post body.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_link_card_description_is_empty() {
		$captured = null;

		$interceptor = function ( $preempt, $args, $url ) use ( &$captured ) {
			if ( false === strpos( $url, 'com.atproto.repo.createRecord' ) ) {
				return $preempt;
			}
			$captured = json_decode( $args['body'], true );

			return array(
				'headers'  => array(),
				'body'     => wp_json_encode(
					array(
						'uri' => 'at://did:plc:test/app.bsky.feed.post/test',
						'cid' => 'test-cid',
					)
				),
				'response' => array(
					'code'    => 200,
					'message' => 'OK',
				),
				'cookies'  => array(),
				'filename' => null,
			);
		};
		add_filter( 'pre_http_request', $interceptor, 10, 3 );

		$api = new Rop_Bluesky_Api( 'test.bsky.social', 'test-app-password' );
		$api->create_post(
			'did:plc:test',
			array(
				'content'  => 'My caption text for this share.',
				'title'    => 'My Post Title',
				'post_url' => 'https://example.org/my-post/',
			),
			'link',
			'',
			'test-access-token'
		);

		remove_filter( 'pre_http_request', $interceptor, 10 );

		$this->assertNotNull( $captured, 'create_post() should have attempted a createRecord request.' );

		$embed = $captured['record']['embed'];
		$this->assertSame( 'app.bsky.embed.external', $embed['$type'] );
		$this->assertSame( 'https://example.org/my-post/', $embed['external']['uri'] );
		$this->assertSame( 'My Post Title', $embed['external']['title'] );
		$this->assertSame(
			'',
			$embed['external']['description'],
			'Link card description must be empty, not a duplicate of the post caption (issue #676).'
		);
	}
}
