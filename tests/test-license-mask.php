<?php
/**
 * ROP Test license key display masking for PHPUnit.
 *
 * get_license_data_view() builds the license field placeholder by repeating '*'
 * for every character of the stored key except the last four. A key shorter than
 * that made the repeat count negative, which is an uncaught ValueError on PHP 8
 * and took down every admin request that enqueues the settings.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test license key display masking. class.
 */
class Test_RopLicenseMask extends WP_UnitTestCase {

	/**
	 * The default, unmasked placeholder.
	 *
	 * @var string
	 */
	const DEFAULT_MASK = 'Add your license key here...';

	/**
	 * Removes the license option so no state leaks into other suites.
	 */
	public function tearDown(): void {
		delete_option( 'tweet_old_post_pro_license_data' );
		parent::tearDown();
	}

	/**
	 * Store a pro license data object and return its display view.
	 *
	 * @param array $license_data The license data to persist.
	 *
	 * @return array
	 */
	private function get_view( $license_data ) {
		if ( ! defined( 'ROP_PRO_VERSION' ) ) {
			define( 'ROP_PRO_VERSION', '3.1.0' );
		}

		update_option( 'tweet_old_post_pro_license_data', (object) $license_data );

		$global = new Rop_Global_Settings();

		return $global->get_license_data_view();
	}

	/**
	 * A key shorter than the displayed suffix must not crash.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_short_key_does_not_crash() {
		$view = $this->get_view(
			array(
				'license' => 'invalid',
				'key'     => 'abc',
			)
		);

		$this->assertEquals( self::DEFAULT_MASK, $view['passwordMask'], 'A key shorter than 4 chars should fall back to the default placeholder' );
	}

	/**
	 * An empty or unusable key must not crash either.
	 *
	 * @param mixed $key The stored key.
	 *
	 * @dataProvider provide_empty_keys
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_empty_key_falls_back_to_default( $key ) {
		$view = $this->get_view(
			array(
				'license' => 'invalid',
				'key'     => $key,
			)
		);

		$this->assertEquals( self::DEFAULT_MASK, $view['passwordMask'], 'An empty key should fall back to the default placeholder' );
	}

	/**
	 * Empty key values reaching the display path.
	 *
	 * @return array
	 */
	public function provide_empty_keys() {
		return array(
			'empty string' => array( '' ),
			'null'         => array( null ),
			'array'        => array( array() ),
		);
	}

	/**
	 * A key exactly as long as the suffix must not be displayed in full.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_four_char_key_is_not_leaked() {
		$view = $this->get_view(
			array(
				'license' => 'invalid',
				'key'     => 'abcd',
			)
		);

		$this->assertEquals( self::DEFAULT_MASK, $view['passwordMask'], 'A 4 char key should not be displayed unmasked' );
	}

	/**
	 * A real key keeps showing only its last four characters.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_real_key_shows_only_last_four_chars() {
		$key  = str_repeat( 'a', 28 ) . 'wxyz';
		$view = $this->get_view(
			array(
				'license' => 'valid',
				'key'     => $key,
			)
		);

		$this->assertEquals( str_repeat( '*', 28 ) . 'wxyz', $view['passwordMask'], 'Only the last 4 chars of the key should be visible' );
	}
}
