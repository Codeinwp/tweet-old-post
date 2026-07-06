<?php
/**
 * ROP Test Bluesky image resolution for PHPUnit.
 *
 * Covers the helper that picks the largest attachment size that still fits
 * within the Bluesky blob upload byte limit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       9.3.7
 */

/**
 * Test Bluesky image resolution. class.
 */
class Test_RopBlueskyImage extends WP_UnitTestCase {

	/**
	 * Invoke the private get_blob_safe_image_url() helper.
	 *
	 * @param array $post_details The post details passed to the helper.
	 * @return string
	 */
	private function resolve( $post_details ) {
		$service = new Rop_Bluesky_Service();
		$method  = new ReflectionMethod( $service, 'get_blob_safe_image_url' );
		$method->setAccessible( true );

		return $method->invoke( $service, $post_details );
	}

	/**
	 * No image means nothing to resolve.
	 *
	 * @covers Rop_Bluesky_Service::get_blob_safe_image_url
	 */
	public function test_missing_image_returns_empty() {
		$this->assertSame( '', $this->resolve( array() ) );
		$this->assertSame( '', $this->resolve( array( 'post_image' => '' ) ) );
	}

	/**
	 * External images are left untouched.
	 *
	 * @covers Rop_Bluesky_Service::get_blob_safe_image_url
	 */
	public function test_external_image_is_untouched() {
		$external = 'https://cdn.example.com/remote.jpg';
		$this->assertSame( $external, $this->resolve( array( 'post_image' => $external ) ) );
	}

	/**
	 * A local URL that resolves to no attachment is returned unchanged.
	 *
	 * @covers Rop_Bluesky_Service::get_blob_safe_image_url
	 */
	public function test_unresolvable_local_image_is_untouched() {
		$uploads = wp_get_upload_dir();
		$url     = $uploads['baseurl'] . '/no-such-attachment.jpg';
		$this->assertSame( $url, $this->resolve( array( 'post_image' => $url ) ) );
	}

	/**
	 * A resized variant is upgraded to the full size when it fits the limit,
	 * and left untouched when even the smallest candidate exceeds the limit.
	 *
	 * @covers Rop_Bluesky_Service::get_blob_safe_image_url
	 */
	public function test_variant_is_upgraded_within_byte_limit() {
		if ( ! function_exists( 'imagejpeg' ) ) {
			$this->markTestSkipped( 'GD JPEG support is required for this test.' );
		}

		$uploads = wp_get_upload_dir();
		$file    = $uploads['basedir'] . '/rop-bsky.jpg';
		$image   = imagecreatetruecolor( 20, 20 );
		imagejpeg( $image, $file );
		imagedestroy( $image );

		$attach_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'rop-bsky',
				'post_status'    => 'inherit',
			),
			$file
		);
		$this->assertGreaterThan( 0, $attach_id );

		$full_url    = $uploads['baseurl'] . '/rop-bsky.jpg';
		$variant_url = $uploads['baseurl'] . '/rop-bsky-150x150.jpg';

		// Full image (a few hundred bytes) is under the default 1MB limit, so
		// the resized variant is upgraded back to the full resolution.
		$this->assertSame( $full_url, $this->resolve( array( 'post_image' => $variant_url ) ) );

		// With a 1 byte limit nothing qualifies, so the input is returned as-is.
		$tiny_limit = function () {
			return 1;
		};
		add_filter( 'rop_bluesky_max_image_bytes', $tiny_limit );
		$this->assertSame( $variant_url, $this->resolve( array( 'post_image' => $variant_url ) ) );
		remove_filter( 'rop_bluesky_max_image_bytes', $tiny_limit );

		wp_delete_attachment( $attach_id, true );
	}
}
