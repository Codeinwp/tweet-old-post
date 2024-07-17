<?php
/**
 * ROP Test Post format actions for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test post format related actions. class.
 */
class Test_RopPostFormat extends WP_UnitTestCase {
	static public $post_ids;

	/**
	 * Init test accounts.
	 */
	public static function setUpBeforeClass() {
		Rop_InitAccounts::init();
		self::$post_ids = Rop_InitAccounts::generatePosts( 10, 'post', '-2 month' );
	}

	/**
	 * Testing URL shortners
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @covers Rop_Url_Shortner_Abstract
	 * @covers Rop_Rvivly_Shortner
	 * @covers Rop_Bitly_Shortner
	 * @covers Rop_Googl_Shortner
	 * @covers Rop_Isgd_Shortner
	 */
	public function test_url_shortners() {
		$url = 'http://google.com/';

		// rviv.ly Test
		$rvivly = new Rop_Rvivly_Shortner();
		$rvivly->set_website( $url );
		$short_url = $rvivly->shorten_url( $url );
		$this->assertNotEquals( $url, $short_url );

		$this->assertNotFalse( filter_var( $short_url, FILTER_VALIDATE_URL ) );
		$this->assertNotEquals( $short_url, '' );

		// bit.ly Test
		$bitly = new Rop_Bitly_Shortner();
		$key   = '604a207ee7119fb60f66b1296ff0d9fa74cf8e01';
		$bitly->set_credentials( array( 'generic_access_token' => $key ) );
		$short_url = $bitly->shorten_url( $url );
		$this->assertNotEquals( $url, $short_url );

		$this->assertNotFalse( filter_var( $short_url, FILTER_VALIDATE_URL ) );
		$this->assertNotEquals( $short_url, '' );

		$this->assertNotEquals( $url, $short_url );
		$this->assertNotFalse( filter_var( $short_url, FILTER_VALIDATE_URL ) );
		$this->assertNotEquals( $short_url, '' );

		// firebase Test
		$googl = new Rop_Firebase_Shortner();
		$key   = 'AIzaSyAXI8IA7oRqnKkebPBPhxEcdwC7zXqoAvQ';
		$googl->set_credentials( array( 'key' => $key, 'domain' => 'https://somethingps.page.link' ) );
		$short_url = $googl->shorten_url( $url );

		$this->assertNotEquals( $url, $short_url );
		$this->assertNotFalse( filter_var( $short_url, FILTER_VALIDATE_URL ) );
		$this->assertNotEquals( $short_url, '' );

		// is.gd Test
		$isgd      = new Rop_Isgd_Shortner();
		$short_url = $isgd->shorten_url( $url );

		$this->assertNotFalse( filter_var( $short_url, FILTER_VALIDATE_URL ) );
		$this->assertNotEquals( $short_url, '' );

		// rebrand.ly Test. Disabled until we find a way of testing it  without tmp keys.
//		$rebrandly = new Rop_Rebrandly_Shortner();
//		$key   = '6f74a48c3e114ed9973feaa45ccdd632';
//		$rebrandly->set_credentials( array( 'key' => $key, 'domain' => '' ) );
//		$short_url = $rebrandly->shorten_url( $url );
//
//		$this->assertNotEquals( $url, $short_url );
//		$this->assertNotEquals( $short_url, '' );
	}

	/**
	 * Test url include option.
	 */
	public function test_url_include() {
		$service                  = Rop_InitAccounts::ROP_TEST_SERVICE_NAME;
		$account_id               = Rop_InitAccounts::get_account_id();
		$post_format              = new Rop_Post_Format_Model( $service );
		$post_id = self::$post_ids[ rand( 0, count( self::$post_ids ) - 1 ) ];
		$format  = new Rop_Post_Format_Helper();
		$formated_post = $format->get_formated_object( $post_id, $account_id );
		$this->assertFalse( empty( $formated_post['post_url'] ), 'By default link should be included.' );

		$new_data                 = $post_format->get_post_format( $account_id );
		$new_data['include_link'] = false;
		$post_format->add_update_post_format( $account_id, $new_data );
		$formated_post = $format->get_formated_object( $post_id, $account_id );
		$this->assertTrue( empty( $formated_post['post_url'] ), 'Link include is not working.' );

	}

