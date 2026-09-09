<?php
/**
 * ROP Test empty post format maximum length for PHPUnit.
 *
 * The Maximum Characters field is a number input, so the value a user can
 * actually leave behind is an empty string. Nothing validates it on the way
 * in — Rop_Rest_Api::save_post_format() only clamps numeric Twitter values,
 * and Rop_Post_Format_Model enforces no field types — so it reaches the
 * arithmetic in Rop_Post_Format_Helper::build_content(), which PHP 8 raises
 * as an uncaught TypeError.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test empty post format maximum length. class.
 */
class Test_RopPostFormatMaxLength extends WP_UnitTestCase {

	/**
	 * The post the content is built from.
	 *
	 * @var int
	 */
	private static $post_id;

	/**
	 * The account's post format as it was before a test touched it.
	 *
	 * @var array
	 */
	private $original_format;

	/**
	 * Init test accounts.
	 */
	public static function setUpBeforeClass(): void {
		Rop_InitAccounts::init();
	}

	/**
	 * Create a post whose content is long enough to be truncated.
	 */
	public function setUp(): void {
		parent::setUp();

		self::$post_id = wp_insert_post(
			array(
				'post_title'   => 'Post format maximum length',
				'post_content' => str_repeat( 'alpha bravo charlie delta echo foxtrot golf hotel india juliet ', 12 ),
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-2 month' ) ),
			)
		);

		$model                 = new Rop_Post_Format_Model( Rop_InitAccounts::ROP_TEST_SERVICE_NAME );
		$this->original_format = $model->get_post_format( Rop_InitAccounts::get_account_id() );
	}

	/**
	 * Put the account's post format back so no state leaks into other suites.
	 */
	public function tearDown(): void {
		$account_id = Rop_InitAccounts::get_account_id();
		$model      = new Rop_Post_Format_Model( Rop_InitAccounts::ROP_TEST_SERVICE_NAME );
		$model->add_update_post_format( $account_id, $this->original_format );

		delete_post_meta( self::$post_id, '_rop_edit_' . md5( $account_id ) );
		wp_delete_post( self::$post_id, true );

		parent::tearDown();
	}

	/**
	 * The three ways build_content() consumes the maximum length.
	 *
	 * @return array
	 */
	private function content_paths() {
		return array(
			'standard'       => array(),
			'without a link' => array( 'include_link' => false ),
			'edited content' => array( 'custom_content' => true ),
		);
	}

	/**
	 * Build the share content with a given persisted maximum length.
	 *
	 * @param mixed $maximum_length The value to persist as `maximum_length`.
	 * @param array $args           Optional. `custom_content` to queue edited
	 *                              content, `include_link` to toggle the link.
	 * @return array The build_content() result.
	 */
	private function build_with_maximum_length( $maximum_length, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'custom_content' => false,
				'include_link'   => true,
			)
		);

		$account_id = Rop_InitAccounts::get_account_id();
		$model      = new Rop_Post_Format_Model( Rop_InitAccounts::ROP_TEST_SERVICE_NAME );

		$format                    = $model->get_post_format( $account_id );
		$format['maximum_length']  = $maximum_length;
		$format['post_content']    = 'post_title_content';
		$format['hashtags']        = 'common-hashtags';
		$format['hashtags_common'] = '#news #wordpress';
		$format['include_link']    = $args['include_link'];
		$model->add_update_post_format( $account_id, $format );

		delete_post_meta( self::$post_id, '_rop_edit_' . md5( $account_id ) );
		if ( $args['custom_content'] ) {
			update_post_meta(
				self::$post_id,
				'_rop_edit_' . md5( $account_id ),
				array( 'text' => str_repeat( 'kilo lima mike november oscar papa ', 12 ) )
			);
		}

		$helper = new Rop_Post_Format_Helper();
		$helper->set_post_format( $account_id );

		return $helper->build_content( self::$post_id );
	}

	/**
	 * An empty maximum length must not fatal the sharing cron.
	 *
	 * @covers Rop_Post_Format_Helper::build_content
	 */
	public function test_empty_maximum_length_does_not_fatal() {
		foreach ( $this->content_paths() as $label => $args ) {
			$content = $this->build_with_maximum_length( '', $args );

			$this->assertIsArray( $content, 'build_content() fataled on the ' . $label . ' path.' );
			$this->assertArrayHasKey( 'display_content', $content );
		}
	}

	/**
	 * An empty maximum length keeps behaving as a zero one, as it did on PHP 7.
	 *
	 * @covers Rop_Post_Format_Helper::build_content
	 */
	public function test_empty_maximum_length_matches_zero() {
		foreach ( $this->content_paths() as $label => $args ) {
			$from_empty = $this->build_with_maximum_length( '', $args );

			$this->assertSame(
				$this->build_with_maximum_length( 0, $args ),
				$from_empty,
				'An empty length diverged from an integer zero on the ' . $label . ' path.'
			);
			$this->assertSame(
				$this->build_with_maximum_length( '0', $args ),
				$from_empty,
				"An empty length diverged from '0' on the " . $label . ' path.'
			);
		}
	}

	/**
	 * On the standard path a zero length has always meant no content.
	 *
	 * @covers Rop_Post_Format_Helper::build_content
	 */
	public function test_empty_maximum_length_yields_no_content() {
		$content = $this->build_with_maximum_length( '' );

		$this->assertSame( '', $content['display_content'] );
	}

	/**
	 * The numeric string the defaults ship keeps truncating as before.
	 *
	 * @covers Rop_Post_Format_Helper::build_content
	 */
	public function test_numeric_string_maximum_length_is_unchanged() {
		$content = $this->build_with_maximum_length( '240' );

		$this->assertNotEmpty( $content['display_content'] );
		$this->assertLessThanOrEqual( 240, strlen( $content['display_content'] ) );
	}

	/**
	 * An integer length still wins over the shorter default.
	 *
	 * @covers Rop_Post_Format_Helper::build_content
	 */
	public function test_integer_maximum_length_is_unchanged() {
		$short = $this->build_with_maximum_length( '240' );
		$long  = $this->build_with_maximum_length( 1900 );

		$this->assertGreaterThan( strlen( $short['display_content'] ), strlen( $long['display_content'] ) );
	}

	/**
	 * A negative length is not turned into a positive one.
	 *
	 * @covers Rop_Post_Format_Helper::build_content
	 */
	public function test_negative_maximum_length_is_unchanged() {
		$content = $this->build_with_maximum_length( -5 );

		$this->assertSame( '', $content['display_content'] );
	}
}
