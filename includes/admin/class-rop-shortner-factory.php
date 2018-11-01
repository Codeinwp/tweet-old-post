<?php
/**
 * The file that defines the factory for shortners plugin class
 *
 * A class that is used as a factory for building shortners.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin
 */

/**
 * Class Rop_Shortner_Factory
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Shortner_Factory {

	/**
	 * Build method for factory.
	 *
	 * @since   8.0.0
	 * @accesss public
	 * @param   string $shortner_name The shortner name.
	 * @return Rop_Url_Shortner_Abstract
	 * @throws Exception An PHP exception is thrown if shortner not found.
	 */
	public static function build( $shortner_name ) {
		$shortner = 'Rop_' . ucwords( str_replace( '.', '', $shortner_name ) ) . '_Shortner';
		if ( class_exists( $shortner ) ) {
			return new $shortner;
		}
		// @codeCoverageIgnoreStart
		throw new Exception( 'Invalid shortener name given.' );
		// @codeCoverageIgnoreEnd
	}
}
