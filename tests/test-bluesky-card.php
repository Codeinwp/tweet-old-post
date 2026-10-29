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

	const POST_URL = 'https://example.org/my-post/';

	const IMAGE_URL = 'https://example.org/wp-content/uploads/featured.jpg';

	const BLOB = array(
		'$type'    => 'blob',
		'ref'      => array( '$link' => 'bafkreitestblob' ),
		'mimeType' => 'image/jpeg',
		'size'     => 1234,
	);

	/**
	 * Call create_post() against mocked Bluesky endpoints and return the record it sent.
	 *
	 * @param array<string, mixed> $post             The post passed to create_post().
	 * @param string               $post_type        Type of the post.
	 * @param bool                 $upload_succeeds  Whether uploadBlob returns a blob.
	 *
	 * @return array<string, mixed> The createRecord record.
	 */
	private function create_post( array $post, string $post_type, bool $upload_succeeds = true ): array {
		$captured = null;

		$interceptor = function ( $preempt, $args, $url ) use ( &$captured, $upload_succeeds ) {
			$body = '';
			$code = 200;

			if ( self::IMAGE_URL === $url ) {
				$body = 'jpeg-bytes';
			} elseif ( false !== strpos( $url, 'com.atproto.repo.uploadBlob' ) ) {
				$code = $upload_succeeds ? 200 : 400;
				$body = wp_json_encode( $upload_succeeds ? array( 'blob' => self::BLOB ) : array( 'error' => 'BlobTooLarge' ) );
			} elseif ( false !== strpos( $url, 'com.atproto.repo.createRecord' ) ) {
				$captured = json_decode( $args['body'], true );
				$body     = wp_json_encode(
					array(
						'uri' => 'at://did:plc:test/app.bsky.feed.post/test',
						'cid' => 'test-cid',
					)
				);
			} else {
				return $preempt;
			}

			return array(
				'headers'  => array(),
				'body'     => $body,
				'response' => array(
					'code'    => $code,
					'message' => 200 === $code ? 'OK' : 'Bad Request',
				),
				'cookies'  => array(),
				'filename' => null,
			);
		};
		add_filter( 'pre_http_request', $interceptor, 10, 3 );

		$api = new Rop_Bluesky_Api( 'test.bsky.social', 'test-app-password' );
		$api->create_post( 'did:plc:test', $post, $post_type, '', 'test-access-token' );

		remove_filter( 'pre_http_request', $interceptor, 10 );

		$this->assertNotNull( $captured, 'create_post() should have attempted a createRecord request.' );

		return $captured['record'];
	}

	/**
	 * Post data with a link and a featured image.
	 *
	 * @param bool $with_link Whether the post has a link.
	 *
	 * @return array{content: string, title: string, post_url: string, post_image: string, mimetype: array{ext: string, type: string}}
	 */
	private function post_with_image( bool $with_link = true ): array {
		return array(
			'content'    => 'My caption text for this share.',
			'title'      => 'My Post Title',
			'post_url'   => $with_link ? self::POST_URL : '',
			'post_image' => self::IMAGE_URL,
			'mimetype'   => array(
				'ext'  => 'jpg',
				'type' => 'image/jpeg',
			),
		);
	}

	/**
	 * The link card description must be empty, not a copy of the post body.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_link_card_description_is_empty() {
		$record = $this->create_post(
			array(
				'content'  => 'My caption text for this share.',
				'title'    => 'My Post Title',
				'post_url' => self::POST_URL,
			),
			'link'
		);

		$embed = $record['embed'];
		$this->assertSame( 'app.bsky.embed.external', $embed['$type'] );
		$this->assertSame( self::POST_URL, $embed['external']['uri'] );
		$this->assertSame( 'My Post Title', $embed['external']['title'] );
		$this->assertSame(
			'',
			$embed['external']['description'],
			'Link card description must be empty, not a duplicate of the post caption (issue #676).'
		);
		$this->assertArrayNotHasKey( 'thumb', $embed['external'] );
	}

	/**
	 * A link share uses the post image as the link card thumbnail.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_link_card_uses_post_image_as_thumbnail(): void {
		$embed = $this->create_post( $this->post_with_image(), 'link' )['embed'];

		$this->assertSame( 'app.bsky.embed.external', $embed['$type'] );
		$this->assertSame( self::POST_URL, $embed['external']['uri'] );
		$this->assertSame( self::BLOB, $embed['external']['thumb'] );
	}

	/**
	 * A failed image upload still shares the link card, just without a thumbnail.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_link_card_has_no_thumbnail_when_upload_fails(): void {
		$embed = $this->create_post( $this->post_with_image(), 'link', false )['embed'];

		$this->assertSame( 'app.bsky.embed.external', $embed['$type'] );
		$this->assertArrayNotHasKey( 'thumb', $embed['external'] );
	}

	/**
	 * An image share with a link uses the image as the link card thumbnail.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_image_post_with_link_uses_image_as_thumbnail(): void {
		$embed = $this->create_post( $this->post_with_image(), 'image' )['embed'];

		$this->assertSame( 'app.bsky.embed.external', $embed['$type'] );
		$this->assertSame( self::BLOB, $embed['external']['thumb'] );
	}

	/**
	 * An image share with a link keeps its link card when the image upload fails.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_image_post_with_link_has_no_thumbnail_when_upload_fails(): void {
		$embed = $this->create_post( $this->post_with_image(), 'image', false )['embed'];

		$this->assertSame( 'app.bsky.embed.external', $embed['$type'] );
		$this->assertArrayNotHasKey( 'thumb', $embed['external'] );
	}

	/**
	 * An image share without a link embeds the image itself.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_image_post_without_link_embeds_image(): void {
		$embed = $this->create_post( $this->post_with_image( false ), 'image' )['embed'];

		$this->assertSame( 'app.bsky.embed.images', $embed['$type'] );
		$this->assertSame( 'My Post Title', $embed['images'][0]['alt'] );
		$this->assertSame( self::BLOB, $embed['images'][0]['image'] );
	}

	/**
	 * An image share without a link is sent as text when the image upload fails.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_image_post_without_link_has_no_embed_when_upload_fails(): void {
		$record = $this->create_post( $this->post_with_image( false ), 'image', false );

		$this->assertArrayNotHasKey( 'embed', $record );
	}
}
