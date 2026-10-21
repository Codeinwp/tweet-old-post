<?php
/**
 * ROP Test post selector language handling under multilingual plugins for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Tests Rop_Posts_Selector_Model language switching with WPML loaded.
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
		$this->save_french_account();
		$languages = $this->record_language_switches();

		( new Rop_Posts_Selector_Model() )->get_taxonomies( array( 'post' ) );

		$this->assertSame( 'fr', $languages->switched[0] ?? null );
	}

	/**
	 * WPML versions and the language switches each must receive.
	 *
	 * @return array{'WPML 4.x': array{0: '4.6.13', 1: array{'fr', 'en', 'fr', 'en'}}, 'WPML 5.0': array{0: '5.0.0', 1: array{'fr', null, 'fr', null}}}
	 */
	public function provide_wpml_restore_switches(): array {
		return array(
			// Before 5.0 the saved language is passed back.
			'WPML 4.x' => array( '4.6.13', array( 'fr', 'en', 'fr', 'en' ) ),
			// 5.0 nests switches, so each one is closed with null.
			'WPML 5.0' => array( '5.0.0', array( 'fr', null, 'fr', null ) ),
		);
	}

	/**
	 * Each language switch is undone the way the installed WPML expects.
	 *
	 * @param string            $wpml_version ICL_SITEPRESS_VERSION to emulate.
	 * @param list<string|null> $expected     Languages passed to wpml_switch_language, in order.
	 *
	 * @dataProvider provide_wpml_restore_switches
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_restores_language_for_wpml_version( string $wpml_version, array $expected ): void {
		define( 'ICL_SITEPRESS_VERSION', $wpml_version );
		$this->load_wpml_stub();
		$this->save_french_account();
		$languages = $this->record_language_switches();

		$selector = new Rop_Posts_Selector_Model();
		$selector->get_taxonomies( array( 'post' ) );
		$selector->select( 'twitter_1_2' );

		$this->assertSame( $expected, $languages->switched );
	}

	/**
	 * The share-once early exit in select() still restores the language.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_select_restores_language_when_share_once_is_exhausted(): void {
		define( 'ICL_SITEPRESS_VERSION', '5.0.0' );
		$this->load_wpml_stub();
		$this->save_french_account();
		$rop_data                 = get_option( 'rop_data' );
		$rop_data['posts_buffer'] = array( 'twitter_1_2' => array( 1 ) );
		update_option( 'rop_data', $rop_data );
		$settings                           = new Rop_Settings_Model();
		$general_settings                   = $settings->get_settings();
		$general_settings['more_than_once'] = false;
		$settings->save_settings( $general_settings );
		update_option( 'rop_one_time_share_accounts', array( 'twitter_1_2' ) );
		$languages = $this->record_language_switches();

		$result = ( new Rop_Posts_Selector_Model() )->select( 'twitter_1_2' );

		$this->assertNull( $result );
		$this->assertSame( array( 'fr', null ), $languages->switched );
	}

	/**
	 * Mark WPML as loaded with English current; tests using it run in a separate process.
	 */
	private function load_wpml_stub(): void {
		if ( ! function_exists( 'icl_object_id' ) ) {
			eval( 'function icl_object_id() {}' );
		}
		add_filter(
			'wpml_current_language',
			static function (): string {
				return 'en';
			}
		);
		$this->factory->post->create( array( 'post_category' => array( $this->factory->category->create() ) ) );
	}

	/**
	 * Save one active account whose post format shares in French, under a Pro plan that allows it.
	 */
	private function save_french_account(): void {
		define( 'ROP_PRO_VERSION', '3.1.0' );
		update_option(
			'tweet_old_post_pro_license_data',
			(object) array(
				'license'  => 'valid',
				'price_id' => 2,
			)
		);
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
	}

	/**
	 * Collect the languages passed to wpml_switch_language.
	 *
	 * @return stdClass&object{switched: list<string|null>} Object whose `switched` list fills as the action fires.
	 */
	private function record_language_switches(): stdClass {
		$recorder           = new stdClass();
		$recorder->switched = array();
		add_action(
			'wpml_switch_language',
			function ( ?string $language ) use ( $recorder ): void {
				$recorder->switched[] = $language;
			}
		);

		return $recorder;
	}
}
