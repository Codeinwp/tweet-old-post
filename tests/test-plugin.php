<?php
/**
 * Sample class for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

/**
 * Sample test class.
 */
class Test_ROP extends WP_UnitTestCase {

	/**
	 * Testing SDK loading.
	 *
	 * @access public
	 */
	public function test_sdk() {
		$this->assertTrue( class_exists( 'ThemeIsle_SDK_Loader' ) );
	}

}
