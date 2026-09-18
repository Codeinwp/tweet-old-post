<?php
/**
 * Revive Social bundles TwitterOAuth under its own namespace so it can never
 * be mixed with another plugin's `Abraham\TwitterOAuth` copy. See #1128.
 *
 * @package     ROP
 * @subpackage  Tests
 */

use Composer\Autoload\ClassLoader;

/**
 * Test the TwitterOAuth namespace isolation.
 */
class Test_RopTwitterOAuthIsolation extends WP_UnitTestCase {

	const FOREIGN = 'Abraham\\TwitterOAuth\\';
	const OWN     = 'Rop_Vendor\\TwitterOAuth\\';

	/**
	 * @var ClassLoader|null Competing loader registered by a test.
	 */
	private $foreign_loader;

	public function tearDown(): void {
		if ( $this->foreign_loader ) {
			$this->foreign_loader->unregister();
			$this->foreign_loader = null;
		}
		parent::tearDown();
	}

	/**
	 * The plugin's own Composer autoloader must not claim the upstream namespace,
	 * through PSR-4 or through its classmap. If it did, another plugin's client
	 * could be handed our classes.
	 */
	public function test_plugin_autoloader_does_not_claim_the_shared_twitteroauth_namespace() {
		$own_loaders = array();
		foreach ( spl_autoload_functions() as $loader ) {
			if ( is_array( $loader ) && $loader[0] instanceof ClassLoader && isset( $loader[0]->getPrefixesPsr4()[ self::OWN ] ) ) {
				$own_loaders[] = $loader[0];
			}
		}
		$this->assertCount( 1, $own_loaders, 'Expected exactly one autoloader to map Rop_Vendor\\TwitterOAuth.' );

		$loader = $own_loaders[0];
		$this->assertArrayNotHasKey( self::FOREIGN, $loader->getPrefixesPsr4() );
		foreach ( array_keys( $loader->getClassMap() ) as $class ) {
			$this->assertStringStartsNotWith( self::FOREIGN, $class );
		}
	}

	/**
	 * A competing loader with the upstream 4.0.1 contract, registered either
	 * ahead of or behind the plugin loader, must serve its own classes, and the
	 * plugin must keep building requests from its own copy.
	 */
	public function test_both_clients_build_requests_when_a_foreign_twitteroauth_is_loaded() {
		$fixture_dir = dirname( __FILE__ ) . '/helpers/foreign-twitteroauth';

		foreach ( array( 'TwitterOAuth', 'Request', 'Consumer', 'Token' ) as $class ) {
			$this->assertFalse( class_exists( self::FOREIGN . $class, false ), "{$class} must not be preloaded by the plugin." );
		}

		// Another plugin activated after this one: its Composer loader is prepended.
		$this->register_foreign_loader( $fixture_dir, true );
		$this->assertStringStartsWith( $fixture_dir, ( new ReflectionClass( self::FOREIGN . 'Request' ) )->getFileName() );
		$this->foreign_loader->unregister();

		// Another plugin activated before this one: its loader sits behind ours.
		$this->register_foreign_loader( $fixture_dir, false );
		$this->assertStringStartsWith( $fixture_dir, ( new ReflectionClass( self::FOREIGN . 'Token' ) )->getFileName() );

		$foreign_request = ( new \Abraham\TwitterOAuth\TwitterOAuth( 'key', 'secret' ) )->oauth( 'oauth/request_token' );
		$this->assertInstanceOf( self::FOREIGN . 'Request', $foreign_request );
		$this->assertSame( 'POST', $foreign_request->method );

		$service = new Rop_Twitter_Service();
		$service->set_api( '', '', 'key', 'secret' );
		$this->assertInstanceOf( self::OWN . 'TwitterOAuth', $service->get_api() );

		$own_request = \Rop_Vendor\TwitterOAuth\Request::fromConsumerAndToken(
			new \Rop_Vendor\TwitterOAuth\Consumer( 'key', 'secret' ),
			'POST',
			'https://api.twitter.com/oauth/request_token',
			null
		);
		$this->assertStringStartsWith( ROP_LITE_PATH, ( new ReflectionClass( $own_request ) )->getFileName() );
		$this->assertSame( 'POST', $own_request->getNormalizedHttpMethod() );
	}

	private function register_foreign_loader( $dir, $prepend ) {
		$this->foreign_loader = new ClassLoader();
		$this->foreign_loader->addPsr4( self::FOREIGN, $dir );
		$this->foreign_loader->register( $prepend );
	}
}
