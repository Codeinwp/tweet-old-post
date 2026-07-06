<?php
/**
 * ROP Test keyword filtering for PHPUnit.
 *
 * Covers the keyword parsing helper and the WHERE clause builder introduced
 * for the keyword based post filtering feature.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       9.3.7
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test keyword filtering. class.
 */
class Test_RopKeywordFilter extends WP_UnitTestCase {

	/**
	 * Init test accounts.
	 */
	public static function setUpBeforeClass(): void {
		Rop_InitAccounts::init();
	}

	/**
	 * The parser returns an empty array for empty / blank / non string input.
	 *
	 * @covers Rop_Settings_Model::parse_keyword_string
	 */
	public function test_parse_keyword_string_empty() {
		$this->assertSame( array(), Rop_Settings_Model::parse_keyword_string( '' ) );
		$this->assertSame( array(), Rop_Settings_Model::parse_keyword_string( '   ' ) );
		$this->assertSame( array(), Rop_Settings_Model::parse_keyword_string( ',, ,' ) );
		$this->assertSame( array(), Rop_Settings_Model::parse_keyword_string( null ) );
		$this->assertSame( array(), Rop_Settings_Model::parse_keyword_string( array( 'news' ) ) );
	}

	/**
	 * The parser trims, drops blanks, de-duplicates and re-indexes.
	 *
	 * @covers Rop_Settings_Model::parse_keyword_string
	 */
	public function test_parse_keyword_string_cleans_input() {
		$this->assertSame(
			array( 'news', 'tech' ),
			Rop_Settings_Model::parse_keyword_string( ' news , news ,, tech ' )
		);
		$this->assertSame(
			array( 'a', 'b', 'c' ),
			Rop_Settings_Model::parse_keyword_string( 'a,b,c' )
		);
	}

	/**
	 * The getter reads the stored setting through the shared parser.
	 *
	 * @covers Rop_Settings_Model::get_keyword_filter
	 */
	public function test_get_keyword_filter_reads_setting() {
		$settings = new Rop_Settings_Model();
		$current  = $settings->get_settings();

		$current['keyword_filter'] = 'alpha, beta, alpha';
		$settings->save_settings( $current );

		$this->assertSame( array( 'alpha', 'beta' ), $settings->get_keyword_filter() );

		// Restore an inactive (empty) filter so the feature stays off for other tests.
		$current['keyword_filter'] = '';
		$settings->save_settings( $current );
		$this->assertSame( array(), $settings->get_keyword_filter() );
	}

	/**
	 * With no keyword config the WHERE clause is returned untouched.
	 *
	 * @covers Rop_Posts_Selector_Model::filter_keyword_where
	 */
	public function test_filter_keyword_where_noop_without_config() {
		$selector = new Rop_Posts_Selector_Model();
		$query    = new WP_Query();

		$this->assertSame( ' AND 1=1', $selector->filter_keyword_where( ' AND 1=1', $query ) );
	}

	/**
	 * Include mode adds a positive LIKE group; exclude mode negates it.
	 *
	 * @covers Rop_Posts_Selector_Model::filter_keyword_where
	 */
	public function test_filter_keyword_where_include_and_exclude() {
		$selector = new Rop_Posts_Selector_Model();

		$include_query = new WP_Query();
		$include_query->set( 'rop_keyword_filter', array( 'keywords' => array( 'news' ), 'exclude' => false ) );
		$include = $selector->filter_keyword_where( '', $include_query );
		$this->assertStringContainsString( 'post_title LIKE', $include );
		$this->assertStringContainsString( 'post_content LIKE', $include );
		$this->assertStringNotContainsString( 'AND NOT', $include );

		$exclude_query = new WP_Query();
		$exclude_query->set( 'rop_keyword_filter', array( 'keywords' => array( 'news' ), 'exclude' => true ) );
		$exclude = $selector->filter_keyword_where( '', $exclude_query );
		$this->assertStringContainsString( 'AND NOT', $exclude );
	}
}
