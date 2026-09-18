<?php
/**
 * Revive Social bundles TwitterOAuth under its own namespace so it can never
 * be mixed with another plugin's `Abraham\TwitterOAuth` copy. See #1128.
 *
 * @package     ROP
 * @subpackage  Tests
 */

/**
 * Test the TwitterOAuth namespace isolation.
 */
class Test_RopTwitterOAuthIsolation extends WP_UnitTestCase {

	/**
	 * The plugin's own Composer autoloader must not claim the upstream namespace.
	 * If it did, another plugin's client could be handed our classes.
	 */
	public function test_plugin_autoloader_does_not_claim_the_shared_twitteroauth_namespace() {
		$own_loaders = array();
		foreach ( spl_autoload_functions() as $loader ) {
			if ( is_array( $loader ) && $loader[0] instanceof \Composer\Autoload\ClassLoader && isset( $loader[0]->getPrefixesPsr4()['Rop_Vendor\\TwitterOAuth\\'] ) ) {
				$own_loaders[] = $loader[0];
			}
		}

		$this->assertCount( 1, $own_loaders, 'Expected exactly one autoloader to map Rop_Vendor\\TwitterOAuth.' );
		$this->assertArrayNotHasKey( 'Abraham\\TwitterOAuth\\', $own_loaders[0]->getPrefixesPsr4() );
	}

	/**
	 * With a foreign `Abraham\TwitterOAuth` present that uses the upstream 4.0.1
	 * argument order, both clients must build requests from their own classes.
	 */
	public function test_both_clients_build_requests_when_a_foreign_twitteroauth_is_loaded() {
		require_once dirname( __FILE__ ) . '/helpers/foreign-twitteroauth.php';

		$foreign_request = ( new \Abraham\TwitterOAuth\TwitterOAuth( 'key', 'secret' ) )->oauth( 'oauth/request_token' );
		$this->assertInstanceOf( 'Abraham\\TwitterOAuth\\Request', $foreign_request );
		$this->assertSame( 'POST', $foreign_request->method );

		$service = new Rop_Twitter_Service();
		$service->set_api( '', '', 'key', 'secret' );
		$this->assertInstanceOf( 'Rop_Vendor\\TwitterOAuth\\TwitterOAuth', $service->get_api() );

		$own_request = \Rop_Vendor\TwitterOAuth\Request::fromConsumerAndToken(
			new \Rop_Vendor\TwitterOAuth\Consumer( 'key', 'secret' ),
			'POST',
			'https://api.twitter.com/oauth/request_token',
			null
		);
		$this->assertInstanceOf( 'Rop_Vendor\\TwitterOAuth\\Request', $own_request );
		$this->assertSame( 'POST', $own_request->getNormalizedHttpMethod() );
	}
}
