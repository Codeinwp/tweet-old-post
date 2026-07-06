<?php
/**
 * ROP Test post URL building for PHPUnit.
 *
 * Regression coverage for Rop_Post_Format_Helper::build_url after the WPML slug
 * fix (issue #556). The multilingual branches require the WPML / TranslatePress
 * plugins and cannot run here, but this guards the standard (non multilingual)
 * path the refactor must preserve.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       9.3.7
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test post URL building. class.
 */
class Test_RopBuildUrl extends WP_UnitTestCase {
	/**
	 * Generated post ids.
	 *
	 * @var array
	 */
	static public $post_ids;

	/**
	 * Init test accounts and posts.
	 */
	public static function setUpBeforeClass(): void {
		Rop_InitAccounts::init();
		self::$post_ids = Rop_InitAccounts::generatePosts( 3, 'post', '-2 month' );
	}

	/**
	 * On a non multilingual site the shared link is the post's own permalink.
	 *
	 * @covers Rop_Post_Format_Helper::build_url
	 */
	public function test_standard_permalink_is_used() {
		$account_id = Rop_InitAccounts::get_account_id();
		$post_id    = self::$post_ids[0];

		$format   = new Rop_Post_Format_Helper();
		$formated = $format->get_formated_object( $post_id, $account_id );

		// build_url may append UTM parameters, so the permalink is the prefix.
		$this->assertStringStartsWith( get_permalink( $post_id ), $formated['post_url'] );
	}

	/**
	 * With link inclusion disabled build_url returns an empty string.
	 *
	 * @covers Rop_Post_Format_Helper::build_url
	 */
	public function test_no_link_when_include_link_disabled() {
		$service     = Rop_InitAccounts::ROP_TEST_SERVICE_NAME;
		$account_id  = Rop_InitAccounts::get_account_id();
		$post_format = new Rop_Post_Format_Model( $service );

		$data                 = $post_format->get_post_format( $account_id );
		$original_include     = $data['include_link'];
		$data['include_link'] = false;
		$post_format->add_update_post_format( $account_id, $data );

		$format   = new Rop_Post_Format_Helper();
		$formated = $format->get_formated_object( self::$post_ids[0], $account_id );
		$this->assertSame( '', $formated['post_url'] );

		// Restore the default so other tests keep the link included.
		$data['include_link'] = $original_include;
		$post_format->add_update_post_format( $account_id, $data );
	}
}
