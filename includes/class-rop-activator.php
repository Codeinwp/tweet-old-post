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
 * @author     ThemeIsle <friends@revive.social>
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

		self::rop_create_install_token();
		self::rop_set_first_install_date();

	}

	/**
	 * ROP installation tasks
	 *
	 * Creates unique ID for website used during authentication requests
	 *
	 * @since      8.3.0
	 * @package    Rop
	 * @subpackage Rop/includes
	 * @author     Revive Social <friends@revive.social>
	 */
	private static function rop_create_install_token() {

		$url = get_site_url();

		$token = hash( 'ripemd160', $url . date( 'Y-m-d H:i:s' ) );

		update_option( ROP_INSTALL_TOKEN_OPTION, $token, false );

	}

	/**
	 * Set Install Date.
	 *
	 * @since  1.0.0
	 */
	private static function rop_set_first_install_date() {

		// Create timestamp for when plugin was activated.
		$install_date = time();

		// If our option doesn't exist already, we'll create it with today's timestamp.
		if ( empty( get_option( 'rop_first_install_date' ) ) ) {
			add_option( 'rop_first_install_date', $install_date, '', 'yes' );
		}
	}

}
