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

	const IMAGE_RESPONSE = array(
		'code'         => 200,
		'content_type' => 'image/jpeg',
		'body'         => 'jpeg-bytes',
	);

	/**
	 * Number of uploadBlob requests made by the last create_post() call.
	 */
	private int $blob_uploads = 0;

	/**
	 * Call create_post() against mocked Bluesky endpoints and return the record it sent.
	 *
	 * @param array<string, mixed>                                                            $post      The post passed to create_post().
	 * @param string                                                                          $post_type Type of the post.
	 * @param array{'$type': string, ref: array{'$link': string}, mimeType: string, size: int}|null $blob      The blob uploadBlob returns, or null when the upload fails.
	 * @param array{code: int, content_type: string, body: string}                               $image     The response served for the post image URL.
	 *
	 * @return array<string, mixed> The createRecord record.
	 */
	private function create_post( array $post, string $post_type, ?array $blob = self::BLOB, array $image = self::IMAGE_RESPONSE ): array {
		$captured           = null;
		$this->blob_uploads = 0;

		$interceptor = function ( $preempt, $args, $url ) use ( &$captured, $blob, $image ) {
			$body    = '';
			$code    = 200;
			$headers = array();

			if ( self::IMAGE_URL === $url ) {
				$code    = $image['code'];
				$body    = $image['body'];
				$headers = array( 'content-type' => $image['content_type'] );
			} elseif ( false !== strpos( $url, 'com.atproto.repo.uploadBlob' ) ) {
				++$this->blob_uploads;
				$code = null === $blob ? 400 : 200;
				$body = wp_json_encode( null === $blob ? array( 'error' => 'BlobTooLarge' ) : array( 'blob' => $blob ) );
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
				'headers'  => $headers,
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
		$embed = $this->create_post( $this->post_with_image(), 'link', null )['embed'];

		$this->assertSame( 'app.bsky.embed.external', $embed['$type'] );
		$this->assertArrayNotHasKey( 'thumb', $embed['external'] );
	}

	/**
	 * Post types that share a post with a link as a link card.
	 *
	 * @return array<string, array{0: string}>
	 */
	public function link_card_post_types(): array {
		return array(
			'link'  => array( 'link' ),
			'image' => array( 'image' ),
		);
	}

	/**
	 * A link card skips a thumbnail over the size Bluesky accepts, rather than failing the share.
	 *
	 * @dataProvider link_card_post_types
	 * @covers Rop_Bluesky_Api::create_post
	 *
	 * @param string $post_type Type of the post.
	 */
	public function test_link_card_skips_oversized_thumbnail( string $post_type ): void {
		$blob = array_merge( self::BLOB, array( 'size' => Rop_Bluesky_Api::MAX_THUMB_BYTES + 1 ) );

		$embed = $this->create_post( $this->post_with_image(), $post_type, $blob )['embed'];

		$this->assertSame( 'app.bsky.embed.external', $embed['$type'] );
		$this->assertSame( self::POST_URL, $embed['external']['uri'] );
		$this->assertArrayNotHasKey( 'thumb', $embed['external'] );
	}

	/**
	 * A link card keeps a thumbnail exactly at the size Bluesky accepts.
	 *
	 * @covers Rop_Bluesky_Api::create_post
	 */
	public function test_link_card_keeps_thumbnail_at_size_limit(): void {
		$blob = array_merge( self::BLOB, array( 'size' => Rop_Bluesky_Api::MAX_THUMB_BYTES ) );

		$embed = $this->create_post( $this->post_with_image(), 'link', $blob )['embed'];

		$this->assertSame( $blob, $embed['external']['thumb'] );
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
		$embed = $this->create_post( $this->post_with_image(), 'image', null )['embed'];

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
		$record = $this->create_post( $this->post_with_image( false ), 'image', null );

		$this->assertArrayNotHasKey( 'embed', $record );
	}

	/**
	 * Responses for the post image URL that are not an image.
	 *
	 * @return array<string, array{0: array{code: int, content_type: string, body: string}}>
	 */
	public function non_image_responses(): array {
		$page = '<html><body>Not Found</body></html>';

		return array(
			'404 page'            => array(
				array(
					'code'         => 404,
					'content_type' => 'text/html; charset=UTF-8',
					'body'         => $page,
				),
			),
			'404 with image type' => array(
				array(
					'code'         => 404,
					'content_type' => 'image/jpeg',
					'body'         => $page,
				),
			),
			'HTML page with 200'  => array(
				array(
					'code'         => 200,
					'content_type' => 'text/html; charset=UTF-8',
					'body'         => $page,
				),
			),
			'200 without a type'  => array(
				array(
					'code'         => 200,
					'content_type' => '',
					'body'         => $page,
				),
			),
		);
	}

	/**
	 * A link card goes out without a thumbnail when the post image URL does not return an image.
	 *
	 * @dataProvider non_image_responses
	 * @covers Rop_Bluesky_Api::create_post
	 *
	 * @param array{code: int, content_type: string, body: string} $image The response served for the post image URL.
	 */
	public function test_link_card_skips_thumbnail_when_image_url_is_not_an_image( array $image ): void {
		$embed = $this->create_post( $this->post_with_image(), 'link', self::BLOB, $image )['embed'];

		$this->assertSame( 0, $this->blob_uploads );
		$this->assertSame( 'app.bsky.embed.external', $embed['$type'] );
		$this->assertArrayNotHasKey( 'thumb', $embed['external'] );
	}

	/**
	 * An image share without a link is sent as text when the post image URL does not return an image.
	 *
	 * @dataProvider non_image_responses
	 * @covers Rop_Bluesky_Api::create_post
	 *
	 * @param array{code: int, content_type: string, body: string} $image The response served for the post image URL.
	 */
	public function test_image_post_has_no_embed_when_image_url_is_not_an_image( array $image ): void {
		$record = $this->create_post( $this->post_with_image( false ), 'image', self::BLOB, $image );

		$this->assertSame( 0, $this->blob_uploads );
		$this->assertArrayNotHasKey( 'embed', $record );
	}
}
