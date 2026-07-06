<?php
/**
 * ROP Test Google My Business image conversion for PHPUnit.
 *
 * Covers the featured image format conversion helper that turns formats GMB
 * cannot ingest (e.g. WebP) into JPEG before sharing.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.5.9
 */

/**
 * Test GMB image conversion. class.
 */
class Test_RopGmbImage extends WP_UnitTestCase {

	/**
	 * Invoke the private maybe_convert_unsupported_image() helper.
	 *
	 * @param string $url The image URL to pass in.
	 * @return string
	 */
	private function convert( $url ) {
		$service = new Rop_Gmb_Service();
		$method  = new ReflectionMethod( $service, 'maybe_convert_unsupported_image' );
		$method->setAccessible( true );

		return $method->invoke( $service, $url );
	}

	/**
	 * An empty URL is returned untouched.
	 *
	 * @covers Rop_Gmb_Service::maybe_convert_unsupported_image
	 */
	public function test_empty_url_is_untouched() {
		$this->assertSame( '', $this->convert( '' ) );
	}

	/**
	 * A supported format (jpg/png) is never converted.
	 *
	 * @covers Rop_Gmb_Service::maybe_convert_unsupported_image
	 */
	public function test_supported_format_is_untouched() {
		$uploads = wp_get_upload_dir();
		$url     = $uploads['baseurl'] . '/rop-test.jpg';
		$this->assertSame( $url, $this->convert( $url ) );
	}

	/**
	 * An unsupported image hosted outside the uploads dir cannot be converted
	 * and is returned unchanged.
	 *
	 * @covers Rop_Gmb_Service::maybe_convert_unsupported_image
	 */
	public function test_external_unsupported_image_is_untouched() {
		$external = 'https://cdn.example.com/remote.webp';
		$this->assertSame( $external, $this->convert( $external ) );
	}

	/**
	 * A local unsupported image whose file is missing is returned unchanged.
	 *
	 * @covers Rop_Gmb_Service::maybe_convert_unsupported_image
	 */
	public function test_missing_local_file_is_untouched() {
		$uploads = wp_get_upload_dir();
		$url     = $uploads['baseurl'] . '/does-not-exist.webp';
		$this->assertSame( $url, $this->convert( $url ) );
	}

	/**
	 * A real local WebP is converted to a deterministic JPEG sibling, and a
	 * second call reuses the already converted file.
	 *
	 * @covers Rop_Gmb_Service::maybe_convert_unsupported_image
	 */
	public function test_local_webp_is_converted_to_jpeg() {
		if ( ! function_exists( 'imagewebp' ) ) {
			$this->markTestSkipped( 'GD WebP support is required for this test.' );
		}

		$uploads = wp_get_upload_dir();
		$path    = $uploads['basedir'] . '/rop-gmb-test.webp';
		$image   = imagecreatetruecolor( 8, 8 );
		imagewebp( $image, $path );
		imagedestroy( $image );

		$editor = wp_get_image_editor( $path );
		if ( is_wp_error( $editor ) ) {
			@unlink( $path );
			$this->markTestSkipped( 'Image editor cannot read WebP in this environment.' );
		}

		$url       = $uploads['baseurl'] . '/rop-gmb-test.webp';
		$converted = $this->convert( $url );

		$this->assertStringEndsWith( '-rop-gmb.jpg', $converted );
		$jpeg_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $converted );
		$this->assertFileExists( $jpeg_path );

		// Second call must reuse the same converted file, not create a new one.
		$this->assertSame( $converted, $this->convert( $url ) );

		@unlink( $path );
		@unlink( $jpeg_path );
	}
}
