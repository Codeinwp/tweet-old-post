<?php
/**
 * ROP Test class for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

/**
 * Test_ROP class.
 */
class Test_ROP extends WP_UnitTestCase {
	/**
	 * Settings model to interact with.
	 *
	 * @var Rop_Settings_Model Settings model.
	 */
	private static $settings;

	/**
	 * Testing SDK loading.
	 *
	 * @access public
	 */
	public function test_sdk() {
		$this->assertTrue( class_exists( 'ThemeisleSDK\\Loader' ), ' ThemeIsle SDK Is NOT present.' );
		$all_plugins = apply_filters( 'themeisle_sdk_products', array() );
		$this->assertContains( ROP_LITE_BASE_FILE, $all_plugins, 'ThemeIsle is NOT loaded' );

		$this->assertFalse( ROP_DEBUG );
	}

	/**
	 * Testing General Settings.
	 *
	 * @access public
	 */
	public function test_global_settings() {
		$global = new Rop_Global_Settings();
		$this->assertFalse( $global->get_start_time(), 'By default the start should be off' );
		$global->update_start_time();
		$this->assertLessThan( 5, ( time() - $global->get_start_time() ), ' Starttime is not relative to unix timestamp.' );
		$global->reset_start_time();
		$this->assertFalse( $global->get_start_time(), 'After reset start time should be false' );
		$this->assertEquals( - 1, $global->license_type(), 'License type should be -1 by default' );
		$defaults = $global->get_default_settings();
		$this->assertNotEmpty( $defaults, 'Default general settings should not be empty.' );
	}

	/**
	 * Testing Settings actions.
	 *
	 * @access public
	 */
	public function test_settings_model() {
		$settings = new Rop_Settings_Model();
		$this->assertFalse( $settings->get_start_time(), 'Start time should be false by default.' );
		$this->assertFalse( $settings->get_custom_messages(), 'Custom messages should be off by default.' );
		$this->assertEmpty( $settings->get_selected_posts(), 'Exclude posts should be empty by default.' );
		$this->assertTrue( $settings->get_ga_tracking(), 'GA tags should be on by default.' );
		$this->assertEquals( 10.00, $settings->get_interval(), 'Default interval should be 4 hours.' );
		$this->assertEquals( 365, $settings->get_maximum_post_age(), 'Default maximum age should be 0' );
		$this->assertEquals( 30, $settings->get_minimum_post_age(), 'Default minimum age should be 0' );
		$this->assertTrue( $settings->get_more_than_once(), 'More than once setting should be on' );
		$this->assertNotEmpty( $settings->get_selected_post_types(), 'Default we should have the post type selected' );
		$this->assertEquals( 1, $settings->get_number_of_posts(), 'By default we should have 1 post selected.' );

		$settings_data = $settings->get_settings();
		$this->assertNotEmpty( $settings_data, 'Settings data should not be empty' );
		self::$settings = $settings;
	}

	public function testing_settings_validation() {

		$settings_data = self::$settings->get_settings();
		$settings      = self::$settings;
		$settings->save_settings( $settings_data );
		$settings_data_after = $settings->get_settings();
		$this->assertEquals( $settings_data, $settings_data_after, 'Settings data missmatch after no change.' );

		$settings_data['custom_messages'] = true;
		$settings->save_settings( $settings_data );
		$this->assertTrue( $settings->get_custom_messages(), 'Custom share messages not changed.' );

		$settings_data['number_of_posts'] = 10;
		$settings->save_settings( $settings_data );
		$this->assertNotEquals( 10, $settings->get_number_of_posts(), 'It should not allow to save more than 5 posts per cycle.' );

		$settings_data['number_of_posts'] = - 1;
		$settings->save_settings( $settings_data );
		$this->assertNotEquals( - 1, $settings->get_number_of_posts(), 'It should not allow to save negative number of posts ' );

		$settings_data['default_interval'] = 0.0001;
		$settings->save_settings( $settings_data );
		$this->assertNotEquals( 0.0001, $settings->get_interval(), 'Time interval should not be less than 6 min.' );

	}

}
