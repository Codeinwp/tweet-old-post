<?php
/**
 * ROP Test Bluesky grapheme limit handling for PHPUnit.
 *
 * Bluesky (AT Protocol) rejects app.bsky.feed.post records whose text exceeds
 * 300 grapheme clusters. These tests reproduce GitHub issue
 * Codeinwp/tweet-old-post-pro#677: byte-based truncation and unchecked hashtag
 * concatenation let the plugin submit records longer than 300 graphemes.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test Bluesky grapheme limit. class.
 */
class Test_RopBlueskyGrapheme extends WP_UnitTestCase {

	/**
	 * Bluesky enforces this limit in grapheme clusters, not bytes.
	 */
	const BLUESKY_MAX_GRAPHEMES = 300;

	/**
	 * The body of the last request captured from create_post().
	 *
	 * @var array|null
	 */
	private $captured_body = null;

	/**
	 * Call create_post() with the outbound HTTP request intercepted, and
	 * return the record that would have been submitted to Bluesky.
	 *
	 * @param string $content  The post content (as produced by build_content).
	 * @param string $hashtags The hashtags string appended by the service.
	 * @return array The captured createRecord request body.
	 */
	private function capture_created_record( $content, $hashtags ) {
		$this->captured_body = null;

		$interceptor = function ( $preempt, $args, $url ) {
			if ( false === strpos( $url, 'com.atproto.repo.createRecord' ) ) {
				return $preempt;
			}
			$this->captured_body = json_decode( $args['body'], true );

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
			array( 'content' => $content ),
			'text',
			$hashtags,
			'test-access-token'
		);

		remove_filter( 'pre_http_request', $interceptor, 10 );

		$this->assertNotNull( $this->captured_body, 'create_post() should have attempted a createRecord request.' );

		return $this->captured_body;
	}

	/**
	 * Multibyte content that passed the user-configured maximum length (which
	 * can be set above 300, e.g. 1000) must still be clamped to 300 graphemes
	 * before it is submitted to Bluesky.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_create_post_clamps_multibyte_content_to_grapheme_limit() {
		// 310 graphemes / 620 bytes — accepted by a byte-unaware 1000 limit upstream.
		$content = str_repeat( "\u{00E9}", 310 );

		$body = $this->capture_created_record( $content, '' );

		$this->assertLessThanOrEqual(
			self::BLUESKY_MAX_GRAPHEMES,
			grapheme_strlen( $body['record']['text'] ),
			'Record text exceeds the 300 grapheme Bluesky limit; the API would reject it with "grapheme too big".'
		);
	}

	/**
	 * Hashtags are appended after upstream truncation, so content that fits
	 * the limit exactly must not overflow it once hashtags are concatenated.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_create_post_clamps_appended_hashtags_to_grapheme_limit() {
		// Plain ASCII content already at the 300 grapheme limit.
		$content  = rtrim( str_repeat( 'word ', 60 ) );
		$hashtags = ' #wordpress #news';

		$body = $this->capture_created_record( $content, $hashtags );

		$this->assertLessThanOrEqual(
			self::BLUESKY_MAX_GRAPHEMES,
			grapheme_strlen( $body['record']['text'] ),
			'Appending hashtags pushed the record text over the 300 grapheme Bluesky limit.'
		);
	}

	/**
	 * token_truncate() must measure characters, not bytes: a 300-character
	 * multibyte string is within a 300-character limit and must survive
	 * truncation unchanged instead of being cut roughly in half.
	 *
	 * @covers Rop_Content_Helper::token_truncate
	 */
	public function test_token_truncate_measures_characters_not_bytes() {
		$ch = new Rop_Content_Helper();

		// 75 tokens of "ééé " = 300 characters (600 é bytes + 75 spaces).
		$string = rtrim( str_repeat( "\u{00E9}\u{00E9}\u{00E9} ", 75 ) );
		$this->assertSame( 299, mb_strlen( $string, 'UTF-8' ) );

		$this->assertSame(
			$string,
			$ch->token_truncate( $string, 300 ),
			'A 299-character string was truncated under a 300-character limit because lengths were counted in bytes.'
		);
	}
}
