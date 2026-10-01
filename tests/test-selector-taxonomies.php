<?php
/**
 * ROP Test taxonomy discovery under multilingual plugins for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test Rop_Posts_Selector_Model::get_taxonomies() with WPML loaded. class.
 */
class Test_RopSelectorTaxonomies extends WP_UnitTestCase {

	/**
	 * Account-less states that reach the multilingual taxonomy branch.
	 *
	 * @return array<string, array{0: array<string, mixed>}>
	 */
	public function provide_accountless_data(): array {
		return array(
			'missing key' => array( array() ),
			'null'        => array( array( 'active_accounts' => null ) ),
			'empty array' => array( array( 'active_accounts' => array() ) ),
		);
	}

	/**
	 * Taxonomies load with WPML active and no accounts.
	 *
	 * @param array<string, mixed> $rop_data Stored plugin data.
	 *
	 * @dataProvider provide_accountless_data
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_get_taxonomies_without_accounts( array $rop_data ): void {
		$this->load_wpml_stub();
		update_option( 'rop_data', $rop_data );
		$languages = $this->record_language_switches();

		$taxonomies = ( new Rop_Posts_Selector_Model() )->get_taxonomies( array( 'post' ) );

		$this->assertContains( 'category', wp_list_pluck( $taxonomies, 'tax' ) );
		$this->assertSame( array(), $languages->switched );
	}

	/**
	 * The first account's language is still applied with WPML active.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_get_taxonomies_uses_first_account_language(): void {
		$this->load_wpml_stub();
		update_option(
			'rop_data',
			array(
				'active_accounts' => array(
					'twitter_1_2' => array(
						'service' => 'twitter',
						'active'  => true,
					),
				),
				'post_format'     => array(
					'twitter_1_2' => array( 'wpml_language' => 'fr' ),
				),
			)
		);
		$languages = $this->record_language_switches();

		( new Rop_Posts_Selector_Model() )->get_taxonomies( array( 'post' ) );

		$this->assertSame( 'fr', $languages->switched[0] ?? null );
	}

	/**
	 * Mark WPML as loaded; tests using it run in a separate process.
	 */
	private function load_wpml_stub(): void {
		if ( ! function_exists( 'icl_object_id' ) ) {
			eval( 'function icl_object_id() {}' );
		}
		$this->factory->post->create( array( 'post_category' => array( $this->factory->category->create() ) ) );
	}

	/**
	 * Collect the languages passed to wpml_switch_language.
	 *
	 * @return stdClass Object whose `switched` list fills as the action fires.
	 */
	private function record_language_switches(): stdClass {
		$recorder           = new stdClass();
		$recorder->switched = array();
		add_action(
			'wpml_switch_language',
			function ( $language ) use ( $recorder ): void {
				$recorder->switched[] = $language;
			}
		);

		return $recorder;
	}
}
