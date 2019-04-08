<?php

/**
 * Fired during plugin activation
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      8.0.0
 * @package    Rop
 * @subpackage Rop/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public static function activate() {

		// Add version when ROP was first installed on site to DB.
		$rop_first_install_version = get_option( 'rop_first_install_version' );

		if ( class_exists( 'Rop' ) ) {
			$rop = new Rop();
			$version = $rop->get_version();
		}

		if ( empty( $rop_first_install_version ) && ! empty( $version ) ) {
			add_option( 'rop_first_install_version', $version );
		}

	}

}
