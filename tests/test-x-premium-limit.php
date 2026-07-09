<?php
/**
 * ROP Test X (Twitter) Premium character limit for PHPUnit.
 *
 * The higher character limit is only granted to Pro sites that explicitly opt
 * in (x_premium). Everyone else — including a spoofed x_premium flag on an
 * unlicensed site — must stay capped at the standard 280 characters.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       9.3.7
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test X Premium character limit. class.
 */
class Test_RopXPremiumLimit extends WP_UnitTestCase {

	/**
	 * Init test accounts.
	 */
	public static function setUpBeforeClass(): void {
		Rop_InitAccounts::init();
	}

	/**
	 * Persist a twitter post format through the REST save path and return the
	 * stored format.
	 *
	 * @param array $overrides Fields to override on the account's post format.
	 * @return array
	 */
	private function save_twitter_format( $overrides ) {
		$account_id = Rop_InitAccounts::get_account_id();
		$model      = new Rop_Post_Format_Model( 'twitter' );

		$format = array_merge( $model->get_post_format( $account_id ), $overrides );
		$data   = array(
			'service'    => 'twitter',
			'account_id' => $account_id,
			'data'       => $format,
		);

		$api    = new Rop_Rest_Api();
		$method = new ReflectionMethod( $api, 'save_post_format' );
		$method->setAccessible( true );
		$method->invoke( $api, $data );

		// Read back with a fresh model so we see the persisted option, not the
		// stale copy cached on the instance used to build the payload.
		$fresh = new Rop_Post_Format_Model( 'twitter' );

		return $fresh->get_post_format( $account_id );
	}

	/**
	 * Without the opt-in the limit is clamped to 280.
	 *
	 * @covers Rop_Rest_Api::save_post_format
	 */
	public function test_standard_account_is_capped_at_280() {
		$saved = $this->save_twitter_format( array( 'maximum_length' => 5000 ) );

		$this->assertSame( 280, (int) $saved['maximum_length'] );
		$this->assertEmpty( $saved['x_premium'] );
	}

	/**
	 * A spoofed x_premium flag on an unlicensed site cannot unlock the higher
	 * limit: it is coerced back to false and the length stays capped at 280.
	 *
	 * @covers Rop_Rest_Api::save_post_format
	 */
	public function test_spoofed_premium_without_license_is_rejected() {
		$saved = $this->save_twitter_format(
			array(
				'maximum_length' => 5000,
				'x_premium'      => true,
			)
		);

		$this->assertSame( 280, (int) $saved['maximum_length'] );
		$this->assertEmpty( $saved['x_premium'] );
	}

	/**
	 * A value already within the standard limit is left untouched.
	 *
	 * @covers Rop_Rest_Api::save_post_format
	 */
	public function test_value_within_limit_is_preserved() {
		$saved = $this->save_twitter_format( array( 'maximum_length' => 100 ) );

		$this->assertSame( 100, (int) $saved['maximum_length'] );
	}
}
