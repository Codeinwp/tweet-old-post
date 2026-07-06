<?php
/**
 * ROP Test legacy auth capability guard for PHPUnit.
 *
 * The legacy_auth() admin_init callback connects social accounts from an OAuth
 * callback. It must never run for users without `manage_options`, otherwise a
 * low privilege user could trigger the account authorization flow.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       9.3.7
 */

/**
 * Test legacy auth capability guard. class.
 */
class Test_RopLegacyAuthCap extends WP_UnitTestCase {

	/**
	 * Whether a redirect (i.e. reaching a service authorize() call) was attempted.
	 *
	 * @var bool
	 */
	private $redirected = false;

	/**
	 * Records a redirect attempt and cancels it so no real OAuth flow runs.
	 *
	 * @return false
	 */
	public function catch_redirect() {
		$this->redirected = true;
		return false;
	}

	/**
	 * Run legacy_auth() with the given OAuth callback query args.
	 *
	 * @param array $get The $_GET payload to simulate.
	 */
	private function run_legacy_auth( $get ) {
		$this->redirected = false;

		$original = $_GET;
		$_GET     = array_merge( $_GET, $get );

		add_filter( 'wp_redirect', array( $this, 'catch_redirect' ) );
		add_filter( 'pre_http_request', '__return_empty_array' );

		$admin = new Rop_Admin( 'tweet-old-post', defined( 'ROP_VERSION' ) ? ROP_VERSION : '' );
		$admin->legacy_auth();

		remove_filter( 'wp_redirect', array( $this, 'catch_redirect' ) );
		remove_filter( 'pre_http_request', '__return_empty_array' );
		$_GET = $original;
	}

	/**
	 * A subscriber must be stopped before the Twitter authorization redirect.
	 *
	 * @covers Rop_Admin::legacy_auth
	 */
	public function test_subscriber_blocked_on_twitter_callback() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->run_legacy_auth(
			array(
				'network'        => 'twitter',
				'oauth_token'    => 'tok',
				'oauth_verifier' => 'ver',
			)
		);

		$this->assertFalse( $this->redirected, 'A subscriber must not reach the Twitter authorization redirect.' );

		wp_set_current_user( 0 );
	}

	/**
	 * A subscriber must be stopped before the LinkedIn authorization redirect.
	 *
	 * @covers Rop_Admin::legacy_auth
	 */
	public function test_subscriber_blocked_on_linkedin_callback() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->run_legacy_auth(
			array(
				'network' => 'linkedin',
				'code'    => 'abc',
				'state'   => 'xyz',
			)
		);

		$this->assertFalse( $this->redirected, 'A subscriber must not reach the LinkedIn authorization redirect.' );

		wp_set_current_user( 0 );
	}
}
