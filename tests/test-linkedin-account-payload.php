<?php
/**
 * ROP Test LinkedIn account payload for PHPUnit.
 *
 * The payload the auth service sends carries integer LinkedIn Page
 * ids and a null `img` for accounts without a picture, and must still be added.
 *
 * @package    ROP
 * @subpackage Tests
 */

/**
 * Test LinkedIn account payload validation.
 */
class Test_RopLinkedinAccountPayload extends WP_UnitTestCase {

	/**
	 * Encode a pages list the way the auth service does.
	 *
	 * @param list<array<string, mixed>> $entries Account entries.
	 * @return array{id: string, pages: string}
	 */
	private function build_payload( array $entries ): array {
		$entries[] = array( 'notify_user_at' => 4102444800 );

		return array(
			'id'    => base64_encode( serialize( 'AbC123xYz' ) ),
			'pages' => base64_encode( serialize( $entries ) ),
		);
	}

	/**
	 * Account entry shaped like the auth service's member entry.
	 *
	 * @param array<string, mixed> $overrides Fields to replace.
	 * @return array<string, mixed>
	 */
	private function member_entry( array $overrides = array() ): array {
		return array_merge(
			array(
				'id'           => 'AbC123xYz',
				'account'      => 'member@example.com',
				'is_company'   => false,
				'user'         => 'Test Member',
				'img'          => 'https://media.licdn.com/member.jpg',
				'access_token' => 'test-token',
			),
			$overrides
		);
	}

	/**
	 * Entries as the auth service sends them: a member without a photo and a Page with an integer id and no logo.
	 *
	 * @return list<array<string, mixed>>
	 */
	private function auth_service_entries(): array {
		return array(
			$this->member_entry( array( 'img' => null ) ),
			array(
				'id'           => 5552231,
				'account'      => 'member@example.com',
				'img'          => null,
				'is_company'   => true,
				'user'         => 'LinkedIn Page: Test Page',
				'access_token' => 'test-token',
			),
		);
	}

	/**
	 * Clear the option written by a successful add.
	 */
	public function tearDown(): void {
		delete_option( 'rop_linkedin_refresh_token_notice' );
		parent::tearDown();
	}

	/**
	 * A member without a photo and a Page with an integer id and no logo are added.
	 */
	public function test_accepts_null_img_and_integer_page_id(): void {
		$service = new Rop_Linkedin_Service();
		$this->assertTrue( $service->add_account_with_app( $this->build_payload( $this->auth_service_entries() ) ) );

		$accounts = $service->get_service()['available_accounts'];
		$this->assertCount( 2, $accounts );
		$this->assertSame( '', $accounts[0]['img'] );
		$this->assertSame( '5552231', $accounts[1]['id'] );
		$this->assertSame( '', $accounts[1]['img'] );
	}

	/**
	 * Entries the account builder cannot read are still rejected.
	 *
	 * @return array<string, array{0: array<string, mixed>}>
	 */
	public function provide_incomplete_entries(): array {
		$missing_img = $this->member_entry();
		unset( $missing_img['img'] );

		return array(
			'img key missing' => array( $missing_img ),
			'array img'       => array( $this->member_entry( array( 'img' => array( 'x' ) ) ) ),
			'empty string id' => array( $this->member_entry( array( 'id' => '' ) ) ),
			'array id'        => array( $this->member_entry( array( 'id' => array( 'x' ) ) ) ),
			'null token'      => array( $this->member_entry( array( 'access_token' => null ) ) ),
		);
	}

	/**
	 * Integer Page ids and null images are accepted while incomplete entries are rejected.
	 *
	 * @dataProvider provide_incomplete_entries
	 *
	 * @param array<string, mixed> $entry Account entry.
	 */
	public function test_rejects_incomplete_entry( array $entry ): void {
		$service = new Rop_Linkedin_Service();

		$this->assertTrue( $service->add_account_with_app( $this->build_payload( $this->auth_service_entries() ) ) );
		$this->assertFalse( $service->add_account_with_app( $this->build_payload( array( $entry ) ) ) );
	}
}
