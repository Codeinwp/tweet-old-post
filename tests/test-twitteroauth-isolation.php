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
	 * Loading the plugin must not make the shared upstream namespace resolvable.
	 */
	public function test_plugin_does_not_provide_the_shared_twitteroauth_namespace() {
		foreach ( array( 'TwitterOAuth', 'Request', 'Consumer', 'Token' ) as $class ) {
			$this->assertFalse(
				class_exists( 'Abraham\\TwitterOAuth\\' . $class ),
				"Revive Social must not autoload Abraham\\TwitterOAuth\\{$class}."
			);
		}
	}

	/**
	 * The Twitter service client must be the plugin's own namespaced copy.
	 */
	public function test_twitter_service_client_uses_the_plugin_namespaced_library() {
		$service = new Rop_Twitter_Service();
		$service->set_api( 'token', 'secret', 'consumer-key', 'consumer-secret' );

		$this->assertInstanceOf( 'Rop_Vendor\\TwitterOAuth\\TwitterOAuth', $service->get_api() );
	}
}