	/**
	 * Test categories hashtags option.
	 */
	public function test_hashtags_from_categories() {
		$service              = Rop_InitAccounts::ROP_TEST_SERVICE_NAME;
		$account_id           = Rop_InitAccounts::get_account_id();
		$post_format          = new Rop_Post_Format_Model( $service );
		$new_data             = $post_format->get_post_format( $account_id );
		$new_data['hashtags'] = 'categories-hashtags';
		$new_data['hashtags_length'] = '30';

		$post_format->add_update_post_format( $account_id, $new_data );

		$format  = new Rop_Post_Format_Helper();
		$post_id = self::$post_ids[ rand( 0, count( self::$post_ids ) - 1 ) ];

		$formated_post = $format->get_formated_object( $post_id, $account_id );

		$this->assertFalse( empty( $formated_post['hashtags'] ), 'Tags hashtags not working' );
	}

	/**
	 * Test tags hashtags option.
	 */
	public function test_hashtags_from_tags() {
		$service              = Rop_InitAccounts::ROP_TEST_SERVICE_NAME;
		$account_id           = Rop_InitAccounts::get_account_id();
		$post_format          = new Rop_Post_Format_Model( $service );
		$new_data             = $post_format->get_post_format( $account_id );
		$new_data['hashtags'] = 'tags-hashtags';
		$new_data['hashtags_length'] = '30';
		$post_format->add_update_post_format( $account_id, $new_data );

		$format  = new Rop_Post_Format_Helper();
		$post_id = self::$post_ids[ rand( 0, count( self::$post_ids ) - 1 ) ];

		$formated_post = $format->get_formated_object( $post_id, $account_id );

		$this->assertFalse( empty( $formated_post['hashtags'] ), 'Cats hashtags not working' );
	}

	/**
	 * Test common hashtags option.
	 *
	 * Obsolete test, we're no longer including the common hashtags
	 * inside the content body, we're instead adding them in share method.
	 */

	/*
	public function test_hashtags_from_common_text() {
		$service                     = Rop_InitAccounts::ROP_TEST_SERVICE_NAME;
		$account_id                  = Rop_InitAccounts::get_account_id();
		$post_format                 = new Rop_Post_Format_Model( $service );
		$new_data                    = $post_format->get_post_format( $account_id );
		$new_data['hashtags']        = 'common-hashtags';
		$new_data['hashtags_common'] = 'testtag, anotherone';

		$post_format->add_update_post_format( $account_id, $new_data );
		$post_id = self::$post_ids[ rand( 0, count( self::$post_ids ) - 1 ) ];

		$format        = new Rop_Post_Format_Helper();
		$formated_post = $format->get_formated_object( $post_id, $account_id );

		$this->assertNotFalse( strpos( $formated_post['content'], 'testtag' ), 'Common hashtags not working' );

	}*/

	/**
	 * Testing post format
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @covers Rop_Model_Abstract
	 * @covers Rop_Post_Format_Model::<public>
	 */
	public function test_post_format() {
		$service         = Rop_InitAccounts::ROP_TEST_SERVICE_NAME;
		$account_id      = Rop_InitAccounts::get_account_id();
		$global_settings = new Rop_Global_Settings();
		$defaults        = $global_settings->get_default_post_format( $service );

		$post_format = new Rop_Post_Format_Model( $service );

		$this->assertEquals( $post_format->get_post_format( $account_id ), $defaults );
		$this->assertArrayHasKey( 'post_content', $defaults );
		$this->assertArrayHasKey( 'maximum_length', $defaults );
		$this->assertArrayHasKey( 'short_url_service', $defaults );
		$this->assertArrayHasKey( 'short_url', $defaults );
		$this->assertArrayHasKey( 'include_link', $defaults );

		$this->assertEquals( 'post_title', $defaults['post_content'] );
		$this->assertEquals( '240', $defaults['maximum_length'] );
		$this->assertEquals( false, $defaults['short_url'] );
		$this->assertEquals( 'rviv.ly', $defaults['short_url_service'] );
		$this->assertEquals( true, $defaults['include_link'] );
		$new_data                      = $defaults;
		$new_data['include_link']      = false;
		$new_data['maximum_length']    = 1900;
		$new_data['short_url_service'] = 'wp_short_url';

		$post_format->add_update_post_format( $account_id, $new_data );

		$this->assertEquals( $post_format->get_post_format( $account_id ), $new_data );

		$post_format->remove_post_format( $account_id );

		$this->assertEquals( $post_format->get_post_format( $account_id ), $defaults );

	}

}
